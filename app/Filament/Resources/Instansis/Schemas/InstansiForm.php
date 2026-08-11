<?php

namespace App\Filament\Resources\Instansis\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class InstansiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_dinas')
                    ->required(),
                Select::make('nama_kepala_dinas')
                    ->options(fn () => \App\Models\User::role('pegawai')->pluck('name', 'name')->toArray())
                    ->searchable()
                    ->placeholder('Pilih Nama Pegawai'),
                Textarea::make('alamat')
                    ->columnSpanFull(),
                FileUpload::make('logo')
                    ->image()
                    ->directory('instansi-logos')
                    ->maxSize(2048) // max 2MB
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg'])
                    ->helperText('Format yang diizinkan: PNG, JPG, JPEG. Maksimal 2MB.'),
            ]);
    }
}
