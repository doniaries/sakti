<?php

namespace App\Filament\Resources\Aktivitas\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AktivitasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode'),
                TextInput::make('nama_aktivitas')
                    ->required(),
                TextInput::make('menit')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
