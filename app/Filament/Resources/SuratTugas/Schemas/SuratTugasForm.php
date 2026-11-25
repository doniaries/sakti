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
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                        // Kolom Kiri (2/3): Dasar Surat Undangan, Detail Kegiatan, Tim Bertugas & Catatan
                        Group::make([
                            Section::make('Dasar Surat Undangan (Opsional)')
                                ->schema([
                                    Grid::make([
                                        'default' => 1,
                                        'md' => 3,
                                    ])
                                        ->schema([
                                            TextInput::make('dasar_surat_undangan')
                                                ->label('Nomor Surat Undangan')
                                                ->placeholder('Contoh: 005/123/Kominfo-2026')
                                                ->maxLength(500),

                                            TextInput::make('asal_surat_undangan')
                                                ->label('Asal Surat')
                                                ->placeholder('Contoh: Sekretariat Daerah Kab. Sijunjung')
                                                ->maxLength(255)
                                                ->columnSpan(['md' => 2]),
                                        ]),

                                    Grid::make([
                                        'default' => 1,
                                        'md' => 3,
                                    ])
                                        ->schema([
                                            TextInput::make('perihal_surat_undangan')
                                                ->label('Perihal Surat')
                                                ->placeholder('Contoh: Undangan Menghadiri Acara...')
                                                ->helperText('Jika diisi dan Nama Kegiatan masih kosong, otomatis mengisi Nama Kegiatan.')
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                                                    if (filled($state) && blank($get('nama_kegiatan'))) {
                                                        $set('nama_kegiatan', $state);
                                                    }
                                                })
                                                ->maxLength(255)
                                                ->columnSpan(['md' => 2]),

                                            DatePicker::make('tanggal_surat_undangan')
                                                ->label('Tanggal Surat')
                                                ->displayFormat('d/m/Y')
                                                ->native(false),
                                        ]),

                                    FileUpload::make('bukti_surat_undangan')
                                        ->label('Upload Bukti Surat Undangan')
                                        ->disk('public')
                                        ->directory('surat-tugas/undangan')
                                        ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                        ->maxSize(5120)
                                        ->downloadable()
                                        ->openable()
                                        ->helperText('Format: PDF, JPG, PNG, atau WEBP (maks. 5 MB).'),
                                ]),

                            Section::make('Detail Kegiatan & Tim Kreatif Publikasi dan Promosi')
                                ->schema([
                                    TextInput::make('nama_kegiatan')
                                        ->label('Nama Kegiatan')
                                        ->placeholder('Contoh: Peliputan Festival Geopark Ranah Minang')
                                        ->datalist(fn (): array => SuratTugas::query()
                                            ->distinct()
                                            ->orderBy('nama_kegiatan')
                                            ->pluck('nama_kegiatan')
                                            ->all()
                                        )
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

                                            TextInput::make('jam_acara')
                                                ->label('Jam Acara')
                                                ->placeholder('Contoh: 08:30')
                                                ->datalist([
                                                    '07:30', '08:00', '08:30', '09:00', '09:30',
                                                    '10:00', '10:30', '11:00', '13:00', '13:30',
                                                    '14:00', '14:30', '15:00', '16:00', '19:00', '20:00',
                                                ])
                                                ->maxLength(20),
                                        ]),

                                    TextInput::make('lokasi')
                                        ->label('Lokasi Kegiatan')
                                        ->required()
                                        ->placeholder('Contoh: Geopark Silokek')
                                        ->maxLength(255)
                                        ->columnSpanFull(),

                                    Select::make('penanggung_jawab_ids')
                                        ->label('Penanggung Jawab Kegiatan Bidang')
                                        ->options(fn (): array => User::query()
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
                                            ->get()
                                            ->mapWithKeys(fn (User $record): array => [
                                                $record->id => $record->jabatan?->nama
                                                    ? "{$record->name} — {$record->jabatan->nama}"
                                                    : $record->name,
                                            ])
                                            ->all()
                                        )
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->helperText('Wajib diisi oleh pejabat bereselon sebagai pimpinan penanggung jawab di lokasi kegiatan (bisa lebih dari satu).')
                                        ->columnSpanFull(),

                                    Select::make('koordinator_acara_ids')
                                        ->label('Koordinator Acara (JF)')
                                        ->options(fn (): array => User::query()
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
                                            ->where(function (Builder $q): void {
                                                $q->whereHas('roles', fn (Builder $r): Builder => $r->where('name', 'jf'))
                                                    ->orWhereHas('jabatan', fn (Builder $j): Builder => $j
                                                        ->where('nama', 'like', '%fungsional%')
                                                        ->orWhere('nama', 'like', '%ahli %'));
                                            })
                                            ->with('jabatan')
                                            ->orderBy('name')
                                            ->get()
                                            ->mapWithKeys(fn (User $record): array => [
                                                $record->id => $record->jabatan?->nama
                                                    ? "{$record->name} — {$record->jabatan->nama}"
                                                    : $record->name,
                                            ])
                                            ->all()
                                        )
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->helperText('Pilih satu atau lebih Jabatan Fungsional (JF) sebagai koordinator acara.')
                                        ->columnSpanFull(),

                                    Select::make('pegawais')
                                        ->label('Pelaksana Teknis / Tim Kreatif yang Bertugas')
                                        ->relationship(
                                            name: 'pegawais',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn (Builder $query): Builder => $query
                                                ->bukanSuperAdmin()
                                                ->where('aktif', true)
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
                                                : "{$record->name} — Staf";
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
