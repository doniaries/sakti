<?php

namespace App\Filament\Resources\MasterPrograms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MasterProgramsTable
{
    public static function configure(Table $table): Table
    {
        $defaultYear = \App\Models\MasterProgram::query()->max('tahun_anggaran') ?? (int) date('Y');
        $currentYear = (int) date('Y');
        $maxYear = max($defaultYear, $currentYear + 1);
        $minYear = min($defaultYear, $currentYear - 2);

        $years = [];
        for ($y = $maxYear; $y >= $minYear; $y--) {
            $years[$y] = (string) $y;
        }

        return $table
            ->modifyQueryUsing(function (Builder $query, Table $table) use ($defaultYear): Builder {
                $query->withCount('kegiatans');

                $livewire = $table->getLivewire();
                $livewireFilters = property_exists($livewire, 'tableFilters') ? $livewire->tableFilters : null;

                $selectedTahun = request()->query('tahun')
                    ?? data_get(request()->query('filters'), 'tahun_anggaran.value')
                    ?? data_get(request()->query('tableFilters'), 'tahun_anggaran.value')
                    ?? data_get($livewireFilters, 'tahun_anggaran.value');

                if (blank($selectedTahun) && request()->header('Referer')) {
                    $refererQuery = [];
                    parse_str(parse_url(request()->header('Referer'), PHP_URL_QUERY) ?? '', $refererQuery);
                    $selectedTahun = $refererQuery['tahun']
                        ?? data_get($refererQuery, 'filters.tahun_anggaran.value')
                        ?? data_get($refererQuery, 'tableFilters.tahun_anggaran.value');
                }

                $selectedTahun = filled($selectedTahun) ? (int) $selectedTahun : $defaultYear;

                $query->where('tahun_anggaran', $selectedTahun);

                return $query;
            })
            ->columns([
                TextColumn::make('tahun_anggaran')
                    ->label('Tahun')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('kode_program')
                    ->label('Kode Program')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nama_program')
                    ->label('Nama Program')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->formatStateUsing(fn (?string $state): string => mb_strtoupper((string) $state, 'UTF-8')),

                TextColumn::make('kegiatans_count')
                    ->label('Jml Kegiatan')
                    ->counts('kegiatans')
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
                    ->default($defaultYear),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\Action::make('select_tahun_anggaran')
                    ->view('filament.tables.toolbar.tahun-anggaran-select'),

                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

