<?php

namespace App\Providers\Filament;

use DutchCodingCompany\FilamentDeveloperLogins\FilamentDeveloperLoginsPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;
use Swis\Filament\Backgrounds\FilamentBackgroundsPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->spa()
            ->font('Poppins')
            // ->topNavigation()
            ->path('admin')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('16rem')
            ->maxContentWidth(\Filament\Support\Enums\Width::Full)
            ->login()
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(function () {
                        /** @var \App\Models\User|null $user */
                        $user = auth()->user();
                        return $user ? $user->name : 'Profil';
                    })
                    ->url(fn(): string => EditProfilePage::getUrl())
                    ->icon('heroicon-m-user-circle'),
                'mobile' => MenuItem::make()
                    ->label('Presensi Mobile Pegawai')
                    ->url(fn(): string => route('mobile.dashboard'))
                    ->icon('heroicon-m-device-phone-mobile'),
            ])

            ->navigationGroups([
                NavigationGroup::make('Keuangan & SPJ')
                    ->collapsed(false),
                NavigationGroup::make('keuangan')
                    ->collapsed(),
                NavigationGroup::make('Absensi')
                    ->collapsed(),
                NavigationGroup::make('LHK')
                    ->collapsed(),
                NavigationGroup::make('Master Data')
                    ->collapsed(),
                NavigationGroup::make('Pengaturan')
                    ->collapsed(),
                NavigationGroup::make('Konfigurasi Sistem')
                    ->collapsed(),
                NavigationGroup::make('Filament Shield')
                    ->collapsed(),
                NavigationGroup::make('Pelindung')
                    ->collapsed(),
            ])
            ->colors([
                'primary' => Color::Amber,
                'danger' => Color::Red,
                'warning' => Color::Orange,
                'success' => Color::Green,
                'info' => Color::Blue,
                'purple' => Color::Purple,
                'teal' => Color::Teal,
                'rose' => Color::Rose,
                'indigo' => Color::Indigo,
                'cyan' => Color::Cyan,
                'emerald' => Color::Emerald,
                'violet' => Color::Violet,
                'lime' => Color::Lime,
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn(): string => view('filament.hooks.topbar-clock')->render()
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn(): string => view('filament.hooks.sidebar-accordion')->render()
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn(): string => view('filament.hooks.sidebar-accordion')->render()
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                // FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentBackgroundsPlugin::make(),
                FilamentEditProfilePlugin::make()
                    ->shouldShowAvatarForm(
                        value: true,
                        directory: 'avatars',
                        rules: 'mimes:jpeg,png|max:1024'
                    ),
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                FilamentDeveloperLoginsPlugin::make()
                    ->enabled(app()->environment('local'))
                    ->users([
                        'SuperAdmin (Doni)' => 'superadmin@gmail.com',
                        'Kadis (Afrineldi)' => 'kepaladinas@gmail.com',
                        'Sekretaris (Desmiwarti)' => 'sekretaris@gmail.com',
                        'Kasubag Umum (Zulfitri)' => 'admin@gmail.com',
                        'Kabid (Indrawadi)' => 'kabid@gmail.com',
                        'Pegawai (Wilda)' => 'wilda@gmail.com',
                    ]),
            ]);
    }
}
