<?php

namespace Database\Seeders;

use App\Models\LogAbsensi;
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
        
        $hariIni = Carbon::now()->startOfDay();

        foreach ($users as $user) {
            for ($i = 0; $i < 3; $i++) {
                $tanggal = $hariIni->copy()->subDays($i);

                // Jam masuk random antara 07:00 - 08:00
                $jamMasuk = $tanggal->copy()->addHours(7)->addMinutes(rand(0, 59));
                
                // Jam pulang random antara 16:00 - 17:00
                $jamPulang = $tanggal->copy()->addHours(16)->addMinutes(rand(0, 59));

                // Seeder Masuk
                LogAbsensi::create([
                    'user_id' => $user->id,
                    'mesin_absensi_id' => 1,
                    'nomor_absensi' => $user->id,
                    'waktu_absensi' => $jamMasuk,
                    'sumber' => 'mesin',
                    'metode_verifikasi' => 'fingerprint',
                    'kode_kerja' => 'masuk',
                    'kode_log_asli' => null,
                    'data_asli' => null,
                ]);

                // Seeder Pulang
                LogAbsensi::create([
                    'user_id' => $user->id,
                    'mesin_absensi_id' => 1,
                    'nomor_absensi' => $user->id,
                    'waktu_absensi' => $jamPulang,
                    'sumber' => 'mesin',
                    'metode_verifikasi' => 'fingerprint',
                    'kode_kerja' => 'pulang',
                    'kode_log_asli' => null,
                    'data_asli' => null,
                ]);
            }
        }
    }
}
