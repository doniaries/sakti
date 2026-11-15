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
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
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

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'terbit' ? 'Resmi (TTE)' : 'Draft')
                    ->color(fn (string $state): string => $state === 'terbit' ? 'success' : 'warning')
                    ->sortable(),

                TextColumn::make('pegawais_count')
                    ->label('Tim & Dibaca')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (SuratTugas $record): string => "{$record->pegawais_sudah_baca_count}/{$record->pegawais_count} Dibaca")
                    ->sortable(),

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
                        'terbit' => 'Resmi (TTE SRIKANDI)',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Surat Tugas')
                    ->icon('heroicon-o-plus'),
            ])
            ->recordActions([
                Action::make('cetak_pdf')
                    ->label(fn (SuratTugas $record): string => filled($record->file_tte) ? 'Baca PDF TTE' : 'Preview Draft')
                    ->icon(fn (SuratTugas $record): string => filled($record->file_tte) ? 'heroicon-o-document-check' : 'heroicon-o-eye')
                    ->color(fn (SuratTugas $record): string => filled($record->file_tte) ? 'success' : 'info')
                    ->modalHeading(fn (SuratTugas $record): string => (filled($record->file_tte) ? 'Surat Tugas Resmi (TTE SRIKANDI) — ' : 'Preview Draft Surat Tugas — ') . ($record->nomor_surat ?: 'Belum Bernomor'))
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
                        Action::make('download_word')
                            ->label('Download Template Word (SRIKANDI)')
                            ->icon('heroicon-o-document-arrow-down')
                            ->color('warning')
                            ->visible(fn (SuratTugas $record): bool => blank($record->file_tte))
                            ->url(fn (SuratTugas $record): string => route('surat-tugas.download-word', $record))
                            ->openUrlInNewTab(),

                        Action::make('download_pdf')
                            ->label('Download PDF')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('primary')
                            ->url(fn (SuratTugas $record): string => route('surat-tugas.cetak-pdf', ['suratTugas' => $record, 'download' => 1]))
                            ->openUrlInNewTab(),
                    ]),

                Action::make('tte_sakti')
                    ->label('Setujui & TTE SAKTI')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->button()
                    ->visible(function (SuratTugas $record): bool {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        return $record->status === 'draft' && ($user?->canSignSuratTugas() ?? false);
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

                Action::make('upload_tte')
                    ->label(fn (SuratTugas $record): string => filled($record->file_tte) ? 'Update Nomor & TTE' : 'Input Nomor & Upload TTE')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('primary')
                    ->button()
                    ->visible(function (): bool {
                        /** @var \App\Models\User|null $user */
                        $user = Auth::user();

                        return $user?->canManageSuratTugas() ?? false;
                    })
                    ->modalHeading('Input Nomor Surat & Upload PDF TTE SRIKANDI')
                    ->modalDescription('Masukkan nomor surat resmi dan file PDF hasil TTE dari aplikasi SRIKANDI (keduanya wajib diisi).')
                    ->modalWidth('md')
                    ->fillForm(fn (SuratTugas $record): array => [
                        'nomor_surat' => $record->nomor_surat,
                    ])
                    ->schema([
                        TextInput::make('nomor_surat')
                            ->label('Nomor Surat Resmi SRIKANDI')
                            ->placeholder('Contoh: 094/012/ST-DISPARPORA/2026')
                            ->required()
                            ->unique('surat_tugas', 'nomor_surat', ignoreRecord: true)
                            ->maxLength(100),

                        FileUpload::make('file_tte')
                            ->label('File PDF TTE SRIKANDI')
                            ->acceptedFileTypes(['application/pdf'])
                            ->disk('public')
                            ->directory('surat_tugas/tte')
                            ->maxSize(5120)
                            ->required(),
                    ])
                    ->action(function (SuratTugas $record, array $data): void {
                        $record->update([
                            'nomor_surat' => $data['nomor_surat'],
                            'file_tte' => $data['file_tte'],
                            'status' => 'terbit',
                        ]);

                        $record->kirimNotifikasiKeTim();

                        Notification::make()
                            ->title('PDF TTE SRIKANDI Berhasil Diunggah')
                            ->body('Surat Tugas resmi telah diterbitkan dan dinotifikasikan ke tim yang bertugas.')
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
