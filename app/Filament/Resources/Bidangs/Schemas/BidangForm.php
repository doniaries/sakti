<?php

namespace App\Filament\Resources\Bidangs\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BidangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama')
                    ->label('Nama Bidang')
                    ->placeholder('tambahkan tanpa menggunakan kata bidang')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn(callable $set, ?string $state) => $set('slug', Str::slug($state ?? ''))),

                Hidden::make('slug')
                    ->dehydrated(),

                Select::make('kepala_id')
                    ->label('Kepala Bidang / Sekretaris')
                    ->relationship('kepala', 'name', fn($query) => $query->bukanSuperAdmin())
                    ->searchable()
                    ->preload(),
            ]);
    }
}
