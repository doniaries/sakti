<?php

namespace App\Filament\Resources\AbsensiHarians\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AbsensiHariansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with(['user', 'logMasuk', 'logPulang']))
            ->columns([
                TextColumn::make('user.name')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('jam_masuk')
                    ->time('H:i')
                    ->sortable()
                    ->icon(fn($record) => $record->logMasuk?->sumber === 'mesin' ? 'heroicon-m-finger-print' : ($record->logMasuk?->sumber === 'mobile' ? 'heroicon-m-device-phone-mobile' : null))
                    ->iconColor(fn($record) => $record->logMasuk?->sumber === 'mesin' ? 'info' : 'success'),
                TextColumn::make('jam_pulang')
                    ->time('H:i')
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
            ->defaultSort('updated_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('buat_lhk')
                    ->label('Buat LHK')
                    ->icon('heroicon-m-document-plus')
                    ->color('info')
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
