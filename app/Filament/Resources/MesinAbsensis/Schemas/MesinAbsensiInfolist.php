<?php

namespace App\Filament\Resources\MesinAbsensis\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MesinAbsensiInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nama'),
                TextEntry::make('merek'),
                TextEntry::make('model'),
                TextEntry::make('alamat_ip'),
                TextEntry::make('port')
                    ->numeric(),
                TextEntry::make('nomor_seri'),
                TextEntry::make('jenis_koneksi'),
                IconEntry::make('aktif')
                    ->boolean(),
                TextEntry::make('sinkronisasi_terakhir')
                    ->dateTime('d/m/Y H:i'),
                TextEntry::make('created_at')
                    ->dateTime('d/m/Y H:i'),
                TextEntry::make('updated_at')
                    ->dateTime('d/m/Y H:i'),
            ]);
    }
}
