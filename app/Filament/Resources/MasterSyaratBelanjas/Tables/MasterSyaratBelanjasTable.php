<?php

namespace App\Filament\Resources\MasterSyaratBelanjas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MasterSyaratBelanjasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('urutan')
                    ->label('No.')
                    ->sortable()
                    ->alignCenter()
                    ->width('60px'),

                TextColumn::make('nama_syarat')
                    ->label('Nama Dokumen Persyaratan')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('jenis_belanja')
                    ->label('Jenis Belanja')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'semua' => 'Semua Belanja',
                        'barang_jasa' => 'Barang & Jasa',
                        'makan_minum' => 'Makan Minum',
                        'sppd' => 'SPPD',
                        'bbm' => 'BBM',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'semua' => 'gray',
                        'barang_jasa' => 'primary',
                        'makan_minum' => 'warning',
                        'sppd' => 'success',
                        'bbm' => 'danger',
                        default => 'secondary',
                    })
                    ->sortable(),

                TextColumn::make('metode_pengadaan')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'semua' => 'Semua',
                        'e_katalog' => 'E-Katalog',
                        'non_katalog' => 'Non-Katalog',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'e_katalog' => 'info',
                        'non_katalog' => 'gray',
                        default => 'secondary',
                    })
                    ->sortable(),

                IconColumn::make('is_required')
                    ->label('Wajib')
                    ->boolean()
                    ->alignCenter(),

                IconColumn::make('aktif')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('jenis_belanja')
                    ->label('Jenis Belanja')
                    ->options([
                        'semua' => 'Semua Jenis',
                        'barang_jasa' => 'Barang & Jasa',
                        'makan_minum' => 'Makan Minum',
                        'sppd' => 'SPPD',
                        'bbm' => 'BBM',
                    ]),

                SelectFilter::make('metode_pengadaan')
                    ->label('Metode Pengadaan')
                    ->options([
                        'semua' => 'Semua Metode',
                        'e_katalog' => 'E-Katalog',
                        'non_katalog' => 'Non-Katalog',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('urutan');
    }
}

