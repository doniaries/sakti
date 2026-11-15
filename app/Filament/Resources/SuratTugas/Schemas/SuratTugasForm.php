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
                Section::make('Detail Kegiatan & Surat Tugas')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])
                            ->schema([
                                TextInput::make('nama_kegiatan')
                                    ->label('Nama Kegiatan')
                                    ->placeholder('Contoh: Peliputan Festival Geopark Ranah Minang')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('lokasi')
                                    ->label('Lokasi Kegiatan')
                                    ->placeholder('Contoh: Geopark Silokek')
                                    ->maxLength(255),
                            ]),

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

                                Select::make('penandatangan_id')
                                    ->label('Penandatangan (TTE)')
                                    ->relationship('penandatangan', 'name', fn (Builder $query) => $query->bukanSuperAdmin())
                                    ->default(fn (): ?int => Instansi::query()->value('kepala_id'))
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Selain Kadis otomatis memakai a.n. Kepala Dinas.')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Tim yang Bertugas')
                    ->schema([
                        Toggle::make('hanya_tim_kreatif')
                            ->label('Tampilkan Hanya Tim Kreatif Dinas')
                            ->default(true)
                            ->dehydrated(false)
                            ->live(),

                        Select::make('pegawais')
                            ->label('Pilih Anggota Tim')
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
                                    ? "{$record->name} — {$jabatanTim}"
                                    : $record->name;
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Nomor Surat & PDF TTE SRIKANDI')
                    ->schema([
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])
                            ->schema([
                                TextInput::make('nomor_surat')
                                    ->label('Nomor Surat')
                                    ->default(fn (): string => SuratTugas::generateNomorSurat())
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(100)
                                    ->helperText('Otomatis terisi, atau sesuaikan dengan nomor resmi dari SRIKANDI.'),

                                FileUpload::make('file_tte')
                                    ->label('Upload PDF TTE SRIKANDI (Opsional)')
                                    ->acceptedFileTypes(['application/pdf'])
                                    ->disk('public')
                                    ->directory('surat_tugas/tte')
                                    ->maxSize(5120)
                                    ->openable()
                                    ->downloadable()
                                    ->helperText('Isi jika sudah TTE di SRIKANDI agar langsung terkirim ke tim.'),
                            ]),
                    ]),

                Section::make('Dasar Surat & Catatan Tambahan')
                    ->description('Sudah terisi otomatis sesuai SK Tim Kreatif. Klik untuk mengubah bila perlu.')
                    ->collapsed()
                    ->schema([
                        Textarea::make('dasar_surat')
                            ->label('Dasar Pelaksanaan')
                            ->default(fn (): string => SuratTugas::defaultDasarSurat())
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('keterangan')
                            ->label('Catatan / Poin Tambahan')
                            ->placeholder('Kosongkan untuk teks standar pelaksanaan tugas.')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
