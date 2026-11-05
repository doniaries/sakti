<?php

namespace App\Filament\Resources\MasterTujuanBbms\Schemas;

use App\Models\Kecamatan;
use App\Models\Nagari;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MasterTujuanBbmForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Standar Rute & Kuota BBM Kedinasan')
                    ->description('Kelola daftar tujuan perjalanan dinas per nagari / rute beserta batas pagu kuota BBM (SBM).')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('kategori_wilayah')
                                    ->label('Kategori Wilayah')
                                    ->options([
                                        'dalam_kabupaten' => 'Dalam Kabupaten (Per Nagari)',
                                        'luar_kabupaten' => 'Luar Kabupaten (Dalam Provinsi)',
                                        'luar_provinsi' => 'Luar Provinsi',
                                    ])
                                    ->default('dalam_kabupaten')
                                    ->live()
                                    ->required(),

                                Select::make('kecamatan_id')
                                    ->label('Kecamatan (Sijunjung)')
                                    ->options(fn () => Kecamatan::pluck('nama_kecamatan', 'id')->all())
                                    ->searchable()
                                    ->live()
                                    ->visible(fn (callable $get) => $get('kategori_wilayah') === 'dalam_kabupaten')
                                    ->afterStateUpdated(fn (callable $set) => $set('nagari_id', null)),

                                Select::make('nagari_id')
                                    ->label('Nagari Tujuan')
                                    ->options(function (callable $get) {
                                        $kecId = $get('kecamatan_id');
                                        return Nagari::query()
                                            ->when($kecId, fn ($q) => $q->where('kecamatan_id', $kecId))
                                            ->pluck('nama_nagari', 'id')
                                            ->all();
                                    })
                                    ->searchable()
                                    ->live()
                                    ->visible(fn (callable $get) => $get('kategori_wilayah') === 'dalam_kabupaten')
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $nagari = Nagari::with('kecamatan')->find($state);
                                            if ($nagari) {
                                                $set('nama_tujuan', "{$nagari->nama_nagari} (Kec. {$nagari->kecamatan?->nama_kecamatan})");
                                                if ($nagari->kuota_liter_bbm > 0) {
                                                    $set('kuota_liter', $nagari->kuota_liter_bbm);
                                                }
                                                if ($nagari->jarak_km > 0) {
                                                    $set('jarak_km', $nagari->jarak_km);
                                                }
                                            }
                                        }
                                    })
                                    ->createOptionForm([
                                        Select::make('kecamatan_id')
                                            ->label('Kecamatan')
                                            ->options(fn () => Kecamatan::pluck('nama_kecamatan', 'id')->all())
                                            ->required(),
                                        TextInput::make('nama_nagari')
                                            ->label('Nama Nagari Baru')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('kuota_liter_bbm')
                                            ->label('Standar Kuota BBM (Liter)')
                                            ->numeric()
                                            ->default(20),
                                        TextInput::make('jarak_km')
                                            ->label('Jarak Perkiraan (KM)')
                                            ->numeric()
                                            ->nullable(),
                                    ])
                                    ->createOptionUsing(function (array $data): int {
                                        $nagari = Nagari::create($data);
                                        return $nagari->id;
                                    }),
                            ]),

                        TextInput::make('nama_tujuan')
                            ->label('Nama Rute / Lokasi Tujuan')
                            ->placeholder('Contoh: Nagari Silokek (Kec. Sijunjung) atau Kota Padang')
                            ->required()
                            ->maxLength(255),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('kuota_liter')
                                    ->label('Standar Kuota BBM (Liter)')
                                    ->numeric()
                                    ->suffix('Liter')
                                    ->required(),

                                TextInput::make('jarak_km')
                                    ->label('Perkiraan Jarak Tempuh (KM)')
                                    ->numeric()
                                    ->suffix('KM')
                                    ->nullable(),

                                Select::make('jenis_kendaraan')
                                    ->label('Jenis Kendaraan')
                                    ->options([
                                        'roda_4' => 'Kendaraan Roda 4 (Mobil Dinas)',
                                        'roda_2' => 'Kendaraan Roda 2 (Motor Dinas)',
                                        'bus' => 'Bus / Kendaraan Khusus',
                                    ])
                                    ->default('roda_4')
                                    ->required(),
                            ]),

                        Textarea::make('keterangan')
                            ->label('Keterangan / Dasar Penetapan SBM')
                            ->placeholder('Contoh: Standar kuota BBM resmi Kabupaten Sijunjung')
                            ->rows(2),

                        Toggle::make('aktif')
                            ->label('Status Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
