<?php

namespace App\Filament\Resources\MasterTokos\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MasterTokosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('bank'))
            ->columns([
                TextColumn::make('nama_toko')
                    ->label('Nama Toko')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_pemilik')
                    ->label('Nama Pemilik')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('telepon')
                    ->label('No. Telepon')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('bank.nama_bank')
                    ->label('Bank')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('nomor_rekening')
                    ->label('No. Rekening')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('alamat')
                    ->label('Alamat Toko')
                    ->limit(35)
                    ->searchable()
                    ->placeholder('-'),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
