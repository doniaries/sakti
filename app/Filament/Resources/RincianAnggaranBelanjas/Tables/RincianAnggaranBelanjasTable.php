<?php

namespace App\Filament\Resources\RincianAnggaranBelanjas\Tables;

use App\Models\Bidang;
use App\Models\KodeRekening;
use App\Models\MasterSubKegiatan;
use App\Models\RincianAnggaranBelanja;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RincianAnggaranBelanjasTable
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
                $query->with(['bidang', 'subKegiatan.kegiatan', 'kodeRekening', 'masterBarang']);

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

                if (filled($selectedTahun)) {
                    $query->where('tahun_anggaran', (int) $selectedTahun);
                }

                return $query;
            })
            ->columns([
                TextColumn::make('tahun_anggaran')
                    ->label('Tahun')
                    ->badge()
                    ->sticky()
                    ->color('primary')
                    ->sortable(),

                TextColumn::make('bidang.nama')
                    ->label('Bidang')
                    ->badge()
                    ->sticky()
                    ->color(fn(?string $state): string => Bidang::badgeColor($state))
                    ->sortable(),

                TextColumn::make('subKegiatan.nama_sub_kegiatan')
                    ->label('Sub Kegiatan')
                    ->description(fn(RincianAnggaranBelanja $r) => $r->subKegiatan?->kode_sub_kegiatan)
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->formatStateUsing(fn(?string $state): string => mb_strtoupper((string) $state, 'UTF-8')),

                TextColumn::make('kodeRekening.uraian')
                    ->label('Rekening Belanja')
                    ->description(fn(RincianAnggaranBelanja $r) => $r->kodeRekening?->kode_lengkap)
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->wrap()
                    ->formatStateUsing(fn(?string $state): string => mb_strtoupper((string) $state, 'UTF-8')),

                TextColumn::make('nama_item')
                    ->label('Uraian Barang / Belanja RKA')
                    ->description(fn(RincianAnggaranBelanja $r) => $r->spesifikasi)
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap()
                    ->formatStateUsing(fn(?string $state): string => mb_strtoupper((string) $state, 'UTF-8')),

                TextColumn::make('pagu_volume')
                    ->label('Pagu Volume')
                    ->state(fn(RincianAnggaranBelanja $r) => number_format((float) $r->volume, 0, ',', '.') . ' ' . $r->satuan)
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('harga_satuan')
                    ->label('Harga Standar')
                    ->formatStateUsing(fn($state) => \App\Support\FormatHelper::rupiah((float) $state))
                    ->sortable(),

                TextColumn::make('pagu_total')
                    ->label('Total Pagu Anggaran')
                    ->formatStateUsing(fn($state) => \App\Support\FormatHelper::rupiah((float) $state))
                    ->weight('bold')
                    ->sortable(),

                TextColumn::make('realisasi')
                    ->label('Realisasi SPJ')
                    ->state(function (RincianAnggaranBelanja $r): string {
                        $vol = number_format((float) $r->volume_realisasi, 0, ',', '.') . ' ' . $r->satuan;
                        $rp = 'Rp ' . number_format((float) $r->nilai_realisasi, 0, ',', '.');
                        return "{$vol} ({$rp})";
                    })
                    ->badge()
                    ->color(fn(RincianAnggaranBelanja $r) => (float) $r->volume_realisasi > 0 ? 'warning' : 'gray'),

                TextColumn::make('sisa_pagu')
                    ->label('Sisa Pagu (Volume & Rp)')
                    ->state(function (RincianAnggaranBelanja $r): string {
                        $sisaVol = number_format($r->sisa_volume, 0, ',', '.') . ' ' . $r->satuan;
                        $sisaRp = 'Rp ' . number_format($r->sisa_anggaran, 0, ',', '.');
                        return "{$sisaVol} ({$sisaRp})";
                    })
                    ->badge()
                    ->color(fn(RincianAnggaranBelanja $r) => $r->sisa_volume > 0 ? 'success' : 'danger'),

                TextColumn::make('persen_realisasi')
                    ->label('Penyerapan')
                    ->state(fn(RincianAnggaranBelanja $r): string => $r->persen_realisasi . '%')
                    ->badge()
                    ->color(fn(RincianAnggaranBelanja $r): string => match (true) {
                        $r->persen_realisasi >= 100 => 'danger',
                        $r->persen_realisasi >= 75 => 'warning',
                        $r->persen_realisasi > 0 => 'info',
                        default => 'gray',
                    }),

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

                SelectFilter::make('sub_kegiatan_id')
                    ->label('Filter Sub Kegiatan')
                    ->options(fn() => MasterSubKegiatan::all()->pluck('nama_sub_kegiatan', 'id')->all())
                    ->searchable(),

                SelectFilter::make('kode_rekening_id')
                    ->label('Filter Kode Rekening')
                    ->options(fn() => KodeRekening::all()->pluck('uraian', 'id')->all())
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    ReplicateAction::make()
                        ->label('Duplikasi RKA')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->successNotificationTitle('Rincian Belanja RKA berhasil diduplikasi!')
                        ->beforeReplicaSaved(function (RincianAnggaranBelanja $replica): void {
                            $replica->volume_realisasi = 0;
                            $replica->nilai_realisasi = 0;
                        }),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
