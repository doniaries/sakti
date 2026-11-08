<?php

namespace App\Filament\Resources\KodeRekenings\Schemas;

use App\Models\KodeRekening;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class KodeRekeningForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Rekening Belanja')
                    ->description('Kelola nomor kode rekening dan uraian belanja sesuai standar Kepmendagri dan SIPD RI.')
                    ->columns(['default' => 1, 'md' => 2])
                    ->schema([
                        Select::make('parent_kode')
                            ->label('Rekening Induk (Opsional)')
                            ->helperText('Pilih kategori/rekening induk untuk mengisi otomatis awalan kode rekening baru.')
                            ->searchable()
                            ->options(function () {
                                return KodeRekening::query()
                                    ->whereRaw("(LENGTH(kode_lengkap) - LENGTH(REPLACE(kode_lengkap, '.', ''))) < 5")
                                    ->orderBy('kode_lengkap')
                                    ->limit(100)
                                    ->get()
                                    ->mapWithKeys(fn (KodeRekening $item) => [
                                        $item->kode_lengkap => "{$item->kode_lengkap} - {$item->uraian}",
                                    ])
                                    ->all();
                            })
                            ->getSearchResultsUsing(function (string $search): array {
                                $tokens = explode(' ', trim($search));
                                return KodeRekening::query()
                                    ->where(function ($q) use ($tokens, $search) {
                                        $q->where('kode_lengkap', 'like', "%{$search}%");
                                        $q->orWhere(function ($sub) use ($tokens) {
                                            foreach ($tokens as $token) {
                                                if (filled($token)) {
                                                    $sub->where('uraian', 'like', "%{$token}%");
                                                }
                                            }
                                        });
                                    })
                                    ->whereRaw("(LENGTH(kode_lengkap) - LENGTH(REPLACE(kode_lengkap, '.', ''))) < 5")
                                    ->orderBy('kode_lengkap')
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn (KodeRekening $item) => [
                                        $item->kode_lengkap => "{$item->kode_lengkap} - {$item->uraian}",
                                    ])
                                    ->all();
                            })
                            ->getOptionLabelUsing(function (?string $value): ?string {
                                if (! $value) {
                                    return null;
                                }
                                $item = KodeRekening::where('kode_lengkap', $value)->first();

                                return $item ? "{$item->kode_lengkap} - {$item->uraian}" : $value;
                            })
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                if ($state) {
                                    $currentKode = (string) $get('kode_lengkap');
                                    if (blank($currentKode) || ! str_starts_with($currentKode, $state)) {
                                        $set('kode_lengkap', $state . '.');
                                    }
                                }
                            })
                            ->dehydrated(false),

                        TextInput::make('kode_lengkap')
                            ->label('Kode Rekening Belanja')
                            ->placeholder('Contoh: 5.1.02.01.001.00024')
                            ->helperText('Nomor kode berjenjang menggunakan pemisah titik (.) sesuai DPA/RKA.')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->extraInputAttributes(['class' => 'font-mono']),

                        TextInput::make('uraian')
                            ->label('Uraian Rekening Belanja')
                            ->placeholder('Contoh: BELANJA BAHAN-BAHAN BAKAR DAN PELUMAS')
                            ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? mb_strtoupper($state, 'UTF-8') : null)
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
