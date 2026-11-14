<?php

namespace App\Filament\Pages\Auth;

use App\Mail\SendOtpCodeMail;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class Login extends BaseLogin
{
    protected static string $layout = 'filament.pages.auth.layout';

    protected static string $view = 'filament.pages.auth.login';

    public bool $requiresOtp = false;

    public string $otp = '';

    public ?int $pendingUserId = null;

    public ?string $pendingUserEmail = null;

    public bool $rememberMe = false;

    public ?string $devOtpHint = null;

    public function getHeading(): string
    {
        return $this->requiresOtp ? 'Two-Factor Authentication' : 'Log in to your Account';
    }

    public function getSubHeading(): string
    {
        return $this->requiresOtp 
            ? "Enter the 6-digit code sent to {$this->pendingUserEmail}" 
            : 'Welcome back!';
    }

    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();
        $throttleKey = Str::transliterate(Str::lower($data['email']) . '|' . request()->ip());

        // Limit login attempts to prevent brute-force attacks (OWASP A07)
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            Notification::make()
                ->title('Too many login attempts')
                ->body("Too many failed login attempts. Please try again in {$seconds} seconds.")
                ->danger()
                ->send();

            $this->addError('email', trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]));

            return null;
        }

        /** @var User|null $user */
        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);
            $this->throwFailureValidationException();
        }

        RateLimiter::clear($throttleKey);

        if (! $user->is_active) {
            Notification::make()
                ->title('Account is deactivated')
                ->body('Please contact your administrator for assistance.')
                ->danger()
                ->send();

            return null;
        }

        // Generate 6-digit OTP code
        $otpCode = (string) random_int(100000, 999999);
        Cache::put('auth_otp_' . $user->id, $otpCode, now()->addMinutes(10));

        // Dispatch OTP Email
        try {
            Mail::to($user->email)->send(new SendOtpCodeMail($otpCode, $user->name, 'Sign In'));
            error_log("FITLEDGER_EMAIL_SUCCESS: OTP email dispatched to {$user->email}");
        } catch (\Throwable $e) {
            \Log::warning('OTP email sending failed: ' . $e->getMessage());
            error_log('FITLEDGER_EMAIL_ERROR: ' . $e->getMessage());
        }

        \Log::info("FITLEDGER_AUTH_OTP: Code for {$user->email} is {$otpCode}");
        error_log("FITLEDGER_AUTH_OTP: Code for {$user->email} is {$otpCode}");

        $this->requiresOtp = true;
        $this->pendingUserId = $user->id;
        $this->pendingUserEmail = $user->email;
        $this->rememberMe = (bool) ($data['remember'] ?? false);
        if (app()->environment(['local', 'testing'])) {
            $this->devOtpHint = $otpCode;
        }

        Notification::make()
            ->title('Verification code sent')
            ->body("We've sent a 6-digit code to {$user->email}")
            ->success()
            ->send();

        return null;
    }

    public function verifyOtp(): ?LoginResponse
    {
        $this->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $otpThrottleKey = 'otp_verify|' . $this->pendingUserId . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($otpThrottleKey, 5)) {
            $seconds = RateLimiter::availableIn($otpThrottleKey);
            $this->addError('otp', "Too many incorrect verification attempts. Please wait {$seconds} seconds.");
            return null;
        }

        $cachedCode = Cache::get('auth_otp_' . $this->pendingUserId);

        if (! $cachedCode || $cachedCode !== trim($this->otp)) {
            RateLimiter::hit($otpThrottleKey, 120);
            $this->addError('otp', 'The verification code is invalid or has expired.');
            return null;
        }

        RateLimiter::clear($otpThrottleKey);
        Cache::forget('auth_otp_' . $this->pendingUserId);

        Filament::auth()->loginUsingId($this->pendingUserId, $this->rememberMe);
        session()->regenerate();

        return app(LoginResponse::class);
    }

    public function resendOtp(): void
    {
        if (! $this->pendingUserId) {
            return;
        }

        $resendKey = 'otp_resend|' . $this->pendingUserId . '|' . request()->ip();
        if (RateLimiter::tooManyAttempts($resendKey, 3)) {
            $seconds = RateLimiter::availableIn($resendKey);
            Notification::make()
                ->title('Please wait')
                ->body("Please wait {$seconds} seconds before requesting another code.")
                ->warning()
                ->send();
            return;
        }
        RateLimiter::hit($resendKey, 60);

        $user = User::find($this->pendingUserId);
        if (! $user) {
            return;
        }

        $otpCode = (string) random_int(100000, 999999);
        Cache::put('auth_otp_' . $user->id, $otpCode, now()->addMinutes(10));
        if (app()->environment(['local', 'testing'])) {
            $this->devOtpHint = $otpCode;
        }

        try {
            Mail::to($user->email)->send(new SendOtpCodeMail($otpCode, $user->name, 'Sign In'));
        } catch (\Throwable $e) {
            \Log::warning('Resend OTP email failed: ' . $e->getMessage());
        }

        Notification::make()
            ->title('Fresh code sent')
            ->body("A new verification code has been dispatched to {$user->email}")
            ->success()
            ->send();
    }

    public function cancelOtp(): void
    {
        $this->requiresOtp = false;
        $this->otp = '';
        $this->pendingUserId = null;
        $this->pendingUserEmail = null;
        $this->devOtpHint = null;
    }
}
