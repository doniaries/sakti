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
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('tanggal')->displayFormat('d/m/Y')
                    ->displayFormat('d/m/Y')
                    ->required(),
                TimePicker::make('jam_masuk')
                    ->displayFormat('H:i'),
                TimePicker::make('jam_pulang')
                    ->displayFormat('H:i'),
                Select::make('log_masuk_id')
                    ->relationship('logMasuk', 'nomor_absensi')
                    ->searchable()
                    ->preload(),
                Select::make('log_pulang_id')
                    ->relationship('logPulang', 'nomor_absensi')
                    ->searchable()
                    ->preload(),
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
                Textarea::make('keterangan')
                    ->columnSpanFull(),
            ]);
    }
}
