<?php

declare(strict_types=1);

namespace App\Filament\Resources\SuratTugas\Schemas;

use App\Enums\Eselon;
use App\Models\Instansi;
use App\Models\SuratTugas;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
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
                            Section::make('Detail Kegiatan & Tim Kreatif Publikasi dan Promosi')
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

                                    Select::make('penanggung_jawab_id')
                                        ->label('Penanggung Jawab Kegiatan Bidang')
                                        ->relationship(
                                            name: 'penanggungJawab',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn (Builder $query): Builder => $query
                                                ->bukanSuperAdmin()
                                                ->where('aktif', true)
                                                ->whereIn('eselon', [
                                                    Eselon::II_A->value,
                                                    Eselon::II_B->value,
                                                    Eselon::III_A->value,
                                                    Eselon::III_B->value,
                                                    Eselon::IV_A->value,
                                                    Eselon::IV_B->value,
                                                ])
                                                ->with('jabatan')
                                                ->orderBy('name')
                                        )
                                        ->getOptionLabelFromRecordUsing(function (User $record): string {
                                            $jabatan = $record->jabatan?->nama;

                                            return $jabatan
                                                ? "{$record->name} — {$jabatan}"
                                                : $record->name;
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->helperText('Wajib diisi oleh pejabat bereselon sebagai pimpinan penanggung jawab di lokasi kegiatan.')
                                        ->columnSpanFull(),

                                    Select::make('pegawais')
                                        ->label('Pelaksana Teknis / Tim Kreatif yang Bertugas')
                                        ->relationship(
                                            name: 'pegawais',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn (Builder $query): Builder => $query
                                                ->bukanSuperAdmin()
                                                ->where('aktif', true)
                                                ->whereNotNull('jabatan_tim_kreatif')
                                                ->where(function (Builder $q): void {
                                                    $q->whereNull('eselon')
                                                        ->orWhere('eselon', Eselon::NON_ESELON->value);
                                                })
                                                ->orderBy('name')
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

                        // Kolom Kanan (1/3): Penomoran Otomatis & Penandatangan Eselon III+
                        Group::make([
                            Section::make('Penomoran & Pengesahan SAKTI')
                                ->schema([
                                    TextInput::make('nomor_surat')
                                        ->label('Nomor Surat Tugas')
                                        ->default(fn (): string => \App\Models\SuratTugas::generateNomorSurat())
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->maxLength(100)
                                        ->helperText('Nomor surat dibuat otomatis oleh sistem (terbit saat disetujui) dan tidak dapat diubah manual.'),

                                    Select::make('penandatangan_id')
                                        ->label('Penandatangan (TTE SAKTI)')
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
                                ]),
                        ])
                            ->columnSpan(['lg' => 1]),
                    ]),
            ]);
    }
}
