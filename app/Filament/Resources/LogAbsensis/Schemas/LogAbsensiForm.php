<?php

namespace App\Filament\Resources\LogAbsensis\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;

class LogAbsensiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name', fn ($query) => $query->orderBy('id'))
                    ->searchable()
                    ->preload(),
                Select::make('mesin_absensi_id')
                    ->relationship('mesinAbsensi', 'nama')
                    ->searchable()
                    ->preload(),
                TextInput::make('nomor_absensi')
                    ->required()
                    ->maxLength(50),
                DateTimePicker::make('waktu_absensi')
                    ->displayFormat('d/m/Y H:i')
                    ->native(false)
                    ->required(),
                Select::make('perangkat')
                    ->options([
                        'mesin' => 'Mesin',
                        'mobile' => 'Mobile',
                    ])
                    ->default('mesin')
                    ->required(),
                TextInput::make('metode_verifikasi')
                    ->maxLength(255),
                TextInput::make('jenis')
                    ->maxLength(255),
                TextInput::make('kode_log_asli')
                    ->maxLength(255),
            ]);
    }
}
