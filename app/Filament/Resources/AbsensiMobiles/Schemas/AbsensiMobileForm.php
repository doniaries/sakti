<?php

namespace App\Filament\Resources\AbsensiMobiles\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AbsensiMobileForm
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
                DateTimePicker::make('waktu_absensi')
                    ->displayFormat('d/m/Y H:i')
                    ->required(),
                Select::make('jenis')
                    ->options([
                        'masuk' => 'Masuk',
                        'pulang' => 'Pulang',
                        'dinas_luar' => 'Dinas Luar',
                        'tugas_lapangan' => 'Tugas Lapangan',
                    ])
                    ->required(),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                TextInput::make('akurasi_lokasi')
                    ->numeric(),
                FileUpload::make('foto')
                    ->image()
                    ->directory('absensi-mobile'),
                Textarea::make('keterangan')
                    ->columnSpanFull(),
                TextInput::make('perangkat_id')
                    ->maxLength(255),
            ]);
    }
}
