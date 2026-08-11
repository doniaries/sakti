<?php

namespace App\Filament\Resources\Bidangs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BidangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                \Filament\Forms\Components\Select::make('kepala_id')
                    ->label('Kepala Bidang/Sekretaris')
                    ->relationship('kepala', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }
}
