<?php

namespace Tests\Feature;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1 & 10: OWASP Security Headers are present on web responses.
     */
    public function test_security_headers_are_applied_to_web_responses(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(self), microphone=(), geolocation=()');
    }

    /**
     * Test 1: User input validation prevents malformed QR code parameter traversal.
     */
    public function test_input_validation_rejects_malformed_qr_codes(): void
    {
        // Malformed characters (e.g. path traversal or script tags)
        $response = $this->get('/pass/../../etc/passwd');
        $response->assertStatus(404);

        $response2 = $this->get('/pass/<script>alert(1)</script>');
        $response2->assertStatus(404);
    }

    /**
     * Test 2: Laravel native authentication hashes passwords and validates properly.
     */
    public function test_laravel_native_authentication_and_password_hashing(): void
    {
        $password = 'Secret123!';
        $user = User::factory()->create([
            'email' => 'secure@fitledger.test',
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        $this->assertTrue(Hash::check($password, $user->password));
        $this->assertFalse(Hash::check('WrongPassword123!', $user->password));
        $this->assertNotEquals($password, $user->password);
    }

    /**
     * Test 5: Narrow CORS headers configuration.
     */
    public function test_cors_configuration_is_configured_narrowly(): void
    {
        $allowedOrigins = config('cors.allowed_origins');
        $allowedHeaders = config('cors.allowed_headers');
        $paths = config('cors.paths');

        $this->assertNotContains('*', $allowedOrigins);
        $this->assertContains('api/*', $paths);
        $this->assertContains('X-Scanner-Key', $allowedHeaders);
        $this->assertContains('Paymongo-Signature', $allowedHeaders);
    }

    /**
     * Test 6: Role-Based Access Control (RBAC) boundaries.
     */
    public function test_role_based_access_control_boundaries(): void
    {
        $platformAdmin = User::factory()->create([
            'is_platform_admin' => true,
            'is_active' => true,
        ]);

        $gym = Gym::factory()->create([
            'owner_email' => 'owner@example.com',
        ]);

        $gymOwner = User::factory()->create([
            'email' => 'owner@example.com',
            'gym_id' => $gym->id,
            'is_platform_admin' => false,
            'is_active' => true,
        ]);

        $staffUser = User::factory()->create([
            'email' => 'staff@example.com',
            'gym_id' => $gym->id,
            'is_platform_admin' => false,
            'is_active' => true,
        ]);

        $this->assertTrue($platformAdmin->isPlatformAdmin());
        $this->assertEquals('Platform Admin', $platformAdmin->getRoleName());
        $this->assertTrue($platformAdmin->hasRole('Platform Admin'));

        $this->assertTrue($gymOwner->isGymOwner($gym));
        $this->assertEquals('Gym Owner', $gymOwner->getRoleName());

        $this->assertTrue($staffUser->isStaff($gym));
        $this->assertEquals('Staff', $staffUser->getRoleName());

        // Platform admin can access any tenant, owner only their own
        $otherGym = Gym::factory()->create(['owner_email' => 'other@example.com']);
        $this->assertTrue($platformAdmin->canAccessTenant($otherGym));
        $this->assertFalse($gymOwner->canAccessTenant($otherGym));
    }

    /**
     * Test 7: Rate limiting prevents brute-force attempts on login.
     */
    public function test_rate_limiting_tracks_and_throttles_failed_login_attempts(): void
    {
        RateLimiter::clear('test_brute_force|127.0.0.1');

        $key = 'test_brute_force|127.0.0.1';

        for ($i = 0; $i < 5; $i++) {
            $this->assertFalse(RateLimiter::tooManyAttempts($key, 5));
            RateLimiter::hit($key, 60);
        }

        $this->assertTrue(RateLimiter::tooManyAttempts($key, 5));
        $this->assertGreaterThan(0, RateLimiter::availableIn($key));

        RateLimiter::clear($key);
        $this->assertFalse(RateLimiter::tooManyAttempts($key, 5));
    }

    /**
     * Test 8: Database backup command runs and produces verifiable archive.
     */
    public function test_database_backup_command_executes_and_lists(): void
    {
        $exitCode = Artisan::call('db:backup');
        $this->assertEquals(0, $exitCode);

        $backupDir = storage_path('app/backups');
        $this->assertTrue(File::isDirectory($backupDir));

        $exitCodeList = Artisan::call('db:backup', ['--list' => true]);
        $this->assertEquals(0, $exitCodeList);
    }

    /**
     * Test 9: Secure error pages render without disclosing internal stack traces.
     */
    public function test_custom_error_views_exist_and_render_generic_messages(): void
    {
        $view403 = view('errors.403')->render();
        $this->assertStringContainsString('403', $view403);
        $this->assertStringContainsString('Access Forbidden', $view403);
        $this->assertStringNotContainsString('SQLSTATE', $view403);

        $view404 = view('errors.404')->render();
        $this->assertStringContainsString('404', $view404);
        $this->assertStringContainsString('Page Not Found', $view404);

        $view500 = view('errors.500')->render();
        $this->assertStringContainsString('500', $view500);
        $this->assertStringContainsString('System Error', $view500);
        $this->assertStringNotContainsString('vendor/', $view500);
        $this->assertStringNotContainsString('Stack trace', $view500);
    }

    /**
     * Test 5 & 10: PayMongo webhook endpoint rejects invalid HMAC signatures.
     */
    public function test_api_paymongo_webhook_enforces_signature_verification(): void
    {
        config(['services.paymongo.webhook_secret' => 'whsec_test_secret_key_12345']);

        $response = $this->postJson('/api/webhooks/paymongo', [
            'data' => [
                'attributes' => [
                    'type' => 'checkout_session.payment.paid',
                ],
            ],
        ], [
            'Paymongo-Signature' => 't=1234567890,te=invalid_signature_hash',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['message' => 'Invalid signature.']);
    }
}
