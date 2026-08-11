<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn ($operation) => $operation === 'create')
                    ->maxLength(255)
                    ->dehydrated(fn ($state) => filled($state)),
                \Filament\Forms\Components\TextInput::make('nip')
                    ->maxLength(30),
                \Filament\Forms\Components\TextInput::make('nomor_absensi')
                    ->maxLength(50),
                \Filament\Forms\Components\Select::make('bidang_id')
                    ->relationship('bidang', 'nama')
                    ->searchable()
                    ->preload(),
                \Filament\Forms\Components\Select::make('jabatan_id')
                    ->relationship('jabatan', 'nama')
                    ->searchable()
                    ->preload(),
                \Filament\Forms\Components\Select::make('atasan_id')
                    ->label('Atasan Langsung')
                    ->relationship('atasan', 'name')
                    ->searchable()
                    ->preload(),
                \Filament\Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name', fn ($query) => auth()->user()->hasRole('super_admin') ? $query : $query->where('name', '!=', 'super_admin'))
                    ->multiple()
                    ->preload()
                    ->searchable(),
                \Filament\Forms\Components\Select::make('jenis_kelamin')
                    ->options(\App\Enums\JenisKelamin::class),
                \Filament\Forms\Components\Select::make('pangkat_golongan')
                    ->options(\App\Enums\GolonganRuang::class)
                    ->searchable(),
                \Filament\Forms\Components\Select::make('eselon')
                    ->options(\App\Enums\Eselon::class),
                \Filament\Forms\Components\Toggle::make('aktif')
                    ->default(true),
            ]);
    }
}
