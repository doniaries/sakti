<?php

namespace App\Filament\Resources\MasterSyaratBelanjas\Pages;

use App\Filament\Resources\MasterSyaratBelanjas\MasterSyaratBelanjaResource;
use App\Models\MasterSyaratBelanja;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Url;

class ListMasterSyaratBelanjas extends ListRecords
{
    protected static string $resource = MasterSyaratBelanjaResource::class;

    #[Url(as: 'view')]
    public string $viewMode = 'shield';

    public array $shieldData = [];

    public function mount(): void
    {
        parent::mount();

        $this->loadShieldData();
    }

    public function loadShieldData(): void
    {
        $categories = $this->getCategoriesData();

        foreach ($categories as $catKey => $cat) {
            $this->shieldData["aktif_{$catKey}"] = $cat['items']
                ->where('aktif', true)
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();

            $this->shieldData["wajib_{$catKey}"] = $cat['items']
                ->where('is_required', true)
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();
        }
    }

    public function content(Schema $schema): Schema
    {
        if ($this->viewMode === 'table') {
            return parent::content($schema);
        }

        return $schema
            ->statePath('shieldData')
            ->components([
                $this->getShieldTabsComponent(),
            ]);
    }

    public function getShieldTabsComponent(): Component
    {
        $categories = $this->getCategoriesData();

        $aktifSections = [];
        $wajibSections = [];

        foreach ($categories as $catKey => $cat) {
            $headerActions = [
                Action::make("tambah_{$catKey}")
                    ->label('Tambah Dokumen')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->modalHeading("Tambah Dokumen - {$cat['label']}")
                    ->modalDescription('Tambahkan berkas persyaratan baru ke kategori ini.')
                    ->modalWidth('md')
                    ->form([
                        TextInput::make('nama_syarat')
                            ->label('Nama Dokumen Persyaratan')
                            ->placeholder('Contoh: Faktur Pembelian Toko Rekanan')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('keterangan')
                            ->label('Petunjuk / Keterangan Dokumen')
                            ->placeholder('Contoh: Scan dokumen PDF asli bertandatangan dan berstempel')
                            ->rows(2),
                        TextInput::make('urutan')
                            ->label('Nomor Urut')
                            ->numeric()
                            ->default((int) $cat['items']->max('urutan') + 1)
                            ->required(),
                    ])
                    ->action(function (array $data) use ($catKey) {
                        $jenisBelanja = match ($catKey) {
                            'barang_jasa' => 'barang_jasa',
                            'makan_minum' => 'makan_minum',
                            'sppd' => 'sppd',
                            'bbm' => 'bbm',
                            default => 'semua',
                        };
                        $metodePengadaan = ($catKey === 'e_katalog') ? 'e_katalog' : 'non_katalog';

                        $record = MasterSyaratBelanja::create([
                            'nama_syarat' => $data['nama_syarat'],
                            'keterangan' => $data['keterangan'] ?? null,
                            'urutan' => $data['urutan'] ?? 1,
                            'jenis_belanja' => $jenisBelanja,
                            'metode_pengadaan' => $metodePengadaan,
                            'aktif' => true,
                            'is_required' => true,
                        ]);

                        $this->loadShieldData();

                        Notification::make()
                            ->title('Dokumen Berhasil Ditambahkan')
                            ->body("Dokumen \"{$record->nama_syarat}\" berhasil ditambahkan dan otomatis dicentang.")
                            ->success()
                            ->send();
                    }),

                Action::make("edit_{$catKey}")
                    ->label('Edit Dokumen')
                    ->icon('heroicon-o-pencil-square')
                    ->color('gray')
                    ->modalHeading("Edit Dokumen - {$cat['label']}")
                    ->modalDescription('Pilih dokumen yang ingin diubah rincian nama atau petunjuknya.')
                    ->modalWidth('md')
                    ->form([
                        Select::make('id')
                            ->label('Pilih Dokumen')
                            ->options($cat['options'])
                            ->placeholder('Pilih berkas yang ingin diedit')
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $record = MasterSyaratBelanja::find($state);
                                if ($record) {
                                    $set('nama_syarat', $record->nama_syarat);
                                    $set('keterangan', $record->keterangan);
                                    $set('urutan', $record->urutan);
                                }
                            }),
                        TextInput::make('nama_syarat')
                            ->label('Nama Dokumen Syarat')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('keterangan')
                            ->label('Petunjuk / Keterangan Dokumen')
                            ->rows(2),
                        TextInput::make('urutan')
                            ->label('Nomor Urut')
                            ->numeric()
                            ->required(),
                        Toggle::make('hapus_dokumen')
                            ->label('Hapus Dokumen Ini Secara Permanen')
                            ->helperText('Aktifkan jika dokumen ini tidak lagi dibutuhkan sama sekali.')
                            ->default(false),
                    ])
                    ->action(function (array $data) {
                        $record = MasterSyaratBelanja::find($data['id'] ?? null);
                        if (! $record) {
                            return;
                        }

                        if (! empty($data['hapus_dokumen'])) {
                            $name = $record->nama_syarat;
                            $record->delete();
                            $this->loadShieldData();

                            Notification::make()
                                ->title('Dokumen Dihapus')
                                ->body("Dokumen \"{$name}\" telah dihapus dari sistem.")
                                ->danger()
                                ->send();
                            return;
                        }

                        $record->update([
                            'nama_syarat' => $data['nama_syarat'],
                            'keterangan' => $data['keterangan'] ?? null,
                            'urutan' => $data['urutan'] ?? $record->urutan,
                        ]);

                        $this->loadShieldData();

                        Notification::make()
                            ->title('Dokumen Diperbarui')
                            ->body("Dokumen \"{$record->nama_syarat}\" berhasil diperbarui.")
                            ->success()
                            ->send();
                    }),
            ];

