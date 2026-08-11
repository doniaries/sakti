<?php

namespace App\Filament\Widgets;

use App\Models\AbsensiHarian;
use App\Filament\Resources\AbsensiHarians\AbsensiHarianResource;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardAbsensiStats extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $today = Carbon::today();
        
        $baseQuery = clone AbsensiHarianResource::getEloquentQuery()->whereDate('tanggal', $today);
        
        $hadir = (clone $baseQuery)->where('status', 'hadir')->count();
        $izin = (clone $baseQuery)->where('status', 'izin')->count();
        $sakit = (clone $baseQuery)->where('status', 'sakit')->count();
        $tanpaKeterangan = (clone $baseQuery)->where('status', 'tidak_hadir')->count();

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
