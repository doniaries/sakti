<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\AnggaranRole;
use App\Enums\Eselon;
use App\Enums\GolonganRuang;
use App\Enums\JenisKelamin;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // Header Identitas Utama Pegawai (Compact & Selalu Terlihat di Atas)
                Section::make('Identitas Pegawai')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 4,
                        ])
                        ->schema([
                            FileUpload::make('avatar_url')
                                ->label('Foto Profil (Avatar)')
                                ->avatar()
                                ->image()
                                ->disk('public')
                                ->directory('avatars')
                                ->maxSize(2048)
                                ->alignCenter()
                                ->columnSpan([
                                    'default' => 1,
                                    'md' => 1,
                                ]),

                            Group::make([
                                TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->placeholder('Contoh: AHMAD FAUZI, S.KOM')
                                    ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                                    ->dehydrateStateUsing(fn(?string $state): ?string => filled($state) ? mb_strtoupper($state, 'UTF-8') : null)
                                    ->required()
                                    ->maxLength(255),

                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('gelar_depan')
                                            ->label('Gelar Depan')
                                            ->placeholder('Contoh: Dr., Ir., Drs.')
                                            ->maxLength(50),

                                        TextInput::make('gelar_belakang')
                                            ->label('Gelar Belakang')
                                            ->placeholder('Contoh: S.IP, S.E., S.Kom')
                                            ->maxLength(100),

                                        TextInput::make('nip')
                                            ->label('NIP')
                                            ->mask('99999999 999999 9 999')
                                            ->placeholder('Contoh: 19720407 199202 1 002')
                                            ->maxLength(30),
                                    ]),
                            ])
                            ->columnSpan([
                                'default' => 1,
                                'md' => 3,
                            ]),
                        ]),
                    ]),

                // Formulir Terstruktur Model Tab MyASN
                Tabs::make('FormPegawaiTabs')
                    ->persistTabInQueryString()
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
                                    Section::make('Identitas Kependudukan & Personal')
                                        ->icon('heroicon-o-identification')
                                        ->columns(2)
                                        ->schema([
                                            TextInput::make('nik')
                                                ->label('Nomor Induk Kependudukan (NIK)')
                                                ->mask('9999999999999999')
                                                ->placeholder('16 digit NIK...')
                                                ->maxLength(20)
                                                ->numeric(),

                                            Select::make('jenis_kelamin')
                                                ->label('Jenis Kelamin')
                                                ->options(JenisKelamin::class),

                                            TextInput::make('tempat_lahir')
                                                ->label('Tempat Lahir')
                                                ->placeholder('Kota/Kab tempat lahir...')
                                                ->maxLength(100),

                                            DatePicker::make('tanggal_lahir')
                                                ->label('Tanggal Lahir')
                                                ->displayFormat('d/m/Y'),

                                            Select::make('agama')
                                                ->label('Agama')
                                                ->options([
                                                    'Islam' => 'Islam',
                                                    'Kristen' => 'Kristen',
                                                    'Katolik' => 'Katolik',
                                                    'Hindu' => 'Hindu',
                                                    'Buddha' => 'Buddha',
                                                    'Konghucu' => 'Konghucu',
                                                ])
                                                ->columnSpanFull(),
                                        ]),

                                    Section::make('Kontak & Domisili')
                                        ->icon('heroicon-o-map-pin')
                                        ->columns(2)
                                        ->schema([
                                            TextInput::make('no_hp')
                                                ->label('Nomor HP / WhatsApp')
                                                ->tel()
                                                ->placeholder('Contoh: 081234567890')
                                                ->helperText('Tampilan input nomor Indonesia (08...). Otomatis disimpan ke database dengan kode +62.')
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

                                            TextInput::make('no_telp')
                                                ->label('Nomor Telepon Rumah/Kantor')
                                                ->tel()
                                                ->placeholder('Nomor telepon lain...')
                                                ->maxLength(25),

                                            Textarea::make('alamat')
                                                ->label('Alamat Domisili / Tempat Tinggal')
                                                ->placeholder('Alamat lengkap domisili...')
                                                ->rows(3)
                                                ->columnSpanFull(),
                                        ]),
                                ]),
                            ]),

                        // Tab 2: Golongan & Pangkat
                        Tab::make('Golongan')
                            ->icon('heroicon-m-shield-check')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'lg' => 2,
                                ])
                                ->schema([
                                    Section::make('Kepangkatan & Golongan Ruang')
                                        ->icon('heroicon-o-academic-cap')
                                        ->columns(2)
                                        ->schema([
                                            Select::make('pangkat_golongan')
                                                ->label('Pangkat / Golongan Ruang')
                                                ->options(GolonganRuang::class)
                                                ->searchable()
                                                ->columnSpanFull(),

                                            DatePicker::make('tmt_pangkat')
                                                ->label('TMT Golongan / Pangkat')
                                                ->displayFormat('d/m/Y'),

                                            Select::make('eselon')
                                                ->label('Eselon')
                                                ->options(Eselon::class)
                                                ->placeholder('Non Eselon'),
                                        ]),

                                    Section::make('Nomor Administrasi & Jaminan Sosial')
                                        ->icon('heroicon-o-document-text')
                                        ->columns(2)
                                        ->schema([
                                            TextInput::make('npwp')
                                                ->label('Nomor NPWP')
                                                ->placeholder('Contoh: 15.988.184.6-203.000')
                                                ->mask('99.999.999.9-999.999')
                                                ->helperText('Format standar: 99.999.999.9-999.999')
                                                ->maxLength(30),

                                            DatePicker::make('tanggal_npwp')
                                                ->label('Tanggal NPWP')
                                                ->displayFormat('d/m/Y'),

                                            TextInput::make('no_bpjs')
                                                ->label('Nomor BPJS Kesehatan')
                                                ->placeholder('Nomor Kartu BPJS...')
                                                ->maxLength(30),

                                            TextInput::make('no_taspen')
                                                ->label('Nomor Taspen')
                                                ->placeholder('Nomor Kartu Taspen...')
                                                ->maxLength(30),
                                        ]),
                                ]),
                            ]),

                        // Tab 3: Jabatan & Penugasan
                        Tab::make('Jabatan')
                            ->icon('heroicon-m-briefcase')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'lg' => 2,
                                ])
                                ->schema([
                                    Section::make('Informasi Jabatan & Unit Kerja')
                                        ->icon('heroicon-o-briefcase')
                                        ->columns(2)
                                        ->schema([
                                            Select::make('bidang_id')
                                                ->label('Bidang')
                                                ->relationship('bidang', 'nama')
                                                ->searchable()
                                                ->preload()
                                                ->placeholder('Kosongkan jika Kepala Dinas (Pimpinan Tertinggi)')
                                                ->helperText('Kepala Dinas tidak memiliki bidang karena memimpin seluruh dinas.')
                                                ->columnSpanFull(),

                                            Select::make('jabatan_id')
                                                ->label('Jabatan')
                                                ->relationship('jabatan', 'nama')
                                                ->searchable()
                                                ->preload()
                                                ->columnSpanFull(),

                                            Select::make('atasan_id')
                                                ->label('Atasan Langsung')
                                                ->relationship('atasan', 'name', fn($query) => $query->bukanSuperAdmin())
                                                ->searchable()
                                                ->preload()
                                                ->placeholder('Kosongkan jika Kepala Dinas')
                                                ->helperText('Kepala Dinas tidak memiliki atasan langsung di lingkungan dinas.')
                                                ->columnSpanFull(),

                                            Select::make('anggaran_role')
                                                ->label('Peran Anggaran (PA/PPK/PPTK)')
                                                ->options(AnggaranRole::class)
                                                ->placeholder('Bukan Pejabat Anggaran (Staf / JF)')
                                                ->searchable()
                                                ->columnSpanFull(),
                                        ]),

                                    Section::make('Presensi & Operasional Kerja')
                                        ->icon('heroicon-o-clock')
                                        ->columns(1)
                                        ->schema([
                                            Select::make('status_kerja')
                                                ->label('Status Kerja (WFH/WFO)')
                                                ->options([
                                                    'wfo' => 'WFO (Work From Office)',
                                                    'wfh' => 'WFH (Work From Home)',
                                                ])
                                                ->default('wfo')
                                                ->required(),

                                            Select::make('jadwal_kerja_id')
                                                ->label('Jadwal / Shift Kerja')
                                                ->relationship('jadwalKerja', 'nama')
                                                ->placeholder('Ikuti Jadwal Default Sistem')
                                                ->searchable()
                                                ->preload(),

                                            TextInput::make('nomor_absensi')
                                                ->label('ID Mesin Presensi (PIN)')
                                                ->maxLength(50)
                                                ->readOnly(function () {
                                                    /** @var \App\Models\User|null $user */
                                                    $user = Auth::user();
                                                    return !($user && ($user->hasRole('super_admin') || $user->hasRole('admin')));
                                                })
                                                ->helperText(function () {
                                                    /** @var \App\Models\User|null $user */
                                                    $user = Auth::user();
                                                    return !($user && ($user->hasRole('super_admin') || $user->hasRole('admin')))
                                                        ? 'Hanya Administrator yang dapat mengubah ID Mesin Presensi.'
                                                        : null;
                                                }),
                                        ]),
                                ]),
                            ]),

                        // Tab 4: Pendidikan
                        Tab::make('Pendidikan')
                            ->icon('heroicon-m-academic-cap')
                            ->schema([
                                Section::make('Riwayat Pendidikan Formal')
                                    ->icon('heroicon-o-academic-cap')
                                    ->columns(2)
                                    ->schema([
                                        Select::make('jenjang_pendidikan')
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

                                        TextInput::make('pendidikan')
                                            ->label('Jurusan / Nama Pendidikan')
                                            ->placeholder('Contoh: S1 Ilmu Komputer, S2 Magister Teknik...')
                                            ->maxLength(100),
                                    ]),
                            ]),

                        // Tab 5: Keluarga
                        Tab::make('Keluarga')
                            ->icon('heroicon-m-user-group')
                            ->schema([
                                Section::make('Data Administrasi Keluarga')
                                    ->icon('heroicon-o-user-group')
                                    ->columns(1)
                                    ->schema([
                                        TextInput::make('no_kk')
                                            ->label('Nomor Kartu Keluarga (KK)')
                                            ->mask('9999999999999999')
                                            ->placeholder('16 digit No. KK...')
                                            ->maxLength(20)
                                            ->numeric()
                                            ->helperText('Nomor Kartu Keluarga resmi yang tercatat di Dinas Kependudukan dan Pencatatan Sipil.'),
                                    ]),
                            ]),

                        // Tab 6: Akun & Keamanan
                        Tab::make('Akun & Keamanan')
                            ->icon('heroicon-m-key')
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'lg' => 2,
                                ])
                                ->schema([
                                    Section::make('Kredensial & Hak Akses Akun')
                                        ->icon('heroicon-o-user-circle')
                                        ->columns(2)
                                        ->schema([
                                            TextInput::make('email')
                                                ->label('Email Dinas / Akun')
                                                ->email()
                                                ->copyable()
                                                ->placeholder('Contoh: user@sijunjung.go.id')
                                                ->required()
                                                ->maxLength(255),

                                            TextInput::make('email_pribadi')
                                                ->label('Email Pribadi')
                                                ->email()
                                                ->copyable()
                                                ->placeholder('Contoh: user@gmail.com')
                                                ->maxLength(255),

                                            Toggle::make('aktif')
                                                ->label('Status Akun Aktif')
                                                ->default(true)
                                                ->inline(false)
                                                ->columnSpanFull(),

                                            Select::make('roles')
                                                ->label('Hak Akses (Role)')
                                                ->relationship('roles', 'name', function ($query) {
                                                    /** @var \App\Models\User|null $user */
                                                    $user = Auth::user();
                                                    return $user?->hasRole('super_admin') ? $query : $query->where('name', '!=', 'super_admin');
                                                })
                                                ->getOptionLabelFromRecordUsing(fn ($record) => match ($record->name) {
                                                    'super_admin' => 'Super Admin (Sistem Penuh)',
                                                    'admin' => 'Admin Dinas (Kasubag Umum / Kepegawaian)',
                                                    'pa' => 'PA (Kepala Dinas / Pengguna Anggaran)',
                                                    'ppk' => 'PPK (Kasubag Keuangan / Pejabat Pembuat Komitmen)',
                                                    'pptk' => 'PPTK (Kepala Bidang / Sekretaris Dinas)',
                                                    'jf' => 'JF (Jabatan Fungsional / Pengusul Belanja)',
                                                    'bendahara' => 'Bendahara Pengeluaran (Uji Kas & Potong Pajak)',
                                                    'pegawai' => 'Pegawai Biasa (Presensi & LHK Pribadi)',
                                                    default => ucwords(str_replace('_', ' ', $record->name)),
                                                })
                                                ->multiple()
                                                ->preload()
                                                ->searchable()
                                                ->columnSpanFull(),
                                        ]),

                                    Section::make('Keamanan & Kata Sandi')
                                        ->icon('heroicon-o-lock-closed')
                                        ->description('Atur atau perbarui password akun pengguna. Wajib diisi 2x agar tidak terjadi kesalahan ketik.')
                                        ->columns(1)
                                        ->schema([
                                            TextInput::make('password')
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
                                                ->dehydrateStateUsing(fn(?string $state): ?string => filled($state) ? Hash::make($state) : null)
                                                ->dehydrated(fn(?string $state) => filled($state))
                                                ->helperText('Kosongkan jika tidak ingin merubah password.'),

                                            TextInput::make('password_confirmation')
                                                ->label('Ulangi Password Baru (Konfirmasi)')
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
                                                ->maxLength(255)
                                                ->dehydrated(false)
                                                ->helperText('Wajib sama persis dengan password baru di atas.'),
                                        ]),
                                ]),
                            ]),
                    ]),
            ]);
    }
}
