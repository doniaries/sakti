<?php

namespace App\Providers\Filament;

use DutchCodingCompany\FilamentDeveloperLogins\FilamentDeveloperLoginsPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
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
            ->breadcrumbs(false)
            ->font('Roboto')
            // ->topNavigation()
            ->path('admin')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('16rem')
            ->maxContentWidth(\Filament\Support\Enums\Width::Full)
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(function () {
                        /** @var \App\Models\User|null $user */
                        $user = \Illuminate\Support\Facades\Auth::user();
                        return $user ? $user->name : 'Profil';
                    })
                    ->url(fn(): string => EditProfilePage::getUrl())
                    ->icon('heroicon-m-user-circle'),
                'mobile' => MenuItem::make()
                    ->label('Presensi Mobile Pegawai')
                    ->url(fn(): string => route('mobile.dashboard'))
                    ->openUrlInNewTab()
                    ->icon('heroicon-m-device-phone-mobile'),
            ])

            ->navigationItems([
                NavigationItem::make('Presensi Mobile')
                    ->url(fn(): string => route('mobile.dashboard'), shouldOpenInNewTab: true)
                    ->icon('heroicon-o-device-phone-mobile')
                    ->sort(2),
            ])
            ->navigationGroups([
                NavigationGroup::make('Master Keuangan')
                    ->icon('heroicon-o-banknotes')
                    ->collapsed(),
                NavigationGroup::make('Absensi')
                    ->collapsed(),
                NavigationGroup::make('LHK')
                    ->collapsed(),
                NavigationGroup::make('Master Data')
                    ->collapsed(),
                NavigationGroup::make('Pengaturan')
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
            ->defaultAvatarProvider(\App\Filament\AvatarProviders\SaktiAvatarProvider::class)
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_BEFORE,
                fn(): string => view('filament.hooks.topbar-clock')->render()
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                fn(): string => view('filament.hooks.sidebar-footer')->render()
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
                    ->shouldRegisterNavigation(false)
                    ->shouldShowEditPasswordForm(false)
                    ->customProfileComponents([
                        \App\Livewire\Filament\CustomEditPasswordForm::class,
                    ])
                    ->shouldShowAvatarForm(
                        value: true,
                        directory: 'avatars',
                        rules: 'mimes:jpeg,png|max:1024'
                    ),
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make()
                    ->navigationGroup('Pengaturan')
                    ->navigationLabel('Peran & Hak Akses')
                    ->navigationIcon('heroicon-o-shield-check')
                    ->navigationSort(99),
                FilamentDeveloperLoginsPlugin::make()
                    ->enabled(app()->environment('local'))
                    ->users([
                        'SuperAdmin Sistem' => 'superadmin@gmail.com',
                        'Admin Dinas (Zulfitri)' => 'admin@gmail.com',
                        'PA / Kadis (Afrineldi)' => 'kepaladinas@gmail.com',
                        'PPK (Julhardianto)' => 'julhardianto@gmail.com',
                        'PPTK Pemasaran (Doni Aries)' => 'doniaries@gmail.com',
                        'PPTK Sekretariat (Desmawati)' => 'sekretaris@gmail.com',
                        'PPTK Destinasi (Wilda Ardes)' => 'wilda@gmail.com',
                        'PPTK Dispora (Indrawadi)' => 'kabid@gmail.com',
                        'JF (Diki Zulfikar)' => 'dikizulfikar@gmail.com',
                        'JF (Andra Pebtaliyus)' => 'andrapebtaliyus@gmail.com',
                        'Staf (Maulana Ibrahim)' => 'maulanaibrahim@gmail.com',
                        'Staf (Roni Rivandra)' => 'ronirivandra@gmail.com',
                    ]),
            ]);
    }
}
