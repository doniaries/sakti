<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\Facades\Hash;
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
                    ->headerActions([
                        Action::make('quick_account')
                            ->label('Akun & Password')
                            ->icon('heroicon-m-key')
                            ->color('info')
                            ->button()
                            ->visible(fn ($record): bool => (\Illuminate\Support\Facades\Auth::user()?->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian']) ?? false) || \Illuminate\Support\Facades\Auth::id() === $record->id)
                            ->modalHeading('Ubah Nama, Email & Password')
                            ->modalDescription('Perbarui nama lengkap, email login, atau password pegawai secara langsung.')
                            ->modalWidth('md')
                            ->fillForm(fn($record): array => [
                                'name' => $record->name,
                                'email' => $record->email,
                            ])
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                                    ->dehydrateStateUsing(fn(?string $state): ?string => filled($state) ? mb_strtoupper($state, 'UTF-8') : null)
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('Email Login')
                                    ->email()
                                    ->required()
                                    ->unique('users', 'email', ignoreRecord: true)
                                    ->maxLength(255),
                                TextInput::make('password')
                                    ->label('Password Baru')
                                    ->password()
                                    ->revealable()
                                    ->placeholder('Kosongkan jika tidak diubah...')
                                    ->rule('nullable|min:6')
                                    ->maxLength(255),
                            ])
                            ->action(function ($record, array $data): void {
                                $payload = [
                                    'name' => $data['name'],
                                    'email' => $data['email'],
                                ];

                                if (filled($data['password'] ?? null)) {
                                    $payload['password'] = Hash::make($data['password']);
                                }

                                $record->update($payload);

                                Notification::make()
                                    ->title('Data akun & password berhasil diperbarui')
                                    ->success()
                                    ->send();
                            }),
                        Action::make('edit')
                            ->label('Ubah')
                            ->icon('heroicon-m-pencil-square')
                            ->color('warning')
                            ->button()
                            ->visible(fn ($record): bool => UserResource::canEdit($record))
                            ->url(fn($record) => UserResource::getUrl('edit', ['record' => $record])),
                    ])
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 5,
                        ])
                            ->schema([
                                ImageEntry::make('avatar_url')
                                    ->label('Foto Profil')
                                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=0D8ABC&color=fff')
                                    ->circular()
                                    ->disk('public')
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 1,
                                    ]),

                                Group::make([
                                    TextEntry::make('nama_lengkap_dan_gelar')
                                        ->label('Nama Lengkap & Gelar')
                                        ->state(function ($record): string {
                                            $nama = trim((string) $record->name);
                                            $namaLower = mb_strtolower($nama, 'UTF-8');
                                            $depan = trim((string) ($record->gelar_depan ?? ''));
                                            $belakang = trim((string) ($record->gelar_belakang ?? ''));

                                            if ($depan !== '' && ! str_starts_with($namaLower, mb_strtolower($depan, 'UTF-8'))) {
                                                $nama = "{$depan} {$nama}";
                                            }
                                            if ($belakang !== '' && ! str_contains($namaLower, mb_strtolower($belakang, 'UTF-8'))) {
                                                $nama = "{$nama}, {$belakang}";
                                            }

                                            return $nama;
                                        })
                                        ->size(TextSize::Large)
                                        ->weight('bold')
                                        ->icon('heroicon-m-user')
                                        ->color('primary'),

                                    Grid::make([
                                        'default' => 1,
                                        'sm' => 2,
                                        'lg' => 3,
                                    ])
                                        ->schema([
                                            TextEntry::make('nip')
                                                ->label('NIP')
                                                ->icon('heroicon-m-hashtag')
                                                ->copyable()
                                                ->weight('medium'),

                                            TextEntry::make('email')
                                                ->label('Email Login')
                                                ->icon('heroicon-m-envelope')
                                                ->copyable()
                                                ->weight('medium')
                                                ->placeholder('-'),

                                            TextEntry::make('nik')
                                                ->label('NIK')
                                                ->icon('heroicon-m-credit-card')
                                                ->copyable()
                                                ->placeholder('-'),

                                            TextEntry::make('bidang.nama')
                                                ->label('Bidang')
                                                ->icon('heroicon-m-building-office')
                                                ->badge()
                                                ->color('primary')
                                                ->tooltip('Klik untuk melihat daftar pegawai di bidang ini')
                                                ->url(function ($record): ?string {
                                                    if (! $record->bidang_id) {
                                                        return null;
                                                    }

                                                    /** @var \App\Models\User|null $authUser */
                                                    $authUser = \Illuminate\Support\Facades\Auth::user();

                                                    if ($authUser && ! $authUser->canViewAllBidangKepegawaian() && ! $authUser->isPptk()) {
                                                        return UserResource::getUrl('index', ['tab' => 'bidang']);
                                                    }

                                                    return UserResource::getUrl('index', ['tab' => $record->bidang?->slug]);
                                                })
                                                ->visible(fn($record): bool => filled($record->bidang_id)),

                                            TextEntry::make('jfPenempatan.name')
                                                ->label('Penempatan di Bawah JF')
                                                ->icon('heroicon-m-user-group')
                                                ->badge()
                                                ->color('success')
                                                ->visible(fn($record): bool => filled($record->jf_id)),
                                        ]),
                                ])
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 4,
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
                                                    ->color(fn($state) => match ($state?->value ?? $state) {
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

                        // Tab 2: Pangkat / Golongan (MyASN Style)
                        Tab::make('Pangkat / Golongan')
                            ->icon('heroicon-m-shield-check')
                            ->schema([
                                Section::make('Tingkat Eselon')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('eselon')
                                            ->label('Eselon Jabatan')
                                            ->badge()
                                            ->color('warning')
                                            ->placeholder('Non Eselon'),
                                    ]),

                                Section::make('Riwayat Pangkat & Golongan Ruang')
                                    ->description('Daftar riwayat kenaikan pangkat dan golongan ruang yang tercatat beserta berkas SK Pangkat asli.')
                                    ->icon('heroicon-o-academic-cap')
                                    ->schema([
                                        RepeatableEntry::make('riwayatGolongan')
                                            ->label('')
                                            ->schema([
                                                Grid::make([
                                                    'default' => 1,
                                                    'md' => 4,
                                                ])
                                                    ->schema([
                                                        TextEntry::make('golongan')
                                                            ->label('Golongan / Ruang')
                                                            ->badge()
                                                            ->color('info'),

                                                        TextEntry::make('nama_pangkat')
                                                            ->label('Nama Pangkat')
                                                            ->weight('bold')
                                                            ->columnSpan(2),

                                                        TextEntry::make('tmt_golongan')
                                                            ->label('TMT Golongan')
                                                            ->date('d F Y')
                                                            ->badge()
                                                            ->color('success')
                                                            ->placeholder('-'),

                                                        TextEntry::make('nomor_sk')
                                                            ->label('Nomor SK')
                                                            ->placeholder('-')
                                                            ->columnSpan(2),

                                                        TextEntry::make('tanggal_sk')
                                                            ->label('Tanggal SK')
                                                            ->date('d/m/Y')
                                                            ->placeholder('-'),

                                                        TextEntry::make('file_sk_golongan')
                                                            ->label('Lampiran Berkas SK Pangkat')
                                                            ->formatStateUsing(fn($state) => $state ? 'Unduh SK Pangkat (PDF)' : 'Belum diunggah')
                                                            ->icon(fn($state) => $state ? 'heroicon-m-arrow-down-tray' : 'heroicon-m-x-circle')
                                                            ->color(fn($state) => $state ? 'primary' : 'gray')
                                                            ->url(function ($record): ?string {
                                                                if (! $record->file_sk_golongan) {
                                                                    return null;
                                                                }
                                                                /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                                                                $disk = Storage::disk('public');

                                                                return $disk->url($record->file_sk_golongan);
                                                            }, shouldOpenInNewTab: true),
                                                    ]),
                                            ]),
                                    ]),
                            ]),

                        // Tab 3: Jabatan & Posisi (MyASN Style)
                        Tab::make('Jabatan')
                            ->icon('heroicon-m-briefcase')
                            ->schema([
                                Section::make('Informasi Jabatan & Unit Kerja Saat Ini')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('jabatan.jenis_jabatan')
                                            ->label('Jenis Jabatan')
                                            ->state(fn($record) => $record->jabatan?->nama ? (str_contains($record->jabatan->nama, 'Kepala') || str_contains($record->jabatan->nama, 'Sekretaris') || str_contains($record->jabatan->nama, 'Kabid') || str_contains($record->jabatan->nama, 'Kasubbag') ? 'Jabatan Struktural' : 'Jabatan Fungsional / Pelaksana') : '-')
                                            ->badge()
                                            ->color('primary'),

                                        TextEntry::make('jabatan.nama')
                                            ->label('Nama Jabatan')
                                            ->weight('bold')
                                            ->placeholder('Belum Ditentukan'),

                                        TextEntry::make('bidang.nama')
                                            ->label('Bidang / Unit Kerja')
                                            ->badge()
                                            ->color(fn(?string $state): string => \App\Models\Bidang::badgeColor($state))
                                            ->placeholder('Kepala Dinas'),

                                        TextEntry::make('atasan.name')
                                            ->label('Atasan Langsung')
                                            ->icon('heroicon-m-user-circle')
                                            ->placeholder('Pimpinan Tertinggi (Tidak Ada Atasan Langsung)'),

                                        TextEntry::make('status_kerja')
                                            ->label('Tipe Kerja')
                                            ->badge()
                                            ->color(fn($state) => strtolower($state ?? 'wfo') === 'wfh' ? 'warning' : 'success')
                                            ->formatStateUsing(fn($state) => strtoupper($state ?? 'WFO')),

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

                                Section::make('Riwayat Jabatan Pegawai')
                                    ->description('Daftar riwayat jabatan struktural, fungsional, maupun pelaksana beserta berkas SK Jabatan asli.')
                                    ->icon('heroicon-o-briefcase')
                                    ->schema([
                                        RepeatableEntry::make('riwayatJabatan')
                                            ->label('')
                                            ->schema([
                                                Grid::make([
                                                    'default' => 1,
                                                    'md' => 4,
                                                ])
                                                    ->schema([
                                                        TextEntry::make('nama_jabatan')
                                                            ->label('Nama Jabatan')
                                                            ->weight('bold')
                                                            ->columnSpan(2),

                                                        TextEntry::make('jenis_jabatan')
                                                            ->label('Jenis Jabatan')
                                                            ->badge()
                                                            ->color('primary'),

                                                        TextEntry::make('tmt_jabatan')
                                                            ->label('TMT Jabatan')
                                                            ->date('d F Y')
                                                            ->badge()
                                                            ->color('success')
                                                            ->placeholder('-'),

                                                        TextEntry::make('unit_kerja')
                                                            ->label('Unit Kerja')
                                                            ->placeholder('-')
                                                            ->columnSpan(2),

                                                        TextEntry::make('nomor_sk')
                                                            ->label('Nomor SK')
                                                            ->placeholder('-'),

                                                        TextEntry::make('file_sk_jabatan')
                                                            ->label('Lampiran Berkas SK Jabatan')
                                                            ->formatStateUsing(fn($state) => $state ? 'Unduh SK Jabatan (PDF)' : 'Belum diunggah')
                                                            ->icon(fn($state) => $state ? 'heroicon-m-arrow-down-tray' : 'heroicon-m-x-circle')
                                                            ->color(fn($state) => $state ? 'primary' : 'gray')
                                                            ->url(function ($record): ?string {
                                                                if (! $record->file_sk_jabatan) {
                                                                    return null;
                                                                }
                                                                /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                                                                $disk = Storage::disk('public');

                                                                return $disk->url($record->file_sk_jabatan);
                                                            }, shouldOpenInNewTab: true),
                                                    ]),
                                            ]),
                                    ]),
                            ]),

                        // Tab 4: Pendidikan
                        Tab::make('Pendidikan')
                            ->icon('heroicon-m-academic-cap')
                            ->schema([
                                Section::make('Riwayat Pendidikan Formal')
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
                                                            ->color(fn($state) => $state instanceof \App\Enums\JenjangPendidikan ? $state->getColor() : 'primary'),

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
                                                            ->formatStateUsing(fn($state) => $state ? 'Unduh Berkas Ijazah (PDF)' : 'Belum diunggah')
                                                            ->icon(fn($state) => $state ? 'heroicon-m-arrow-down-tray' : 'heroicon-m-x-circle')
                                                            ->color(fn($state) => $state ? 'primary' : 'gray')
                                                            ->url(function ($record): ?string {
                                                                if (! $record->file_ijazah) {
                                                                    return null;
                                                                }
                                                                /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                                                                $disk = Storage::disk('public');

                                                                return $disk->url($record->file_ijazah);
                                                            }, shouldOpenInNewTab: true),
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
                                            ->label('Nomor Induk Kependudukan (NIK)')
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
