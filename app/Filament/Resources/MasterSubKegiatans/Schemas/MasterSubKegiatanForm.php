<?php

namespace App\Filament\Resources\MasterSubKegiatans\Schemas;

use App\Models\MasterKegiatan;
use App\Models\MasterSubKegiatan;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MasterSubKegiatanForm
{
    /**
     * Reusable fields untuk Form Edit & Modal SlideOver (DRY)
     *
     * @return array<\Filament\Schemas\Components\Component|\Filament\Forms\Components\Component>
     */
    public static function getFields(bool $disabled = false): array
    {
        $currentYear = (int) date('Y');
        $years = [];
        for ($y = $currentYear + 1; $y >= $currentYear - 2; $y--) {
            $years[$y] = (string) $y;
        }

        return [
            Grid::make(['default' => 1, 'sm' => 2, 'lg' => 3])
                ->schema([
                    TextInput::make('kode_sub_kegiatan')
                        ->label('Kode Sub Kegiatan')
                        ->mask('9.99.99.9.99.9999')
                        ->disabled($disabled)
                        ->required(),

                    Select::make('tahun_anggaran')
                        ->label('Tahun Anggaran')
                        ->options($years)
                        ->default(fn () => (int) (request()->query('tahun') ?? $currentYear))
                        ->disabled($disabled)
                        ->required(),

                    Select::make('jf_id')
                        ->label('Penanggung Jawab (JF)')
                        ->placeholder('Pilih Pejabat Fungsional (Opsional)')
                        ->options(function (Get $get, ?MasterSubKegiatan $record) {
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
                        ->helperText('Hanya menampilkan Pejabat Fungsional (JF) yang berada di bidang kegiatan induk.')
                        ->disabled($disabled),
                ]),

            Select::make('kegiatan_id')
                ->label('Kegiatan Induk')
                ->relationship('kegiatan', 'nama_kegiatan')
                ->getOptionLabelFromRecordUsing(fn (MasterKegiatan $record) => "[{$record->kode_kegiatan}] {$record->nama_kegiatan}")
                ->searchable()
                ->preload()
                ->disabled($disabled)
                ->columnSpanFull()
                ->required(),

            Textarea::make('nama_sub_kegiatan')
                ->label('Nomenklatur Sub Kegiatan')
                ->disabled($disabled)
                ->extraInputAttributes(['style' => 'text-transform: uppercase; font-weight: 600;'])
                ->rows(2)
                ->columnSpanFull()
                ->required(),
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Sub Kegiatan')
                    ->description('Identitas Sub Kegiatan, tahun anggaran, dan penugasan JF (Read-Only).')
                    ->columnSpanFull()
                    ->schema(static::getFields(disabled: true)),
            ]);
    }
}

