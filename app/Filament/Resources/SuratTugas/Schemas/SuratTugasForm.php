<?php

declare(strict_types=1);

namespace App\Filament\Resources\SuratTugas\Schemas;

use App\Enums\Eselon;
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
use Filament\Schemas\Components\Group;
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
                Grid::make([
                    'default' => 1,
                    'lg' => 3,
                ])
                    ->schema([
                        // Kolom Kiri (2/3): Detail Kegiatan, Tim Bertugas & Catatan
                        Group::make([
                            Section::make('Detail Kegiatan & Tim Bertugas')
                                ->schema([
                                    TextInput::make('nama_kegiatan')
                                        ->label('Nama Kegiatan')
                                        ->placeholder('Contoh: Peliputan Festival Geopark Ranah Minang')
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
                                                ->label('Lokasi Kegiatan')
                                                ->required()
                                                ->placeholder('Contoh: Geopark Silokek')
                                                ->maxLength(255),
                                        ]),

                                    Toggle::make('hanya_tim_kreatif')
                                        ->label('Tampilkan Hanya Tim Kreatif Dinas')
                                        ->default(true)
                                        ->dehydrated(false)
                                        ->live(),

                                    Select::make('pegawais')
                                        ->label('Tim yang Bertugas')
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

                                    Textarea::make('keterangan')
                                        ->label('Catatan Tambahan (Opsional)')
                                        ->placeholder('Contoh: Membawa perlengkapan kamera/drone dan melaporkan hasil dokumentasi kepada Pimpinan.')
                                        ->helperText('Akan tampil sebagai poin ke-2 pada bagian "Untuk" di surat (misal: perlengkapan, titik kumpul, atau instruksi khusus). Kosongkan untuk teks standar.')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                ]),
                        ])
                            ->columnSpan(['lg' => 2]),

                        // Kolom Kanan (1/3): Penomoran Otomatis, Penandatangan Eselon III+ & Upload TTE
                        Group::make([
                            Section::make('Penomoran & TTE SRIKANDI')
                                ->schema([
                                    TextInput::make('nomor_surat')
                                        ->label('Nomor Surat SRIKANDI')
                                        ->placeholder('Diisi setelah terbit di SRIKANDI')
                                        ->unique(ignoreRecord: true)
                                        ->maxLength(100)
                                        ->helperText('Kosongkan jika masih draf; nomor resmi diisi dari SRIKANDI.'),

                                    Select::make('penandatangan_id')
                                        ->label('Penandatangan (TTE)')
                                        ->relationship(
                                            name: 'penandatangan',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn(Builder $query): Builder => $query
                                                ->bukanSuperAdmin()
                                                ->whereIn('eselon', [
                                                    Eselon::II_A->value,
                                                    Eselon::II_B->value,
                                                    Eselon::III_A->value,
                                                    Eselon::III_B->value,
                                                ])
                                                ->orderBy('eselon')
                                                ->orderBy('name')
                                        )
                                        ->default(fn(): ?int => Instansi::query()->value('kepala_id'))
                                        ->searchable()
                                        ->preload()
                                        ->helperText('Hanya Eselon III ke atas. Selain Kadis otomatis a.n. Kepala Dinas.')
                                        ->required(),

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
                        ])
                            ->columnSpan(['lg' => 1]),
                    ]),
            ]);
    }
}
