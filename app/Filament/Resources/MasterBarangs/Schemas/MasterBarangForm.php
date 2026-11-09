<?php

namespace App\Filament\Resources\MasterBarangs\Schemas;

use App\Models\KodeRekening;
use App\Support\FormatHelper;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MasterBarangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Komponen Belanja')
                    ->description('Tautkan komponen/nama produk belanja ke kode rekening belanja yang sesuai dengan cetak RKA/DPA SIPD.')
                    ->schema([
                        Select::make('kode_rekening_id')
                            ->label('Kode Rekening Belanja')
                            ->relationship('kodeRekening', 'uraian')
                            ->getOptionLabelFromRecordUsing(fn (KodeRekening $record) => $record->label)
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('nama_barang')
                            ->label('Nama Komponen Belanja (Produk)')
                            ->placeholder('Contoh: Bahan Bakar dan Pelumas, Kertas dan Cover, Natura, Alat Tulis Kantor')
                            ->helperText('Nama kelompok barang/produk yang tertera pada cetak rincian belanja RKA SIPD.')
                            ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? mb_strtoupper($state, 'UTF-8') : null)
                            ->required(),

                        Select::make('satuan_id')
                            ->label('Satuan Standar')
                            ->relationship('satuan', 'nama')
                            ->searchable()
                            ->preload()
                            ->required(),

                        FormatHelper::rupiahInput('harga_satuan', 'Harga Standar (SSH)')
                            ->default(0)
                            ->required(),
                    ]),
            ]);
    }
}

