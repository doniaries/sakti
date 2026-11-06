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
use App\Models\MasterTujuanBbm;
use App\Models\PengajuanSpj;
use App\Models\RincianAnggaranBelanja;
use App\Models\Satuan;
use App\Models\User;
use App\Services\Spj\SpjWorkflowService;
use Carbon\Carbon;
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
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

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
            $id = $get('/data.bidang_id')
                ?: $get('bidang_id')
                ?: $get('../../bidang_id')
                ?: $get('../../../bidang_id')
                ?: $get('../../../../bidang_id');

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
                    // STEP 1: PENGAJUAN NOTA PESANAN / SURAT PESANAN (LAYOUT 2/3 dan 1/3)
                    // =========================================================================
                    Step::make('1. Pengajuan Nota Pesanan')
                        ->icon('heroicon-o-document-text')
                        ->description('Data kegiatan, daftar barang pesanan, dan toko penyedia')
                        ->schema([
                            Grid::make(['default' => 1, 'lg' => 3])
                                ->schema([
                                    // -------------------------------------------------------------
                                    // SISI KIRI (2/3): Identitas Kegiatan, Rantai SIPD & Rincian Barang
                                    // -------------------------------------------------------------
                                    Group::make([
                                        // 1. IDENTITAS KEGIATAN & SURAT PESANAN
                                        Section::make('Identitas Kegiatan & Surat Pesanan')
                                            ->description('Lengkapi klasifikasi pengadaan dan rantai hierarki anggaran kegiatan SIPD.')
                                            ->compact()
                                            ->schema([
                                                // BARIS 1: 3 Kolom (Metode, Jenis Belanja, Tahun Anggaran)
                                                Grid::make(['default' => 1, 'md' => 3])
                                                    ->schema([
                                                        Select::make('metode_pengadaan')
                                                            ->label('Metode Pengadaan')
                                                            ->options([
                                                                'non_katalog' => 'Non-Katalog (Pengadaan Langsung)',
                                                                'e_katalog' => 'E-Katalog (E-Purchasing LKPP)',
                                                            ])
                                                            ->default('non_katalog')
                                                            ->required()
                                                            ->live(),

                                                        Select::make('jenis_belanja')
                                                            ->label('Jenis Belanja')
                                                            ->options([
                                                                'barang_jasa' => 'Belanja Barang & Jasa',
                                                                'makan_minum' => 'Makan & Minum Rapat',
                                                                'sppd' => 'Perjalanan Dinas (SPPD)',
                                                                'bbm' => 'BBM (Bahan Bakar Minyak)',
                                                            ])
                                                            ->default('barang_jasa')
                                                            ->required()
                                                            ->live(),

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
                                                            ->afterStateUpdated(function ($state, callable $set, callable $get, ?Model $record) {
                                                                if (! $record) {
                                                                    $set('nomor_pengajuan', PengajuanSpj::generateNomorPengajuan(
                                                                        $get('bidang_id'),
                                                                        (int) ($state ?? date('Y')),
                                                                        $get('tanggal_pengajuan')
                                                                    ));
                                                                }
                                                            }),
                                                    ]),

                                                // BARIS 2: 3 Kolom (Bidang Pengusul, Nomor Surat Pesanan, Tanggal Surat Pesanan)
                                                Grid::make(['default' => 1, 'md' => 3])
                                                    ->schema([
                                                        Select::make('bidang_id')
                                                            ->label('Bidang Pengusul')
                                                            ->placeholder('Pilih Bidang Pengusul')
                                                            ->default(fn (): ?int => $resolveUserBidangId())
                                                            ->options(function () use ($resolveUserBidangId): array {
                                                                /** @var \App\Models\User|null $user */
                                                                $user = Auth::user();
                                                                $canViewAllBidang = $user !== null && (
                                                                    $user->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'bendahara'])
                                                                    || $user->can('viewAny', Bidang::class)
                                                                );

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
                                                                $canViewAllBidang = $user !== null && (
                                                                    $user->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'bendahara'])
                                                                    || $user->can('viewAny', Bidang::class)
                                                                );

                                                                return ! $canViewAllBidang && $resolveUserBidangId() !== null;
                                                            })
                                                            ->dehydrated()
                                                            ->searchable()
                                                            ->live()
                                                            ->afterStateUpdated(function ($state, callable $set, callable $get, ?Model $record) {
                                                                $set('kegiatan_id', null);
                                                                $set('sub_kegiatan_id', null);
                                                                if (! $record) {
                                                                    $set('nomor_pengajuan', PengajuanSpj::generateNomorPengajuan(
                                                                        $state,
                                                                        (int) ($get('tahun_anggaran') ?? date('Y')),
                                                                        $get('tanggal_pengajuan')
                                                                    ));
                                                                }

                                                                $details = $get('details') ?? [];
                                                                foreach (array_keys($details) as $uuid) {
                                                                    $set("details.{$uuid}.kode_rekening_id", null);
                                                                    $set("details.{$uuid}.master_barang_id", null);
                                                                    $set("details.{$uuid}.stok_tersedia", 0);
                                                                    $set("details.{$uuid}.harga_satuan", 0);
                                                                    $set("details.{$uuid}.subtotal", 0);
                                                                }
                                                            })
                                                            ->required(),

                                                        TextInput::make('nomor_pengajuan')
                                                            ->label('Nomor Surat Pesanan')
                                                            ->default(fn (Get $get) => PengajuanSpj::generateNomorPengajuan(
                                                                $get('bidang_id') ?: $resolveUserBidangId(),
                                                                (int) ($get('tahun_anggaran') ?? date('Y')),
                                                                $get('tanggal_pengajuan')
                                                            ))
                                                            ->required()
                                                            ->readOnly(),

                                                        DatePicker::make('tanggal_pengajuan')
                                                            ->label('Tanggal Surat Pesanan')
                                                            ->default(now())
                                                            ->displayFormat('d/m/Y')
                                                            ->required()
                                                            ->live()
                                                            ->afterStateUpdated(function ($state, callable $set, callable $get, ?Model $record) {
                                                                if (! $record) {
                                                                    $set('nomor_pengajuan', PengajuanSpj::generateNomorPengajuan(
                                                                        $get('bidang_id'),
                                                                        (int) ($get('tahun_anggaran') ?? date('Y')),
                                                                        $state
                                                                    ));
                                                                }
                                                            }),
                                                    ]),

                                                // BARIS 3: Rantai Anggaran SIPD (Kegiatan & Sub Kegiatan)
                                                Grid::make(['default' => 1, 'md' => 2])
                                                    ->schema([

                                                        Select::make('kegiatan_id')
                                                            ->label('Kegiatan (SIPD)')
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
                                                                    ->placeholder('Contoh: 3.26.03.2.01')
                                                                    ->mask('9.99.99.9.99')
                                                                    ->helperText('Format titik otomatis sesuai SIPD (contoh: 3.26.03.2.01)')
                                                                    ->required()
                                                                    ->maxLength(50),
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
                                                            ->required(),

                                                        Select::make('sub_kegiatan_id')
                                                            ->label('Sub Kegiatan (SIPD)')
                                                            ->placeholder(fn (callable $get): string => filled($get('kegiatan_id'))
                                                                ? 'Pilih Sub Kegiatan'
                                                                : 'Pilih Kegiatan terlebih dahulu')
                                                            ->disabled(fn (callable $get): bool => blank($get('kegiatan_id')))
                                                            ->options(function (callable $get): array {
                                                                $kegiatanId = $get('kegiatan_id');
                                                                if (! $kegiatanId) {
                                                                    return [];
                                                                }

                                                                $query = MasterSubKegiatan::where('kegiatan_id', $kegiatanId)->with('jf');

                                                                /** @var \App\Models\User|null $user */
                                                                $user = auth()->user();
                                                                if ($user && $user->hasRole('jf') && ! $user->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'pptk'])) {
                                                                    if ((clone $query)->where('jf_id', $user->id)->exists()) {
                                                                        $query->where('jf_id', $user->id);
                                                                    }
                                                                }

                                                                return $query->get()->mapWithKeys(function ($sk) {
                                                                    $jfLabel = $sk->jf ? " [JF: {$sk->jf->name}]" : '';
                                                                    return [$sk->id => "{$sk->label}{$jfLabel}"];
                                                                })->all();
                                                            })
                                                            ->createOptionForm([
                                                                TextInput::make('kode_sub_kegiatan')
                                                                    ->label('Kode Sub Kegiatan')
                                                                    ->placeholder('Contoh: 3.26.03.2.01.0006')
                                                                    ->mask('9.99.99.9.99.9999')
                                                                    ->helperText('Format titik otomatis sesuai SIPD (contoh: 3.26.03.2.01.0006)')
                                                                    ->required()
                                                                    ->maxLength(50),
                                                                TextInput::make('nama_sub_kegiatan')
                                                                    ->label('Nama Sub Kegiatan')
                                                                    ->required()
                                                                    ->maxLength(255),
                                                                Select::make('jf_id')
                                                                    ->label('JF Pengampu')
                                                                    ->placeholder('Pilih Pejabat Fungsional (Opsional)')
                                                                    ->options(function (callable $get) {
                                                                        $kegiatanId = $get('kegiatan_id');
                                                                        $bidangId = $kegiatanId ? MasterKegiatan::find($kegiatanId)?->bidang_id : null;

                                                                        return User::query()
                                                                            ->where('aktif', true)
                                                                            ->whereHas('roles', fn ($q) => $q->where('name', 'jf'))
                                                                            ->when($bidangId, fn ($q) => $q->where('bidang_id', $bidangId))
                                                                            ->pluck('name', 'id')
                                                                            ->all();
                                                                    })
                                                                    ->searchable()
                                                                    ->nullable(),
                                                            ])
                                                            ->createOptionUsing(function (array $data, callable $get): int {
                                                                return MasterSubKegiatan::create([
                                                                    'kegiatan_id' => (int) $get('kegiatan_id'),
                                                                    'jf_id' => $data['jf_id'] ?? (auth()->user()?->hasRole('jf') ? auth()->id() : null),
                                                                    'kode_sub_kegiatan' => $data['kode_sub_kegiatan'],
                                                                    'nama_sub_kegiatan' => $data['nama_sub_kegiatan'],
                                                                ])->id;
                                                            })
                                                            ->searchable()
                                                            ->required(),
                                                    ]),

                                                Hidden::make('user_id')
                                                    ->default(fn () => Auth::id()),
                                            ]),

                                        // 1.B KHUSUS E-KATALOG (E-PURCHASING LKPP)
                                        Section::make('Bundel Dokumen E-Purchasing (E-Katalog LKPP)')
                                            ->description('Untuk belanja via E-Katalog, Surat Pesanan, BAST, dan Kwitansi diterbitkan resmi oleh LKPP/E-Proc. Unggah dokumen bundel resmi PDF tersebut di sini.')
                                            ->compact()
                                            ->visible(fn (callable $get) => $get('metode_pengadaan') === 'e_katalog')
                                            ->schema([
                                                FileUpload::make('file_ekatalog_bundel')
                                                    ->label('Unggah Bundel Dokumen E-Katalog LKPP (PDF)')
                                                    ->disk('public')
                                                    ->directory('spj/ekatalog')
                                                    ->acceptedFileTypes(['application/pdf'])
                                                    ->maxSize(10240)
                                                    ->openable()
                                                    ->downloadable()
                                                    ->columnSpanFull(),
                                            ]),

                                        // 1.C KHUSUS PERJALANAN DINAS (SPPD)
                                        Section::make('Rincian Surat Tugas & Perjalanan Dinas (SPPD)')
                                            ->description('Lengkapi data dasar surat perintah tugas dan tujuan kedinasan.')
                                            ->compact()
                                            ->visible(fn (callable $get) => $get('jenis_belanja') === 'sppd')
                                            ->columns(12)
                                            ->schema([
                                                Select::make('tingkat_perjalanan')
                                                    ->label('Tingkat Perjalanan')
                                                    ->options([
                                                        'dalam_kabupaten' => 'Dalam Kabupaten (Per Nagari)',
                                                        'luar_kabupaten' => 'Luar Kabupaten (Dalam Provinsi)',
                                                        'luar_provinsi' => 'Luar Provinsi',
                                                    ])
                                                    ->default('dalam_kabupaten')
                                                    ->required(fn (callable $get) => $get('jenis_belanja') === 'sppd')
                                                    ->columnSpan(['default' => 12, 'md' => 4]),

                                                TextInput::make('nomor_spt')
                                                    ->label('Nomor SPT')
                                                    ->placeholder('Contoh: 090/045/SPT/PARPORA-2026')
                                                    ->columnSpan(['default' => 12, 'md' => 4]),

                                                DatePicker::make('tanggal_spt')
                                                    ->label('Tanggal SPT')
                                                    ->displayFormat('d/m/Y')
                                                    ->default(now())
                                                    ->columnSpan(['default' => 12, 'md' => 4]),

                                                DatePicker::make('tanggal_berangkat')
                                                    ->label('Tanggal Berangkat')
                                                    ->displayFormat('d/m/Y')
                                                    ->default(now())
                                                    ->live()
                                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                                        $tglPulang = $get('tanggal_kembali');
                                                        if ($state && $tglPulang) {
                                                            $days = Carbon::parse($state)->diffInDays(Carbon::parse($tglPulang)) + 1;
                                                            $set('lama_hari', max(1, $days));
                                                        }
                                                    })
                                                    ->columnSpan(['default' => 12, 'md' => 4]),

                                                DatePicker::make('tanggal_kembali')
                                                    ->label('Tanggal Kembali')
                                                    ->displayFormat('d/m/Y')
                                                    ->default(now())
                                                    ->live()
                                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                                        $tglBerangkat = $get('tanggal_berangkat');
                                                        if ($state && $tglBerangkat) {
                                                            $days = Carbon::parse($tglBerangkat)->diffInDays(Carbon::parse($state)) + 1;
                                                            $set('lama_hari', max(1, $days));
                                                        }
                                                    })
                                                    ->columnSpan(['default' => 12, 'md' => 4]),

                                                TextInput::make('lama_hari')
                                                    ->label('Lama Perjalanan')
                                                    ->numeric()
                                                    ->default(1)
                                                    ->readOnly()
                                                    ->suffix('Hari')
                                                    ->columnSpan(['default' => 12, 'md' => 4]),

                                                TextInput::make('lokasi_tujuan')
                                                    ->label('Lokasi / Tempat Tujuan')
                                                    ->placeholder('Contoh: Nagari Silokek / Kantor Gubernur Sumbar')
                                                    ->columnSpanFull(),

                                                Textarea::make('maksud_perjalanan')
                                                    ->label('Maksud Perjalanan Dinas')
                                                    ->placeholder('Contoh: Koordinasi penguatan destinasi wisata nagari')
                                                    ->rows(2)
                                                    ->columnSpanFull(),
                                            ]),

                                        // 1.D KHUSUS BAHAN BAKAR MINYAK (BBM)
                                        Section::make('Standar Kuota BBM Kedinasan')
                                            ->description('Pilih nagari atau rute tujuan kedinasan. Standar kuota liter akan terisi otomatis sesuai SBM.')
                                            ->compact()
                                            ->visible(fn (callable $get) => $get('jenis_belanja') === 'bbm')
                                            ->columns(12)
                                            ->schema([
                                                Select::make('master_tujuan_bbm_id')
                                                    ->label('Rute / Nagari Tujuan BBM')
                                                    ->options(fn () => MasterTujuanBbm::where('aktif', true)->get()->pluck('label', 'id')->all())
                                                    ->searchable()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, callable $set) {
                                                        if ($state) {
                                                            $tujuan = MasterTujuanBbm::find($state);
                                                            if ($tujuan) {
                                                                $set('jumlah_liter_bbm', $tujuan->kuota_liter);
                                                                $set('lokasi_tujuan', $tujuan->nama_tujuan);
                                                            }
                                                        }
                                                    })
                                                    ->createOptionForm([
                                                        TextInput::make('nama_tujuan')
                                                            ->label('Nama Tujuan / Nagari Baru')
                                                            ->required(),
                                                        TextInput::make('kuota_liter')
                                                            ->label('Standar Kuota BBM (Liter)')
                                                            ->numeric()
                                                            ->required(),
                                                        TextInput::make('jarak_km')
                                                            ->label('Jarak (KM)')
                                                            ->numeric()
                                                            ->nullable(),
                                                    ])
                                                    ->createOptionUsing(function (array $data): int {
                                                        $tujuan = MasterTujuanBbm::create([
                                                            'nama_tujuan' => $data['nama_tujuan'],
                                                            'kuota_liter' => (float) $data['kuota_liter'],
                                                            'jarak_km' => isset($data['jarak_km']) ? (float) $data['jarak_km'] : null,
                                                            'kategori_wilayah' => 'dalam_kabupaten',
                                                            'aktif' => true,
                                                        ]);
                                                        return $tujuan->id;
                                                    })
                                                    ->columnSpan(['default' => 12, 'md' => 8]),

                                                TextInput::make('jumlah_liter_bbm')
                                                    ->label('Jumlah Kuota BBM')
                                                    ->numeric()
                                                    ->default(20)
                                                    ->suffix('Liter')
                                                    ->required(fn (callable $get) => $get('jenis_belanja') === 'bbm')
                                                    ->columnSpan(['default' => 12, 'md' => 4]),
                                            ]),

                                        // 2. RINCIAN BARANG PESANAN
                                        Section::make('Rincian Barang Pesanan')
                                            ->description('Pilih item belanja sesuai ketersediaan sisa pagu alokasi RKA Bidang Pengusul.')
                                            ->compact()
                                            ->schema([
                                                Repeater::make('details')
                                                    ->relationship()
                                                    ->label('Daftar Barang & Rincian Belanja')
                                                    ->itemLabel(fn (array $state): ?string => filled($state['nama_item_belanja'] ?? null)
                                                        ? ($state['nama_item_belanja'] . ' — ' . ($state['kuantitas'] ?? 1) . ' ' . ($state['satuan'] ?? 'Unit') . ' (Rp ' . number_format((float) ($state['subtotal'] ?? 0), 0, ',', '.') . ')')
                                                        : 'Item Belanja Baru')
                                                    ->collapsible()
                                                    ->cloneable()
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
                                                    ->columns(12)
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

                                                                $subKegiatanId = $get('/data.sub_kegiatan_id')
                                                                    ?: $get('sub_kegiatan_id')
                                                                    ?: $get('../../sub_kegiatan_id')
                                                                    ?: $get('../../../sub_kegiatan_id')
                                                                    ?: $get('../../../../sub_kegiatan_id');

                                                                if ($subKegiatanId) {
                                                                    $rekIds = RincianAnggaranBelanja::where('sub_kegiatan_id', $subKegiatanId)
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
                                                            ->createOptionForm([
                                                                TextInput::make('kode_lengkap')
                                                                    ->label('Kode Rekening (SIPD)')
                                                                    ->placeholder('Contoh: 5.1.02.01.01.0024')
                                                                    ->required()
                                                                    ->maxLength(50),
                                                                TextInput::make('uraian')
                                                                    ->label('Uraian Rekening Belanja')
                                                                    ->placeholder('Contoh: Belanja Alat/Bahan untuk Kegiatan Kantor')
                                                                    ->required()
                                                                    ->maxLength(255),
                                                            ])
                                                            ->createOptionUsing(function (array $data): int {
                                                                $rek = KodeRekening::create([
                                                                    'kode_lengkap' => $data['kode_lengkap'],
                                                                    'uraian' => $data['uraian'],
                                                                ]);
                                                                return $rek->id;
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
                                                            ->columnSpan(['default' => 12, 'md' => 5]),

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

                                                                $subKegiatanId = $get('/data.sub_kegiatan_id')
                                                                    ?: $get('sub_kegiatan_id')
                                                                    ?: $get('../../sub_kegiatan_id')
                                                                    ?: $get('../../../sub_kegiatan_id')
                                                                    ?: $get('../../../../sub_kegiatan_id');

                                                                // 1. Cek RKA Belanja untuk Sub Kegiatan & Rekening ini
                                                                $rkaItems = RincianAnggaranBelanja::query()
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
                                                            ->createOptionForm([
                                                                TextInput::make('nama_barang')
                                                                    ->label('Nama Item Belanja Baru')
                                                                    ->required()
                                                                    ->maxLength(255),
                                                                TextInput::make('harga_satuan')
                                                                    ->label('Harga Satuan (Rp)')
                                                                    ->numeric()
                                                                    ->default(0)
                                                                    ->prefix('Rp')
                                                                    ->required(),
                                                                TextInput::make('satuan_nama')
                                                                    ->label('Satuan')
                                                                    ->placeholder('Contoh: Kotak / Unit / Rim / Porsi')
                                                                    ->default('Unit'),
                                                            ])
                                                            ->createOptionUsing(function (array $data, callable $get): string {
                                                                $kodeRekeningId = $get('kode_rekening_id');
                                                                $satuan = Satuan::firstOrCreate(['nama' => $data['satuan_nama'] ?? 'Unit']);
                                                                $barang = MasterBarang::create([
                                                                    'kode_rekening_id' => $kodeRekeningId,
                                                                    'nama_barang' => $data['nama_barang'],
                                                                    'harga_satuan' => (float) ($data['harga_satuan'] ?? 0),
                                                                    'satuan_id' => $satuan->id,
                                                                ]);
                                                                return "mb_{$barang->id}";
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
                                                                        $rka = RincianAnggaranBelanja::find($state);
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
                                                            ->columnSpan(['default' => 12, 'md' => 7]),

                                                        Hidden::make('master_barang_id'),
                                                        Hidden::make('nama_item_belanja'),
                                                        Hidden::make('satuan'),

                                                        TextInput::make('stok_tersedia')
                                                            ->label('Sisa Pagu RKA')
                                                            ->disabled()
                                                            ->dehydrated(false)
                                                            ->default(0)
                                                            ->prefix('📊')
                                                            ->suffix(fn (callable $get) => $get('satuan') ?: 'Unit')
                                                            ->columnSpan(['default' => 6, 'md' => 2]),

                                                        TextInput::make('kuantitas')
                                                            ->label('Jumlah Dipesan')
                                                            ->numeric()
                                                            ->default(1)
                                                            ->minValue(1)
                                                            ->suffix(fn (callable $get) => $get('satuan') ?: 'Unit')
                                                            ->live(onBlur: true)
                                                            ->rules([
                                                                fn (callable $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                                                    $rkaId = $get('rincian_anggaran_belanja_id');
                                                                    if ($rkaId && ! str_starts_with((string) $rkaId, 'mb_')) {
                                                                        $rka = RincianAnggaranBelanja::find($rkaId);
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
                                                                    Notification::make()
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
                                                            ->columnSpan(['default' => 6, 'md' => 2]),

                                                        TextInput::make('harga_satuan')
                                                            ->label('Harga Satuan')
                                                            ->numeric()
                                                            ->default(0)
                                                            ->prefix('Rp')
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                                $qty = (int) ($get('kuantitas') ?: 0);
                                                                $harga = (float) ($get('harga_satuan') ?: 0);
                                                                $set('subtotal', $qty * $harga);
                                                            })
                                                            ->required()
                                                            ->columnSpan(['default' => 12, 'md' => 4]),

                                                        TextInput::make('subtotal')
                                                            ->label('Subtotal Pesanan')
                                                            ->numeric()
                                                            ->default(0)
                                                            ->prefix('Rp')
                                                            ->readOnly()
                                                            ->columnSpan(['default' => 12, 'md' => 4]),

                                                        TextInput::make('kuantitas_realisasi')
                                                            ->label('Jumlah Realisasi (Faktur)')
                                                            ->numeric()
                                                            ->default(1)
                                                            ->suffix(fn (callable $get) => $get('satuan') ?: 'Unit')
                                                            ->visible($isPesananDisetujui)
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                                $qty = (int) ($get('kuantitas_realisasi') ?: 0);
                                                                $harga = (float) ($get('harga_realisasi') ?: 0);
                                                                $set('subtotal_realisasi', $qty * $harga);
                                                            })
                                                            ->columnSpan(['default' => 12, 'md' => 4]),

                                                        TextInput::make('harga_realisasi')
                                                            ->label('Harga Realisasi (Faktur)')
                                                            ->numeric()
                                                            ->default(0)
                                                            ->prefix('Rp')
                                                            ->visible($isPesananDisetujui)
                                                            ->live(onBlur: true)
                                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                                $qty = (int) ($get('kuantitas_realisasi') ?: 0);
                                                                $harga = (float) ($get('harga_realisasi') ?: 0);
                                                                $set('subtotal_realisasi', $qty * $harga);
                                                            })
                                                            ->columnSpan(['default' => 12, 'md' => 4]),

                                                        TextInput::make('subtotal_realisasi')
                                                            ->label('Subtotal Realisasi')
                                                            ->numeric()
                                                            ->default(0)
                                                            ->prefix('Rp')
                                                            ->visible($isPesananDisetujui)
                                                            ->readOnly()
                                                            ->columnSpan(['default' => 12, 'md' => 4]),
                                                    ])
                                                    ->defaultItems(1)
                                                    ->addActionLabel('Tambah Barang Belanja'),
                                            ]),
                                    ])
                                        ->columnSpan(['default' => 1, 'lg' => 2]),

                                    // -------------------------------------------------------------
                                    // SISI KANAN (1/3): Data Toko / Rekanan Penyedia & Ketentuan Belanja
                                    // -------------------------------------------------------------
                                    Group::make([
                                        Section::make('Data Toko / Rekanan')
                                            ->description('Pilih toko rekanan dari master data tersimpan.')
                                            ->compact()
                                            ->schema([
                                                Select::make('master_toko_id')
                                                    ->label('Toko / Rekanan')
                                                    ->placeholder('Pilih Toko atau Klik (+) Tambah')
                                                    ->options(fn () => MasterToko::where('is_active', true)->get()->pluck('label', 'id')->all())
                                                    ->searchable()
                                                    ->preload()
                                                    ->live()
                                                    ->afterStateUpdated(function (callable $set, ?string $state) {
                                                        if (! $state) {
                                                            $set('nama_toko', null);
                                                            $set('penerima_nama', null);
                                                            $set('telepon_toko', null);
                                                            $set('alamat_toko', null);
                                                            $set('nama_bank', null);
                                                            $set('penerima_identitas', null);
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
                                                    ->required(),

                                                Placeholder::make('info_toko_preview')
                                                    ->label('Rincian Toko Terpilih')
                                                    ->content(function (callable $get): HtmlString {
                                                        $masterTokoId = $get('master_toko_id');
                                                        $toko = $masterTokoId ? MasterToko::find((int) $masterTokoId) : null;

                                                        $namaPemilik = $toko?->nama_pemilik ?: ($get('penerima_nama') ?: '-');
                                                        $telepon = $toko?->telepon ?: ($get('telepon_toko') ?: '-');
                                                        $bank = $toko?->nama_bank?->getLabel() ?? ($toko?->nama_bank?->value ?? ($get('nama_bank') ?: '-'));
                                                        $rekening = $toko?->nomor_rekening ?: ($get('penerima_identitas') ?: '-');
                                                        $alamat = $toko?->alamat ?: ($get('alamat_toko') ?: '-');

                                                        if (! $masterTokoId && $namaPemilik === '-' && $alamat === '-') {
                                                            return new HtmlString('<span class="text-xs text-gray-500 italic">Pilih toko untuk menampilkan rincian data penyedia</span>');
                                                        }

                                                        return new HtmlString("
                                                            <div class='text-xs space-y-1.5 bg-gray-50 dark:bg-gray-800/80 p-3 rounded-lg border border-gray-200 dark:border-gray-700'>
                                                                <div><span class='font-medium text-gray-500 dark:text-gray-400'>Pemilik:</span> <span class='font-semibold text-gray-900 dark:text-gray-100'>{$namaPemilik}</span></div>
                                                                <div><span class='font-medium text-gray-500 dark:text-gray-400'>Telepon:</span> {$telepon}</div>
                                                                <div><span class='font-medium text-gray-500 dark:text-gray-400'>Bank:</span> {$bank}</div>
                                                                <div><span class='font-medium text-gray-500 dark:text-gray-400'>No. Rek:</span> <span class='font-mono font-medium text-primary-600 dark:text-primary-400'>{$rekening}</span></div>
                                                                <div><span class='font-medium text-gray-500 dark:text-gray-400'>Alamat:</span> {$alamat}</div>
                                                            </div>
                                                        ");
                                                    }),

                                                // Field tersimpan otomatis ke database tanpa mengacaukan tampilan form
                                                Hidden::make('nama_toko'),
                                                Hidden::make('penerima_nama'),
                                                Hidden::make('telepon_toko'),
                                                Hidden::make('nama_bank'),
                                                Hidden::make('penerima_identitas'),
                                                Hidden::make('alamat_toko'),

                                                DatePicker::make('tanggal_batas_pengiriman')
                                                    ->label('Batas Waktu Pengiriman')
                                                    ->default(fn () => now()->addDays(7))
                                                    ->displayFormat('d/m/Y'),

                                                TextInput::make('metode_pembayaran')
                                                    ->label('Metode Pembayaran')
                                                    ->default('Transfer Bank / Tempo 14 Hari setelah barang diterima')
                                                    ->placeholder('Contoh: Transfer Bank / Tempo 14 Hari'),

                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('ppn_persen')
                                                            ->label('PPN (%)')
                                                            ->numeric()
                                                            ->default(11)
                                                            ->minValue(0)
                                                            ->maxValue(100)
                                                            ->suffix('%')
                                                            ->helperText('> Rp 2 Jt = 11%'),

                                                        TextInput::make('pph22_persen')
                                                            ->label('PPh 22 (%)')
                                                            ->numeric()
                                                            ->default(1.5)
                                                            ->minValue(0)
                                                            ->maxValue(100)
                                                            ->suffix('%')
                                                            ->helperText('> Rp 2 Jt = 1.5%'),
                                                    ]),
                                            ]),
                                    ])
                                        ->columnSpan(['default' => 1, 'lg' => 1]),
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
                                ->columns(12)
                                ->schema([
                                    Placeholder::make('nomor_kwitansi_preview')
                                        ->label('Nomor Kwitansi')
                                        ->content(fn (callable $get): string => ($get('nomor_pengajuan') ?: 'SP/...') . '/KW/' . date('Y'))
                                        ->columnSpan(['default' => 12, 'md' => 4]),

                                    Placeholder::make('status_ttd_pesanan')
                                        ->label('Status Surat Pesanan')
                                        ->content(function (mixed $record = null, mixed $livewire = null) use ($resolveSpjRecord): string {
                                            $spj = $resolveSpjRecord($record, $livewire);
                                            $namaPptk = $spj?->pptk?->name ?? 'PPTK';

                                            return "✅ Telah disetujui oleh {$namaPptk}";
                                        })
                                        ->columnSpan(['default' => 12, 'md' => 4]),

                                    Placeholder::make('info_pemilik_toko_kwitansi')
                                        ->label('Penerima Pembayaran')
                                        ->content(fn (callable $get): string => ($get('nama_toko') ?: '-') . ' — ' . ($get('penerima_nama') ?: '-'))
                                        ->columnSpan(['default' => 12, 'md' => 4]),

                                    FileUpload::make('file_nota_penjual')
                                        ->label('Faktur / Nota Asli Toko')
                                        ->directory('spj/nota-penjual')
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                        ->maxSize(5120)
                                        ->columnSpan(['default' => 12, 'md' => 6]),

                                    FileUpload::make('file_dokumentasi_barang')
                                        ->label('Foto Dokumentasi Barang (Wajib jika > Rp 2 Jt)')
                                        ->directory('spj/dokumentasi-barang')
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                        ->maxSize(5120)
                                        ->columnSpan(['default' => 12, 'md' => 6]),

                                    FileUpload::make('dokumen_syarat_files')
                                        ->label('Berkas Persyaratan Tambahan (Scan SPT, SPPD, Notulen, Daftar Hadir, dsb.)')
                                        ->multiple()
                                        ->disk('public')
                                        ->directory('spj/dokumen-syarat')
                                        ->openable()
                                        ->downloadable()
                                        ->columnSpanFull(),

                                    Textarea::make('justifikasi_selisih')
                                        ->label('Catatan / Justifikasi Selisih (Diisi bila nilai realisasi berbeda dengan pesanan)')
                                        ->rows(2)
                                        ->columnSpanFull(),

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
                                        ->columnSpan(['default' => 12, 'md' => 6]),

                                    Placeholder::make('rincian_pajak_kwitansi')
                                        ->label('Rincian Potongan Pajak & Nilai Bersih Rekanan')
                                        ->content(function (callable $get, mixed $record = null, mixed $livewire = null) use ($resolveSpjRecord): string {
                                            $spj = $resolveSpjRecord($record, $livewire);
                                            $details = $get('details') ?? [];
                                            $total = 0;
                                            foreach ($details as $item) {
                                                $total += (float) ($item['subtotal_realisasi'] ?: ($item['subtotal'] ?? 0));
                                            }
                                            if ($total <= 0 && $spj) {
                                                $total = (float) ($spj->total_nilai_realisasi ?: $spj->total_nilai_rencana);
                                            }

                                            $ppnRate = (float) ($get('ppn_persen') ?: ($spj?->ppn_persen ?? ($total > 2000000 ? 11 : 0)));
                                            $pphRate = (float) ($get('pph22_persen') ?: ($spj?->pph22_persen ?? ($total > 2000000 ? 1.5 : 0)));

                                            $nilaiPpn = round($total * ($ppnRate / 100));
                                            $nilaiPph = round($total * ($pphRate / 100));
                                            $totalPajak = $nilaiPpn + $nilaiPph;
                                            $netto = max(0, $total - $totalPajak);

                                            return "Bruto: Rp " . number_format($total, 0, ',', '.') . " | PPN ({$ppnRate}%): Rp " . number_format($nilaiPpn, 0, ',', '.') . " | PPh 22 ({$pphRate}%): Rp " . number_format($nilaiPph, 0, ',', '.') . " | Total Pajak: Rp " . number_format($totalPajak, 0, ',', '.') . " ➔ DITERIMA BERSIH: Rp " . number_format($netto, 0, ',', '.');
                                        })
                                        ->columnSpan(['default' => 12, 'md' => 6]),
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
                                ->columns(12)
                                ->schema([
                                    Placeholder::make('nomor_npd_preview')
                                        ->label('Nomor NPD')
                                        ->content(fn (callable $get): string => ($get('nomor_pengajuan') ?: 'SP/...') . '/NPD/' . date('Y'))
                                        ->columnSpan(['default' => 12, 'md' => 6]),

                                    Placeholder::make('status_spj_aktif')
                                        ->label('Status SPJ')
                                        ->content(function (mixed $record = null, mixed $livewire = null) use ($resolveSpjRecord): string {
                                            $spj = $resolveSpjRecord($record, $livewire);

                                            return $spj ? $spj->status_label : 'Draft';
                                        })
                                        ->columnSpan(['default' => 12, 'md' => 6]),

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
                                        })
                                        ->columnSpan(['default' => 12, 'md' => 6]),

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
                                        })
                                        ->columnSpan(['default' => 12, 'md' => 6]),

                                    TextInput::make('nomor_bku')
                                        ->label('Nomor BKU')
                                        ->placeholder('Diisi Bendahara saat pencairan')
                                        ->visible(function (mixed $record = null, mixed $livewire = null) use ($resolveSpjRecord): bool {
                                            $spj = $resolveSpjRecord($record, $livewire);

                                            return $spj !== null
                                                && in_array($spj->status, ['menunggu_verifikasi_bendahara', 'revisi_bendahara', 'disetujui', 'dicairkan'], true);
                                        })
                                        ->maxLength(100)
                                        ->columnSpan(['default' => 12, 'md' => 6]),

                                    DatePicker::make('tanggal_dicairkan')
                                        ->label('Tanggal Pencairan')
                                        ->visible(function (mixed $record = null, mixed $livewire = null) use ($resolveSpjRecord): bool {
                                            $spj = $resolveSpjRecord($record, $livewire);

                                            return $spj !== null
                                                && in_array($spj->status, ['menunggu_verifikasi_bendahara', 'revisi_bendahara', 'disetujui', 'dicairkan'], true);
                                        })
                                        ->displayFormat('d/m/Y')
                                        ->columnSpan(['default' => 12, 'md' => 6]),

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
                                        ->columnSpanFull(),
                                ]),

                            Section::make('Spesimen Tanda Tangan Pejabat (Non-TTE)')
                                ->description('Unggah gambar tanda tangan (PNG/JPG) untuk dicetak pada dokumen PDF.')
                                ->compact()
                                ->columns(['default' => 2, 'lg' => 4])
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
