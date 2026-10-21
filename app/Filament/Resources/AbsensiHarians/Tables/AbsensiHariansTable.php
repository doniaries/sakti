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

        $table->modifyQueryUsing(function ($query) use ($currentUser) {
            $query->with(['user', 'logMasuk', 'logPulang']);

            if ($currentUser && ! $currentUser->hasAnyRole(['super_admin', 'admin', 'pimpinan'])) {
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
                            ->placeholder('Semua Tanggal (Klik untuk Memilih Hari/Bulan/Tahun)')
                            ->displayFormat('d/m/Y')
                            ->maxDate(now()) // Tanggal yang belum aktif (masa depan) otomatis disabled!
                            ->native(false)
                            ->closeOnDateSelection()
                            ->live(),
                    ])
                    ->query(
                        fn(Builder $query, array $data): Builder =>
                        $query->when($data['tanggal'] ?? null, fn(Builder $q, $v) => $q->whereDate('tanggal', $v))
                    )
                    ->indicateUsing(fn(array $data): ?string => !empty($data['tanggal']) ? 'Tanggal: ' . Carbon::parse($data['tanggal'])->translatedFormat('d F Y') : null),

                Filter::make('kategori_status')
                    ->label('Kategori Presensi')
                    ->form([
                        Select::make('kategori')
                            ->label('Status Kehadiran')
                            ->options([
                                'lengkap'      => 'Lengkap (Masuk & Pulang)',
                                'belum_pulang' => 'Belum Pulang (Hanya Masuk)',
                                'dinas_luar'   => 'Dinas Luar / Izin / Sakit / Cuti',
                                'tidak_hadir'  => 'Tidak Hadir / Alpa',
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
                            'lengkap' => $query->whereNotNull('jam_masuk')->whereNotNull('jam_pulang'),
                            'belum_pulang' => $query->whereNotNull('jam_masuk')->whereNull('jam_pulang')->whereNotIn('status', ['tidak_hadir', 'izin', 'sakit']),
                            'dinas_luar' => $query->where(fn ($q) => $q->where('jenis_kehadiran', 'dinas_luar')->orWhereIn('status', ['dinas_luar', 'izin', 'sakit', 'cuti'])),
                            'tidak_hadir' => $query->where('status', 'tidak_hadir')->orWhere(fn ($q) => $q->whereNull('jam_masuk')->whereNull('jam_pulang')),
                            default => $query,
                        };
                    })
                    ->indicateUsing(function (array $data): ?string {
                        $labels = [
                            'lengkap'      => 'Kategori: Lengkap',
                            'belum_pulang' => 'Kategori: Belum Pulang',
                            'dinas_luar'   => 'Kategori: Dinas Luar / Izin',
                            'tidak_hadir'  => 'Kategori: Tidak Hadir / Alpa',
                        ];
                        return $labels[$data['kategori'] ?? ''] ?? null;
                    }),

                Filter::make('pegawai')
                    ->label('Pegawai')
                    ->visible(fn() => \Illuminate\Support\Facades\Auth::user()?->hasAnyRole(['super_admin', 'admin', 'pimpinan']))
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
                    ->visible(fn($record) => in_array($record->status, ['izin', 'sakit'])),
                EditAction::make(),

                // ─── Buat LHK → Modal Slide-Over ──────────────────────────────
                Action::make('buat_lhk')
                    ->label('Buat LHK')
                    ->icon('heroicon-m-document-plus')
                    ->color('info')
                    ->modalWidth('7xl')
                    ->visible(fn($record) => ! in_array($record->status, ['izin', 'sakit', 'tidak_hadir']))
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
                            ->default(match ($record->status) {
                                'hadir', 'terlambat' => 'Hadir',
                                'sakit'              => 'Sakit',
                                'izin', 'cuti'       => 'Izin',
                                'dinas_luar'         => 'Dinas Luar',
                                default              => 'Hadir',
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
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    \Filament\Actions\BulkAction::make('set_jam_pulang_massal')
                        ->label('Set Jam Pulang Massal (1-Klik)')
                        ->icon('heroicon-o-arrow-right-start-on-rectangle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Set Jam Pulang Massal untuk Pegawai Terpilih')
                        ->modalDescription('Aksi ini akan mengisi jam pulang untuk pegawai terpilih yang belum memiliki absen pulang berdasarkan jam pulang resmi jadwal kerja hari ini (Senin-Kamis atau Jumat).')
                        ->modalSubmitActionLabel('Ya, Set Jam Pulang Sekarang')
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
                                        'keterangan' => trim(($absen->keterangan ?? '') . ' (Diset Otomatis Admin)'),
                                    ]);
                                    $count++;
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->title("Berhasil mengisi jam pulang untuk $count pegawai.")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
