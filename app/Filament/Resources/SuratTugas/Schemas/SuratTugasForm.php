<?php

declare(strict_types=1);

namespace App\Filament\Resources\SuratTugas\Schemas;

use App\Models\Instansi;
use App\Models\SuratTugas;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
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
                                    ->label('Pejabat Penandatangan')
                                    ->relationship('penandatangan', 'name', fn (Builder $query) => $query->bukanSuperAdmin())
                                    ->default(fn (): ?int => Instansi::query()->value('kadis_id'))
                                    ->searchable()
                                    ->preload()
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
                            ->label('Catatan / Keterangan Tambahan (Opsional)')
                            ->rows(2)
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
