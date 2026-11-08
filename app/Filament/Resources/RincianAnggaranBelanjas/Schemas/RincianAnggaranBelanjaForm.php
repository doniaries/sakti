<?php

namespace App\Filament\Resources\RincianAnggaranBelanjas\Schemas;

use App\Enums\SumberDana;
use App\Models\Bidang;
use App\Models\KodeRekening;
use App\Models\MasterBarang;
use App\Models\MasterSubKegiatan;
use App\Models\Satuan;
use App\Support\FormatHelper;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class RincianAnggaranBelanjaForm
{
    public static function configure(Schema $schema): Schema
    {
        $currentYear = (int) date('Y');
        $maxYear = $currentYear + 1; // Batasi maksimal ke 2027 jika tahun sekarang 2026
        $minYear = $currentYear - 2;

        $years = [];
        for ($y = $maxYear; $y >= $minYear; $y--) {
            $years[$y] = (string) $y;
        }

        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $isSuperAdmin = (bool) ($user?->hasRole('super_admin'));
        $userBidangId = $user?->bidang_id;

        // Smart Memory (Konteks Terakhir Pengisian Cepat)
        $lastContext = session('rka_last_context', []);
        $defaultTahun = $lastContext['tahun_anggaran'] ?? $currentYear;
        $defaultSumberDana = $lastContext['sumber_dana'] ?? SumberDana::DAU->value;
        $defaultSubKegiatanId = $lastContext['sub_kegiatan_id'] ?? null;
        $defaultKodeRekeningId = $lastContext['kode_rekening_id'] ?? null;

        return $schema
            ->columns(1)
            ->components([
                Section::make('Formulir Rincian Belanja RKA')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        // Baris 1: Tahun Anggaran, Sumber Pendanaan, dan Bidang Pengampu (Readonly kecuali superadmin)
                        Grid::make([
                            'default' => 1,
                            'md' => 3,
                        ])->schema([
                            Select::make('tahun_anggaran')
                                ->label('Tahun Anggaran')
                                ->options($years)
                                ->default($defaultTahun)
                                ->required()
                                ->live(onBlur: true),

                            Select::make('sumber_dana')
                                ->label('Sumber Pendanaan')
                                ->options(SumberDana::class)
                                ->default($defaultSumberDana)
                                ->required(),

                            Select::make('bidang_id')
                                ->label('Bidang Pengampu')
                                ->options(fn() => Bidang::pluck('nama', 'id')->all())
                                ->default(fn() => $userBidangId ?? Bidang::first()?->id)
                                ->disabled(! $isSuperAdmin)
                                ->dehydrated()
                                ->required()
                                ->live(onBlur: true),
                        ]),

                        // Baris 2: Sub Kegiatan (Preload & Filter sesuai Bidang)
                        Select::make('sub_kegiatan_id')
                            ->label('Sub Kegiatan')
                            ->placeholder('Pilih Sub Kegiatan Belanja...')
                            ->default($defaultSubKegiatanId)
                            ->options(function (Get $get) use ($userBidangId, $isSuperAdmin) {
                                $tahun = $get('tahun_anggaran') ?? (int) date('Y');
                                $bidangId = $isSuperAdmin ? $get('bidang_id') : ($userBidangId ?? $get('bidang_id'));

                                return MasterSubKegiatan::query()
                                    ->where('tahun_anggaran', $tahun)
                                    ->when($bidangId, fn($q) => $q->whereHas('kegiatan', fn($k) => $k->where('bidang_id', $bidangId)))
                                    ->get()
                                    ->mapWithKeys(fn($item) => [$item->id => "{$item->kode_sub_kegiatan} - {$item->nama_sub_kegiatan}"])
                                    ->all();
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(),

                        // Baris 3: Rincian Objek Belanja & Komponen Barang
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])->schema([
                            Select::make('kode_rekening_id')
                                ->label('Rincian Objek Belanja (SIPD RI)')
                                ->placeholder('Pilih nomor atau nama Rincian Objek...')
                                ->default($defaultKodeRekeningId)
                                ->searchable()
                                ->preload()
                                ->options(function () {
                                    $popular = KodeRekening::hanyaRincianObjek()
                                        ->withCount(['rincianAnggarans', 'masterBarangs'])
                                        ->where(function ($q) {
                                            $q->has('rincianAnggarans')
                                                ->orWhereHas('masterBarangs')
                                                ->orWhere('kode_lengkap', 'like', '5.1.02.01.001.%')
                                                ->orWhere('kode_lengkap', 'like', '5.1.02.02.001.%')
                                                ->orWhere('kode_lengkap', 'like', '5.1.02.04.001.%');
                                        })
                                        ->where('kode_lengkap', 'like', '5.1.02%')
                                        ->orderByDesc(DB::raw('(rincian_anggarans_count * 2) + master_barangs_count'))
                                        ->orderBy('kode_lengkap')
                                        ->get();

                                    $popularIds = $popular->pluck('id')->all();

                                    $popularOptions = $popular->mapWithKeys(fn($r) => [
                                        $r->id => "⭐ {$r->kode_lengkap} - {$r->uraian}",
                                    ])->all();

                                    $otherOptions = KodeRekening::hanyaRincianObjek()
                                        ->whereNotIn('id', $popularIds)
                                        ->where('kode_lengkap', 'like', '5.1.02%')
                                        ->orderBy('kode_lengkap')
                                        ->limit(40)
                                        ->get()
                                        ->mapWithKeys(fn($r) => [$r->id => "{$r->kode_lengkap} - {$r->uraian}"])
                                        ->all();

                                    return [
                                        '⭐ Sering Digunakan (Favorit Belanja SKPD)' => $popularOptions,
                                        'Rincian Objek Belanja Lainnya' => $otherOptions,
                                    ];
                                })
                                ->getSearchResultsUsing(function (string $search): array {
                                    $tokens = explode(' ', trim($search));

                                    $results = KodeRekening::hanyaRincianObjek()
                                        ->withCount(['rincianAnggarans', 'masterBarangs'])
                                        ->where(function ($q) use ($tokens, $search) {
                                            $q->where('kode_lengkap', 'like', "%{$search}%");
                                            $q->orWhere(function ($sub) use ($tokens) {
                                                foreach ($tokens as $token) {
                                                    if (filled($token)) {
                                                        $sub->where('uraian', 'like', "%{$token}%");
                                                    }
                                                }
                                            });
                                        })
                                        ->orderByDesc(DB::raw('CASE WHEN kode_lengkap LIKE "5.1.02%" THEN 1 ELSE 0 END'))
                                        ->orderByDesc(DB::raw('(rincian_anggarans_count * 2) + master_barangs_count'))
                                        ->orderBy('kode_lengkap')
                                        ->limit(50)
                                        ->get();

                                    return $results->mapWithKeys(function ($r) {
                                        $isPopular = ($r->rincian_anggarans_count > 0 || $r->master_barangs_count > 0);
                                        $prefix = $isPopular ? '⭐ ' : '';
                                        return [$r->id => "{$prefix}{$r->kode_lengkap} - {$r->uraian}"];
                                    })->all();
                                })
                                ->getOptionLabelUsing(fn($value): ?string => ($r = KodeRekening::find($value)) ? "{$r->kode_lengkap} - {$r->uraian}" : null)
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    $set('master_barang_id', null);

                                    // Fitur Cepat Filament: Auto-pilih jika rincian objek hanya memiliki 1 barang
                                    if ($state) {
                                        $barangs = MasterBarang::where('kode_rekening_id', $state)->with('satuan')->get();
                                        if ($barangs->count() === 1) {
                                            $barang = $barangs->first();
                                            $set('master_barang_id', $barang->id);
                                            $set('nama_item', $barang->nama_barang);
                                            if ($barang->satuan) {
                                                $set('satuan', $barang->satuan->nama);
                                            }
                                            $harga = (float) $barang->harga_satuan;
                                            $set('harga_satuan', $harga);
                                            $vol = max(1, (float) ($get('volume') ?: 1));
                                            $set('volume', $vol);
                                            $set('pagu_total', number_format($vol * $harga, 0, ',', '.'));
                                        }
                                    }
                                }),

                            Select::make('master_barang_id')
                                ->label('Komponen Belanja / Barang')
                                ->placeholder(fn(Get $get): string => filled($get('kode_rekening_id')) ? 'Pilih komponen barang...' : 'Pilih Rincian Objek terlebih dahulu')
                                ->helperText(function (Get $get): ?string {
                                    $rekId = $get('kode_rekening_id');
                                    if (! $rekId) {
                                        return null;
                                    }
                                    $count = MasterBarang::where('kode_rekening_id', $rekId)->count();
                                    return $count === 0 ? '⚠️ Belum ada komponen barang. Klik (+) untuk menambahkan.' : null;
                                })
                                ->options(function (Get $get) {
                                    $rekId = $get('kode_rekening_id');
                                    if (! $rekId) {
                                        return [];
                                    }

                                    return MasterBarang::query()
                                        ->where('kode_rekening_id', $rekId)
                                        ->with('satuan')
                                        ->orderBy('nama_barang')
                                        ->get()
                                        ->mapWithKeys(fn($b) => [$b->id => "📦 {$b->nama_barang}" . ($b->satuan ? " ({$b->satuan->nama})" : '')])
                                        ->all();
                                })
                                ->getSearchResultsUsing(function (string $search, Get $get): array {
                                    $rekId = $get('kode_rekening_id');
                                    if (! $rekId) {
                                        return [];
                                    }

                                    return MasterBarang::query()
                                        ->where('kode_rekening_id', $rekId)
                                        ->with('satuan')
                                        ->where(function ($q) use ($search) {
                                            $q->where('nama_barang', 'like', "%{$search}%")
                                                ->orWhere('uraian', 'like', "%{$search}%");
                                        })
                                        ->orderBy('nama_barang')
                                        ->limit(50)
                                        ->get()
                                        ->mapWithKeys(fn($b) => [$b->id => "📦 {$b->nama_barang}" . ($b->satuan ? " ({$b->satuan->nama})" : '')])
                                        ->all();
                                })
                                ->getOptionLabelUsing(fn($value): ?string => ($b = MasterBarang::with('satuan')->find($value)) ? "📦 {$b->nama_barang}" . ($b->satuan ? " ({$b->satuan->nama})" : '') : null)
                                ->searchable()
                                ->createOptionForm([
                                    TextInput::make('nama_barang')
                                        ->label('Nama Komponen Barang')
                                        ->placeholder('Contoh: Kertas HVS Sidu F4 75 gram / Pertamax')
                                        ->required(),
                                    Select::make('satuan_id')
                                        ->label('Satuan')
                                        ->options(Satuan::pluck('nama', 'id')->all())
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    TextInput::make('harga_satuan')
                                        ->label('Harga Satuan Standar (Rp)')
                                        ->numeric()
                                        ->prefix('Rp')
                                        ->default(0),
                                ])
                                ->createOptionUsing(function (array $data, Get $get): int {
                                    $data['kode_rekening_id'] = $get('kode_rekening_id');
                                    $barang = MasterBarang::create($data);
                                    return $barang->id;
                                })
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    if ($state) {
                                        $barang = MasterBarang::with('satuan')->find($state);
                                        if ($barang) {
                                            $set('nama_item', $barang->nama_barang);
                                            if ($barang->satuan) {
                                                $set('satuan', $barang->satuan->nama);
                                            }
                                            $harga = (float) $barang->harga_satuan;
                                            $set('harga_satuan', $harga);
                                            $vol = max(1, (float) ($get('volume') ?: 1));
                                            $set('volume', $vol);
                                            $set('pagu_total', number_format($vol * $harga, 0, ',', '.'));
                                        }
                                    }
                                })
                                ->required(),

                            Hidden::make('nama_item')
                                ->dehydrated(),
                        ]),

                        // Baris 4: Volume, Satuan, Harga Satuan, dan Total Pagu
                        Grid::make([
                            'default' => 1,
                            'sm' => 2,
                            'lg' => 4,
                        ])->schema([
                            TextInput::make('volume')
                                ->label('Volume / Koefisien')
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $vol = (float) ($get('volume') ?? 0);
                                    $harga = FormatHelper::parseRupiah($get('harga_satuan'));
                                    $pagu = $vol * $harga;
                                    $set('pagu_total', number_format($pagu, 0, ',', '.'));
                                }),

                            Select::make('satuan')
                                ->label('Satuan')
                                ->options(fn() => Satuan::pluck('nama', 'nama')->all())
                                ->default('Rim')
                                ->searchable()
                                ->required(),

                            FormatHelper::rupiahInput('harga_satuan', 'Harga Satuan')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $vol = (float) ($get('volume') ?? 0);
                                    $harga = FormatHelper::parseRupiah($get('harga_satuan'));
                                    $pagu = $vol * $harga;
                                    $set('pagu_total', number_format($pagu, 0, ',', '.'));
                                }),

                            TextInput::make('pagu_total')
                                ->label('Total Pagu Anggaran')
                                ->prefix('Rp')
                                ->readOnly()
                                ->formatStateUsing(fn($state) => filled($state) ? number_format(FormatHelper::parseRupiah($state), 0, ',', '.') : '0')
                                ->dehydrateStateUsing(fn($state) => FormatHelper::parseRupiah($state))
                                ->extraInputAttributes(['class' => 'font-bold text-amber-500']),
                        ]),
                    ]),
            ]);
    }
}
