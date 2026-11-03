<?php

namespace App\Filament\Resources\MasterBanks\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MasterBankForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Bank')
                    ->compact()
                    ->columns(2)
                    ->schema([
                        TextInput::make('kode_bank')
                            ->label('Kode Bank')
                            ->placeholder('Contoh: 118')
                            ->maxLength(20),

                        TextInput::make('nama_bank')
                            ->label('Nama Bank')
                            ->placeholder('Contoh: Bank Nagari (BPD Sumbar)')
                            ->required()
                            ->maxLength(255),

                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->columnSpan(2),
                    ]),
            ]);
    }
}
