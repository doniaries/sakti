<?php

namespace App\Filament\Resources\PengajuanIzins\Widgets;

use App\Filament\Resources\PengajuanIzins\PengajuanIzinResource;
use App\Models\HariLibur;
use App\Models\PengajuanIzin;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PengajuanIzinStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '5s';

    protected function getColumns(): int | array
    {
        return [
            'default' => 2,
            'md' => 4,
        ];
    }

    protected function getStats(): array
    {
        /** @var User|null $user */
        $user = Auth::user();
        $tahun = now()->year;
        $kuotaTahunan = 12;

        $baseQuery = PengajuanIzinResource::getEloquentQuery();

        $totalPengajuan = (clone $baseQuery)->count();
        $menungguCount = (clone $baseQuery)->where('status', 'menunggu')->count();
        $disetujuiCount = (clone $baseQuery)->where('status', 'disetujui')->count();
        $ditolakCount = (clone $baseQuery)->where('status', 'ditolak')->count();

        // Hitung hari cuti bersama aktif di tahun berjalan (tidak memotong jatah 12 hari)
        $cutiBersamaCount = HariLibur::query()
            ->whereYear('tanggal', $tahun)
            ->where('jenis', 'cuti_bersama')
            ->where('is_aktif', true)
            ->count();

        // Hitung cuti tahunan terpakai milik user yang sedang login (di luar libur & cuti bersama)
        $cutiTerpakai = 0;
        if ($user) {
            $cutiDisetujui = PengajuanIzin::query()
                ->where('user_id', $user->id)
                ->where('status', 'disetujui')
                ->whereYear('tanggal_mulai', $tahun)
                ->whereHas('jenisIzin', fn ($q) => $q->where('kode', 'cuti_tahunan'))
                ->get(['tanggal_mulai', 'tanggal_selesai']);

            foreach ($cutiDisetujui as $izin) {
                $period = CarbonPeriod::create(
                    Carbon::parse($izin->tanggal_mulai),
                    Carbon::parse($izin->tanggal_selesai)
                );

                foreach ($period as $date) {
                    if ($date->year !== $tahun) {
                        continue;
                    }

                    if (! HariLibur::isHariLibur($date)) {
                        $cutiTerpakai++;
                    }
                }
            }
        }

        $sisaCuti = max(0, $kuotaTahunan - $cutiTerpakai);
        $sisaColor = match (true) {
            $sisaCuti >= 6 => 'success',
            $sisaCuti >= 1 => 'warning',
            default => 'danger',
        };

        return [
            Stat::make('Jumlah Pengajuan Izin', $totalPengajuan)
                ->description("{$disetujuiCount} disetujui • {$ditolakCount} ditolak")
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('primary'),

            Stat::make('Menunggu Persetujuan', $menungguCount)
                ->description($menungguCount > 0 ? 'Perlu tindak lanjut atasan' : 'Semua pengajuan diproses')
                ->descriptionIcon('heroicon-m-clock')
                ->color($menungguCount > 0 ? 'warning' : 'success'),

            Stat::make('Cuti Tahunan Terpakai', "{$cutiTerpakai} Hari")
                ->description("Periode tahun {$tahun}")
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Sisa Cuti Tahunan', "{$sisaCuti} / {$kuotaTahunan} Hari")
                ->description("Di luar {$cutiBersamaCount} hr cuti bersama (hari libur)")
                ->descriptionIcon('heroicon-m-sparkles')
                ->color($sisaColor),
        ];
    }
}

