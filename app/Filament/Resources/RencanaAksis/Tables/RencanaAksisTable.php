<?php

namespace App\Filament\Resources\RencanaAksis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RencanaAksisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'jabatan:id,nama',
                'bidang:id,nama',
            ]))
            ->columns([
                TextColumn::make('level')
                    ->label('Level')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'kabid' => 'Kabid',
                        'jf' => 'JF',
                        'staf' => 'Staf',
                        default => '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'kabid' => 'primary',
                        'jf' => 'info',
                        'staf' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('sasaran_sub_kegiatan')
                    ->label('Sub Kegiatan')
                    ->wrap()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('rencana_aksi')
                    ->label('Rencana Aksi')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('jenis')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state === 'trajectory' ? 'Trajectory' : 'Non-Trajectory')
                    ->color(fn (?string $state): string => $state === 'trajectory' ? 'warning' : 'gray'),
                TextColumn::make('satuan')
                    ->label('Satuan')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('jabatan.nama')
                    ->label('Jabatan')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('bidang.nama')
                    ->label('Bidang')
                    ->badge()
                    ->color(fn (?string $state): string => \App\Models\Bidang::badgeColor($state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tahun')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('target_jan')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_feb')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_mar')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_apr')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_mei')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_jun')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_jul')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_agu')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_sep')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_okt')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_nov')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_des')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('level')
                    ->label('Level Jabatan')
                    ->options([
                        'kabid' => 'Kabid',
                        'jf' => 'Jabatan Fungsional (JF)',
                        'staf' => 'Staf / Pelaksana',
                    ]),
                SelectFilter::make('sasaran_sub_kegiatan')
                    ->label('Sub Kegiatan')
                    ->options(fn (): array => \App\Models\RencanaAksi::query()
                        ->whereNotNull('sasaran_sub_kegiatan')
                        ->distinct()
                        ->orderBy('sasaran_sub_kegiatan')
                        ->pluck('sasaran_sub_kegiatan', 'sasaran_sub_kegiatan')
                        ->all())
                    ->searchable(),
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
