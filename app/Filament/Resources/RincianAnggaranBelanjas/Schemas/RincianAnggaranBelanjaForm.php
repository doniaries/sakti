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
                // Section 1: Pembebanan Sub Kegiatan & Sumber Pendanaan
                Section::make('Sub Kegiatan & Pembebanan Anggaran')
                    ->description('Tentukan tahun anggaran, bidang pengampu, sumber pendanaan, dan sub kegiatan sesuai Dokumen Pelaksanaan Anggaran (DPA/RKA).')
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
                                ->live(onBlur: true),

                            Select::make('bidang_id')
                                ->label('Bidang Pengampu')
                                ->options(fn () => Bidang::pluck('nama', 'id')->all())
                                ->default(fn () => Bidang::where('nama', 'LIKE', '%Pemasaran%')->value('id') ?? Bidang::first()?->id)
                                ->required()
                                ->live(onBlur: true),

                            Select::make('sumber_dana')
                                ->label('Sumber Pendanaan')
                                ->options(SumberDana::class)
                                ->default(SumberDana::DAU->value)
                                ->required(),

                            Select::make('sub_kegiatan_id')
                                ->label('Sub Kegiatan')
                                ->visible(fn (Get $get): bool => filled($get('bidang_id')))
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
                                ->live()
                                ->required(),
                        ]),
                    ]),

                // Section 2: Objek & Komponen Belanja (Muncul bertahap setelah Sub Kegiatan dipilih)
                Section::make('Objek & Komponen Belanja SIPD')
                    ->description('Pilih rekening belanja dan komponen belanja yang akan dibebankan sesuai rincian SIPD.')
                    ->icon('heroicon-o-shopping-bag')
                    ->visible(fn (Get $get): bool => filled($get('sub_kegiatan_id')))
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
                                ->helperText('Pilih komponen/produk belanja. Transaksi SPJ akan mengurangi volume pagu komponen ini.')
                                ->visible(fn (Get $get): bool => filled($get('kode_rekening_id')))
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

                            TextInput::make('spesifikasi')
                                ->label('Spesifikasi Teknis / Merk Barang')
                                ->placeholder('Contoh: Kertas HVS Sidu, ukuran F4, 75 gram / Pertamax')
                                ->visible(fn (Get $get): bool => filled($get('master_barang_id')))
                                ->columnSpanFull(),
                        ]),
                    ]),

                // Section 3: Kalkulasi Koefisien Volume & Pagu Anggaran (Muncul bertahap setelah Komponen Belanja dipilih)
                Section::make('Kalkulasi Koefisien Volume & Pagu Anggaran')
                    ->description('Hitung total pagu anggaran berdasarkan koefisien volume dan harga satuan standar belanja.')
                    ->icon('heroicon-o-calculator')
                    ->visible(fn (Get $get): bool => filled($get('master_barang_id')))
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
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Get $get, Set $set) {
                                    $vol = (float) ($get('volume') ?? 0);
                                    $harga = FormatHelper::parseRupiah($get('harga_satuan'));
                                    $pagu = $vol * $harga;
                                    $set('pagu_total', number_format($pagu, 0, ',', '.'));
                                }),

                            Select::make('satuan')
                                ->label('Satuan Unit')
                                ->options(fn () => Satuan::pluck('nama', 'nama')->all())
                                ->default('Rim')
                                ->searchable()
                                ->required(),

                            FormatHelper::rupiahInput('harga_satuan', 'Harga Satuan Standar')
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
                                ->formatStateUsing(fn ($state) => filled($state) ? number_format(FormatHelper::parseRupiah($state), 0, ',', '.') : '0')
                                ->dehydrateStateUsing(fn ($state) => FormatHelper::parseRupiah($state))
                                ->extraInputAttributes(['class' => 'font-bold text-amber-500'])
                                ->helperText('Kalkulasi otomatis (Volume x Harga Satuan)'),
                        ]),
                    ]),
            ]);
    }
}
