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
        $this->app->bind(
            \Filament\Auth\Notifications\ResetPassword::class,
            \App\Notifications\SaktiResetPasswordNotification::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Kustomisasi pesan email reset password Laravel & Filament dengan template SAKTI
        \Illuminate\Auth\Notifications\ResetPassword::toMailUsing(function ($notifiable, $token) {
            $notification = app(\Filament\Auth\Notifications\ResetPassword::class, ['token' => $token]);
            $notification->url = \Filament\Facades\Filament::getResetPasswordUrl($token, $notifiable);
            return $notification->toMail($notifiable);
        });

        // Paksa HTTPS jika di lingkungan production atau URL menggunakan HTTPS
        if ($this->app->environment('production') || str_starts_with(config('app.url', ''), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \App\Models\PengajuanSpj::observe(\App\Observers\PengajuanSpjObserver::class);
        \App\Models\PengajuanIzin::observe(\App\Observers\PengajuanIzinObserver::class);

        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        // Daftarkan RolePolicy secara eksplisit untuk mencegah kegagalan resolusi PSR-4 di server production
        \Illuminate\Support\Facades\Gate::policy(
            \Spatie\Permission\Models\Role::class,
            \App\Policies\RolePolicy::class
        );

        // Paksa Laravel untuk menggunakan view filament-tables yang ada di resources/views/vendor
        // karena package leek/filament-header-filters melakukan hard-override view bawaan Filament.
        $this->app->make('view')->prependNamespace(
            'filament-tables',
            resource_path('views/vendor/filament-tables')
        );

        // Format global DatePicker, DateTimePicker, dan TimePicker Filament
        \Filament\Forms\Components\DatePicker::configureUsing(function (\Filament\Forms\Components\DatePicker $datePicker): void {
            $datePicker->native(false)->displayFormat('d/m/Y');
        });

        \Filament\Forms\Components\DateTimePicker::configureUsing(function (\Filament\Forms\Components\DateTimePicker $dateTimePicker): void {
            // Hindari mengubah TimePicker (karena TimePicker extends DateTimePicker)
            if (get_class($dateTimePicker) === \Filament\Forms\Components\DateTimePicker::class) {
                $dateTimePicker->native(false)->displayFormat('d/m/Y H:i');
            }
        });

        \Filament\Forms\Components\TimePicker::configureUsing(function (\Filament\Forms\Components\TimePicker $timePicker): void {
            $timePicker->native(false)->displayFormat('H:i')->format('H:i')->seconds(false);
        });

        // Batasan ukuran upload file global maksimal 2MB (2048 KB)
        \Filament\Forms\Components\FileUpload::configureUsing(function (\Filament\Forms\Components\FileUpload $fileUpload): void {
            $fileUpload->maxSize(2048);
        });

        // Registrasi Observers untuk Audit Trail / Log Aktivitas
        \App\Models\PengajuanSpj::observe(\App\Observers\PengajuanSpjObserver::class);
        \App\Models\Lhk::observe(\App\Observers\LhkObserver::class);
        \App\Models\AbsensiHarian::observe(\App\Observers\AbsensiHarianObserver::class);
        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\PengaturanAplikasi::observe(\App\Observers\PengaturanAplikasiObserver::class);

        // Override menu-builder-items component for Filament 4 compatibility
        \Livewire\Livewire::component('menu-builder-items', \App\Livewire\MenuItems::class);

        // Catat Event Autentikasi Pengguna
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function (\Illuminate\Auth\Events\Login $event): void {
            if ($event->user instanceof \App\Models\User) {
                \App\Services\ActivityLogger::log(
                    event: 'login',
                    module: 'Autentikasi',
                    description: "Pengguna {$event->user->name} berhasil login ke dalam sistem",
                    subject: $event->user,
                    user: $event->user
                );
            }
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Logout::class, function (\Illuminate\Auth\Events\Logout $event): void {
            if ($event->user instanceof \App\Models\User) {
                \App\Services\ActivityLogger::log(
                    event: 'logout',
                    module: 'Autentikasi',
                    description: "Pengguna {$event->user->name} logout dari sistem",
                    subject: $event->user,
                    user: $event->user
                );
            }
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Failed::class, function (\Illuminate\Auth\Events\Failed $event): void {
            $identifier = $event->credentials['email'] ?? ($event->credentials['nip'] ?? 'Tidak diketahui');
            \App\Services\ActivityLogger::log(
                event: 'login_failed',
                module: 'Autentikasi',
                description: "Percobaan login gagal untuk akun/identitas: {$identifier}",
                properties: ['identifier' => $identifier],
                user: $event->user instanceof \App\Models\User ? $event->user : null
            );
        });
    }
}
