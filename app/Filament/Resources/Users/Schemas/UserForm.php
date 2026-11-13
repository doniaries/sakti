<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\AnggaranRole;
use App\Enums\Eselon;
use App\Enums\GolonganRuang;
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
                                    ->disk('public')
                                    ->directory('avatars')
                                    ->maxSize(2048)
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
                                                ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? mb_strtoupper($state, 'UTF-8') : null)
                                                ->required()
                                                ->maxLength(255),

                                            TextInput::make('nip')
                                                ->label('NIP')
                                                ->mask('99999999 999999 9 999')
                                                ->placeholder('Contoh: 19720407 199202 1 002')
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
                                                ->label('Email Dinas / Login')
                                                ->email()
                                                ->copyable()
                                                ->placeholder('Contoh: user@sijunjung.go.id')
                                                ->required()
                                                ->unique(User::class, 'email', ignoreRecord: true)
                                                ->maxLength(255),

                                            TextInput::make('password')
                                                ->label('Password Login')
                                                ->password()
                                                ->revealable()
                                                ->placeholder(fn (string $operation): string => $operation === 'create' ? 'Minimal 6 karakter...' : 'Kosongkan jika tidak diubah...')
                                                ->formatStateUsing(fn () => '')
                                                ->autocomplete('new-password')
                                                ->extraInputAttributes([
                                                    'autocomplete' => 'new-password',
                                                    'data-lpignore' => 'true',
                                                ])
                                                ->required(fn (string $operation): bool => $operation === 'create')
                                                ->rule(fn (string $operation): string => $operation === 'create' ? 'required|min:6' : 'nullable|min:6')
                                                ->maxLength(255)
                                                ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null)
                                                ->dehydrated(fn (?string $state): bool => filled($state)),
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
                            ->schema([
                                Section::make('Tingkat Eselon Jabatan')
                                    ->icon('heroicon-o-chart-bar')
                                    ->schema([
                                        Select::make('eselon')
                                            ->label('Tingkat Eselon')
                                            ->options(Eselon::class)
                                            ->placeholder('Non Eselon'),
                                    ]),

                                Section::make('Riwayat Pangkat & Golongan Ruang')
                                    ->description('Kelola seluruh riwayat kenaikan pangkat dan golongan ruang pegawai beserta berkas scan SK Pangkat (PDF) yang dapat diunduh kembali.')
                                    ->icon('heroicon-o-academic-cap')
                                    ->schema([
                                        Repeater::make('riwayatGolongan')
                                            ->relationship('riwayatGolongan')
                                            ->label('Daftar Riwayat Pangkat / Golongan')
                                            ->addActionLabel('+ Tambah Riwayat Pangkat/Golongan')
                                            ->reorderableWithButtons()
                                            ->collapsible()
                                            ->cloneable()
                                            ->itemLabel(function (array $state): ?string {
                                                $gol = $state['golongan'] ?? '';
                                                if ($gol instanceof \App\Enums\GolonganRuang) {
                                                    $gol = $gol->value;
                                                }
                                                $pangkat = $state['nama_pangkat'] ?? '';
                                                $sk = $state['nomor_sk'] ?? '';

                                                $label = $gol ? "[$gol]" : '';
                                                if ($pangkat) {
                                                    $label .= ' ' . $pangkat;
                                                }
                                                if ($sk) {
                                                    $label .= ' (' . $sk . ')';
                                                }
                                                return $label ?: 'Riwayat Golongan';
                                            })
                                            ->schema([
                                                Grid::make([
                                                    'default' => 1,
                                                    'md' => 3,
                                                ])
                                                    ->schema([
                                                        Select::make('golongan')
                                                            ->label('Pangkat / Golongan Ruang')
                                                            ->options(GolonganRuang::class)
                                                            ->required()
                                                            ->searchable(),

                                                        TextInput::make('nama_pangkat')
                                                            ->label('Nama Pangkat')
                                                            ->placeholder('Contoh: Pembina, Penata Tingkat I')
                                                            ->maxLength(100),

                                                        DatePicker::make('tmt_golongan')
                                                            ->label('TMT Golongan')
                                                            ->displayFormat('d/m/Y')
                                                            ->required(),

                                                        TextInput::make('nomor_sk')
                                                            ->label('Nomor SK Kenaikan Pangkat')
                                                            ->placeholder('Contoh: 823.4/1136/BKD-2026')
                                                            ->maxLength(100),

                                                        DatePicker::make('tanggal_sk')
                                                            ->label('Tanggal SK')
                                                            ->displayFormat('d/m/Y'),

                                                        Toggle::make('is_golongan_terakhir')
                                                            ->label('Pangkat / Golongan Terakhir')
                                                            ->helperText('Tandai jika merupakan pangkat aktif sekarang')
                                                            ->default(false)
                                                            ->inline(false),

                                                        FileUpload::make('file_sk_golongan')
                                                            ->label('Berkas SK Pangkat (Wajib PDF, Maks. 2 MB)')
                                                            ->acceptedFileTypes(['application/pdf'])
                                                            ->rules(['file', 'mimes:pdf', 'max:2048'])
                                                            ->maxSize(2048)
                                                            // ->required()
                                                            ->disk('public')
                                                            ->directory('dokumen_golongan')
                                                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                                                                return (string) str('sk-golongan-' . str()->random(16) . '.pdf');
                                                            })
                                                            ->downloadable()
                                                            ->openable()
                                                            ->columnSpanFull()
                                                            ->helperText('Wajib unggah berkas scan PDF asli SK Kenaikan Pangkat resmi (Maksimal 2 MB).'),
                                                    ]),
                                            ]),
                                    ]),
                            ]),

                        // Tab 3: Jabatan & Penugasan (MyASN Style)
                        Tab::make('Jabatan')
                            ->icon('heroicon-m-briefcase')
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

                                Section::make('Riwayat Mutasi & Jabatan Pegawai')
                                    ->description('Kelola seluruh riwayat penugasan/jabatan struktural, fungsional, dan pelaksana beserta berkas scan SK Jabatan (PDF) yang dapat diunduh kembali.')
                                    ->icon('heroicon-o-briefcase')
                                    ->schema([
                                        Repeater::make('riwayatJabatan')
                                            ->relationship('riwayatJabatan')
                                            ->label('Daftar Riwayat Jabatan')
                                            ->addActionLabel('+ Tambah Riwayat Jabatan')
                                            ->reorderableWithButtons()
                                            ->collapsible()
                                            ->cloneable()
                                            ->itemLabel(function (array $state): ?string {
                                                $nama = $state['nama_jabatan'] ?? '';
                                                $jenis = $state['jenis_jabatan'] ?? '';
                                                $tmt = $state['tmt_jabatan'] ?? '';

                                                $label = $nama ?: 'Riwayat Jabatan';
                                                if ($jenis) {
                                                    $label .= " [$jenis]";
                                                }
                                                if ($tmt) {
                                                    $label .= ' (' . $tmt . ')';
                                                }
                                                return $label;
                                            })
                                            ->schema([
                                                Grid::make([
                                                    'default' => 1,
                                                    'md' => 3,
                                                ])
                                                    ->schema([
                                                        TextInput::make('nama_jabatan')
                                                            ->label('Nama Jabatan')
                                                            ->placeholder('Contoh: Kepala BIDANG PEMASARAN PARIWISATA')
                                                            ->required()
                                                            ->columnSpan(2),

                                                        Select::make('jenis_jabatan')
                                                            ->label('Jenis Jabatan')
                                                            ->options([
                                                                'Jabatan Struktural' => 'Jabatan Struktural',
                                                                'Jabatan Fungsional' => 'Jabatan Fungsional',
                                                                'Jabatan Pelaksana' => 'Jabatan Pelaksana',
                                                            ])
                                                            ->default('Jabatan Struktural')
                                                            ->required(),

                                                        TextInput::make('unit_kerja')
                                                            ->label('Unit Kerja / SKPD')
                                                            ->placeholder('Contoh: Dinas Pariwisata, Pemuda dan Olahraga')
                                                            ->columnSpan(2),

                                                        DatePicker::make('tmt_jabatan')
                                                            ->label('TMT Jabatan')
                                                            ->displayFormat('d/m/Y'),

                                                        TextInput::make('nomor_sk')
                                                            ->label('Nomor SK Jabatan')
                                                            ->placeholder('Nomor SK pelantikan / mutasi...')
                                                            ->maxLength(100),

                                                        DatePicker::make('tanggal_sk')
                                                            ->label('Tanggal SK')
                                                            ->displayFormat('d/m/Y'),

                                                        Toggle::make('is_jabatan_sekarang')
                                                            ->label('Jabatan Sekarang')
                                                            ->helperText('Tandai jika ini merupakan jabatan aktif saat ini')
                                                            ->default(false)
                                                            ->inline(false),

                                                        FileUpload::make('file_sk_jabatan')
                                                            ->label('Berkas SK Jabatan (Wajib PDF, Maks. 2 MB)')
                                                            ->acceptedFileTypes(['application/pdf'])
                                                            ->rules(['file', 'mimes:pdf', 'max:2048'])
                                                            ->maxSize(2048)
                                                            ->required()
                                                            ->disk('public')
                                                            ->directory('dokumen_jabatan')
                                                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                                                                return (string) str('sk-jabatan-' . str()->random(16) . '.pdf');
                                                            })
                                                            ->downloadable()
                                                            ->openable()
                                                            ->columnSpanFull()
                                                            ->helperText('Wajib unggah berkas scan PDF asli SK Jabatan / Pelantikan resmi (Maksimal 2 MB).'),
                                                    ]),
                                            ]),
                                    ]),
                            ]),

                        // Tab 4: Pendidikan
                        Tab::make('Pendidikan')
                            ->icon('heroicon-m-academic-cap')
                            ->schema([
                                Section::make('Riwayat Pendidikan Formal')
                                    ->description('Kelola seluruh riwayat pendidikan pegawai mulai dari jenjang SD, SLTP, SLTA, Diploma, S1, S2, hingga S3 beserta berkas scan Ijazah (PDF) yang dapat diunduh kembali.')
                                    ->icon('heroicon-o-academic-cap')
                                    ->schema([
                                        Repeater::make('riwayatPendidikan')
                                            ->relationship('riwayatPendidikan')
                                            ->label('Daftar Riwayat Pendidikan')
                                            ->addActionLabel('+ Tambah Riwayat Pendidikan')
                                            ->reorderableWithButtons()
                                            ->collapsible()
                                            ->cloneable()
                                            ->itemLabel(function (array $state): ?string {
                                                $jenjang = $state['jenjang'] ?? '';
                                                if ($jenjang instanceof \App\Enums\JenjangPendidikan) {
                                                    $jenjang = $jenjang->value;
                                                }
                                                $nama = $state['nama_institusi'] ?? '';
                                                $jurusan = $state['jurusan'] ?? '';
                                                $tahun = $state['tahun_lulus'] ?? '';

                                                $label = $jenjang ? "[$jenjang]" : '';
                                                if ($jurusan) {
                                                    $label .= ' ' . $jurusan;
                                                } elseif ($nama) {
                                                    $label .= ' ' . $nama;
                                                }
                                                if ($tahun) {
                                                    $label .= ' (' . $tahun . ')';
                                                }
                                                return $label ?: 'Riwayat Pendidikan';
                                            })
                                            ->schema([
                                                Grid::make([
                                                    'default' => 1,
                                                    'md' => 3,
                                                ])
                                                    ->schema([
                                                        Select::make('jenjang')
                                                            ->label('Jenjang Pendidikan')
                                                            ->options(JenjangPendidikan::class)
                                                            ->required()
                                                            ->searchable(),

                                                        TextInput::make('nama_institusi')
                                                            ->label('Nama Sekolah / Perguruan Tinggi')
                                                            ->placeholder('Contoh: SMKN 1 Sijunjung / Universitas Terbuka')
                                                            ->required()
                                                            ->maxLength(255)
                                                            ->columnSpan(2),

                                                        TextInput::make('jurusan')
                                                            ->label('Jurusan / Program Studi')
                                                            ->placeholder('Contoh: Ilmu Pemerintahan, Teknik Komputer, Umum')
                                                            ->maxLength(255),

                                                        TextInput::make('tahun_lulus')
                                                            ->label('Tahun Lulus')
                                                            ->numeric()
                                                            ->mask('9999')
                                                            ->placeholder('Contoh: 2015'),

                                                        TextInput::make('nomor_ijazah')
                                                            ->label('Nomor Seri Ijazah')
                                                            ->placeholder('Contoh: DN-08/D-SMK/06/0012345')
                                                            ->maxLength(100),

                                                        FileUpload::make('file_ijazah')
                                                            ->label('Berkas Ijazah Asli (Wajib PDF, Maks. 2 MB)')
                                                            ->acceptedFileTypes(['application/pdf'])
                                                            ->rules(['file', 'mimes:pdf', 'max:2048'])
                                                            ->maxSize(2048)
                                                            ->required()
                                                            ->disk('public')
                                                            ->directory('dokumen_pendidikan')
                                                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                                                                return (string) str('ijazah-' . str()->random(16) . '.pdf');
                                                            })
                                                            ->downloadable()
                                                            ->openable()
                                                            ->columnSpan(2)
                                                            ->helperText('Wajib unggah berkas ijazah resmi berformat PDF (Maksimal 2 MB).'),

                                                        Toggle::make('is_pendidikan_terakhir')
                                                            ->label('Pendidikan Terakhir / Diakui')
                                                            ->helperText('Tandai jika merupakan jenjang pendidikan terakhir')
                                                            ->default(false)
                                                            ->inline(false),
                                                    ]),
                                            ]),
                                    ]),
                            ]),

                        // Tab 5: Keluarga
                        Tab::make('Keluarga')
                            ->icon('heroicon-m-user-group')
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
                                        TextInput::make('email_pribadi')
                                            ->label('Email Pribadi')
                                            ->email()
                                            ->copyable()
                                            ->placeholder('Contoh: user@gmail.com')
                                            ->maxLength(255),

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
                            ]),
                    ]),
            ]);
    }
}
