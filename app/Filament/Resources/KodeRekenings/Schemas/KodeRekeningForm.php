<?php

namespace App\Filament\Resources\KodeRekenings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KodeRekeningForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Rekening Belanja')
                    ->description('Masukkan nomor kode rekening dan uraian belanja sesuai standar Kepmendagri / SIPD RI.')
                    ->schema([
                        TextInput::make('kode_lengkap')
                            ->label('Kode Rekening Belanja')
                            ->placeholder('Contoh: 5.1.02.01.001.00024 atau 5.1.01.01.01.0001')
                            ->helperText('Nomor kode rekening berjenjang menggunakan pemisah titik (.) sesuai dokumen DPA/RKA atau daftar Kepmendagri.')
                            ->required()
                            ->unique(ignoreRecord: true),

                        TextInput::make('uraian')
                            ->label('Uraian Rekening Belanja')
                            ->placeholder('Contoh: Belanja Bahan-Bahan Bakar dan Pelumas')
                            ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? mb_strtoupper($state, 'UTF-8') : null)
                            ->required(),
                    ]),
            ]);
    }
}
