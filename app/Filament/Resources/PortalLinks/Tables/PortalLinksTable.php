<?php

namespace App\Filament\Resources\PortalLinks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class PortalLinksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('urutan', 'asc')
            ->columns([
                TextColumn::make('urutan')
                    ->label('#')
                    ->sortable()
                    ->alignCenter()
                    ->width('60px'),

                TextColumn::make('judul')
                    ->label('Nama Fitur / Kartu')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->deskripsi ? \Illuminate\Support\Str::limit($record->deskripsi, 60) : null),

                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'keuangan' => 'Keuangan & SPJ',
                        'kepegawaian' => 'Kepegawaian',
                        'kinerja' => 'Kinerja & LHK',
                        'administrasi' => 'Administrasi',
                        'panduan' => 'Panduan',
                        'eksternal' => 'Eksternal',
                        default => ucfirst($state),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'keuangan' => 'success',
                        'kepegawaian' => 'info',
                        'kinerja' => 'warning',
                        'administrasi' => 'primary',
                        'panduan' => 'gray',
                        default => 'secondary',
                    })
                    ->sortable(),

                TextColumn::make('warna')
                    ->label('Warna')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'emerald' => 'success',
                        'amber' => 'warning',
                        'rose' => 'danger',
                        'sky', 'teal', 'indigo', 'purple' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('bentuk')
                    ->label('Bentuk')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('url')
                    ->label('Tautan')
                    ->limit(35)
                    ->icon('heroicon-o-link')
                    ->copyable()
                    ->tooltip(fn ($record) => $record->url),

                IconColumn::make('lampiran_path')
                    ->label('Lampiran')
                    ->alignCenter()
                    ->icon(fn ($state) => $state ? 'heroicon-o-paper-clip' : null)
                    ->color('primary')
                    ->tooltip(fn ($state) => $state ? 'Memiliki file lampiran' : null),

                ToggleColumn::make('is_aktif')
                    ->label('Aktif')
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

