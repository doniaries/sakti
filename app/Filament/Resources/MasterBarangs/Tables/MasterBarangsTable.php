<?php

namespace App\Filament\Resources\MasterBarangs\Tables;

use App\Models\MasterBarang;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MasterBarangsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query->with(['kodeRekening', 'satuan']);
            })
            ->columns([
                TextColumn::make('kode_barang')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nama_barang')
                    ->label('Nama Barang Persediaan')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('kodeRekening.uraian')
                    ->label('Rekening Belanja')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->placeholder('-'),

                TextColumn::make('satuan.nama')
                    ->label('Satuan')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('keterangan')
                    ->label('Keterangan / Spesifikasi')
                    ->searchable()
                    ->wrap()
                    ->placeholder('-'),

                TextColumn::make('harga_satuan')
                    ->label('Harga Referensi (Opsional)')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
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
