<?php

namespace App\Filament\Resources\MasterJenisBelanjas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MasterJenisBelanjaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Jenis Pekerjaan / Belanja SPJ')
                    ->description('Kelola kelompok transaksi belanja kedinasan dan lingkup pengadaannya.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('nama')
                                    ->label('Nama Jenis Pekerjaan')
                                    ->placeholder('Contoh: Pemeliharaan Kendaraan Dinas')
                                    ->required()
                                    ->maxLength(100)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if (blank($get('kode'))) {
                                            $set('kode', Str::slug($state, '_'));
                                        }
                                    }),

                                Select::make('metode_pengadaan')
                                    ->label('Metode Pengadaan')
                                    ->options([
                                        'non_katalog' => 'Non-Katalog (Pengadaan Langsung)',
                                        'e_katalog' => 'E-Katalog LKPP (E-Purchasing)',
                                        'semua' => 'Semua Metode (Lintas Pengadaan)',
                                    ])
                                    ->default('non_katalog')
                                    ->required(),
                            ]),

                        TextInput::make('kode')
                            ->label('Kode Sistem / Kategori')
                            ->helperText('Format kode slug sistem (contoh: barang_jasa, makan_minum, sppd)')
                            ->required()
                            ->maxLength(100),

                        Textarea::make('deskripsi')
                            ->label('Deskripsi / Petunjuk Pengajuan (Opsional)')
                            ->placeholder('Jelaskan ruang lingkup transaksi belanja ini...')
                            ->rows(2),

                        Toggle::make('aktif')
                            ->label('Status Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}

