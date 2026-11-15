<?php

declare(strict_types=1);

namespace App\Filament\Resources\SuratTugas\Schemas;

use App\Models\Instansi;
use App\Models\SuratTugas;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class SuratTugasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Informasi Kegiatan & Surat Tugas')
                    ->description('Isi informasi kegiatan dan tim yang bertugas. Format surat mengikuti template baku dinas.')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])
                            ->schema([
                                TextInput::make('nomor_surat')
                                    ->label('Nomor Surat Tugas')
                                    ->default(fn (): string => SuratTugas::generateNomorSurat())
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(100),

                                Select::make('penandatangan_id')
                                    ->label('Pejabat Penandatangan (TTE)')
                                    ->relationship('penandatangan', 'name', fn (Builder $query) => $query->bukanSuperAdmin())
                                    ->default(fn (): ?int => Instansi::query()->value('kepala_id'))
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Default Kepala Dinas. Jika memilih Sekretaris/Kabid, format otomatis memakai a.n. Kepala Dinas.')
                                    ->required(),
                            ]),

                        TextInput::make('nama_kegiatan')
                            ->label('Nama / Perihal Kegiatan')
                            ->placeholder('Contoh: Peliputan dan Publikasi Festival Geopark Ranah Minang')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Grid::make([
                            'default' => 1,
                            'md' => 3,
                        ])
                            ->schema([
                                DatePicker::make('tanggal_mulai')
                                    ->label('Tanggal Mulai')
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->default(now())
                                    ->required(),

                                DatePicker::make('tanggal_selesai')
                                    ->label('Tanggal Selesai')
                                    ->displayFormat('d/m/Y')
                                    ->native(false)
                                    ->default(now())
                                    ->afterOrEqual('tanggal_mulai')
                                    ->required(),

                                TextInput::make('lokasi')
                                    ->label('Lokasi / Tempat Kegiatan')
                                    ->placeholder('Contoh: Kawasan Wisata Geopark Silokek')
                                    ->maxLength(255),
                            ]),

                        Textarea::make('dasar_surat')
                            ->label('Dasar Pelaksanaan')
                            ->default(fn (): string => SuratTugas::defaultDasarSurat())
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('keterangan')
                            ->label('Poin Tambahan / Catatan ("Untuk" poin 2)')
                            ->placeholder('Kosongkan untuk teks standar: Melaksanakan tugas dengan penuh tanggung jawab dan melaporkan hasilnya kepada Pimpinan.')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Dokumen Resmi SRIKANDI (TTE)')
                    ->description('Upload file PDF hasil TTE dari aplikasi SRIKANDI agar otomatis terbaca oleh Tim Kreatif.')
                    ->icon('heroicon-o-document-check')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])
                            ->schema([
                                Select::make('sumber')
                                    ->label('Alur Pembuatan')
                                    ->options([
                                        'sakti' => 'Draft dari SAKTI (lalu TTE di SRIKANDI)',
                                        'srikandi' => 'Langsung dari SRIKANDI (Upload PDF TTE)',
                                    ])
                                    ->default('sakti')
                                    ->required(),

                                Select::make('status')
                                    ->label('Status Surat')
                                    ->options([
                                        'draft' => 'Draft (Menunggu TTE SRIKANDI)',
                                        'terbit' => 'Resmi (Sudah TTE SRIKANDI)',
                                    ])
                                    ->default('draft')
                                    ->disabled()
                                    ->dehydrated(),
                            ]),

                        FileUpload::make('file_tte')
                            ->label('File PDF Surat Tugas (TTE SRIKANDI)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->disk('public')
                            ->directory('surat_tugas/tte')
                            ->maxSize(5120)
                            ->openable()
                            ->downloadable()
                            ->helperText('Jika file PDF TTE sudah di-upload, status otomatis menjadi Resmi dan dikirim ke kotak masuk tim yang bertugas.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Tim yang Bertugas')
                    ->icon('heroicon-o-user-group')
                    ->schema([
                        Toggle::make('hanya_tim_kreatif')
                            ->label('Filter Hanya Anggota Tim Kreatif Dinas')
                            ->default(true)
                            ->dehydrated(false)
                            ->live(),

                        Select::make('pegawais')
                            ->label('Pilih Pegawai / Anggota Tim')
                            ->relationship(
                                name: 'pegawais',
                                titleAttribute: 'name',
                                modifyQueryUsing: function (Builder $query, Get $get): Builder {
                                    $query->bukanSuperAdmin()->where('aktif', true);

                                    if ($get('hanya_tim_kreatif')) {
                                        $query->whereNotNull('jabatan_tim_kreatif');
                                    }

                                    return $query->orderBy('name');
                                }
                            )
                            ->getOptionLabelFromRecordUsing(function (User $record): string {
                                $jabatanTim = $record->jabatan_tim_kreatif?->getLabel();

                                return $jabatanTim
                                    ? "{$record->name} — [{$jabatanTim}]"
                                    : $record->name;
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
