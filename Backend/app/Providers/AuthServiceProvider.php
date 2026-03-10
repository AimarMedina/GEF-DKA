<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function register(): void
    {
        // no-op
    }

    /**
     * Bootstrap any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gates based on the `tipo` field on User model.
        Gate::define('manage-imports', function (User $user) {
            return $user->tipo === 'admin';
        });

        Gate::define('manage-empresas', function (User $user) {
            return $user->tipo === 'admin';
        });

        Gate::define('create-instructor', function (User $user) {
            return $user->tipo === 'admin';
        });

        Gate::define('assign-instructor', function (User $user) {
            return in_array($user->tipo, ['admin', 'tutor']);
        });
    }
}
