<?php

namespace App\Filament\Resources\Instansis\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class InstansiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_dinas')
                    ->required(),
                TextInput::make('nama_kepala_dinas'),
                Textarea::make('alamat')
                    ->columnSpanFull(),
                TextInput::make('logo'),
            ]);
    }
}
