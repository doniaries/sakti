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
                    ->visible(fn ($record) => $record->status === 'menunggu' && $record->jenisIzin?->kategori !== 'cuti')
                    ->requiresConfirmation()
                    ->form([
                        \Filament\Forms\Components\Textarea::make('catatan_atasan')
                            ->label('Catatan Pimpinan (Opsional)')
                            ->placeholder('Tuliskan catatan verifikasi...'),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'disetujui',
                            'disetujui_oleh' => Auth::id(),
                            'catatan_atasan' => $data['catatan_atasan'] ?? null,
                        ]);

                        $record->sinkronkanKeAbsensiHarian();

                        // Notifikasi ke pegawai
                        if ($record->user) {
                            Notification::make()
                                ->title('Pengajuan Izin Disetujui')
                                ->body("Pengajuan {$record->jenisIzin?->nama} Anda telah disetujui.")
                                ->success()
                                ->sendToDatabase($record->user);
                        }

                        Notification::make()
                            ->title('Pengajuan Berhasil Disetujui!')
                            ->body('Data absensi harian pegawai otomatis dibuat/diperbarui.')
                            ->success()
                            ->send();
                    }),

                Action::make('tolak')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'menunggu' && $record->jenisIzin?->kategori !== 'cuti')
                    ->requiresConfirmation()
                    ->form([
                        \Filament\Forms\Components\Textarea::make('catatan_atasan')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'ditolak',
                            'disetujui_oleh' => Auth::id(),
                            'catatan_atasan' => $data['catatan_atasan'] ?? null,
                        ]);

                        // Notifikasi ke pegawai
                        if ($record->user) {
                            Notification::make()
                                ->title('Pengajuan Izin Ditolak')
                                ->body("Pengajuan {$record->jenisIzin?->nama} Anda ditolak. Catatan: {$data['catatan_atasan']}")
                                ->danger()
                                ->sendToDatabase($record->user);
                        }

                        Notification::make()
                            ->title('Pengajuan Ditolak')
                            ->danger()
                            ->send();
                    }),

                Action::make('cetak_pdf')
                    ->label('Preview & Cetak PDF')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn ($record): bool => $record->jenisIzin?->kategori === 'cuti')
                    ->modalHeading(fn ($record): string => 'Preview Surat Cuti — ' . ($record->user?->name ?? 'Pegawai'))
                    ->modalWidth('5xl')
                    ->modalContent(fn ($record): \Illuminate\Support\HtmlString => new \Illuminate\Support\HtmlString(
                        '<iframe src="' . route('pengajuan-izin.cetak-pdf', ['pengajuanIzin' => $record, 'preview' => 1]) . '" class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white" style="height: 75vh;"></iframe>'
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->extraModalFooterActions([
                        Action::make('setujui_preview')
                            ->label('Setujui')
                            ->icon('heroicon-o-check-circle')
                            ->color('success')
                            ->visible(fn ($record): bool => $record->status === 'menunggu')
                            ->requiresConfirmation()
                            ->modalHeading('Setujui Pengajuan Cuti?')
                            ->action(function ($record): void {
                                $record->update([
                                    'status' => 'disetujui',
                                    'disetujui_oleh' => Auth::id(),
                                ]);

                                $record->sinkronkanKeAbsensiHarian();

                                if ($record->user) {
                                    Notification::make()
                                        ->title('Pengajuan Cuti Disetujui')
                                        ->body("Pengajuan {$record->jenisIzin?->nama} Anda telah disetujui.")
                                        ->success()
                                        ->sendToDatabase($record->user);
                                }

                                Notification::make()
                                    ->title('Pengajuan Cuti Disetujui')
                                    ->success()
                                    ->send();
                            }),
                        Action::make('tolak_preview')
                            ->label('Tolak')
                            ->icon('heroicon-o-x-circle')
                            ->color('danger')
                            ->visible(fn ($record): bool => $record->status === 'menunggu')
                            ->requiresConfirmation()
                            ->modalHeading('Tolak Pengajuan Cuti?')
                            ->form([
                                \Filament\Forms\Components\Textarea::make('catatan_atasan')
                                    ->label('Alasan Penolakan')
                                    ->required(),
                            ])
                            ->action(function ($record, array $data): void {
                                $record->update([
                                    'status' => 'ditolak',
                                    'disetujui_oleh' => Auth::id(),
                                    'catatan_atasan' => $data['catatan_atasan'] ?? null,
                                ]);

                                if ($record->user) {
                                    Notification::make()
                                        ->title('Pengajuan Cuti Ditolak')
                                        ->body("Pengajuan {$record->jenisIzin?->nama} Anda ditolak. Catatan: {$data['catatan_atasan']}")
                                        ->danger()
                                        ->sendToDatabase($record->user);
                                }

                                Notification::make()
                                    ->title('Pengajuan Cuti Ditolak')
                                    ->danger()
                                    ->send();
                            }),
                        Action::make('download_pdf')
                            ->label('Buka Tab Baru / Download')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('primary')
                            ->url(fn ($record): string => route('pengajuan-izin.cetak-pdf', $record))
                            ->openUrlInNewTab(),
                    ]),

                EditAction::make()
                    ->visible(fn ($record) => $record->status === 'menunggu' && $record->jenisIzin?->kategori !== 'cuti'),

                DeleteAction::make()
                    ->visible(fn ($record) => $record->status === 'menunggu' && $record->jenisIzin?->kategori !== 'cuti'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->filter(fn ($r) => $r->status === 'menunggu')->each->delete();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
