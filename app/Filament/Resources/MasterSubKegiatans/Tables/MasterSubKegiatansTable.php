<?php

namespace App\Filament\Resources\MasterSubKegiatans\Tables;

use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Models\Bidang;
use App\Models\MasterSubKegiatan;
use App\Support\FormatHelper;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MasterSubKegiatansTable
{
    public static function configure(Table $table): Table
    {
        $defaultYear = MasterSubKegiatan::query()->max('tahun_anggaran') ?? (int) date('Y');
        $currentYear = (int) date('Y');
        $maxYear = max($defaultYear, $currentYear + 1);
        $minYear = min($defaultYear, $currentYear - 2);

        $years = [];
        for ($y = $maxYear; $y >= $minYear; $y--) {
            $years[$y] = (string) $y;
        }

        return $table
            ->recordUrl(fn (MasterSubKegiatan $record): string => MasterSubKegiatanResource::getUrl('edit', ['record' => $record]))
            ->modifyQueryUsing(function (Builder $query, Table $table) use ($defaultYear): Builder {
                $query->with(['kegiatan.bidang', 'kegiatan.program', 'jf'])
                    ->withSum('rincianAnggarans as total_pagu', 'pagu_total')
                    ->withSum('rincianAnggarans as total_realisasi', 'nilai_realisasi')
                    ->withCount('rincianAnggarans');

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

                TextColumn::make('kegiatan.bidang.nama')
                    ->label('Bidang')
                    ->badge()
                    ->color(fn (?string $state): string => Bidang::badgeColor($state))
                    ->sortable(),

                TextColumn::make('kegiatan.nama_kegiatan')
                    ->label('Kegiatan Induk')
                    ->description(fn (MasterSubKegiatan $record): ?string => $record->kegiatan?->kode_kegiatan)
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->extraAttributes(['style' => 'min-width: 250px;'])
                    ->extraHeaderAttributes(['style' => 'min-width: 250px;'])
                    ->formatStateUsing(fn (?string $state): string => mb_strtoupper((string) $state, 'UTF-8')),

                TextColumn::make('nama_sub_kegiatan')
                    ->label('Sub Kegiatan')
                    ->description(fn (MasterSubKegiatan $record): ?string => $record->kode_sub_kegiatan)
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap()
                    ->extraAttributes(['style' => 'min-width: 300px;'])
                    ->extraHeaderAttributes(['style' => 'min-width: 300px;'])
                    ->formatStateUsing(fn (?string $state): string => mb_strtoupper((string) $state, 'UTF-8')),

                TextColumn::make('jf.name')
                    ->label('PPTK')
                    ->placeholder('Belum Ditugaskan')
                    ->badge()
                    ->color(fn ($state) => $state ? 'info' : 'gray')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_pagu')
                    ->label('Pagu Anggaran')
                    ->formatStateUsing(fn ($state) => FormatHelper::rupiah((float) $state))
                    ->weight('bold')
                    ->alignEnd()
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total Pagu Seluruh Sub Kegiatan')
                            ->formatStateUsing(fn ($state) => FormatHelper::rupiah((float) $state))
                    ),

                TextColumn::make('total_realisasi')
                    ->label('Realisasi SPJ')
                    ->formatStateUsing(fn ($state) => FormatHelper::rupiah((float) $state))
                    ->alignEnd()
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total Realisasi SPJ')
                            ->formatStateUsing(fn ($state) => FormatHelper::rupiah((float) $state))
                    ),

                TextColumn::make('sisa_pagu')
                    ->label('Sisa Pagu')
                    ->state(fn (MasterSubKegiatan $record): string => FormatHelper::rupiah($record->sisa_pagu))
                    ->color(fn (MasterSubKegiatan $record): string => $record->sisa_pagu > 0 ? 'success' : 'danger')
                    ->weight('bold')
                    ->alignEnd(),

                TextColumn::make('rincian_anggarans_count')
                    ->label('Item Belanja')
                    ->badge()
                    ->color(fn ($state) => (int) $state > 0 ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state) => "{$state} Item")
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('tahun_anggaran')
                    ->label('Tahun Anggaran')
                    ->options($years)
                    ->default($defaultYear),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('kelola_rka')
                    ->label('Kelola Item RKA')
                    ->icon('heroicon-o-table-cells')
                    ->color('primary')
                    ->url(fn (MasterSubKegiatan $record): string => MasterSubKegiatanResource::getUrl('edit', ['record' => $record])),

                EditAction::make()->label('Ubah'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                Action::make('select_tahun_anggaran')
                    ->view('filament.tables.toolbar.tahun-anggaran-select'),

                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
