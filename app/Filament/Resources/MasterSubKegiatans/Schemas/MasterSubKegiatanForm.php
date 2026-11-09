<?php

namespace App\Filament\Resources\MasterSubKegiatans\Schemas;

use App\Models\MasterKegiatan;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class MasterSubKegiatanForm
{
    public static function configure(Schema $schema): Schema
    {
        $currentYear = (int) date('Y');
        $maxYear = $currentYear + 1;
        $minYear = $currentYear - 2;

        $years = [];
        for ($y = $maxYear; $y >= $minYear; $y--) {
            $years[$y] = (string) $y;
        }

        return $schema
            ->components([
                Section::make('Informasi Sub Kegiatan')
                    ->compact()
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2, 'lg' => 3])
                            ->schema([
                                TextInput::make('kode_sub_kegiatan')
                                    ->label('Kode Sub Kegiatan')
                                    ->mask('9.99.99.9.99.9999')
                                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                                    ->required(),

                                Select::make('tahun_anggaran')
                                    ->label('Tahun Anggaran')
                                    ->options($years)
                                    ->default(fn () => (int) (request()->query('tahun') ?? $currentYear))
                                    ->disabled(fn (string $operation): bool => $operation === 'edit')
                                    ->required(),

                                Select::make('jf_id')
                                    ->label('Penanggung Jawab (JF)')
                                    ->placeholder('Belum Ditugaskan')
                                    ->options(function (Get $get, ?\App\Models\MasterSubKegiatan $record) {
                                        $kegiatanId = $get('kegiatan_id') ?? $record?->kegiatan_id;
                                        $bidangId = $kegiatanId ? MasterKegiatan::find($kegiatanId)?->bidang_id : null;

                                        return User::query()
                                            ->where('aktif', true)
                                            ->when($bidangId, fn ($q) => $q->where('bidang_id', $bidangId))
                                            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['pa', 'ppk']))
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->all();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn (string $operation): bool => $operation === 'edit'),
                            ]),

                        Select::make('kegiatan_id')
                            ->label('Kegiatan Induk')
                            ->relationship('kegiatan', 'nama_kegiatan')
                            ->getOptionLabelFromRecordUsing(fn (MasterKegiatan $record) => "[{$record->kode_kegiatan}] {$record->nama_kegiatan}")
                            ->searchable()
                            ->preload()
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->columnSpanFull()
                            ->required(),

                        TextInput::make('nama_sub_kegiatan')
                            ->label('Nomenklatur Sub Kegiatan')
                            ->disabled(fn (string $operation): bool => $operation === 'edit')
                            ->extraInputAttributes(['style' => 'text-transform: uppercase; font-weight: 600;'])
                            ->columnSpanFull()
                            ->required(),
                    ]),
            ]);
    }
}

