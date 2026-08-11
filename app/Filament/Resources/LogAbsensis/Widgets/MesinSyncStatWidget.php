<?php

namespace App\Filament\Resources\LogAbsensis\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MesinSyncStatWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $mesin = \App\Models\MesinAbsensi::where('aktif', true)->first();

        if (! $mesin || ! $mesin->sinkronisasi_terakhir) {
            return []; // Sembunyikan widget jika belum ada sinkronisasi
        }

        $tanggal = \Carbon\Carbon::parse($mesin->sinkronisasi_terakhir)->translatedFormat('d F Y H:i:s');

        return [
            Stat::make('Sinkronisasi Terakhir', $tanggal)
                ->description('Dari Mesin: ' . $mesin->nama)
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('success'),
        ];
    }
}
