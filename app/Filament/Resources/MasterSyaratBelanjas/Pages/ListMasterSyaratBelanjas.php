<?php

namespace App\Filament\Resources\MasterSyaratBelanjas\Pages;

use App\Filament\Resources\MasterSyaratBelanjas\MasterSyaratBelanjaResource;
use App\Models\MasterSyaratBelanja;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ListMasterSyaratBelanjas extends ListRecords
{
    protected static string $resource = MasterSyaratBelanjaResource::class;

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getShieldTabs(),
            ]);
    }

    public function getShieldTabs(): Tabs
    {
        $categories = $this->getCategoriesDefinition();

        $nonKatalogSections = [];
        $eKatalogSections = [];
        $umumSections = [];

        foreach ($categories as $catKey => $cat) {
            $query = MasterSyaratBelanja::query();
            ($cat['query'])($query);
            $items = $query->orderBy('urutan')->get();

            $headerActions = [
                Action::make("btn_add_{$catKey}")
                    ->label('+ Tambah Syarat')
                    ->icon('heroicon-m-plus')
                    ->color('primary')
                    ->size(Size::Small)
                    ->modalHeading("Tambah Syarat: {$cat['label']}")
                    ->modalDescription('Tambahkan berkas persyaratan baru yang wajib/opsional diunggah staf.')
                    ->modalWidth(\Filament\Support\Enums\Width::Large)
                    ->form([
                        TextInput::make('nama_syarat')
                            ->label('Nama Dokumen Persyaratan')
                            ->placeholder('Contoh: Struk SPBU Asli / Foto Odometer')
                            ->required()
                            ->maxLength(200),

                        Textarea::make('keterangan')
                            ->label('Rincian / Keterangan Dokumen')
                            ->placeholder('Penjelasan rincian berkas yang wajib diunggah...')
                            ->rows(2),

                        Toggle::make('is_required')
                            ->label('Wajib Diunggah')
                            ->helperText('Jika aktif, staf wajib mengunggah dokumen ini.')
                            ->default(true),

                        Toggle::make('aktif')
                            ->label('Status Aktif')
                            ->default(true),
                    ])
                    ->action(function (array $data) use ($cat) {
                        $maxUrutan = MasterSyaratBelanja::where('jenis_belanja', $cat['jenis_belanja'])
                            ->where('metode_pengadaan', $cat['metode'])
                            ->max('urutan') ?? 0;

                        MasterSyaratBelanja::create([
                            'jenis_belanja' => $cat['jenis_belanja'],
                            'metode_pengadaan' => $cat['metode'],
                            'nama_syarat' => trim($data['nama_syarat']),
                            'keterangan' => ! empty($data['keterangan']) ? trim($data['keterangan']) : null,
                            'is_required' => (bool) ($data['is_required'] ?? true),
                            'aktif' => (bool) ($data['aktif'] ?? true),
                            'urutan' => $maxUrutan + 1,
                        ]);

                        Notification::make()
                            ->title('Syarat Dokumen Ditambahkan')
                            ->body("Dokumen \"{$data['nama_syarat']}\" berhasil ditambahkan ke {$cat['label']}.")
                            ->success()
                            ->send();
                    }),
            ];

            if ($cat['is_custom'] ?? false) {
                $headerActions[] = Action::make("btn_del_job_{$catKey}")
                    ->label('Hapus Pekerjaan')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->size(Size::Small)
                    ->requiresConfirmation()
                    ->modalHeading("Hapus Jenis Pekerjaan {$cat['label']}?")
                    ->modalDescription('Seluruh syarat dokumen pada pekerjaan ini akan dihapus secara permanen.')
                    ->action(function () use ($cat) {
                        MasterSyaratBelanja::where('jenis_belanja', $cat['jenis_belanja'])
                            ->where('metode_pengadaan', $cat['metode'])
                            ->delete();

                        Notification::make()
                            ->title('Jenis Pekerjaan Dihapus')
                            ->body("Pekerjaan {$cat['label']} berhasil dihapus.")
                            ->success()
                            ->send();
                    });
            }

            $isFirstOpen = ($catKey === 'barang_jasa' || $catKey === 'e_katalog');
            $syaratComponents = [];

            if ($items->isEmpty()) {
                $syaratComponents[] = Placeholder::make("empty_{$catKey}")
                    ->hiddenLabel()
                    ->content(new HtmlString('<div class="py-4 text-center text-sm text-gray-500 italic">Belum ada syarat dokumen pada pekerjaan ini. Klik tombol <strong>+ Tambah Syarat</strong> di atas untuk menambahkan dokumen.</div>'));
            } else {
                $totalCount = $items->count();

                foreach ($items as $idx => $item) {
                    $loopIndex = $idx + 1;
                    $isFirst = ($loopIndex === 1);
                    $isLast = ($loopIndex === $totalCount);

                    $syaratComponents[] = Grid::make(['default' => 12])
                        ->extraAttributes([
                            'class' => 'items-center py-2.5 px-3 rounded-lg border border-gray-100 dark:border-gray-800 bg-white/50 dark:bg-gray-900/40 mb-2 hover:bg-gray-50 dark:hover:bg-gray-800/40 transition',
                        ])
                        ->schema([
                            Placeholder::make("info_{$item->id}")
                                ->hiddenLabel()
                                ->content(function () use ($item) {
                                    $badgeWajib = $item->is_required
                                        ? '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300 font-mono">WAJIB</span>'
                                        : '<span class="px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 font-mono">OPSIONAL</span>';

                                    $badgeAktif = $item->aktif
                                        ? '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300 font-mono">AKTIF</span>'
                                        : '<span class="px-2 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300 font-mono">NONAKTIF</span>';

                                    $keterangan = $item->keterangan
                                        ? "<div class=\"text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed\">{$item->keterangan}</div>"
                                        : '';

                                    return new HtmlString(
                                        "<div class=\"flex flex-col\">
                                            <div class=\"flex items-center gap-2 flex-wrap\">
                                                <span class=\"font-mono font-bold text-xs text-primary-600 dark:text-primary-400 w-6\">#{$item->urutan}</span>
                                                <span class=\"font-semibold text-sm text-gray-900 dark:text-gray-100\">{$item->nama_syarat}</span>
                                                {$badgeWajib}
                                                {$badgeAktif}
                                            </div>
                                            {$keterangan}
                                        </div>"
                                    );
                                })
                                ->columnSpan(['default' => 12, 'md' => 8]),

                            Actions::make([
                                Action::make("up_{$item->id}")
                                    ->label('Naik')
                                    ->icon('heroicon-m-arrow-up')
                                    ->size(Size::ExtraSmall)
                                    ->color('gray')
                                    ->disabled($isFirst)
                                    ->tooltip('Geser Urutan Naik')
                                    ->action(fn () => $this->swapUrutan($item->id, 'up')),

                                Action::make("down_{$item->id}")
                                    ->label('Turun')
                                    ->icon('heroicon-m-arrow-down')
                                    ->size(Size::ExtraSmall)
                                    ->color('gray')
                                    ->disabled($isLast)
                                    ->tooltip('Geser Urutan Turun')
                                    ->action(fn () => $this->swapUrutan($item->id, 'down')),

                                Action::make("edit_btn_{$item->id}")
                                    ->label('Edit')
                                    ->icon('heroicon-m-pencil-square')
                                    ->size(Size::ExtraSmall)
                                    ->color('warning')
                                    ->modalHeading("Edit Syarat: {$item->nama_syarat}")
                                    ->form([
                                        TextInput::make('nama_syarat')
                                            ->label('Nama Dokumen Persyaratan')
                                            ->default($item->nama_syarat)
                                            ->required(),

                                        Textarea::make('keterangan')
                                            ->label('Rincian / Keterangan Dokumen')
                                            ->default($item->keterangan)
                                            ->rows(2),

                                        Toggle::make('is_required')
                                            ->label('Wajib Diunggah')
                                            ->default((bool) $item->is_required),

                                        Toggle::make('aktif')
                                            ->label('Status Aktif')
                                            ->default((bool) $item->aktif),
                                    ])
                                    ->action(function (array $data) use ($item) {
                                        $item->update([
                                            'nama_syarat' => trim($data['nama_syarat']),
                                            'keterangan' => ! empty($data['keterangan']) ? trim($data['keterangan']) : null,
                                            'is_required' => (bool) ($data['is_required'] ?? true),
                                            'aktif' => (bool) ($data['aktif'] ?? true),
                                        ]);

                                        Notification::make()
                                            ->title('Syarat Berhasil Diperbarui')
                                            ->success()
                                            ->send();
                                    }),

                                Action::make("del_btn_{$item->id}")
                                    ->label('Hapus')
                                    ->icon('heroicon-m-trash')
                                    ->size(Size::ExtraSmall)
                                    ->color('danger')
                                    ->requiresConfirmation()
                                    ->modalHeading("Hapus \"{$item->nama_syarat}\"?")
                                    ->modalDescription('Dokumen persyaratan ini akan dihapus dari SOP pekerjaan ini.')
                                    ->action(function () use ($item) {
                                        $item->delete();

                                        Notification::make()
                                            ->title('Syarat Berhasil Dihapus')
                                            ->success()
                                            ->send();
                                    }),
                            ])
                            ->columnSpan(['default' => 12, 'md' => 4])
                            ->alignEnd(),
                        ]);
                }
            }

            $section = Section::make(fn () => "{$cat['label']} (" . count($items) . " Syarat)")
                ->description($cat['description'])
                ->compact()
                ->collapsible()
                ->collapsed(! $isFirstOpen)
                ->headerActions($headerActions)
                ->schema($syaratComponents);

            if ($catKey === 'semua') {
                $umumSections[] = $section;
            } elseif (($cat['metode'] ?? 'non_katalog') === 'e_katalog') {
                $eKatalogSections[] = $section;
            } else {
                $nonKatalogSections[] = $section;
            }
        }

        return Tabs::make('Pengelompokan Dokumen SPJ')
            ->contained()
            ->extraAttributes([
                'class' => 'syarat-accordion-tabs',
            ])
            ->tabs([
                Tab::make('Non-Katalog (Pengadaan Langsung)')
                    ->icon('heroicon-o-building-storefront')
                    ->badge(fn () => count($nonKatalogSections) . ' Pekerjaan')
                    ->badgeColor('primary')
                    ->schema($nonKatalogSections),

                Tab::make('E-Katalog LKPP (E-Purchasing)')
                    ->icon('heroicon-o-shopping-bag')
                    ->badge(fn () => count($eKatalogSections) . ' Paket')
                    ->badgeColor('warning')
                    ->schema($eKatalogSections),

                Tab::make('Dokumen Umum / Lampiran Tambahan')
                    ->icon('heroicon-o-document-duplicate')
                    ->badge(fn () => count($umumSections) . ' Grup')
                    ->badgeColor('gray')
                    ->schema($umumSections),
            ]);
    }

    public function swapUrutan(int $id, string $direction): void
    {
        $item = MasterSyaratBelanja::find($id);
        if (! $item) {
            return;
        }

        $query = MasterSyaratBelanja::query()
            ->where('jenis_belanja', $item->jenis_belanja)
            ->where('metode_pengadaan', $item->metode_pengadaan);

        if ($direction === 'up') {
            $neighbor = (clone $query)->where('urutan', '<', $item->urutan)->orderByDesc('urutan')->first();
        } else {
            $neighbor = (clone $query)->where('urutan', '>', $item->urutan)->orderBy('urutan')->first();
        }

        if ($neighbor) {
            $oldUrutan = $item->urutan;
            $item->urutan = $neighbor->urutan;
            $neighbor->urutan = $oldUrutan;
            $item->save();
            $neighbor->save();

            Notification::make()
                ->title('Urutan Berhasil Diubah')
                ->success()
                ->send();
        }
    }

    public function tambahSyaratAction(): Action
    {
        return Action::make('tambahSyarat')
            ->label('+ Tambah Syarat')
            ->icon('heroicon-m-plus')
            ->color('primary')
            ->modalHeading(fn (array $arguments) => 'Tambah Syarat Dokumen: ' . ($arguments['label'] ?? 'Pekerjaan'))
            ->modalDescription('Tambahkan syarat berkas SOP baru yang wajib/opsional diunggah staf.')
            ->modalWidth(\Filament\Support\Enums\Width::Large)
            ->form([
                Hidden::make('jenis_belanja'),
                Hidden::make('metode_pengadaan'),
                TextInput::make('nama_syarat')
                    ->label('Nama Dokumen Persyaratan')
                    ->placeholder('Contoh: Struk SPBU Asli / Foto Odometer')
                    ->required()
                    ->maxLength(200),
                Textarea::make('keterangan')
                    ->label('Rincian / Keterangan Dokumen')
                    ->placeholder('Penjelasan rincian berkas yang wajib diunggah...')
                    ->rows(2),
                Toggle::make('is_required')
                    ->label('Wajib Diunggah')
                    ->helperText('Jika aktif, staf wajib mengunggah dokumen ini.')
                    ->default(true),
                Toggle::make('aktif')
                    ->label('Status Aktif')
                    ->default(true),
            ])
            ->fillForm(fn (array $arguments) => [
                'jenis_belanja' => $arguments['jenis_belanja'] ?? 'barang_jasa',
                'metode_pengadaan' => $arguments['metode_pengadaan'] ?? 'non_katalog',
                'is_required' => true,
                'aktif' => true,
            ])
            ->action(function (array $data) {
                $maxUrutan = MasterSyaratBelanja::where('jenis_belanja', $data['jenis_belanja'])
                    ->where('metode_pengadaan', $data['metode_pengadaan'])
                    ->max('urutan') ?? 0;

                MasterSyaratBelanja::create([
                    'jenis_belanja' => $data['jenis_belanja'],
                    'metode_pengadaan' => $data['metode_pengadaan'],
                    'nama_syarat' => trim($data['nama_syarat']),
                    'keterangan' => ! empty($data['keterangan']) ? trim($data['keterangan']) : null,
                    'is_required' => (bool) ($data['is_required'] ?? true),
                    'aktif' => (bool) ($data['aktif'] ?? true),
                    'urutan' => $maxUrutan + 1,
                ]);

                Notification::make()
                    ->title('Syarat Dokumen Ditambahkan')
                    ->body("Dokumen \"{$data['nama_syarat']}\" berhasil ditambahkan.")
                    ->success()
                    ->send();
            });
    }

    public function editSyaratAction(): Action
    {
        return Action::make('editSyarat')
            ->label('Edit Syarat')
            ->icon('heroicon-m-pencil-square')
            ->color('warning')
            ->modalHeading(fn (array $arguments) => 'Edit Syarat: ' . (MasterSyaratBelanja::find($arguments['id'] ?? null)?->nama_syarat ?? ''))
            ->modalWidth(\Filament\Support\Enums\Width::Large)
            ->form([
                Hidden::make('id'),
                TextInput::make('nama_syarat')
                    ->label('Nama Dokumen Persyaratan')
                    ->required()
                    ->maxLength(200),
                Textarea::make('keterangan')
                    ->label('Rincian / Keterangan Dokumen')
                    ->rows(2),
                Toggle::make('is_required')
                    ->label('Wajib Diunggah'),
                Toggle::make('aktif')
                    ->label('Status Aktif'),
            ])
            ->fillForm(function (array $arguments) {
                $record = MasterSyaratBelanja::find($arguments['id'] ?? null);

                return [
                    'id' => $record?->id,
                    'nama_syarat' => $record?->nama_syarat,
                    'keterangan' => $record?->keterangan,
                    'is_required' => (bool) $record?->is_required,
                    'aktif' => (bool) $record?->aktif,
                ];
            })
            ->action(function (array $data) {
                $record = MasterSyaratBelanja::find($data['id']);
                if ($record) {
                    $record->update([
                        'nama_syarat' => trim($data['nama_syarat']),
                        'keterangan' => ! empty($data['keterangan']) ? trim($data['keterangan']) : null,
                        'is_required' => (bool) ($data['is_required'] ?? true),
                        'aktif' => (bool) ($data['aktif'] ?? true),
                    ]);

                    Notification::make()
                        ->title('Syarat Berhasil Diperbarui')
                        ->success()
                        ->send();
                }
            });
    }

    public function hapusSyaratAction(): Action
    {
        return Action::make('hapusSyarat')
            ->label('Hapus Syarat')
            ->icon('heroicon-m-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(fn (array $arguments) => 'Hapus "' . (MasterSyaratBelanja::find($arguments['id'] ?? null)?->nama_syarat ?? 'Syarat') . '"?')
            ->modalDescription('Dokumen persyaratan ini akan dihapus dari SOP pekerjaan ini.')
            ->form([
                Hidden::make('id'),
            ])
            ->fillForm(fn (array $arguments) => [
                'id' => $arguments['id'] ?? null,
            ])
            ->action(function (array $data, array $arguments) {
                $id = $data['id'] ?? $arguments['id'] ?? null;
                if ($id) {
                    MasterSyaratBelanja::where('id', $id)->delete();

                    Notification::make()
                        ->title('Syarat Berhasil Dihapus')
                        ->success()
                        ->send();
                }
            });
    }

    public function hapusPekerjaanAction(): Action
    {
        return Action::make('hapusPekerjaan')
            ->label('Hapus Pekerjaan')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(fn (array $arguments) => 'Hapus Jenis Pekerjaan ' . ($arguments['label'] ?? '') . '?')
            ->modalDescription('Seluruh syarat dokumen pada pekerjaan ini akan dihapus secara permanen.')
            ->form([
                Hidden::make('jenis_belanja'),
                Hidden::make('metode_pengadaan'),
            ])
            ->fillForm(fn (array $arguments) => [
                'jenis_belanja' => $arguments['jenis_belanja'] ?? null,
                'metode_pengadaan' => $arguments['metode_pengadaan'] ?? null,
            ])
            ->action(function (array $data, array $arguments) {
                $jenis = $data['jenis_belanja'] ?? $arguments['jenis_belanja'] ?? null;
                $metode = $data['metode_pengadaan'] ?? $arguments['metode_pengadaan'] ?? null;

                if ($jenis && $metode) {
                    MasterSyaratBelanja::where('jenis_belanja', $jenis)
                        ->where('metode_pengadaan', $metode)
                        ->delete();

                    Notification::make()
                        ->title('Jenis Pekerjaan Dihapus')
                        ->success()
                        ->send();
                }
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('tambahPekerjaan')
                ->label('+ Tambah Jenis Pekerjaan')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->modalHeading('Tambah Jenis Pekerjaan & Syarat Dokumen Baru')
                ->modalDescription('Definisikan jenis pekerjaan/transaksi belanja baru beserta syarat dokumen kelengkapan pertama.')
                ->modalWidth(\Filament\Support\Enums\Width::Large)
                ->form([
                    Select::make('metode_pengadaan')
                        ->label('Metode Pengadaan')
                        ->options([
                            'non_katalog' => 'Non-Katalog (Pengadaan Langsung)',
                            'e_katalog' => 'E-Katalog (E-Purchasing LKPP)',
                        ])
                        ->default('non_katalog')
                        ->required(),

                    TextInput::make('nama_pekerjaan')
                        ->label('Nama Jenis Pekerjaan')
                        ->placeholder('Contoh: Pemeliharaan Kendaraan Dinas')
                        ->helperText('Akan menjadi pilihan transaksi saat staf membuat pengajuan SPJ.')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('syarat_pertama')
                        ->label('Nama Dokumen Persyaratan Pertama')
                        ->placeholder('Contoh: Faktur / Nota Toko Asli / Struk BBM')
                        ->default('Faktur / Nota Asli Toko')
                        ->required(),

                    Textarea::make('deskripsi')
                        ->label('Deskripsi / Petunjuk Pengajuan (Opsional)')
                        ->placeholder('Contoh: Persyaratan untuk servis rutin, ganti oli, dan perbaikan kendaraan dinas operasional.')
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $slug = Str::slug($data['nama_pekerjaan'], '_');
                    if (empty($slug)) {
                        $slug = 'pekerjaan_' . time();
                    }

                    $metode = $data['metode_pengadaan'] ?? 'non_katalog';
                    $syaratPertama = ! empty($data['syarat_pertama']) ? trim($data['syarat_pertama']) : 'Faktur / Nota Asli Toko';

                    MasterSyaratBelanja::updateOrCreate(
                        [
                            'jenis_belanja' => $slug,
                            'metode_pengadaan' => $metode,
                            'nama_syarat' => $syaratPertama,
                        ],
                        [
                            'keterangan' => ! empty($data['deskripsi']) ? trim($data['deskripsi']) : "Persyaratan dokumen untuk {$data['nama_pekerjaan']}",
                            'is_required' => true,
                            'urutan' => 1,
                            'aktif' => true,
                        ]
                    );

                    Notification::make()
                        ->title('Jenis Pekerjaan Berhasil Ditambahkan')
                        ->body("Pekerjaan \"{$data['nama_pekerjaan']}\" berhasil dibuat dengan syarat awal \"{$syaratPertama}\". Anda dapat menambahkan syarat lainnya lewat tombol + Tambah Syarat.")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function getCategoriesDefinition(): array
    {
        $categories = [
            'barang_jasa' => [
                'label' => 'Non-Katalog: Belanja Barang & Jasa',
                'description' => 'Pengadaan langsung offline kepada toko rekanan fisik untuk ATK, cetak, dan perlengkapan kantor (Nota Fisik Toko, Foto Barang, BAST Rekanan).',
                'metode' => 'non_katalog',
                'jenis_belanja' => 'barang_jasa',
                'is_custom' => false,
                'query' => fn (Builder $q) => $q->where('jenis_belanja', 'barang_jasa')->where('metode_pengadaan', 'non_katalog'),
            ],
            'makan_minum' => [
                'label' => 'Non-Katalog: Makan & Minum Rapat',
                'description' => 'Persyaratan jamuan rapat dinas offline (Nota Katering, Surat Undangan Rapat, Daftar Hadir bertandatangan, Notulensi, dan Foto Rapat).',
                'metode' => 'non_katalog',
                'jenis_belanja' => 'makan_minum',
                'is_custom' => false,
                'query' => fn (Builder $q) => $q->where('jenis_belanja', 'makan_minum')->where('metode_pengadaan', 'non_katalog'),
            ],
            'sppd' => [
                'label' => 'Non-Katalog: Perjalanan Dinas (SPPD)',
                'description' => 'Persyaratan perjalanan dinas resmi (Surat Perintah Tugas / SPT, Lembar SPPD dicap pejabat tujuan, Laporan Perjalanan, dan Tiket/Kuitansi Hotel).',
                'metode' => 'non_katalog',
                'jenis_belanja' => 'sppd',
                'is_custom' => false,
                'query' => fn (Builder $q) => $q->where('jenis_belanja', 'sppd')->where('metode_pengadaan', 'non_katalog'),
            ],
            'bbm' => [
                'label' => 'Non-Katalog: Bahan Bakar Minyak (BBM)',
                'description' => 'Persyaratan BBM kendaraan operasional dinas (Struk SPBU asli atau kupon resmi BBM sesuai kuota liter pagu, dan Foto Odometer Kendaraan).',
                'metode' => 'non_katalog',
                'jenis_belanja' => 'bbm',
                'is_custom' => false,
                'query' => fn (Builder $q) => $q->where('jenis_belanja', 'bbm')->where('metode_pengadaan', 'non_katalog'),
            ],
            'e_katalog' => [
                'label' => 'E-Katalog LKPP (E-Purchasing)',
                'description' => 'Persyaratan pengadaan melalui portal resmi E-Katalog / E-Proc LKPP. File diunggah langsung dari berkas hasil unduhan sistem E-Katalog (Surat Pesanan, BAST & Invoice E-Proc).',
                'metode' => 'e_katalog',
                'jenis_belanja' => 'semua',
                'is_custom' => false,
                'query' => fn (Builder $q) => $q->where('metode_pengadaan', 'e_katalog'),
            ],
            'semua' => [
                'label' => 'Dokumen Umum / Lampiran Tambahan',
                'description' => 'Persyaratan pendukung umum yang berlaku lintas transaksi SPJ (Surat Pernyataan Tanggung Jawab Mutlak / SPTJM, dsb.).',
                'metode' => 'non_katalog',
                'jenis_belanja' => 'semua',
                'is_custom' => false,
                'query' => fn (Builder $q) => $q->where('jenis_belanja', 'semua')->where('metode_pengadaan', '!=', 'e_katalog'),
            ],
        ];

        // Ambil jenis_belanja kustom yang ditambahkan oleh user
        $customRows = MasterSyaratBelanja::query()
            ->select('jenis_belanja', 'metode_pengadaan')
            ->whereNotIn('jenis_belanja', ['barang_jasa', 'makan_minum', 'sppd', 'bbm', 'semua'])
            ->distinct()
            ->get();

        foreach ($customRows as $row) {
            $catKey = $row->jenis_belanja;
            $namaClean = ucwords(str_replace('_', ' ', $catKey));
            $prefix = $row->metode_pengadaan === 'e_katalog' ? 'E-Katalog' : 'Non-Katalog';

            $categories[$catKey] = [
                'label' => "{$prefix}: {$namaClean}",
                'description' => "Persyaratan kelengkapan berkas untuk pekerjaan {$namaClean}.",
                'metode' => $row->metode_pengadaan,
                'jenis_belanja' => $catKey,
                'is_custom' => true,
                'query' => fn (Builder $q) => $q->where('jenis_belanja', $catKey)->where('metode_pengadaan', $row->metode_pengadaan),
            ];
        }

        return $categories;
    }
}
