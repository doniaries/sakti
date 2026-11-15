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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
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
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('nama_kegiatan')
                    ->label('Kegiatan')
                    ->searchable()
                    ->wrap()
                    ->limit(60),

                TextColumn::make('tanggal_mulai')
                    ->label('Tgl Mulai')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('tanggal_selesai')
                    ->label('Tgl Selesai')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('lokasi')
                    ->label('Lokasi')
                    ->searchable()
                    ->placeholder('-')
                    ->limit(35),

                TextColumn::make('pegawais_count')
                    ->label('Tim Bertugas')
                    ->badge()
                    ->color('info')
                    ->suffix(' Orang')
                    ->sortable(),

                TextColumn::make('penandatangan.name')
                    ->label('Penandatangan')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Buat Surat Tugas')
                    ->icon('heroicon-o-plus'),
            ])
            ->recordActions([
                Action::make('cetak_pdf')
                    ->label('Preview & Cetak PDF')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->modalHeading(fn (SuratTugas $record): string => 'Preview Surat Tugas — ' . $record->nomor_surat)
                    ->modalWidth('5xl')
                    ->modalContent(fn (SuratTugas $record): HtmlString => new HtmlString(
                        '<iframe src="' . route('surat-tugas.cetak-pdf', ['suratTugas' => $record, 'preview' => 1]) . '" class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white" style="height: 75vh;"></iframe>'
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->extraModalFooterActions([
                        Action::make('download_pdf')
                            ->label('Download / Cetak PDF')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('primary')
                            ->url(fn (SuratTugas $record): string => route('surat-tugas.cetak-pdf', $record))
                            ->openUrlInNewTab(),
                    ]),

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
