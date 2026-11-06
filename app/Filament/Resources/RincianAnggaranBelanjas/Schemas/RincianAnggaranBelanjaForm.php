<?php

namespace App\Filament\Resources\RincianAnggaranBelanjas\Schemas;

use App\Models\Bidang;
use App\Models\KodeRekening;
use App\Models\MasterBarang;
use App\Models\MasterSubKegiatan;
use App\Models\Satuan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class RincianAnggaranBelanjaForm
{
    public static function configure(Schema $schema): Schema
    {
        $currentYear = (int) date('Y');
        $years = [];
        for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++) {
            $years[$y] = "Tahun {$y}";
        }

        return $schema
            ->columns(1)
            ->components([
                // Section 1: Pembebanan Sub Kegiatan
                Section::make('Sub Kegiatan & Pembebanan Anggaran')
                    ->description('Tentukan tahun anggaran, bidang pengampu, dan sub kegiatan sesuai Dokumen Pelaksanaan Anggaran (DPA/RKA).')
                    ->icon('heroicon-o-building-office-2')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 4,
                        ])->schema([
                            Select::make('tahun_anggaran')
                                ->label('Tahun Anggaran')
                                ->options($years)
                                ->default($currentYear)
                                ->required()
                                ->live(),

                            Select::make('bidang_id')
                                ->label('Bidang Pengampu')
                                ->options(fn () => Bidang::pluck('nama', 'id')->all())
                                ->default(fn () => Bidang::where('nama', 'LIKE', '%Pemasaran%')->value('id') ?? Bidang::first()?->id)
                                ->required()
                                ->live(),

                            Select::make('sub_kegiatan_id')
                                ->label('Sub Kegiatan')
                                ->options(function (Get $get) {
                                    $tahun = $get('tahun_anggaran') ?? (int) date('Y');
                                    $bidangId = $get('bidang_id');

                                    return MasterSubKegiatan::query()
                                        ->where('tahun_anggaran', $tahun)
                                        ->when($bidangId, fn ($q) => $q->whereHas('kegiatan', fn ($k) => $k->where('bidang_id', $bidangId)))
                                        ->get()
                                        ->mapWithKeys(fn ($item) => [$item->id => "{$item->kode_sub_kegiatan} - {$item->nama_sub_kegiatan}"])
                                        ->all();
                                })
                                ->searchable()
                                ->required()
                                ->columnSpan([
                                    'default' => 1,
                                    'md' => 2,
                                ]),
                        ]),
                    ]),

                // Section 2: Objek & Komponen Belanja
                Section::make('Objek & Komponen Belanja SIPD')
                    ->description('Pilih rekening belanja dan komponen belanja yang akan dibebankan sesuai rincian SIPD.')
                    ->icon('heroicon-o-shopping-bag')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('kode_rekening_id')
                                ->label('Kode Rekening Belanja')
                                ->options(fn () => KodeRekening::all()->mapWithKeys(fn ($r) => [$r->id => "{$r->kode_lengkap} - {$r->uraian}"])->all())
                                ->searchable()
                                ->required()
                                ->live(),

                            Select::make('master_barang_id')
                                ->label('Komponen Belanja')
                                ->helperText('Pilih komponen/produk belanja (BBM, Kertas & Cover, Natura, ATK). Transaksi SPJ akan mengurangi volume pagu komponen ini.')
                                ->options(function (Get $get) {
                                    $rekId = $get('kode_rekening_id');
                                    return MasterBarang::query()
                                        ->when($rekId, fn ($q) => $q->where('kode_rekening_id', $rekId))
                                        ->with('satuan')
                                        ->get()
                                        ->mapWithKeys(fn ($b) => [$b->id => "📦 {$b->nama_barang}" . ($b->satuan ? " ({$b->satuan->nama})" : '')])
                                        ->all();
                                })
                                ->searchable()
                                ->live()
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    if ($state) {
                                        $barang = MasterBarang::with('satuan')->find($state);
                                        if ($barang) {
                                            if (blank($get('nama_item'))) {
                                                $set('nama_item', $barang->nama_barang);
                                            }
                                            if ($barang->satuan) {
                                                $set('satuan', $barang->satuan->nama);
                                            }
                                        }
                                    }
                                }),

                            TextInput::make('nama_item')
                                ->label('Uraian Belanja [-]')
                                ->required()
                                ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                                ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? mb_strtoupper($state, 'UTF-8') : null)
                                ->placeholder('Contoh: KERTAS HVS F4, NASI GORENG, BBM PERTAMAX'),

                            TextInput::make('spesifikasi')
                                ->label('Spesifikasi Teknis / Merk Barang')
                                ->placeholder('Contoh: Kertas HVS Sidu, ukuran F4, 75 gram'),
                        ]),
                    ]),

                // Section 3: Kalkulasi Koefisien & Pagu Anggaran
                Section::make('Kalkulasi Koefisien Volume & Pagu Anggaran')
                    ->description('Hitung total pagu anggaran berdasarkan koefisien volume dan harga satuan standar belanja.')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 4,
                        ])->schema([
                            TextInput::make('volume')
                                ->label('Koefisien / Volume Pagu')
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $vol = (float) ($get('volume') ?? 0);
                                    $harga = (float) ($get('harga_satuan') ?? 0);
                                    $set('pagu_total', $vol * $harga);
                                }),

                            Select::make('satuan')
                                ->label('Satuan Unit')
                                ->options(fn () => Satuan::pluck('nama', 'nama')->all())
                                ->default('Rim')
                                ->searchable()
                                ->required(),

                            TextInput::make('harga_satuan')
                                ->label('Harga Satuan Standar')
                                ->numeric()
                                ->prefix('Rp')
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $vol = (float) ($get('volume') ?? 0);
                                    $harga = (float) ($get('harga_satuan') ?? 0);
                                    $set('pagu_total', $vol * $harga);
                                }),

                            TextInput::make('pagu_total')
                                ->label('Total Pagu Anggaran')
                                ->numeric()
                                ->prefix('Rp')
                                ->readOnly()
                                ->dehydrated()
                                ->helperText('Kalkulasi otomatis (Volume x Harga Satuan)'),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('sumber_dana')
                                ->label('Sumber Pendanaan')
                                ->default('Dana Alokasi Umum (DAU)')
                                ->required(),

                            TextInput::make('keterangan')
                                ->label('Catatan Tambahan / Peruntukan')
                                ->placeholder('Contoh: Kegiatan Sosialisasi Pariwisata Triwulan I'),
                        ]),
                    ]),
            ]);
    }
}

