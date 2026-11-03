<?php

namespace App\Filament\Resources\KodeRekenings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class KodeRekeningForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('kode_lengkap')
                    ->label('Kode Rekening Lengkap')
                    ->placeholder('Contoh: 1.1.7.01.01.01.000')
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('uraian')
                    ->label('Uraian Rekening Belanja')
                    ->placeholder('Contoh: ALAT TULIS KANTOR')
                    ->required(),

                TextInput::make('akun')->label('Akun')->default('1'),
                TextInput::make('kelompok')->label('Kelompok')->default('1'),
                TextInput::make('jenis')->label('Jenis')->default('7'),
                TextInput::make('objek')->label('Objek')->default('01'),
                TextInput::make('rincian_objek')->label('Rincian Objek')->default('01'),
                TextInput::make('sub_rincian_objek')->label('Sub Rincian')->default('01'),
                TextInput::make('sub_sub_rincian_objek')->label('Sub-Sub Rincian')->default('000'),
            ]);
    }
}

