<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        // Regla 1: Acceso General al Panel (Admin O Staff)
        // Se usa en rutas generales como ver usuarios, dashboard básico, etc.
        Gate::define('admin-or-staff', function ($user) {
            return $user->roles()->whereIn('rol', ['admin','staff'])->exists();
        });

        // Regla 2: Acceso TOTAL (Solo Admin)
        // Se usará para ocultar: Gráficas de dinero, Configuración de aforo, Gestión de Planes.
        Gate::define('admin-only', function ($user) {
            return $user->roles()->where('rol', 'admin')->exists();
        });
    }
}