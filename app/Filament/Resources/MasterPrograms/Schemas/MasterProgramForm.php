<?php

namespace App\Filament\Resources\MasterPrograms\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MasterProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        $currentYear = (int) date('Y');
        $years = [];
        for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++) {
            $years[$y] = "Tahun Anggaran {$y}";
        }

        return $schema
            ->components([
                Select::make('tahun_anggaran')
                    ->label('Tahun Anggaran')
                    ->options($years)
                    ->default(fn () => (int) (request()->query('tahun') ?? $currentYear))
                    ->searchable()
                    ->required(),

                TextInput::make('kode_program')
                    ->label('Kode Program')
                    ->placeholder('Contoh: 3.26.04')
                    ->mask('9.99.99')
                    ->maxLength(50)
                    ->helperText('Format titik otomatis sesuai standar SIPD (contoh: 3.26.04)')
                    ->required(),

                TextInput::make('nama_program')
                    ->label('Nama / Nomenklatur Program')
                    ->placeholder('Contoh: PROGRAM PENGEMBANGAN EKONOMI KREATIF...')
                    ->required()
                    ->columnSpanFull(),

                Textarea::make('keterangan')
                    ->label('Keterangan Tambahan')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }
}

