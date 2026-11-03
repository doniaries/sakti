<?php

namespace App\Filament\Resources\MasterKegiatans\Tables;

use App\Models\Bidang;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MasterKegiatansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                /** @var \App\Models\User|null $user */
                $user = Auth::user();
                $canViewAllBidang = $user !== null && $user->can('viewAny', Bidang::class);

                if (! $canViewAllBidang && $user !== null) {
                    $userBidangId = filled($user->bidang_id)
                        ? (int) $user->bidang_id
                        : (int) (Bidang::where('kepala_id', $user->id)->value('id') ?? 0);

                    if ($userBidangId > 0) {
                        $query->where('bidang_id', $userBidangId);
                    }
                }

                return $query;
            })
            ->columns([
                TextColumn::make('bidang.nama')
                    ->label('Bidang Pengampu')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kode_kegiatan')
                    ->label('Kode Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nama_kegiatan')
                    ->label('Nama Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('pagu_anggaran')
                    ->label('Jumlah Uang Kegiatan')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('sub_kegiatans_count')
                    ->label('Jml Sub Kegiatan')
                    ->counts('subKegiatans')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('bidang_id')
                    ->relationship('bidang', 'nama')
                    ->label('Filter Bidang'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Tambah Kegiatan')
                    ->icon('heroicon-m-plus'),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
