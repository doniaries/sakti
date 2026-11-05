<?php

namespace App\Filament\Resources\MasterTujuanBbms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MasterTujuanBbmsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_tujuan')
                    ->label('Nama Tujuan / Nagari')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('kategori_wilayah')
                    ->label('Wilayah')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'dalam_kabupaten' => 'Dalam Kabupaten',
                        'luar_kabupaten' => 'Luar Kabupaten',
                        'luar_provinsi' => 'Luar Provinsi',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'dalam_kabupaten' => 'primary',
                        'luar_kabupaten' => 'warning',
                        'luar_provinsi' => 'danger',
                        default => 'secondary',
                    })
                    ->sortable(),

                TextColumn::make('kuota_liter')
                    ->label('Kuota BBM')
                    ->numeric(2)
                    ->suffix(' Liter')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('jarak_km')
                    ->label('Jarak')
                    ->numeric(1)
                    ->suffix(' KM')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),

                IconColumn::make('aktif')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('kategori_wilayah')
                    ->label('Kategori Wilayah')
                    ->options([
                        'dalam_kabupaten' => 'Dalam Kabupaten',
                        'luar_kabupaten' => 'Luar Kabupaten',
                        'luar_provinsi' => 'Luar Provinsi',
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
            ->defaultSort('nama_tujuan');
    }
}
