<?php

namespace App\Filament\Resources\JadwalKerjas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class JadwalKerjasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Shift / Jadwal')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('kode')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                TextColumn::make('jam_senin_kamis')
                    ->label('Senin - Kamis')
                    ->state(fn ($record) => substr($record->jam_masuk_senin_kamis, 0, 5) . ' - ' . substr($record->jam_pulang_senin_kamis, 0, 5) . ' WIB'),

                TextColumn::make('jam_jumat')
                    ->label('Jumat')
                    ->state(fn ($record) => substr($record->jam_masuk_jumat, 0, 5) . ' - ' . substr($record->jam_pulang_jumat, 0, 5) . ' WIB'),

                TextColumn::make('toleransi_terlambat_menit')
                    ->label('Toleransi')
                    ->suffix(' mnt')
                    ->sortable(),

                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->sortable(),

                ToggleColumn::make('is_aktif')
                    ->label('Aktif'),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
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
