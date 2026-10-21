<?php

namespace App\Filament\Resources\JenisIzins\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class JenisIzinsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Tipe')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('kategori')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'cuti' => 'warning',
                        'sakit' => 'danger',
                        'wfh' => 'info',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn ($state) => strtoupper($state ?? '-'))
                    ->sortable(),

                TextColumn::make('kode')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('kuota_hari')
                    ->label('Kuota / Thn')
                    ->suffix(' hari')
                    ->placeholder('Tanpa Batas')
                    ->sortable(),

                IconColumn::make('butuh_lampiran')
                    ->label('Wajib Lampiran')
                    ->boolean()
                    ->sortable(),

                ToggleColumn::make('aktif')
                    ->label('Aktif'),
            ])
            ->filters([
                SelectFilter::make('kategori')
                    ->options([
                        'izin' => 'Izin',
                        'sakit' => 'Sakit',
                        'cuti' => 'Cuti',
                        'wfh' => 'Work From Home (WFH)',
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
