<?php

namespace App\Filament\Resources\KodeRekenings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KodeRekeningForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Rekening Belanja')
                    ->description('Kelola nomor kode rekening dan uraian belanja.')
                    ->schema([
                        Grid::make(1)
                            ->schema([
                                TextInput::make('kode_lengkap')
                                    ->label('Kode Rekening Belanja')
                                    ->placeholder('5.1.01.01.01.0001')
                                    ->mask('9.9.99.99.99.9999')
                                    ->helperText('Format: 5.1.01.01.01.0001 (Titik otomatis terbentuk saat mengetik angka, kode harus unik).')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->extraInputAttributes(['class' => 'font-mono']),

                                TextInput::make('uraian')
                                    ->label('Uraian Rekening Belanja')
                                    ->placeholder('Contoh: BELANJA GAJI POKOK PNS')
                                    ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? mb_strtoupper($state, 'UTF-8') : null)
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
