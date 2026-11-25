<?php

declare(strict_types=1);

namespace App\Filament\Resources\SuratTugas\Tables;

use App\Models\SuratTugas;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class SuratTugasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal_mulai', 'desc')
            ->columns([
                TextColumn::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->placeholder('Menunggu SRIKANDI')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('nama_kegiatan')
                    ->label('Kegiatan')
                    ->searchable()
                    ->wrap()
                    ->limit(55),

                TextColumn::make('tanggal_mulai')
                    ->label('Tgl Mulai')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('tanggal_selesai')
                    ->label('Tgl Selesai')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('lokasi')
                    ->label('Lokasi')
                    ->searchable()
                    ->placeholder('-')
                    ->limit(30),

                TextColumn::make('penanggungJawab.name')
                    ->label('Penanggung Jawab Bidang')
                    ->searchable()
                    ->placeholder('-')
                    ->wrap(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => $state === 'terbit' ? 'Diterbitkan' : 'Draft')
                    ->color(fn(string $state): string => $state === 'terbit' ? 'success' : 'warning')
                    ->sortable(),

                TextColumn::make('pegawais_count')
                    ->label('Tim Kreatif & Dibaca')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn(SuratTugas $record): string => "{$record->pegawais_sudah_baca_count}/{$record->pegawais_count} Dibaca")
                    ->sortable(),

                TextColumn::make('kehadiran_tim')
                    ->label('Konfirmasi Kehadiran')
                    ->getStateUsing(function (SuratTugas $record): array {
                        return $record->pegawais->map(function ($p): string {
                            $status = match ($p->pivot->status_kehadiran ?? 'belum_konfirmasi') {
                                'hadir' => 'Hadir',
                                'berhalangan' => 'Berhalangan',
                                default => 'Belum Konfirmasi',
                            };

                            return "{$p->name}: {$status}";
                        })->all();
                    })
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_ends_with($state, ': Hadir') => 'success',
                        str_ends_with($state, ': Berhalangan') => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('penandatangan.name')
                    ->label('Penandatangan')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Surat')
                    ->options([
                        'draft' => 'Draft (Menunggu TTE)',
                        'terbit' => 'Diterbitkan',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Surat Tugas')
                    ->icon('heroicon-o-plus'),
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make()
                    ->label('Detail')
                    ->infolist([
                        \Filament\Infolists\Components\TextEntry::make('nomor_surat')
                            ->label('Nomor Surat')
                            ->placeholder('Draft'),
                        \Filament\Infolists\Components\TextEntry::make('nama_kegiatan')
                            ->label('Nama Kegiatan'),
                        \Filament\Infolists\Components\TextEntry::make('tanggal_mulai')
                            ->label('Tanggal')
                            ->date('d/m/Y'),
                        \Filament\Infolists\Components\TextEntry::make('jam_acara')
                            ->label('Jam Acara')
                            ->placeholder('-'),
                        \Filament\Infolists\Components\TextEntry::make('lokasi')
                            ->label('Lokasi'),
                        \Filament\Infolists\Components\TextEntry::make('kehadiran_tim')
                            ->label('Kehadiran Tim')
                            ->getStateUsing(fn (SuratTugas $record): array => $record->pegawais->map(function ($p): string {
                                $status = match ($p->pivot->status_kehadiran ?? 'belum_konfirmasi') {
                                    'hadir' => 'Hadir',
                                    'berhalangan' => 'Berhalangan' . (filled($p->pivot->catatan_kehadiran) ? " ({$p->pivot->catatan_kehadiran})" : ''),
                                    default => 'Belum Konfirmasi',
                                };

                                return "{$p->name} — {$status}";
                            })->all())
                            ->badge(),
                        \Filament\Infolists\Components\RepeatableEntry::make('laporans')
                            ->label('Laporan Kegiatan')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('user.name')
                                    ->label('Pelapor'),
                                \Filament\Infolists\Components\TextEntry::make('ringkasan_kegiatan')
                                    ->label('Ringkasan'),
                                \Filament\Infolists\Components\TextEntry::make('link_dokumentasi')
                                    ->label('Link Dokumentasi')
                                    ->url(fn (?string $state): ?string => $state, shouldOpenInNewTab: true)
                                    ->placeholder('-'),
                            ])
                            ->columns(3),
                    ]),

                Action::make('cetak_pdf')
                    ->label(fn(SuratTugas $record): string => $record->status === 'terbit' ? 'Lihat Surat Tugas' : 'Preview Draft')
                    ->icon(fn(SuratTugas $record): string => $record->status === 'terbit' ? 'heroicon-o-document-check' : 'heroicon-o-eye')
                    ->color(fn(SuratTugas $record): string => $record->status === 'terbit' ? 'success' : 'info')
                    ->modalHeading(fn(SuratTugas $record): string => ($record->status === 'terbit' ? 'Surat Tugas Diterbitkan — ' : 'Preview Draft Surat Tugas — ') . ($record->nomor_surat ?: 'Belum Bernomor'))
                    ->modalWidth(Width::FourExtraLarge)
                    ->modalContent(function (SuratTugas $record): HtmlString {
                        $record->tandaiDibacaOleh(Auth::id());

                        return new HtmlString(
                            '<iframe src="' . route('surat-tugas.cetak-pdf', ['suratTugas' => $record, 'preview' => 1]) . '" class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white" style="height: 75vh;"></iframe>'
                        );
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->extraModalFooterActions([
                        Action::make('download_pdf')
                            ->label('Download PDF')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('primary')
                            ->url(fn(SuratTugas $record): string => route('surat-tugas.cetak-pdf', ['suratTugas' => $record, 'download' => 1]))
                            ->openUrlInNewTab(),
                    ]),

                Action::make('lihat_undangan')
                    ->label('Lihat Undangan')
                    ->icon('heroicon-o-paper-clip')
                    ->color('gray')
                    ->visible(fn (SuratTugas $record): bool => filled($record->bukti_surat_undangan))
                    ->url(fn (SuratTugas $record): string => \Illuminate\Support\Facades\Storage::disk('public')->url($record->bukti_surat_undangan), shouldOpenInNewTab: true),

                Action::make('konfirmasi_kehadiran')
                    ->label('Konfirmasi Hadir')
                    ->icon('heroicon-o-hand-thumb-up')
                    ->color('success')
                    ->visible(fn (SuratTugas $record): bool => $record->status === 'terbit'
                        && $record->pegawais()->where('users.id', \Illuminate\Support\Facades\Auth::id())->exists())
                    ->schema([
                        \Filament\Forms\Components\Radio::make('status_kehadiran')
                            ->label('Status Kehadiran')
                            ->options([
                                'hadir' => 'Hadir',
                                'berhalangan' => 'Berhalangan',
                            ])
                            ->required()
                            ->live()
                            ->inline(),
                        \Filament\Forms\Components\Textarea::make('catatan_kehadiran')
                            ->label('Alasan Berhalangan')
                            ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => $get('status_kehadiran') === 'berhalangan')
                            ->required(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => $get('status_kehadiran') === 'berhalangan')
                            ->rows(2),
                    ])
                    ->action(function (SuratTugas $record, array $data): void {
                        $catatan = $data['status_kehadiran'] === 'berhalangan'
                            ? ($data['catatan_kehadiran'] ?? null)
                            : null;

                        $record->pegawais()->updateExistingPivot(\Illuminate\Support\Facades\Auth::id(), [
                            'status_kehadiran' => $data['status_kehadiran'],
                            'catatan_kehadiran' => $catatan,
                            'dikonfirmasi_at' => now(),
                            'dibaca_at' => now(),
                        ]);

                        /** @var \App\Models\User|null $user */
                        $user = \Illuminate\Support\Facades\Auth::user();
                        if ($user) {
                            $record->kirimNotifikasiKehadiran(
                                $user,
                                $data['status_kehadiran'],
                                $catatan
                            );
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Konfirmasi kehadiran tersimpan')
                            ->success()
                            ->send();
                    }),

                Action::make('buat_laporan')
                    ->label('Buat Laporan')
                    ->icon('heroicon-o-document-plus')
                    ->color('warning')
                    ->visible(fn (SuratTugas $record): bool => $record->status === 'terbit'
                        && $record->pegawais()->where('users.id', \Illuminate\Support\Facades\Auth::id())->exists())
                    ->schema([
                        \Filament\Forms\Components\Textarea::make('ringkasan_kegiatan')
                            ->label('Ringkasan Hasil Kegiatan')
                            ->required()
                            ->rows(4),
                        \Filament\Forms\Components\TextInput::make('link_dokumentasi')
                            ->label('Link Dokumentasi (Google Drive / YouTube, Opsional)')
                            ->url()
                            ->maxLength(500),
                        \Filament\Forms\Components\FileUpload::make('foto_dokumentasi')
                            ->label('Foto Dokumentasi (Opsional)')
                            ->disk('public')
                            ->directory('surat-tugas/laporan')
                            ->image()
                            ->multiple()
                            ->maxFiles(5),
                    ])
                    ->action(function (SuratTugas $record, array $data): void {
                        \App\Models\LaporanSuratTugas::create([
                            'surat_tugas_id' => $record->id,
                            'user_id' => \Illuminate\Support\Facades\Auth::id(),
                            'ringkasan_kegiatan' => $data['ringkasan_kegiatan'],
                            'link_dokumentasi' => $data['link_dokumentasi'] ?? null,
                            'foto_dokumentasi' => $data['foto_dokumentasi'] ?? [],
                            'dilaporkan_at' => now(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Laporan kegiatan berhasil dikirim')
                            ->success()
                            ->send();
                    }),

                Action::make('tte_sakti')
                    ->label('Setujui')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->button()
                    ->visible(function (SuratTugas $record): bool {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        return $record->status === 'draft' && (
                            ($user?->canSignSuratTugas() ?? false)
                            || (int) $record->penandatangan_id === (int) $user?->id
                        );
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Terbitkan & TTE Surat Tugas di SAKTI')
                    ->modalDescription('Surat Tugas akan disahkan secara elektronik di SAKTI (dengan QR Code verifikasi berlogo Geopark) dan langsung dikirim ke kotak masuk tim yang bertugas.')
                    ->action(function (SuratTugas $record): void {
                        $record->tandatanganiDiSakti();

                        Notification::make()
                            ->title('Surat Tugas Disahkan (TTE SAKTI)')
                            ->body("Nomor {$record->nomor_surat} telah diterbitkan dan dinotifikasikan ke tim.")
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
