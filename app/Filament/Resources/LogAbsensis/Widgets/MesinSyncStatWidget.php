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

        $tanggal = \Carbon\Carbon::parse($mesin->sinkronisasi_terakhir)->locale('id')->translatedFormat('d F Y');
        $jam = \Carbon\Carbon::parse($mesin->sinkronisasi_terakhir)->format('H:i:s');

        return [
            Stat::make('Sinkronisasi Terakhir', new \Illuminate\Support\HtmlString($tanggal . '<br><span class="text-xl">' . $jam . '</span>'))
                ->description('Dari Mesin: ' . $mesin->nama)
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('success'),
        ];
    }
}
