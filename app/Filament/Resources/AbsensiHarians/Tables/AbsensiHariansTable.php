<?php

namespace App\Filament\Resources\AbsensiHarians\Tables;

use App\Models\Aktivitas;
use App\Models\Lhk;
use App\Models\LhkPekerjaan;
use App\Models\LogAbsensi;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AbsensiHariansTable
{
    public static function configure(Table $table): Table
    {
        /** @var \App\Models\User|null $currentUser */
        $currentUser = \Illuminate\Support\Facades\Auth::user();

        $isPimpinanOrAdmin = $currentUser !== null && (
            $currentUser->hasAnyRole(['super_admin', 'admin', 'pimpinan', 'pa', 'pptk'])
            || in_array($currentUser->anggaran_role?->value ?? $currentUser->anggaran_role, ['PA', 'PPTK'], true)
            || str_contains(strtolower($currentUser->jabatan ?? ''), 'kepala dinas')
            || str_contains(strtolower($currentUser->jabatan ?? ''), 'sekretaris')
            || str_contains(strtolower($currentUser->jabatan ?? ''), 'kepala bidang')
            || str_contains(strtolower($currentUser->jabatan ?? ''), 'kasubag')
        );

        $table->modifyQueryUsing(function ($query) use ($currentUser, $isPimpinanOrAdmin) {
            $query->with(['user', 'logMasuk', 'logPulang']);

            if (! $isPimpinanOrAdmin && $currentUser !== null) {
                $query->where('user_id', $currentUser->id);
            }

            return $query;
        });

        return $table
            ->heading(new \Illuminate\Support\HtmlString('
                <style>
                    @media (min-width: 768px) {
                        .fi-ta-filters .fi-ta-filters-wrapper form {
                            display: flex;
                            align-items: flex-end;
                            gap: 1rem;
                        }
                        .fi-ta-filters .fi-ta-filters-wrapper form > .grid {
                            flex: 1;
                        }
                        .fi-ta-filters .fi-ta-filters-wrapper form > .flex.items-center {
                            margin-bottom: 0.25rem;
                        }
                    }
                </style>
            '))
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jam_masuk')
                    ->time('H:i:s')
                    ->sortable()
                    ->icon(fn($record) => $record->logMasuk?->sumber === 'mesin'
                        ? 'heroicon-m-finger-print'
                        : ($record->logMasuk?->sumber === 'mobile' ? 'heroicon-m-device-phone-mobile' : null))
                    ->iconColor(fn($record) => $record->logMasuk?->sumber === 'mesin' ? 'info' : 'success'),
                TextColumn::make('jam_pulang')
                    ->time('H:i:s')
                    ->sortable()
                    ->icon(fn($record) => $record->logPulang?->sumber === 'mesin'
                        ? 'heroicon-m-finger-print'
                        : ($record->logPulang?->sumber === 'mobile' ? 'heroicon-m-device-phone-mobile' : null))
                    ->iconColor(fn($record) => $record->logPulang?->sumber === 'mesin' ? 'info' : 'success'),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('jenis_kehadiran')
                    ->label('Tipe')
                    ->badge(),
                TextColumn::make('status_apel')
                    ->badge()
                    ->searchable(),
            ])
            ->defaultSort('tanggal', 'desc')
            ->deferFilters(false)
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)
            ->filters([
                Filter::make('tanggal')
                    ->label('Filter Tanggal')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('tanggal')
                            ->label('Pilih Tanggal Presensi')
                            ->default(now()->format('Y-m-d'))
                            ->displayFormat('d/m/Y')
                            ->maxDate(now()) // Tanggal yang belum aktif (masa depan) otomatis disabled!
                            ->native(false)
                            ->closeOnDateSelection()
                            ->live(),
                    ])
                    ->query(
                        fn(Builder $query, array $data): Builder =>
                        $query->when($data['tanggal'] ?? null, function (Builder $q, $v) {
                            $cleanDate = Carbon::parse($v)->toDateString();
                            return $q->whereDate('tanggal', $cleanDate);
                        })
                    )
                    ->indicateUsing(fn(array $data): ?string => !empty($data['tanggal']) ? 'Tanggal: ' . Carbon::parse($data['tanggal'])->translatedFormat('d F Y') : null),

                Filter::make('kategori_status')
                    ->label('Kategori Presensi')
                    ->form([
                        Select::make('kategori')
                            ->label('Status Kehadiran')
                            ->options([
                                'hadir'        => 'Hadir Tepat Waktu',
                                'terlambat'    => 'Terlambat Masuk',
                                'pulang_cepat' => 'Pulang Cepat (Tidak Absen Pulang)',
                                'dinas_luar'   => 'Dinas Luar',
                                'izin'         => 'Izin',
                                'sakit'        => 'Sakit',
                                'cuti'         => 'Cuti',
                                'tidak_hadir'  => 'Tanpa Keterangan (Alpa)',
                            ])
                            ->placeholder('Semua Status')
                            ->live(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $kategori = $data['kategori'] ?? null;
                        if (!$kategori) {
                            return $query;
                        }

                        return match ($kategori) {
                            'hadir'        => $query->where('status', 'hadir')->whereNotNull('jam_masuk')->whereNotNull('jam_pulang'),
                            'terlambat'    => $query->where('status', 'terlambat'),
                            'pulang_cepat' => $query->whereNotNull('jam_masuk')->whereNull('jam_pulang')->whereNotIn('status', ['tidak_hadir', 'izin', 'sakit', 'cuti']),
                            'dinas_luar'   => $query->where(fn ($q) => $q->where('jenis_kehadiran', 'dinas_luar')->orWhere('status', 'dinas_luar')),
                            'izin'         => $query->where('status', 'izin'),
                            'sakit'        => $query->where('status', 'sakit'),
                            'cuti'         => $query->where('status', 'cuti'),
                            'tidak_hadir'  => $query->where('status', 'tidak_hadir')->orWhere(fn ($q) => $q->whereNull('jam_masuk')->whereNull('jam_pulang')),
                            default        => $query,
                        };
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $labels = [
                            'hadir'        => 'Status: Hadir Tepat Waktu',
                            'terlambat'    => 'Status: Terlambat Masuk',
                            'pulang_cepat' => 'Status: Pulang Cepat',
                            'dinas_luar'   => 'Status: Dinas Luar',
                            'izin'         => 'Status: Izin',
                            'sakit'        => 'Status: Sakit',
                            'cuti'         => 'Status: Cuti',
                            'tidak_hadir'  => 'Status: Tanpa Keterangan (Alpa)',
                        ];
                        return $labels[$data['kategori'] ?? ''] ?? null;
                    }),

                Filter::make('bidang')
                    ->label('Bidang')
                    ->visible(fn() => $isPimpinanOrAdmin)
                    ->form([
                        Select::make('bidang_id')
                            ->label('Bidang')
                            ->options(
                                \App\Models\Bidang::orderBy('nama', 'asc')->pluck('nama', 'id')->toArray()
                            )
                            ->searchable()
                            ->placeholder('Semua Bidang')
                            ->live(),
                    ])
                    ->query(
                        fn(Builder $query, array $data): Builder =>
                        $query->when($data['bidang_id'] ?? null, fn(Builder $q, $v) => $q->whereHas('user', fn($uq) => $uq->where('bidang_id', $v)))
                    )
                    ->indicateUsing(function (array $data): ?string {
                        $bidang = \App\Models\Bidang::find((int) ($data['bidang_id'] ?? 0));
                        return $bidang ? 'Bidang: ' . $bidang->nama : null;
                    }),

                Filter::make('pegawai')
                    ->label('Pegawai')
                    ->visible(fn() => $isPimpinanOrAdmin)
                    ->form([
                        Select::make('user_id')
                            ->label('Nama Pegawai')
                            ->options(
                                User::orderBy('name', 'asc')->pluck('name', 'id')->toArray()
                            )
                            ->searchable()
                            ->placeholder('Semua Pegawai')
                            ->live(),
                    ])
                    ->query(
                        fn(Builder $query, array $data): Builder =>
                        $query->when($data['user_id'] ?? null, fn(Builder $q, $v) => $q->where('user_id', $v))
                    )
                    ->indicateUsing(function (array $data): ?string {
                        /** @var \App\Models\User|null $pegawai */
                        $pegawai = User::query()->find((int) ($data['user_id'] ?? 0));
                        return $pegawai ? 'Pegawai: ' . $pegawai->name : null;
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->visible(function ($record) {
                        $statusVal = $record->status instanceof \BackedEnum ? $record->status->value : (string) $record->status;
                        return in_array($statusVal, ['izin', 'sakit']);
                    }),
                EditAction::make(),

                // ─── Buat LHK → Modal Slide-Over ──────────────────────────────
                Action::make('buat_lhk')
                    ->label('Buat LHK')
                    ->icon('heroicon-m-document-plus')
                    ->color('info')
                    ->modalWidth('7xl')
                    ->visible(function ($record) {
                        $statusVal = $record->status instanceof \BackedEnum ? $record->status->value : (string) $record->status;
                        return ! in_array($statusVal, ['izin', 'sakit', 'tidak_hadir']);
                    })
                    // ->slideOver()
                    ->closeModalByClickingAway(false)
                    ->modalHeading(fn($record) => 'Buat LHK — ' . $record->user?->name . ' (' . $record->tanggal?->format('d/m/Y') . ')')
                    ->modalSubmitActionLabel('Simpan LHK')
                    ->form(fn($record) => [

                        // ── Data kehadiran (tersembunyi) ──────────────────────
                        Hidden::make('user_id')
                            ->default($record->user_id),

                        Hidden::make('tanggal')
                            ->default($record->tanggal?->format('Y-m-d')),

                        Hidden::make('log_absensi_masuk_id')
                            ->default($record->log_masuk_id),

                        Hidden::make('log_absensi_pulang_id')
                            ->default($record->log_pulang_id),

                        Hidden::make('status_kehadiran')
                            ->default(function ($record) {
                                $statusVal = $record->status instanceof \BackedEnum ? $record->status->value : (string) $record->status;
                                return match ($statusVal) {
                                    'hadir', 'terlambat' => 'Hadir',
                                    'sakit'              => 'Sakit',
                                    'izin', 'cuti'       => 'Izin',
                                    'dinas_luar'         => 'Dinas Luar',
                                    default              => 'Hadir',
                                };
                            }),

                        // ── Tampilkan jam masuk/pulang (read-only) ────────────
                        Section::make('Informasi Kehadiran')
                            ->extraAttributes(['class' => 'sticky top-0 z-10'])
                            ->schema([
                                TextEntry::make('info_pegawai')
                                    ->label('Pegawai')
                                    ->state($record->user?->name)
                                    ->columnSpan(1),

                                TextEntry::make('info_tanggal')
                                    ->label('Tanggal')
                                    ->state($record->tanggal?->format('d M Y'))
                                    ->columnSpan(1),

                                TextEntry::make('info_jam_masuk')
                                    ->label('Jam Masuk')
                                    ->state(function ($record) {
                                        $log = $record->logMasuk;
                                        return $log ? ($log->waktu_absensi?->format('H:i:s') . (!empty($log->sumber) ? ' (' . $log->sumber . ')' : '')) : ($record->jam_masuk ? \Carbon\Carbon::parse($record->jam_masuk)->format('H:i:s') : '-');
                                    })
                                    ->badge()
                                    ->color(function ($record) {
                                        $log = $record->logMasuk;
                                        return $log ? 'primary' : 'gray';
                                    })
                                    ->columnSpan(1),

                                TextEntry::make('info_jam_pulang')
                                    ->label('Jam Pulang')
                                    ->state(function ($record) {
                                        $log = $record->logPulang;
                                        return $log ? ($log->waktu_absensi?->format('H:i:s') . (!empty($log->sumber) ? ' (' . $log->sumber . ')' : '')) : ($record->jam_pulang ? \Carbon\Carbon::parse($record->jam_pulang)->format('H:i:s') : '-');
                                    })
                                    ->badge()
                                    ->color('success')
                                    ->columnSpan(1),
                            ])
                            ->columns(4),

                        // ── Rincian Pekerjaan ─────────────────────────────────
                        Section::make('Rincian Pekerjaan')
                            ->schema([
                                Repeater::make('pekerjaans')
                                    ->label('')
                                    ->schema([
                                        // 1. Rencana Aksi
                                        Select::make('rencana_aksi_id')
                                            ->label('Rencana Aksi')
                                            ->options([
                                                1 => 'Diluar Sasaran Kinerja',
                                                2 => 'Melaksanakan Tugas Lain yang diberikan Pimpinan'
                                            ])
                                            ->searchable()
                                            ->columnSpan(1),

                                        // 2. Aktivitas
                                        Select::make('aktivitas_id')
                                            ->options(
                                                Aktivitas::all()->mapWithKeys(function ($r) {
                                                    return [$r->id => $r->nama_aktivitas . ' (' . ($r->menit ?? 0) . ' mnt)'];
                                                })->toArray()
                                            )
                                            ->searchable()
                                            ->live()
                                            ->afterStateUpdated(function ($state, $set, $get) {
                                                if ($state) {
                                                    $aktivitas = Aktivitas::find((int) $state);
                                                    $menit = $aktivitas?->menit ?? 0;
                                                    $set('lama_menit', $menit);
                                                    $jamMulai = $get('jam_mulai');
                                                    if ($jamMulai && $menit > 0) {
                                                        try {
                                                            $set('jam_selesai', Carbon::parse($jamMulai)
                                                                ->addMinutes((int) $menit)
                                                                ->format('H:i'));
                                                        } catch (\Exception $e) {
                                                        }
                                                    }
                                                } else {
                                                    $set('lama_menit', 0);
                                                }
                                            })
                                            ->label('Aktivitas')
                                            ->columnSpan(1),

                                        // 3. Lama menit (auto, readonly)
                                        TextInput::make('lama_menit')
                                            ->numeric()
                                            ->default(0)
                                            ->suffix('mnt')
                                            ->readOnly()
                                            ->label('Lama')
                                            ->columnSpan(1),

                                        // 4. Jam Mulai
                                        TimePicker::make('jam_mulai')
                                            ->required()
                                            ->seconds(false)
                                            ->live()
                                            ->afterStateUpdated(function ($state, $set, $get) {
                                                $lamaMenit = (int) ($get('lama_menit') ?? 0);
                                                if ($state && $lamaMenit > 0) {
                                                    try {
                                                        $set('jam_selesai', Carbon::parse($state)
                                                            ->addMinutes($lamaMenit)
                                                            ->format('H:i'));
                                                    } catch (\Exception $e) {
                                                    }
                                                }
                                            })
                                            ->label('Jam Mulai')
                                            ->columnSpan(1),

                                        // 5. Jam Selesai (auto)
                                        TimePicker::make('jam_selesai')
                                            ->required()
                                            ->seconds(false)
                                            ->label('Jam Selesai')
                                            ->columnSpan(1),

                                        // 6. Hasil & Bukti
                                        Textarea::make('hasil_pekerjaan')
                                            ->rows(2)
                                            ->columnSpanFull(),

                                        TextInput::make('bukti_url')
                                            ->url()
                                            ->label('Link Bukti')
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(3)
                                    ->collapsible()
                                    ->defaultItems(1)
                                    ->addActionLabel('+ Tambah Pekerjaan'),
                            ]),
                    ])
                    ->action(function (array $data, $record): void {
                        $pekerjaans = $data['pekerjaans'] ?? [];
                        unset($data['pekerjaans']);

                        $lhk = Lhk::create([
                            'user_id'               => $data['user_id'],
                            'tanggal'               => $data['tanggal'],
                            'jam_masuk'             => $record->jam_masuk,
                            'jam_pulang'            => $record->jam_pulang,
                            'log_absensi_masuk_id'  => $data['log_absensi_masuk_id'],
                            'log_absensi_pulang_id' => $data['log_absensi_pulang_id'],
                            'status_kehadiran'      => $data['status_kehadiran'],
                        ]);

                        foreach ($pekerjaans as $pekerjaan) {
                            $lhk->pekerjaans()->create($pekerjaan);
                        }
                    }),
                // ──────────────────────────────────────────────────────────────
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->label('Tambah Absensi Harian')
                    ->icon('heroicon-o-plus-circle')
                    ->url(\App\Filament\Resources\AbsensiHarians\AbsensiHarianResource::getUrl('create')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    \Filament\Actions\BulkAction::make('set_jam_pulang_massal')
                        ->label('Verifikasi Jam Pulang Massal')
                        ->icon('heroicon-o-arrow-right-start-on-rectangle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Verifikasi & Isi Jam Pulang Massal')
                        ->modalDescription('Aksi ini diperuntukkan bagi pegawai yang telah konfirmasi ke admin namun tidak/belum sempat mengambil tap absen pulang. Sistem akan mengisi jam pulang resmi jadwal kerja hari tersebut (Senin-Kamis atau Jumat).')
                        ->modalSubmitActionLabel('Ya, Verifikasi Jam Pulang')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $count = 0;
                            foreach ($records as $absen) {
                                if (empty($absen->jam_pulang)) {
                                    $user = $absen->user;
                                    $jadwal = $user?->getJadwalKerja();
                                    $tanggal = $absen->tanggal ?? today();
                                    $jamPulangResmi = $jadwal ? $jadwal->getJamPulangHari($tanggal) : '16:00';

                                    $absen->update([
                                        'jam_pulang' => strlen($jamPulangResmi) === 5 ? $jamPulangResmi . ':00' : $jamPulangResmi,
                                        'keterangan' => trim(($absen->keterangan ?? '') . ' (Konfirmasi Pegawai - Diset Admin)'),
                                    ]);
                                    $count++;
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->title("Berhasil memverifikasi dan mengisi jam pulang untuk $count pegawai.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
