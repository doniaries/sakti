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
        $isSuperAdmin = $currentUser && $currentUser->hasAnyRole(['super_admin', 'superadmin']);

        return $schema
            ->components([
                Section::make('Cakupan & Pemilik Aktivitas')
                    ->description($isSuperAdmin
                        ? 'Pilih Global jika aktivitas dapat digunakan oleh seluruh pegawai, atau Per Bidang / Pegawai untuk bidang/pegawai tertentu.'
                        : 'Isi aktivitas untuk Per Bidang / Pegawai tertentu.')
                    ->columns(3)
                    ->schema([
                        Radio::make('kategori_form')
                            ->label('Kategori Aktivitas')
                            ->options([
                                'global' => 'Global (Semua Pegawai)',
                                'spesifik' => 'Per Bidang / Pegawai / Jabatan',
                            ])
                            ->default($isSuperAdmin ? 'global' : 'spesifik')
                            ->inline()
                            ->live()
                            ->dehydrated(false)
                            ->visible($isSuperAdmin)
                            ->afterStateHydrated(function (Radio $component, ?Aktivitas $record) use ($isSuperAdmin): void {
                                if (! $record) {
                                    $component->state($isSuperAdmin ? 'global' : 'spesifik');
                                    return;
                                }
                                $isGlobal = is_null($record->user_id) && is_null($record->jabatan_id);
                                $component->state($isGlobal && $isSuperAdmin ? 'global' : 'spesifik');
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
                            ->relationship('user', 'name', fn ($query) => $query->orderBy('id'))
                            ->default($isAdmin ? null : $currentUser?->id)
                            ->disabled(! $isAdmin)
                            ->dehydrated()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->visible(fn (Get $get): bool => ! $isSuperAdmin || $get('kategori_form') === 'spesifik')
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
                            ->visible(fn (Get $get): bool => ! $isSuperAdmin || $get('kategori_form') === 'spesifik'),

                        Select::make('bidang_id')
                            ->label('Bidang')
                            ->relationship('bidang', 'nama')
                            ->default($isAdmin ? null : $currentUser?->bidang_id)
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->visible(fn (Get $get): bool => ! $isSuperAdmin || $get('kategori_form') === 'spesifik'),

                        Select::make('rencana_aksi_id')
                            ->label('Rencana Aksi Terkait')
                            ->relationship('rencanaAksi', 'rencana_aksi')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => ! $isSuperAdmin || $get('kategori_form') === 'spesifik'),
                    ])
                    ->columnSpanFull(),

                Section::make('Detail Aktivitas LHK')
                    ->columns(1)
                    ->schema([
                        Textarea::make('nama_aktivitas')
                            ->label('Nama / Uraian Aktivitas')
                            ->required()
                            ->rows(3)
                            ->rule(function (Get $get, ?Aktivitas $record): \Closure {
                                return function (string $attribute, mixed $value, \Closure $fail) use ($get, $record): void {
                                    $targetUserId = $get('kategori_form') === 'global'
                                        ? null
                                        : ($get('user_id') ? (int) $get('user_id') : $record?->user_id);

                                    if (Aktivitas::sudahAdaDuplikat((string) $value, $targetUserId, $record?->id)) {
                                        $fail('Aktivitas dengan uraian yang sama sudah ada di Bank Aktivitas (Global atau milik pegawai ini).');
                                    }
                                };
                            })
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
