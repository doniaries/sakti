<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identitas Pegawai')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 4,
                        ])
                        ->schema([
                            ImageEntry::make('avatar_url')
                                ->label('Foto Profil')
                                ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=0D8ABC&color=fff')
                                ->circular()
                                ->disk('public')
                                ->columnSpan([
                                    'default' => 1,
                                    'md' => 1,
                                ]),

                            Group::make([
                                TextEntry::make('nama_lengkap_dan_gelar')
                                    ->label('Nama Lengkap & Gelar')
                                    ->state(fn ($record) => trim(($record->gelar_depan ? $record->gelar_depan . ' ' : '') . $record->name . ($record->gelar_belakang ? ', ' . $record->gelar_belakang : '')))
                                    ->size(TextSize::Large)
                                    ->weight('bold')
                                    ->icon('heroicon-m-user')
                                    ->color('primary'),

                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('nip')
                                            ->label('NIP')
                                            ->icon('heroicon-m-hashtag')
                                            ->copyable()
                                            ->weight('medium'),

                                        TextEntry::make('nik')
                                            ->label('NIK')
                                            ->icon('heroicon-m-credit-card')
                                            ->copyable()
                                            ->placeholder('-'),

                                        TextEntry::make('jenis_kelamin')
                                            ->label('Jenis Kelamin')
                                            ->badge()
                                            ->color(fn ($state) => match ($state?->value ?? $state) {
                                                'L', 'Laki-Laki' => 'info',
                                                'P', 'Perempuan' => 'danger',
                                                default => 'gray',
                                            }),
                                    ]),
                            ])
                            ->columnSpan([
                                'default' => 1,
                                'md' => 3,
                            ]),
                        ]),
                    ]),

                Grid::make([
                    'default' => 1,
                    'lg' => 2,
                ])
                ->schema([
                    Section::make('Data Pribadi & Kontak')
                        ->icon('heroicon-o-user')
                        ->columns(2)
                        ->schema([
                            TextEntry::make('tempat_lahir')
                                ->label('Tempat Lahir')
                                ->icon('heroicon-m-map-pin')
                                ->placeholder('-'),

                            TextEntry::make('tanggal_lahir')
                                ->label('Tanggal Lahir')
                                ->date('d F Y')
                                ->icon('heroicon-m-calendar')
                                ->placeholder('-'),

                            TextEntry::make('agama')
                                ->label('Agama')
                                ->icon('heroicon-m-bookmark')
                                ->placeholder('-'),

                            TextEntry::make('no_hp')
                                ->label('Nomor HP / WhatsApp')
                                ->icon('heroicon-m-device-phone-mobile')
                                ->copyable()
                                ->placeholder('-'),

                            TextEntry::make('no_telp')
                                ->label('Nomor Telepon')
                                ->icon('heroicon-m-phone')
                                ->placeholder('0'),

                            TextEntry::make('email_pribadi')
                                ->label('Email Pribadi')
                                ->icon('heroicon-m-envelope')
                                ->copyable()
                                ->placeholder('-'),

                            TextEntry::make('email')
                                ->label('Email Akun Dinas')
                                ->icon('heroicon-m-at-symbol')
                                ->copyable()
                                ->columnSpanFull(),

                            TextEntry::make('alamat')
                                ->label('Alamat Domisili')
                                ->icon('heroicon-m-home')
                                ->placeholder('-')
                                ->columnSpanFull(),
                        ]),

                    Section::make('Data Kepegawaian & Dokumen')
                        ->icon('heroicon-o-briefcase')
                        ->columns(2)
                        ->schema([
                            TextEntry::make('jabatan.nama')
                                ->label('Jabatan')
                                ->badge()
                                ->color('primary')
                                ->placeholder('Belum Ditentukan'),

                            TextEntry::make('bidang.nama')
                                ->label('Bidang / Unit Kerja')
                                ->badge()
                                ->color('gray')
                                ->placeholder('Pimpinan / Tanpa Bidang'),

                            TextEntry::make('pangkat_golongan')
                                ->label('Pangkat / Golongan')
                                ->badge()
                                ->color('info')
                                ->placeholder('-'),

                            TextEntry::make('eselon')
                                ->label('Eselon')
                                ->badge()
                                ->color('warning')
                                ->placeholder('Non Eselon'),

                            TextEntry::make('jenjang_pendidikan')
                                ->label('Jenjang Pendidikan')
                                ->badge()
                                ->color('success')
                                ->placeholder('-'),

                            TextEntry::make('pendidikan')
                                ->label('Jurusan / Prodi')
                                ->placeholder('-'),

                            TextEntry::make('npwp')
                                ->label('Nomor NPWP')
                                ->icon('heroicon-m-wallet')
                                ->copyable()
                                ->placeholder('-'),

                            TextEntry::make('tanggal_npwp')
                                ->label('Tanggal NPWP')
                                ->date('d/m/Y')
                                ->placeholder('-'),

                            TextEntry::make('no_bpjs')
                                ->label('Nomor BPJS')
                                ->icon('heroicon-m-shield-check')
                                ->copyable()
                                ->placeholder('-'),

                            TextEntry::make('no_taspen')
                                ->label('Nomor Taspen')
                                ->icon('heroicon-m-building-library')
                                ->copyable()
                                ->placeholder('-'),
                        ]),
                ]),
            ]);
    }
}
