<?php

declare(strict_types=1);

namespace App\Filament\Resources\PengajuanSpjs\Schemas;

use App\Models\Bidang;
use App\Models\KodeRekening;
use App\Models\MasterBarang;
use App\Models\MasterKegiatan;
use App\Models\MasterSubKegiatan;
use App\Models\PengajuanSpj;
use App\Models\StokBarang;
use App\Services\Spj\SpjWorkflowService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PengajuanSpjForm
{
    public static function configure(Schema $schema): Schema
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        // Helper closure: Ambil bidang_id secara akurat dari level root maupun dari dalam Wizard/Section/Repeater
        $resolveBidangId = function (callable $get): ?int {
            $id = $get('/data.bidang_id') ?: $get('bidang_id') ?: $get('../../bidang_id');

            return filled($id) ? (int) $id : null;
        };

        // Helper closure: Apakah Surat Pesanan sudah disetujui & di-TTD oleh Kabid (PPTK)?
        $isPesananDisetujui = fn (?PengajuanSpj $record): bool => $record !== null
            && ! in_array($record->status, ['draft', 'diajukan', 'ditolak'], true);

        // Helper closure: Apakah Kwitansi sudah diproses dan masuk tahap NPD / Verifikasi Pejabat?
        $isKwitansiDiproses = fn (?PengajuanSpj $record): bool => $record !== null
            && ! in_array($record->status, ['draft', 'diajukan', 'menunggu_realisasi', 'ditolak'], true);

        return $schema
            ->components([
                Wizard::make([
                    // =========================================================================
                    // STEP 1: PENGAJUAN NOTA PESANAN / SURAT PESANAN (SELALU TAMPIL)
                    // =========================================================================
                    Step::make('1. Pengajuan Nota Pesanan')
                        ->icon('heroicon-o-document-text')
                        ->description('Ajukan Surat / Nota Pesanan ke Kabid (PPTK) sesuai Kegiatan & Stok Barang Bidang')
                        ->schema([
                            Section::make('Identitas Kegiatan & Surat Pesanan')
                                ->description('Pilih Bidang Pengusul terlebih dahulu agar Kegiatan DPA dan Stok Barang otomatis menyesuaikan dengan Bidang terkait.')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('nomor_pengajuan')
                                        ->label('Nomor Pengajuan / Surat Pesanan')
                                        ->default(fn () => PengajuanSpj::generateNomorPengajuan())
                                        ->helperText(fn (callable $get): string => 'Format Surat Pesanan: ' . ($get('nomor_pengajuan') ?: 'SPJ/...') . '/NP/' . date('Y'))
                                        ->required()
                                        ->readOnly(),

                                    DatePicker::make('tanggal_pengajuan')
                                        ->label('Tanggal Surat Pesanan')
                                        ->default(now())
                                        ->displayFormat('d/m/Y')
                                        ->required(),

                                    Select::make('bidang_id')
                                        ->label('Bidang Pengusul (PPTK: Kepala Bidang)')
                                        ->options(fn () => Bidang::pluck('nama', 'id')->all())
                                        ->default(fn () => $user?->bidang_id ?: Bidang::query()->value('id'))
                                        ->searchable()
                                        ->live()
                                        ->afterStateUpdated(function (callable $set, callable $get) {
                                            // Reset Kegiatan & Sub Kegiatan ketika Bidang berubah
                                            $set('kegiatan_id', null);
                                            $set('sub_kegiatan_id', null);

                                            // Reset pilihan barang pada repeater agar selaras dengan stok Bidang baru
                                            $details = $get('details') ?? [];
                                            foreach (array_keys($details) as $uuid) {
                                                $set("details.{$uuid}.master_barang_id", null);
                                                $set("details.{$uuid}.stok_tersedia", 0);
                                                $set("details.{$uuid}.harga_satuan", 0);
                                                $set("details.{$uuid}.subtotal", 0);
                                            }
                                        })
                                        ->required()
                                        ->columnSpan(2),

                                    Select::make('kegiatan_id')
                                        ->label('Kegiatan DPA (Sesuai Bidang)')
                                        ->placeholder(fn (callable $get): string => $resolveBidangId($get)
                                            ? 'Pilih Kegiatan DPA Bidang'
                                            : 'Pilih Bidang Pengusul terlebih dahulu')
                                        ->disabled(fn (callable $get): bool => $resolveBidangId($get) === null)
                                        ->options(function (callable $get) use ($resolveBidangId): array {
                                            $bidangId = $resolveBidangId($get);
                                            if (! $bidangId) {
                                                return [];
                                            }

                                            return MasterKegiatan::where('bidang_id', $bidangId)
                                                ->get()
                                                ->pluck('label', 'id')
                                                ->all();
                                        })
                                        ->searchable()
                                        ->live()
                                        ->afterStateUpdated(fn (callable $set) => $set('sub_kegiatan_id', null))
                                        ->required()
                                        ->columnSpan(1),

                                    Select::make('sub_kegiatan_id')
                                        ->label('Sub Kegiatan DPA')
                                        ->placeholder(fn (callable $get): string => filled($get('kegiatan_id'))
                                            ? 'Pilih Sub Kegiatan DPA'
                                            : 'Pilih Kegiatan DPA terlebih dahulu')
                                        ->disabled(fn (callable $get): bool => blank($get('kegiatan_id')))
                                        ->options(function (callable $get): array {
                                            $kegiatanId = $get('kegiatan_id');
                                            if (! $kegiatanId) {
                                                return [];
                                            }

                                            return MasterSubKegiatan::where('kegiatan_id', $kegiatanId)
                                                ->get()
                                                ->pluck('label', 'id')
                                                ->all();
                                        })
                                        ->searchable()
                                        ->required()
                                        ->columnSpan(1),

                                    Hidden::make('user_id')
                                        ->default(fn () => Auth::id()),
                                ]),

                            // SECTION TERPISAH KHUSUS DATA TOKO & PEMILIK TOKO
                            Section::make('Data Toko / Rekanan Penyedia Barang')
                                ->description('Informasi toko tujuan Surat Pesanan dan nama pemilik toko yang akan menandatangani Surat Pesanan serta Kwitansi Pembayaran.')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('nama_toko')
                                        ->label('Nama Toko / Rekanan Penyedia')
                                        ->placeholder('Contoh: Toko ATK Sinar Jaya')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(1),

                                    TextInput::make('penerima_nama')
                                        ->label('Nama Pemilik Toko (Untuk TTD Kwitansi & Pesanan)')
                                        ->placeholder('Contoh: H. Budi Santoso')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(1),

                                    TextInput::make('penerima_identitas')
                                        ->label('Jabatan / NPWP / No. HP Pemilik Toko')
                                        ->placeholder('Contoh: Pemilik Toko / NPWP: 01.234.567.8')
                                        ->maxLength(100)
                                        ->columnSpan(1),

                                    Textarea::make('alamat_toko')
                                        ->label('Alamat Lengkap Toko / Rekanan')
                                        ->placeholder('Contoh: Jl. Jenderal Sudirman No. 45, Kompleks Pertokoan Blok B No. 3')
                                        ->required()
                                        ->rows(2)
                                        ->columnSpan(3),
                                ]),

                            Section::make('Rincian Barang Nota Pesanan (Terkoneksi dengan Stok Bidang)')
                                ->description('Daftar barang terhubung langsung dengan data stok Bidang Pengusul. Barang yang stoknya kosong (0) pada Bidang tersebut dikunci dan tidak dapat dipilih.')
                                ->schema([
                                    Repeater::make('details')
                                        ->relationship()
                                        ->label('Daftar Barang yang Dipesan')
                                        ->schema([
                                            Select::make('kode_rekening_id')
                                                ->label('Rekening Belanja')
                                                ->placeholder(fn (callable $get): string => $resolveBidangId($get)
                                                    ? 'Pilih Rekening Belanja'
                                                    : 'Pilih Bidang Pengusul terlebih dahulu')
                                                ->disabled(fn (callable $get): bool => $resolveBidangId($get) === null)
                                                ->options(function (callable $get) use ($resolveBidangId): array {
                                                    $bidangId = $resolveBidangId($get);
                                                    if (! $bidangId) {
                                                        return [];
                                                    }

                                                    return KodeRekening::whereHas('masterBarangs')
                                                        ->get()
                                                        ->pluck('label', 'id')
                                                        ->all();
                                                })
                                                ->searchable()
                                                ->live()
                                                ->afterStateUpdated(function (callable $set) {
                                                    $set('master_barang_id', null);
                                                    $set('stok_tersedia', 0);
                                                    $set('harga_satuan', 0);
                                                    $set('subtotal', 0);
                                                })
                                                ->required()
                                                ->columnSpan(3),

                                            Select::make('master_barang_id')
                                                ->label('Pilih Barang Persediaan Bidang')
                                                ->placeholder(function (callable $get) use ($resolveBidangId): string {
                                                    if (! $resolveBidangId($get)) {
                                                        return 'Pilih Bidang Pengusul terlebih dahulu';
                                                    }
                                                    if (blank($get('kode_rekening_id'))) {
                                                        return 'Pilih Rekening Belanja terlebih dahulu';
                                                    }

                                                    return 'Pilih Barang (Barang stok 0 tidak dapat dipilih)';
                                                })
                                                ->disabled(fn (callable $get): bool => $resolveBidangId($get) === null || blank($get('kode_rekening_id')))
                                                ->options(function (callable $get) use ($resolveBidangId): array {
                                                    $bidangId = $resolveBidangId($get);
                                                    $kodeRekeningId = $get('kode_rekening_id');

                                                    if (! $bidangId || ! $kodeRekeningId) {
                                                        return [];
                                                    }

                                                    $bidangNama = Bidang::whereKey($bidangId)->value('nama') ?? 'Bidang';

                                                    $barangs = MasterBarang::with([
                                                        'satuan',
                                                        'stokBarangs' => fn ($q) => $q->where('bidang_id', $bidangId),
                                                    ])
                                                        ->where('kode_rekening_id', $kodeRekeningId)
                                                        ->get();

                                                    return $barangs->mapWithKeys(function (MasterBarang $barang) use ($bidangNama) {
                                                        $stok = (int) $barang->stokBarangs->sum('stok_tersedia');
                                                        $satuan = $barang->satuan?->nama ?? 'Unit';
                                                        $harga = number_format((float) $barang->harga_satuan, 0, ',', '.');

                                                        if ($stok <= 0) {
                                                            return [
                                                                $barang->id => "🚫 {$barang->nama_barang} ({$satuan}) — [{$bidangNama} | STOK KOSONG: 0]",
                                                            ];
                                                        }

                                                        return [
                                                            $barang->id => "{$barang->nama_barang} ({$satuan}) — [{$bidangNama} | Stok: {$stok}] — Rp {$harga}",
                                                        ];
                                                    })->all();
                                                })
                                                ->disableOptionWhen(function (string $value, callable $get) use ($resolveBidangId): bool {
                                                    $bidangId = $resolveBidangId($get);
                                                    if (! $bidangId) {
                                                        return true;
                                                    }

                                                    $stok = (int) StokBarang::where('bidang_id', $bidangId)
                                                        ->where('master_barang_id', (int) $value)
                                                        ->sum('stok_tersedia');

                                                    return $stok <= 0;
                                                })
                                                ->searchable()
                                                ->live()
                                                ->afterStateUpdated(function (callable $set, callable $get, ?string $state) use ($resolveBidangId) {
                                                    if ($state) {
                                                        $bidangId = $resolveBidangId($get);
                                                        $barang = MasterBarang::with('satuan')->find($state);
                                                        if ($barang && $bidangId) {
                                                            $stokTersedia = (int) StokBarang::where('bidang_id', $bidangId)
                                                                ->where('master_barang_id', (int) $state)
                                                                ->sum('stok_tersedia');

                                                            if ($stokTersedia <= 0) {
                                                                $set('master_barang_id', null);
                                                                $set('stok_tersedia', 0);

                                                                Notification::make()
                                                                    ->danger()
                                                                    ->title('Stok Barang Kosong')
                                                                    ->body('Barang ini memiliki stok 0 pada Bidang terpilih sehingga tidak dapat diajukan.')
                                                                    ->send();

                                                                return;
                                                            }

                                                            $harga = (float) $barang->harga_satuan;
                                                            $qty = min((int) ($get('kuantitas') ?: 1), $stokTersedia);

                                                            $set('stok_tersedia', $stokTersedia);
                                                            $set('kuantitas', $qty);
                                                            $set('harga_satuan', $harga);
                                                            $set('subtotal', $qty * $harga);
                                                            $set('kuantitas_realisasi', $qty);
                                                            $set('harga_realisasi', $harga);
                                                            $set('subtotal_realisasi', $qty * $harga);
                                                        }
                                                    } else {
                                                        $set('stok_tersedia', 0);
                                                        $set('harga_satuan', 0);
                                                        $set('subtotal', 0);
                                                    }
                                                })
                                                ->required()
                                                ->columnSpan(3),

                                            TextInput::make('stok_tersedia')
                                                ->label('Stok Tersedia')
                                                ->disabled()
                                                ->dehydrated(false)
                                                ->default(0)
                                                ->formatStateUsing(function (callable $get) use ($resolveBidangId): int {
                                                    $barangId = $get('master_barang_id');
                                                    $bidangId = $resolveBidangId($get);
                                                    if (! $barangId || ! $bidangId) {
                                                        return 0;
                                                    }

                                                    return (int) StokBarang::where('bidang_id', $bidangId)
                                                        ->where('master_barang_id', (int) $barangId)
                                                        ->sum('stok_tersedia');
                                                })
                                                ->columnSpan(1),

                                            TextInput::make('kuantitas')
                                                ->label('Vol Pesanan')
                                                ->numeric()
                                                ->default(1)
                                                ->minValue(1)
                                                ->maxValue(function (callable $get): int {
                                                    $stok = (int) ($get('stok_tersedia') ?: 0);

                                                    return $stok > 0 ? $stok : 1;
                                                })
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                                    $stokTersedia = (int) ($get('stok_tersedia') ?: 0);
                                                    $qty = max(1, (int) ($state ?: 1));

                                                    if ($stokTersedia > 0 && $qty > $stokTersedia) {
                                                        $qty = $stokTersedia;
                                                        $set('kuantitas', $qty);

                                                        Notification::make()
                                                            ->danger()
                                                            ->title('Melebihi Stok Tersedia Bidang')
                                                            ->body("Volume pesanan tidak boleh melebihi stok barang yang tersedia pada bidang ini ({$stokTersedia}).")
                                                            ->send();
                                                    }

                                                    $harga = (float) ($get('harga_satuan') ?: 0);
                                                    $set('subtotal', $qty * $harga);
                                                    $set('kuantitas_realisasi', $qty);
                                                    $set('subtotal_realisasi', $qty * $harga);
                                                })
                                                ->required()
                                                ->columnSpan(1),

                                            TextInput::make('harga_satuan')
                                                ->label('Harga Pesanan (Rp)')
                                                ->numeric()
                                                ->default(0)
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function (callable $set, callable $get) {
                                                    $qty = (int) ($get('kuantitas') ?: 0);
                                                    $harga = (float) ($get('harga_satuan') ?: 0);
                                                    $set('subtotal', $qty * $harga);
                                                })
                                                ->required()
                                                ->columnSpan(2),

                                            TextInput::make('subtotal')
                                                ->label('Subtotal Pesanan (Rp)')
                                                ->numeric()
                                                ->default(0)
                                                ->readOnly()
                                                ->columnSpan(2),

                                            // Kolom Realisasi Kwitansi HANYA TAMPIL setelah Surat Pesanan disetujui & di-TTD Kabid (PPTK)
                                            TextInput::make('kuantitas_realisasi')
                                                ->label('Vol Realisasi Kwitansi')
                                                ->numeric()
                                                ->default(1)
                                                ->visible($isPesananDisetujui)
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function (callable $set, callable $get) {
                                                    $qty = (int) ($get('kuantitas_realisasi') ?: 0);
                                                    $harga = (float) ($get('harga_realisasi') ?: 0);
                                                    $set('subtotal_realisasi', $qty * $harga);
                                                })
                                                ->columnSpan(2),

                                            TextInput::make('harga_realisasi')
                                                ->label('Harga Aktual Kwitansi (Rp)')
                                                ->numeric()
                                                ->default(0)
                                                ->visible($isPesananDisetujui)
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function (callable $set, callable $get) {
                                                    $qty = (int) ($get('kuantitas_realisasi') ?: 0);
                                                    $harga = (float) ($get('harga_realisasi') ?: 0);
                                                    $set('subtotal_realisasi', $qty * $harga);
                                                })
                                                ->columnSpan(2),

                                            TextInput::make('subtotal_realisasi')
                                                ->label('Subtotal Kwitansi (Rp)')
                                                ->numeric()
                                                ->default(0)
                                                ->visible($isPesananDisetujui)
                                                ->readOnly()
                                                ->columnSpan(2),
                                        ])
                                        ->columns(6)
                                        ->defaultItems(1)
                                        ->addActionLabel('Tambah Item Barang (Stok Bidang)'),
                                ]),
                        ]),

                    // =========================================================================
                    // STEP 2: PEMBUATAN SURAT KWITANSI (TAMPIL SETELAH NOTA PESANAN DISETUJUI KABID)
                    // =========================================================================
                    Step::make('2. Pembuatan Surat Kwitansi')
                        ->icon('heroicon-o-receipt-percent')
                        ->description('Proses Surat Kwitansi setelah Nota Pesanan di-TTD Kabid (PPTK) & unggah bukti faktur')
                        ->visible($isPesananDisetujui)
                        ->schema([
                            Section::make('Identitas & Bukti Surat Kwitansi Pembayaran')
                                ->description('Surat Kwitansi Pembayaran diproses setelah Surat Pesanan ditandatangani oleh Kepala Bidang selaku PPTK. Unggah faktur asli penjual dan dokumentasi barang.')
                                ->columns(2)
                                ->schema([
                                    Placeholder::make('nomor_kwitansi_preview')
                                        ->label('Nomor Bukti Kas / Surat Kwitansi')
                                        ->content(fn (callable $get): string => ($get('nomor_pengajuan') ?: 'SPJ/...') . '/KW/' . date('Y')),

                                    Placeholder::make('status_ttd_pesanan')
                                        ->label('Status TTD Surat Pesanan (Kabid / PPTK)')
                                        ->content(function (?PengajuanSpj $record): string {
                                            $namaPptk = $record?->pptk?->name ?? 'Kepala Bidang (PPTK)';

                                            return "✅ Surat Pesanan Telah Di-TTD oleh {$namaPptk} — Kwitansi Siap Diproses";
                                        }),

                                    Placeholder::make('info_pemilik_toko_kwitansi')
                                        ->label('Toko & Nama Pemilik Toko (Penandatangan Kwitansi)')
                                        ->content(fn (callable $get): string => ($get('nama_toko') ?: '-') . ' — Pemilik/TTD: ' . ($get('penerima_nama') ?: '-'))
                                        ->columnSpan(2),

                                    FileUpload::make('file_nota_penjual')
                                        ->label('Unggah Faktur / Nota Asli Toko (Dasar Surat Kwitansi)')
                                        ->directory('spj/nota-penjual')
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                        ->maxSize(5120),

                                    FileUpload::make('file_dokumentasi_barang')
                                        ->label('Unggah Foto Dokumentasi Fisik Barang (Wajib jika > Rp 2 Jt)')
                                        ->directory('spj/dokumentasi-barang')
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                        ->maxSize(5120),

                                    Textarea::make('justifikasi_selisih')
                                        ->label('Catatan Penjelasan / Justifikasi Selisih (Wajib diisi bila nilai Kwitansi berbeda dengan Surat Pesanan)')
                                        ->rows(2)
                                        ->columnSpan(2),

                                    Placeholder::make('ringkasan_nominal_kwitansi')
                                        ->label('Nominal & Terbilang Kwitansi')
                                        ->content(function (callable $get, ?PengajuanSpj $record): string {
                                            $details = $get('details') ?? [];
                                            $totalRealisasi = 0;
                                            $totalRencana = 0;
                                            foreach ($details as $item) {
                                                $totalRencana += (float) ($item['subtotal'] ?? 0);
                                                $totalRealisasi += (float) ($item['subtotal_realisasi'] ?? 0);
                                            }
                                            if ($totalRencana <= 0 && $record) {
                                                $totalRencana = (float) $record->total_nilai_rencana;
                                                $totalRealisasi = (float) ($record->total_nilai_realisasi ?: $record->total_nilai_rencana);
                                            }
                                            $nominalAktif = $totalRealisasi > 0 ? $totalRealisasi : $totalRencana;
                                            $terbilang = $nominalAktif > 0 ? SpjWorkflowService::terbilang($nominalAktif) : 'Nol Rupiah';

                                            return 'Rp ' . number_format($nominalAktif, 0, ',', '.') . ',- (' . $terbilang . ')';
                                        })
                                        ->columnSpan(2),
                                ]),
                        ]),

                    // =========================================================================
                    // STEP 3: PEMBUATAN NPD & SPESIMEN TTD PEJABAT (TAMPIL SETELAH KWITANSI DIPROSES)
                    // =========================================================================
                    Step::make('3. Pembuatan NPD')
                        ->icon('heroicon-o-banknotes')
                        ->description('Nota Pencairan Dana (NPD), Spesimen TTD Pejabat (Non-TTE), & Pencairan Kas')
                        ->visible($isKwitansiDiproses)
                        ->schema([
                            Section::make('Rincian Nota Pencairan Dana (NPD) & Realisasi Kas Daerah')
                                ->description('Nota Pencairan Dana (NPD) diajukan oleh PPTK (Kepala Bidang) kepada Pengguna Anggaran / KPA sebagai dasar pencairan kas oleh Bendahara Pengeluaran.')
                                ->columns(2)
                                ->schema([
                                    Placeholder::make('nomor_npd_preview')
                                        ->label('Nomor Nota Pencairan Dana (NPD)')
                                        ->content(fn (callable $get): string => ($get('nomor_pengajuan') ?: 'SPJ/...') . '/NPD/' . date('Y')),

                                    Placeholder::make('status_spj_aktif')
                                        ->label('Status Tahapan Dokumen SPJ')
                                        ->content(fn (?PengajuanSpj $record): string => $record ? $record->status_label : 'Draft Surat Pesanan Baru'),

                                    Placeholder::make('total_rencana_npd')
                                        ->label('Total Nilai Nota Pesanan (Rencana)')
                                        ->content(function (callable $get, ?PengajuanSpj $record): string {
                                            $details = $get('details') ?? [];
                                            $total = 0;
                                            foreach ($details as $item) {
                                                $total += (float) ($item['subtotal'] ?? 0);
                                            }
                                            if ($total <= 0 && $record) {
                                                $total = (float) $record->total_nilai_rencana;
                                            }

                                            return 'Rp ' . number_format($total, 0, ',', '.') . ',-';
                                        }),

                                    Placeholder::make('total_pencairan_npd')
                                        ->label('Total Dana Dimohon pada NPD & Kwitansi (Realisasi)')
                                        ->content(function (callable $get, ?PengajuanSpj $record): string {
                                            $details = $get('details') ?? [];
                                            $total = 0;
                                            foreach ($details as $item) {
                                                $total += (float) ($item['subtotal_realisasi'] ?? 0);
                                            }
                                            if ($total <= 0 && $record) {
                                                $total = (float) ($record->total_nilai_realisasi ?: $record->total_nilai_rencana);
                                            }

                                            return 'Rp ' . number_format($total, 0, ',', '.') . ',-';
                                        }),

                                    TextInput::make('nomor_bku')
                                        ->label('Nomor Buku Kas Umum (BKU)')
                                        ->placeholder('Diisi Bendahara Pengeluaran saat pencairan dana')
                                        ->visible(fn (?PengajuanSpj $record): bool => $record !== null
                                            && in_array($record->status, ['menunggu_verifikasi_bendahara', 'revisi_bendahara', 'disetujui', 'dicairkan'], true))
                                        ->maxLength(100),

                                    DatePicker::make('tanggal_dicairkan')
                                        ->label('Tanggal Pencairan Kas (Lunas Dibayar)')
                                        ->visible(fn (?PengajuanSpj $record): bool => $record !== null
                                            && in_array($record->status, ['menunggu_verifikasi_bendahara', 'revisi_bendahara', 'disetujui', 'dicairkan'], true))
                                        ->displayFormat('d/m/Y'),

                                    Placeholder::make('matrix_penandatangan_npd')
                                        ->label('Otorisasi Pejabat Penandatangan (Surat Pesanan, Kwitansi, Belakang Kwitansi & NPD)')
                                        ->content(function (?PengajuanSpj $record): string {
                                            if (! $record) {
                                                return '1. PPTK (Kabid): Menunggu Pengajuan | 2. PPK (Kasubag Keu): Menunggu | 3. PA/KPA (Kadis): Menunggu | 4. Bendahara: Menunggu';
                                            }

                                            $pptk = $record->pptk?->name ?? 'Belum TTD';
                                            $ppk = $record->ppk?->name ?? 'Belum Verifikasi';
                                            $pa = $record->pa?->name ?? 'Belum TTD';
                                            $bendahara = $record->bendahara?->name ?? 'Belum Cair';

                                            return "PPTK (Kabid): {$pptk}  |  PPK: {$ppk}  |  PA/KPA (Kadis): {$pa}  |  Bendahara: {$bendahara}";
                                        })
                                        ->columnSpan(2),
                                ]),

                            Section::make('Unggah Spesimen Tanda Tangan Pejabat (PPTK, PPK, PA/KPA, Bendahara)')
                                ->description('Karena Tanda Tangan Elektronik (TTE) belum digunakan, unggah gambar contoh/spesimen tanda tangan (PNG/JPG) untuk ditampilkan pada cetakan PDF Surat Pesanan, Kwitansi, Belakang Kwitansi, dan NPD.')
                                ->columns(2)
                                ->collapsed()
                                ->schema([
                                    FileUpload::make('ttd_pptk')
                                        ->label('Spesimen TTD PPTK (Kepala Bidang)')
                                        ->directory('spj/ttd')
                                        ->image()
                                        ->maxSize(2048),

                                    FileUpload::make('ttd_ppk')
                                        ->label('Spesimen TTD PPK (Pejabat Penatausahaan Keuangan)')
                                        ->directory('spj/ttd')
                                        ->image()
                                        ->maxSize(2048),

                                    FileUpload::make('ttd_pa')
                                        ->label('Spesimen TTD Pengguna Anggaran / KPA (Kepala Dinas)')
                                        ->directory('spj/ttd')
                                        ->image()
                                        ->maxSize(2048),

                                    FileUpload::make('ttd_bendahara')
                                        ->label('Spesimen TTD Bendahara Pengeluaran')
                                        ->directory('spj/ttd')
                                        ->image()
                                        ->maxSize(2048),
                                ]),
                        ]),
                ])
                    ->columnSpanFull()
                    ->skippable()
                    ->persistStepInQueryString()
                    ->startOnStep(function (?PengajuanSpj $record): int {
                        if (! $record || in_array($record->status, ['draft', 'diajukan'], true)) {
                            return 1;
                        }
                        if (in_array($record->status, ['menunggu_realisasi', 'revisi_pptk'], true)) {
                            return 2;
                        }

                        return 3;
                    }),
            ]);
    }
}
