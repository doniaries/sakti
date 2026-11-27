<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\AnggaranRole;
use App\Enums\Eselon;
use App\Enums\JabatanTimKreatif;
use App\Enums\JenisKelamin;
use App\Enums\StatusKepegawaian;
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
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                Section::make('Identitas & Akun Pegawai')
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
                                    ->imageEditor()
                                    ->circleCropper()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->imageResizeMode('cover')
                                    ->imageResizeTargetWidth('600')
                                    ->imageResizeTargetHeight('600')
                                    ->disk('public')
                                    ->directory(fn($record): string => $record?->id ? "dokumen_pegawai/{$record->id}/avatars" : 'avatars')
                                    ->maxSize(1024)
                                    ->preserveFilenames(false)
                                    ->openable()
                                    ->downloadable()
                                    ->alignCenter()
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 1,
                                    ]),

                                Group::make([
                                    Grid::make([
                                        'default' => 1,
                                        'md' => 2,
                                    ])
                                        ->schema([
                                            TextInput::make('name')
                                                ->label('Nama Lengkap')
                                                ->placeholder('Contoh: AHMAD FAUZI, S.KOM')
                                                ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                                                ->dehydrateStateUsing(fn(?string $state): ?string => filled($state) ? mb_strtoupper($state, 'UTF-8') : null)
                                                ->required()
                                                ->maxLength(255),

                                            TextInput::make('nip')
                                                ->label('NIP')
                                                ->mask('99999999 999999 9 999')
                                                ->placeholder('Contoh: 19720407 199202 1 002')
                                                ->hidden(fn(Get $get): bool => self::isKontrak($get('status_kepegawaian'), $get('roles')))
                                                ->maxLength(30),

                                            TextInput::make('gelar_depan')
                                                ->label('Gelar Depan')
                                                ->placeholder('Contoh: Dr., Ir., Drs.')
                                                ->maxLength(50),

                                            TextInput::make('gelar_belakang')
                                                ->label('Gelar Belakang')
                                                ->placeholder('Contoh: S.IP, S.E., S.Kom')
                                                ->maxLength(100),

                                            TextInput::make('email')
                                                ->label('Email Login')
                                                ->email()
                                                ->copyable()
                                                ->placeholder('Contoh: user@gmail.com')
                                                ->required()
                                                ->unique(User::class, 'email', ignoreRecord: true)
                                                ->maxLength(255),

                                            TextInput::make('email_resmi')
                                                ->label('Email Resmi (@sijunjung.go.id)')
                                                ->email()
                                                ->copyable()
                                                ->placeholder('Contoh: doniaries@sijunjung.go.id')
                                                ->unique(User::class, 'email_resmi', ignoreRecord: true)
                                                ->dehydrateStateUsing(fn(?string $state): ?string => filled($state) ? strtolower(trim($state)) : null)
                                                ->maxLength(255),

                                            TextInput::make('password')
                                                ->label('Password Login')
                                                ->password()
                                                ->revealable()
                                                ->placeholder(fn(string $operation): string => $operation === 'create' ? 'Minimal 6 karakter...' : 'Kosongkan jika tidak diubah...')
                                                ->formatStateUsing(fn() => '')
                                                ->autocomplete('new-password')
                                                ->extraInputAttributes([
                                                    'autocomplete' => 'new-password',
                                                    'data-lpignore' => 'true',
                                                ])
                                                ->required(fn(string $operation): bool => $operation === 'create')
                                                ->minLength(6)
                                                ->maxLength(255)
                                                ->dehydrateStateUsing(fn(?string $state): ?string => filled($state) ? Hash::make($state) : null)
                                                ->dehydrated(fn(?string $state): bool => filled($state)),

                                            Select::make('roles')
                                                ->label('Role / Hak Akses')
                                                ->relationship('roles', 'name', function ($query) {
                                                    /** @var \App\Models\User|null $user */
                                                    $user = Auth::user();

                                                    return $user?->hasRole('super_admin') ? $query : $query->where('name', '!=', 'super_admin');
                                                }),

                                            Select::make('permissions')
                                                ->label('Tugas Tambahan / Akses Khusus')
                                                ->relationship('permissions', 'name')
                                                ->multiple()
                                                ->preload()
                                                ->searchable()
                                                ->helperText('Berikan akses menu tertentu (misal: kelola_agenda untuk Ajudan) tanpa mengubah Role utama.')
                                                ->visible(function (): bool {
                                                    /** @var \App\Models\User|null $user */
                                                    $user = Auth::user();

                                                    return $user?->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian']) ?? false;
                                                })
                                                ->getOptionLabelFromRecordUsing(fn($record) => match ($record->name) {
                                                    'super_admin' => 'Super Admin (Sistem Penuh)',
                                                    'admin' => 'Admin Dinas (Kasubag Umum / Kepegawaian)',
                                                    'pa' => 'PA (Kepala Dinas / Pengguna Anggaran)',
                                                    'ppk' => 'PPK (Kasubag Keuangan / Pejabat Pembuat Komitmen)',
                                                    'pptk' => 'PPTK (Kepala Bidang / Sekretaris Dinas)',
                                                    'jf' => 'JF (Jabatan Fungsional / Pengusul Belanja)',
                                                    'bendahara' => 'Bendahara Pengeluaran (Uji Kas & Potong Pajak)',
                                                    'pegawai' => 'Pegawai Biasa (Presensi & LHK Pribadi)',
                                                    'pegawai_kontrak' => 'Pegawai Kontrak (Izin, Presensi, Profil & Tim Kreatif)',
                                                    default => ucwords(str_replace('_', ' ', $record->name)),
                                                })
                                                ->multiple()
                                                ->preload()
                                                ->searchable()
                                                ->live()
                                                ->afterStateUpdated(function ($state, Set $set): void {
                                                    if (self::isKontrak(null, $state)) {
                                                        $set('status_kepegawaian', StatusKepegawaian::KONTRAK->value);
                                                    }
                                                }),

                                            Toggle::make('aktif')
                                                ->label('Status Akun Aktif')
                                                ->default(true)
                                                ->inline(false),
                                        ]),
                                ])
                                    ->columnSpan([
                                        'default' => 1,
                                        'md' => 3,
                                    ]),
                            ]),
                    ]),

                Tabs::make('FormDetailPegawaiTabs')
                    ->tabs([
                        Tab::make('Data Pribadi & Kontak')
                            ->icon('heroicon-o-user')
                            ->columns([
                                'default' => 1,
                                'md' => 3,
                            ])
                            ->schema([
                                TextInput::make('nik')
                                    ->label('NIK')
                                    ->mask('9999999999999999')
                                    ->placeholder('16 digit NIK...')
                                    ->maxLength(20)
                                    ->numeric(),

                                TextInput::make('nama_pasangan')
                                    ->label('Nama Istri / Suami')
                                    ->maxLength(255),
                                \Filament\Forms\Components\Textarea::make('nama_anak')
                                    ->label('Nama Anak')
                                    ->rows(2)
                                    ->columnSpanFull(),
                                TextInput::make('no_kk')
                                    ->label('Nomor KK')
                                    ->mask('9999999999999999')
                                    ->placeholder('16 digit No. KK...')
                                    ->maxLength(20)
                                    ->numeric(),

                                Select::make('agama')
                                    ->label('Agama')
                                    ->options([
                                        'Islam' => 'Islam',
                                        'Kristen' => 'Kristen',
                                        'Katolik' => 'Katolik',
                                        'Hindu' => 'Hindu',
                                        'Buddha' => 'Buddha',
                                        'Konghucu' => 'Konghucu',
                                    ]),

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

                                TextInput::make('no_hp')
                                    ->label('Nomor HP / WhatsApp')
                                    ->tel()
                                    ->placeholder('Contoh: 081234567890')
                                    ->helperText('Otomatis disimpan dengan format +62.')
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
                                    ->label('Nomor Telepon')
                                    ->tel()
                                    ->placeholder('Nomor telepon rumah/kantor...')
                                    ->maxLength(25),

                                Textarea::make('alamat')
                                    ->label('Alamat Domisili')
                                    ->placeholder('Alamat lengkap domisili...')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Administrasi & Jaminan')
                            ->icon('heroicon-o-document-text')
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                            ])
                            ->schema([
                                TextInput::make('npwp')
                                    ->label('Nomor NPWP')
                                    ->placeholder('Contoh: 15.988.184.6-203.000')
                                    ->mask('99.999.999.9-999.999')
                                    ->maxLength(30),

                                DatePicker::make('tanggal_npwp')
                                    ->label('Tanggal NPWP')
                                    ->displayFormat('d/m/Y'),

                                TextInput::make('no_bpjs')
                                    ->label('Nomor BPJS')
                                    ->placeholder('Nomor Kartu BPJS...')
                                    ->maxLength(30),

                                TextInput::make('no_taspen')
                                    ->label('Nomor Taspen')
                                    ->placeholder('Nomor Kartu Taspen...')
                                    ->maxLength(30),
                            ]),

                        Tab::make('Jabatan & Penugasan')
                            ->icon('heroicon-o-briefcase')
                            ->columns([
                                'default' => 1,
                                'md' => 3,
                            ])
                            ->schema([
                                Select::make('status_kepegawaian')
                                    ->label('Status Kepegawaian')
                                    ->options(StatusKepegawaian::class)
                                    ->default(StatusKepegawaian::PNS)
                                    ->live()
                                    ->disabled(fn(Get $get): bool => self::isKontrak(null, $get('roles')))
                                    ->dehydrated()
                                    ->required(fn(Get $get): bool => ! self::isKontrak(null, $get('roles'))),

                                Select::make('bidang_id')
                                    ->label('Bidang')
                                    ->relationship('bidang', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Kosongkan jika Kepala Dinas'),

                                Select::make('jabatan_id')
                                    ->label('Jabatan Saat Ini')
                                    ->relationship('jabatan', 'nama')
                                    ->searchable()
                                    ->preload(),

                                Select::make('eselon')
                                    ->label('Eselon Jabatan')
                                    ->options(Eselon::class)
                                    ->placeholder('Non Eselon')
                                    ->hidden(fn(Get $get): bool => self::isKontrak($get('status_kepegawaian'), $get('roles'))),

                                Select::make('atasan_id')
                                    ->label('Atasan Langsung')
                                    ->relationship('atasan', 'name', fn($query) => $query->bukanSuperAdmin()->orderBy('id'))
                                    ->default(fn() => User::query()
                                        ->whereHas('jabatan', fn($q) => $q->where('nama', 'like', '%Kepala Dinas%'))
                                        ->orWhereHas('roles', fn($q) => $q->whereIn('name', ['pa', 'kadis']))
                                        ->value('id'))
                                    ->afterStateHydrated(function (Select $component, $state, ?User $record): void {
                                        if (filled($state)) {
                                            return;
                                        }

                                        $isKadis = $record && (
                                            $record->hasAnyRole(['pa', 'kadis'])
                                            || str_contains(strtolower((string) ($record->jabatan?->nama ?? '')), 'kepala dinas')
                                        );

                                        if ($isKadis) {
                                            return;
                                        }

                                        $kadisId = User::query()
                                            ->whereHas('jabatan', fn($q) => $q->where('nama', 'like', '%Kepala Dinas%'))
                                            ->orWhereHas('roles', fn($q) => $q->whereIn('name', ['pa', 'kadis']))
                                            ->value('id');

                                        if ($kadisId && $kadisId !== $record?->id) {
                                            $component->state($kadisId);
                                        }
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Kosongkan jika Kepala Dinas'),

                                Select::make('anggaran_role')
                                    ->label('Peran Anggaran (PA/PPK/PPTK)')
                                    ->options(AnggaranRole::class)
                                    ->placeholder('Bukan Pejabat Anggaran')
                                    ->searchable(),

                                Select::make('jabatan_tim_kreatif')
                                    ->label('Tim Kreatif Dinas')
                                    ->options(JabatanTimKreatif::class)
                                    ->placeholder('Bukan Anggota Tim Kreatif')
                                    ->searchable(),

                                Toggle::make('is_pengelola_aset')
                                    ->label('Pengelola Aset / Pengurus Barang')
                                    ->default(false)
                                    ->inline(false),
                            ]),

                        Tab::make('Jadwal Kerja & Cuti')
                            ->icon('heroicon-o-clock')
                            ->columns([
                                'default' => 1,
                                'md' => 3,
                            ])
                            ->schema([
                                Select::make('status_kerja')
                                    ->label('Tipe Kerja')
                                    ->options([
                                        'wfo' => 'WFO (Work From Office)',
                                        'wfh' => 'WFH (Work From Home)',
                                    ])
                                    ->default('wfo')
                                    ->required(),

                                Select::make('jadwal_kerja_id')
                                    ->label('Shift / Jadwal Kerja')
                                    ->relationship('jadwalKerja', 'nama')
                                    ->placeholder('Jadwal Default Sistem')
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('kuota_cuti')
                                    ->label('Kuota Cuti Tahunan')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(365)
                                    ->default(12)
                                    ->suffix('Hari')
                                    ->disabled(function () {
                                        /** @var \App\Models\User|null $user */
                                        $user = Auth::user();

                                        return ! ($user?->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian']) ?? false);
                                    })
                                    ->dehydrated(function () {
                                        /** @var \App\Models\User|null $user */
                                        $user = Auth::user();

                                        return $user?->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian']) ?? false;
                                    }),
                            ]),
                    ]),
            ]);
    }

    private static function isKontrak(mixed $status, mixed $roles = null): bool
    {
        $value = $status instanceof StatusKepegawaian
            ? $status->value
            : (string) $status;

        if ($value === StatusKepegawaian::KONTRAK->value) {
            return true;
        }

        if (is_array($roles) && ! empty($roles)) {
            static $kontrakRoleId = null;
            if ($kontrakRoleId === null) {
                $kontrakRoleId = \Spatie\Permission\Models\Role::query()
                    ->where('name', 'pegawai_kontrak')
                    ->value('id') ?? 0;
            }

            if ($kontrakRoleId > 0 && in_array($kontrakRoleId, array_map('intval', $roles), true)) {
                return true;
            }

            if (in_array('pegawai_kontrak', $roles, true)) {
                return true;
            }
        }

        return false;
    }
}
