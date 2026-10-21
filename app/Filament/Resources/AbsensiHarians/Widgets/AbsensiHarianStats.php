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
        // Gunakan clone dari getPageTableQuery() yang sudah membawa semua filter aktif dari tabel
        $query = clone $this->getPageTableQuery();

        // Total pegawai yang terdaftar di seluruh instansi
        $totalPegawaiInstansi = \App\Models\User::query()->count('*');

        // Ambil filter tanggal aktif jika diset oleh pengguna
        $filterData = $this->tableFilters['tanggal']['tanggal'] ?? null;
        if (!empty($filterData)) {
            $labelPeriode = Carbon::parse($filterData)->translatedFormat('d F Y');
        } else {
            // Jika filter belum diubah, periksa tanggal dari query atau fallback ke hari ini
            $labelPeriode = Carbon::today()->translatedFormat('d F Y');
        }

        // Single optimized aggregation query langsung dari query tabel aktif
        $counts = (clone $query)->selectRaw('
            COUNT(CASE WHEN status IN ("hadir", "terlambat") THEN 1 END) as hadir,
            COUNT(CASE WHEN status = "sakit" THEN 1 END) as sakit,
            COUNT(CASE WHEN status = "izin" THEN 1 END) as izin,
            COUNT(CASE WHEN status = "tidak_hadir" THEN 1 END) as alpa
        ')->first();

        $hadir = (int) ($counts->hadir ?? 0);
        $sakit = (int) ($counts->sakit ?? 0);
        $izin = (int) ($counts->izin ?? 0);
        $tanpaKeterangan = (int) ($counts->alpa ?? 0);

        return [
            Stat::make('Total Pegawai', $totalPegawaiInstansi)
                ->description('Total Pegawai di Instansi')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
            Stat::make('Hadir', $hadir)
                ->description('Total Hadir pada ' . $labelPeriode)
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            Stat::make('Sakit', $sakit)
                ->description('Total Sakit pada ' . $labelPeriode)
                ->descriptionIcon('heroicon-m-heart')
                ->color('warning'),
            Stat::make('Izin', $izin)
                ->description('Total Izin pada ' . $labelPeriode)
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
            Stat::make('Tanpa Keterangan', $tanpaKeterangan)
                ->description('Total Tanpa Keterangan pada ' . $labelPeriode)
                ->descriptionIcon('heroicon-m-user-minus')
                ->color('danger'),
        ];
    }
}
