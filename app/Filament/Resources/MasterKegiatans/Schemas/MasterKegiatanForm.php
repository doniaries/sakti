<?php

namespace App\Filament\Resources\MasterKegiatans\Schemas;

use App\Models\Bidang;
use App\Models\MasterProgram;
use App\Support\FormatHelper;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class MasterKegiatanForm
{
    public static function configure(Schema $schema): Schema
    {
        $currentYear = (int) date('Y');
        $maxYear = $currentYear + 1; // Batasi maksimal ke 2027 jika tahun sekarang 2026
        $minYear = $currentYear - 2;

        $years = [];
        for ($y = $maxYear; $y >= $minYear; $y--) {
            $years[$y] = (string) $y;
        }

        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Kegiatan DPA')
                    ->icon('heroicon-o-briefcase')
                    ->schema([
                        // Baris 1: Tahun Anggaran & Bidang Pengampu
                        Grid::make([
                            'default' => 1,
                            'md' => 3,
                        ])->schema([
                            Select::make('tahun_anggaran')
                                ->label('Tahun Anggaran')
                                ->options($years)
                                ->default(fn() => (int) (request()->query('tahun') ?? $currentYear))
                                ->searchable()
                                ->live()
                                ->required()
                                ->columnSpan(['default' => 1, 'md' => 1]),

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
                                    $canViewAll = $user !== null && $user->canViewAllBidangKeuangan();

                                    if ($canViewAll) {
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
                                    return ! ($user !== null && $user->canViewAllBidangKeuangan());
                                })
                                ->dehydrated()
                                ->searchable()
                                ->preload()
                                ->required()
                                ->columnSpan(['default' => 1, 'md' => 2]),
                        ]),

                        // Baris 2: Program Induk
                        Select::make('program_id')
                            ->label('Program Induk')
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
                            ->required(),

                        // Baris 3: Kode Kegiatan & Nomenklatur Kegiatan
                        Grid::make([
                            'default' => 1,
                            'md' => 3,
                        ])->schema([
                            TextInput::make('kode_kegiatan')
                                ->label('Kode Kegiatan')
                                ->placeholder('Contoh: 3.26.04.2.02')
                                ->mask('9.99.99.9.99')
                                ->maxLength(50)
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->columnSpan(['default' => 1, 'md' => 1]),

                            TextInput::make('nama_kegiatan')
                                ->label('Nomenklatur Kegiatan')
                                ->placeholder('Contoh: PENGEMBANGAN EKOSISTEM EKONOMI KREATIF')
                                ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                                ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? mb_strtoupper($state, 'UTF-8') : null)
                                ->required()
                                ->columnSpan(['default' => 1, 'md' => 2]),
                        ]),

                        // Baris 4: Pagu Kegiatan
                        FormatHelper::rupiahInput('pagu_anggaran', 'Jumlah Uang / Pagu Kegiatan')
                            ->required(),
                    ]),
            ]);
    }
}
