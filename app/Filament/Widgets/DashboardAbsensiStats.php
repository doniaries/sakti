<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\MasterKegiatans\MasterKegiatanResource;
use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Filament\Resources\RincianAnggaranBelanjas\RincianAnggaranBelanjaResource;
use App\Models\MasterKegiatan;
use App\Models\PengajuanSpj;
use App\Models\RincianAnggaranBelanja;
use BezhanSalleh\FilamentShield\Traits\HasWidgetShield;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardAbsensiStats extends BaseWidget
{
    use HasWidgetShield;

    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        // 1. Total Anggaran Pagu RKA
        $totalAnggaran = (float) RincianAnggaranBelanja::sum('pagu_total');

        // 2. Total Kegiatan
        $jumlahKegiatan = MasterKegiatan::count();

        // 3. Realisasi Belanja SPJ Dicairkan
        $realisasiDicairkan = (float) PengajuanSpj::where('status', 'dicairkan')->sum('total_nilai_realisasi');
        if ($realisasiDicairkan <= 0) {
            $realisasiDicairkan = (float) PengajuanSpj::whereIn('status', ['dicairkan', 'disetujui_pa'])->sum('total_nilai_realisasi');
        }

        // 4. Sisa Anggaran
        $sisaAnggaran = max(0, $totalAnggaran - $realisasiDicairkan);

        // Persentase Realisasi
        $persenRealisasi = $totalAnggaran > 0 ? round(($realisasiDicairkan / $totalAnggaran) * 100, 1) : 0;

        return [
            Stat::make('Jumlah Anggaran', 'Rp ' . number_format($totalAnggaran, 0, ',', '.'))
                ->description('Total pagu anggaran DPA / RKA')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary')
                ->url(RincianAnggaranBelanjaResource::getUrl('index')),

            Stat::make('Jumlah Kegiatan', $jumlahKegiatan . ' Kegiatan')
                ->description('Total kegiatan belanja terdaftar')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info')
                ->url(MasterKegiatanResource::getUrl('index')),

            Stat::make('Realisasi Belanja', 'Rp ' . number_format($realisasiDicairkan, 0, ',', '.'))
                ->description("Realisasi pencairan ({$persenRealisasi}%)")
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->url(PengajuanSpjResource::getUrl('index')),

            Stat::make('Sisa Anggaran', 'Rp ' . number_format($sisaAnggaran, 0, ',', '.'))
                ->description('Sisa pagu anggaran tersedia')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('warning')
                ->url(RincianAnggaranBelanjaResource::getUrl('index')),
        ];
    }
}
