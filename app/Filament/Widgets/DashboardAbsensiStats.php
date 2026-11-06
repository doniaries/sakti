<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\MasterKegiatans\MasterKegiatanResource;
use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Models\MasterKegiatan;
use App\Models\MasterSubKegiatan;
use App\Models\PengajuanSpj;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardAbsensiStats extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $tahun = (int) date('Y');

        // 1. Total Anggaran Pagu DPA Kegiatan TA Aktif
        $totalAnggaran = (float) MasterKegiatan::where('tahun_anggaran', $tahun)->sum('pagu_anggaran');

        // 2. Total Kegiatan & Sub Kegiatan TA Aktif
        $jumlahKegiatan = MasterKegiatan::where('tahun_anggaran', $tahun)->count();
        $jumlahSubKegiatan = MasterSubKegiatan::where('tahun_anggaran', $tahun)->count();

        // 3. Realisasi Belanja SPJ Dicairkan TA Aktif
        $realisasiDicairkan = (float) PengajuanSpj::where('tahun_anggaran', $tahun)
            ->where('status', 'dicairkan')
            ->sum('total_nilai_realisasi');

        $totalSpjCair = PengajuanSpj::where('tahun_anggaran', $tahun)->where('status', 'dicairkan')->count();
        $totalSemuaSpj = PengajuanSpj::where('tahun_anggaran', $tahun)->count();

        // 4. Sisa Anggaran TA Aktif
        $sisaAnggaran = max(0, $totalAnggaran - $realisasiDicairkan);
        $persenRealisasi = $totalAnggaran > 0 ? round(($realisasiDicairkan / $totalAnggaran) * 100, 1) : 0;
        $persenSisa = $totalAnggaran > 0 ? round(($sisaAnggaran / $totalAnggaran) * 100, 1) : 0;

        return [
            Stat::make('Jumlah Anggaran', 'Rp ' . number_format($totalAnggaran, 0, ',', '.'))
                ->description("Alokasi pagu DPA TA {$tahun}")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary')
                ->url(MasterKegiatanResource::getUrl('index')),

            Stat::make('Jumlah Kegiatan', $jumlahKegiatan . ' Kegiatan')
                ->description("{$jumlahSubKegiatan} Sub-Kegiatan aktif")
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info')
                ->url(MasterKegiatanResource::getUrl('index')),

            Stat::make('Realisasi Belanja', 'Rp ' . number_format($realisasiDicairkan, 0, ',', '.'))
                ->description("{$totalSpjCair} dari {$totalSemuaSpj} SPJ dicairkan ({$persenRealisasi}%)")
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url(PengajuanSpjResource::getUrl('index')),

            Stat::make('Sisa Anggaran', 'Rp ' . number_format($sisaAnggaran, 0, ',', '.'))
                ->description("{$persenSisa}% pagu anggaran tersedia")
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning')
                ->url(MasterKegiatanResource::getUrl('index')),
        ];
    }
}
