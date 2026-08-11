<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\AbsensiHarian;
use Carbon\Carbon;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class PegawaiAbsensiBulanChartWidget extends ChartWidget
{
    use HasWidgetShield, InteractsWithPageFilters;

    public function getHeading(): string
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $userName = $user ? explode(' ', trim($user->name))[0] : 'Pribadi';
        return "Rekap Absensi Bulanan ({$userName})";
    }
    protected static ?int $sort = 3;
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $tahun = $this->filters['tahun'] ?? Carbon::now()->year;
        $bulan = $this->filters['bulan'] ?? Carbon::now()->month;
        
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $userId = $user ? $user->id : null;
        
        $absensi = AbsensiHarian::where('user_id', $userId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $hadir = $absensi->where('status', 'hadir')->count();
        $sakit = $absensi->where('status', 'sakit')->count();
        $izin = $absensi->where('status', 'izin')->count();
        $alpa = $absensi->where('status', 'tidak_hadir')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Total Hari',
                    'data' => [$hadir, $sakit, $izin, $alpa],
                    'backgroundColor' => [
                        '#10b981', // green
                        '#f59e0b', // amber
                        '#3b82f6', // blue
                        '#ef4444', // red
                    ],
                ],
            ],
            'labels' => ['Hadir', 'Sakit', 'Izin', 'Tanpa Keterangan'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
