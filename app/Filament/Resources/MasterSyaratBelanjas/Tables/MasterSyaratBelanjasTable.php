<?php

namespace App\Filament\Resources\MasterSyaratBelanjas\Tables;

use App\Models\MasterSyaratBelanja;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class MasterSyaratBelanjasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextInputColumn::make('urutan')
                    ->label('No.')
                    ->sortable()
                    ->alignCenter()
                    ->width('75px'),

                TextColumn::make('nama_syarat')
                    ->label('Nama Dokumen Persyaratan')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn (MasterSyaratBelanja $record): ?string => $record->keterangan)
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

                ToggleColumn::make('is_required')
                    ->label('Wajib')
                    ->alignCenter()
                    ->sortable(),

                ToggleColumn::make('aktif')
                    ->label('Aktif')
                    ->alignCenter()
                    ->sortable(),
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

                TernaryFilter::make('is_required')
                    ->label('Kewajiban')
                    ->placeholder('Semua Status')
                    ->trueLabel('Hanya Wajib Diunggah')
                    ->falseLabel('Hanya Dokumen Opsional'),

                TernaryFilter::make('aktif')
                    ->label('Status')
                    ->placeholder('Semua Dokumen')
                    ->trueLabel('Hanya yang Aktif')
                    ->falseLabel('Hanya Nonaktif'),
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('aktifkanSemua')
                        ->label('Aktifkan Dokumen Terpilih')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['aktif' => true])),

                    BulkAction::make('nonaktifkanSemua')
                        ->label('Nonaktifkan Dokumen Terpilih')
                        ->icon('heroicon-o-x-circle')
                        ->color('gray')
                        ->action(fn (Collection $records) => $records->each->update(['aktif' => false])),

                    BulkAction::make('wajibkanSemua')
                        ->label('Wajibkan Dokumen Terpilih')
                        ->icon('heroicon-o-exclamation-circle')
                        ->color('warning')
                        ->action(fn (Collection $records) => $records->each->update(['is_required' => true])),

                    BulkAction::make('opsionalkanSemua')
                        ->label('Jadikan Opsional Dokumen Terpilih')
                        ->icon('heroicon-o-information-circle')
                        ->color('info')
                        ->action(fn (Collection $records) => $records->each->update(['is_required' => false])),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('urutan');
    }
}
