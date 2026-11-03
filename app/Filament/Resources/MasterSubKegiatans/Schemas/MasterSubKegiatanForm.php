<?php

namespace App\Filament\Resources\MasterSubKegiatans\Schemas;

use App\Models\MasterKegiatan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MasterSubKegiatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('kegiatan_id')
                    ->label('Kegiatan Induk')
                    ->relationship('kegiatan', 'nama_kegiatan')
                    ->getOptionLabelFromRecordUsing(fn (MasterKegiatan $record) => $record->label)
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('kode_sub_kegiatan')
                    ->label('Kode Sub Kegiatan')
                    ->placeholder('Contoh: 1.01.02.1.01.0003')
                    ->required(),

                TextInput::make('nama_sub_kegiatan')
                    ->label('Nomenklatur Sub Kegiatan')
                    ->placeholder('Contoh: Penyediaan Bahan Logistik Kantor dan ATK')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}

