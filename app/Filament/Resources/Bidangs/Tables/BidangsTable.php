<?php

namespace App\Filament\Resources\Bidangs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BidangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Bidang')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('kepala.name')
                    ->label('Pimpinan / Kepala')
                    ->placeholder('Belum Ditentukan')
                    ->badge()
                    ->color('primary')
                    ->searchable(),
                TextColumn::make('users_count')
                    ->label('Jml Pegawai')
                    ->counts('users')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                \Filament\Actions\Action::make('buat_bidang')
                    ->label('Tambah Bidang')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->url(fn(): string => \App\Filament\Resources\Bidangs\BidangResource::getUrl('create')),

                \Filament\Actions\Action::make('data_pegawai')
                    ->label('Data Pegawai')
                    ->icon('heroicon-o-users')
                    ->color('success')
                    ->url(fn(): string => \App\Filament\Resources\Users\UserResource::getUrl('index')),
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
