<?php

namespace App\Filament\Resources\Aktivitas\Schemas;

use App\Models\Aktivitas;
use App\Models\User;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class AktivitasForm
{
    public static function configure(Schema $schema): Schema
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        $isAdmin = $currentUser && $currentUser->hasAnyRole(['super_admin', 'superadmin', 'admin', 'admin_kepegawaian']);

        return $schema
            ->components([
                Section::make('Cakupan & Pemilik Aktivitas')
                    ->description('Pilih Global jika aktivitas dapat digunakan oleh seluruh pegawai, atau Spesifik untuk pegawai/bidang tertentu.')
                    ->columns(3)
                    ->schema([
                        Radio::make('kategori_form')
                            ->label('Kategori Aktivitas')
                            ->options([
                                'global' => 'Global (Semua Pegawai)',
                                'spesifik' => 'Spesifik (Per Pegawai / Jabatan / Bidang)',
                            ])
                            ->default($isAdmin ? 'global' : 'spesifik')
                            ->inline()
                            ->live()
                            ->dehydrated(false)
                            ->visible($isAdmin)
                            ->afterStateHydrated(function (Radio $component, ?Aktivitas $record) use ($isAdmin): void {
                                if (! $record) {
                                    $component->state($isAdmin ? 'global' : 'spesifik');
                                    return;
                                }
                                $isGlobal = is_null($record->user_id) && is_null($record->jabatan_id);
                                $component->state($isGlobal ? 'global' : 'spesifik');
                            })
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                if ($state === 'global') {
                                    $set('user_id', null);
                                    $set('jabatan_id', null);
                                    $set('bidang_id', null);
                                }
                            })
                            ->columnSpanFull(),

                        Select::make('user_id')
                            ->label('Pegawai (Pemilik Aktivitas)')
                            ->relationship('user', 'name')
                            ->default($isAdmin ? null : $currentUser?->id)
                            ->disabled(! $isAdmin)
                            ->dehydrated()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->visible(fn (Get $get): bool => ! $isAdmin || $get('kategori_form') === 'spesifik')
                            ->afterStateUpdated(function ($state, Set $set): void {
                                if ($state && ($pegawai = User::find((int) $state))) {
                                    $set('jabatan_id', $pegawai->jabatan_id);
                                    $set('bidang_id', $pegawai->bidang_id);
                                }
                            }),

                        Select::make('jabatan_id')
                            ->label('Jabatan')
                            ->relationship('jabatan', 'nama')
                            ->default($isAdmin ? null : $currentUser?->jabatan_id)
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->visible(fn (Get $get): bool => ! $isAdmin || $get('kategori_form') === 'spesifik'),

                        Select::make('bidang_id')
                            ->label('Bidang')
                            ->relationship('bidang', 'nama')
                            ->default($isAdmin ? null : $currentUser?->bidang_id)
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->visible(fn (Get $get): bool => ! $isAdmin || $get('kategori_form') === 'spesifik'),

                        Select::make('rencana_aksi_id')
                            ->label('Rencana Aksi Terkait')
                            ->relationship('rencanaAksi', 'rencana_aksi')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => ! $isAdmin || $get('kategori_form') === 'spesifik'),
                    ])
                    ->columnSpanFull(),

                Section::make('Detail Aktivitas LHK')
                    ->columns(1)
                    ->schema([
                        Textarea::make('nama_aktivitas')
                            ->label('Nama / Uraian Aktivitas')
                            ->required()
                            ->rows(3)
                            ->placeholder('Tuliskan uraian aktivitas kinerja harian pegawai...'),

                        TextInput::make('menit')
                            ->label('Standar Durasi')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->default(30)
                            ->suffix('menit')
                            ->helperText('Contoh: 30, 60 (1 jam), 120 (2 jam), 180 (3 jam).'),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
