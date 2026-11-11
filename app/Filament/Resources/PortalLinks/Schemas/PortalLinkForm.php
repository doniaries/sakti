<?php

namespace App\Filament\Resources\PortalLinks\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PortalLinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Fitur & Kartu Portal')
                    ->description('Kelola judul, deskripsi, tujuan tautan, serta badge informasi pada kartu portal depan.')
                    ->icon('heroicon-o-identification')
                    ->compact()
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
                                TextInput::make('judul')
                                    ->label('Nama Fitur / Judul Kartu')
                                    ->placeholder('Contoh: Administrasi Keuangan & SPJ')
                                    ->required()
                                    ->maxLength(100),

                                TextInput::make('badge')
                                    ->label('Teks Badge / Tag (Opsional)')
                                    ->placeholder('Contoh: SPJ & PPN / MyASN Mobile')
                                    ->maxLength(50),
                            ]),

                        Textarea::make('deskripsi')
                            ->label('Deskripsi Singkat')
                            ->placeholder('Jelaskan fungsi atau panduan singkat fitur ini...')
                            ->rows(3)
                            ->columnSpanFull(),

                        Grid::make(['default' => 1, 'md' => 3])
                            ->schema([
                                TextInput::make('url')
                                    ->label('Tautan Tujuan (URL / Rute)')
                                    ->placeholder('Contoh: /admin/pengajuan-spjs atau https://...')
                                    ->helperText('Dapat berupa path internal (/admin/...) atau tautan web eksternal (https://...)')
                                    ->maxLength(255),

                                Select::make('target')
                                    ->label('Buka Tautan Di')
                                    ->options([
                                        '_self' => 'Tab Saat Ini (_self)',
                                        '_blank' => 'Tab Baru (_blank)',
                                    ])
                                    ->default('_self')
                                    ->required(),

                                Select::make('kategori')
                                    ->label('Kategori')
                                    ->options([
                                        'keuangan' => 'Keuangan & SPJ',
                                        'kepegawaian' => 'Kepegawaian & Presensi',
                                        'kinerja' => 'Kinerja & LHK',
                                        'administrasi' => 'Administrasi & Panel',
                                        'panduan' => 'Panduan & Regulasi',
                                        'eksternal' => 'Layanan Eksternal',
                                        'lainnya' => 'Lainnya',
                                    ])
                                    ->default('administrasi')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Desain & Visual Kartu (Bukan Petak)')
                    ->description('Sesuaikan bentuk kelengkungan organik dan warna aksen profesional kartu.')
                    ->icon('heroicon-o-paint-brush')
                    ->compact()
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 3])
                            ->schema([
                                Select::make('bentuk')
                                    ->label('Bentuk Kartu (Non-Petak)')
                                    ->options([
                                        'squircle' => 'Squircle Organik (Sudut Lengkung Halus)',
                                        'capsule' => 'Kapsul Elips (Pill Capsule)',
                                        'circle' => 'Orbital Lingkaran Penuh (Circle Hub)',
                                    ])
                                    ->default('squircle')
                                    ->helperText('Desain non-petak terinspirasi standar BKN ASN Digital & modern UI.')
                                    ->required(),

                                Select::make('warna')
                                    ->label('Warna Aksen Kartu')
                                    ->options([
                                        'emerald' => 'Zamrud Hijau (Emerald)',
                                        'amber' => 'Kuning Emas / Oranye (Amber)',
                                        'sky' => 'Biru Langit (Sky Blue)',
                                        'indigo' => 'Indigo Ungu Dalam (Indigo)',
                                        'rose' => 'Merah Mawar Cerah (Rose)',
                                        'teal' => 'Toska / Cyan Bahari (Teal)',
                                        'purple' => 'Ungu Lembayung (Purple)',
                                        'slate' => 'Abu Logam Elegan (Slate)',
                                    ])
                                    ->default('sky')
                                    ->required(),

                                TextInput::make('icon')
                                    ->label('Kode Heroicon')
                                    ->placeholder('heroicon-o-banknotes')
                                    ->helperText('Contoh: heroicon-o-banknotes, heroicon-o-user-group, heroicon-o-device-phone-mobile, heroicon-o-shield-check')
                                    ->default('heroicon-o-squares-2x2'),
                            ]),
                    ]),

                Section::make('Lampiran Dokumen & Pengaturan Akses')
                    ->description('Upload file lampiran opsional (PDF panduan/SK) dan pengaturan prioritas tampil.')
                    ->icon('heroicon-o-paper-clip')
                    ->compact()
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 3])
                            ->schema([
                                FileUpload::make('lampiran_path')
                                    ->label('File Lampiran (PDF / Gambar)')
                                    ->directory('portal-lampiran')
                                    ->acceptedFileTypes(['application/pdf', 'image/*'])
                                    ->maxSize(15360)
                                    ->helperText('Maks. 15 MB. Pengunjung dapat mengunduh atau membaca lampiran ini langsung dari kartu.')
                                    ->columnSpan(['default' => 1, 'md' => 2]),

                                Grid::make(1)
                                    ->schema([
                                        TextInput::make('urutan')
                                            ->label('Urutan Tampil')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Nomor lebih kecil tampil lebih awal.'),

                                        Toggle::make('is_aktif')
                                            ->label('Aktif Ditampilkan')
                                            ->default(true)
                                            ->helperText('Nonaktifkan jika fitur sedang dalam pemeliharaan.'),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}

