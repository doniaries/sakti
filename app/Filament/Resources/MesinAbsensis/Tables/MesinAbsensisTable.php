<?php

namespace App\Filament\Resources\MesinAbsensis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class MesinAbsensisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('merek')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('model')
                    ->searchable(),
                TextColumn::make('alamat_ip')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('port')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('nomor_seri')
                    ->searchable(),
                TextColumn::make('jenis_koneksi')
                    ->sortable(),
                ToggleColumn::make('aktif')
                    ->sortable(),
                TextColumn::make('sinkronisasi_terakhir')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
