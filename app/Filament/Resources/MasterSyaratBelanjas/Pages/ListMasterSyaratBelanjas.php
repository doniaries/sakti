<?php

namespace App\Filament\Resources\MasterSyaratBelanjas\Pages;

use App\Filament\Resources\MasterSyaratBelanjas\MasterSyaratBelanjaResource;
use App\Models\MasterSyaratBelanja;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Component;
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

    public static function getMasterDokumenLibrary(): array
    {
        $defaultMaster = [
            'Kwitansi / Bukti Pembayaran Asli' => 'Kwitansi atau bukti bayar asli bertandatangan dan berstempel lunas dari penyedia/toko',
            'Faktur / Nota Asli Penjual' => 'Faktur atau nota rincian item pembelian barang/jasa dari toko rekanan',
            'Foto Fisik Barang / Pengadaan' => 'Foto dokumentasi fisik barang yang telah dibeli atau serah terima di kantor',
            'Berita Acara Serah Terima (BAST) Rekanan' => 'BAST resmi pekerjaan atau penyerahan barang dari rekanan/penyedia',
            'Surat Undangan / Nota Dinas Rapat' => 'Surat undangan resmi atau nota dinas pelaksanaan rapat kedinasan',
            'Daftar Hadir Peserta Rapat (Bertandatangan)' => 'Scan lembar daftar hadir peserta rapat lengkap dengan tanda tangan',
            'Notulensi Hasil Rapat' => 'Laporan atau risalah ringkas hasil pembahasan rapat kedinasan',
            'Foto Dokumentasi Pelaksanaan Rapat' => 'Foto suasana rapat dinas dan konsumsi snack/makan yang disajikan',
            'Surat Perintah Tugas (SPT)' => 'SPT resmi bertandatangan Kepala Dinas / Pimpinan',
            'Lembar SPPD Bertandatangan & Cap Tujuan' => 'Lembar SPPD halaman belakang dicap dan ditandatangani pejabat instansi tujuan',
            'Laporan Hasil Perjalanan Dinas' => 'Laporan pertanggungjawaban pelaksanaan tugas perjalanan dinas',
            'Tiket Transportasi / Boarding Pass / Bukti Penginapan' => 'Kuitansi hotel/penginapan, karcis travel, atau boarding pass transportasi',
            'Struk / Kupon Asli Pembelian BBM' => 'Struk SPBU atau kupon resmi BBM sesuai kuota liter yang disetujui',
            'Foto Odometer & Kendaraan Dinas' => 'Foto plat nomor dinas dan angka odometer sebelum dan sesudah tugas',
            'Bundel Dokumen E-Purchasing LKPP (Surat Pesanan, BAST & Invoice E-Proc)' => 'Bundel dokumen resmi PDF lengkap dari portal E-Katalog / E-Purchasing LKPP',
            'Foto / Bukti Penerimaan Barang' => 'Foto dokumentasi fisik saat barang e-katalog diterima di dinas',
            'Surat Pernyataan Tanggung Jawab Mutlak (SPTJM)' => 'SPTJM kebenaran belanja dan bukti pengeluaran riil',
        ];

        // Tambahkan dokumen kustom yang pernah dibuat user di database
        $customDocs = MasterSyaratBelanja::query()
            ->select('nama_syarat', 'keterangan')
            ->distinct()
            ->get();

        foreach ($customDocs as $doc) {
            if (! isset($defaultMaster[$doc->nama_syarat])) {
                $defaultMaster[$doc->nama_syarat] = $doc->keterangan ?: 'Dokumen persyaratan SPJ';
            }
        }

        return $defaultMaster;
    }

    public function loadShieldData(): void
    {
        $categories = $this->getCategoriesDefinition();

        foreach ($categories as $catKey => $cat) {
            $query = MasterSyaratBelanja::query();
            ($cat['query'])($query);
            $items = $query->get();

            $this->shieldData["aktif_{$catKey}"] = $items
                ->where('aktif', true)
                ->pluck('nama_syarat')
                ->values()
                ->all();

            $this->shieldData["wajib_{$catKey}"] = $items
                ->where('aktif', true)
                ->where('is_required', true)
                ->pluck('nama_syarat')
                ->values()
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
        $categories = $this->getCategoriesDefinition();
        $masterDocs = static::getMasterDokumenLibrary();

        $aktifSections = [];
        $wajibSections = [];

        foreach ($categories as $catKey => $cat) {
            $headerActions = [
                Action::make("tambah_{$catKey}")
                    ->label('Tambah Dokumen')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->modalHeading("Tambah Dokumen Baku - {$cat['label']}")
                    ->modalDescription('Tambahkan nama dokumen baku ke master. Dokumen ini dapat digunakan di berbagai urusan.')
                    ->modalWidth('md')
                    ->form([
                        TextInput::make('nama_syarat')
                            ->label('Nama Dokumen Baku')
                            ->placeholder('Contoh: Kwitansi / Tanda Terima Honor')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('keterangan')
                            ->label('Petunjuk / Keterangan Dokumen')
                            ->placeholder('Contoh: Scan bukti kwitansi asli bermaterai dan bertandatangan')
                            ->rows(2),
                        CheckboxList::make('kategori_urusan')
                            ->label('Pilih Urusan Belanja yang Menggunakan Dokumen Ini')
                            ->options([
                                'barang_jasa' => 'Belanja Barang & Jasa',
                                'makan_minum' => 'Makan & Minum Rapat',
                                'sppd' => 'Perjalanan Dinas (SPPD)',
                                'bbm' => 'Bahan Bakar Minyak (BBM)',
                                'e_katalog' => 'E-Katalog LKPP',
                                'semua' => 'Dokumen Umum (Semua Urusan)',
                            ])
                            ->default([$catKey])
                            ->columns(2)
                            ->bulkToggleable()
                            ->required(),
                        Toggle::make('is_required')
                            ->label('Wajib Diunggah')
                            ->default(true),
                    ])
                    ->action(function (array $data) {
                        $nama = trim($data['nama_syarat']);
                        $ket = $data['keterangan'] ?? null;
                        $isRequired = (bool) ($data['is_required'] ?? true);
                        $kategoriList = (array) ($data['kategori_urusan'] ?? []);

                        foreach ($kategoriList as $kategori) {
                            $jenisBelanja = match ($kategori) {
                                'barang_jasa' => 'barang_jasa',
                                'makan_minum' => 'makan_minum',
                                'sppd' => 'sppd',
                                'bbm' => 'bbm',
                                default => 'semua',
                            };
                            $metode = ($kategori === 'e_katalog') ? 'e_katalog' : 'non_katalog';

                            MasterSyaratBelanja::updateOrCreate(
                                [
                                    'jenis_belanja' => $jenisBelanja,
                                    'metode_pengadaan' => $metode,
                                    'nama_syarat' => $nama,
                                ],
                                [
                                    'keterangan' => $ket,
                                    'aktif' => true,
                                    'is_required' => $isRequired,
                                    'urutan' => (int) MasterSyaratBelanja::where('jenis_belanja', $jenisBelanja)->max('urutan') + 1,
                                ]
                            );
                        }

                        $this->loadShieldData();

                        Notification::make()
                            ->title('Dokumen Baku Berhasil Ditambahkan')
                            ->body("Dokumen \"{$nama}\" telah ditambahkan ke master dan dicentang pada urusan yang dipilih.")
                            ->success()
                            ->send();
                    }),

                Action::make("edit_{$catKey}")
                    ->label('Edit Dokumen')
                    ->icon('heroicon-o-pencil-square')
                    ->color('gray')
                    ->modalHeading("Pilih & Atur Dokumen - {$cat['label']}")
                    ->modalDescription('Centang dokumen apa saja yang akan digunakan dan diwajibkan untuk urusan ini.')
                    ->modalWidth('xl')
                    ->fillForm(function () use ($catKey): array {
                        return [
                            'dokumen_dipakai' => $this->shieldData["aktif_{$catKey}"] ?? [],
                            'dokumen_wajib' => $this->shieldData["wajib_{$catKey}"] ?? [],
                        ];
                    })
                    ->form([
                        CheckboxList::make('dokumen_dipakai')
                            ->label('Pilih Dokumen yang Digunakan untuk Urusan Ini')
                            ->helperText('Dokumen yang dicentang akan menjadi checklist berkas bagi staf saat mengajukan SPJ ini.')
                            ->options(array_combine(array_keys($masterDocs), array_keys($masterDocs)))
                            ->descriptions($masterDocs)
                            ->columns(['default' => 1, 'sm' => 2])
                            ->bulkToggleable()
                            ->searchable(),

                        CheckboxList::make('dokumen_wajib')
                            ->label('Dari Dokumen yang Digunakan, Centang yang Berstatus Wajib (Harus Upload)')
                            ->helperText('Dokumen yang tidak dicentang di sini akan berstatus Opsional.')
                            ->options(array_combine(array_keys($masterDocs), array_keys($masterDocs)))
                            ->columns(['default' => 1, 'sm' => 2])
                            ->bulkToggleable()
                            ->searchable(),
                    ])
                    ->action(function (array $data) use ($catKey) {
                        $this->syncAktifCategory($catKey, $data['dokumen_dipakai'] ?? []);
                        $this->syncWajibCategory($catKey, $data['dokumen_wajib'] ?? []);

                        Notification::make()
                            ->title('Pengaturan Dokumen Disimpan')
                            ->body("Daftar dokumen untuk urusan {$catKey} berhasil diperbarui.")
                            ->success()
                            ->send();
                    }),
            ];

            $options = array_combine(array_keys($masterDocs), array_keys($masterDocs));

            $aktifSections[] = Section::make($cat['label'])
                ->description($cat['description'])
                ->compact()
                ->collapsible()
                ->headerActions($headerActions)
                ->schema([
                    CheckboxList::make("aktif_{$catKey}")
                        ->hiddenLabel()
                        ->options($options)
                        ->descriptions($masterDocs)
                        ->bulkToggleable()
                        ->searchable()
                        ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
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
                        ->options($options)
                        ->descriptions($masterDocs)
                        ->bulkToggleable()
                        ->searchable()
                        ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
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
                    ->label('Centang Dokumen yang Digunakan per Urusan')
                    ->badge($totalAktif)
                    ->schema($aktifSections),

                Tab::make('wajib')
                    ->label('Centang Dokumen yang Wajib Diunggah')
                    ->badge($totalWajib)
                    ->schema($wajibSections),
            ]);
    }

    protected function syncAktifCategory(string $catKey, mixed $state): void
    {
        $selectedDocs = collect($state ?? [])->map(fn ($doc) => (string) $doc)->all();
        $masterDocs = static::getMasterDokumenLibrary();

        $jenisBelanja = match ($catKey) {
            'barang_jasa' => 'barang_jasa',
            'makan_minum' => 'makan_minum',
            'sppd' => 'sppd',
            'bbm' => 'bbm',
            default => 'semua',
        };
        $metode = ($catKey === 'e_katalog') ? 'e_katalog' : 'non_katalog';

        // 1. Aktifkan atau buat dokumen yang dicentang
        foreach ($selectedDocs as $docName) {
            MasterSyaratBelanja::updateOrCreate(
                [
                    'jenis_belanja' => $jenisBelanja,
                    'metode_pengadaan' => $metode,
                    'nama_syarat' => $docName,
                ],
                [
                    'keterangan' => $masterDocs[$docName] ?? null,
                    'aktif' => true,
                ]
            );
        }

        // 2. Nonaktifkan dokumen yang tidak dicentang untuk kategori ini
        MasterSyaratBelanja::where('jenis_belanja', $jenisBelanja)
            ->where('metode_pengadaan', $metode)
            ->whereNotIn('nama_syarat', $selectedDocs)
            ->update(['aktif' => false]);

        $this->shieldData["aktif_{$catKey}"] = $selectedDocs;
    }

    protected function syncWajibCategory(string $catKey, mixed $state): void
    {
        $selectedDocs = collect($state ?? [])->map(fn ($doc) => (string) $doc)->all();

        $jenisBelanja = match ($catKey) {
            'barang_jasa' => 'barang_jasa',
            'makan_minum' => 'makan_minum',
            'sppd' => 'sppd',
            'bbm' => 'bbm',
            default => 'semua',
        };
        $metode = ($catKey === 'e_katalog') ? 'e_katalog' : 'non_katalog';

        // Set is_required true untuk yang dicentang
        MasterSyaratBelanja::where('jenis_belanja', $jenisBelanja)
            ->where('metode_pengadaan', $metode)
            ->whereIn('nama_syarat', $selectedDocs)
            ->update(['is_required' => true]);

        // Set is_required false untuk yang tidak dicentang
        MasterSyaratBelanja::where('jenis_belanja', $jenisBelanja)
            ->where('metode_pengadaan', $metode)
            ->whereNotIn('nama_syarat', $selectedDocs)
            ->update(['is_required' => false]);

        $this->shieldData["wajib_{$catKey}"] = $selectedDocs;
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
                ->modalDescription('Tindakan ini akan mengembalikan daftar syarat dokumen baku sesuai SOP dinas.')
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

    public function getCategoriesDefinition(): array
    {
        return [
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
    }
}
