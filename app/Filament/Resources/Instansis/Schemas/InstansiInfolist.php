<?php

namespace App\Filament\Resources\Instansis\Schemas;

use App\Models\Bidang;
use App\Models\User;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;

class InstansiInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profil Instansi')
                    ->description('Identitas resmi dinas dan pimpinan instansi')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                ImageEntry::make('logo')
                                    ->label('Logo Instansi')
                                    ->defaultImageUrl(url('/images/logo.png'))
                                    ->circular()
                                    ->columnSpan(1),

                                Group::make([
                                    TextEntry::make('nama_dinas')
                                        ->label('Nama Instansi / Dinas')
                                        ->size(TextSize::Large)
                                        ->weight('bold')
                                        ->icon('heroicon-m-building-office-2'),

                                    TextEntry::make('alamat')
                                        ->label('Alamat Kantor')
                                        ->icon('heroicon-m-map-pin'),
                                ])
                                ->columnSpan(2),
                            ]),
                    ]),

                Section::make('Struktur Kepemimpinan & Kepegawaian')
                    ->description('Daftar pejabat pimpinan dinas dan ringkasan kepegawaian')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('kepala_dinas')
                                    ->label('Kepala Dinas (Kadis)')
                                    ->state(fn ($record) => $record->kepala?->name ?? $record->nama_kepala_dinas ?? '-')
                                    ->icon('heroicon-m-user-circle')
                                    ->color('primary')
                                    ->weight('bold'),

                                TextEntry::make('sekretaris')
                                    ->label('Sekretaris Dinas')
                                    ->state(function () {
                                        $sekretariat = Bidang::where('slug', 'like', '%sekretariat%')->with('kepala')->first();
                                        return $sekretariat?->kepala?->name ?? 'Belum ditentukan';
                                    })
                                    ->icon('heroicon-m-user')
                                    ->color('success')
                                    ->weight('bold'),
                            ]),

                        Section::make('Para Kepala Bidang (Kabid)')
                            ->compact()
                            ->schema([
                                Grid::make(3)
                                    ->schema(function () {
                                        $bidangs = Bidang::where('slug', 'not like', '%sekretariat%')
                                            ->with('kepala')
                                            ->get();

                                        return $bidangs->map(function ($bidang) {
                                            return TextEntry::make('kabid_' . $bidang->id)
                                                ->label($bidang->nama)
                                                ->state($bidang->kepala?->name ?? 'Belum ditentukan')
                                                ->icon('heroicon-m-briefcase')
                                                ->badge()
                                                ->color($bidang->kepala ? 'info' : 'gray');
                                        })->toArray();
                                    }),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextEntry::make('total_pegawai')
                                    ->label('Jumlah Pegawai')
                                    ->state(fn () => User::role('pegawai')->count() . ' Orang Pegawai')
                                    ->icon('heroicon-m-user-group')
                                    ->badge()
                                    ->color('success'),

                                TextEntry::make('total_bidang')
                                    ->label('Jumlah Bidang Kerja')
                                    ->state(fn () => Bidang::count() . ' Bidang')
                                    ->icon('heroicon-m-rectangle-group')
                                    ->badge()
                                    ->color('primary'),
                            ]),
                    ]),
            ]);
    }
}
