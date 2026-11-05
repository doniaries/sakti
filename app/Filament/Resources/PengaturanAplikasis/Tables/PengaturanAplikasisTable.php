<?php

namespace App\Filament\Resources\PengaturanAplikasis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PengaturanAplikasisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_aplikasi')
                    ->label('Nama Sistem')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('sub_nama')
                    ->label('Sub-Judul (Kepanjangan)')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('radius_meter')
                    ->label('Radius Presensi')
                    ->suffix(' M')
                    ->badge()
                    ->color('warning'),

                TextColumn::make('versi_aplikasi')
                    ->label('Versi')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                TextColumn::make('pembuat_aplikasi')
                    ->label('Pengembang')
                    ->searchable(),

                TextColumn::make('kontak_bantuan')
                    ->label('Kontak / Bantuan')
                    ->searchable(),
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
