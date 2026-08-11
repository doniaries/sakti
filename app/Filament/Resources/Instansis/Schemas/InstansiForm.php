<?php

namespace App\Filament\Resources\Instansis\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InstansiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_dinas')
                    ->required(),
                \Filament\Forms\Components\Select::make('nama_kepala_dinas')
                    ->options(fn() => \App\Models\User::role('pegawai')->pluck('name', 'name')->toArray())
                    ->searchable()
                    ->placeholder('Pilih Nama Pegawai'),
                Textarea::make('alamat')
                    ->columnSpanFull(),
                \Filament\Forms\Components\FileUpload::make('logo')
                    ->image()
                    ->directory('instansis')
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                    ->helperText('Format yang diizinkan: PNG, JPG, JPEG. Maksimal 2MB.'),
            ]);
    }
}
