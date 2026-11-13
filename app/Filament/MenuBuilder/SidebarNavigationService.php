<?php

declare(strict_types=1);

namespace App\Filament\MenuBuilder;

use App\Filament\Pages\BackupDatabase;
use App\Filament\Pages\Dashboard as AppDashboard;
use App\Filament\Pages\LaporanFungsional;
use App\Filament\Pages\LaporanLrfk;
use App\Filament\Pages\PengaturanAbsensi;
use App\Filament\Pages\PengaturanKeuangan;
use App\Filament\Pages\ProfilPegawai;
use App\Filament\Resources\AbsensiHarians\AbsensiHarianResource;
use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Filament\Resources\Aktivitas\AktivitasResource;
use App\Filament\Resources\Bidangs\BidangResource;
use App\Filament\Resources\HariLiburs\HariLiburResource;
use App\Filament\Resources\Instansis\InstansiResource;
use App\Filament\Resources\Jabatans\JabatanResource;
use App\Filament\Resources\JadwalKerjas\JadwalKerjaResource;
use App\Filament\Resources\JenisIzins\JenisIzinResource;
use App\Filament\Resources\KodeRekenings\KodeRekeningResource;
use App\Filament\Resources\Lhks\LhkResource;
use App\Filament\Resources\LogAbsensis\LogAbsensiResource;
use App\Filament\Resources\MasterBarangs\MasterBarangResource;
use App\Filament\Resources\MasterJenisBelanjas\MasterJenisBelanjaResource;
use App\Filament\Resources\MasterKegiatans\MasterKegiatanResource;
use App\Filament\Resources\MasterPrograms\MasterProgramResource;
use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Filament\Resources\MasterTokos\MasterTokoResource;
use App\Filament\Resources\MasterTujuanBbms\MasterTujuanBbmResource;
use App\Filament\Resources\MesinAbsensis\MesinAbsensiResource;
use App\Filament\Resources\PengajuanIzins\PengajuanIzinResource;
use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Filament\Resources\PengaturanAplikasis\PengaturanAplikasiResource;
use App\Filament\Resources\PortalLinks\PortalLinkResource;
use App\Filament\Resources\RencanaAksis\RencanaAksiResource;
use App\Filament\Resources\Reputasis\ReputasiResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Menu;
use BezhanSalleh\FilamentShield\Resources\Roles\RoleResource;
use Datlechin\FilamentMenuBuilder\Models\MenuItem;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Support\Facades\Schema;

class SidebarNavigationService
{
    public static function build(NavigationBuilder $builder): NavigationBuilder
    {
        $topItems = [
            NavigationItem::make('Dasbor')
                ->url(fn (): string => AppDashboard::getUrl())
                ->icon('heroicon-o-home')
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.dashboard'))
                ->sort(1),
            NavigationItem::make('Presensi Mobile')
                ->url(fn (): string => route('mobile.dashboard'), shouldOpenInNewTab: true)
                ->icon('heroicon-o-device-phone-mobile')
                ->sort(2),
            NavigationItem::make('Profil Pegawai')
                ->url(fn (): string => ProfilPegawai::getUrl())
                ->icon('heroicon-o-user-circle')
                ->visible(fn (): bool => ProfilPegawai::canAccess())
                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.profil-pegawai'))
                ->sort(3),
        ];

        if (! Schema::hasTable('menus') || ! Schema::hasTable('menu_items')) {
            return $builder->items($topItems);
        }

        $menus = Menu::query()
            ->where('is_visible', true)
            ->whereHas('locations', fn ($query) => $query->where('location', 'sidebar'))
            ->with([
                'items' => fn ($query) => $query->where('is_visible', true)->orderBy('order')->orderBy('id'),
            ])
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        $groups = [];
        $hasMenuBuilderLink = false;

        foreach ($menus as $menu) {
            if ($menu->items->isEmpty() && strcasecmp((string) $menu->name, 'Menu Builder') === 0 && CustomMenuResource::canAccess()) {
                $groups[] = NavigationGroup::make()->items([
                    NavigationItem::make($menu->translated('name'))
                        ->url(fn (): string => CustomMenuResource::getUrl())
                        ->icon($menu->icon ?: 'heroicon-o-bars-3')
                        ->isActiveWhen(fn (): bool => request()->is('admin/menus*')),
                ]);
                $hasMenuBuilderLink = true;
                continue;
            }

            $tree = $menu->items->toTree();
            $navItems = [];

            foreach ($tree as $item) {
                if (self::isMenuBuilderItem($item)) {
                    $hasMenuBuilderLink = true;
                }

                $navItem = self::convertItem($item, $hasMenuBuilderLink);
                if ($navItem !== null) {
                    $navItems[] = $navItem->sort(count($navItems) + 1);
                }
            }

            if ($navItems !== []) {
                $groups[] = NavigationGroup::make($menu->translated('name'))
                    ->icon($menu->icon ?: 'heroicon-o-folder')
                    ->items($navItems)
                    ->collapsed();
            }
        }

        if ($menus->isEmpty() && ! $hasMenuBuilderLink && CustomMenuResource::canAccess()) {
            $topItems[] = NavigationItem::make('Menu Builder')
                ->url(fn (): string => CustomMenuResource::getUrl())
                ->icon('heroicon-o-bars-3')
                ->isActiveWhen(fn (): bool => request()->is('admin/menus*'))
                ->sort(count($topItems) + 1);
        }

        return $builder->items($topItems)->groups($groups);
    }

