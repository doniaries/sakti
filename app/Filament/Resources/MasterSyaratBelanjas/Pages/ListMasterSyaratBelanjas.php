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
            // 1. DOKUMEN KHUSUS E-KATALOG (DIUNDUH DARI SISTEM E-KATALOG / E-PROC LKPP)
            'Bundel Dokumen E-Purchasing LKPP (Surat Pesanan, BAST & Invoice E-Proc)' => '[E-Katalog] File PDF bundel resmi lengkap hasil unduh langsung dari sistem E-Katalog LKPP (mencakup Surat Pesanan, BAST, dan Faktur E-Proc).',
            'Foto / Bukti Penerimaan Barang E-Katalog' => '[E-Katalog] Foto dokumentasi fisik saat barang e-katalog tiba dan diperiksa oleh tim penerima barang di kantor dinas.',
            'Surat Pesanan Resmi E-Katalog LKPP' => '[E-Katalog] Surat Pesanan elektronik resmi unduhan portal e-proc LKPP jika diunggah per dokumen terpisah.',
            'Berita Acara Serah Terima (BAST) E-Purchasing LKPP' => '[E-Katalog] BAST elektronik resmi unduhan portal E-Katalog LKPP bertandatangan penyedia portal.',
            'Faktur / Invoice Resmi E-Procurement LKPP' => '[E-Katalog] Invoice / tagihan resmi elektronik dari penyedia di sistem E-Katalog LKPP.',
            'Surat Pernyataan Tanggung Jawab Mutlak (SPTJM) E-Purchasing' => '[E-Katalog] SPTJM kebenaran pengadaan e-purchasing dan penerimaan barang bermaterai.',

            // 2. DOKUMEN NON-KATALOG (PENGADAAN LANGSUNG TOKO FISIK & KEDINASAN)
            'Faktur / Nota Asli Penjual' => '[Non-Katalog] Nota atau faktur fisik asli berstempel basah dari toko rekanan yang memuat rincian pembelian barang.',
            'Foto Fisik Barang / Pengadaan' => '[Non-Katalog] Foto dokumentasi fisik barang yang dibeli di toko rekanan (wajib jika nilai belanja > Rp 2.000.000,-).',
            'Berita Acara Serah Terima (BAST) Rekanan' => '[Non-Katalog] BAST fisik pekerjaan atau serah terima barang dari toko rekanan/penyedia offline.',
            'Kwitansi / Bukti Pembayaran Asli' => '[Non-Katalog] Kwitansi fisik atau bukti bayar asli bertandatangan dan berstempel lunas dari penyedia/toko.',
            'Surat Undangan / Nota Dinas Rapat' => '[Non-Katalog] Surat undangan resmi atau nota dinas pelaksanaan rapat kedinasan dinas.',
            'Daftar Hadir Peserta Rapat (Bertandatangan)' => '[Non-Katalog] Scan lembar daftar hadir peserta rapat lengkap dengan tanda tangan asli peserta.',
            'Notulensi Hasil Rapat' => '[Non-Katalog] Risalah ringkas atau laporan tertulis hasil pembahasan rapat kedinasan.',
            'Foto Dokumentasi Pelaksanaan Rapat' => '[Non-Katalog] Foto suasana rapat dinas dan sajian konsumsi makan/snack box di ruangan.',
            'Nota / Kwitansi Rumah Makan / Katering' => '[Non-Katalog] Nota rincian jumlah snack box atau nasi kotak dari rumah makan/katering penyedia.',
            'Surat Perintah Tugas (SPT)' => '[Non-Katalog] SPT resmi bertandatangan Kepala Dinas / Pimpinan sebagai dasar perjalanan dinas.',
            'Lembar SPPD Bertandatangan & Cap Tujuan' => '[Non-Katalog] Lembar SPPD halaman belakang dicap dan ditandatangani pejabat instansi tujuan.',
            'Laporan Hasil Perjalanan Dinas' => '[Non-Katalog] Laporan pertanggungjawaban pelaksanaan tugas perjalanan dinas oleh pelaksana tugas.',
            'Foto Dokumentasi Kegiatan di Lokasi Tujuan' => '[Non-Katalog] Foto dokumentasi kegiatan pelaksanaan tugas kedinasan di tempat tujuan.',
            'Tiket Transportasi / Boarding Pass / Bukti Penginapan' => '[Non-Katalog] Kuitansi hotel/penginapan, karcis travel resmi, atau boarding pass transportasi.',
            'Struk / Kupon Asli Pembelian BBM' => '[Non-Katalog] Struk SPBU asli atau kupon resmi BBM sesuai kuota liter yang disetujui.',
            'Foto Odometer & Kendaraan Dinas' => '[Non-Katalog] Foto plat nomor kendaraan dinas dan angka odometer sebelum dan sesudah tugas.',
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

        $this->redirect($this->getResource()::getUrl('index'));
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
            'e_katalog' => [
                'label' => 'E-Katalog LKPP (E-Purchasing)',
                'description' => 'Persyaratan pengadaan melalui portal resmi E-Katalog / E-Proc LKPP. File diunggah langsung dari berkas hasil unduhan sistem E-Katalog (Surat Pesanan, BAST & Invoice E-Proc).',
                'query' => fn (Builder $q) => $q->where('metode_pengadaan', 'e_katalog'),
            ],
            'barang_jasa' => [
                'label' => 'Non-Katalog: Belanja Barang & Jasa',
                'description' => 'Pengadaan langsung offline kepada toko rekanan fisik untuk ATK, cetak, dan perlengkapan kantor (Nota Fisik Toko, Foto Barang, BAST Rekanan).',
                'query' => fn (Builder $q) => $q->where('jenis_belanja', 'barang_jasa')->where('metode_pengadaan', 'non_katalog'),
            ],
            'makan_minum' => [
                'label' => 'Non-Katalog: Makan & Minum Rapat',
                'description' => 'Persyaratan jamuan rapat dinas offline (Nota Katering, Surat Undangan Rapat, Daftar Hadir bertandatangan, Notulensi, dan Foto Rapat).',
                'query' => fn (Builder $q) => $q->where('jenis_belanja', 'makan_minum')->where('metode_pengadaan', 'non_katalog'),
            ],
            'sppd' => [
                'label' => 'Non-Katalog: Perjalanan Dinas (SPPD)',
                'description' => 'Persyaratan perjalanan dinas resmi (Surat Perintah Tugas / SPT, Lembar SPPD dicap pejabat tujuan, Laporan Perjalanan, dan Tiket/Kuitansi Hotel).',
                'query' => fn (Builder $q) => $q->where('jenis_belanja', 'sppd')->where('metode_pengadaan', 'non_katalog'),
            ],
            'bbm' => [
                'label' => 'Non-Katalog: Bahan Bakar Minyak (BBM)',
                'description' => 'Persyaratan BBM kendaraan operasional dinas (Struk SPBU asli atau kupon resmi BBM sesuai kuota liter pagu, dan Foto Odometer Kendaraan).',
                'query' => fn (Builder $q) => $q->where('jenis_belanja', 'bbm')->where('metode_pengadaan', 'non_katalog'),
            ],
            'semua' => [
                'label' => 'Dokumen Umum / Lampiran Tambahan',
                'description' => 'Persyaratan pendukung umum yang berlaku lintas transaksi SPJ (Surat Pernyataan Tanggung Jawab Mutlak / SPTJM, dsb.).',
                'query' => fn (Builder $q) => $q->where('jenis_belanja', 'semua')->where('metode_pengadaan', '!=', 'e_katalog'),
            ],
        ];
    }
}
