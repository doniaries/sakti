<?php

namespace App\Filament\Resources\MasterTokos\Schemas;

use App\Enums\NamaBank;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MasterTokoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Data Toko / Rekanan Penyedia')
                    ->compact()
                    ->columns(2)
                    ->schema([
                        TextInput::make('nama_toko')
                            ->label('Nama Toko / Pemasok')
                            ->placeholder('Contoh: Toko Rilex Abadi')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('nama_pemilik')
                            ->label('Nama Pemilik Toko')
                            ->placeholder('Contoh: Agusrizal')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('telepon')
                            ->label('No. Telepon')
                            ->placeholder('Contoh: 0812-3456-7890')
                            ->maxLength(50),

                        TextInput::make('npwp')
                            ->label('NPWP Toko (Opsional)')
                            ->placeholder('Contoh: 01.234.567.8-201.000')
                            ->maxLength(50),

                        Select::make('nama_bank')
                            ->label('Nama Bank')
                            ->options(NamaBank::class)
                            ->default(NamaBank::BANK_NAGARI)
                            ->searchable(),

                        TextInput::make('nomor_rekening')
                            ->label('No. Rekening Bank Toko')
                            ->placeholder('Contoh: 0700.0210.21699-2')
                            ->maxLength(100),

                        Textarea::make('alamat')
                            ->label('Alamat Toko')
                            ->placeholder('Contoh: Pasar Inpres Muaro Sijunjung')
                            ->required()
                            ->rows(2)
                            ->columnSpan(2),

                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->columnSpan(2),
                    ]),
            ]);
    }
}
