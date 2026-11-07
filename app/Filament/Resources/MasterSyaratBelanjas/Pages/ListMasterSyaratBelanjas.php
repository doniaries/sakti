<?php

namespace App\Filament\Resources\MasterSyaratBelanjas\Pages;

use App\Filament\Resources\MasterSyaratBelanjas\MasterSyaratBelanjaResource;
use App\Models\MasterSyaratBelanja;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
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
            ->components(
                $this->getShieldSections()
            );
    }

    public function getShieldSections(): array
    {
        $categories = $this->getCategoriesDefinition();
        $masterDocs = static::getMasterDokumenLibrary();
        $options = array_combine(array_keys($masterDocs), array_keys($masterDocs));

        $sections = [];

        foreach ($categories as $catKey => $cat) {
            $sections[] = Section::make(fn () => "{$cat['label']} (" . count($this->shieldData["aktif_{$catKey}"] ?? []) . " Syarat)")
                ->description($cat['description'])
                ->compact()
                ->collapsible()
                ->headerActions([
                    Action::make("simpan_{$catKey}")
                        ->label('Simpan Dokumen')
                        ->icon('heroicon-o-check')
                        ->color('primary')
                        ->action(fn () => $this->saveCategory($catKey)),
                ])
                ->schema([
                    CheckboxList::make("aktif_{$catKey}")
                        ->hiddenLabel()
                        ->options($options)
                        ->descriptions($masterDocs)
                        ->bulkToggleable()
                        ->searchable()
                        ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
                        ->live()
                        ->afterStateUpdated(fn ($state) => $this->syncAktifCategory($catKey, $state, false)),
                ]);
        }

        return $sections;
    }

    public function saveCategory(string $catKey): void
    {
        $selectedDocs = (array) ($this->shieldData["aktif_{$catKey}"] ?? []);
        $this->syncAktifCategory($catKey, $selectedDocs, false);

        $categories = $this->getCategoriesDefinition();
        $label = $categories[$catKey]['label'] ?? 'Dokumen';
        $count = count($selectedDocs);

        Notification::make()
            ->title('Syarat Dokumen Disimpan')
            ->body("Sebanyak {$count} syarat dokumen untuk {$label} berhasil disimpan.")
            ->success()
            ->send();
    }

    protected function syncAktifCategory(string $catKey, mixed $state, bool $notify = false): void
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

        foreach ($selectedDocs as $docName) {
            $existing = MasterSyaratBelanja::where('jenis_belanja', $jenisBelanja)
                ->where('metode_pengadaan', $metode)
                ->where('nama_syarat', $docName)
                ->first();

            MasterSyaratBelanja::updateOrCreate(
                [
                    'jenis_belanja' => $jenisBelanja,
                    'metode_pengadaan' => $metode,
                    'nama_syarat' => $docName,
                ],
                [
                    'keterangan' => $masterDocs[$docName] ?? null,
                    'aktif' => true,
                    'is_required' => $existing ? $existing->is_required : true,
                ]
            );
        }

        MasterSyaratBelanja::where('jenis_belanja', $jenisBelanja)
            ->where('metode_pengadaan', $metode)
            ->whereNotIn('nama_syarat', $selectedDocs)
            ->update(['aktif' => false]);

        $this->shieldData["aktif_{$catKey}"] = $selectedDocs;

        if ($notify) {
            Notification::make()
                ->title('Status Dokumen Diperbarui')
                ->body('Perubahan centang dokumen berhasil disimpan.')
                ->success()
                ->send();
        }
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

        MasterSyaratBelanja::where('jenis_belanja', $jenisBelanja)
            ->where('metode_pengadaan', $metode)
            ->whereIn('nama_syarat', $selectedDocs)
            ->update(['is_required' => true]);

        MasterSyaratBelanja::where('jenis_belanja', $jenisBelanja)
            ->where('metode_pengadaan', $metode)
            ->whereNotIn('nama_syarat', $selectedDocs)
            ->update(['is_required' => false]);

        $this->shieldData["wajib_{$catKey}"] = $selectedDocs;

        Notification::make()
            ->title('Kewajiban Dokumen Diperbarui')
            ->body('Perubahan kewajiban dokumen berhasil disimpan.')
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
