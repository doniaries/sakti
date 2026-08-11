<?php

namespace App\Services;

use App\Models\AbsensiHarian;
use App\Models\LogAbsensi;
use Illuminate\Support\Carbon;

class AbsensiHarianService
{
    /**
     * Proses log absensi mentah menjadi absensi_harian.
     *
     * @param string|null $tanggal  Format: Y-m-d. Jika null, proses semua tanggal.
     * @param int|null    $userId   Jika null, proses semua user.
     * @return int Jumlah record absensi_harian yang dibuat/diperbarui.
     */
    public function prosesLog(?string $tanggal = null, ?int $userId = null): int
    {
        $processed = 0;

        // Ambil data log yang sudah punya user_id (sudah dikenali)
        $query = LogAbsensi::query()
            ->whereNotNull('user_id');

        if ($tanggal) {
            $query->whereDate('waktu_absensi', $tanggal);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Kelompokkan per user per tanggal
        $logs = $query->orderBy('waktu_absensi')->get();

        $grouped = $logs->groupBy(function ($log) {
            return $log->user_id . '|' . Carbon::parse($log->waktu_absensi)->toDateString();
        });

        foreach ($grouped as $groupKey => $logGroup) {
            [$uid, $tanggalLog] = explode('|', $groupKey, 2);

            /** @var \App\Models\LogAbsensi $logMasuk */
            $logMasuk = $logGroup->first(); // log terlama = masuk

            /** @var \App\Models\LogAbsensi|null $logPulang */
            $logPulang = $logGroup->count() > 1 ? $logGroup->last() : null;

            $jamMasuk  = Carbon::parse($logMasuk->waktu_absensi)->format('H:i:s');
            $jamPulang = $logPulang ? Carbon::parse($logPulang->waktu_absensi)->format('H:i:s') : null;

            AbsensiHarian::updateOrCreate(
                [
                    'user_id' => (int) $uid,
                    'tanggal' => $tanggalLog,
                ],
                [
                    'jam_masuk'     => $jamMasuk,
                    'jam_pulang'    => $jamPulang,
                    'log_masuk_id'  => $logMasuk->id,
                    'log_pulang_id' => $logPulang?->id,
                    'status'        => 'hadir',
                ]
            );

            $processed++;
        }

        return $processed;
    }
}
