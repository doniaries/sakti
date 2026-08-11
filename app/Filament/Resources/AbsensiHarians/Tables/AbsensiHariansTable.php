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
use Filament\Forms\Components\Placeholder;
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

        return $table
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(4)
            ->modifyQueryUsing(fn($query) => $query->with(['user', 'logMasuk', 'logPulang']))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tanggal')
                    ->date()
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
                    ->color(fn(?string $state): string => match ($state) {
                        'hadir'       => 'success',
                        'terlambat'   => 'warning',
                        'tidak_hadir' => 'danger',
                        'dinas_luar'  => 'info',
                        'cuti', 'izin', 'sakit' => 'warning',
                        'libur'       => 'gray',
                        default       => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('status_apel')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'ikut apel'      => 'success',
                        'tidak ikut apel' => 'danger',
                        default          => 'gray',
                    })
                    ->searchable(),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                Filter::make('pegawai')
                    ->label('Pegawai')
                    ->form([
                        Select::make('user_id')
                            ->label('Nama Pegawai')
                            ->options(
                                User::orderBy('name', 'asc')->pluck('name', 'id')->toArray()
                            )
                            ->default($currentUser?->id)
                            ->searchable()
                            ->placeholder('Semua Pegawai'),
                    ])
                    ->query(
                        fn(Builder $query, array $data): Builder =>
                        $query->when($data['user_id'] ?? null, fn(Builder $q, $v) => $q->where('user_id', $v))
                    )
                    ->indicateUsing(function (array $data): ?string {
                        /** @var \App\Models\User|null $pegawai */
                        $pegawai = User::query()->find((int) $data['user_id']);
                        return $pegawai ? 'Pegawai: ' . $pegawai->name : null;
                    }),

                Filter::make('tahun')
                    ->label('Tahun')
                    ->form([
                        Select::make('tahun')
                            ->label('Tahun')
                            ->options(function () {
                                $years = [];
                                for ($y = Carbon::now()->year; $y >= Carbon::now()->year - 5; $y--) {
                                    $years[$y] = $y;
                                }
                                return $years;
                            })
                            ->default(Carbon::now()->year)
                            ->placeholder('Semua Tahun'),
                    ])
                    ->query(
                        fn(Builder $query, array $data): Builder =>
                        $query->when($data['tahun'] ?? null, fn(Builder $q, $v) => $q->whereYear('tanggal', $v))
                    )
                    ->indicateUsing(fn(array $data): ?string => $data['tahun'] ? 'Tahun: ' . $data['tahun'] : null),

                Filter::make('bulan')
                    ->label('Bulan')
                    ->form([
                        Select::make('bulan')
                            ->label('Bulan')
                            ->options([
                                1 => 'Januari',
                                2 => 'Februari',
                                3 => 'Maret',
                                4 => 'April',
                                5 => 'Mei',
                                6 => 'Juni',
                                7 => 'Juli',
                                8 => 'Agustus',
                                9 => 'September',
                                10 => 'Oktober',
                                11 => 'November',
                                12 => 'Desember',
                            ])
                            ->default(Carbon::now()->month)
                            ->placeholder('Semua Bulan'),
                    ])
                    ->query(
                        fn(Builder $query, array $data): Builder =>
                        $query->when($data['bulan'] ?? null, fn(Builder $q, $v) => $q->whereMonth('tanggal', $v))
                    )
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['bulan']) return null;
                        $namaBulan = [
                            1 => 'Januari',
                            2 => 'Februari',
                            3 => 'Maret',
                            4 => 'April',
                            5 => 'Mei',
                            6 => 'Juni',
                            7 => 'Juli',
                            8 => 'Agustus',
                            9 => 'September',
                            10 => 'Oktober',
                            11 => 'November',
                            12 => 'Desember',
                        ];
                        return 'Bulan: ' . ($namaBulan[$data['bulan']] ?? '-');
                    }),

                Filter::make('tanggal')
                    ->label('Tanggal')
                    ->form([
                        Select::make('tanggal')
                            ->label('Tanggal')
                            ->options(
                                collect(range(1, 31))->mapWithKeys(fn($d) => [$d => $d])->toArray()
                            )
                            ->default(Carbon::now()->day)
                            ->placeholder('Semua Tanggal'),
                    ])
                    ->query(
                        fn(Builder $query, array $data): Builder =>
                        $query->when($data['tanggal'] ?? null, fn(Builder $q, $v) => $q->whereDay('tanggal', $v))
                    )
                    ->indicateUsing(fn(array $data): ?string => $data['tanggal'] ? 'Tanggal: ' . $data['tanggal'] : null),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                // ─── Buat LHK → Modal Slide-Over ──────────────────────────────
                Action::make('buat_lhk')
                    ->label('Buat LHK')
                    ->icon('heroicon-m-document-plus')
                    ->color('info')
                    ->visible(fn($record) => ! in_array($record->status, ['izin', 'sakit', 'tidak_hadir']))
                    ->slideOver()
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
                            ->schema([
                                Placeholder::make('info_pegawai')
                                    ->label('Pegawai')
                                    ->content($record->user?->name)
                                    ->columnSpan(1),

                                Placeholder::make('info_tanggal')
                                    ->label('Tanggal')
                                    ->content($record->tanggal?->format('d M Y'))
                                    ->columnSpan(1),

                                Placeholder::make('info_jam_masuk')
                                    ->label('Jam Masuk')
                                    ->content(function () use ($record) {
                                        $jam = $record->jam_masuk
                                            ? Carbon::createFromFormat('H:i:s', $record->jam_masuk)->format('H:i')
                                            : '-';
                                        $sumber = $record->logMasuk?->sumber ?? '';
                                        return $jam . ($sumber ? " ($sumber)" : '');
                                    })
                                    ->columnSpan(1),

                                Placeholder::make('info_jam_pulang')
                                    ->label('Jam Pulang')
                                    ->content(function () use ($record) {
                                        $jam = $record->jam_pulang
                                            ? Carbon::createFromFormat('H:i:s', $record->jam_pulang)->format('H:i')
                                            : '-';
                                        $sumber = $record->logPulang?->sumber ?? '';
                                        return $jam . ($sumber ? " ($sumber)" : '');
                                    })
                                    ->columnSpan(1),
                            ])
                            ->columns(4),

                        // ── Rincian Pekerjaan ─────────────────────────────────
                        Section::make('Rincian Pekerjaan')
                            ->schema([
                                Repeater::make('pekerjaans')
                                    ->label('')
                                    ->schema([
                                        // 1. Aktivitas
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
                                                        $set('jam_selesai', Carbon::createFromFormat('H:i', substr($jamMulai, 0, 5))
                                                            ->addMinutes((int) $menit)
                                                            ->format('H:i'));
                                                    }
                                                } else {
                                                    $set('lama_menit', 0);
                                                }
                                            })
                                            ->label('Aktivitas')
                                            ->columnSpan(1),

                                        // 2. Rencana Aksi
                                        Select::make('rencana_aksi_id')
                                            ->options(
                                                \App\Models\RencanaAksi::pluck('rencana_aksi', 'id')->toArray()
                                            )
                                            ->searchable()
                                            ->label('Rencana Aksi')
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
                                                    $set('jam_selesai', Carbon::createFromFormat('H:i', substr($state, 0, 5))
                                                        ->addMinutes($lamaMenit)
                                                        ->format('H:i'));
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
                ]),
            ]);
    }
}
