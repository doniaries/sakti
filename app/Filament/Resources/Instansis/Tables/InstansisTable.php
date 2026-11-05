<?php

namespace App\Filament\Resources\Instansis\Tables;

use App\Models\Bidang;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InstansisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 1,
                'xl' => 1,
            ])
            ->columns([
                Panel::make([
                    Stack::make([
                        // Header Identitas Instansi
                        Split::make([
                            ImageColumn::make('logo')
                                ->defaultImageUrl(asset('images/logo-instansi.png'))
                                ->grow(false),

                            Stack::make([
                                TextColumn::make('nama_dinas')
                                    ->size(TextSize::Large)
                                    ->weight('bold')
                                    ->color('primary')
                                    ->icon('heroicon-m-building-office-2'),

                                TextColumn::make('alamat')
                                    ->icon('heroicon-m-map-pin')
                                    ->color('gray'),
                            ])->space(1),
                        ]),

                        // Bagian Pejabat Utama & Jumlah Pegawai
                        Grid::make([
                            'default' => 1,
                            'md' => 3,
                        ])
                        ->schema([
                            Stack::make([
                                TextColumn::make('label_kadis')
                                    ->state('Kepala Dinas (Kadis)')
                                    ->size(TextSize::Small)
                                    ->color('gray'),
                                TextColumn::make('kepala_dinas')
                                    ->state(fn ($record) => $record->kepala?->name ?? $record->nama_kepala_dinas ?? '-')
                                    ->weight('bold')
                                    ->icon('heroicon-m-user-circle')
                                    ->color('primary'),
                            ])->space(1),

                            Stack::make([
                                TextColumn::make('label_sekretaris')
                                    ->state('Sekretaris Dinas')
                                    ->size(TextSize::Small)
                                    ->color('gray'),
                                TextColumn::make('sekretaris')
                                    ->state(function () {
                                        $sekretariat = Bidang::where('slug', 'like', '%sekretariat%')->with('kepala')->first();
                                        return $sekretariat?->kepala?->name ?? 'Belum ditentukan';
                                    })
                                    ->weight('bold')
                                    ->icon('heroicon-m-user')
                                    ->color('success'),
                            ])->space(1),

                            Stack::make([
                                TextColumn::make('label_total_pegawai')
                                    ->state('Jumlah Pegawai')
                                    ->size(TextSize::Small)
                                    ->color('gray'),
                                TextColumn::make('total_pegawai')
                                    ->state(fn () => User::role('pegawai')->count() . ' Orang Pegawai')
                                    ->weight('bold')
                                    ->icon('heroicon-m-user-group')
                                    ->color('info'),
                            ])->space(1),
                        ]),

                        // Bagian Kepala Bidang
                        Panel::make([
                            Stack::make([
                                TextColumn::make('daftar_kabid_header')
                                    ->state('Kepala Bidang (Kabid):')
                                    ->weight('bold')
                                    ->size(TextSize::Small)
                                    ->color('primary'),

                                Grid::make([
                                    'default' => 1,
                                    'md' => 3,
                                ])
                                ->schema(function () {
                                    $bidangs = Bidang::where('slug', 'not like', '%sekretariat%')
                                        ->with('kepala')
                                        ->get();

                                    return $bidangs->map(function ($bidang) {
                                        return Stack::make([
                                            TextColumn::make('bidang_label_' . $bidang->id)
                                                ->state($bidang->nama)
                                                ->size(TextSize::Small)
                                                ->color('gray'),
                                            TextColumn::make('kabid_' . $bidang->id)
                                                ->state($bidang->kepala?->name ?? 'Belum ditentukan')
                                                ->weight('medium')
                                                ->icon('heroicon-m-briefcase')
                                                ->color($bidang->kepala ? 'white' : 'gray'),
                                        ])->space(1);
                                    })->toArray();
                                }),
                            ])->space(2),
                        ]),
                    ])->space(4),
                ]),
            ])
            ->paginated(false)
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make()
                    ->button()
                    ->label('Edit Profil Instansi')
                    ->icon('heroicon-m-pencil-square'),
            ]);
    }
}

