<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ActivityLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama Aktivitas')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('created_at')
                                ->label('Waktu Kejadian')
                                ->dateTime('d/m/Y H:i:s')
                                ->badge()
                                ->color('info'),
                            TextEntry::make('module')
                                ->label('Modul Aplikasi')
                                ->badge()
                                ->color(fn (?string $state): string => match ($state) {
                                    'Autentikasi' => 'info',
                                    'Pengajuan SPJ' => 'warning',
                                    'Presensi' => 'success',
                                    'LHK' => 'purple',
                                    'Kepegawaian' => 'emerald',
                                    'Pengaturan' => 'gray',
                                    default => 'primary',
                                }),
                            TextEntry::make('event')
                                ->label('Jenis Aksi')
                                ->badge()
                                ->formatStateUsing(fn (?string $state): string => strtoupper((string) $state))
                                ->color(fn (?string $state): string => match (strtolower((string) $state)) {
                                    'login' => 'success',
                                    'logout' => 'gray',
                                    'login_failed' => 'danger',
                                    'create' => 'success',
                                    'update' => 'info',
                                    'delete' => 'danger',
                                    'verify' => 'success',
                                    'revisi' => 'warning',
                                    'cair' => 'emerald',
                                    'tolak' => 'danger',
                                    default => 'primary',
                                }),
                        ]),
                        Grid::make(2)->schema([
                            TextEntry::make('user_name')
                                ->label('Nama Pengguna (Pelaku)')
                                ->weight('bold'),
                            TextEntry::make('user_role')
                                ->label('Peran / Jabatan Saat Aksi')
                                ->badge()
                                ->color('warning'),
                        ]),
                        TextEntry::make('description')
                            ->label('Rincian Aktivitas')
                            ->columnSpanFull(),
                    ]),

                Section::make('Objek Terkait & Identifikasi Perangkat')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('subject_label')
                                ->label('Nama / Nomor Objek')
                                ->placeholder('-'),
                            TextEntry::make('subject_type')
                                ->label('Tipe Model Sistem')
                                ->placeholder('-'),
                            TextEntry::make('subject_id')
                                ->label('ID Entitas')
                                ->placeholder('-'),
                        ]),
                        Grid::make(2)->schema([
                            TextEntry::make('ip_address')
                                ->label('Alamat IP (IP Address)')
                                ->badge()
                                ->color('gray'),
                            TextEntry::make('device')
                                ->label('Perangkat & Sistem Operasi')
                                ->badge()
                                ->color('info'),
                        ]),
                        TextEntry::make('user_agent')
                            ->label('User Agent Browser Lengkap')
                            ->columnSpanFull()
                            ->placeholder('-'),
                    ]),

                Section::make('Data & Parameter Payload')
                    ->schema([
                        KeyValueEntry::make('properties')
                            ->label('Detail Perubahan Data / Payload')
                            ->columnSpanFull()
                            ->placeholder('Tidak ada parameter tambahan.'),
                    ])
                    ->collapsible(),
            ]);
    }
}

