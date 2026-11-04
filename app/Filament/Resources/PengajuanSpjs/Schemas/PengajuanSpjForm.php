<?php

declare(strict_types=1);

namespace App\Filament\Resources\PengajuanSpjs\Schemas;

use App\Enums\NamaBank;
use App\Models\Bidang;
use App\Models\DetailPengajuanSpj;
use App\Models\KodeRekening;
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
        // Helper closure: Ambil bidang_id otomatis dari Staf atau Kepala Bidang yang sedang login
        $resolveUserBidangId = function (): ?int {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            if (! $user) {
                return null;
            }

            if (filled($user->bidang_id)) {
                return (int) $user->bidang_id;
            }

            $kepalaBidangId = Bidang::where('kepala_id', $user->id)->value('id');

            return $kepalaBidangId ? (int) $kepalaBidangId : null;
        };

        // Helper closure: Ambil bidang_id dari level root maupun dari dalam Wizard/Section/Repeater (fallback ke bidang user login)
        $resolveBidangId = function (callable $get) use ($resolveUserBidangId): ?int {
            $id = $get('/data.bidang_id') ?: $get('bidang_id') ?: $get('../../bidang_id');

            return filled($id) ? (int) $id : $resolveUserBidangId();
        };

        // Helper closure: Ambil parent record PengajuanSpj baik dari level root maupun dari dalam Repeater DetailPengajuanSpj
        $resolveSpjRecord = function (mixed $record = null, mixed $livewire = null): ?PengajuanSpj {
            if ($record instanceof PengajuanSpj) {
                return $record;
            }
            if ($record instanceof DetailPengajuanSpj) {
                return $record->pengajuanSpj;
            }
            if (is_object($livewire) && method_exists($livewire, 'getRecord')) {
                $parent = $livewire->getRecord();
                if ($parent instanceof PengajuanSpj) {
                    return $parent;
                }
            }

            return null;
        };

        // Helper closure: Apakah Surat Pesanan sudah disetujui & di-TTD?
        $isPesananDisetujui = function (mixed $record = null, mixed $livewire = null) use ($resolveSpjRecord): bool {
            $spj = $resolveSpjRecord($record, $livewire);

            return $spj !== null
                && ! in_array($spj->status, ['draft', 'diajukan', 'ditolak'], true);
        };

        // Helper closure: Apakah Kwitansi sudah diproses dan masuk tahap NPD / Verifikasi?
        $isKwitansiDiproses = function (mixed $record = null, mixed $livewire = null) use ($resolveSpjRecord): bool {
            $spj = $resolveSpjRecord($record, $livewire);

            return $spj !== null
                && ! in_array($spj->status, ['draft', 'diajukan', 'menunggu_realisasi', 'ditolak'], true);
        };

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
                                ->description('Bidang Pengusul terisi otomatis sesuai keberadaan Staf atau Kepala Bidang yang mengakses.')
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

                                    Select::make('tahun_anggaran')
                                        ->label('Tahun Anggaran')
                                        ->options([
                                            2024 => '2024',
                                            2025 => '2025',
                                            2026 => '2026',
                                            2027 => '2027',
                                            2028 => '2028',
                                        ])
                                        ->default(fn () => (int) (request()->query('tahun') ?? date('Y')))
                                        ->required()
                                        ->live()
                                        ->columnSpan(2),

                                    Select::make('bidang_id')
                                        ->label('Bidang Pengusul')
                                        ->placeholder('Pilih Bidang Pengusul')
                                        ->default(fn (): ?int => $resolveUserBidangId())
                                        ->options(function () use ($resolveUserBidangId): array {
                                            /** @var \App\Models\User|null $user */
                                            $user = Auth::user();
                                            $canViewAllBidang = $user !== null && $user->can('viewAny', Bidang::class);

                                            if ($canViewAllBidang) {
                                                return Bidang::pluck('nama', 'id')->all();
                                            }

                                            $userBidangId = $resolveUserBidangId();

                                            return $userBidangId
                                                ? Bidang::whereKey($userBidangId)->pluck('nama', 'id')->all()
                                                : [];
                                        })
                                        ->disabled(function () use ($resolveUserBidangId): bool {
                                            /** @var \App\Models\User|null $user */
                                            $user = Auth::user();
                                            $canViewAllBidang = $user !== null && $user->can('viewAny', Bidang::class);

                                            return ! $canViewAllBidang && $resolveUserBidangId() !== null;
                                        })
                                        ->dehydrated()
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
                                            TextInput::make('pagu_anggaran')
                                                ->label('Jumlah Uang / Pagu Kegiatan (Rp)')
                                                ->numeric()
                                                ->default(0)
                                                ->prefix('Rp')
                                                ->required(),
                                        ])
                                        ->createOptionUsing(function (array $data, callable $get) use ($resolveBidangId): int {
                                            return MasterKegiatan::create([
                                                'bidang_id' => $resolveBidangId($get),
                                                'kode_kegiatan' => $data['kode_kegiatan'],
                                                'nama_kegiatan' => $data['nama_kegiatan'],
                                                'pagu_anggaran' => (float) ($data['pagu_anggaran'] ?? 0),
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
                                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                            if (isset($data['rincian_anggaran_belanja_id']) && str_starts_with((string) $data['rincian_anggaran_belanja_id'], 'mb_')) {
                                                $data['rincian_anggaran_belanja_id'] = null;
                                            }
                                            return $data;
                                        })
                                        ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                            if (isset($data['rincian_anggaran_belanja_id']) && str_starts_with((string) $data['rincian_anggaran_belanja_id'], 'mb_')) {
                                                $data['rincian_anggaran_belanja_id'] = null;
                                            }
                                            return $data;
                                        })
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

                                                    $subKegiatanId = $get('../../sub_kegiatan_id');
                                                    if ($subKegiatanId) {
                                                        $rekIds = \App\Models\RincianAnggaranBelanja::where('sub_kegiatan_id', $subKegiatanId)
                                                            ->pluck('kode_rekening_id')
                                                            ->unique();

                                                        if ($rekIds->isNotEmpty()) {
                                                            return KodeRekening::whereIn('id', $rekIds)
                                                                ->get()
                                                                ->pluck('label', 'id')
                                                                ->all();
                                                        }
                                                    }

                                                    return KodeRekening::all()
                                                        ->pluck('label', 'id')
                                                        ->all();
                                                })
                                                ->searchable()
                                                ->live()
                                                ->afterStateUpdated(function (callable $set) {
                                                    $set('rincian_anggaran_belanja_id', null);
                                                    $set('master_barang_id', null);
                                                    $set('nama_item_belanja', null);
                                                    $set('satuan', null);
                                                    $set('stok_tersedia', 0);
                                                    $set('harga_satuan', 0);
                                                    $set('subtotal', 0);
                                                })
                                                ->required()
                                                ->columnSpan(5),

                                            Select::make('rincian_anggaran_belanja_id')
                                                ->label('Barang / Belanja (RKA)')
                                                ->placeholder(function (callable $get) use ($resolveBidangId): string {
                                                    if (! $resolveBidangId($get)) {
                                                        return 'Pilih Bidang Pengusul terlebih dahulu';
                                                    }
                                                    if (blank($get('kode_rekening_id'))) {
                                                        return 'Pilih Rekening Belanja terlebih dahulu';
                                                    }

                                                    return 'Pilih Barang / Belanja dari RKA';
                                                })
                                                ->disabled(fn (callable $get): bool => $resolveBidangId($get) === null || blank($get('kode_rekening_id')))
                                                ->options(function (callable $get) use ($resolveBidangId): array {
                                                    $bidangId = $resolveBidangId($get);
                                                    $kodeRekeningId = $get('kode_rekening_id');

                                                    if (! $bidangId || ! $kodeRekeningId) {
                                                        return [];
                                                    }

                                                    $subKegiatanId = $get('../../sub_kegiatan_id');

                                                    // 1. Cek RKA Belanja untuk Sub Kegiatan & Rekening ini
                                                    $rkaItems = \App\Models\RincianAnggaranBelanja::query()
                                                        ->where('sub_kegiatan_id', $subKegiatanId)
                                                        ->where('kode_rekening_id', $kodeRekeningId)
                                                        ->get();

                                                    if ($rkaItems->isNotEmpty()) {
                                                        return $rkaItems->mapWithKeys(function ($rka) {
                                                            $sisaVol = number_format($rka->sisa_volume, 0, ',', '.');
                                                            $harga = number_format((float) $rka->harga_satuan, 0, ',', '.');
                                                            return [
                                                                $rka->id => "📋 {$rka->nama_item} ({$rka->satuan}) — [Sisa Pagu: {$sisaVol} {$rka->satuan} | Rp {$harga}]",
                                                            ];
                                                        })->all();
                                                    }

                                                    // 2. Fallback jika belum diinput di RKA
                                                    $barangs = MasterBarang::with('satuan')
                                                        ->where('kode_rekening_id', $kodeRekeningId)
                                                        ->get();

                                                    return $barangs->mapWithKeys(function (MasterBarang $barang) {
                                                        $satuan = $barang->satuan?->nama ?? 'Unit';
                                                        $harga = number_format((float) $barang->harga_satuan, 0, ',', '.');

                                                        return [
                                                            "mb_{$barang->id}" => "📦 {$barang->nama_barang} ({$satuan}) — Rp {$harga}",
                                                        ];
                                                    })->all();
                                                })
                                                ->searchable()
                                                ->live()
                                                ->afterStateUpdated(function (callable $set, callable $get, ?string $state) {
                                                    if ($state) {
                                                        if (str_starts_with($state, 'mb_')) {
                                                            $mbId = (int) substr($state, 3);
                                                            $barang = MasterBarang::with('satuan')->find($mbId);
                                                            if ($barang) {
                                                                $set('master_barang_id', $barang->id);
                                                                $set('nama_item_belanja', $barang->nama_barang);
                                                                $set('satuan', $barang->satuan?->nama ?? 'Unit');
                                                                $harga = (float) $barang->harga_satuan;
                                                                $set('stok_tersedia', 9999);
                                                            } else {
                                                                $harga = 0;
                                                                $set('stok_tersedia', 0);
                                                            }
                                                        } else {
                                                            $rka = \App\Models\RincianAnggaranBelanja::find($state);
                                                            if ($rka) {
                                                                $set('master_barang_id', $rka->master_barang_id);
                                                                $set('nama_item_belanja', $rka->nama_item);
                                                                $set('satuan', $rka->satuan);
                                                                $harga = (float) $rka->harga_satuan;
                                                                $set('stok_tersedia', (float) $rka->sisa_volume);
                                                            } else {
                                                                $harga = 0;
                                                                $set('stok_tersedia', 0);
                                                            }
                                                        }

                                                        $qty = max(1, (int) ($get('kuantitas') ?: 1));
                                                        $set('kuantitas', $qty);
                                                        $set('harga_satuan', $harga);
                                                        $set('subtotal', $qty * $harga);
                                                        $set('kuantitas_realisasi', $qty);
                                                        $set('harga_realisasi', $harga);
                                                        $set('subtotal_realisasi', $qty * $harga);
                                                    } else {
                                                        $set('master_barang_id', null);
                                                        $set('nama_item_belanja', null);
                                                        $set('satuan', null);
                                                        $set('stok_tersedia', 0);
                                                        $set('harga_satuan', 0);
                                                        $set('subtotal', 0);
                                                    }
                                                })
                                                ->required()
                                                ->columnSpan(7),

                                            Hidden::make('master_barang_id'),
                                            Hidden::make('nama_item_belanja'),
                                            Hidden::make('satuan'),

                                            TextInput::make('stok_tersedia')
                                                ->label('Sisa Pagu RKA')
                                                ->disabled()
                                                ->dehydrated(false)
                                                ->default(0)
                                                ->suffix(fn (callable $get) => $get('satuan') ?: 'Unit')
                                                ->columnSpan(2),

                                            TextInput::make('kuantitas')
                                                ->label('Jumlah')
                                                ->numeric()
                                                ->default(1)
                                                ->minValue(1)
                                                ->live(onBlur: true)
                                                ->rules([
                                                    fn (callable $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                                        $rkaId = $get('rincian_anggaran_belanja_id');
                                                        if ($rkaId && ! str_starts_with((string) $rkaId, 'mb_')) {
                                                            $rka = \App\Models\RincianAnggaranBelanja::find($rkaId);
                                                            if ($rka) {
                                                                $sisa = (float) $rka->sisa_volume;
                                                                if ((float) $value > $sisa) {
                                                                    $fail("Ditolak Sistem (Over Budget): Jumlah pesanan ({$value} {$rka->satuan}) melebihi sisa pagu RKA yang tersedia ({$sisa} {$rka->satuan})!");
                                                                }
                                                            }
                                                        }
                                                    },
                                                ])
                                                ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                                    $qty = max(1, (int) ($state ?: 1));
                                                    $harga = (float) ($get('harga_satuan') ?: 0);
                                                    $sisaPagu = (float) ($get('stok_tersedia') ?: 0);

                                                    if ($sisaPagu > 0 && $qty > $sisaPagu) {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title('Peringatan: Over Budget!')
                                                            ->body("Jumlah yang diinput ({$qty}) melebihi sisa pagu RKA ({$sisaPagu}). Pengajuan akan ditolak oleh sistem.")
                                                            ->warning()
                                                            ->send();
                                                    }

                                                    $set('subtotal', $qty * $harga);
                                                    $set('kuantitas_realisasi', $qty);
                                                    $set('harga_realisasi', $harga);
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

                            // 3. DATA TOKO / REKANAN PENYEDIA (Paling Bawah pada Step 1 + Fitur Pilih/Tambah Toko Langsung)
                            Section::make('Data Toko / Rekanan Penyedia')
                                ->description('Pilih toko dari daftar tersimpan atau klik tombol (+) untuk menambah Toko baru tanpa berpindah halaman.')
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
                                                $set('nama_bank', $toko->nama_bank?->value ?? $toko->nama_bank);
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

                                                    Select::make('nama_bank')
                                                        ->label('Nama Bank')
                                                        ->options(NamaBank::class)
                                                        ->default(NamaBank::BANK_NAGARI)
                                                        ->searchable(),

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
                                                'nama_bank' => $data['nama_bank'] ?? NamaBank::BANK_NAGARI->value,
                                                'nomor_rekening' => $data['nomor_rekening'] ?? null,
                                                'alamat' => $data['alamat'] ?? null,
                                                'is_active' => true,
                                            ]);

                                            $set('nama_toko', $toko->nama_toko);
                                            $set('penerima_nama', $toko->nama_pemilik);
                                            $set('telepon_toko', $toko->telepon);
                                            $set('alamat_toko', $toko->alamat);
                                            $set('nama_bank', $toko->nama_bank?->value ?? $toko->nama_bank);
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

                                    Select::make('nama_bank')
                                        ->label('Nama Bank')
                                        ->options(NamaBank::class)
                                        ->default(NamaBank::BANK_NAGARI)
                                        ->searchable()
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
                                        ->content(function (mixed $record = null, mixed $livewire = null) use ($resolveSpjRecord): string {
                                            $spj = $resolveSpjRecord($record, $livewire);
                                            $namaPptk = $spj?->pptk?->name ?? 'PPTK';

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
                                        ->content(function (callable $get, mixed $record = null, mixed $livewire = null) use ($resolveSpjRecord): string {
                                            $spj = $resolveSpjRecord($record, $livewire);
                                            $details = $get('details') ?? [];
                                            $totalRealisasi = 0;
                                            $totalRencana = 0;
                                            foreach ($details as $item) {
                                                $totalRencana += (float) ($item['subtotal'] ?? 0);
                                                $totalRealisasi += (float) ($item['subtotal_realisasi'] ?? 0);
                                            }
                                            if ($totalRencana <= 0 && $spj) {
                                                $totalRencana = (float) $spj->total_nilai_rencana;
                                                $totalRealisasi = (float) ($spj->total_nilai_realisasi ?: $spj->total_nilai_rencana);
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
                                        ->content(function (mixed $record = null, mixed $livewire = null) use ($resolveSpjRecord): string {
                                            $spj = $resolveSpjRecord($record, $livewire);

                                            return $spj ? $spj->status_label : 'Draft';
                                        }),

                                    Placeholder::make('total_rencana_npd')
                                        ->label('Total Nilai Pesanan')
                                        ->content(function (callable $get, mixed $record = null, mixed $livewire = null) use ($resolveSpjRecord): string {
                                            $spj = $resolveSpjRecord($record, $livewire);
                                            $details = $get('details') ?? [];
                                            $total = 0;
                                            foreach ($details as $item) {
                                                $total += (float) ($item['subtotal'] ?? 0);
                                            }
                                            if ($total <= 0 && $spj) {
                                                $total = (float) $spj->total_nilai_rencana;
                                            }

                                            return 'Rp ' . number_format($total, 0, ',', '.') . ',-';
                                        }),

                                    Placeholder::make('total_pencairan_npd')
                                        ->label('Total Nilai Pencairan (NPD)')
                                        ->content(function (callable $get, mixed $record = null, mixed $livewire = null) use ($resolveSpjRecord): string {
                                            $spj = $resolveSpjRecord($record, $livewire);
                                            $details = $get('details') ?? [];
                                            $total = 0;
                                            foreach ($details as $item) {
                                                $total += (float) ($item['subtotal_realisasi'] ?? 0);
                                            }
                                            if ($total <= 0 && $spj) {
                                                $total = (float) ($spj->total_nilai_realisasi ?: $spj->total_nilai_rencana);
                                            }

                                            return 'Rp ' . number_format($total, 0, ',', '.') . ',-';
                                        }),

                                    TextInput::make('nomor_bku')
                                        ->label('Nomor BKU')
                                        ->placeholder('Diisi Bendahara saat pencairan')
                                        ->visible(function (mixed $record = null, mixed $livewire = null) use ($resolveSpjRecord): bool {
                                            $spj = $resolveSpjRecord($record, $livewire);

                                            return $spj !== null
                                                && in_array($spj->status, ['menunggu_verifikasi_bendahara', 'revisi_bendahara', 'disetujui', 'dicairkan'], true);
                                        })
                                        ->maxLength(100),

                                    DatePicker::make('tanggal_dicairkan')
                                        ->label('Tanggal Pencairan')
                                        ->visible(function (mixed $record = null, mixed $livewire = null) use ($resolveSpjRecord): bool {
                                            $spj = $resolveSpjRecord($record, $livewire);

                                            return $spj !== null
                                                && in_array($spj->status, ['menunggu_verifikasi_bendahara', 'revisi_bendahara', 'disetujui', 'dicairkan'], true);
                                        })
                                        ->displayFormat('d/m/Y'),

                                    Placeholder::make('matrix_penandatangan_npd')
                                        ->label('Pejabat Penandatangan')
                                        ->content(function (mixed $record = null, mixed $livewire = null) use ($resolveSpjRecord): string {
                                            $spj = $resolveSpjRecord($record, $livewire);
                                            if (! $spj) {
                                                return 'PPTK: Menunggu | PPK: Menunggu | PA/KPA: Menunggu | Bendahara: Menunggu';
                                            }

                                            $pptk = $spj->pptk?->name ?? 'Belum TTD';
                                            $ppk = $spj->ppk?->name ?? 'Belum Verifikasi';
                                            $pa = $spj->pa?->name ?? 'Belum TTD';
                                            $bendahara = $spj->bendahara?->name ?? 'Belum Cair';

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
                    ->startOnStep(function (mixed $record = null, mixed $livewire = null) use ($resolveSpjRecord): int {
                        $spj = $resolveSpjRecord($record, $livewire);
                        if (! $spj || in_array($spj->status, ['draft', 'diajukan'], true)) {
                            return 1;
                        }
                        if (in_array($spj->status, ['menunggu_realisasi', 'revisi_pptk'], true)) {
                            return 2;
                        }

                        return 3;
                    }),
            ]);
    }
}
