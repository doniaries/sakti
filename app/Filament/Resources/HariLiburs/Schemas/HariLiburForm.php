<?php

namespace App\Filament\Resources\HariLiburs\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HariLiburForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Hari Libur')
                    ->description('Masukkan tanggal dan nama hari libur resmi nasional, cuti bersama, atau edaran daerah.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('tanggal')
                                    ->label('Tanggal Libur')
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->unique(ignoreRecord: true)
                                    ->required(),

                                TextInput::make('nama')
                                    ->label('Nama Hari Libur')
                                    ->placeholder('Contoh: Hari Kemerdekaan RI / Hari Jadi Kabupaten')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('jenis')
                                    ->label('Jenis Libur')
                                    ->options([
                                        'nasional' => 'Libur Nasional',
                                        'cuti_bersama' => 'Cuti Bersama',
                                        'khusus_daerah' => 'Khusus Daerah / Surat Edaran',
                                    ])
                                    ->default('nasional')
                                    ->required(),

                                Select::make('sumber')
                                    ->label('Sumber Data')
                                    ->options([
                                        'api' => 'Sinkronisasi API',
                                        'manual' => 'Input Manual Edaran',
                                    ])
                                    ->default('manual')
                                    ->required(),
                            ]),

                        Textarea::make('keterangan')
                            ->label('Dasar Hukum / Keterangan')
                            ->placeholder('Contoh: Surat Edaran Bupati No. 800/123/BKPSDM-2026')
                            ->rows(2)
                            ->columnSpanFull(),

                        Toggle::make('is_aktif')
                            ->label('Status Aktif')
                            ->helperText('Hari libur aktif tidak akan mencatat pegawai sebagai alpa/tidak hadir.')
                            ->default(true),
                    ]),
            ]);
    }
}
