<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gate para verificar si es admin
        Gate::define('es-admin', function (User $user) {
            return $user->tipo === 'admin';
        });

        // Gate para verificar si es tutor
        Gate::define('es-tutor', function (User $user) {
            return $user->tipo === 'tutor';
        });

        // Gate para verificar si es instructor
        Gate::define('es-instructor', function (User $user) {
            return $user->tipo === 'instructor';
        });

        // Gate para verificar si es alumno
        Gate::define('es-alumno', function (User $user) {
            return $user->tipo === 'alumno';
        });
    }
}