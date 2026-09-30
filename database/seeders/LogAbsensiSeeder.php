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
        
        // Mencari 7 hari ke belakang (termasuk hari libur jika ingin diisi, atau bisa filter isWeekday)
        $hariKerja = [];
        $tanggalIterasi = Carbon::now()->startOfDay();
        
        // Kita ambil 7 hari terakhir agar data seminggu penuh terisi
        for ($i = 0; $i < 7; $i++) {
            // Jika LHK hanya bisa diisi hari kerja, aktifkan kondisi isWeekday()
            if ($tanggalIterasi->isWeekday()) {
                $hariKerja[] = $tanggalIterasi->copy();
            }
            $tanggalIterasi->subDay();
        }
        $hariKerja = array_reverse($hariKerja); // Urutkan dari terlama ke terbaru

        foreach ($users as $user) {
            foreach ($hariKerja as $tanggal) {
                // Semua hari diatur menjadi HADIR agar bisa mengisi LHK (karena butuh jam datang & pulang)
                
                // Jam masuk random antara 07:00 - 08:00
                $jamMasuk = $tanggal->copy()->setTime(7, rand(0, 59), rand(0, 59));
                
                // Jam pulang random antara 16:00 - 17:00
                $jamPulang = $tanggal->copy()->setTime(16, rand(0, 59), rand(0, 59));

                // Seeder Masuk (Apel Pagi dengan Mesin)
                $logMasuk = LogAbsensi::updateOrCreate(
                    ['user_id' => $user->id, 'jenis' => 'masuk', 'waktu_absensi' => $jamMasuk],
                    [
                        'mesin_absensi_id' => 1,
                        'nomor_absensi' => $user->id,
                        'perangkat' => 'mesin',
                        'metode_verifikasi' => 'fingerprint',
                    ]
                );

                // Seeder Pulang (Menggunakan Mobile App)
                $logPulang = LogAbsensi::updateOrCreate(
                    ['user_id' => $user->id, 'jenis' => 'pulang', 'waktu_absensi' => $jamPulang],
                    [
                        'mesin_absensi_id' => null,
                        'nomor_absensi' => $user->id,
                        'perangkat' => 'mobile',
                        'metode_verifikasi' => 'gps',
                    ]
                );

                AbsensiHarian::updateOrCreate(
                    ['user_id' => $user->id, 'tanggal' => $tanggal->format('Y-m-d')],
                    [
                        'jam_masuk' => $jamMasuk->format('H:i:s'),
                        'jam_pulang' => $jamPulang->format('H:i:s'),
                        'log_masuk_id' => $logMasuk->id,
                        'log_pulang_id' => $logPulang->id,
                        'status' => 'hadir',
                        'keterangan' => 'Hadir Tepat Waktu (Data Dummy LHK)',
                        'is_absen_mesin' => true,
                        'is_absen_mobile' => true,
                        'status_apel' => 'ikut apel',
                    ]
                );
            }
        }
    }
}
