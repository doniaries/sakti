<?php

namespace App\Filament\Resources\MasterSubKegiatans\Schemas;

use App\Models\MasterKegiatan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class MasterSubKegiatanForm
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

                Select::make('kegiatan_id')
                    ->label('Kegiatan Induk')
                    ->relationship('kegiatan', 'nama_kegiatan')
                    ->getOptionLabelFromRecordUsing(fn (MasterKegiatan $record) => $record->label)
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        if ($state) {
                            $kegiatan = MasterKegiatan::find($state);
                            if ($kegiatan) {
                                $currentCode = (string) $get('kode_sub_kegiatan');
                                if (blank($currentCode) || ! str_starts_with($currentCode, $kegiatan->kode_kegiatan)) {
                                    $set('kode_sub_kegiatan', $kegiatan->kode_kegiatan);
                                }
                            }
                        }
                    })
                    ->required(),

                TextInput::make('kode_sub_kegiatan')
                    ->label('Kode Sub Kegiatan')
                    ->placeholder('Contoh: 3.26.04.2.02.0014')
                    ->mask('9.99.99.9.99.9999')
                    ->maxLength(50)
                    ->helperText('Format titik otomatis sesuai standar SIPD (contoh: 3.26.04.2.02.0014)')
                    ->required(),

                TextInput::make('nama_sub_kegiatan')
                    ->label('Nomenklatur Sub Kegiatan')
                    ->placeholder('Contoh: Fasilitasi penguatan Kekayaan Intelektual')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
