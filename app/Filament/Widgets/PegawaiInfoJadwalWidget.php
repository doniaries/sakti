<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\Lhks\LhkResource;
use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\JadwalKerja;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PegawaiInfoJadwalWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getColumns(): int|array
    {
        return [
            'default' => 2,
            'md' => 3,
        ];
    }

    public static function canView(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return $user !== null && ! $user->hasRole('super_admin');
    }

    protected function getStats(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user) {
            return [];
        }

        $user->loadMissing(['jadwalKerja', 'jabatan', 'bidang', 'atasan', 'jfPenempatan']);
        $jadwal = $user->jadwalKerja ?? JadwalKerja::query()->where('is_default', true)->first();

        $jamSeninKamis = $jadwal
            ? substr((string) $jadwal->jam_masuk_senin_kamis, 0, 5) . '–' . substr((string) $jadwal->jam_pulang_senin_kamis, 0, 5)
            : '-';
        $jamJumat = $jadwal
            ? substr((string) ($jadwal->jam_masuk_jumat ?? $jadwal->jam_masuk_senin_kamis), 0, 5) . '–' . substr((string) ($jadwal->jam_pulang_jumat ?? $jadwal->jam_pulang_senin_kamis), 0, 5)
            : '-';
        $toleransi = (int) ($jadwal?->toleransi_terlambat_menit ?? 15);

        $tipeKerja = strtoupper((string) ($user->status_kerja ?? 'WFO'));
        $statusAktif = $user->aktif ? 'Aktif' : 'Nonaktif';

        $spjQuery = PengajuanSpjResource::getEloquentQuery();
        $totalSpj = (clone $spjQuery)->count();
        $spjProses = (clone $spjQuery)->whereNotIn('status', ['dicairkan', 'ditolak'])->count();

        return [
            Stat::make('Jadwal Kerja', $jadwal?->nama ?? 'Reguler')
                ->description("Sen–Kam {$jamSeninKamis} • Jum {$jamJumat} (±{$toleransi}m)")
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary')
                ->extraAttributes(['class' => 'sakti-stat-card sakti-stat-indigo']),

            Stat::make('Status Pegawai', "{$statusAktif} ({$tipeKerja})")
                ->description('Kuota Cuti: ' . (int) ($user->kuota_cuti ?? 12) . ' hari')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color($user->aktif ? 'success' : 'danger')
                ->extraAttributes(['class' => 'sakti-stat-card ' . ($user->aktif ? 'sakti-stat-emerald' : 'sakti-stat-rose')]),

            Stat::make('Jabatan & Unit', $user->jabatan?->nama ?? '-')
                ->description(collect([
                    $user->bidang?->nama ?? 'Unit Khusus',
                    $user->jfPenempatan ? 'JF: ' . $user->jfPenempatan->name : null,
                ])->filter()->implode(' • '))
                ->descriptionIcon('heroicon-m-building-office')
                ->color('info')
                ->url(UserResource::canAccess() ? UserResource::getUrl('index') : null)
                ->extraAttributes(['class' => 'sakti-stat-card sakti-stat-sky']),

            Stat::make('Laporan LHK', 'Kinerja Harian')
                ->description('Kelola laporan kerja harian')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('primary')
                ->url(LhkResource::canAccess() ? LhkResource::getUrl('index') : null)
                ->extraAttributes(['class' => 'sakti-stat-card sakti-stat-slate']),

            Stat::make('Pengajuan SPJ', "{$totalSpj} Berkas")
                ->description("{$spjProses} dalam proses • Klik untuk lihat")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning')
                ->url(PengajuanSpjResource::canAccess() ? PengajuanSpjResource::getUrl('index') : null)
                ->extraAttributes(['class' => 'sakti-stat-card sakti-stat-amber']),

            Stat::make('Presensi Mobile', 'Absen Online')
                ->description('Buka aplikasi presensi pegawai')
                ->descriptionIcon('heroicon-m-device-phone-mobile')
                ->color('success')
                ->url(route('mobile.dashboard'), shouldOpenInNewTab: true)
                ->extraAttributes(['class' => 'sakti-stat-card sakti-stat-teal']),
        ];
    }
}
