<?php

namespace Database\Seeders;

use App\Models\AbsensiHarian;
use App\Models\JadwalKerja;
use App\Models\LogAbsensi;
use App\Models\PengaturanAplikasi;
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

        $pengaturan = PengaturanAplikasi::getPengaturan();
        $lat = (float) ($pengaturan->latitude_kantor ?? -0.69752300);
        $lng = (float) ($pengaturan->longitude_kantor ?? 100.98547200);

        $today = Carbon::today();

        // 1. Seed histori 4 hari kerja ke belakang (Senin - kemarin) agar riwayat kehadiran terisi lengkap
        $this->command->info('Men-generate histori absensi beberapa hari kerja sebelumnya...');
        for ($i = 4; $i >= 1; $i--) {
            $tglHistori = $today->copy()->subDays($i);

            // Lewati hari Sabtu dan Minggu
            if ($tglHistori->isWeekend()) {
                continue;
            }

            $tglStr = $tglHistori->format('Y-m-d');

            foreach ($users as $user) {
                $nomorAbsensi = (string) ($user->nomor_absensi ?? $user->id);

                // Buat Log Masuk Masa Lalu (07:25:00)
                $logMasuk = LogAbsensi::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'waktu_absensi' => "{$tglStr} 07:25:00",
                        'jenis' => 'masuk',
                    ],
                    [
                        'nomor_absensi' => $nomorAbsensi,
                        'perangkat' => 'mobile',
                        'metode_verifikasi' => 'gps',
                        'data_asli' => [
                            'latitude' => $lat,
                            'longitude' => $lng,
                            'status_lokasi' => 'dalam_radius',
                        ],
                    ]
                );

                // Buat Log Pulang Masa Lalu (16:05:00)
                $logPulang = LogAbsensi::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'waktu_absensi' => "{$tglStr} 16:05:00",
                        'jenis' => 'pulang',
                    ],
                    [
                        'nomor_absensi' => $nomorAbsensi,
                        'perangkat' => 'mobile',
                        'metode_verifikasi' => 'gps',
                        'data_asli' => [
                            'latitude' => $lat,
                            'longitude' => $lng,
                            'status_lokasi' => 'dalam_radius',
                        ],
                    ]
                );

                // Absensi Harian Lengkap
                AbsensiHarian::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'tanggal' => $tglStr,
                    ],
                    [
                        'status' => 'hadir',
                        'log_masuk_id' => $logMasuk->id,
                        'jam_masuk' => '07:25:00',
                        'latitude_masuk' => $lat,
                        'longitude_masuk' => $lng,
                        'log_pulang_id' => $logPulang->id,
                        'jam_pulang' => '16:05:00',
                        'latitude_pulang' => $lat,
                        'longitude_pulang' => $lng,
                        'is_absen_mobile' => true,
                    ]
                );
            }
        }

        // 2. Khusus HARI INI: HANYA ISI ABSEN MASUK, KOSONGKAN ABSEN PULANG
        // Agar pengguna/admin dapat langsung mencoba dan mendemokan tombol ABSEN PULANG!
        $this->command->info("Menyiapkan absensi HARI INI ({$today->format('Y-m-d')}): Masuk terisi (07:25 WIB), Pulang KOSONG (Siap coba Absen Pulang)...");
        $todayStr = $today->format('Y-m-d');

        // Bersihkan log pulang hari ini jika sebelumnya ada
        LogAbsensi::where('jenis', 'pulang')
            ->whereDate('waktu_absensi', $todayStr)
            ->delete();

        foreach ($users as $user) {
            $nomorAbsensi = (string) ($user->nomor_absensi ?? $user->id);

            // Buat / ambil Log Masuk Pagi Hari Ini (07:25:00 - Tepat Waktu)
            $logMasukHariIni = LogAbsensi::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'jenis' => 'masuk',
                    'waktu_absensi' => "{$todayStr} 07:25:00",
                ],
                [
                    'nomor_absensi' => $nomorAbsensi,
                    'perangkat' => 'mobile',
                    'metode_verifikasi' => 'gps',
                    'data_asli' => [
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'status_lokasi' => 'dalam_radius',
                    ],
                ]
            );

            // Update Absensi Harian Hari Ini: jam_masuk terisi, jam_pulang KOSONG!
            AbsensiHarian::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'tanggal' => $todayStr,
                ],
                [
                    'status' => 'hadir',
                    'log_masuk_id' => $logMasukHariIni->id,
                    'jam_masuk' => '07:25:00',
                    'latitude_masuk' => $lat,
                    'longitude_masuk' => $lng,
                    'log_pulang_id' => null,
                    'jam_pulang' => null,
                    'latitude_pulang' => null,
                    'longitude_pulang' => null,
                    'is_absen_mobile' => true,
                ]
            );
        }

        $this->command->info("Absensi berhasil di-seed! Hari ini ({$todayStr}): Seluruh pegawai telah tercatat MASUK pagi dan KOSONG pada jam pulang, siap untuk uji coba tombol Absen Pulang.");
    }
}
