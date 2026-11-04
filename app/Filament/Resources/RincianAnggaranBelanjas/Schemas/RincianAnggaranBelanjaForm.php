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
            ->components([
                Section::make('Pedoman Anggaran & Penugasan Bidang')
                    ->description('Tentukan tahun anggaran, bidang pengampu, dan sub kegiatan sesuai cetak dokumen RKA/DPA.')
                    ->schema([
                        Grid::make(3)->schema([
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
                                ->required(),
                        ]),
                    ]),

                Section::make('Rincian Belanja & Harga Satuan Standar')
                    ->description('Masukkan uraian barang/jasa, kode rekening pembebanan, koefisien volume, dan harga satuan.')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('kode_rekening_id')
                                ->label('Kode Rekening Belanja')
                                ->options(fn () => KodeRekening::all()->mapWithKeys(fn ($r) => [$r->id => "{$r->kode_lengkap} - {$r->uraian}"])->all())
                                ->searchable()
                                ->required()
                                ->live(),

                            Select::make('master_barang_id')
                                ->label('Tautkan ke Kode Barang Persediaan (Opsional)')
                                ->helperText('Khusus barang persediaan fisik gudang (ATK/komputer). Untuk jasa/konsumsi mamin, biarkan kosong.')
                                ->options(function (Get $get) {
                                    $rekId = $get('kode_rekening_id');
                                    return MasterBarang::query()
                                        ->when($rekId, fn ($q) => $q->where('kode_rekening_id', $rekId))
                                        ->get()
                                        ->mapWithKeys(fn ($b) => [$b->id => "📦 {$b->kode_barang} - {$b->nama_barang}"])
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
                                            if ((float) $barang->harga_satuan > 0 && blank($get('harga_satuan'))) {
                                                $set('harga_satuan', $barang->harga_satuan);
                                            }
                                        }
                                    }
                                }),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('nama_item')
                                ->label('Uraian Nama Barang / Jasa')
                                ->required()
                                ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                                ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? mb_strtoupper($state, 'UTF-8') : null)
                                ->placeholder('Contoh: Kertas HVS Sidu, ukuran F4, 75 gram'),

                            TextInput::make('spesifikasi')
                                ->label('Spesifikasi / Merk / Keterangan Teknis')
                                ->placeholder('Contoh: F4 75 gram, isi 500 lembar per rim'),
                        ]),

                        Grid::make(4)->schema([
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
                                ->label('Satuan')
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
                                ->label('Total Pagu Anggaran (Rp)')
                                ->numeric()
                                ->prefix('Rp')
                                ->readOnly()
                                ->dehydrated(),
                        ]),

                        Grid::make(2)->schema([
                            TextInput::make('sumber_dana')
                                ->label('Sumber Pendanaan')
                                ->default('Dana Alokasi Umum (DAU)')
                                ->required(),

                            Textarea::make('keterangan')
                                ->label('Catatan Tambahan')
                                ->rows(2)
                                ->placeholder('Catatan atau peruntukan belanja'),
                        ]),
                    ]),
            ]);
    }
}

