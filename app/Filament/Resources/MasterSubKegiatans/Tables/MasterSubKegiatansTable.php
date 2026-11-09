<?php

namespace App\Filament\Resources\MasterSubKegiatans\Tables;

use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Models\Bidang;
use App\Models\MasterKegiatan;
use App\Models\MasterSubKegiatan;
use App\Models\User;
use App\Support\FormatHelper;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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
            ->recordActionsPosition(RecordActionsPosition::BeforeColumns)
            ->recordActionsColumnLabel('Aksi')
            ->stickyableColumns()
            ->recordUrl(fn(MasterSubKegiatan $record): string => MasterSubKegiatanResource::getUrl('edit', ['record' => $record]))
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
                TextColumn::make('nama_sub_kegiatan')
                    ->label('Sub Kegiatan')
                    ->description(fn(MasterSubKegiatan $record): string => ($record->kode_sub_kegiatan ? "[{$record->kode_sub_kegiatan}] " : '') . ($record->kegiatan?->nama_kegiatan ? "Kegiatan: {$record->kegiatan->nama_kegiatan}" : ''))
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap()
                    ->extraAttributes(['style' => 'min-width: 320px;'], merge: true)
                    ->extraHeaderAttributes(['style' => 'min-width: 320px;'], merge: true)
                    ->formatStateUsing(fn(?string $state): string => mb_strtoupper((string) $state, 'UTF-8'))
                    ->sticky(),

                TextColumn::make('jf.name')
                    ->label('Penanggung Jawab (JF)')
                    ->placeholder('Belum Ditugaskan')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'danger')
                    ->icon(fn($state) => $state ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->action(
                        Action::make('ganti_jf_inline')
                            ->modalHeading(fn(MasterSubKegiatan $record): string => "Penugasan JF: {$record->nama_sub_kegiatan}")
                            ->modalDescription('Pilih Pejabat Fungsional (JF) yang bertanggung jawab atas Sub Kegiatan ini.')
                            ->visible(fn() => auth()->user()?->hasAnyRole(['pptk', 'super_admin', 'admin', 'pa']))
                            ->form([
                                Select::make('jf_id')
                                    ->label('Pejabat Fungsional (JF)')
                                    ->options(function (MasterSubKegiatan $record): array {
                                        $bidangId = $record->kegiatan?->bidang_id;

                                        return User::query()
                                            ->where('aktif', true)
                                            ->when($bidangId, fn($q) => $q->where('bidang_id', $bidangId))
                                            ->whereDoesntHave('roles', fn($q) => $q->whereIn('name', ['pa', 'ppk']))
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->all();
                                    })
                                    ->default(fn(MasterSubKegiatan $record) => $record->jf_id)
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ])
                            ->action(function (MasterSubKegiatan $record, array $data): void {
                                $record->update(['jf_id' => $data['jf_id']]);
                                Notification::make()
                                    ->title('Penugasan JF Berhasil Disimpan')
                                    ->success()
                                    ->send();
                            })
                    ),

                TextColumn::make('total_pagu')
                    ->label('Pagu Anggaran')
                    ->formatStateUsing(fn($state) => FormatHelper::rupiah((float) $state))
                    ->weight('bold')
                    ->alignEnd()
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total Pagu')
                            ->formatStateUsing(fn($state) => FormatHelper::rupiah((float) $state))
                    ),

                TextColumn::make('total_realisasi')
                    ->label('Realisasi SPJ')
                    ->formatStateUsing(fn($state) => FormatHelper::rupiah((float) $state))
                    ->alignEnd()
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->label('Total Realisasi')
                            ->formatStateUsing(fn($state) => FormatHelper::rupiah((float) $state))
                    ),

                TextColumn::make('sisa_pagu')
                    ->label('Sisa Pagu')
                    ->state(fn(MasterSubKegiatan $record): string => FormatHelper::rupiah($record->sisa_pagu))
                    ->color(fn(MasterSubKegiatan $record): string => $record->sisa_pagu > 0 ? 'success' : 'danger')
                    ->weight('bold')
                    ->alignEnd(),

                TextColumn::make('rincian_anggarans_count')
                    ->label('Item Belanja')
                    ->badge()
                    ->color(fn($state) => (int) $state > 0 ? 'info' : 'gray')
                    ->formatStateUsing(fn($state) => "{$state} Item")
                    ->alignCenter(),

                TextColumn::make('tahun_anggaran')
                    ->label('Tahun')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('kegiatan.bidang.nama')
                    ->label('Bidang')
                    ->badge()
                    ->color(fn(?string $state): string => Bidang::badgeColor($state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tahun_anggaran')
                    ->label('Tahun Anggaran')
                    ->options($years)
                    ->default($defaultYear),

                TernaryFilter::make('penugasan_jf')
                    ->label('Status Penugasan JF')
                    ->placeholder('Semua Sub Kegiatan')
                    ->trueLabel('Sudah Ditugaskan')
                    ->falseLabel('Belum Ditugaskan (Perlu JF)')
                    ->queries(
                        true: fn(Builder $q) => $q->whereNotNull('jf_id'),
                        false: fn(Builder $q) => $q->whereNull('jf_id'),
                    ),

                SelectFilter::make('jf_id')
                    ->label('Filter JF')
                    ->options(fn() => User::where('aktif', true)->whereHas('roles', fn($q) => $q->where('name', 'jf'))->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->placeholder('Semua JF'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('kelola_rka')
                    ->label('Rincian Belanja RKA')
                    ->icon('heroicon-o-table-cells')
                    ->color('primary')
                    ->url(fn(MasterSubKegiatan $record): string => MasterSubKegiatanResource::getUrl('edit', ['record' => $record])),

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
