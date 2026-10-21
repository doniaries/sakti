<?php

namespace App\Filament\Widgets;

use App\Models\AbsensiHarian;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class PegawaiAbsensiBulanStatsWidget extends BaseWidget
{
    use HasWidgetShield, InteractsWithPageFilters;

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $userId = $user ? $user->id : null;
        
        $tahun = $this->filters['tahun'] ?? Carbon::now()->year;
        $bulan = $this->filters['bulan'] ?? Carbon::now()->month;
        
        $absensi = AbsensiHarian::where('user_id', $userId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $hadir = $absensi->where('status', 'hadir')->count();
        $sakit = $absensi->where('status', 'sakit')->count();
        $izin = $absensi->where('status', 'izin')->count();
        $tanpaKeterangan = $absensi->where('status', 'tidak_hadir')->count();

        $userName = $user ? explode(' ', trim($user->name))[0] : 'Pribadi';

        $filterParams = [
            'pegawai' => ['user_id' => $userId],
        ];

        return [
            Stat::make('Hadir', $hadir)
                ->description("Hadir bulan ini ({$userName})")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->url(\App\Filament\Resources\AbsensiHarians\AbsensiHarianResource::getUrl('index', [
                    'tableFilters' => array_merge($filterParams, [
                        'kategori_status' => ['kategori' => 'hadir'],
                    ]),
                ])),
            Stat::make('Sakit', $sakit)
                ->description("Sakit bulan ini ({$userName})")
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning')
                ->url(\App\Filament\Resources\AbsensiHarians\AbsensiHarianResource::getUrl('index', [
                    'tableFilters' => array_merge($filterParams, [
                        'kategori_status' => ['kategori' => 'sakit'],
                    ]),
                ])),
            Stat::make('Izin', $izin)
                ->description("Izin bulan ini ({$userName})")
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning')
                ->url(\App\Filament\Resources\AbsensiHarians\AbsensiHarianResource::getUrl('index', [
                    'tableFilters' => array_merge($filterParams, [
                        'kategori_status' => ['kategori' => 'izin'],
                    ]),
                ])),
            Stat::make('Tanpa Keterangan', $tanpaKeterangan)
                ->description("Tanpa keterangan bulan ini ({$userName})")
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->url(\App\Filament\Resources\AbsensiHarians\AbsensiHarianResource::getUrl('index', [
                    'tableFilters' => array_merge($filterParams, [
                        'kategori_status' => ['kategori' => 'tidak_hadir'],
                    ]),
                ])),
        ];
    }
}
