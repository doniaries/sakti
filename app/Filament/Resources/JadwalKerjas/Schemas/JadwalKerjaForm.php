<?php

namespace App\Filament\Resources\JadwalKerjas\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JadwalKerjaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Jam Kerja / Shift')
                    ->description('Konfigurasi shift kerja harian pegawai (Reguler dan Bulan Ramadhan).')
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Jadwal / Shift')
                            ->placeholder('Contoh: Jadwal Reguler / Jadwal Ramadhan')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('kode')
                            ->label('Kode Unik')
                            ->placeholder('Contoh: reguler, ramadhan')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        Grid::make(2)
                            ->schema([
                                Section::make('Jam Kerja: Senin - Kamis')
                                    ->schema([
                                        TimePicker::make('jam_masuk_senin_kamis')
                                            ->label('Jam Masuk')
                                            ->seconds(false)
                                            ->default('07:30')
                                            ->required(),
                                        TimePicker::make('jam_pulang_senin_kamis')
                                            ->label('Jam Pulang')
                                            ->seconds(false)
                                            ->default('16:00')
                                            ->required(),
                                    ])->columns(2),

                                Section::make('Jam Kerja: Jumat')
                                    ->schema([
                                        TimePicker::make('jam_masuk_jumat')
                                            ->label('Jam Masuk')
                                            ->seconds(false)
                                            ->default('07:30')
                                            ->required(),
                                        TimePicker::make('jam_pulang_jumat')
                                            ->label('Jam Pulang')
                                            ->seconds(false)
                                            ->default('16:30')
                                            ->required(),
                                    ])->columns(2),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('toleransi_terlambat_menit')
                                    ->label('Toleransi Terlambat')
                                    ->numeric()
                                    ->suffix('menit')
                                    ->default(15)
                                    ->required(),

                                Toggle::make('is_default')
                                    ->label('Jadwal Default Utama')
                                    ->helperText('Otomatis berlaku untuk semua pegawai yang tidak memiliki shift khusus.')
                                    ->default(false),

                                Toggle::make('is_aktif')
                                    ->label('Status Aktif')
                                    ->default(true),
                            ]),

                        Textarea::make('keterangan')
                            ->label('Keterangan / Catatan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
