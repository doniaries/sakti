<?php

namespace App\Filament\Resources\AbsensiHarians\Widgets;

use App\Models\AbsensiHarian;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AbsensiHarianStats extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();
        
        $hadir = AbsensiHarian::whereDate('tanggal', $today)->where('status', 'hadir')->count();
        $izin = AbsensiHarian::whereDate('tanggal', $today)->where('status', 'izin')->count();
        $sakit = AbsensiHarian::whereDate('tanggal', $today)->where('status', 'sakit')->count();
        $tanpaKeterangan = AbsensiHarian::whereDate('tanggal', $today)->where('status', 'tidak_hadir')->count();

        return [
            Stat::make('Hadir', $hadir)
                ->description('Hadir hari ini')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Sakit', $sakit)
                ->description('Sakit hari ini')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
            Stat::make('Izin', $izin)
                ->description('Izin hari ini')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
            Stat::make('Tanpa Keterangan', $tanpaKeterangan)
                ->description('Tanpa keterangan hari ini')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
