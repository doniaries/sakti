<?php

namespace App\Filament\Resources\Reputasis\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ReputasiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Penilaian Rekan Kerja')
                    ->schema([
                        \Filament\Forms\Components\Hidden::make('penilai_id')
                            ->default(fn() => auth()->id()),

                        Select::make('user_id')
                            ->label('Nama Pegawai (Rekan Kerja)')
                            ->relationship('user', 'name', fn($query) => $query->where('id', '!=', auth()->id())->orderBy('id'))
                            ->searchable()
                            ->required(),

                        DatePicker::make('tanggal')
                            ->label('Tanggal Penilaian')
                            ->displayFormat('d/m/Y')
                            ->default(now())
                            ->native(false)
                            ->required(),

                        \Filament\Forms\Components\Radio::make('nilai_reputasi')
                            ->label('Nilai Reputasi (Bintang)')
                            ->options([
                                1 => '⭐ (1)',
                                2 => '⭐⭐ (2)',
                                3 => '⭐⭐⭐ (3)',
                                4 => '⭐⭐⭐⭐ (4)',
                                5 => '⭐⭐⭐⭐⭐ (5)',
                            ])
                            ->inline()
                            ->required(),

                        Textarea::make('keterangan')
                            ->label('Catatan Penilaian')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
