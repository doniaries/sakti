<?php

namespace App\Filament\Resources\MesinAbsensis\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class MesinAbsensiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
                TextInput::make('merek')
                    ->maxLength(255),
                TextInput::make('model')
                    ->maxLength(255),
                TextInput::make('alamat_ip')
                    ->required()
                    ->maxLength(255),
                TextInput::make('port')
                    ->numeric()
                    ->default(4370),
                TextInput::make('nomor_seri')
                    ->maxLength(255),
                Select::make('jenis_koneksi')
                    ->options([
                        'tcp_ip' => 'TCP/IP',
                        'usb' => 'USB',
                    ])
                    ->default('tcp_ip')
                    ->required(),
                Toggle::make('aktif')
                    ->default(true),
            ]);
    }
}
