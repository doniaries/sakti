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
        
        // Mencari 5 hari kerja terakhir dari hari ini
        $hariKerja = [];
        $tanggalIterasi = Carbon::now()->startOfDay();
        
        while (count($hariKerja) < 5) {
            if ($tanggalIterasi->isWeekday()) {
                $hariKerja[] = $tanggalIterasi->copy();
            }
            $tanggalIterasi->subDay();
        }
        $hariKerja = array_reverse($hariKerja); // Urutkan dari terlama ke terbaru

        $statuses = ['hadir', 'hadir', 'hadir', 'hadir', 'hadir', 'izin', 'sakit', 'tidak_hadir']; // Probabilitas banyak hadir

        foreach ($users as $user) {
            foreach ($hariKerja as $tanggal) {
                $statusHariIni = $statuses[array_rand($statuses)];

                if ($statusHariIni === 'hadir') {
                    // Jam masuk random antara 07:00 - 08:00
                    $jamMasuk = $tanggal->copy()->setTime(7, rand(0, 59), rand(0, 59));
                    
                    // Jam pulang random antara 16:00 - 17:00
                    $jamPulang = $tanggal->copy()->setTime(16, rand(0, 59), rand(0, 59));

                    // Seeder Masuk (Apel Pagi dengan Mesin)
                    $logMasuk = LogAbsensi::create([
                        'user_id' => $user->id,
                        'mesin_absensi_id' => 1,
                        'nomor_absensi' => $user->id,
                        'waktu_absensi' => $jamMasuk,
                        'sumber' => 'mesin',
                        'metode_verifikasi' => 'fingerprint',
                        'kode_kerja' => 'masuk',
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
                    ]);

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
                } elseif ($statusHariIni === 'izin' || $statusHariIni === 'sakit') {
                    // Izin / Sakit diisi dari mobile, tidak ada log mesin
                    AbsensiHarian::create([
                        'user_id' => $user->id,
                        'tanggal' => $tanggal->format('Y-m-d'),
                        'jam_masuk' => null,
                        'jam_pulang' => null,
                        'log_masuk_id' => null,
                        'log_pulang_id' => null,
                        'status' => $statusHariIni,
                        'keterangan' => ucfirst($statusHariIni) . ' alasan pribadi',
                        'is_absen_mesin' => false,
                        'is_absen_mobile' => true, // Diisi dari mobile
                        'status_apel' => 'tidak ikut apel',
                    ]);
                } else {
                    // Tanpa keterangan (tidak_hadir)
                    AbsensiHarian::create([
                        'user_id' => $user->id,
                        'tanggal' => $tanggal->format('Y-m-d'),
                        'jam_masuk' => null,
                        'jam_pulang' => null,
                        'log_masuk_id' => null,
                        'log_pulang_id' => null,
                        'status' => 'tidak_hadir',
                        'keterangan' => 'Tanpa Keterangan',
                        'is_absen_mesin' => false,
                        'is_absen_mobile' => false,
                        'status_apel' => 'tidak ikut apel',
                    ]);
                }
            }
        }
    }
}
