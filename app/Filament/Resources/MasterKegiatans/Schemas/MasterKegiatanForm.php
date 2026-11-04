<?php

namespace App\Filament\Resources\MasterKegiatans\Schemas;

use App\Models\Bidang;
use App\Models\MasterProgram;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class MasterKegiatanForm
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
                    ->default(fn() => (int) (request()->query('tahun') ?? $currentYear))
                    ->searchable()
                    ->live()
                    ->required(),

                Select::make('bidang_id')
                    ->label('Bidang Pengampu Kegiatan')
                    ->default(function (): ?int {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        if (! $user) {
                            return null;
                        }
                        if (filled($user->bidang_id)) {
                            return (int) $user->bidang_id;
                        }
                        $kepalaBidangId = Bidang::where('kepala_id', $user->id)->value('id');

                        return $kepalaBidangId ? (int) $kepalaBidangId : null;
                    })
                    ->options(function (): array {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();
                        if ($user !== null && $user->can('viewAny', Bidang::class)) {
                            return Bidang::pluck('nama', 'id')->all();
                        }

                        $userBidangId = $user && filled($user->bidang_id)
                            ? (int) $user->bidang_id
                            : (int) (Bidang::where('kepala_id', $user?->id)->value('id') ?? 0);

                        return $userBidangId > 0
                            ? Bidang::whereKey($userBidangId)->pluck('nama', 'id')->all()
                            : [];
                    })
                    ->disabled(function (): bool {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        return $user !== null && ! $user->can('viewAny', Bidang::class);
                    })
                    ->dehydrated()
                    ->searchable()
                    ->required(),

                Select::make('program_id')
                    ->label('Program')
                    ->relationship('program', 'nama_program')
                    ->getOptionLabelFromRecordUsing(fn(MasterProgram $record) => $record->label)
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        if ($state) {
                            $program = MasterProgram::find($state);
                            if ($program) {
                                $currentCode = (string) $get('kode_kegiatan');
                                if (blank($currentCode) || ! str_starts_with($currentCode, $program->kode_program)) {
                                    $set('kode_kegiatan', $program->kode_program . '.');
                                }
                            }
                        }
                    })
                    ->columnSpanFull()
                    ->required(),

                TextInput::make('kode_kegiatan')
                    ->label('Kode Kegiatan')
                    ->placeholder('Contoh: 3.26.04.2.02')
                    ->mask('9.99.99.9.99')
                    ->maxLength(50)
                    ->helperText('Format titik otomatis sesuai standar SIPD (contoh: 3.26.04.2.02)')
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('nama_kegiatan')
                    ->label('Nomenklatur Kegiatan')
                    ->placeholder('Contoh: Pengembangan Ekosistem Ekonomi Kreatif')
                    ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? mb_strtoupper($state, 'UTF-8') : null)
                    ->required(),

                TextInput::make('pagu_anggaran')
                    ->label('Jumlah Uang / Pagu Kegiatan (Rp)')
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp')
                    ->required(),
            ]);
    }
}
