<?php

namespace App\Filament\Resources\MasterSyaratBelanjas\Pages;

use App\Filament\Resources\MasterSyaratBelanjas\MasterSyaratBelanjaResource;
use App\Models\MasterSyaratBelanja;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
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
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ListMasterSyaratBelanjas extends ListRecords
{
    protected static string $resource = MasterSyaratBelanjaResource::class;

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
            'Scan Nota Pesanan Bertandatangan Basah (PPTK & Toko)' => '[Non-Katalog] Scan lembar fisik Nota Pesanan resmi yang telah ditandatangani basah oleh PPTK (Kabid) dan dicap/ditandatangani Rekanan Toko.',
            'Scan Kwitansi Bertandatangan Basah (Rekanan & PPTK)' => '[Non-Katalog] Scan lembar fisik Kwitansi Pembayaran yang telah ditandatangani basah oleh Rekanan Toko (bermaterai) dan PPTK (Kabid).',
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
            $items = $query->orderBy('urutan')->get();

            $this->shieldData["items_{$catKey}"] = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_syarat' => $item->nama_syarat,
                    'keterangan' => $item->keterangan,
                    'is_required' => (bool) $item->is_required,
                    'aktif' => (bool) $item->aktif,
                    'urutan' => (int) $item->urutan,
                ];
            })->values()->all();
        }
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->statePath('shieldData')
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
            $headerActions = [
                Action::make("simpan_{$catKey}")
                    ->label('Simpan Dokumen')
                    ->icon('heroicon-o-check')
                    ->color('primary')
                    ->action(fn () => $this->saveCategory($catKey)),
            ];

            if ($cat['is_custom'] ?? false) {
                $headerActions[] = Action::make("hapus_{$catKey}")
                    ->label('Hapus Pekerjaan')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading("Hapus Jenis Pekerjaan {$cat['label']}?")
                    ->modalDescription('Semua syarat dokumen untuk pekerjaan ini akan dihapus dari daftar.')
                    ->action(function () use ($catKey, $cat) {
                        MasterSyaratBelanja::where('jenis_belanja', $cat['jenis_belanja'])
                            ->where('metode_pengadaan', $cat['metode'])
                            ->delete();

                        Notification::make()
                            ->title('Pekerjaan Dihapus')
                            ->body("Jenis pekerjaan {$cat['label']} berhasil dihapus.")
                            ->success()
                            ->send();

                        $this->redirect($this->getResource()::getUrl('index'));
                    });
            }

            $countSyarat = count($this->shieldData["items_{$catKey}"] ?? []);
            $isFirstOpen = ($catKey === 'barang_jasa' || $catKey === 'e_katalog');

            $section = Section::make(fn () => "{$cat['label']} (" . count($this->shieldData["items_{$catKey}"] ?? []) . " Syarat)")
                ->description($cat['description'])
                ->compact()
                ->collapsible()
                ->collapsed(! $isFirstOpen)
                ->headerActions($headerActions)
                ->schema([
                    Repeater::make("items_{$catKey}")
                        ->hiddenLabel()
                        ->itemLabel(fn (array $state): ?string => ($state['nama_syarat'] ?? null)
                            ? ($state['nama_syarat'] . (! empty($state['is_required']) ? ' [Wajib]' : ' [Opsional]') . (empty($state['aktif']) ? ' (Nonaktif)' : ''))
                            : 'Syarat Dokumen Baru'
                        )
                        ->collapsible()
                        ->collapsed()
                        ->cloneable()
                        ->reorderableWithButtons()
                        ->addActionLabel('+ Tambah Rincian Syarat Dokumen')
                        ->schema([
                            Hidden::make('id'),

                            Grid::make(['default' => 1, 'md' => 12])
                                ->schema([
                                    TextInput::make('nama_syarat')
                                        ->label('Nama Dokumen Persyaratan')
                                        ->placeholder('Contoh: Struk SPBU / Kwitansi BBM')
                                        ->required()
                                        ->columnSpan(['default' => 12, 'md' => 6]),

                                    Toggle::make('is_required')
                                        ->label('Wajib Diunggah')
                                        ->default(true)
                                        ->inline(false)
                                        ->columnSpan(['default' => 6, 'md' => 3]),

                                    Toggle::make('aktif')
                                        ->label('Status Aktif')
                                        ->default(true)
                                        ->inline(false)
                                        ->columnSpan(['default' => 6, 'md' => 3]),

                                    Textarea::make('keterangan')
                                        ->label('Rincian / Keterangan Dokumen')
                                        ->placeholder('Penjelasan berkas rincian yang wajib/opsional disiapkan staf...')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                ]),
                        ]),
                ]);

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

    public function saveCategory(string $catKey): void
    {
        $items = (array) ($this->shieldData["items_{$catKey}"] ?? []);
        $categories = $this->getCategoriesDefinition();
        $cat = $categories[$catKey] ?? null;

        if (! $cat) {
            return;
        }

        $jenisBelanja = $cat['jenis_belanja'];
        $metode = $cat['metode'];

        $submittedIds = [];
        $urutan = 1;

        foreach ($items as $itemData) {
            if (empty($itemData['nama_syarat'])) {
                continue;
            }

            $record = null;
            if (! empty($itemData['id'])) {
                $record = MasterSyaratBelanja::find($itemData['id']);
            }

            if (! $record) {
                $record = new MasterSyaratBelanja();
                $record->jenis_belanja = $jenisBelanja;
                $record->metode_pengadaan = $metode;
            }

            $record->nama_syarat = trim($itemData['nama_syarat']);
            $record->keterangan = ! empty($itemData['keterangan']) ? trim($itemData['keterangan']) : null;
            $record->is_required = (bool) ($itemData['is_required'] ?? true);
            $record->aktif = (bool) ($itemData['aktif'] ?? true);
            $record->urutan = $urutan++;
            $record->save();

            $submittedIds[] = $record->id;
        }

        // Hapus syarat yang dibuang dari repeater pada pekerjaan ini
        MasterSyaratBelanja::where('jenis_belanja', $jenisBelanja)
            ->where('metode_pengadaan', $metode)
            ->whereNotIn('id', $submittedIds)
            ->delete();

        Notification::make()
            ->title('Syarat Dokumen Berhasil Disimpan')
            ->body("Perubahan rincian syarat untuk {$cat['label']} berhasil diperbarui.")
            ->success()
            ->send();

        $this->loadShieldData();
        $this->redirect($this->getResource()::getUrl('index'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('tambahPekerjaan')
                ->label('+ Tambah Jenis Pekerjaan')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->modalHeading('Tambah Jenis Pekerjaan & Syarat Dokumen Baru')
                ->modalDescription('Definisikan jenis pekerjaan/transaksi belanja baru beserta syarat dokumen kelengkapan yang wajib/opsional disiapkan staf pengaju.')
                ->modalWidth(\Filament\Support\Enums\Width::TwoExtraLarge)
                ->form([
                    Grid::make(['default' => 1, 'md' => 2])
                        ->schema([
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
                                ->helperText('Akan menjadi pilihan transaksi saat staf membuat pengajuan.')
                                ->required()
                                ->maxLength(100),
                        ]),

                    Textarea::make('deskripsi')
                        ->label('Deskripsi / Petunjuk Pengajuan')
                        ->placeholder('Contoh: Persyaratan untuk servis rutin, ganti oli, dan perbaikan kendaraan dinas operasional.')
                        ->rows(2),

                    CheckboxList::make('syarat_dokumen')
                        ->label('Pilih Syarat Dokumen Wajib / Pendukung')
                        ->helperText('Pilih satu atau lebih dokumen dari pustaka dokumen SOP dinas:')
                        ->options(static::getMasterDokumenLibrary())
                        ->descriptions(static::getMasterDokumenLibrary())
                        ->searchable()
                        ->bulkToggleable()
                        ->columns(['default' => 1, 'md' => 2]),

                    TextInput::make('syarat_tambahan')
                        ->label('Atau Ketik Syarat Dokumen Baru Khusus (Opsional)')
                        ->placeholder('Contoh: Berita Acara Kerusakan Kendaraan')
                        ->helperText('Jika dokumen belum ada di daftar di atas, ketik nama dokumen di sini.'),
                ])
                ->action(function (array $data) {
                    $slug = Str::slug($data['nama_pekerjaan'], '_');
                    if (empty($slug)) {
                        $slug = 'pekerjaan_' . time();
                    }

                    $metode = $data['metode_pengadaan'] ?? 'non_katalog';
                    $selectedDocs = (array) ($data['syarat_dokumen'] ?? []);

                    if (! empty($data['syarat_tambahan'])) {
                        $newDocName = trim($data['syarat_tambahan']);
                        if (! in_array($newDocName, $selectedDocs, true)) {
                            $selectedDocs[] = $newDocName;
                        }
                    }

                    if (empty($selectedDocs)) {
                        Notification::make()
                            ->title('Peringatan')
                            ->body('Pilih minimal satu syarat dokumen untuk jenis pekerjaan ini.')
                            ->warning()
                            ->send();

                        return;
                    }

                    $masterDocs = static::getMasterDokumenLibrary();
                    $urutan = 1;

                    foreach ($selectedDocs as $docName) {
                        MasterSyaratBelanja::updateOrCreate(
                            [
                                'jenis_belanja' => $slug,
                                'metode_pengadaan' => $metode,
                                'nama_syarat' => $docName,
                            ],
                            [
                                'keterangan' => $masterDocs[$docName] ?? "Persyaratan dokumen untuk {$data['nama_pekerjaan']}",
                                'is_required' => true,
                                'urutan' => $urutan++,
                                'aktif' => true,
                            ]
                        );
                    }

                    $this->loadShieldData();

                    Notification::make()
                        ->title('Jenis Pekerjaan Berhasil Ditambahkan')
                        ->body("Pekerjaan \"{$data['nama_pekerjaan']}\" beserta " . count($selectedDocs) . ' syarat dokumen telah aktif.')
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('index'));
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
