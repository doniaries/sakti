<?php

declare(strict_types=1);

namespace App\Filament\Resources\RincianAnggaranBelanjas\Widgets;

use App\Filament\Resources\MasterKegiatans\MasterKegiatanResource;
use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Filament\Resources\RincianAnggaranBelanjas\RincianAnggaranBelanjaResource;
use App\Models\DetailPengajuanSpj;
use App\Models\MasterKegiatan;
use App\Models\MasterSubKegiatan;
use App\Models\RincianAnggaranBelanja;
use App\Support\FormatHelper;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class RkaOverviewWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getColumns(): int | array | null
    {
        return [
            'default' => 1,
            'sm' => 2,
            'lg' => 4,
        ];
    }

    protected function getStats(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $isRestrictedJf = $user && $user->hasRole('jf') && ! $user->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'bendahara']);

        // Tahun anggaran aktif sistem
        $tahun = \App\Models\PengaturanAplikasi::getTahunAnggaranAktif();

        // Query RKA sesuai tahun & role scope
        $rkaQuery = RincianAnggaranBelanja::query()->where('tahun_anggaran', $tahun);
        if ($isRestrictedJf) {
            $rkaQuery->where(function ($q) use ($user) {
                if ($user->bidang_id) {
                    $q->where('bidang_id', $user->bidang_id)
                        ->orWhereHas('subKegiatan.kegiatan', fn ($kq) => $kq->where('bidang_id', $user->bidang_id));
                }
                $q->orWhereHas('subKegiatan', fn ($sq) => $sq->where('jf_id', $user->id));
            });
        }

        $totalPagu = (float) (clone $rkaQuery)->sum('pagu_total');
        $countItemRka = (clone $rkaQuery)->count();

        // 1. Kegiatan
        $kegiatanQuery = MasterKegiatan::query()->where('tahun_anggaran', $tahun);
        if ($isRestrictedJf) {
            if ($user->bidang_id) {
                $kegiatanQuery->where('bidang_id', $user->bidang_id);
            } else {
                $kegiatanQuery->whereHas('subKegiatans', fn ($sq) => $sq->where('jf_id', $user->id));
            }
        }
        $totalKegiatan = (clone $kegiatanQuery)->count();
        $kegiatanDenganRka = (clone $kegiatanQuery)->whereHas('subKegiatans.rincianAnggarans', function ($rq) use ($tahun) {
            $rq->where('tahun_anggaran', $tahun);
        })->count();

        // 2. Sub Kegiatan
        $subKegiatanQuery = MasterSubKegiatan::query()->where('tahun_anggaran', $tahun);
        if ($isRestrictedJf) {
            if ($user->bidang_id) {
                $subKegiatanQuery->whereHas('kegiatan', fn ($kq) => $kq->where('bidang_id', $user->bidang_id));
            } else {
                $subKegiatanQuery->where('jf_id', $user->id);
            }
        }
        $totalSubKegiatan = (clone $subKegiatanQuery)->count();
        $subKegiatanDenganRka = (clone $subKegiatanQuery)->whereHas('rincianAnggarans', function ($rq) use ($tahun) {
            $rq->where('tahun_anggaran', $tahun);
        })->count();

        // 3. Realisasi Keuangan (SPJ Dicairkan / Nilai Realisasi RKA)
        $realisasiCair = (float) DetailPengajuanSpj::query()
            ->whereHas('rincianAnggaranBelanja', function ($q) use ($tahun, $isRestrictedJf, $user) {
                $q->where('tahun_anggaran', $tahun);
                if ($isRestrictedJf && $user->bidang_id) {
                    $q->where('bidang_id', $user->bidang_id);
                }
            })
            ->whereHas('pengajuanSpj', fn ($q) => $q->where('status', 'dicairkan'))
            ->sum('subtotal');

        $nilaiRealisasiRka = (float) (clone $rkaQuery)->sum('nilai_realisasi');
        $totalRealisasi = max($realisasiCair, $nilaiRealisasiRka);

        $totalTeralokasiSpj = (float) DetailPengajuanSpj::query()
            ->whereHas('rincianAnggaranBelanja', function ($q) use ($tahun, $isRestrictedJf, $user) {
                $q->where('tahun_anggaran', $tahun);
                if ($isRestrictedJf && $user->bidang_id) {
                    $q->where('bidang_id', $user->bidang_id);
                }
            })
            ->whereHas('pengajuanSpj', fn ($q) => $q->whereNotIn('status', ['draft', 'ditolak']))
            ->sum('subtotal');

        $persenRealisasi = $totalPagu > 0 ? round(($totalRealisasi / $totalPagu) * 100, 1) : 0;
        $sisaPagu = max(0, $totalPagu - $totalRealisasi);

        $filterParams = ['tahun' => $tahun];

        return [
            Stat::make('Tahun Anggaran', 'TA ' . $tahun)
                ->description('Pagu: ' . FormatHelper::rupiah($totalPagu))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary')
                ->url(RincianAnggaranBelanjaResource::getUrl('index', [
                    'tableFilters' => ['tahun_anggaran' => ['value' => $tahun]],
                    'tahun' => $tahun,
                ])),

            Stat::make('Jumlah Kegiatan', $totalKegiatan . ' Kegiatan')
                ->description("{$kegiatanDenganRka} kegiatan teralokasi di RKA")
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('info')
                ->url(MasterKegiatanResource::getUrl('index', $filterParams)),

            Stat::make('Sub Kegiatan', $totalSubKegiatan . ' Sub Kegiatan')
                ->description("{$subKegiatanDenganRka} teralokasi ({$countItemRka} Item)")
                ->descriptionIcon('heroicon-m-rectangle-stack')
                ->color('warning')
                ->url(MasterSubKegiatanResource::getUrl('index', $filterParams)),

            Stat::make('Realisasi Keuangan', FormatHelper::rupiah($totalRealisasi))
                ->description(
                    $totalRealisasi > 0
                        ? "{$persenRealisasi}% Serapan (Sisa: " . FormatHelper::rupiah($sisaPagu) . ")"
                        : ($totalTeralokasiSpj > 0 ? 'SPJ Berjalan: ' . FormatHelper::rupiah($totalTeralokasiSpj) : 'Belum ada realisasi SPJ cair')
                )
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($totalRealisasi > 0 ? 'success' : ($totalTeralokasiSpj > 0 ? 'info' : 'gray'))
                ->url(PengajuanSpjResource::getUrl('index', $filterParams)),
        ];
    }
}

