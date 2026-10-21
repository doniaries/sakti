<?php

namespace App\Filament\Resources\JenisIzins\Schemas;

use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JenisIzinForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Tipe Izin & Cuti')
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Tipe Izin / Cuti')
                            ->placeholder('Contoh: Cuti Tahunan, Sakit, Izin Pribadi, WFH')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('kode')
                            ->label('Kode')
                            ->placeholder('Contoh: cuti_tahunan, sakit, wfh')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        Select::make('kategori')
                            ->label('Kategori Utama')
                            ->options([
                                'izin' => 'Izin',
                                'sakit' => 'Sakit',
                                'cuti' => 'Cuti',
                                'wfh' => 'Work From Home (WFH)',
                            ])
                            ->required(),

                        TextInput::make('kuota_hari')
                            ->label('Kuota Hari Per Tahun')
                            ->helperText('Kosongkan jika tidak ada batasan kuota hari.')
                            ->numeric()
                            ->suffix('hari'),

                        Grid::make(2)
                            ->schema([
                                Toggle::make('butuh_lampiran')
                                    ->label('Wajib Melampirkan Bukti / Surat')
                                    ->helperText('Contoh: Surat keterangan dokter untuk izin sakit.')
                                    ->default(false),

                                Toggle::make('aktif')
                                    ->label('Status Aktif')
                                    ->default(true),
                            ]),

                        Textarea::make('keterangan')
                            ->label('Keterangan Aturan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
