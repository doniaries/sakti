<?php

namespace App\Filament\Resources\AbsensiHarians\Widgets;

use App\Filament\Resources\AbsensiHarians\Pages\ListAbsensiHarians;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AbsensiHarianStats extends BaseWidget
{
    use InteractsWithPageTable;

    // Refresh realtime setiap 15 detik tanpa cache
    protected ?string $pollingInterval = '15s';

    protected function getTablePage(): string
    {
        return ListAbsensiHarians::class;
    }

    protected function getStats(): array
    {
        // Query realtime langsung dari database sesuai filter aktif tabel
        $query = clone $this->getPageTableQuery();

        $totalPegawai = (clone $query)->count();
        $hadir = (clone $query)->where('status', 'hadir')->count();
        $izin = (clone $query)->where('status', 'izin')->count();
        $sakit = (clone $query)->where('status', 'sakit')->count();
        $tanpaKeterangan = (clone $query)->where('status', 'tidak_hadir')->count();

        // Ambil informasi tanggal dari filter tabel (jika ada yang dipilih)
        $filterData = $this->tableFilters['tanggal']['tanggal'] ?? null;
        if (!empty($filterData)) {
            $labelPeriode = Carbon::parse($filterData)->translatedFormat('d F Y');
        } else {
            $labelPeriode = Carbon::today()->translatedFormat('d F Y');
        }

        return [
            Stat::make('Total Pegawai', $totalPegawai)
                ->description('Total Pegawai pada ' . $labelPeriode)
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
            Stat::make('Hadir', $hadir)
                ->description('Total Hadir pada ' . $labelPeriode)
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Sakit', $sakit)
                ->description('Total Sakit pada ' . $labelPeriode)
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
            Stat::make('Izin', $izin)
                ->description('Total Izin pada ' . $labelPeriode)
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
            Stat::make('Tanpa Keterangan', $tanpaKeterangan)
                ->description('Alpa pada ' . $labelPeriode)
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
