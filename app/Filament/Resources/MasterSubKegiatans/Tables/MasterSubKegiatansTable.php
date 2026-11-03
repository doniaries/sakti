<?php

namespace App\Filament\Resources\MasterSubKegiatans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MasterSubKegiatansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Sub Kegiatan')
                    ->icon('heroicon-m-plus'),
            ])
            ->columns([
                TextColumn::make('kegiatan.nama_kegiatan')
                    ->label('Kegiatan Induk')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('kode_sub_kegiatan')
                    ->label('Kode Sub Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nama_sub_kegiatan')
                    ->label('Nama Sub Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

