<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\AnggaranRole;
use App\Enums\Eselon;
use App\Enums\GolonganRuang;
use App\Enums\JabatanTimKreatif;
use App\Enums\JenisKelamin;
use App\Enums\JenjangPendidikan;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
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
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
                                                ->hidden(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => self::isKontrak($get('status_kepegawaian'), $get('roles')))
                                                ->maxLength(30),
                                        ]),

                                    Grid::make([
                                        'default' => 1,
                                        'md' => 2,
                                    ])
                                        ->schema([
                                            TextInput::make('gelar_depan')
                                                ->label('Gelar Depan')
                                                ->placeholder('Contoh: Dr., Ir., Drs.')
                                                ->maxLength(50),

                                            TextInput::make('gelar_belakang')
                                                ->label('Gelar Belakang')
                                                ->placeholder('Contoh: S.IP, S.E., S.Kom')
                                                ->maxLength(100),
                                        ]),

                                    Grid::make([
                                        'default' => 1,
                                        'md' => 2,
                                    ])
                                        ->schema([
                                            TextInput::make('email')
                                                ->label('Email User')
                                                ->email()
                                                ->copyable()
                                                ->placeholder('Contoh: user@gmail.com')
                                                ->required()
                                                ->unique(User::class, 'email', ignoreRecord: true)
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
                                        Section::make('Identitas Personal')
                                            ->icon('heroicon-o-identification')
                                            ->columns(2)
                                            ->schema([
                                                Select::make('jenis_kelamin')
                                                    ->label('Jenis Kelamin')
                                                    ->options(JenisKelamin::class),

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

                                                TextInput::make('tempat_lahir')
                                                    ->label('Tempat Lahir')
                                                    ->placeholder('Kota/Kab tempat lahir...')
                                                    ->maxLength(100),

                                                DatePicker::make('tanggal_lahir')
                                                    ->label('Tanggal Lahir')
                                                    ->displayFormat('d/m/Y'),
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

                                Section::make('Nomor Administrasi Kepegawaian & Jaminan Sosial')
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

                        // Tab 2: Pangkat / Golongan (MyASN Style)
                        Tab::make('Pangkat / Golongan')
                            ->icon('heroicon-m-shield-check')
                            ->disabled(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => self::isKontrak($get('status_kepegawaian'), $get('roles')))
                            ->extraAttributes(fn (\Filament\Schemas\Components\Utilities\Get $get): array => self::disabledTabAttributes($get('status_kepegawaian'), $get('roles')))
                            ->schema([
                                Section::make('Tingkat Eselon Jabatan')
                                    ->icon('heroicon-o-chart-bar')
                                    ->schema([
                                        Select::make('eselon')
                                            ->label('Tingkat Eselon')
                                            ->options(Eselon::class)
                                            ->placeholder('Non Eselon'),
                                    ]),
                            ]),

                        // Tab 3: Jabatan & Penugasan (MyASN Style)
                        Tab::make('Jabatan')
                            ->icon('heroicon-m-briefcase')
                            ->disabled(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => self::isKontrak($get('status_kepegawaian'), $get('roles')))
                            ->extraAttributes(fn (\Filament\Schemas\Components\Utilities\Get $get): array => self::disabledTabAttributes($get('status_kepegawaian'), $get('roles')))
                            ->schema([
                                Grid::make([
                                    'default' => 1,
                                    'lg' => 2,
                                ])
                                    ->schema([
                                        Section::make('Informasi Jabatan & Unit Kerja Saat Ini')
                                            ->icon('heroicon-o-briefcase')
                                            ->columns(2)
                                            ->schema([
                                                Select::make('status_kepegawaian')
                                                    ->label('Status Kepegawaian')
                                                    ->options(\App\Enums\StatusKepegawaian::class)
                                                    ->default(\App\Enums\StatusKepegawaian::PNS)
                                                    ->live()
                                                    ->disabled(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => self::isKontrak(null, $get('roles')))
                                                    ->dehydrated()
                                                    ->required(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => ! self::isKontrak(null, $get('roles')))
                                                    ->columnSpanFull(),

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
                                                    ->default(fn () => User::query()
                                                        ->whereHas('jabatan', fn ($q) => $q->where('nama', 'like', '%Kepala Dinas%'))
                                                        ->orWhereHas('roles', fn ($q) => $q->whereIn('name', ['pa', 'kadis']))
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
                                                            ->whereHas('jabatan', fn ($q) => $q->where('nama', 'like', '%Kepala Dinas%'))
                                                            ->orWhereHas('roles', fn ($q) => $q->whereIn('name', ['pa', 'kadis']))
                                                            ->value('id');

                                                        if ($kadisId && $kadisId !== $record?->id) {
                                                            $component->state($kadisId);
                                                        }
                                                    })
                                                    ->searchable()
                                                    ->preload()
                                                    ->placeholder('Kosongkan jika Kepala Dinas')
                                                    ->helperText('Otomatis mengarah ke Kepala Dinas jika tidak diubah. Kosongkan khusus untuk Kepala Dinas.')
                                                    ->columnSpanFull(),

                                                Select::make('anggaran_role')
                                                    ->label('Peran Anggaran (PA/PPK/PPTK)')
                                                    ->options(AnggaranRole::class)
                                                    ->placeholder('Bukan Pejabat Anggaran (Staf / JF)')
                                                    ->searchable()
                                                    ->columnSpanFull(),

                                                Select::make('jabatan_tim_kreatif')
                                                    ->label('Jabatan Tim Kreatif Dinas')
                                                    ->options(JabatanTimKreatif::class)
                                                    ->placeholder('Bukan Anggota Tim Kreatif')
                                                    ->searchable()
                                                    ->columnSpanFull(),

                                                Toggle::make('is_pengelola_aset')
                                                    ->label('Pengelola Aset / Pengurus Barang')
                                                    ->helperText('Pegawai ini berhak menambah, mengubah, dan menghapus data Buku Inventaris (Role tetap Pegawai).')
                                                    ->default(false)
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

                                                TextInput::make('kuota_cuti')
                                                    ->label('Kuota Cuti Tahunan (Hari)')
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
                                                    })
                                                    ->helperText('Diinput manual sesuai rekapitulasi cuti tahunan oleh Super Admin atau Admin Kepegawaian.'),
                                            ]),
                                    ]),
                            ]),

                        // Tab 4: Pendidikan
                        Tab::make('Pendidikan')
                            ->icon('heroicon-m-academic-cap')
                            ->disabled(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => self::isKontrak($get('status_kepegawaian'), $get('roles')))
                            ->extraAttributes(fn (\Filament\Schemas\Components\Utilities\Get $get): array => self::disabledTabAttributes($get('status_kepegawaian'), $get('roles')))
                            ->schema([

                                Section::make('Sertifikat, Diklat & Penghargaan')
                                    ->description('Unggah sertifikat pelatihan, bimtek, kompetensi, atau penghargaan pegawai (Wajib PDF, Maks. 2 MB).')
                                    ->icon('heroicon-o-document-check')
                                    ->schema([
                                        Repeater::make('riwayatSertifikat')
                                            ->relationship('riwayatSertifikat')
                                            ->label('Daftar Sertifikat & Diklat')
                                            ->addActionLabel('+ Tambah Sertifikat / Diklat')
                                            ->defaultItems(0)
                                            ->collapsible()
                                            ->itemLabel(fn(array $state): ?string => filled($state['nama_sertifikat'] ?? null)
                                                ? $state['nama_sertifikat']
                                                : 'Sertifikat / Diklat')
                                            ->schema([
                                                Grid::make([
                                                    'default' => 1,
                                                    'md' => 3,
                                                ])
                                                    ->schema([
                                                        TextInput::make('nama_sertifikat')
                                                            ->label('Nama Sertifikat / Diklat')
                                                            ->placeholder('Contoh: Diklat Kepemimpinan Tk. IV')
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->columnSpan(2),

                                                        Select::make('jenis')
                                                            ->label('Jenis')
                                                            ->options([
                                                                'sertifikat' => 'Sertifikat Kompetensi',
                                                                'diklat' => 'Diklat',
                                                                'bimtek' => 'Bimtek / Workshop',
                                                                'penghargaan' => 'Penghargaan',
                                                                'lainnya' => 'Lainnya',
                                                            ])
                                                            ->default('sertifikat')
                                                            ->required(),

                                                        TextInput::make('penyelenggara')
                                                            ->label('Lembaga Penyelenggara')
                                                            ->placeholder('Contoh: BPSDM / LAN RI')
                                                            ->maxLength(255),

                                                        TextInput::make('nomor_sertifikat')
                                                            ->label('Nomor Sertifikat')
                                                            ->placeholder('Contoh: 001/DIKLAT/2026')
                                                            ->distinct()
                                                            ->maxLength(120),

                                                        DatePicker::make('tanggal_terbit')
                                                            ->label('Tanggal Terbit')
                                                            ->displayFormat('d/m/Y'),

                                                        FileUpload::make('file_sertifikat')
                                                            ->label('Berkas Sertifikat (Wajib PDF, Maks. 2 MB)')
                                                            ->acceptedFileTypes(['application/pdf'])
                                                            ->rules(['file', 'mimes:pdf', 'max:2048'])
                                                            ->maxSize(2048)
                                                            ->required()
                                                            ->disk('public')
                                                            ->directory(fn($livewire): string => isset($livewire->record?->id) ? "dokumen_pegawai/{$livewire->record->id}/sertifikat" : 'dokumen_sertifikat')
                                                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                                                                return (string) str('sertifikat-' . str()->random(16) . '.pdf');
                                                            })
                                                            ->downloadable()
                                                            ->openable()
                                                            ->columnSpanFull()
                                                            ->helperText('Wajib unggah berkas sertifikat berformat PDF (Maksimal 2 MB).'),
                                                    ]),
                                            ]),
                                    ]),
                            ]),

                        // Tab 5: Keluarga
                        Tab::make('Keluarga')
                            ->icon('heroicon-m-user-group')
                            ->disabled(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => self::isKontrak($get('status_kepegawaian'), $get('roles')))
                            ->extraAttributes(fn (\Filament\Schemas\Components\Utilities\Get $get): array => self::disabledTabAttributes($get('status_kepegawaian'), $get('roles')))
                            ->schema([
                                Section::make('Data Administrasi Kependudukan & Keluarga')
                                    ->icon('heroicon-o-user-group')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('no_kk')
                                            ->label('Nomor Kartu Keluarga (KK)')
                                            ->mask('9999999999999999')
                                            ->placeholder('16 digit No. KK...')
                                            ->maxLength(20)
                                            ->numeric()
                                            ->helperText('Nomor Kartu Keluarga resmi yang tercatat di Dinas Kependudukan dan Pencatatan Sipil.'),

                                        TextInput::make('nik')
                                            ->label('Nomor Induk Kependudukan (NIK)')
                                            ->mask('9999999999999999')
                                            ->placeholder('16 digit NIK...')
                                            ->maxLength(20)
                                            ->numeric()
                                            ->helperText('Nomor Induk Kependudukan resmi pegawai yang tercantum pada KTP / KK.'),
                                    ]),
                            ]),

                        Tab::make('Akun & Keamanan')
                            ->icon('heroicon-m-key')
                            ->schema([
                                Section::make('Hak Akses & Status Akun')
                                    ->icon('heroicon-o-user-circle')
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('aktif')
                                            ->label('Status Akun Aktif')
                                            ->default(true)
                                            ->inline(false),

                                        Select::make('roles')
                                            ->label('Hak Akses (Role)')
                                            ->relationship('roles', 'name', function ($query) {
                                                /** @var \App\Models\User|null $user */
                                                $user = Auth::user();
                                                return $user?->hasRole('super_admin') ? $query : $query->where('name', '!=', 'super_admin');
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
                                            ->afterStateUpdated(function ($state, \Filament\Schemas\Components\Utilities\Set $set): void {
                                                if (self::isKontrak(null, $state)) {
                                                    $set('status_kepegawaian', \App\Enums\StatusKepegawaian::KONTRAK->value);
                                                }
                                            })
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    private static function isKontrak(mixed $status, mixed $roles = null): bool
    {
        $value = $status instanceof \App\Enums\StatusKepegawaian
            ? $status->value
            : (string) $status;

        if ($value === \App\Enums\StatusKepegawaian::KONTRAK->value) {
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

    /**
     * @return array<string, mixed>
     */
    private static function disabledTabAttributes(mixed $status, mixed $roles = null): array
    {
        if (! self::isKontrak($status, $roles)) {
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
