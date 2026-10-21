<?php

namespace App\Filament\Widgets;

use App\Models\AbsensiHarian;
use App\Filament\Resources\AbsensiHarians\AbsensiHarianResource;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardAbsensiStats extends BaseWidget
{
    use \BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $today = Carbon::today();
        
        $baseQuery = clone AbsensiHarianResource::getEloquentQuery()->whereDate('tanggal', $today);
        
        $hadir = (clone $baseQuery)->where('status', 'hadir')->count();
        $izin = (clone $baseQuery)->where('status', 'izin')->count();
        $sakit = (clone $baseQuery)->where('status', 'sakit')->count();
        $tanpaKeterangan = (clone $baseQuery)->where('status', 'tidak_hadir')->count();

        $todayStr = $today->toDateString();

        return [
            Stat::make('Hadir', $hadir)
                ->description('Hadir hari ini (Seluruh Pegawai)')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(AbsensiHarianResource::getUrl('index', [
                    'tableFilters' => [
                        'tanggal' => ['tanggal' => $todayStr],
                        'kategori_status' => ['kategori' => 'hadir'],
                    ],
                ])),
            Stat::make('Sakit', $sakit)
                ->description('Sakit hari ini (Seluruh Pegawai)')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning')
                ->url(AbsensiHarianResource::getUrl('index', [
                    'tableFilters' => [
                        'tanggal' => ['tanggal' => $todayStr],
                        'kategori_status' => ['kategori' => 'sakit'],
                    ],
                ])),
            Stat::make('Izin', $izin)
                ->description('Izin hari ini (Seluruh Pegawai)')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning')
                ->url(AbsensiHarianResource::getUrl('index', [
                    'tableFilters' => [
                        'tanggal' => ['tanggal' => $todayStr],
                        'kategori_status' => ['kategori' => 'izin'],
                    ],
                ])),
            Stat::make('Tanpa Keterangan', $tanpaKeterangan)
                ->description('Tanpa keterangan hari ini (Seluruh Pegawai)')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url(AbsensiHarianResource::getUrl('index', [
                    'tableFilters' => [
                        'tanggal' => ['tanggal' => $todayStr],
                        'kategori_status' => ['kategori' => 'tidak_hadir'],
                    ],
                ])),
        ];
    }
}
