<?php

namespace App\Filament\Resources\Users\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $total = User::count();
        $wfo = User::where('status_kerja', 'wfo')->count();
        $wfh = User::where('status_kerja', 'wfh')->count();

        return [
            Stat::make('Total Pegawai', $total)
                ->description('Seluruh pegawai dinas')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Pegawai WFO', $wfo)
                ->description('Bekerja di kantor (Office)')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success'),

            Stat::make('Pegawai WFH', $wfh)
                ->description('Bekerja dari rumah (Home)')
                ->descriptionIcon('heroicon-m-home')
                ->color('warning'),
        ];
    }
}
