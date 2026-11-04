<?php

namespace App\Filament\Resources\MasterSubKegiatans\Tables;

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

class MasterSubKegiatansTable
{
    public static function configure(Table $table): Table
    {
        $currentYear = (int) date('Y');
        $years = [];
        for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++) {
            $years[$y] = "Tahun {$y}";
        }

        return $table
            ->modifyQueryUsing(function (Builder $query, Table $table): Builder {
                /** @var \App\Models\User|null $user */
                $user = Auth::user();
                $canViewAllBidang = $user !== null && $user->can('viewAny', Bidang::class);

                if (! $canViewAllBidang && $user !== null) {
                    $userBidangId = filled($user->bidang_id)
                        ? (int) $user->bidang_id
                        : (int) (Bidang::where('kepala_id', $user->id)->value('id') ?? 0);

                    if ($userBidangId > 0) {
                        $query->whereHas('kegiatan', fn (Builder $q) => $q->where('bidang_id', $userBidangId));
                    }
                }

                $livewire = $table->getLivewire();
                $livewireFilters = property_exists($livewire, 'tableFilters') ? $livewire->tableFilters : null;

                $selectedBidang = request()->query('bidang')
                    ?? data_get(request()->query('filters'), 'bidang_id.value')
                    ?? data_get(request()->query('tableFilters'), 'bidang_id.value')
                    ?? data_get($livewireFilters, 'bidang_id.value');

                $selectedTahun = request()->query('tahun')
                    ?? data_get(request()->query('filters'), 'tahun_anggaran.value')
                    ?? data_get(request()->query('tableFilters'), 'tahun_anggaran.value')
                    ?? data_get($livewireFilters, 'tahun_anggaran.value');

                if ((blank($selectedBidang) || blank($selectedTahun)) && request()->header('Referer')) {
                    $refererQuery = [];
                    parse_str(parse_url(request()->header('Referer'), PHP_URL_QUERY) ?? '', $refererQuery);
                    if (blank($selectedBidang)) {
                        $selectedBidang = $refererQuery['bidang']
                            ?? data_get($refererQuery, 'filters.bidang_id.value')
                            ?? data_get($refererQuery, 'tableFilters.bidang_id.value');
                    }
                    if (blank($selectedTahun)) {
                        $selectedTahun = $refererQuery['tahun']
                            ?? data_get($refererQuery, 'filters.tahun_anggaran.value')
                            ?? data_get($refererQuery, 'tableFilters.tahun_anggaran.value');
                    }
                }

                if (filled($selectedBidang)) {
                    $query->whereHas('kegiatan', fn (Builder $q) => $q->where('bidang_id', (int) $selectedBidang));
                }

                if (filled($selectedTahun)) {
                    $query->where('tahun_anggaran', (int) $selectedTahun);
                }

                return $query;
            })
            ->columns([
                TextColumn::make('tahun_anggaran')
                    ->label('Tahun')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('kegiatan.bidang.nama')
                    ->label('Bidang')
                    ->badge()
                    ->color(fn (?string $state): string => Bidang::badgeColor($state))
                    ->sortable(),

                TextColumn::make('kegiatan.nama_kegiatan')
                    ->label('Kegiatan Induk')
                    ->description(fn ($record) => $record->kegiatan?->kode_kegiatan)
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('kode_sub_kegiatan')
                    ->label('Kode Sub Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nama_sub_kegiatan')
                    ->label('Nama Sub Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tahun_anggaran')
                    ->label('Pilih Tahun')
                    ->options($years)
                    ->placeholder('Semua Tahun'),
                SelectFilter::make('kegiatan_id')
                    ->relationship('kegiatan', 'nama_kegiatan')
                    ->label('Filter Kegiatan'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Tambah Sub Kegiatan')
                    ->icon('heroicon-m-plus'),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
