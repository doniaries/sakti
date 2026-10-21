<?php

namespace App\Filament\Resources\Jabatans\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class JabatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->label('Nama Jabatan')
                    ->placeholder('Contoh: Analis Kebijakan Ahli Muda')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (callable $set, ?string $state) => $set('slug', Str::slug($state ?? '')))
                    ->dehydrateStateUsing(fn ($state) => ucwords(strtolower($state))),

                Hidden::make('slug')
                    ->dehydrated(),
            ]);
    }
}