            $aktifSections[] = Section::make($cat['label'])
                ->description($cat['description'])
                ->compact()
                ->collapsible()
                ->headerActions($headerActions)
                ->schema([
                    CheckboxList::make("aktif_{$catKey}")
                        ->hiddenLabel()
                        ->options($cat['options'])
                        ->descriptions($cat['descriptions'])
                        ->bulkToggleable()
                        ->columns(['default' => 1, 'sm' => 2, 'lg' => 3, 'xl' => 4])
                        ->live()
                        ->afterStateUpdated(fn ($state) => $this->syncAktifCategory($catKey, $state)),
                ]);

            $wajibSections[] = Section::make($cat['label'])
                ->description($cat['description'])
                ->compact()
                ->collapsible()
                ->headerActions($headerActions)
                ->schema([
                    CheckboxList::make("wajib_{$catKey}")
                        ->hiddenLabel()
                        ->options($cat['options'])
                        ->descriptions($cat['descriptions'])
                        ->bulkToggleable()
                        ->columns(['default' => 1, 'sm' => 2, 'lg' => 3, 'xl' => 4])
                        ->live()
                        ->afterStateUpdated(fn ($state) => $this->syncWajibCategory($catKey, $state)),
                ]);
        }

        $totalAktif = MasterSyaratBelanja::where('aktif', true)->count();
        $totalWajib = MasterSyaratBelanja::where('is_required', true)->count();

        return Tabs::make('SyaratDokumen')
            ->contained()
            ->tabs([
                Tab::make('aktif')
                    ->label('Dokumen Aktif Digunakan')
                    ->badge($totalAktif)
                    ->schema($aktifSections),

                Tab::make('wajib')
                    ->label('Dokumen Wajib Diunggah')
                    ->badge($totalWajib)
                    ->schema($wajibSections),
            ]);
    }

    protected function syncAktifCategory(string $catKey, mixed $state): void
    {
        $selectedIds = collect($state ?? [])->map(fn ($id) => (int) $id)->all();

        $query = MasterSyaratBelanja::query();
        $this->applyCategoryScope($query, $catKey);
        $categoryIds = $query->pluck('id')->all();

        MasterSyaratBelanja::whereIn('id', $categoryIds)
            ->whereIn('id', $selectedIds)
            ->update(['aktif' => true]);

        MasterSyaratBelanja::whereIn('id', $categoryIds)
            ->whereNotIn('id', $selectedIds)
            ->update(['aktif' => false]);

        Notification::make()
            ->title('Status Dokumen Diperbarui')
            ->body('Perubahan centang dokumen aktif berhasil disimpan.')
            ->success()
            ->send();
    }

    protected function syncWajibCategory(string $catKey, mixed $state): void
    {
        $selectedIds = collect($state ?? [])->map(fn ($id) => (int) $id)->all();

        $query = MasterSyaratBelanja::query();
        $this->applyCategoryScope($query, $catKey);
        $categoryIds = $query->pluck('id')->all();

        MasterSyaratBelanja::whereIn('id', $categoryIds)
            ->whereIn('id', $selectedIds)
            ->update(['is_required' => true]);

        MasterSyaratBelanja::whereIn('id', $categoryIds)
            ->whereNotIn('id', $selectedIds)
            ->update(['is_required' => false]);

        Notification::make()
            ->title('Kewajiban Dokumen Diperbarui')
            ->body('Perubahan centang dokumen wajib berhasil disimpan.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('switchView')
                ->label(fn (): string => $this->viewMode === 'shield' ? 'Mode Tabel' : 'Mode Centang Shield')
                ->icon(fn (): string => $this->viewMode === 'shield' ? 'heroicon-o-table-cells' : 'heroicon-o-shield-check')
                ->color(fn (): string => $this->viewMode === 'shield' ? 'gray' : 'primary')
                ->action(function () {
                    $this->viewMode = $this->viewMode === 'shield' ? 'table' : 'shield';
                }),

            Action::make('resetSop')
                ->label('Pulihkan Standar SOP')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Pulihkan Dokumen Standar SOP?')
                ->modalDescription('Tindakan ini akan menyinkronkan kembali daftar syarat dokumen SPJ sesuai SOP dinas.')
                ->action(function () {
                    Artisan::call('db:seed', ['--class' => 'MasterSyaratBelanjaSeeder', '--force' => true]);
                    $this->loadShieldData();

                    Notification::make()
                        ->title('Standar SOP Berhasil Dipulihkan')
                        ->body('Checklist dokumen persyaratan SPJ telah disinkronkan kembali.')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function applyCategoryScope(Builder $query, string $category): void
    {
        match ($category) {
            'barang_jasa' => $query->where('jenis_belanja', 'barang_jasa'),
            'makan_minum' => $query->where('jenis_belanja', 'makan_minum'),
            'sppd' => $query->where('jenis_belanja', 'sppd'),
            'bbm' => $query->where('jenis_belanja', 'bbm'),
            'e_katalog' => $query->where('metode_pengadaan', 'e_katalog'),
            'semua' => $query->where('jenis_belanja', 'semua')->where('metode_pengadaan', '!=', 'e_katalog'),
            default => null,
        };
    }

    public function getCategoriesData(): array
    {
        $definitions = [
            'barang_jasa' => [
                'label' => 'Belanja Barang & Jasa',
                'description' => 'Persyaratan pertanggungjawaban untuk belanja ATK, cetak, perlengkapan kantor, dan barang operasional dinas.',
                'query' => fn (Builder $q) => $q->where('jenis_belanja', 'barang_jasa'),
            ],
            'makan_minum' => [
                'label' => 'Makan & Minum Rapat',
                'description' => 'Persyaratan pertanggungjawaban konsumsi rapat dinas, snack box, makan siang, dan notulensi kegiatan.',
                'query' => fn (Builder $q) => $q->where('jenis_belanja', 'makan_minum'),
            ],
            'sppd' => [
                'label' => 'Perjalanan Dinas (SPPD)',
                'description' => 'Persyaratan pertanggungjawaban perjalanan dinas dalam daerah, luar kabupaten, dan luar provinsi.',
                'query' => fn (Builder $q) => $q->where('jenis_belanja', 'sppd'),
            ],
            'bbm' => [
                'label' => 'Bahan Bakar Minyak (BBM)',
                'description' => 'Persyaratan pertanggungjawaban pembelian BBM kendaraan dinas sesuai standar kuota liter.',
                'query' => fn (Builder $q) => $q->where('jenis_belanja', 'bbm'),
            ],
            'e_katalog' => [
                'label' => 'E-Katalog LKPP (E-Purchasing)',
                'description' => 'Persyaratan pertanggungjawaban pengadaan barang/jasa melalui portal resmi E-Katalog / E-Purchasing LKPP.',
                'query' => fn (Builder $q) => $q->where('metode_pengadaan', 'e_katalog'),
            ],
            'semua' => [
                'label' => 'Dokumen Umum (Semua Belanja)',
                'description' => 'Persyaratan umum yang berlaku untuk seluruh transaksi belanja SPJ.',
                'query' => fn (Builder $q) => $q->where('jenis_belanja', 'semua')->where('metode_pengadaan', '!=', 'e_katalog'),
            ],
        ];

        $result = [];

        foreach ($definitions as $key => $meta) {
            $query = MasterSyaratBelanja::query();
            ($meta['query'])($query);

            $items = $query->orderBy('urutan')->get();

            $options = [];
            $descriptions = [];
            foreach ($items as $item) {
                $options[(string) $item->id] = $item->nama_syarat;
                if (filled($item->keterangan)) {
                    $descriptions[(string) $item->id] = $item->keterangan;
                }
            }

            $result[$key] = [
                'key' => $key,
                'label' => $meta['label'],
                'description' => $meta['description'],
                'items' => $items,
                'options' => $options,
                'descriptions' => $descriptions,
            ];
        }

        return $result;
    }
}
