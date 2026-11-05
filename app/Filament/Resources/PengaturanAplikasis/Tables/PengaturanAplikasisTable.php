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
                \Filament\Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->defaultImageUrl(asset('images/logo-instansi.png')),

                TextColumn::make('nama_aplikasi')
                    ->label('Nama Sistem')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('sub_nama')
                    ->label('Sub-Judul (Kepanjangan)')
                    ->searchable()
                    ->limit(45),

                TextColumn::make('nama_dinas')
                    ->label('Nama Instansi')
                    ->searchable()
                    ->weight('medium')
                    ->color('primary'),

                TextColumn::make('radius_meter')
                    ->label('Radius')
                    ->suffix(' M')
                    ->badge()
                    ->color('warning'),

                TextColumn::make('alamat_kantor')
                    ->label('Alamat Kantor')
                    ->limit(30)
                    ->toggleable(),

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
