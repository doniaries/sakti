<?php

namespace App\Filament\Resources\MasterKegiatans\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MasterKegiatanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_kegiatan')
                    ->label('Kode Kegiatan')
                    ->placeholder('Contoh: 1.01.02.1.01')
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('nama_kegiatan')
                    ->label('Nomenklatur Kegiatan')
                    ->placeholder('Contoh: Program Pembinaan dan Pengembangan Olahraga')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
