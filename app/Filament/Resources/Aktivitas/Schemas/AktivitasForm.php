<?php

namespace App\Filament\Resources\Aktivitas\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class AktivitasForm
{
    public static function configure(Schema $schema): Schema
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser && $currentUser->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian']);

        return $schema
            ->components([
                Section::make('Informasi Pegawai & Unit Kerja')
                    ->columns(3)
                    ->schema([
                        Select::make('user_id')
                            ->label('Pegawai (Pemilik Aktivitas)')
                            ->relationship('user', 'name')
                            ->default($isAdmin ? null : $currentUser?->id)
                            ->disabled(! $isAdmin)
                            ->dehydrated()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set): void {
                                if ($state && ($pegawai = User::find((int) $state))) {
                                    $set('jabatan_id', $pegawai->jabatan_id);
                                    $set('bidang_id', $pegawai->bidang_id);
                                }
                            })
                            ->helperText('Setiap pegawai dapat memiliki daftar aktivitas yang berbeda. Kosongkan jika berlaku umum.'),

                        Select::make('jabatan_id')
                            ->label('Jabatan')
                            ->relationship('jabatan', 'nama')
                            ->default($isAdmin ? null : $currentUser?->jabatan_id)
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Select::make('bidang_id')
                            ->label('Bidang')
                            ->relationship('bidang', 'nama')
                            ->default($isAdmin ? null : $currentUser?->bidang_id)
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])
                    ->columnSpanFull(),

                Section::make('Detail Aktivitas LHK')
                    ->columns(3)
                    ->schema([
                        TextInput::make('kode')
                            ->label('Kode Aktivitas')
                            ->placeholder('Contoh: 1 atau AKT-01')
                            ->maxLength(50)
                            ->columnSpan(1),

                        TextInput::make('menit')
                            ->label('Standar Durasi')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(30)
                            ->suffix('menit')
                            ->columnSpan(2),

                        Textarea::make('nama_aktivitas')
                            ->label('Nama / Uraian Aktivitas')
                            ->required()
                            ->rows(3)
                            ->placeholder('Tuliskan uraian aktivitas kinerja harian pegawai...')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
