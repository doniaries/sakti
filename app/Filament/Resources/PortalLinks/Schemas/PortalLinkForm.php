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
use Wallacemartinss\FilamentIconPicker\Forms\Components\IconPickerField;

class PortalLinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Aplikasi & Tautan Website')
                    ->description('Isi data aplikasi atau tautan website yang ingin ditampilkan pada halaman beranda portal.')
                    ->icon('heroicon-o-globe-alt')
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
                                TextInput::make('judul')
                                    ->label('Nama Aplikasi / Website')
                                    ->placeholder('Contoh: Website Resmi Disparpora / SIMDA Keuangan')
                                    ->required()
                                    ->maxLength(100),

                                TextInput::make('url')
                                    ->label('Alamat URL / Tautan Web')
                                    ->placeholder('Contoh: https://disparpora.sijunjung.go.id atau /mobile')
                                    ->helperText('Tautan web diawali https://, atau rute internal seperti /mobile atau /admin/...')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Textarea::make('deskripsi')
                            ->label('Keterangan Singkat')
                            ->placeholder('Jelaskan fungsi, layanan, atau isi singkat dari website/aplikasi ini...')
                            ->rows(2)
                            ->columnSpanFull(),

                        Grid::make(['default' => 1, 'md' => 3])
                            ->schema([
                                Select::make('kategori')
                                    ->label('Kategori Layanan')
                                    ->options([
                                        'eksternal' => 'Website / Layanan Eksternal',
                                        'kepegawaian' => 'Kepegawaian & Presensi',
                                        'kinerja' => 'Kinerja & LHK',
                                        'keuangan' => 'Keuangan & SPJ',
                                        'administrasi' => 'Administrasi & Panel',
                                        'panduan' => 'Panduan & Regulasi',
                                        'lainnya' => 'Lainnya',
                                    ])
                                    ->default('eksternal')
                                    ->required(),

                                Select::make('warna')
                                    ->label('Warna Kartu')
                                    ->options([
                                        'sky' => 'Biru Langit (Sky Blue)',
                                        'emerald' => 'Hijau Zamrud (Emerald)',
                                        'teal' => 'Toska Bahari (Teal)',
                                        'amber' => 'Kuning Emas / Oranye (Amber)',
                                        'indigo' => 'Ungu Indigo (Indigo)',
                                        'purple' => 'Ungu Lembayung (Purple)',
                                        'rose' => 'Merah Mawar (Rose)',
                                        'slate' => 'Abu Logam (Slate)',
                                    ])
                                    ->default('sky')
                                    ->required(),

                                Select::make('target')
                                    ->label('Buka Tautan Di')
                                    ->options([
                                        '_blank' => 'Tab Baru (Website Luar)',
                                        '_self' => 'Halaman Ini (Internal Sistem)',
                                    ])
                                    ->default('_blank')
                                    ->required(),
                            ]),

                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
                                IconPickerField::make('icon')
                                    ->label('Pilih Ikon Visual (Opsional)')
                                    ->placeholder('Klik untuk memilih ikon visual...')
                                    ->helperText('Pilih ikon visual dengan pencarian interaktif. Jika dikosongkan, sistem memilih otomatis.')
                                    ->nullable(),

                                TextInput::make('badge')
                                    ->label('Label Tag / Badge (Opsional)')
                                    ->placeholder('Contoh: Website Resmi / E-Kinerja')
                                    ->maxLength(40),
                            ]),

                        Grid::make(['default' => 1, 'md' => 2])
                            ->schema([
                                TextInput::make('urutan')
                                    ->label('Nomor Urut Tampil')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Angka lebih kecil tampil lebih awal di halaman depan.'),

                                Toggle::make('is_aktif')
                                    ->label('Tampilkan di Halaman Depan')
                                    ->default(true)
                                    ->inline(false)
                                    ->helperText('Aktifkan agar kartu muncul di portal depan.'),
                            ]),

                        FileUpload::make('lampiran_path')
                            ->label('Upload Berkas / Lampiran PDF (Opsional)')
                            ->directory('portal-lampiran')
                            ->acceptedFileTypes(['application/pdf', 'image/*'])
                            ->maxSize(15360)
                            ->helperText('Opsional. Unggah jika ada file PDF SK / petunjuk teknis yang dapat diunduh langsung pengunjung.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
