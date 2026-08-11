<?php

namespace App\Filament\Resources\AbsensiHarians\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class AbsensiHarianForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Informasi Absensi Harian')
                    ->schema([
                        Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        DatePicker::make('tanggal')->displayFormat('d/m/Y')
                            ->required(),
                        TimePicker::make('jam_masuk')
                            ->displayFormat('H:i:s'),
                        TimePicker::make('jam_pulang')
                            ->displayFormat('H:i:s'),
                        Select::make('status')
                            ->options([
                                'hadir' => 'Hadir',
                                'terlambat' => 'Terlambat',
                                'tidak_hadir' => 'Tidak Hadir',
                                'dinas_luar' => 'Dinas Luar',
                                'cuti' => 'Cuti',
                                'izin' => 'Izin',
                                'sakit' => 'Sakit',
                                'libur' => 'Libur',
                            ])
                            ->default('hadir')
                            ->required(),
                        Select::make('status_apel')
                            ->label('Status Apel')
                            ->options([
                                'apel' => 'Apel',
                                'tidak_apel' => 'Tidak Apel',
                            ]),
                        Textarea::make('keterangan')
                            ->columnSpanFull(),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Sumber Absensi')
                    ->schema([
                        \Filament\Forms\Components\Toggle::make('is_absen_mesin')
                            ->label('Dari Mesin'),
                        \Filament\Forms\Components\Toggle::make('is_absen_mobile')
                            ->label('Dari Mobile'),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Bukti Mobile')
                    ->schema([
                        \Filament\Forms\Components\FileUpload::make('foto_masuk')
                            ->label('Foto Masuk')
                            ->image()
                            ->directory('absensi-mobile'),
                        \Filament\Forms\Components\FileUpload::make('foto_pulang')
                            ->label('Foto Pulang')
                            ->image()
                            ->directory('absensi-mobile'),
                    ])->columns(2),
            ]);
    }
}
