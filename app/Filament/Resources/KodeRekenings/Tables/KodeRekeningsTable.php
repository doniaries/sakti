<?php

namespace App\Filament\Resources\KodeRekenings\Tables;

use App\Models\KodeRekening;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KodeRekeningsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('level')
                    ->label('Tingkat')
                    ->badge()
                    ->state(fn (KodeRekening $record): string => $record->nama_level)
                    ->color(fn (KodeRekening $record): string => match ($record->level) {
                        1 => 'danger',
                        2 => 'warning',
                        3 => 'info',
                        4 => 'primary',
                        5 => 'gray',
                        default => 'success',
                    })
                    ->sortable(query: function (Builder $query, string $direction) {
                        return $query->orderByRaw("(LENGTH(kode_lengkap) - LENGTH(REPLACE(kode_lengkap, '.', '')) + 1) {$direction}");
                    }),

                TextColumn::make('kode_lengkap')
                    ->label('Kode Rekening')
                    ->searchable()
                    ->sortable()
                    ->fontFamily('mono')
                    ->weight(fn (KodeRekening $record): FontWeight => $record->level <= 2 ? FontWeight::Bold : ($record->level <= 4 ? FontWeight::SemiBold : FontWeight::Normal))
                    ->copyable()
                    ->copyMessage('Kode rekening disalin')
                    ->copyMessageDuration(1500),

                TextColumn::make('uraian')
                    ->label('Uraian Rekening Belanja')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight(fn (KodeRekening $record): FontWeight => $record->level <= 2 ? FontWeight::Bold : ($record->level <= 4 ? FontWeight::SemiBold : FontWeight::Normal))
                    ->formatStateUsing(fn (?string $state, KodeRekening $record): string => $record->level <= 2 ? mb_strtoupper((string) $state, 'UTF-8') : (string) $state),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('kode_lengkap', 'asc')
            ->filters([
                SelectFilter::make('tingkat')
                    ->label('Tingkat Rekening')
                    ->options([
                        '1' => 'Akun',
                        '2' => 'Kelompok',
                        '3' => 'Jenis',
                        '4' => 'Obyek',
                        '5' => 'Rincian Obyek',
                        '6' => 'Sub Rincian Obyek',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (filled($data['value'])) {
                            $dots = (int) $data['value'] - 1;
                            $query->whereRaw("(LENGTH(kode_lengkap) - LENGTH(REPLACE(kode_lengkap, '.', ''))) = ?", [$dots]);
                        }
                    }),
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
