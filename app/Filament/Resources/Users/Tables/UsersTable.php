<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('nip')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('jabatan.nama')
                    ->label('Jabatan')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('bidang.nama')
                    ->label('Bidang')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('nomor_absensi')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('roles.name')->badge(),
                \Filament\Tables\Columns\ToggleColumn::make('aktif'),
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
