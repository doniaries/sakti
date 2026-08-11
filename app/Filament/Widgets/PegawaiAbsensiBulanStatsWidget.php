<?php

namespace App\Filament\Widgets;

use App\Models\AbsensiHarian;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class PegawaiAbsensiBulanStatsWidget extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $userId = $user ? $user->id : null;
        $date = Carbon::now();
        
        $absensi = AbsensiHarian::where('user_id', $userId)
            ->whereMonth('tanggal', $date->month)
            ->whereYear('tanggal', $date->year)
            ->get();

        $hadir = $absensi->where('status', 'Hadir')->count();
        $sakit = $absensi->where('status', 'Sakit')->count();
        $izin = $absensi->where('status', 'Izin')->count();
        $tanpaKeterangan = $absensi->where('status', 'Tanpa Keterangan')->count();

        $userName = $user ? $user->name : 'Pribadi';

        return [
            Stat::make('Hadir', $hadir)
                ->description("Hadir bulan ini ({$userName})")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Sakit', $sakit)
                ->description("Sakit bulan ini ({$userName})")
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
            Stat::make('Izin', $izin)
                ->description("Izin bulan ini ({$userName})")
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
            Stat::make('Tanpa Keterangan', $tanpaKeterangan)
                ->description("Tanpa keterangan bulan ini ({$userName})")
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
