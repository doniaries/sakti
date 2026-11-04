<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Profil Pegawai')
                            ->icon('heroicon-o-user')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->placeholder('Contoh: AHMAD FAUZI, S.KOM')
                                    ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                                    ->dehydrateStateUsing(fn(?string $state): ?string => filled($state) ? mb_strtoupper($state, 'UTF-8') : null)
                                    ->required()
                                    ->columnSpanFull()
                                    ->maxLength(255),
                                \Filament\Forms\Components\TextInput::make('gelar_depan')
                                    ->label('Gelar Depan')
                                    ->placeholder('Contoh: Dr., Ir., Drs.')
                                    ->maxLength(50),
                                \Filament\Forms\Components\TextInput::make('gelar_belakang')
                                    ->label('Gelar Belakang')
                                    ->placeholder('Contoh: S.IP, S.A.P., S.E., S.Kom')
                                    ->maxLength(100),
                                \Filament\Forms\Components\TextInput::make('email')
                                    ->label('Email Dinas / Akun')
                                    ->email()
                                    ->copyable()
                                    ->placeholder('Contoh: user@sijunjung.go.id')
                                    ->required()
                                    ->maxLength(255),
                                \Filament\Forms\Components\TextInput::make('email_pribadi')
                                    ->label('Email Pribadi')
                                    ->email()
                                    ->copyable()
                                    ->placeholder('Contoh: user@gmail.com')
                                    ->maxLength(255),
                                \Filament\Forms\Components\Toggle::make('aktif')
                                    ->label('Status Akun Aktif')
                                    ->default(true)
                                    ->inline(false)
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Section::make('Data Kependudukan & Administrasi Instansi')
                            ->description('Kebutuhan data internal instansi (NIK, No. KK, No. HP/WA, BPJS Kesehatan, NPWP, Taspen, dll).')
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
                                    ->helperText('Tampilan input nomor Indonesia (08...). Otomatis disimpan ke database dengan kode +62 untuk integrasi WhatsApp.')
                                    ->formatStateUsing(function (?string $state): ?string {
                                        if (blank($state)) {
                                            return null;
                                        }
                                        $clean = preg_replace('/[^\d]/', '', $state);
                                        if (str_starts_with($clean, '62')) {
                                            return '0' . substr($clean, 2);
                                        }
                                        if (str_starts_with($clean, '0')) {
                                            return $clean;
                                        }
                                        return '0' . $clean;
                                    })
                                    ->dehydrateStateUsing(function (?string $state): ?string {
                                        if (blank($state)) {
                                            return null;
                                        }
                                        $clean = preg_replace('/[^\d]/', '', $state);
                                        if (str_starts_with($clean, '0')) {
                                            $clean = substr($clean, 1);
                                        } elseif (str_starts_with($clean, '62')) {
                                            $clean = substr($clean, 2);
                                        }
                                        return '+62' . $clean;
                                    })
                                    ->maxLength(25),
                                \Filament\Forms\Components\TextInput::make('no_telp')
                                    ->label('Nomor Telepon Rumah/Kantor')
                                    ->tel()
                                    ->placeholder('Nomor telepon lain/rumah...')
                                    ->maxLength(25),
                                \Filament\Forms\Components\TextInput::make('npwp')
                                    ->label('Nomor NPWP')
                                    ->placeholder('Contoh: 15.988.184.6-203.000')
                                    ->mask('99.999.999.9-999.999')
                                    ->helperText('Format standar NPWP: 99.999.999.9-999.999 (titik dan strip otomatis)')
                                    ->maxLength(30),
                                \Filament\Forms\Components\DatePicker::make('tanggal_npwp')
                                    ->label('Tanggal NPWP')
                                    ->displayFormat('d/m/Y'),
                                \Filament\Forms\Components\TextInput::make('no_bpjs')
                                    ->label('Nomor BPJS Kesehatan')
                                    ->placeholder('Nomor Kartu BPJS Kesehatan...')
                                    ->maxLength(30),
                                \Filament\Forms\Components\TextInput::make('no_taspen')
                                    ->label('Nomor Taspen')
                                    ->placeholder('Nomor Kartu Taspen...')
                                    ->maxLength(30),
                                \Filament\Forms\Components\Select::make('agama')
                                    ->label('Agama')
                                    ->options([
                                        'Islam' => 'Islam',
                                        'Kristen' => 'Kristen',
                                        'Katolik' => 'Katolik',
                                        'Hindu' => 'Hindu',
                                        'Buddha' => 'Buddha',
                                        'Konghucu' => 'Konghucu',
                                    ]),
                                \Filament\Forms\Components\Textarea::make('alamat')
                                    ->label('Alamat Domisili / Tempat Tinggal')
                                    ->placeholder('Alamat lengkap...')
                                    ->columnSpanFull(),
                            ])->columns(2),

                        Section::make('Detail Kepegawaian & Posisi')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('nip')
                                    ->label('NIP')
                                    ->mask('99999999 999999 9 999')
                                    ->placeholder('Contoh: 19720407 199202 1 002')
                                    ->maxLength(30),
                                \Filament\Forms\Components\Select::make('jenjang_pendidikan')
                                    ->label('Jenjang Pendidikan')
                                    ->options([
                                        'S3' => 'S3 (Doktoral)',
                                        'S2' => 'S2 (Magister)',
                                        'S1' => 'S1 / D-IV (Sarjana)',
                                        'D3' => 'D3 (Diploma)',
                                        'SLTA' => 'SLTA / SMA / SMK',
                                        'SMP' => 'SMP / MTs',
                                        'SD' => 'SD',
                                    ]),
                                \Filament\Forms\Components\TextInput::make('pendidikan')
                                    ->label('Jurusan / Nama Pendidikan')
                                    ->placeholder('Contoh: S1 Ilmu Komputer, S2 Magister Teknik...')
                                    ->maxLength(100),
                                \Filament\Forms\Components\TextInput::make('nomor_absensi')
                                    ->label('ID Mesin (PIN)')
                                    ->maxLength(50)
                                    ->readOnly(function () {
                                        /** @var \App\Models\User|null $user */
                                        $user = \Illuminate\Support\Facades\Auth::user();
                                        return !($user && ($user->hasRole('super_admin') || $user->hasRole('admin')));
                                    })
                                    ->helperText(function () {
                                        /** @var \App\Models\User|null $user */
                                        $user = \Illuminate\Support\Facades\Auth::user();
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
                                \Filament\Forms\Components\Select::make('anggaran_role')
                                    ->label('Peran Anggaran (PA/PPK/PPTK)')
                                    ->options(\App\Enums\AnggaranRole::class)
                                    ->placeholder('Bukan Pejabat Anggaran (Staf / JF)')
                                    ->searchable(),
                                \Filament\Forms\Components\Select::make('atasan_id')
                                    ->label('Atasan Langsung')
                                    ->relationship('atasan', 'name', fn($query) => $query->bukanSuperAdmin())
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

                Group::make()
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

                        Section::make('Keamanan & Kata Sandi')
                            ->icon('heroicon-o-key')
                            ->description('Atur atau perbarui password akun pengguna.')
                            ->schema([
                                \Filament\Forms\Components\TextInput::make('password')
                                    ->label('Password Baru')
                                    ->password()
                                    ->placeholder('Kosongkan jika tidak diubah...')
                                    ->revealable()
                                    ->formatStateUsing(fn() => '')
                                    ->autocomplete('new-password')
                                    ->extraInputAttributes([
                                        'autocomplete' => 'new-password',
                                        'data-lpignore' => 'true',
                                    ])
                                    ->required(fn(string $operation): bool => $operation === 'create')
                                    ->rule(fn(string $operation) => $operation === 'create' ? 'required|min:6' : 'nullable|min:6')
                                    ->maxLength(255)
                                    ->dehydrateStateUsing(fn(?string $state): ?string => filled($state) ? \Illuminate\Support\Facades\Hash::make($state) : null)
                                    ->dehydrated(fn(?string $state) => filled($state))
                                    ->helperText('Kosongkan jika tidak ingin merubah password.'),

                                \Filament\Forms\Components\TextInput::make('password_confirmation')
                                    ->label('Konfirmasi Password')
                                    ->password()
                                    ->placeholder('Ketik ulang password baru...')
                                    ->revealable()
                                    ->formatStateUsing(fn() => '')
                                    ->autocomplete('new-password')
                                    ->extraInputAttributes([
                                        'autocomplete' => 'new-password',
                                        'data-lpignore' => 'true',
                                    ])
                                    ->required(fn(string $operation, callable $get): bool => $operation === 'create' || (filled($get('password')) && $get('password') !== ''))
                                    ->same('password')
                                    ->visible(fn(string $operation, callable $get): bool => $operation === 'create' || filled($get('password')))
                                    ->maxLength(255)
                                    ->dehydrated(false)
                                    ->helperText('Wajib sama persis dengan password baru di atas.'),
                            ])->columns(1),

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
