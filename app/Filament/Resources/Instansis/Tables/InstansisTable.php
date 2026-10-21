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
                        Split::make([
                            ImageColumn::make('logo')
                                ->defaultImageUrl(url('/images/logo.png'))
                                ->circular()
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

                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                            'lg' => 4,
                        ])
                        ->schema([
                            TextColumn::make('kepala_dinas')
                                ->label('Kepala Dinas (Kadis)')
                                ->state(fn ($record) => $record->kepala?->name ?? $record->nama_kepala_dinas ?? '-')
                                ->icon('heroicon-m-user-circle')
                                ->badge()
                                ->color('primary'),

                            TextColumn::make('sekretaris')
                                ->label('Sekretaris Dinas')
                                ->state(function () {
                                    $sekretariat = Bidang::where('slug', 'like', '%sekretariat%')->with('kepala')->first();
                                    return $sekretariat?->kepala?->name ?? 'Belum ditentukan';
                                })
                                ->icon('heroicon-m-user')
                                ->badge()
                                ->color('success'),

                            TextColumn::make('total_pegawai')
                                ->label('Jumlah Pegawai')
                                ->state(fn () => User::role('pegawai')->count() . ' Orang Pegawai')
                                ->icon('heroicon-m-user-group')
                                ->badge()
                                ->color('info'),

                            TextColumn::make('total_bidang')
                                ->label('Jumlah Bidang')
                                ->state(fn () => Bidang::count() . ' Bidang Kerja')
                                ->icon('heroicon-m-rectangle-group')
                                ->badge()
                                ->color('warning'),
                        ]),

                        Panel::make([
                            Stack::make([
                                TextColumn::make('daftar_kabid_header')
                                    ->state('Kepala Bidang (Kabid):')
                                    ->weight('bold')
                                    ->size(TextSize::Small)
                                    ->color('gray'),

                                Grid::make([
                                    'default' => 1,
                                    'md' => 3,
                                ])
                                ->schema(function () {
                                    $bidangs = Bidang::where('slug', 'not like', '%sekretariat%')
                                        ->with('kepala')
                                        ->get();

                                    return $bidangs->map(function ($bidang) {
                                        return TextColumn::make('kabid_' . $bidang->id)
                                            ->label($bidang->nama)
                                            ->state($bidang->nama . ': ' . ($bidang->kepala?->name ?? 'Belum ditentukan'))
                                            ->icon('heroicon-m-briefcase')
                                            ->color($bidang->kepala ? 'info' : 'gray');
                                    })->toArray();
                                }),
                            ]),
                        ]),
                    ])->space(3),
                ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->button()
                    ->label('Edit Profil Instansi')
                    ->icon('heroicon-m-pencil-square'),
            ]);
    }
}

