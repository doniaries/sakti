<?php

namespace Database\Seeders;

use App\Models\JenisIzin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SimulasiEnamBulanSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->where('is_aktif', true)->get(['id', 'jadwal_kerja_id', 'status_kerja']);
        if ($users->isEmpty()) {
            $this->command?->warn('Tidak ada user aktif untuk disimulasikan.');

            return;
        }

        $jenisIzins = JenisIzin::query()->pluck('id', 'kategori');
        $start = Carbon::today()->subMonths(6);
        $end = Carbon::yesterday();

        $absensiRows = [];
        $izinRows = [];
        $now = now();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->isWeekend()) {
                continue;
            }

            $tgl = $date->toDateString();

            foreach ($users as $user) {
                $roll = random_int(1, 100);

                if ($roll <= 75) {
                    $status = 'hadir';
                    $jamMasuk = sprintf('07:%02d:00', random_int(5, 29));
                    $jamPulang = sprintf('16:%02d:00', random_int(0, 30));
                } elseif ($roll <= 87) {
                    $status = 'terlambat';
                    $jamMasuk = sprintf('07:%02d:00', random_int(35, 59));
                    $jamPulang = sprintf('16:%02d:00', random_int(0, 30));
                } elseif ($roll <= 92) {
                    $status = 'dinas_luar';
                    $jamMasuk = '07:20:00';
                    $jamPulang = '16:10:00';
                } elseif ($roll <= 96) {
                    $status = 'izin';
                    $jamMasuk = null;
                    $jamPulang = null;
                    $jenisId = $jenisIzins['izin'] ?? $jenisIzins->first();
                    if ($jenisId) {
                        $izinRows[] = [
                            'user_id' => $user->id,
                            'jenis_izin_id' => $jenisId,
                            'tanggal_mulai' => $tgl,
                            'tanggal_selesai' => $tgl,
                            'jumlah_hari' => 1,
                            'alasan' => 'Simulasi 6 Bulan - Keperluan Pribadi',
                            'status' => 'disetujui',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                } elseif ($roll <= 98) {
                    $status = 'sakit';
                    $jamMasuk = null;
                    $jamPulang = null;
                } else {
                    $status = 'tidak_hadir';
                    $jamMasuk = null;
                    $jamPulang = null;
                }

                $absensiRows[] = [
                    'user_id' => $user->id,
                    'tanggal' => $tgl,
                    'jam_masuk' => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                    'status' => $status,
                    'jenis_kehadiran' => $user->status_kerja === 'wfh' ? 'wfh' : 'wfo',
                    'jadwal_kerja_id' => $user->jadwal_kerja_id,
                    'keterangan' => 'Simulasi 6 Bulan',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($absensiRows, 500) as $chunk) {
            DB::table('absensi_harian')->insertOrIgnore($chunk);
        }

        foreach (array_chunk($izinRows, 500) as $chunk) {
            DB::table('pengajuan_izin')->insert($chunk);
        }

        $this->command?->info(
            'Simulasi 6 bulan selesai: '
            . count($absensiRows) . ' absensi_harian & '
            . count($izinRows) . ' pengajuan_izin.'
        );
    }
}
