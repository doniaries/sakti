<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                \Filament\Schemas\Components\Group::make()
                    ->schema([
                        Section::make('Profil Pegawai')
                            ->icon('heroicon-o-user')
                            ->schema([

                                \Filament\Forms\Components\TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->required()
                                    ->maxLength(255),
                                \Filament\Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                \Filament\Forms\Components\TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->placeholder('Abaikan jika tidak ingin merubah...')
                                    ->revealable()
                                    ->confirmed()
                                    ->required(fn ($operation) => $operation === 'create')
                                    ->maxLength(255)
                                    ->dehydrated(fn ($state) => filled($state)),

                                \Filament\Forms\Components\TextInput::make('password_confirmation')
                                    ->label('Konfirmasi Password')
                                    ->password()
                                    ->placeholder('Ketik ulang password baru...')
                                    ->revealable()
                                    ->required(fn ($operation, callable $get) => $operation === 'create' || filled($get('password')))
                                    ->maxLength(255)
                                    ->dehydrated(false),

                                \Filament\Forms\Components\Toggle::make('aktif')
                                    ->label('Status Akun Aktif')
                                    ->default(true)
                                    ->inline(false)
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Section::make('Data Kependudukan & Administrasi Instansi')
                            ->description('Kebutuhan data internal instansi (NIK, No. KK, No. HP/WA, BPJS Kesehatan) agar tidak perlu diminta berulang kali.')
                            ->icon('heroicon-o-identification')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('nik')
                                    ->label('Nomor Induk Kependudukan (NIK)')
                                    ->mask('9999999999999999')
                                    ->placeholder('16 digit NIK...')
                                    ->maxLength(20)
                                    ->numeric(),
                                \Filament\Forms\Components\TextInput::make('no_kk')
                                    ->label('Nomor Kartu Keluarga (KK)')
                                    ->mask('9999999999999999')
                                    ->placeholder('16 digit No. KK...')
                                    ->maxLength(20)
                                    ->numeric(),
                                \Filament\Forms\Components\TextInput::make('no_hp')
                                    ->label('Nomor Handphone / WhatsApp')
                                    ->tel()
                                    ->placeholder('Contoh: 081234567890')
                                    ->maxLength(25),
                                \Filament\Forms\Components\TextInput::make('no_bpjs')
                                    ->label('Nomor BPJS Kesehatan')
                                    ->placeholder('Nomor Kartu BPJS Kesehatan...')
                                    ->maxLength(30),
                            ])->columns(2),

                        Section::make('Detail Kepegawaian & Posisi')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('nip')
                                    ->label('NIP')
                                    ->mask('99999999 999999 9 999')
                                    ->placeholder('Contoh: 19720407 199202 1 002')
                                    ->maxLength(30),
                                \Filament\Forms\Components\TextInput::make('nomor_absensi')
                                    ->label('ID Mesin (PIN)')
                                    ->maxLength(50)
                                    ->readOnly(function () {
                                        /** @var \App\Models\User|null $user */
                                        $user = auth()->user();
                                        return !($user && ($user->hasRole('super_admin') || $user->hasRole('admin')));
                                    })
                                    ->helperText(function () {
                                        /** @var \App\Models\User|null $user */
                                        $user = auth()->user();
                                        return !($user && ($user->hasRole('super_admin') || $user->hasRole('admin')))
                                            ? 'Hanya Administrator yang dapat mengubah ID Mesin Presensi.'
                                            : null;
                                    }),
                                \Filament\Forms\Components\Select::make('bidang_id')
                                    ->label('Bidang')
                                    ->relationship('bidang', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Kosongkan jika Kepala Dinas (Pimpinan Tertinggi)')
                                    ->helperText('Kepala Dinas tidak memiliki bidang karena memimpin seluruh dinas.'),
                                \Filament\Forms\Components\Select::make('jabatan_id')
                                    ->label('Jabatan')
                                    ->relationship('jabatan', 'nama')
                                    ->searchable()
                                    ->preload(),
                                \Filament\Forms\Components\Select::make('atasan_id')
                                    ->label('Atasan Langsung')
                                    ->relationship('atasan', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Kosongkan jika Kepala Dinas')
                                    ->helperText('Kepala Dinas tidak memiliki atasan langsung di lingkungan dinas.'),
                                \Filament\Forms\Components\Select::make('jadwal_kerja_id')
                                    ->label('Jadwal / Shift Kerja')
                                    ->relationship('jadwalKerja', 'nama')
                                    ->placeholder('Ikuti Jadwal Default Sistem')
                                    ->searchable()
                                    ->preload(),
                                \Filament\Forms\Components\Select::make('status_kerja')
                                    ->label('Status Kerja (WFH/WFO)')
                                    ->options([
                                        'wfo' => 'WFO (Work From Office)',
                                        'wfh' => 'WFH (Work From Home)',
                                    ])
                                    ->default('wfo')
                                    ->required(),
                                \Filament\Forms\Components\Select::make('roles')
                                    ->label('Hak Akses (Role)')
                                    ->relationship('roles', 'name', function ($query) {
                                        /** @var \App\Models\User|null $user */
                                        $user = \Illuminate\Support\Facades\Auth::user();
                                        return $user?->hasRole('super_admin') ? $query : $query->where('name', '!=', 'super_admin');
                                    })
                                    ->multiple()
                                    ->preload()
                                    ->searchable(),
                            ])->columns(2),
                    ])->columnSpan(2),

                \Filament\Schemas\Components\Group::make()
                    ->schema([
                        Section::make('Foto Profil')
                            ->schema([
                                \Filament\Forms\Components\FileUpload::make('avatar_url')
                                    ->label('Foto Profil (Avatar)')
                                    ->avatar()
                                    ->image()
                                    ->disk('public')
                                    ->directory('avatars')
                                    ->maxSize(2048)
                                    ->columnSpanFull()
                                    ->alignCenter(),
                            ]),
                        Section::make('Informasi Tambahan')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                \Filament\Forms\Components\Select::make('jenis_kelamin')
                                    ->label('Jenis Kelamin')
                                    ->options(\App\Enums\JenisKelamin::class),
                                \Filament\Forms\Components\Select::make('pangkat_golongan')
                                    ->label('Pangkat / Golongan Ruang')
                                    ->options(\App\Enums\GolonganRuang::class)
                                    ->searchable(),
                                \Filament\Forms\Components\Select::make('eselon')
                                    ->label('Eselon')
                                    ->options(\App\Enums\Eselon::class),
                            ])->columns(1),
                    ]),
            ]);
    }
}
