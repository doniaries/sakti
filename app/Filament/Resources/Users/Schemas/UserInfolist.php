<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\Facades\Storage;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // Header Identitas Utama Pegawai
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

                // Tab Navigasi MyASN
                Tabs::make('DataPegawaiTabs')
                    ->tabs([
                        // Tab 1: Data Utama
                        Tab::make('Data Utama')
                            ->icon('heroicon-m-user')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'lg' => 2,
                                ])
                                ->schema([
                                    Section::make('Identitas Personal')
                                        ->icon('heroicon-o-identification')
                                        ->columns(2)
                                        ->schema([
                                            TextEntry::make('nip')
                                                ->label('NIP')
                                                ->icon('heroicon-m-hashtag')
                                                ->copyable()
                                                ->weight('bold'),

                                            TextEntry::make('nik')
                                                ->label('NIK')
                                                ->icon('heroicon-m-credit-card')
                                                ->copyable()
                                                ->placeholder('-'),

                                            TextEntry::make('name')
                                                ->label('Nama Pegawai')
                                                ->icon('heroicon-m-user')
                                                ->weight('medium'),

                                            TextEntry::make('jenis_kelamin')
                                                ->label('Jenis Kelamin')
                                                ->badge()
                                                ->color(fn ($state) => match ($state?->value ?? $state) {
                                                    'L', 'Laki-Laki' => 'info',
                                                    'P', 'Perempuan' => 'danger',
                                                    default => 'gray',
                                                }),

                                            TextEntry::make('gelar_depan')
                                                ->label('Gelar Depan')
                                                ->icon('heroicon-m-academic-cap')
                                                ->placeholder('-'),

                                            TextEntry::make('gelar_belakang')
                                                ->label('Gelar Belakang')
                                                ->icon('heroicon-m-academic-cap')
                                                ->placeholder('-'),

                                            TextEntry::make('tempat_lahir')
                                                ->label('Tempat Lahir')
                                                ->icon('heroicon-m-map-pin')
                                                ->placeholder('-'),

                                            TextEntry::make('tanggal_lahir')
                                                ->label('Tanggal Lahir')
                                                ->date('d F Y')
                                                ->icon('heroicon-m-calendar')
                                                ->placeholder('-'),
                                        ]),

                                    Section::make('Kontak & Administrasi')
                                        ->icon('heroicon-o-envelope')
                                        ->columns(2)
                                        ->schema([
                                            TextEntry::make('agama')
                                                ->label('Agama')
                                                ->icon('heroicon-m-bookmark')
                                                ->placeholder('-'),

                                            TextEntry::make('alamat')
                                                ->label('Alamat Domisili')
                                                ->icon('heroicon-m-home')
                                                ->placeholder('-')
                                                ->columnSpanFull(),

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
                                                ->label('Email Dinas / Akun')
                                                ->icon('heroicon-m-at-symbol')
                                                ->copyable(),

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
                            ]),

                        // Tab 2: Golongan & Pangkat
                        Tab::make('Golongan')
                            ->icon('heroicon-m-shield-check')
                            ->schema([
                                Section::make('Kepangkatan & Golongan Ruang')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('pangkat_golongan')
                                            ->label('Golongan / Ruang')
                                            ->badge()
                                            ->color('info')
                                            ->placeholder('-'),

                                        TextEntry::make('pangkat_pembina')
                                            ->label('Pangkat')
                                            ->state(fn ($record) => match ($record->pangkat_golongan?->value ?? $record->pangkat_golongan) {
                                                'IV/e' => 'Pembina Utama',
                                                'IV/d' => 'Pembina Utama Madya',
                                                'IV/c' => 'Pembina Utama Muda',
                                                'IV/b' => 'Pembina Tingkat I',
                                                'IV/a' => 'Pembina',
                                                'III/d' => 'Penata Tingkat I',
                                                'III/c' => 'Penata',
                                                'III/b' => 'Penata Muda Tingkat I',
                                                'III/a' => 'Penata Muda',
                                                'II/d' => 'Pengatur Tingkat I',
                                                'II/c' => 'Pengatur',
                                                'II/b' => 'Pengatur Muda Tingkat I',
                                                'II/a' => 'Pengatur Muda',
                                                'I/d' => 'Juru Tingkat I',
                                                'I/c' => 'Juru',
                                                'I/b' => 'Juru Muda Tingkat I',
                                                'I/a' => 'Juru Muda',
                                                default => '-',
                                            })
                                            ->weight('medium'),

                                        TextEntry::make('eselon')
                                            ->label('Eselon')
                                            ->badge()
                                            ->color('warning')
                                            ->placeholder('Non Eselon'),

                                        TextEntry::make('created_at')
                                            ->label('Terdaftar Sejak')
                                            ->date('d F Y')
                                            ->icon('heroicon-m-calendar'),
                                    ]),
                            ]),

                        // Tab 3: Jabatan & Posisi
                        Tab::make('Jabatan')
                            ->icon('heroicon-m-briefcase')
                            ->schema([
                                Section::make('Informasi Jabatan & Unit Kerja')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('jabatan.jenis_jabatan')
                                            ->label('Jenis Jabatan')
                                            ->state(fn ($record) => $record->jabatan?->nama ? (str_contains($record->jabatan->nama, 'Kepala') || str_contains($record->jabatan->nama, 'Sekretaris') || str_contains($record->jabatan->nama, 'Kabid') || str_contains($record->jabatan->nama, 'Kasubbag') ? 'Jabatan Struktural' : 'Jabatan Fungsional / Pelaksana') : '-')
                                            ->badge()
                                            ->color('primary'),

                                        TextEntry::make('jabatan.nama')
                                            ->label('Nama Jabatan')
                                            ->weight('bold')
                                            ->placeholder('Belum Ditentukan'),

                                        TextEntry::make('bidang.nama')
                                            ->label('Bidang / Unit Kerja')
                                            ->badge()
                                            ->color(fn (?string $state): string => \App\Models\Bidang::badgeColor($state))
                                            ->placeholder('Pimpinan / Tanpa Bidang'),

                                        TextEntry::make('atasan.name')
                                            ->label('Atasan Langsung')
                                            ->icon('heroicon-m-user-circle')
                                            ->placeholder('Pimpinan Tertinggi (Tidak Ada Atasan Langsung)'),

                                        TextEntry::make('status_kerja')
                                            ->label('Tipe Kerja')
                                            ->badge()
                                            ->color(fn ($state) => strtolower($state ?? 'wfo') === 'wfh' ? 'warning' : 'success')
                                            ->formatStateUsing(fn ($state) => strtoupper($state ?? 'WFO')),

                                        TextEntry::make('jadwalKerja.nama')
                                            ->label('Shift / Jadwal Kerja')
                                            ->badge()
                                            ->color('info')
                                            ->placeholder('Jadwal Default Sistem'),

                                        TextEntry::make('nomor_absensi')
                                            ->label('ID Mesin Presensi (PIN)')
                                            ->badge()
                                            ->color('secondary')
                                            ->placeholder('Belum Terhubung Mesin'),
                                    ]),
                            ]),

                        // Tab 4: Pendidikan
                        Tab::make('Pendidikan')
                            ->icon('heroicon-m-academic-cap')
                            ->schema([
                                Section::make('Riwayat Pendidikan Formal (MyASN)')
                                    ->description('Daftar riwayat jenjang pendidikan formal yang tercatat dan lampiran berkas Ijazah asli.')
                                    ->icon('heroicon-o-academic-cap')
                                    ->schema([
                                        RepeatableEntry::make('riwayatPendidikan')
                                            ->label('')
                                            ->schema([
                                                Grid::make([
                                                    'default' => 1,
                                                    'md' => 4,
                                                ])
                                                ->schema([
                                                    TextEntry::make('jenjang')
                                                        ->label('Jenjang')
                                                        ->badge()
                                                        ->color(fn ($state) => $state instanceof \App\Enums\JenjangPendidikan ? $state->getColor() : 'primary'),

                                                    TextEntry::make('nama_institusi')
                                                        ->label('Nama Sekolah / Perguruan Tinggi')
                                                        ->weight('bold')
                                                        ->columnSpan(2),

                                                    TextEntry::make('tahun_lulus')
                                                        ->label('Tahun Lulus')
                                                        ->badge()
                                                        ->color('info')
                                                        ->placeholder('-'),

                                                    TextEntry::make('jurusan')
                                                        ->label('Jurusan / Program Studi')
                                                        ->placeholder('-')
                                                        ->columnSpan(2),

                                                    TextEntry::make('nomor_ijazah')
                                                        ->label('Nomor Seri Ijazah')
                                                        ->placeholder('-'),

                                                    TextEntry::make('file_ijazah')
                                                        ->label('Lampiran Berkas Ijazah')
                                                        ->formatStateUsing(fn ($state) => $state ? 'Unduh Berkas Ijazah (PDF)' : 'Belum diunggah')
                                                        ->icon(fn ($state) => $state ? 'heroicon-m-arrow-down-tray' : 'heroicon-m-x-circle')
                                                        ->color(fn ($state) => $state ? 'primary' : 'gray')
                                                        ->url(fn ($record) => $record->file_ijazah ? Storage::disk('public')->url($record->file_ijazah) : null, shouldOpenInNewTab: true),
                                                ]),
                                            ]),
                                    ]),
                            ]),

                        // Tab 5: Keluarga & Lainnya
                        Tab::make('Keluarga')
                            ->icon('heroicon-m-user-group')
                            ->schema([
                                Section::make('Data Anggota Keluarga & Kependudukan')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('no_kk')
                                            ->label('Nomor Kartu Keluarga (KK)')
                                            ->icon('heroicon-m-identification')
                                            ->copyable()
                                            ->placeholder('-'),

                                        TextEntry::make('nik')
                                            ->label('NIK')
                                            ->icon('heroicon-m-credit-card')
                                            ->copyable()
                                            ->placeholder('-'),

                                        TextEntry::make('alamat')
                                            ->label('Alamat Domisili Keluarga')
                                            ->icon('heroicon-m-home')
                                            ->placeholder('-')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}

