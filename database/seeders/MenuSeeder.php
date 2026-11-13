<?php

namespace Database\Seeders;

use App\Models\Menu;
use Datlechin\FilamentMenuBuilder\MenuBuilder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $definitions = [
                [
                    'name' => 'Keuangan',
                    'icon' => 'heroicon-o-banknotes',
                    'sort' => 1,
                    'locations' => ['sidebar'],
                    'items' => [
                        ['title' => 'Pengajuan & SPJ Belanja', 'url' => '/admin/pengajuan-spjs'],
                        ['title' => 'DPA / Sub Kegiatan (RKA)', 'url' => '/admin/master-sub-kegiatans'],
                        [
                            'title' => 'Master Keuangan',
                            'type' => 'text',
                            'children' => [
                                ['title' => 'Program', 'url' => '/admin/master-programs'],
                                ['title' => 'Kegiatan', 'url' => '/admin/master-kegiatans'],
                                ['title' => 'Kode Rekening Belanja', 'url' => '/admin/kode-rekenings'],
                                ['title' => 'Komponen Belanja', 'url' => '/admin/master-barangs'],
                                ['title' => 'Data Toko / Rekanan', 'url' => '/admin/master-tokos'],
                                ['title' => 'Standar Kuota BBM', 'url' => '/admin/master-tujuan-bbms'],
                                ['title' => 'Syarat Dokumen SPJ', 'url' => '/admin/master-syarat-belanjas'],
                                ['title' => 'Pengaturan Keuangan', 'url' => '/admin/pengaturan-keuangan'],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Absensi',
                    'icon' => 'heroicon-o-calendar-days',
                    'sort' => 2,
                    'locations' => ['sidebar'],
                    'items' => [
                        ['title' => 'Data Absensi', 'url' => '/admin/absensi-harians'],
                        ['title' => 'Log Mesin & Mobile', 'url' => '/admin/log-absensis'],
                        ['title' => 'Pengajuan Izin & Cuti', 'url' => '/admin/pengajuan-izins'],
                        ['title' => 'Pengaturan Absen', 'url' => '/admin/pengaturan-absensi'],
                    ],
                ],
                [
                    'name' => 'LHK',
                    'icon' => 'heroicon-o-clipboard-document-list',
                    'sort' => 3,
                    'locations' => ['sidebar'],
                    'items' => [
                        ['title' => 'Laporan LHK', 'url' => '/admin/lhks'],
                        [
                            'title' => 'Master & Penilaian LHK',
                            'type' => 'text',
                            'children' => [
                                ['title' => 'Master Aktivitas', 'url' => '/admin/aktivitas'],
                                ['title' => 'Data Rencana Aksi', 'url' => '/admin/rencana-aksis', 'is_visible' => false],
                                ['title' => 'Reputasi', 'url' => '/admin/reputasis', 'is_visible' => false],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Kepegawaian',
                    'icon' => 'heroicon-o-user-group',
                    'sort' => 4,
                    'locations' => ['sidebar'],
                    'items' => [
                        ['title' => 'Data Pegawai', 'url' => '/admin/users'],
                        [
                            'title' => 'Master Kepegawaian',
                            'type' => 'text',
                            'children' => [
                                ['title' => 'Profil Instansi', 'url' => '/admin/instansis'],
                                ['title' => 'Bidang', 'url' => '/admin/bidangs'],
                                ['title' => 'Jabatan', 'url' => '/admin/jabatans'],
                                ['title' => 'Jadwal / Shift Kerja', 'url' => '/admin/jadwal-kerjas'],
                                ['title' => 'Hari Libur & Cuti Bersama', 'url' => '/admin/hari-liburs'],
                                ['title' => 'Master Jenis Izin & Cuti', 'url' => '/admin/jenis-izins'],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Pengaturan',
                    'icon' => 'heroicon-o-cog-6-tooth',
                    'sort' => 5,
                    'locations' => ['sidebar'],
                    'items' => [
                        ['title' => 'Konfigurasi Sistem', 'url' => '/admin/pengaturan-aplikasis'],
                        ['title' => 'Mesin Presensi (ZKTeco)', 'url' => '/admin/mesin-absensis'],
                        ['title' => 'Peran & Hak Akses', 'url' => '/admin/shield/roles'],
                        ['title' => 'Log Aktivitas', 'url' => '/admin/activity-logs'],
                        ['title' => 'Backup & Restore SQL', 'url' => '/admin/backup-database'],
                        ['title' => 'Link Portal & Fitur', 'url' => '/admin/portal-links', 'is_visible' => false],
                    ],
                ],
                [
                    'name' => 'Menu Builder',
                    'icon' => 'heroicon-o-bars-3',
                    'sort' => 6,
                    'locations' => ['sidebar'],
                    'items' => [],
                ],
            ];

            $itemModel = MenuBuilder::menuItemModel();

            Menu::withoutEvents(function () use ($definitions, $itemModel): void {
                foreach ($definitions as $def) {
                    $menu = Menu::query()->updateOrCreate(
                        ['name' => $def['name']],
                        [
                            'icon' => $def['icon'],
                            'sort' => $def['sort'],
                            'is_visible' => true,
                        ],
                    );

                    $menu->syncLocations($def['locations']);

                    // Bersihkan item lama pada menu ini agar urutan & penamaan bersih
                    $itemModel::query()->where('menu_id', $menu->getKey())->whereNotNull('parent_id')->delete();
                    $itemModel::query()->where('menu_id', $menu->getKey())->delete();

                    $this->seedItems($itemModel, $menu->getKey(), $def['items']);
                }
            });
        });
    }

    /**
     * @param  class-string<\Datlechin\FilamentMenuBuilder\Models\MenuItem>  $itemModel
     * @param  array<int, array<string, mixed>>  $items
     */
    private function seedItems(string $itemModel, int $menuId, array $items, ?int $parentId = null): void
    {
        foreach (array_values($items) as $index => $item) {
            $type = $item['type'] ?? 'resource';

            $record = $itemModel::query()->create([
                'menu_id' => $menuId,
                'parent_id' => $parentId,
                'type' => $type,
                'title' => $item['title'],
                'url' => $type === 'text' ? null : ($item['url'] ?? null),
                'is_visible' => $item['is_visible'] ?? true,
                'order' => $index + 1,
            ]);

            if (! empty($item['children']) && is_array($item['children'])) {
                $this->seedItems($itemModel, $menuId, $item['children'], $record->getKey());
            }
        }
    }
}

