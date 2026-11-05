<?php

namespace App\Filament\Resources\MasterTujuanBbms\Schemas;

use App\Models\Kecamatan;
use App\Models\Nagari;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                Section::make('Standar Rute & Kuota BBM')
                    ->description('Tentukan nama tujuan / nagari dan pagu standar kuota liter BBM.')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Select::make('kategori_wilayah')
                                    ->label('Kategori Wilayah')
                                    ->options([
                                        'dalam_kabupaten' => 'Dalam Kabupaten (Nagari)',
                                        'luar_kabupaten' => 'Luar Kabupaten',
                                        'luar_provinsi' => 'Luar Provinsi',
                                    ])
                                    ->default('dalam_kabupaten')
                                    ->live()
                                    ->required(),

                                Select::make('kecamatan_id')
                                    ->label('Pilih Kecamatan Tujuan')
                                    ->options(fn() => Kecamatan::pluck('nama_kecamatan', 'id')->all())
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->visible(fn(callable $get) => $get('kategori_wilayah') === 'dalam_kabupaten')
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $set('nagari_id', null);
                                        // Jika user hanya memilih kecamatan dan belum memilih nagari
                                        if ($state && empty($get('nagari_id'))) {
                                            $kec = Kecamatan::find($state);
                                            if ($kec) {
                                                $set('nama_tujuan', $kec->nama_kecamatan);
                                            }
                                        }
                                    }),

                                Select::make('nagari_id')
                                    ->label('Pilih Nagari Tujuan')
                                    ->options(function (callable $get) {
                                        $kecId = $get('kecamatan_id');
                                        return Nagari::query()
                                            ->when($kecId, fn($q) => $q->where('kecamatan_id', $kecId))
                                            ->pluck('nama_nagari', 'id')
                                            ->all();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->visible(fn(callable $get) => $get('kategori_wilayah') === 'dalam_kabupaten')
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $nagari = Nagari::find($state);
                                            if ($nagari) {
                                                // Cukup nama nagari saja, tidak berlebihan
                                                $set('nama_tujuan', $nagari->nama_nagari);
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
                                            ->options(fn() => Kecamatan::pluck('nama_kecamatan', 'id')->all())
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
                                            ->label('Jarak (KM)')
                                            ->numeric()
                                            ->nullable(),
                                    ])
                                    ->createOptionUsing(function (array $data): int {
                                        $nagari = Nagari::create($data);
                                        return $nagari->id;
                                    }),
                            ]),

                        TextInput::make('nama_tujuan')
                            ->label('Nama Lokasi / Rute Tujuan')
                            ->placeholder('Contoh: Nagari Aia Amo atau Kota Padang')
                            ->required()
                            ->maxLength(255),

                        Grid::make(2)
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
                            ]),

                        Hidden::make('jenis_kendaraan')
                            ->default('semua'),

                        Toggle::make('aktif')
                            ->label('Status Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
