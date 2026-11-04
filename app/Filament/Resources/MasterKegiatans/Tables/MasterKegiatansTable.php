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
        $currentYear = (int) date('Y');
        $years = [];
        for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++) {
            $years[$y] = "Tahun {$y}";
        }

        return $table
            ->modifyQueryUsing(function (Builder $query, Table $table): Builder {
                $query->with(['bidang', 'program'])->withCount('subKegiatans');

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
                    $query->where('bidang_id', (int) $selectedBidang);
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

                TextColumn::make('bidang.nama')
                    ->label('Bidang Pengampu')
                    ->badge()
                    ->color(fn (?string $state): string => Bidang::badgeColor($state))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('program.nama_program')
                    ->label('Program Induk')
                    ->description(fn ($record) => $record->program?->kode_program)
                    ->searchable()
                    ->sortable()
                    ->wrap(),

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
                SelectFilter::make('tahun_anggaran')
                    ->label('Pilih Tahun')
                    ->options($years)
                    ->placeholder('Semua Tahun'),
                SelectFilter::make('bidang_id')
                    ->relationship('bidang', 'nama')
                    ->label('Filter Bidang'),
                SelectFilter::make('program_id')
                    ->relationship('program', 'nama_program')
                    ->label('Filter Program'),
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
