<?php

namespace App\Filament\Resources\MasterSyaratBelanjas\Pages;

use App\Filament\Resources\MasterSyaratBelanjas\MasterSyaratBelanjaResource;
use App\Models\MasterSyaratBelanja;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ListMasterSyaratBelanjas extends ListRecords
{
    protected static string $resource = MasterSyaratBelanjaResource::class;

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Syarat')
                ->icon('heroicon-o-list-bullet')
                ->badge(MasterSyaratBelanja::count()),

            'non_katalog' => Tab::make('Non-Katalog (Pengadaan Langsung)')
                ->icon('heroicon-o-building-storefront')
                ->badge(MasterSyaratBelanja::where('metode_pengadaan', 'non_katalog')->count())
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('metode_pengadaan', 'non_katalog')),

            'e_katalog' => Tab::make('E-Katalog LKPP (E-Purchasing)')
                ->icon('heroicon-o-shopping-bag')
                ->badge(MasterSyaratBelanja::where('metode_pengadaan', 'e_katalog')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('metode_pengadaan', 'e_katalog')),

            'umum' => Tab::make('Dokumen Umum / Tambahan')
                ->icon('heroicon-o-document-duplicate')
                ->badge(MasterSyaratBelanja::where(fn ($q) => $q->where('jenis_belanja', 'semua')->orWhere('metode_pengadaan', 'semua'))->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where(fn ($q) => $q->where('jenis_belanja', 'semua')->orWhere('metode_pengadaan', 'semua'))),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // Aksi 1: Tambah Syarat Dokumen Satuan ke Jenis Pekerjaan yang Ada
            Action::make('tambahSyarat')
                ->label('Tambah Syarat Dokumen')
                ->icon('heroicon-o-document-plus')
                ->color('primary')
                ->modalHeading('Tambah Syarat Dokumen ke Jenis Pekerjaan')
                ->modalDescription('Tambahkan berkas persyaratan baru ke dalam SOP pekerjaan yang dipilih.')
                ->modalWidth(\Filament\Support\Enums\Width::Large)
                ->form([
                    Grid::make(2)->schema([
                        Select::make('metode_pengadaan')
                            ->label('Metode Pengadaan')
                            ->options([
                                'non_katalog' => 'Non-Katalog (Pengadaan Langsung)',
                                'e_katalog' => 'E-Katalog (E-Purchasing LKPP)',
                                'semua' => 'Semua Metode (Lintas Pengadaan)',
                            ])
                            ->default('non_katalog')
                            ->required()
                            ->live(),

                        Select::make('jenis_belanja')
                            ->label('Jenis Pekerjaan / Belanja')
                            ->options(function () {
                                $options = [
                                    'barang_jasa' => 'Non-Katalog: Belanja Barang & Jasa',
                                    'makan_minum' => 'Non-Katalog: Makan & Minum Rapat',
                                    'sppd' => 'Non-Katalog: Perjalanan Dinas (SPPD)',
                                    'bbm' => 'Non-Katalog: BBM (Bahan Bakar Minyak)',
                                    'e_katalog' => 'E-Katalog: Pengadaan Paket E-Purchasing',
                                    'semua' => 'Dokumen Umum / Lintas Belanja',
                                ];

                                $customs = MasterSyaratBelanja::query()
                                    ->whereNotIn('jenis_belanja', array_keys($options))
                                    ->distinct()
                                    ->pluck('jenis_belanja');

                                foreach ($customs as $c) {
                                    $options[$c] = 'Pekerjaan: ' . ucwords(str_replace('_', ' ', $c));
                                }

                                return $options;
                            })
                            ->searchable()
                            ->required(),
                    ]),

                    TextInput::make('nama_syarat')
                        ->label('Nama Dokumen Persyaratan')
                        ->placeholder('Contoh: Berita Acara Pemeriksaan Barang (BAP)')
                        ->required()
                        ->maxLength(200),

                    Textarea::make('keterangan')
                        ->label('Rincian / Petunjuk Dokumen (Opsional)')
                        ->placeholder('Penjelasan rincian berkas yang wajib diunggah...')
                        ->rows(2),

                    Grid::make(3)->schema([
                        TextInput::make('urutan')
                            ->label('Nomor Urut (Otomatis jika kosong)')
                            ->numeric()
                            ->placeholder('Auto'),

                        Toggle::make('is_required')
                            ->label('Wajib Diunggah')
                            ->default(true),

                        Toggle::make('aktif')
                            ->label('Status Aktif')
                            ->default(true),
                    ]),
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

                    $urutan = ! empty($data['urutan']) ? (int) $data['urutan'] : ($maxUrutan + 1);

                    MasterSyaratBelanja::create([
                        'jenis_belanja' => $data['jenis_belanja'],
                        'metode_pengadaan' => $data['metode_pengadaan'],
                        'nama_syarat' => trim($data['nama_syarat']),
                        'keterangan' => ! empty($data['keterangan']) ? trim($data['keterangan']) : null,
                        'is_required' => (bool) ($data['is_required'] ?? true),
                        'aktif' => (bool) ($data['aktif'] ?? true),
                        'urutan' => $urutan,
                    ]);

                    Notification::make()
                        ->title('Syarat Dokumen Ditambahkan')
                        ->body("Dokumen \"{$data['nama_syarat']}\" berhasil ditambahkan.")
                        ->success()
                        ->send();
                }),

            // Aksi 2: Tambah Jenis Pekerjaan Baru (dengan Langkah Terstruktur & Repeater Dokumen)
            Action::make('tambahPekerjaan')
                ->label('Tambah Jenis Pekerjaan')
                ->icon('heroicon-o-folder-plus')
                ->color('success')
                ->modalHeading('Tambah Jenis Pekerjaan & Syarat Dokumen Baru')
                ->modalDescription('Langkah 1: Tentukan nama kelompok pekerjaan. Langkah 2: Lengkapi daftar dokumen yang dipersyaratkan.')
                ->modalWidth(\Filament\Support\Enums\Width::TwoExtraLarge)
                ->form([
                    Section::make('Langkah 1: Identitas Jenis Pekerjaan')
                        ->compact()
                        ->schema([
                            Grid::make(2)->schema([
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
                                    ->helperText('Akan menjadi kelompok belanja pada SPJ.')
                                    ->required()
                                    ->maxLength(100),
                            ]),

                            Textarea::make('deskripsi')
                                ->label('Deskripsi / Petunjuk Pengajuan (Opsional)')
                                ->placeholder('Contoh: Persyaratan untuk servis rutin, ganti oli, dan perbaikan kendaraan dinas operasional.')
                                ->rows(2),
                        ]),

                    Section::make('Langkah 2: Daftar Syarat Dokumen Kelengkapan')
                        ->compact()
                        ->schema([
                            TextInput::make('syarat_pertama')
                                ->label('Nama Dokumen Syarat Pertama (Wajib)')
                                ->placeholder('Contoh: Faktur / Nota Asli Toko')
                                ->default('Faktur / Nota Asli Toko')
                                ->required(),

                            Repeater::make('syarat_tambahan')
                                ->label('Syarat Dokumen Tambahan (Opsional)')
                                ->itemLabel(fn (array $state): ?string => $state['nama_syarat'] ?? 'Dokumen Tambahan')
                                ->defaultItems(0)
                                ->schema([
                                    Grid::make(12)->schema([
                                        TextInput::make('nama_syarat')
                                            ->label('Nama Dokumen')
                                            ->placeholder('Contoh: Foto Dokumentasi Barang / BAST')
                                            ->required()
                                            ->columnSpan(6),

                                        TextInput::make('keterangan')
                                            ->label('Keterangan')
                                            ->placeholder('Petunjuk berkas...')
                                            ->columnSpan(4),

                                        Toggle::make('is_required')
                                            ->label('Wajib')
                                            ->default(true)
                                            ->columnSpan(2),
                                    ]),
                                ])
                                ->addActionLabel('+ Tambah Dokumen Lainnya')
                                ->collapsible(),
                        ]),
                ])
                ->action(function (array $data) {
                    $slug = Str::slug($data['nama_pekerjaan'], '_');
                    if (empty($slug)) {
                        $slug = 'pekerjaan_' . time();
                    }

                    $metode = $data['metode_pengadaan'] ?? 'non_katalog';
                    $deskripsi = ! empty($data['deskripsi']) ? trim($data['deskripsi']) : "Persyaratan dokumen untuk {$data['nama_pekerjaan']}";

                    $syaratPertama = ! empty($data['syarat_pertama']) ? trim($data['syarat_pertama']) : 'Faktur / Nota Asli Toko';

                    MasterSyaratBelanja::create([
                        'jenis_belanja' => $slug,
                        'metode_pengadaan' => $metode,
                        'nama_syarat' => $syaratPertama,
                        'keterangan' => $deskripsi,
                        'is_required' => true,
                        'urutan' => 1,
                        'aktif' => true,
                    ]);

                    $urutan = 2;
                    $tambahanCount = 0;
                    if (! empty($data['syarat_tambahan']) && is_array($data['syarat_tambahan'])) {
                        foreach ($data['syarat_tambahan'] as $s) {
                            if (! empty($s['nama_syarat'])) {
                                MasterSyaratBelanja::create([
                                    'jenis_belanja' => $slug,
                                    'metode_pengadaan' => $metode,
                                    'nama_syarat' => trim($s['nama_syarat']),
                                    'keterangan' => ! empty($s['keterangan']) ? trim($s['keterangan']) : $deskripsi,
                                    'is_required' => (bool) ($s['is_required'] ?? true),
                                    'urutan' => $urutan++,
                                    'aktif' => true,
                                ]);
                                $tambahanCount++;
                            }
                        }
                    }

                    $totalSyarat = 1 + $tambahanCount;

                    Notification::make()
                        ->title('Jenis Pekerjaan Berhasil Dibuat')
                        ->body("Pekerjaan \"{$data['nama_pekerjaan']}\" berhasil dibuat beserta {$totalSyarat} syarat dokumen.")
                        ->success()
                        ->send();
                }),

            // Aksi 3: Hapus Pekerjaan Kustom
            Action::make('hapusPekerjaan')
                ->label('Hapus Pekerjaan')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->modalHeading('Hapus Kelompok Pekerjaan Kustom')
                ->modalDescription('Pilih jenis pekerjaan kustom yang ingin dihapus beserta seluruh dokumen syaratnya.')
                ->visible(function () {
                    return MasterSyaratBelanja::query()
                        ->whereNotIn('jenis_belanja', ['barang_jasa', 'makan_minum', 'sppd', 'bbm', 'semua'])
                        ->exists();
                })
                ->form([
                    Select::make('jenis_belanja')
                        ->label('Pilih Jenis Pekerjaan yang Dihapus')
                        ->options(function () {
                            return MasterSyaratBelanja::query()
                                ->whereNotIn('jenis_belanja', ['barang_jasa', 'makan_minum', 'sppd', 'bbm', 'semua'])
                                ->distinct()
                                ->pluck('jenis_belanja')
                                ->mapWithKeys(fn ($k) => [$k => ucwords(str_replace('_', ' ', $k))])
                                ->all();
                        })
                        ->required(),
                ])
                ->requiresConfirmation()
                ->action(function (array $data, array $arguments) {
                    $slug = $data['jenis_belanja'] ?? $arguments['jenis_belanja'] ?? null;
                    if ($slug) {
                        $query = MasterSyaratBelanja::where('jenis_belanja', $slug);
                        if (! empty($arguments['metode_pengadaan'])) {
                            $query->where('metode_pengadaan', $arguments['metode_pengadaan']);
                        }
                        $count = $query->count();
                        $query->delete();

                        Notification::make()
                            ->title('Pekerjaan Berhasil Dihapus')
                            ->body("Pekerjaan dan {$count} syarat dokumen telah dihapus.")
                            ->success()
                            ->send();
                    }
                }),

            // Aksi pembantu untuk kompatibilitas test & modal aksi langsung
            $this->editSyaratAction(),
            $this->hapusSyaratAction(),
        ];
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
}
