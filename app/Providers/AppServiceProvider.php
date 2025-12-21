<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Super Admin Bypass - Automatically grant all permissions
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            // Users with "Super Admin" role bypass all permission checks
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}
