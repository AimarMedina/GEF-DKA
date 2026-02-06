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

        // Gates combinados (útiles para permisos compartidos)
        Gate::define('es-personal', function (User $user) {
            return in_array($user->tipo, ['admin', 'tutor', 'instructor']);
        });
    }
}