    /**
     * @return array<int, array{name: string, icon: ?string, items: array<int, array<string, mixed>>}>
     */
    public static function getHeaderMenus(): array
    {
        if (! Schema::hasTable('menus') || ! Schema::hasTable('menu_items')) {
            return [];
        }

        $menus = Menu::query()
            ->where('is_visible', true)
            ->whereHas('locations', fn ($query) => $query->where('location', 'header'))
            ->with([
                'items' => fn ($query) => $query->where('is_visible', true)->orderBy('order')->orderBy('id'),
            ])
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        $result = [];

        foreach ($menus as $menu) {
            $items = self::mapHeaderItems($menu->items->toTree());
            if ($items !== []) {
                $result[] = [
                    'name' => $menu->translated('name'),
                    'icon' => $menu->icon,
                    'items' => $items,
                ];
            }
        }

        return $result;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, MenuItem>  $tree
     * @return array<int, array<string, mixed>>
     */
    private static function mapHeaderItems($tree): array
    {
        $mapped = [];

        foreach ($tree as $item) {
            if (! self::canAccessItem($item)) {
                continue;
            }

            $children = self::mapHeaderItems($item->children);
            $url = $item->getUrl();

            if ($item->type === 'text' && $children === []) {
                continue;
            }

            $mapped[] = [
                'title' => $item->translated('title'),
                'url' => $url,
                'icon' => $item->icon,
                'target' => $item->target,
                'active' => filled($url) && self::isUrlActive($url),
                'children' => $children,
            ];
        }

        return $mapped;
    }

    private static function convertItem(MenuItem $item, bool &$hasMenuBuilderLink): ?NavigationItem
    {
        if (! self::canAccessItem($item)) {
            return null;
        }

        $childNavItems = [];
        foreach ($item->children as $child) {
            if (! $child->is_visible) {
                continue;
            }

            if (self::isMenuBuilderItem($child)) {
                $hasMenuBuilderLink = true;
            }

            $convertedChild = self::convertItem($child, $hasMenuBuilderLink);
            if ($convertedChild !== null) {
                $childNavItems[] = $convertedChild->sort(count($childNavItems) + 1);
            }
        }

        $url = $item->getUrl();

        if ($item->type === 'text' && $childNavItems === []) {
            return null;
        }

        $navItem = NavigationItem::make($item->translated('title'))
            ->openUrlInNewTab($item->target === '_blank');

        if (filled($url)) {
            $navItem->url($url);
        }

        if ($childNavItems !== []) {
            $navItem
                ->icon($item->icon ?: 'heroicon-o-folder')
                ->childItems($childNavItems);
        } elseif (filled($item->icon)) {
            $navItem->icon($item->icon);
        }

        $navItem->isActiveWhen(function () use ($url, $childNavItems): bool {
            if (filled($url) && self::isUrlActive($url)) {
                return true;
            }

            foreach ($childNavItems as $childNavItem) {
                if ($childNavItem->isActive() || $childNavItem->isChildItemsActive()) {
                    return true;
                }
            }

            return false;
        });

        return $navItem;
    }

    private static function canAccessItem(MenuItem $item): bool
    {
        $targetClass = $item->data['target_class'] ?? self::resolveTargetClassFromUrl($item->getUrl());

        if (is_string($targetClass) && class_exists($targetClass)) {
            if (method_exists($targetClass, 'canAccess')) {
                return (bool) $targetClass::canAccess();
            }

            if (method_exists($targetClass, 'canViewAny')) {
                return (bool) $targetClass::canViewAny();
            }
        }

        return true;
    }

    private static function resolveTargetClassFromUrl(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        static $pathToClass = null;

        if ($pathToClass === null) {
            $pathToClass = [];
            foreach (ResourceItemType::getCatalog() as $class => $meta) {
                $normalized = '/' . trim((string) (parse_url($meta['path'] ?? '', PHP_URL_PATH) ?: ''), '/');
                if ($normalized !== '/') {
                    $pathToClass[$normalized] = $class;
                }
            }
        }

        $path = '/' . trim((string) (parse_url($url, PHP_URL_PATH) ?: $url), '/');

        return $pathToClass[$path] ?? null;
    }

    private static function isMenuBuilderItem(MenuItem $item): bool
    {
        $targetClass = $item->data['target_class'] ?? null;
        if ($targetClass === CustomMenuResource::class || $targetClass === \Datlechin\FilamentMenuBuilder\Resources\MenuResource::class) {
            return true;
        }

        $url = $item->getUrl() ?? '';
        $path = parse_url($url, PHP_URL_PATH) ?: $url;

        return rtrim($path, '/') === '/admin/menus';
    }

    private static function isUrlActive(string $url): bool
    {
        $path = trim((string) (parse_url($url, PHP_URL_PATH) ?: $url), '/');

        if ($path === '' || $path === 'admin') {
            return request()->is('admin');
        }

        return request()->is($path) || request()->is($path . '/*');
    }

    public static function seedDefaultMenus(): void
    {
        $structure = [
            [
                'name' => 'Kepegawaian',
                'icon' => 'heroicon-o-user-group',
                'sort' => 1,
                'items' => [
                    ['class' => UserResource::class],
                    [
                        'title' => 'Master Kepegawaian',
                        'icon' => 'heroicon-o-folder',
                        'children' => [
                            ['class' => InstansiResource::class],
                            ['class' => BidangResource::class],
                            ['class' => JabatanResource::class],
                            ['class' => JadwalKerjaResource::class],
                            ['class' => HariLiburResource::class],
                            ['class' => JenisIzinResource::class],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Keuangan',
                'icon' => 'heroicon-o-banknotes',
                'sort' => 2,
                'items' => [
                    ['class' => PengajuanSpjResource::class],
                    ['class' => MasterSubKegiatanResource::class],
                    [
                        'title' => 'Master Keuangan',
                        'icon' => 'heroicon-o-archive-box',
                        'children' => [
                            ['class' => MasterProgramResource::class],
                            ['class' => MasterKegiatanResource::class],
                            ['class' => KodeRekeningResource::class],
                            ['class' => MasterBarangResource::class],
                            ['class' => MasterTokoResource::class],
                            ['class' => MasterTujuanBbmResource::class],
                            ['class' => MasterJenisBelanjaResource::class],
                        ],
                    ],
                    [
                        'title' => 'Laporan Keuangan',
                        'icon' => 'heroicon-o-document-chart-bar',
                        'children' => [
                            ['class' => LaporanLrfk::class],
                            ['class' => LaporanFungsional::class],
                        ],
                    ],
                    ['class' => PengaturanKeuangan::class],
                ],
            ],
            [
                'name' => 'Absensi',
                'icon' => 'heroicon-o-calendar-days',
                'sort' => 3,
                'items' => [
                    ['class' => AbsensiHarianResource::class],
                    ['class' => LogAbsensiResource::class],
                    ['class' => PengajuanIzinResource::class],
                    ['class' => PengaturanAbsensi::class],
                ],
            ],
            [
                'name' => 'LHK',
                'icon' => 'heroicon-o-clipboard-document-list',
                'sort' => 4,
                'items' => [
                    ['class' => LhkResource::class],
                    [
                        'title' => 'Master & Penilaian LHK',
                        'icon' => 'heroicon-o-folder',
                        'children' => [
                            ['class' => RencanaAksiResource::class],
                            ['class' => AktivitasResource::class],
                            ['class' => ReputasiResource::class],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Pengaturan',
                'icon' => 'heroicon-o-cog-6-tooth',
                'sort' => 5,
                'items' => [
                    ['class' => PengaturanAplikasiResource::class],
                    ['class' => MesinAbsensiResource::class],
                    ['class' => PortalLinkResource::class],
                    ['class' => ActivityLogResource::class],
                    ['class' => CustomMenuResource::class],
                    ['class' => BackupDatabase::class],
                    ['class' => RoleResource::class],
                ],
            ],
        ];

        $catalog = ResourceItemType::getCatalog();

        foreach ($structure as $menuData) {
            /** @var Menu $menu */
            $menu = Menu::query()->updateOrCreate(
                ['name' => $menuData['name']],
                [
                    'icon' => $menuData['icon'],
                    'sort' => $menuData['sort'],
                    'is_visible' => true,
                ]
            );

            $menu->syncLocations(['sidebar']);

            if ($menu->items()->count() > 0) {
                continue;
            }

            self::seedItemsForMenu($menu, $menuData['items'], null, $catalog);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<class-string, array{label: string, group: string, path: string, icon: ?string, sort: int}>  $catalog
     */
    private static function seedItemsForMenu(Menu $menu, array $items, ?int $parentId, array $catalog): void
    {
        $order = 1;

        foreach ($items as $entry) {
            if (isset($entry['class'])) {
                $class = $entry['class'];
                $meta = $catalog[$class] ?? null;

                if (! $meta) {
                    continue;
                }

                $menu->items()->create([
                    'parent_id' => $parentId,
                    'type' => 'resource',
                    'title' => $meta['label'],
                    'url' => $meta['path'],
                    'icon' => $meta['icon'],
                    'data' => ['target_class' => $class],
                    'is_visible' => true,
                    'order' => $order++,
                ]);

                continue;
            }

            /** @var MenuItem $folder */
            $folder = $menu->items()->create([
                'parent_id' => $parentId,
                'type' => 'text',
                'title' => $entry['title'],
                'icon' => $entry['icon'] ?? 'heroicon-o-folder',
                'is_visible' => true,
                'order' => $order++,
            ]);

            if (! empty($entry['children'])) {
                self::seedItemsForMenu($menu, $entry['children'], (int) $folder->getKey(), $catalog);
            }
        }
    }
}

