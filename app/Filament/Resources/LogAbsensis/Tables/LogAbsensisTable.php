<?php

namespace App\Filament\Resources\LogAbsensis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LogAbsensisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['user', 'mesinAbsensi']))
            ->columns([
                TextColumn::make('user.name')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('mesinAbsensi.nama')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('nomor_absensi')
                    ->searchable(),
                TextColumn::make('waktu_absensi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('sumber')
                    ->searchable(),
                TextColumn::make('metode_verifikasi')
                    ->searchable(),
                TextColumn::make('kode_kerja')
                    ->searchable(),
                TextColumn::make('kode_log_asli')
                    ->searchable(),
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
