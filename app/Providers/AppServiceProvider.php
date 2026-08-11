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
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        // Paksa Laravel untuk menggunakan view filament-tables yang ada di resources/views/vendor
        // karena package leek/filament-header-filters melakukan hard-override view bawaan Filament.
        $this->app->make('view')->prependNamespace(
            'filament-tables',
            resource_path('views/vendor/filament-tables')
        );
    }
}
