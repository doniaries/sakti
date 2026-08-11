<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\AbsensiHarian;
use Carbon\Carbon;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;

class PegawaiAbsensiBulanChartWidget extends ChartWidget
{
    use HasWidgetShield;

    public function getHeading(): string
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $userName = $user ? explode(' ', trim($user->name))[0] : 'Pribadi';
        return "Rekap Absensi Bulanan ({$userName})";
    }
    protected static ?int $sort = 3;
    protected ?string $maxHeight = '300px';

    protected function getFilters(): ?array
    {
        $filters = [];
        $currentMonth = Carbon::now();
        
        for ($i = 0; $i < 12; $i++) {
            $month = $currentMonth->copy()->subMonths($i);
            $key = $month->format('Y-m');
            $filters[$key] = $month->translatedFormat('F Y');
        }

        return $filters;
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter ?? Carbon::now()->format('Y-m');
        $date = Carbon::createFromFormat('Y-m', $activeFilter);
        
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $userId = $user ? $user->id : null;
        
        $absensi = AbsensiHarian::where('user_id', $userId)
            ->whereMonth('tanggal', $date->month)
            ->whereYear('tanggal', $date->year)
            ->get();

        $hadir = $absensi->where('status', 'Hadir')->count();
        $sakit = $absensi->where('status', 'Sakit')->count();
        $izin = $absensi->where('status', 'Izin')->count();
        $alpa = $absensi->where('status', 'Tanpa Keterangan')->count();

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
