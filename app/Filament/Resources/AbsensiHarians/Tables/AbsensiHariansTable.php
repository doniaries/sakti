<?php

namespace App\Filament\Resources\AbsensiHarians\Tables;

use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
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
                    ->icon(fn($record) => $record->logMasuk?->sumber === 'mesin' ? 'heroicon-m-finger-print' : ($record->logMasuk?->sumber === 'mobile' ? 'heroicon-m-device-phone-mobile' : null))
                    ->iconColor(fn($record) => $record->logMasuk?->sumber === 'mesin' ? 'info' : 'success'),
                TextColumn::make('jam_pulang')
                    ->time('H:i:s')
                    ->sortable()
                    ->icon(fn($record) => $record->logPulang?->sumber === 'mesin' ? 'heroicon-m-finger-print' : ($record->logPulang?->sumber === 'mobile' ? 'heroicon-m-device-phone-mobile' : null))
                    ->iconColor(fn($record) => $record->logPulang?->sumber === 'mesin' ? 'info' : 'success'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'hadir' => 'success',
                        'terlambat' => 'warning',
                        'tidak_hadir' => 'danger',
                        'dinas_luar' => 'info',
                        'cuti', 'izin', 'sakit' => 'warning',
                        'libur' => 'gray',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('status_apel')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'ikut apel' => 'success',
                        'tidak ikut apel' => 'danger',
                        default => 'gray',
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
                                User::orderBy('name')->pluck('name', 'id')->all()
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
                        if (! $data['user_id']) return null;
                        return 'Pegawai: ' . (User::find((int) $data['user_id'])?->name ?? '-');
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
                Action::make('buat_lhk')
                    ->label('Buat LHK')
                    ->icon('heroicon-m-document-plus')
                    ->color('info')
                    ->visible(fn($record) => !in_array($record->status, ['izin', 'sakit', 'tidak_hadir']))
                    ->url(fn($record) => \App\Filament\Resources\Lhks\LhkResource::getUrl('create', [
                        'tanggal' => $record->tanggal,
                        'user_id' => $record->user_id,
                        'log_absensi_masuk_id' => $record->log_absensi_masuk_id,
                        'log_absensi_pulang_id' => $record->log_absensi_pulang_id,
                        'status' => match ($record->status) {
                            'hadir', 'terlambat' => 'Hadir',
                            'sakit' => 'Sakit',
                            'izin', 'cuti' => 'Izin',
                            'dinas_luar' => 'Dinas Luar',
                            default => 'Hadir',
                        }
                    ]))
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
