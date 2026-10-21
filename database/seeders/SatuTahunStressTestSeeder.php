<?php

namespace Database\Seeders;

use App\Enums\JenisKehadiran;
use App\Enums\StatusApel;
use App\Enums\StatusKehadiran;
use App\Models\HariLibur;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatuTahunStressTestSeeder extends Seeder
{
    /**
     * Konfigurasi Aktif / Non-Aktif Seeder Stress Test 1 Tahun.
     * Ubah menjadi true jika ingin menjalankan seeder ini di server online.
     */
    protected bool $enabled = false;

    /**
     * Seed ribuan data absensi selama 1 tahun (365 hari ke belakang)
     * untuk seluruh pegawai (~20.000+ records).
     */
    public function run(): void
    {
        if (! $this->enabled) {
            $this->command->warn("SatuTahunStressTestSeeder saat ini berstatus DISABLED.");
            $this->command->info("Untuk mengaktifkan, ubah \$enabled = true di SatuTahunStressTestSeeder.php");
            return;
        }

        $userIds = User::pluck('id')->toArray();
        if (empty($userIds)) {
            $this->command->error("Tidak ada user ditemukan.");
            return;
        }

        $endDate = Carbon::today();
        $startDate = (clone $endDate)->subYear();

        $this->command->info("Memulai seeding 1 tahun data presensi ($startDate s.d $endDate)...");

        // Cache daftar hari libur
        $liburDates = HariLibur::whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->pluck('tanggal')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        $period = CarbonPeriod::create($startDate, $endDate);
        $totalRecords = 0;
        $batch = [];

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $isWeekend = $date->isWeekend();
            $isLibur = in_array($dateStr, $liburDates);

            // Lewati akhir pekan atau hari libur resmi (sesuai aturan instansi)
            if ($isWeekend || $isLibur) {
                continue;
            }

            $isFriday = $date->dayOfWeekIso === 5;
            $jamPulangResmi = $isFriday ? '16:30:00' : '16:00:00';

            foreach ($userIds as $userId) {
                $rand = rand(1, 100);

                if ($rand <= 80) {
                    $status = StatusKehadiran::HADIR->value;
                    $jenis = JenisKehadiran::WFO->value;
                    $jamMasuk = sprintf('07:%02d:%02d', rand(0, 29), rand(0, 59));
                    $jamPulang = sprintf('%s:%02d:%02d', substr($jamPulangResmi, 0, 2), rand(0, 30), rand(0, 59));
                } elseif ($rand <= 90) {
                    $status = StatusKehadiran::TERLAMBAT->value;
                    $jenis = JenisKehadiran::WFO->value;
                    $jamMasuk = sprintf('07:%02d:%02d', rand(46, 59), rand(0, 59));
                    $jamPulang = sprintf('%s:%02d:%02d', substr($jamPulangResmi, 0, 2), rand(0, 20), rand(0, 59));
                } elseif ($rand <= 95) {
                    $status = rand(0, 1) ? StatusKehadiran::SAKIT->value : StatusKehadiran::IZIN->value;
                    $jenis = JenisKehadiran::WFO->value;
                    $jamMasuk = null;
                    $jamPulang = null;
                } elseif ($rand <= 98) {
                    $status = StatusKehadiran::DINAS_LUAR->value;
                    $jenis = JenisKehadiran::DINAS_LUAR->value;
                    $jamMasuk = '07:30:00';
                    $jamPulang = $jamPulangResmi;
                } else {
                    $status = StatusKehadiran::TIDAK_HADIR->value;
                    $jenis = JenisKehadiran::WFO->value;
                    $jamMasuk = null;
                    $jamPulang = null;
                }

                $batch[] = [
                    'user_id' => $userId,
                    'tanggal' => $dateStr,
                    'jam_masuk' => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                    'status' => $status,
                    'jenis_kehadiran' => $jenis,
                    'status_apel' => $date->dayOfWeekIso === 1 ? (rand(1, 10) > 2 ? StatusApel::IKUT->value : StatusApel::TIDAK_IKUT->value) : null,
                    'is_absen_mesin' => $jamMasuk ? true : false,
                    'is_absen_mobile' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $totalRecords++;

                if (count($batch) >= 1000) {
                    DB::table('absensi_harian')->upsert(
                        $batch,
                        ['user_id', 'tanggal'],
                        ['jam_masuk', 'jam_pulang', 'status', 'jenis_kehadiran', 'status_apel', 'is_absen_mesin', 'updated_at']
                    );
                    $batch = [];
                }
            }
        }

        if (!empty($batch)) {
            DB::table('absensi_harian')->upsert(
                $batch,
                ['user_id', 'tanggal'],
                ['jam_masuk', 'jam_pulang', 'status', 'jenis_kehadiran', 'status_apel', 'is_absen_mesin', 'updated_at']
            );
        }

        $this->command->info("Selesai! Berhasil meng-generate $totalRecords data absensi harian untuk 1 tahun.");
    }
}
