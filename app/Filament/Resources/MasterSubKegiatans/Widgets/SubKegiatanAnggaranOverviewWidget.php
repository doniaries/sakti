<?php

declare(strict_types=1);

namespace App\Filament\Resources\MasterSubKegiatans\Widgets;

use App\Models\MasterSubKegiatan;
use App\Support\FormatHelper;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class SubKegiatanAnggaranOverviewWidget extends StatsOverviewWidget
{
    public ?Model $record = null;

    protected function getStats(): array
    {
        if (! $this->record instanceof MasterSubKegiatan) {
            return [];
        }

        /** @var MasterSubKegiatan $sub */
        $sub = $this->record;

        $pagu = (float) $sub->total_pagu;
        $realisasi = (float) $sub->total_realisasi;
        $sisa = (float) $sub->sisa_pagu;
        $persen = $pagu > 0 ? round(($realisasi / $pagu) * 100, 1) : 0;
        $jumlahItem = $sub->rincianAnggarans()->count();

        return [
            Stat::make('Tahun Anggaran', 'TA ' . ($sub->tahun_anggaran ?: date('Y')))
                ->description($sub->kegiatan?->bidang?->nama ?? 'Semua Bidang')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('Total Pagu Anggaran', FormatHelper::rupiah($pagu))
                ->description("{$jumlahItem} Item Belanja")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make('Realisasi SPJ', FormatHelper::rupiah($realisasi))
                ->description("{$persen}% Serapan Anggaran")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($realisasi > 0 ? 'warning' : 'gray'),

            Stat::make('Sisa Anggaran', FormatHelper::rupiah($sisa))
                ->description($sisa > 0 ? 'Siap Dibelanjakan' : 'Pagu Habis / Terpenuhi')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color($sisa > 0 ? 'success' : 'danger'),
        ];
    }
}

