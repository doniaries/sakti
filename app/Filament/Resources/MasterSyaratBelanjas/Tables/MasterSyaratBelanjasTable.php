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
use Filament\Tables\Grouping\Group;
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
                    ->width('70px'),

                TextColumn::make('nama_syarat')
                    ->label('Nama Dokumen Persyaratan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (MasterSyaratBelanja $record): ?string => $record->keterangan)
                    ->wrap(),

                TextColumn::make('jenis_belanja')
                    ->label('Jenis Pekerjaan')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'semua' => 'Dokumen Umum',
                        'barang_jasa' => 'Barang & Jasa',
                        'makan_minum' => 'Makan Minum',
                        'sppd' => 'SPPD',
                        'bbm' => 'BBM',
                        default => ucwords(str_replace('_', ' ', $state)),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'semua' => 'gray',
                        'barang_jasa' => 'primary',
                        'makan_minum' => 'warning',
                        'sppd' => 'success',
                        'bbm' => 'danger',
                        default => 'info',
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
            ->groups([
                Group::make('jenis_belanja')
                    ->label('Kelompok Pekerjaan')
                    ->getTitleFromRecordUsing(fn (MasterSyaratBelanja $record): string => match ($record->jenis_belanja) {
                        'semua' => 'Dokumen Umum / Lampiran Tambahan',
                        'barang_jasa' => 'Non-Katalog: Belanja Barang & Jasa',
                        'makan_minum' => 'Non-Katalog: Makan & Minum Rapat',
                        'sppd' => 'Non-Katalog: Perjalanan Dinas (SPPD)',
                        'bbm' => 'Non-Katalog: Bahan Bakar Minyak (BBM)',
                        default => 'Pekerjaan: ' . ucwords(str_replace('_', ' ', $record->jenis_belanja)),
                    })
                    ->collapsible(),
            ])
            ->defaultGroup('jenis_belanja')
            ->reorderable('urutan')
            ->defaultSort('urutan')
            ->filters([
                SelectFilter::make('metode_pengadaan')
                    ->label('Metode Pengadaan')
                    ->options([
                        'non_katalog' => 'Non-Katalog (Pengadaan Langsung)',
                        'e_katalog' => 'E-Katalog LKPP (E-Purchasing)',
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
            ]);
    }
}
