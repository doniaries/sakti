<?php

namespace App\Filament\Resources\MasterBarangs\Schemas;

use App\Models\KodeRekening;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Support\RawJs;

class MasterBarangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('kode_rekening_id')
                    ->label('Kode Rekening Belanja')
                    ->relationship('kodeRekening', 'uraian')
                    ->getOptionLabelFromRecordUsing(fn (KodeRekening $record) => $record->label)
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('kode_barang')
                    ->label('Kode Barang Persediaan')
                    ->placeholder('Contoh: 1.1.7.01.01.01.001')
                    ->mask(RawJs::make(<<<'JS'
                        $input.startsWith('5') ? '9.9.99.99.999.99999.999' : '9.9.9.99.99.99.999'
                    JS))
                    ->helperText('Format titik otomatis sesuai standar persediaan (contoh: 1.1.7.01.01.01.001)')
                    ->required(),

                TextInput::make('nama_barang')
                    ->label('Nama Barang Persediaan')
                    ->placeholder('Contoh: Kertas HVS A4 80 gram')
                    ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? mb_strtoupper($state, 'UTF-8') : null)
                    ->required()
                    ->columnSpanFull(),

                Select::make('satuan_id')
                    ->label('Satuan Unit')
                    ->relationship('satuan', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('harga_satuan')
                    ->label('Harga Referensi (Opsional)')
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0)
                    ->helperText('Opsional. Harga pagu resmi diinput pada menu Rincian Belanja (RKA) sesuai DPA SIPD.'),

                Textarea::make('keterangan')
                    ->label('Keterangan / Spesifikasi')
                    ->columnSpanFull(),
            ]);
    }
}

