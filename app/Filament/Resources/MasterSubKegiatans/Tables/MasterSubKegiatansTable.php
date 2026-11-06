<?php

namespace App\Filament\Resources\MasterSubKegiatans\Tables;

use App\Models\Bidang;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MasterSubKegiatansTable
{
    public static function configure(Table $table): Table
    {
        $currentYear = (int) date('Y');
        $years = \App\Models\MasterSubKegiatan::query()
            ->distinct()
            ->orderBy('tahun_anggaran', 'desc')
            ->pluck('tahun_anggaran', 'tahun_anggaran')
            ->map(fn ($y) => "Tahun {$y}" . ((int) $y === $currentYear ? ' (Aktif)' : ''))
            ->toArray();

        if (empty($years)) {
            $years = [$currentYear => "Tahun {$currentYear} (Aktif)"];
        }

        return $table
            ->modifyQueryUsing(function (Builder $query, Table $table): Builder {
                $query->with(['kegiatan.bidang', 'kegiatan.program', 'jf']);

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

                $selectedTahun = filled($selectedTahun) ? (int) $selectedTahun : (int) date('Y');

                $query->where('tahun_anggaran', $selectedTahun);

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
                    ->wrap()
                    ->formatStateUsing(fn (?string $state): string => mb_strtoupper((string) $state, 'UTF-8')),

                TextColumn::make('kode_sub_kegiatan')
                    ->label('Kode Sub Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nama_sub_kegiatan')
                    ->label('Nama Sub Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->formatStateUsing(fn (?string $state): string => mb_strtoupper((string) $state, 'UTF-8')),

                TextColumn::make('jf.name')
                    ->label('JF Pengampu')
                    ->placeholder('Belum Ditugaskan')
                    ->badge()
                    ->color(fn ($state) => $state ? 'info' : 'gray')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tahun_anggaran')
                    ->label('Tahun Anggaran')
                    ->options($years)
                    ->default((int) date('Y')),
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
