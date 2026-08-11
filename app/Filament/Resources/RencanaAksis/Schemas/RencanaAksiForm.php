<?php

namespace App\Filament\Resources\RencanaAksis\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class RencanaAksiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('jabatan_id')
                    ->relationship('jabatan', 'nama'),
                Select::make('bidang_id')
                    ->relationship('bidang', 'nama_bidang'),
                TextInput::make('tahun')
                    ->required()
                    ->default('2026'),
                Textarea::make('sasaran_opd')
                    ->columnSpanFull(),
                Textarea::make('sasaran_program')
                    ->columnSpanFull(),
                Textarea::make('sasaran_kegiatan')
                    ->columnSpanFull(),
                Textarea::make('sasaran_sub_kegiatan')
                    ->columnSpanFull(),
                Textarea::make('sasaran_teknis')
                    ->columnSpanFull(),
                Textarea::make('indikator')
                    ->columnSpanFull(),
                Textarea::make('rencana_aksi')
                    ->columnSpanFull(),
                TextInput::make('target_jan')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('target_feb')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('target_mar')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('target_apr')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('target_mei')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('target_jun')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('target_jul')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('target_agu')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('target_sep')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('target_okt')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('target_nov')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('target_des')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}
