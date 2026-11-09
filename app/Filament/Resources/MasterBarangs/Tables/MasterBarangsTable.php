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
                TextColumn::make('kodeRekening.kode_lengkap')
                    ->label('Kode Rekening')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('kodeRekening.uraian')
                    ->label('Uraian Rekening')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->placeholder('-'),

                TextColumn::make('nama_barang')
                    ->label('Nama Komponen Belanja')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary')
                    ->wrap()
                    ->formatStateUsing(fn(?string $state): string => mb_strtoupper((string) $state, 'UTF-8')),

                TextColumn::make('satuan.nama')
                    ->label('Satuan')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('harga_satuan')
                    ->label('Harga Standar (SSH)')
                    ->formatStateUsing(fn ($state): string => \App\Support\FormatHelper::rupiah((float) $state))
                    ->alignEnd()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('kode_barang')
                    ->label('Kode Sistem')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
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
