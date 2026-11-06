<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogs\Tables;

use App\Models\ActivityLog;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('user'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i:s')
                    ->description(fn (ActivityLog $record): string => $record->created_at ? $record->created_at->diffForHumans() : '')
                    ->sortable(),

                TextColumn::make('user_name')
                    ->label('Pelaku / Pengguna')
                    ->weight('bold')
                    ->color('primary')
                    ->description(fn (ActivityLog $record): string => $record->user_role ?? '-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('module')
                    ->label('Modul')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Autentikasi' => 'info',
                        'Pengajuan SPJ' => 'warning',
                        'Presensi' => 'success',
                        'LHK' => 'purple',
                        'Kepegawaian' => 'emerald',
                        'Pengaturan' => 'gray',
                        default => 'primary',
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('event')
                    ->label('Aksi')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => strtoupper((string) $state))
                    ->color(fn (?string $state): string => match (strtolower((string) $state)) {
                        'login' => 'success',
                        'logout' => 'gray',
                        'login_failed' => 'danger',
                        'create' => 'success',
                        'update' => 'info',
                        'delete' => 'danger',
                        'verify' => 'success',
                        'revisi' => 'warning',
                        'cair' => 'emerald',
                        'tolak' => 'danger',
                        default => 'primary',
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Rincian Aktivitas')
                    ->wrap()
                    ->searchable(),

                TextColumn::make('subject_label')
                    ->label('Objek Terkait')
                    ->badge()
                    ->color('gray')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('ip_address')
                    ->label('IP & Perangkat')
                    ->description(fn (ActivityLog $record): string => $record->device ?? '-')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('module')
                    ->label('Modul')
                    ->options([
                        'Autentikasi' => 'Autentikasi',
                        'Pengajuan SPJ' => 'Pengajuan SPJ',
                        'Presensi' => 'Presensi',
                        'LHK' => 'LHK',
                        'Kepegawaian' => 'Kepegawaian',
                        'Pengaturan' => 'Pengaturan',
                    ]),

                SelectFilter::make('event')
                    ->label('Jenis Aksi')
                    ->options([
                        'login' => 'Login',
                        'logout' => 'Logout',
                        'login_failed' => 'Login Gagal',
                        'create' => 'Create (Tambah Baru)',
                        'update' => 'Update (Perubahan Data)',
                        'delete' => 'Delete (Penghapusan)',
                        'verify' => 'Verify (Verifikasi / Setujui)',
                        'revisi' => 'Revisi (Kembalikan Berkas)',
                        'cair' => 'Cair (Pencairan Dana)',
                        'tolak' => 'Tolak',
                    ]),

                SelectFilter::make('user_id')
                    ->label('Pegawai / Pelaku')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('rentang_waktu')
                    ->form([
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal')
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal')
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Detail Log')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detail Log Aktivitas Pengguna')
                    ->modalWidth('4xl'),
            ])
            ->headerActions([])
            ->bulkActions([]);
    }
}

