<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                \Filament\Schemas\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                \Filament\Schemas\Components\TextInput::make('password')
                    ->password()
                    ->required(fn ($operation) => $operation === 'create')
                    ->maxLength(255)
                    ->dehydrated(fn ($state) => filled($state)),
                \Filament\Schemas\Components\TextInput::make('nip')
                    ->maxLength(30),
                \Filament\Schemas\Components\TextInput::make('nomor_absensi')
                    ->maxLength(50),
                \Filament\Schemas\Components\Select::make('roles')
                    ->relationship('roles', 'name', fn ($query) => auth()->user()->hasRole('super_admin') ? $query : $query->where('name', '!=', 'super_admin'))
                    ->multiple()
                    ->preload()
                    ->searchable(),
                \Filament\Schemas\Components\Toggle::make('aktif')
                    ->default(true),
            ]);
    }
}
