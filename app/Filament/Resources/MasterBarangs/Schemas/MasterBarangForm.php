<?php

namespace App\Filament\Resources\MasterBarangs\Schemas;

use App\Models\KodeRekening;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

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
                    ->label('Kode Barang')
                    ->placeholder('Contoh: 1.1.7.01.01.01.001')
                    ->mask('9.9.9.99.99.99.999')
                    ->length(18)
                    ->rules(['regex:/^\d\.\d\.\d\.\d{2}\.\d{2}\.\d{2}\.\d{3}$/'])
                    ->validationMessages([
                        'regex' => 'Format kode barang wajib X.X.X.XX.XX.XX.XXX (contoh: 1.1.7.01.01.01.001).',
                        'length' => 'Kode barang harus tepat 18 karakter (termasuk titik).',
                    ])
                    ->helperText('Format: X.X.X.XX.XX.XX.XXX (tepat 11 digit angka dengan 6 titik, contoh: 1.1.7.01.01.01.001)')
                    ->required(),

                TextInput::make('nama_barang')
                    ->label('Nama Barang Persediaan')
                    ->placeholder('Contoh: Kertas HVS A4 80 gram')
                    ->required()
                    ->columnSpanFull(),

                Select::make('satuan_id')
                    ->label('Satuan Unit')
                    ->relationship('satuan', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('harga_satuan')
                    ->label('Harga Satuan Standar (Rp)')
                    ->numeric()
                    ->prefix('Rp')
                    ->required(),

                Textarea::make('keterangan')
                    ->label('Keterangan / Spesifikasi')
                    ->columnSpanFull(),
            ]);
    }
}

