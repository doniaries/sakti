<?php

namespace App\Filament\Resources\Jabatans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class JabatansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Jabatan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
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
                \Filament\Actions\Action::make('buat_jabatan')
                    ->label('Tambah Jabatan')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->url(fn (): string => \App\Filament\Resources\Jabatans\JabatanResource::getUrl('create')),

                \Filament\Actions\Action::make('data_pegawai')
                    ->label('Data Pegawai')
                    ->icon('heroicon-o-users')
                    ->color('gray')
                    ->url(fn (): string => \App\Filament\Resources\Users\UserResource::getUrl('index')),
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
