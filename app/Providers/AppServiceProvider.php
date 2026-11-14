<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce HTTPS across the entire application in production
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // OWASP & Laravel Input Validation: Prevent mass-assignment silently discarding attributes
        Model::preventSilentlyDiscardingAttributes(! app()->isProduction());

        // Configure robust default password complexity requirements for Laravel Auth
        Password::defaults(function () {
            $rule = Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers();

            return app()->isProduction()
                ? $rule->symbols()->uncompromised()
                : $rule;
        });
    }
}
