<?php

declare(strict_types=1);

namespace App\Filament\Resources\BeritaAcaraBmd\Tables;

use App\Models\BeritaAcaraBmd;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class BeritaAcaraBmdTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('items'))
            ->columns([
                TextColumn::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('jenis')
                    ->label('Jenis BA')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'peminjaman' ? 'Peminjaman Barang' : 'Penggunaan BMD')
                    ->color(fn (string $state): string => $state === 'peminjaman' ? 'warning' : 'info'),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('pihak_pertama_nama')
                    ->label('Pihak Pertama')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('pihak_kedua_nama')
                    ->label('Pihak Kedua')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('items_count')
                    ->label('Jml Barang')
                    ->alignCenter()
                    ->badge()
                    ->color('success'),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                SelectFilter::make('jenis')
                    ->label('Jenis Berita Acara')
                    ->options([
                        'penggunaan' => 'Penggunaan BMD',
                        'peminjaman' => 'Peminjaman Barang',
                    ]),
            ])
            ->recordActions([
                Action::make('preview_pdf')
                    ->label('Preview / Cetak')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->modalHeading(fn (BeritaAcaraBmd $record): string => 'Pratinjau ' . $record->nomor_surat)
                    ->modalWidth(Width::FiveExtraLarge)
                    ->modalContent(fn (BeritaAcaraBmd $record): HtmlString => new HtmlString(
                        '<iframe src="' . route('admin.berita-acara-bmd.cetak', ['beritaAcaraBmd' => $record->id, 'preview' => 1]) . '" class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white" style="height: 75vh;"></iframe>'
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->extraModalFooterActions([
                        Action::make('download_pdf')
                            ->label('Unduh PDF')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('danger')
                            ->url(fn (BeritaAcaraBmd $record): string => route('admin.berita-acara-bmd.cetak', ['beritaAcaraBmd' => $record->id]))
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
