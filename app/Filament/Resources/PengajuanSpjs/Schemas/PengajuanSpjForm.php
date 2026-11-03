<?php

declare(strict_types=1);

namespace App\Filament\Resources\PengajuanSpjs\Schemas;

use App\Models\Bidang;
use App\Models\KodeRekening;
use App\Models\MasterBank;
use App\Models\MasterBarang;
use App\Models\MasterKegiatan;
use App\Models\MasterSubKegiatan;
use App\Models\MasterToko;
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
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PengajuanSpjForm
{
    public static function configure(Schema $schema): Schema
    {
        // Helper closure: Ambil bidang_id dari level root maupun dari dalam Wizard/Section/Repeater
        $resolveBidangId = function (callable $get): ?int {
            $id = $get('/data.bidang_id') ?: $get('bidang_id') ?: $get('../../bidang_id');

            return filled($id) ? (int) $id : null;
        };

        // Helper closure: Apakah Surat Pesanan sudah disetujui & di-TTD?
        $isPesananDisetujui = fn (?PengajuanSpj $record): bool => $record !== null
            && ! in_array($record->status, ['draft', 'diajukan', 'ditolak'], true);

        // Helper closure: Apakah Kwitansi sudah diproses dan masuk tahap NPD / Verifikasi?
        $isKwitansiDiproses = fn (?PengajuanSpj $record): bool => $record !== null
            && ! in_array($record->status, ['draft', 'diajukan', 'menunggu_realisasi', 'ditolak'], true);

        return $schema
            ->components([
                Wizard::make([
                    // =========================================================================
                    // STEP 1: PENGAJUAN NOTA PESANAN / SURAT PESANAN
                    // =========================================================================
                    Step::make('1. Pengajuan Nota Pesanan')
                        ->icon('heroicon-o-document-text')
                        ->description('Data kegiatan, daftar barang pesanan, dan toko penyedia')
                        ->schema([
                            // 1. IDENTITAS KEGIATAN & SURAT PESANAN
                            Section::make('Identitas Kegiatan & Surat Pesanan')
                                ->description('Pilih Bidang Pengusul terlebih dahulu untuk memuat daftar Kegiatan dan Stok Barang.')
                                ->compact()
                                ->columns(6)
                                ->schema([
                                    TextInput::make('nomor_pengajuan')
                                        ->label('Nomor Surat Pesanan')
                                        ->default(fn () => PengajuanSpj::generateNomorPengajuan())
                                        ->required()
                                        ->readOnly()
                                        ->columnSpan(2),

                                    DatePicker::make('tanggal_pengajuan')
                                        ->label('Tanggal Surat Pesanan')
                                        ->default(now())
                                        ->displayFormat('d/m/Y')
                                        ->required()
                                        ->columnSpan(2),

                                    Select::make('bidang_id')
                                        ->label('Bidang Pengusul')
                                        ->placeholder('Pilih Bidang Pengusul terlebih dahulu')
                                        ->options(fn () => Bidang::pluck('nama', 'id')->all())
                                        ->searchable()
                                        ->live()
                                        ->afterStateUpdated(function (callable $set, callable $get) {
                                            $set('kegiatan_id', null);
                                            $set('sub_kegiatan_id', null);

                                            $details = $get('details') ?? [];
                                            foreach (array_keys($details) as $uuid) {
                                                $set("details.{$uuid}.kode_rekening_id", null);
                                                $set("details.{$uuid}.master_barang_id", null);
                                                $set("details.{$uuid}.stok_tersedia", 0);
                                                $set("details.{$uuid}.harga_satuan", 0);
                                                $set("details.{$uuid}.subtotal", 0);
                                            }
                                        })
                                        ->required()
                                        ->columnSpan(2),

                                    Select::make('kegiatan_id')
                                        ->label('Kegiatan')
                                        ->placeholder(fn (callable $get): string => $resolveBidangId($get)
                                            ? 'Pilih Kegiatan'
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
                                        ->createOptionForm([
                                            TextInput::make('kode_kegiatan')
                                                ->label('Kode Kegiatan')
                                                ->placeholder('Contoh: 2.19.02.2.01')
                                                ->required()
                                                ->maxLength(100),
                                            TextInput::make('nama_kegiatan')
                                                ->label('Nama Kegiatan')
                                                ->required()
                                                ->maxLength(255),
                                        ])
                                        ->createOptionUsing(function (array $data, callable $get) use ($resolveBidangId): int {
                                            return MasterKegiatan::create([
                                                'bidang_id' => $resolveBidangId($get),
                                                'kode_kegiatan' => $data['kode_kegiatan'],
                                                'nama_kegiatan' => $data['nama_kegiatan'],
                                            ])->id;
                                        })
                                        ->searchable()
                                        ->live()
                                        ->afterStateUpdated(fn (callable $set) => $set('sub_kegiatan_id', null))
                                        ->required()
                                        ->columnSpan(3),

                                    Select::make('sub_kegiatan_id')
                                        ->label('Sub Kegiatan')
                                        ->placeholder(fn (callable $get): string => filled($get('kegiatan_id'))
                                            ? 'Pilih Sub Kegiatan'
                                            : 'Pilih Kegiatan terlebih dahulu')
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
                                        ->createOptionForm([
                                            TextInput::make('kode_sub_kegiatan')
                                                ->label('Kode Sub Kegiatan')
                                                ->placeholder('Contoh: 2.19.02.2.01.0001')
                                                ->required()
                                                ->maxLength(100),
                                            TextInput::make('nama_sub_kegiatan')
                                                ->label('Nama Sub Kegiatan')
                                                ->required()
                                                ->maxLength(255),
                                        ])
                                        ->createOptionUsing(function (array $data, callable $get): int {
                                            return MasterSubKegiatan::create([
                                                'kegiatan_id' => (int) $get('kegiatan_id'),
                                                'kode_sub_kegiatan' => $data['kode_sub_kegiatan'],
                                                'nama_sub_kegiatan' => $data['nama_sub_kegiatan'],
                                            ])->id;
                                        })
                                        ->searchable()
                                        ->required()
                                        ->columnSpan(3),

                                    Hidden::make('user_id')
                                        ->default(fn () => Auth::id()),
                                ]),

                            // 2. RINCIAN BARANG PESANAN (Diletakkan di atas Data Toko karena lebih utama)
                            Section::make('Rincian Barang Pesanan')
                                ->description('Pilih barang sesuai stok yang tersedia pada Bidang Pengusul.')
                                ->compact()
                                ->schema([
                                    Repeater::make('details')
                                        ->relationship()
                                        ->label('Daftar Barang')
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
                                                ->columnSpan(5),

                                            Select::make('master_barang_id')
                                                ->label('Nama Barang')
                                                ->placeholder(function (callable $get) use ($resolveBidangId): string {
                                                    if (! $resolveBidangId($get)) {
                                                        return 'Pilih Bidang Pengusul terlebih dahulu';
                                                    }
                                                    if (blank($get('kode_rekening_id'))) {
                                                        return 'Pilih Rekening Belanja terlebih dahulu';
                                                    }

                                                    return 'Pilih Barang';
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
                                                                $barang->id => "🚫 {$barang->nama_barang} ({$satuan}) — [{$bidangNama} | Stok: 0]",
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
                                                ->columnSpan(7),

                                            TextInput::make('stok_tersedia')
                                                ->label('Stok')
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
                                                ->columnSpan(2),

                                            TextInput::make('kuantitas')
                                                ->label('Jumlah')
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
                                                            ->title('Melebihi Stok Tersedia')
                                                            ->body("Jumlah pesanan tidak boleh melebihi stok yang tersedia ({$stokTersedia}).")
                                                            ->send();
                                                    }

                                                    $harga = (float) ($get('harga_satuan') ?: 0);
                                                    $set('subtotal', $qty * $harga);
                                                    $set('kuantitas_realisasi', $qty);
                                                    $set('subtotal_realisasi', $qty * $harga);
                                                })
                                                ->required()
                                                ->columnSpan(2),

                                            TextInput::make('harga_satuan')
                                                ->label('Harga Satuan (Rp)')
                                                ->numeric()
                                                ->default(0)
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function (callable $set, callable $get) {
                                                    $qty = (int) ($get('kuantitas') ?: 0);
                                                    $harga = (float) ($get('harga_satuan') ?: 0);
                                                    $set('subtotal', $qty * $harga);
                                                })
                                                ->required()
                                                ->columnSpan(4),

                                            TextInput::make('subtotal')
                                                ->label('Subtotal (Rp)')
                                                ->numeric()
                                                ->default(0)
                                                ->readOnly()
                                                ->columnSpan(4),

                                            TextInput::make('kuantitas_realisasi')
                                                ->label('Jumlah Realisasi')
                                                ->numeric()
                                                ->default(1)
                                                ->visible($isPesananDisetujui)
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function (callable $set, callable $get) {
                                                    $qty = (int) ($get('kuantitas_realisasi') ?: 0);
                                                    $harga = (float) ($get('harga_realisasi') ?: 0);
                                                    $set('subtotal_realisasi', $qty * $harga);
                                                })
                                                ->columnSpan(4),

                                            TextInput::make('harga_realisasi')
                                                ->label('Harga Realisasi (Rp)')
                                                ->numeric()
                                                ->default(0)
                                                ->visible($isPesananDisetujui)
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function (callable $set, callable $get) {
                                                    $qty = (int) ($get('kuantitas_realisasi') ?: 0);
                                                    $harga = (float) ($get('harga_realisasi') ?: 0);
                                                    $set('subtotal_realisasi', $qty * $harga);
                                                })
                                                ->columnSpan(4),

                                            TextInput::make('subtotal_realisasi')
                                                ->label('Subtotal Realisasi (Rp)')
                                                ->numeric()
                                                ->default(0)
                                                ->visible($isPesananDisetujui)
                                                ->readOnly()
                                                ->columnSpan(4),
                                        ])
                                        ->columns(12)
                                        ->defaultItems(1)
                                        ->addActionLabel('Tambah Barang'),
                                ]),

                            // 3. DATA TOKO / REKANAN PENYEDIA (Paling Bawah pada Step 1 + Fitur Pilih/Tambah Toko & Bank Langsung)
                            Section::make('Data Toko / Rekanan Penyedia')
                                ->description('Pilih toko dari daftar tersimpan atau klik tombol (+) untuk menambah Toko / Bank baru tanpa berpindah halaman.')
                                ->compact()
                                ->columns(6)
                                ->schema([
                                    Select::make('master_toko_id')
                                        ->label('Pilih Toko / Pemasok')
                                        ->placeholder('Pilih Toko Tersimpan atau Klik (+) Tambah Baru')
                                        ->options(fn () => MasterToko::where('is_active', true)->get()->pluck('label', 'id')->all())
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->afterStateUpdated(function (callable $set, ?string $state) {
                                            if (! $state) {
                                                return;
                                            }
                                            $toko = MasterToko::find((int) $state);
                                            if ($toko) {
                                                $set('nama_toko', $toko->nama_toko);
                                                $set('penerima_nama', $toko->nama_pemilik);
                                                $set('telepon_toko', $toko->telepon);
                                                $set('alamat_toko', $toko->alamat);
                                                $set('master_bank_id', $toko->master_bank_id);
                                                $set('penerima_identitas', $toko->nomor_rekening);
                                            }
                                        })
                                        ->createOptionForm([
                                            Grid::make(2)
                                                ->schema([
                                                    TextInput::make('nama_toko')
                                                        ->label('Nama Toko / Pemasok')
                                                        ->placeholder('Contoh: Toko Rilex Abadi')
                                                        ->required()
                                                        ->maxLength(255),

                                                    TextInput::make('nama_pemilik')
                                                        ->label('Nama Pemilik Toko')
                                                        ->placeholder('Contoh: Agusrizal')
                                                        ->required()
                                                        ->maxLength(255),

                                                    TextInput::make('telepon')
                                                        ->label('No. Telepon Toko')
                                                        ->placeholder('Contoh: 0812-3456-7890')
                                                        ->maxLength(50),

                                                    Select::make('master_bank_id')
                                                        ->label('Nama Bank')
                                                        ->placeholder('Pilih Bank')
                                                        ->options(fn () => MasterBank::where('is_active', true)->pluck('nama_bank', 'id')->all())
                                                        ->searchable()
                                                        ->preload(),

                                                    TextInput::make('nomor_rekening')
                                                        ->label('No. Rekening Bank Toko')
                                                        ->placeholder('Contoh: 0700.0210.21699-2')
                                                        ->maxLength(100)
                                                        ->columnSpan(2),

                                                    Textarea::make('alamat')
                                                        ->label('Alamat Toko')
                                                        ->placeholder('Contoh: Pasar Inpres Muaro Sijunjung')
                                                        ->required()
                                                        ->rows(2)
                                                        ->columnSpan(2),
                                                ]),
                                        ])
                                        ->createOptionUsing(function (array $data, callable $set): int {
                                            $toko = MasterToko::create([
                                                'nama_toko' => $data['nama_toko'],
                                                'nama_pemilik' => $data['nama_pemilik'] ?? null,
                                                'telepon' => $data['telepon'] ?? null,
                                                'master_bank_id' => $data['master_bank_id'] ?? null,
                                                'nomor_rekening' => $data['nomor_rekening'] ?? null,
                                                'alamat' => $data['alamat'] ?? null,
                                                'is_active' => true,
                                            ]);

                                            $set('nama_toko', $toko->nama_toko);
                                            $set('penerima_nama', $toko->nama_pemilik);
                                            $set('telepon_toko', $toko->telepon);
                                            $set('alamat_toko', $toko->alamat);
                                            $set('master_bank_id', $toko->master_bank_id);
                                            $set('penerima_identitas', $toko->nomor_rekening);

                                            return $toko->id;
                                        })
                                        ->columnSpan(2),

                                    TextInput::make('nama_toko')
                                        ->label('Nama Toko / Pemasok')
                                        ->placeholder('Contoh: Toko Rilex Abadi')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(2),

                                    TextInput::make('penerima_nama')
                                        ->label('Nama Pemilik Toko')
                                        ->placeholder('Contoh: Agusrizal')
                                        ->required()
                                        ->maxLength(255)
                                        ->columnSpan(2),

                                    TextInput::make('telepon_toko')
                                        ->label('No. Telepon Toko')
                                        ->placeholder('Contoh: 0812-3456-7890')
                                        ->maxLength(50)
                                        ->columnSpan(2),

                                    Select::make('master_bank_id')
                                        ->label('Nama Bank')
                                        ->placeholder('Pilih Bank atau Klik (+)')
                                        ->options(fn () => MasterBank::where('is_active', true)->pluck('nama_bank', 'id')->all())
                                        ->searchable()
                                        ->preload()
                                        ->createOptionForm([
                                            TextInput::make('kode_bank')
                                                ->label('Kode Bank')
                                                ->placeholder('Contoh: 118')
                                                ->maxLength(20),
                                            TextInput::make('nama_bank')
                                                ->label('Nama Bank')
                                                ->placeholder('Contoh: Bank Nagari (BPD Sumbar)')
                                                ->required()
                                                ->maxLength(255),
                                        ])
                                        ->createOptionUsing(function (array $data): int {
                                            return MasterBank::create([
                                                'kode_bank' => $data['kode_bank'] ?? null,
                                                'nama_bank' => $data['nama_bank'],
                                                'is_active' => true,
                                            ])->id;
                                        })
                                        ->columnSpan(2),

                                    TextInput::make('penerima_identitas')
                                        ->label('No. Rekening Bank Toko')
                                        ->placeholder('Contoh: 0700.0210.21699-2')
                                        ->maxLength(100)
                                        ->columnSpan(2),

                                    DatePicker::make('tanggal_batas_pengiriman')
                                        ->label('Batas Waktu Pengiriman')
                                        ->default(fn () => now()->addDays(7))
                                        ->displayFormat('d/m/Y')
                                        ->columnSpan(2),

                                    TextInput::make('ppn_persen')
                                        ->label('Tarif PPN (%)')
                                        ->numeric()
                                        ->default(12)
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->suffix('%')
                                        ->columnSpan(2),

                                    TextInput::make('metode_pembayaran')
                                        ->label('Metode Pembayaran')
                                        ->default('Transfer Bank / Tempo 14 Hari setelah barang diterima')
                                        ->placeholder('Contoh: Transfer Bank / Tempo 14 Hari setelah barang diterima')
                                        ->maxLength(255)
                                        ->columnSpan(2),

                                    Textarea::make('alamat_toko')
                                        ->label('Alamat Toko')
                                        ->placeholder('Contoh: Pasar Inpres Muaro Sijunjung')
                                        ->required()
                                        ->rows(2)
                                        ->columnSpan(6),
                                ]),
                        ]),

                    // =========================================================================
                    // STEP 2: PEMBUATAN SURAT KWITANSI
                    // =========================================================================
                    Step::make('2. Pembuatan Surat Kwitansi')
                        ->icon('heroicon-o-receipt-percent')
                        ->description('Proses kwitansi pembayaran & unggah bukti faktur')
                        ->visible($isPesananDisetujui)
                        ->schema([
                            Section::make('Bukti & Rincian Kwitansi Pembayaran')
                                ->compact()
                                ->columns(2)
                                ->schema([
                                    Placeholder::make('nomor_kwitansi_preview')
                                        ->label('Nomor Kwitansi')
                                        ->content(fn (callable $get): string => ($get('nomor_pengajuan') ?: 'SP/...') . '/KW/' . date('Y')),

                                    Placeholder::make('status_ttd_pesanan')
                                        ->label('Status Surat Pesanan')
                                        ->content(function (?PengajuanSpj $record): string {
                                            $namaPptk = $record?->pptk?->name ?? 'PPTK';

                                            return "✅ Telah disetujui oleh {$namaPptk}";
                                        }),

                                    Placeholder::make('info_pemilik_toko_kwitansi')
                                        ->label('Penerima Pembayaran')
                                        ->content(fn (callable $get): string => ($get('nama_toko') ?: '-') . ' — ' . ($get('penerima_nama') ?: '-'))
                                        ->columnSpan(2),

                                    FileUpload::make('file_nota_penjual')
                                        ->label('Faktur / Nota Asli Toko')
                                        ->directory('spj/nota-penjual')
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                        ->maxSize(5120),

                                    FileUpload::make('file_dokumentasi_barang')
                                        ->label('Foto Dokumentasi Barang (Wajib jika > Rp 2 Jt)')
                                        ->directory('spj/dokumentasi-barang')
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                        ->maxSize(5120),

                                    Textarea::make('justifikasi_selisih')
                                        ->label('Catatan / Justifikasi Selisih (Diisi bila nilai realisasi berbeda dengan pesanan)')
                                        ->rows(2)
                                        ->columnSpan(2),

                                    Placeholder::make('ringkasan_nominal_kwitansi')
                                        ->label('Total Nominal & Terbilang Kwitansi')
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
                    // STEP 3: PEMBUATAN NPD & SPESIMEN TTD PEJABAT
                    // =========================================================================
                    Step::make('3. Pembuatan NPD')
                        ->icon('heroicon-o-banknotes')
                        ->description('Nota Pencairan Dana (NPD), spesimen TTD, & pencairan kas')
                        ->visible($isKwitansiDiproses)
                        ->schema([
                            Section::make('Rincian Nota Pencairan Dana (NPD)')
                                ->compact()
                                ->columns(2)
                                ->schema([
                                    Placeholder::make('nomor_npd_preview')
                                        ->label('Nomor NPD')
                                        ->content(fn (callable $get): string => ($get('nomor_pengajuan') ?: 'SP/...') . '/NPD/' . date('Y')),

                                    Placeholder::make('status_spj_aktif')
                                        ->label('Status SPJ')
                                        ->content(fn (?PengajuanSpj $record): string => $record ? $record->status_label : 'Draft'),

                                    Placeholder::make('total_rencana_npd')
                                        ->label('Total Nilai Pesanan')
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
                                        ->label('Total Nilai Pencairan (NPD)')
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
                                        ->label('Nomor BKU')
                                        ->placeholder('Diisi Bendahara saat pencairan')
                                        ->visible(fn (?PengajuanSpj $record): bool => $record !== null
                                            && in_array($record->status, ['menunggu_verifikasi_bendahara', 'revisi_bendahara', 'disetujui', 'dicairkan'], true))
                                        ->maxLength(100),

                                    DatePicker::make('tanggal_dicairkan')
                                        ->label('Tanggal Pencairan')
                                        ->visible(fn (?PengajuanSpj $record): bool => $record !== null
                                            && in_array($record->status, ['menunggu_verifikasi_bendahara', 'revisi_bendahara', 'disetujui', 'dicairkan'], true))
                                        ->displayFormat('d/m/Y'),

                                    Placeholder::make('matrix_penandatangan_npd')
                                        ->label('Pejabat Penandatangan')
                                        ->content(function (?PengajuanSpj $record): string {
                                            if (! $record) {
                                                return 'PPTK: Menunggu | PPK: Menunggu | PA/KPA: Menunggu | Bendahara: Menunggu';
                                            }

                                            $pptk = $record->pptk?->name ?? 'Belum TTD';
                                            $ppk = $record->ppk?->name ?? 'Belum Verifikasi';
                                            $pa = $record->pa?->name ?? 'Belum TTD';
                                            $bendahara = $record->bendahara?->name ?? 'Belum Cair';

                                            return "PPTK: {$pptk}  |  PPK: {$ppk}  |  PA/KPA: {$pa}  |  Bendahara: {$bendahara}";
                                        })
                                        ->columnSpan(2),
                                ]),

                            Section::make('Spesimen Tanda Tangan Pejabat (Non-TTE)')
                                ->description('Unggah gambar tanda tangan (PNG/JPG) untuk dicetak pada dokumen PDF.')
                                ->compact()
                                ->columns(2)
                                ->collapsed()
                                ->schema([
                                    FileUpload::make('ttd_pptk')
                                        ->label('TTD PPTK')
                                        ->directory('spj/ttd')
                                        ->image()
                                        ->maxSize(2048),

                                    FileUpload::make('ttd_ppk')
                                        ->label('TTD PPK')
                                        ->directory('spj/ttd')
                                        ->image()
                                        ->maxSize(2048),

                                    FileUpload::make('ttd_pa')
                                        ->label('TTD PA / KPA')
                                        ->directory('spj/ttd')
                                        ->image()
                                        ->maxSize(2048),

                                    FileUpload::make('ttd_bendahara')
                                        ->label('TTD Bendahara')
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
