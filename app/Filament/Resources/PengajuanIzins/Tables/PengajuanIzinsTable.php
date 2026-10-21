<?php

namespace App\Filament\Resources\PengajuanIzins\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class PengajuanIzinsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Pegawai')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('jenisIzin.nama')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn ($record) => match ($record->jenisIzin?->kategori) {
                        'cuti' => 'warning',
                        'sakit' => 'danger',
                        'wfh' => 'info',
                        default => 'primary',
                    })
                    ->searchable(),

                TextColumn::make('tanggal_mulai')
                    ->label('Mulai')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('tanggal_selesai')
                    ->label('Sampai')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('jumlah_hari')
                    ->label('Durasi')
                    ->suffix(' hari')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'disetujui' => 'success',
                        'ditolak' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state ?? 'Menunggu'))
                    ->sortable(),

                TextColumn::make('approver.name')
                    ->label('Verifikator')
                    ->placeholder('-'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'menunggu' => 'Menunggu',
                        'disetujui' => 'Disetujui',
                        'ditolak' => 'Ditolak',
                    ]),

                SelectFilter::make('jenis_izin_id')
                    ->label('Tipe Izin')
                    ->relationship('jenisIzin', 'nama'),
            ])
            ->recordActions([
                Action::make('setujui')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'menunggu')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'disetujui',
                            'disetujui_oleh' => Auth::id(),
                        ]);

                        Notification::make()
                            ->title('Pengajuan Disetujui!')
                            ->body('Data absensi harian pegawai otomatis dibuat/diperbarui.')
                            ->success()
                            ->send();
                    }),

                Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'menunggu')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'ditolak',
                            'disetujui_oleh' => Auth::id(),
                        ]);

                        Notification::make()
                            ->title('Pengajuan Ditolak')
                            ->danger()
                            ->send();
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
