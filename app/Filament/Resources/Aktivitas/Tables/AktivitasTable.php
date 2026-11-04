<?php

namespace App\Filament\Resources\Aktivitas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AktivitasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('jabatan.nama')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('bidang.nama')
                    ->label('Bidang')
                    ->badge()
                    ->color(fn (?string $state): string => \App\Models\Bidang::badgeColor($state))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('kode')
                    ->searchable(),
                TextColumn::make('nama_aktivitas')
                    ->searchable(),
                TextColumn::make('menit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc')
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
