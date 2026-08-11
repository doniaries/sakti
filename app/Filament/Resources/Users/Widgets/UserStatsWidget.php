<?php

namespace App\Filament\Resources\Users\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Pegawai', \App\Models\User::role('pegawai')->count())
                ->description('Pengguna dengan peran pegawai')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
        ];
    }
}
