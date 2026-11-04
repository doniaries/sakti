<?php

namespace App\Filament\Resources\HariLiburs\Tables;

use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HariLibursTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal Libur')
                    ->date('d/m/Y')
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nama')
                    ->label('Nama Hari Libur')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'nasional' => 'danger',
                        'cuti_bersama' => 'warning',
                        'khusus_daerah' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'nasional' => 'Libur Nasional',
                        'cuti_bersama' => 'Cuti Bersama',
                        'khusus_daerah' => 'Khusus Daerah',
                        default => ucfirst($state),
                    }),

                TextColumn::make('sumber')
                    ->label('Sumber')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'api' => 'success',
                        'manual' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'api' => 'API Nasional',
                        'manual' => 'Input Manual',
                        default => ucfirst($state),
                    }),

                TextColumn::make('keterangan')
                    ->label('Keterangan / Dasar Hukum')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: false),

                ToggleColumn::make('is_aktif')
                    ->label('Aktif'),
            ])
            ->defaultSort('tanggal', 'desc')
            ->filters([
                SelectFilter::make('jenis')
                    ->label('Jenis Libur')
                    ->options([
                        'nasional' => 'Libur Nasional',
                        'cuti_bersama' => 'Cuti Bersama',
                        'khusus_daerah' => 'Khusus Daerah',
                    ]),

                SelectFilter::make('sumber')
                    ->label('Sumber Data')
                    ->options([
                        'api' => 'API Nasional',
                        'manual' => 'Input Manual',
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
            ]);
    }
}
