<?php

namespace App\Filament\Resources\AbsensiHarians\Widgets;

use App\Models\AbsensiHarian;
use App\Filament\Resources\AbsensiHarians\AbsensiHarianResource;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Filament\Resources\AbsensiHarians\Pages\ListAbsensiHarians;

class AbsensiHarianStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListAbsensiHarians::class;
    }

    protected function getStats(): array
    {
        $query = clone $this->getPageTableQuery();
        
        $hadir = (clone $query)->where('status', 'hadir')->count();
        $izin = (clone $query)->where('status', 'izin')->count();
        $sakit = (clone $query)->where('status', 'sakit')->count();
        $tanpaKeterangan = (clone $query)->where('status', 'tidak_hadir')->count();

        return [
            Stat::make('Hadir', $hadir)
                ->description('Total Hadir')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Sakit', $sakit)
                ->description('Total Sakit')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('warning'),
            Stat::make('Izin', $izin)
                ->description('Total Izin')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
            Stat::make('Tanpa Keterangan', $tanpaKeterangan)
                ->description('Total Tanpa Keterangan')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),
        ];
    }
}
