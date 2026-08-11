<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Jumlah Pegawai', \App\Models\User::role('pegawai')->count())
                ->description('Total pengguna berstatus pegawai')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
            Stat::make('Jumlah Bidang', \App\Models\Bidang::count())
                ->description('Total bidang terdaftar')
                ->descriptionIcon('heroicon-m-rectangle-group')
                ->color('success'),
        ];
    }
}
