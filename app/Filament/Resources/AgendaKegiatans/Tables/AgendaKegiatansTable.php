<?php

namespace App\Filament\Resources\AgendaKegiatans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AgendaKegiatansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('jam_mulai')
                    ->label('Jam')
                    ->formatStateUsing(fn ($record) => $record->jam_selesai
                        ? substr((string) $record->jam_mulai, 0, 5) . ' - ' . substr((string) $record->jam_selesai, 0, 5)
                        : substr((string) $record->jam_mulai, 0, 5) . ' - Selesai'),
                TextColumn::make('judul')
                    ->label('Kegiatan')
                    ->searchable()
                    ->limit(45),
                TextColumn::make('tempat')
                    ->label('Tempat')
                    ->searchable()
                    ->limit(30),
                TextColumn::make('asal_undangan')
                    ->label('Asal')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('disposisis_count')
                    ->label('Disposisi')
                    ->counts('disposisis')
                    ->badge()
                    ->color('info'),
            ])
            ->defaultSort('tanggal', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
