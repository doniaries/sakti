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

        return [
            Stat::make('Total Pegawai', $total)
                ->description('Seluruh pegawai dinas')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),



        ];
    }
}
