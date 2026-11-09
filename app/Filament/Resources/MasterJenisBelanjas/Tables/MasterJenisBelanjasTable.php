<?php

namespace App\Filament\Resources\MasterJenisBelanjas\Tables;

use App\Filament\Resources\MasterJenisBelanjas\MasterJenisBelanjaResource;
use App\Models\MasterJenisBelanja;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MasterJenisBelanjasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('urutan')
            ->defaultSort('urutan')
            ->recordUrl(fn (MasterJenisBelanja $record): string => MasterJenisBelanjaResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('metode_pengadaan')
                    ->label('Metode Pengadaan')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'non_katalog' => 'Non-Katalog (Pengadaan Langsung)',
                        'e_katalog' => 'E-Katalog LKPP (E-Purchasing)',
                        default => 'Semua Metode',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'e_katalog' => 'info',
                        'non_katalog' => 'gray',
                        default => 'secondary',
                    })
                    ->sortable(),

                TextColumn::make('nama')
                    ->label('Jenis Pekerjaan / Belanja')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (MasterJenisBelanja $record): ?string => $record->deskripsi)
                    ->wrap(),

                TextColumn::make('syarat_belanjas_count')
                    ->counts('syaratBelanjas')
                    ->label('Syarat Dokumen SOP Terkait')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => "{$state} Dokumen SOP")
                    ->color(fn ($state): string => (int) $state > 0 ? 'success' : 'danger')
                    ->alignCenter(),

                ToggleColumn::make('aktif')
                    ->label('Aktif')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('metode_pengadaan')
                    ->label('Metode Pengadaan')
                    ->options([
                        'non_katalog' => 'Non-Katalog (Pengadaan Langsung)',
                        'e_katalog' => 'E-Katalog LKPP (E-Purchasing)',
                    ]),

                TernaryFilter::make('aktif')
                    ->label('Status Aktif'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Kelola Syarat Dokumen')
                    ->icon('heroicon-o-document-check')
                    ->color('primary')
                    ->tooltip('Klik untuk melihat dan mengatur syarat dokumen pekerjaan ini'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
