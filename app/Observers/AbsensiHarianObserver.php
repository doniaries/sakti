<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\AbsensiHarian;
use App\Services\ActivityLogger;
use Illuminate\Support\Carbon;

class AbsensiHarianObserver
{
    public function updated(AbsensiHarian $absen): void
    {
        if ($absen->wasChanged(['jam_masuk', 'jam_pulang', 'status'])) {
            $tgl = $absen->tanggal ? Carbon::parse($absen->tanggal)->format('d/m/Y') : '-';
            $namaUser = $absen->user?->name ?? 'Pegawai';

            $aksi = 'Pembaruan Kehadiran';
            if ($absen->wasChanged('jam_pulang') && filled($absen->jam_pulang)) {
                $aksi = "Absen Pulang ({$absen->jam_pulang})";
            } elseif ($absen->wasChanged('jam_masuk') && filled($absen->jam_masuk)) {
                $aksi = "Absen Masuk ({$absen->jam_masuk})";
            }

            ActivityLogger::log(
                event: 'update',
                module: 'Presensi',
                description: "Presensi pegawai {$namaUser} ({$tgl}): {$aksi}, status: {$absen->status}",
                subject: $absen,
                properties: [
                    'tanggal' => $absen->tanggal,
                    'status' => $absen->status,
                    'jam_masuk' => $absen->jam_masuk,
                    'jam_pulang' => $absen->jam_pulang,
                ]
            );
        }
    }
}

