<?php

namespace Database\Seeders;

use App\Models\LogAbsensi;
use App\Models\AbsensiHarian;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LogAbsensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        $hariIni = Carbon::now();
        $bulanSekarang = $hariIni->month;
        $tahunSekarang = $hariIni->year;

        // Mencari 5 hari kerja pertama di bulan ini
        $hariKerja = [];
        $tanggalIterasi = Carbon::create($tahunSekarang, $bulanSekarang, 1);
        
        while (count($hariKerja) < 5) {
            if ($tanggalIterasi->isWeekday()) {
                $hariKerja[] = $tanggalIterasi->copy();
            }
            $tanggalIterasi->addDay();
        }

        foreach ($users as $user) {
            foreach ($hariKerja as $tanggal) {
                // Jam masuk random antara 07:00 - 08:00
                $jamMasuk = $tanggal->copy()->setTime(7, rand(0, 59), rand(0, 59));
                
                // Jam pulang random antara 16:00 - 17:00
                $jamPulang = $tanggal->copy()->setTime(16, rand(0, 59), rand(0, 59));

                // Seeder Masuk (Apel Pagi dengan Mesin)
                $logMasuk = LogAbsensi::create([
                    'user_id' => $user->id,
                    'mesin_absensi_id' => 1, // Asumsi mesin 1 adalah fingerprint kantor
                    'nomor_absensi' => $user->id,
                    'waktu_absensi' => $jamMasuk,
                    'sumber' => 'mesin',
                    'metode_verifikasi' => 'fingerprint',
                    'kode_kerja' => 'masuk',
                    'kode_log_asli' => null,
                    'data_asli' => null,
                ]);

                // Seeder Pulang (Menggunakan Mobile App)
                $logPulang = LogAbsensi::create([
                    'user_id' => $user->id,
                    'mesin_absensi_id' => null,
                    'nomor_absensi' => $user->id,
                    'waktu_absensi' => $jamPulang,
                    'sumber' => 'mobile',
                    'metode_verifikasi' => 'gps',
                    'kode_kerja' => 'pulang',
                    'kode_log_asli' => null,
                    'data_asli' => null,
                ]);

                // Seeder Absensi Harian (Kombinasi masuk dan pulang)
                AbsensiHarian::create([
                    'user_id' => $user->id,
                    'tanggal' => $tanggal->format('Y-m-d'),
                    'jam_masuk' => $jamMasuk->format('H:i:s'),
                    'jam_pulang' => $jamPulang->format('H:i:s'),
                    'log_masuk_id' => $logMasuk->id,
                    'log_pulang_id' => $logPulang->id,
                    'status' => 'hadir',
                    'keterangan' => 'Hadir Tepat Waktu',
                    'is_absen_mesin' => true,
                    'is_absen_mobile' => true,
                    'status_apel' => 'ikut apel',
                ]);
            }
        }
    }
}
