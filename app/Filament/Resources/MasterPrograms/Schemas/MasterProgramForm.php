<?php

namespace App\Filament\Resources\MasterPrograms\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MasterProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        $currentYear = \App\Models\PengaturanAplikasi::getTahunAnggaranAktif();
        $maxYear = $currentYear + 1; // Batasi maksimal ke 2027 jika tahun sekarang 2026
        $minYear = $currentYear - 2;

        $years = [];
        for ($y = $maxYear; $y >= $minYear; $y--) {
            $years[$y] = (string) $y;
        }

        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Program DPA')
                    ->icon('heroicon-o-folder')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 3,
                        ])->schema([
                            Select::make('tahun_anggaran')
                                ->label('Tahun Anggaran')
                                ->options($years)
                                ->default(fn () => (int) (request()->query('tahun') ?? $currentYear))
                                ->searchable()
                                ->required()
                                ->columnSpan(['default' => 1, 'md' => 1]),

                            TextInput::make('kode_program')
                                ->label('Kode Program')
                                ->placeholder('Contoh: 3.26.04')
                                ->mask('9.99.99')
                                ->maxLength(50)
                                ->required()
                                ->columnSpan(['default' => 1, 'md' => 2]),
                        ]),

                        TextInput::make('nama_program')
                            ->label('Nama / Nomenklatur Program')
                            ->placeholder('Contoh: PROGRAM PEMASARAN PARIWISATA')
                            ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? mb_strtoupper($state, 'UTF-8') : null)
                            ->required(),

                        Textarea::make('keterangan')
                            ->label('Keterangan Tambahan')
                            ->rows(2)
                            ->nullable(),
                    ]),
            ]);
    }
}
