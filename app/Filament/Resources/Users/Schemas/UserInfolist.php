<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
use Illuminate\Support\Facades\Auth;
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
                                    ->rules(['nullable', 'min:6'])
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
                                    ->extraImgAttributes(['class' => 'cursor-pointer hover:opacity-90 transition'])
                                    ->action(
                                        Action::make('zoom_foto_profil')
                                            ->modalHeading(fn (User $record): string => 'Foto Profil — ' . $record->name)
                                            ->modalSubmitAction(false)
                                            ->modalCancelActionLabel('Tutup')
                                            ->modalContent(function (User $record): \Illuminate\Support\HtmlString {
                                                $url = $record->avatar_url
                                                    ? asset('storage/' . ltrim((string) $record->avatar_url, '/'))
                                                    : 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&background=0D8ABC&color=fff&size=512';

                                                return new \Illuminate\Support\HtmlString(
                                                    '<div class="flex justify-center"><img src="' . e($url) . '" alt="' . e($record->name) . '" class="max-h-[75vh] w-auto rounded-xl object-contain shadow-lg" /></div>'
                                                );
                                            })
                                    )
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
                                                ->tooltip('Klik untuk menyalin NIP')
                                                ->helperText('Klik untuk menyalin')
                                                ->weight('medium'),

                                            TextEntry::make('email')
                                                ->label('Email Login')
                                                ->icon('heroicon-m-envelope')
                                                ->copyable()
                                                ->tooltip('Klik untuk menyalin Email')
                                                ->helperText('Klik untuk menyalin')
                                                ->weight('medium')
                                                ->placeholder('-'),

                                            TextEntry::make('nik')
                                                ->label('NIK')
                                                ->icon('heroicon-m-credit-card')
                                                ->copyable()
                                                ->tooltip('Klik untuk menyalin NIK')
                                                ->helperText('Klik untuk menyalin')
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

                                            TextEntry::make('atasan.name')
                                                ->label('Atasan Langsung')
                                                ->icon('heroicon-m-user-circle')
                                                ->weight('medium')
                                                ->placeholder('Pimpinan Tertinggi (Tidak Ada Atasan Langsung)'),

                                            TextEntry::make('roles.name')
                                                ->label('Role / Hak Akses')
                                                ->icon('heroicon-m-shield-check')
                                                ->badge()
                                                ->color('primary')
                                                ->placeholder('-'),

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
                                            ->headerActions([
                                                Action::make('ubah_identitas')
                                                    ->label('Ubah')
                                                    ->icon('heroicon-o-pencil-square')
                                                    ->size('sm')
                                                    ->color('gray')
                                                    ->slideOver()
                                                    ->modalHeading('Ubah Identitas Personal')
                                                    ->visible(function (User $record): bool {
                                                        /** @var User|null $authUser */
                                                        $authUser = Auth::user();

                                                        return $authUser?->can('update', $record) ?? false;
                                                    })
                                                    ->fillForm(fn (User $record): array => [
                                                        'nip' => $record->nip,
                                                        'nik' => $record->nik,
                                                        'name' => $record->name,
                                                        'jenis_kelamin' => $record->jenis_kelamin?->value ?? $record->jenis_kelamin,
                                                        'gelar_depan' => $record->gelar_depan,
                                                        'gelar_belakang' => $record->gelar_belakang,
                                                        'tempat_lahir' => $record->tempat_lahir,
                                                        'tanggal_lahir' => $record->tanggal_lahir,
                                                    ])
                                                    ->form([
                                                        TextInput::make('nip')->label('NIP')->maxLength(50),
                                                        TextInput::make('nik')->label('NIK')->maxLength(50),
                                                        TextInput::make('name')->label('Nama Pegawai')->required()->maxLength(255),
                                                        Select::make('jenis_kelamin')
                                                            ->label('Jenis Kelamin')
                                                            ->options(['L' => 'Laki-Laki', 'P' => 'Perempuan']),
                                                        TextInput::make('gelar_depan')->label('Gelar Depan')->maxLength(50),
                                                        TextInput::make('gelar_belakang')->label('Gelar Belakang')->maxLength(100),
                                                        TextInput::make('tempat_lahir')->label('Tempat Lahir')->maxLength(100),
                                                        DatePicker::make('tanggal_lahir')->label('Tanggal Lahir')->displayFormat('d/m/Y'),
                                                    ])
                                                    ->action(function (User $record, array $data): void {
                                                        $record->update($data);
                                                        Notification::make()->title('Identitas personal berhasil diperbarui')->success()->send();
                                                    }),
                                            ])
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
                                            ->headerActions([
                                                Action::make('ubah_kontak')
                                                    ->label('Ubah')
                                                    ->icon('heroicon-o-pencil-square')
                                                    ->size('sm')
                                                    ->color('gray')
                                                    ->slideOver()
                                                    ->modalHeading('Ubah Kontak & Administrasi')
                                                    ->visible(function (User $record): bool {
                                                        /** @var User|null $authUser */
                                                        $authUser = Auth::user();

                                                        return $authUser?->can('update', $record) ?? false;
                                                    })
                                                    ->fillForm(fn (User $record): array => [
                                                        'agama' => $record->agama,
                                                        'alamat' => $record->alamat,
                                                        'no_hp' => $record->no_hp,
                                                        'no_telp' => $record->no_telp,
                                                        'email' => $record->email,
                                                        'npwp' => $record->npwp,
                                                        'tanggal_npwp' => $record->tanggal_npwp,
                                                        'no_bpjs' => $record->no_bpjs,
                                                        'no_taspen' => $record->no_taspen,
                                                    ])
                                                    ->form([
                                                        Select::make('agama')
                                                            ->label('Agama')
                                                            ->options([
                                                                'Islam' => 'Islam',
                                                                'Kristen Protestan' => 'Kristen Protestan',
                                                                'Katolik' => 'Katolik',
                                                                'Hindu' => 'Hindu',
                                                                'Buddha' => 'Buddha',
                                                                'Konghucu' => 'Konghucu',
                                                            ]),
                                                        Textarea::make('alamat')->label('Alamat Domisili')->rows(2),
                                                        TextInput::make('no_hp')->label('Nomor HP / WhatsApp')->maxLength(30),
                                                        TextInput::make('no_telp')->label('Nomor Telepon')->maxLength(30),
                                                        TextInput::make('email')->label('Email User')->email()->required()->maxLength(255),
                                                        TextInput::make('npwp')->label('Nomor NPWP')->maxLength(50),
                                                        DatePicker::make('tanggal_npwp')->label('Tanggal NPWP')->displayFormat('d/m/Y'),
                                                        TextInput::make('no_bpjs')->label('Nomor BPJS')->maxLength(50),
                                                        TextInput::make('no_taspen')->label('Nomor Taspen')->maxLength(50),
                                                    ])
                                                    ->action(function (User $record, array $data): void {
                                                        $record->update($data);
                                                        Notification::make()->title('Kontak & administrasi berhasil diperbarui')->success()->send();
                                                    }),
                                            ])
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

                                                TextEntry::make('email')
                                                    ->label('Email User')
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
                            ->disabled(fn ($record): bool => self::isKontrak($record))
                            ->extraAttributes(fn ($record): array => self::disabledTabAttributes($record))
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

                                                        self::pdfAttachmentEntry('file_sk_golongan', 'Lampiran Berkas SK Pangkat'),
                                                    ]),
                                            ]),
                                    ]),
                            ]),

                        // Tab 3: Jabatan & Posisi (MyASN Style)
                        Tab::make('Jabatan')
                            ->icon('heroicon-m-briefcase')
                            ->disabled(fn ($record): bool => self::isKontrak($record))
                            ->extraAttributes(fn ($record): array => self::disabledTabAttributes($record))
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

                                        TextEntry::make('jabatan_tim_kreatif')
                                            ->label('Tim Kreatif Dinas')
                                            ->badge()
                                            ->placeholder('Bukan Anggota Tim Kreatif'),
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

                                                        self::pdfAttachmentEntry('file_sk_jabatan', 'Lampiran Berkas SK Jabatan'),
                                                    ]),
                                            ]),
                                    ]),
                            ]),

                        // Tab 4: Pendidikan
                        Tab::make('Pendidikan')
                            ->icon('heroicon-m-academic-cap')
                            ->disabled(fn ($record): bool => self::isKontrak($record))
                            ->extraAttributes(fn ($record): array => self::disabledTabAttributes($record))
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

                                                        self::pdfAttachmentEntry('file_ijazah', 'Lampiran Berkas Ijazah'),
                                                    ]),
                                            ]),
                                    ]),

                                Section::make('Sertifikat, Diklat & Penghargaan')
                                    ->description('Daftar sertifikat pelatihan, bimtek, kompetensi, dan penghargaan pegawai.')
                                    ->icon('heroicon-o-document-check')
                                    ->schema([
                                        RepeatableEntry::make('riwayatSertifikat')
                                            ->label('')
                                            ->schema([
                                                Grid::make([
                                                    'default' => 1,
                                                    'md' => 4,
                                                ])
                                                    ->schema([
                                                        TextEntry::make('jenis')
                                                            ->label('Jenis')
                                                            ->badge()
                                                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                                                'diklat' => 'Diklat',
                                                                'bimtek' => 'Bimtek / Workshop',
                                                                'penghargaan' => 'Penghargaan',
                                                                'lainnya' => 'Lainnya',
                                                                default => 'Sertifikat',
                                                            })
                                                            ->color('info'),

                                                        TextEntry::make('nama_sertifikat')
                                                            ->label('Nama Sertifikat / Diklat')
                                                            ->weight('bold')
                                                            ->columnSpan(2),

                                                        TextEntry::make('tanggal_terbit')
                                                            ->label('Tanggal Terbit')
                                                            ->date('d/m/Y')
                                                            ->placeholder('-'),

                                                        TextEntry::make('penyelenggara')
                                                            ->label('Penyelenggara')
                                                            ->placeholder('-')
                                                            ->columnSpan(2),

                                                        TextEntry::make('nomor_sertifikat')
                                                            ->label('Nomor Sertifikat')
                                                            ->placeholder('-'),

                                                        self::pdfAttachmentEntry('file_sertifikat', 'Lampiran Sertifikat'),
                                                    ]),
                                            ]),
                                    ]),
                            ]),

                        // Tab 5: Keluarga & Lainnya
                        Tab::make('Keluarga')
                            ->icon('heroicon-m-user-group')
                            ->disabled(fn ($record): bool => self::isKontrak($record))
                            ->extraAttributes(fn ($record): array => self::disabledTabAttributes($record))
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

    private static function pdfAttachmentEntry(string $attribute, string $label): TextEntry
    {
        return TextEntry::make($attribute)
            ->label($label)
            ->formatStateUsing(fn ($state): string => filled($state) ? 'Preview PDF' : 'Belum diunggah')
            ->icon(fn ($state): string => filled($state) ? 'heroicon-m-eye' : 'heroicon-m-x-circle')
            ->color(fn ($state): string => filled($state) ? 'primary' : 'gray')
            ->url(function ($record) use ($attribute): ?string {
                $path = $record->{$attribute} ?? null;
                if (blank($path)) {
                    return null;
                }

                /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                $disk = Storage::disk('public');

                return $disk->url($path);
            }, shouldOpenInNewTab: true)
            ->suffixAction(
                Action::make('unduh_' . $attribute)
                    ->icon('heroicon-m-arrow-down-tray')
                    ->color('success')
                    ->tooltip('Unduh Berkas PDF')
                    ->visible(fn ($record): bool => filled($record->{$attribute} ?? null) && Storage::disk('public')->exists((string) $record->{$attribute}))
                    ->action(function ($record) use ($attribute) {
                        $path = (string) ($record->{$attribute} ?? '');

                        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                        $disk = Storage::disk('public');

                        return $disk->download($path);
                    })
            );
    }

    private static function isKontrak(mixed $record): bool
    {
        $status = $record?->status_kepegawaian ?? null;
        $value = $status instanceof \App\Enums\StatusKepegawaian
            ? $status->value
            : (string) $status;

        return $value === \App\Enums\StatusKepegawaian::KONTRAK->value;
    }

    /**
     * @return array<string, mixed>
     */
    private static function disabledTabAttributes(mixed $record): array
    {
        if (! self::isKontrak($record)) {
            return [];
        }

        return [
            'disabled' => true,
            'aria-disabled' => 'true',
            'class' => 'opacity-40 pointer-events-none cursor-not-allowed',
            'title' => 'Tidak berlaku untuk Pegawai Kontrak / Non-ASN',
        ];
    }
}
