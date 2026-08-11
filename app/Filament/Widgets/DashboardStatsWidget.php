<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Waktu Saat Ini', new \Illuminate\Support\HtmlString('
                <span x-data="{ time: new Date().toLocaleTimeString(\'id-ID\', { timeZone: \'Asia/Jakarta\', hour: \'2-digit\', minute:\'2-digit\', second:\'2-digit\' }) }" 
                      x-init="setInterval(() => time = new Date().toLocaleTimeString(\'id-ID\', { timeZone: \'Asia/Jakarta\', hour: \'2-digit\', minute:\'2-digit\', second:\'2-digit\' }), 1000)" 
                      x-text="time">
                </span> WIB
            '))
            ->description(new \Illuminate\Support\HtmlString('
                <span x-data="{ date: new Date().toLocaleDateString(\'id-ID\', { timeZone: \'Asia/Jakarta\', weekday: \'long\', year: \'numeric\', month: \'long\', day: \'numeric\' }) }" 
                      x-init="setInterval(() => date = new Date().toLocaleDateString(\'id-ID\', { timeZone: \'Asia/Jakarta\', weekday: \'long\', year: \'numeric\', month: \'long\', day: \'numeric\' }), 60000)" 
                      x-text="date">
                </span>
            '))
            ->descriptionIcon('heroicon-m-clock')
            ->color('primary'),

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
