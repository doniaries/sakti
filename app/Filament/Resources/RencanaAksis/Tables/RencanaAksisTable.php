<?php

namespace App\Filament\Resources\RencanaAksis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RencanaAksisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('jabatan.nama')
                    ->sortable(),
                TextColumn::make('bidang.nama_bidang')
                    ->sortable(),
                TextColumn::make('tahun'),
                TextColumn::make('target_jan')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_feb')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_mar')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_apr')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_mei')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_jun')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_jul')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_agu')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_sep')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_okt')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_nov')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_des')
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
