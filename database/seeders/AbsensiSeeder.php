<?php

namespace Database\Seeders;

use App\Models\AbsensiHarian;
use App\Models\LogAbsensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AbsensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->warn('Tidak ada user untuk di-seed absensinya. Buat user terlebih dahulu.');
            return;
        }

        $tanggal = Carbon::today(); // Hari ini

        foreach ($users as $user) {
            // Cek apakah sudah ada absen hari ini
            $absenHarian = AbsensiHarian::firstOrCreate(
                ['user_id' => $user->id, 'tanggal' => $tanggal->format('Y-m-d')],
                ['status' => 'hadir']
            );

            // Buat log masuk (jam 08:00) jika belum ada
            if (!$absenHarian->log_masuk_id) {
                $logMasuk = LogAbsensi::create([
                    'user_id' => $user->id,
                    'tanggal' => $tanggal->format('Y-m-d'),
                    'jam' => '08:00:00',
                    'jenis' => 'masuk',
                    'latitude' => -6.200000,
                    'longitude' => 106.816666,
                    'status_lokasi' => 'dalam_radius',
                ]);
                $absenHarian->log_masuk_id = $logMasuk->id;
                $absenHarian->jam_masuk = '08:00:00';
            }

            // Buat log pulang (jam 17:00) jika belum ada
            if (!$absenHarian->log_pulang_id) {
                $logPulang = LogAbsensi::create([
                    'user_id' => $user->id,
                    'tanggal' => $tanggal->format('Y-m-d'),
                    'jam' => '17:00:00',
                    'jenis' => 'pulang',
                    'latitude' => -6.200000,
                    'longitude' => 106.816666,
                    'status_lokasi' => 'dalam_radius',
                ]);
                $absenHarian->log_pulang_id = $logPulang->id;
                $absenHarian->jam_pulang = '17:00:00';
            }

            $absenHarian->save();
        }

        $this->command->info('Data absensi (masuk dan pulang) untuk tanggal hari ini (' . $tanggal->format('Y-m-d') . ') berhasil di-seed.');
    }
}
