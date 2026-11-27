<?php

namespace App\Filament\Resources\Aktivitas\Widgets;

use App\Models\Aktivitas;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AktivitasStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $total = Aktivitas::count();
        $global = Aktivitas::whereNull('user_id')->whereNull('jabatan_id')->count();
        $spesifik = Aktivitas::where(function ($q) {
            $q->whereNotNull('user_id')->orWhereNotNull('jabatan_id');
        })->count();

        return [
            Stat::make('Total Aktivitas', number_format($total))
                ->description('Seluruh item di Bank Aktivitas')
                ->color('primary'),
            Stat::make('Aktivitas Global', number_format($global))
                ->description('Dapat dipilih oleh seluruh pegawai')
                ->color('info'),
            Stat::make('Aktivitas Spesifik', number_format($spesifik))
                ->description('Diisi per pegawai / jabatan / bidang')
                ->color('success'),
        ];
    }
}
