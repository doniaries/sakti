<?php

declare(strict_types=1);

namespace App\Filament\Resources\StokBarangs\Schemas;

use App\Models\Bidang;
use App\Models\MasterBarang;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StokBarangForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Stok Persediaan')
                    ->description('Tentukan bidang pengelola dan jumlah fisik persediaan barang.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('bidang_id')
                                    ->label('Bidang Pengelola')
                                    ->relationship('bidang', 'nama')
                                    ->options(fn () => Bidang::pluck('nama', 'id')->all())
                                    ->default(fn () => request()->query('bidang') ?? Bidang::first()?->id)
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Select::make('master_barang_id')
                                    ->label('Nama Barang Persediaan')
                                    ->relationship('masterBarang', 'nama_barang')
                                    ->getOptionLabelFromRecordUsing(fn (MasterBarang $record): string => "{$record->nama_barang} ({$record->kode_barang})")
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                TextInput::make('stok_tersedia')
                                    ->label('Jumlah Stok Fisik Tersedia')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}

