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

        // 2. Khusus HARI INI: Variasi realistis status kehadiran
        // Doni Aries & sebagian besar pegawai: Hadir Masuk terisi, Pulang KOSONG (Siap coba Absen Pulang)
        // Dilengkapi variasi: Dinas Luar, Izin, Cuti, Sakit, Terlambat, dan Alpa agar data lengkap & hidup
        $this->command->info("Menyiapkan absensi HARI INI ({$today->format('Y-m-d')}): Variasi lengkap kehadiran...");
        $todayStr = $today->format('Y-m-d');

        // Bersihkan log dan data absensi hari ini agar bersih dan konsisten
        LogAbsensi::whereDate('waktu_absensi', $todayStr)->delete();
        AbsensiHarian::where('tanggal', $todayStr)->delete();

        // Pisahkan user non-Doni Aries untuk dialokasikan statusnya secara teratur
        $otherUsers = $users->filter(fn ($u) => $u->id !== 5)->values();

        // Alokasi indeks spesifik untuk variasi realistis
        $dinasLuarUsers = $otherUsers->slice(0, 4);   // 4 orang DL
        $izinUsers      = $otherUsers->slice(4, 3);   // 3 orang Izin
        $sakitUsers     = $otherUsers->slice(7, 2);   // 2 orang Sakit
        $cutiUsers      = $otherUsers->slice(9, 1);   // 1 orang Cuti
        $alpaUsers      = $otherUsers->slice(10, 1);  // 1 orang Alpa
        $terlambatUsers = $otherUsers->slice(11, 5);  // 5 orang Terlambat
        $hadirLainUsers = $otherUsers->slice(16);     // Sisanya hadir normal

        // 1) Doni Aries (User ID 5): Hadir Tepat Waktu, Pulang KOSONG (Siap coba demo tombol Absen Pulang)
        $doniUser = $users->firstWhere('id', 5) ?? $users->first();
        if ($doniUser) {
            $logDoni = LogAbsensi::create([
                'user_id' => $doniUser->id,
                'jenis' => 'masuk',
                'waktu_absensi' => "{$todayStr} 07:22:15",
                'nomor_absensi' => (string) ($doniUser->nomor_absensi ?? $doniUser->id),
                'perangkat' => 'mobile',
                'metode_verifikasi' => 'gps',
                'data_asli' => [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'status_lokasi' => 'dalam_radius',
                ],
            ]);

            AbsensiHarian::create([
                'user_id' => $doniUser->id,
                'tanggal' => $todayStr,
                'status' => 'hadir',
                'jenis_kehadiran' => 'wfo',
                'log_masuk_id' => $logDoni->id,
                'jam_masuk' => '07:22:15',
                'latitude_masuk' => $lat,
                'longitude_masuk' => $lng,
                'log_pulang_id' => null,
                'jam_pulang' => null,
                'latitude_pulang' => null,
                'longitude_pulang' => null,
                'is_absen_mobile' => true,
                'keterangan' => 'Hadir Masuk (Siap Uji Absen Pulang)',
            ]);
        }

        // 2) Pegawai Hadir Normal (Tepat Waktu, Pulang KOSONG)
        foreach ($hadirLainUsers as $user) {
            $logMasuk = LogAbsensi::create([
                'user_id' => $user->id,
                'jenis' => 'masuk',
                'waktu_absensi' => "{$todayStr} 07:25:00",
                'nomor_absensi' => (string) ($user->nomor_absensi ?? $user->id),
                'perangkat' => 'mobile',
                'metode_verifikasi' => 'gps',
                'data_asli' => [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'status_lokasi' => 'dalam_radius',
                ],
            ]);

            AbsensiHarian::create([
                'user_id' => $user->id,
                'tanggal' => $todayStr,
                'status' => 'hadir',
                'jenis_kehadiran' => 'wfo',
                'log_masuk_id' => $logMasuk->id,
                'jam_masuk' => '07:25:00',
                'latitude_masuk' => $lat,
                'longitude_masuk' => $lng,
                'log_pulang_id' => null,
                'jam_pulang' => null,
                'is_absen_mobile' => true,
            ]);
        }

        // 3) Pegawai Terlambat Masuk (Jam 08:18 WIB)
        foreach ($terlambatUsers as $user) {
            $logMasuk = LogAbsensi::create([
                'user_id' => $user->id,
                'jenis' => 'masuk',
                'waktu_absensi' => "{$todayStr} 08:18:30",
                'nomor_absensi' => (string) ($user->nomor_absensi ?? $user->id),
                'perangkat' => 'mobile',
                'metode_verifikasi' => 'gps',
                'data_asli' => [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'status_lokasi' => 'dalam_radius',
                ],
            ]);

            AbsensiHarian::create([
                'user_id' => $user->id,
                'tanggal' => $todayStr,
                'status' => 'terlambat',
                'jenis_kehadiran' => 'wfo',
                'log_masuk_id' => $logMasuk->id,
                'jam_masuk' => '08:18:30',
                'latitude_masuk' => $lat,
                'longitude_masuk' => $lng,
                'log_pulang_id' => null,
                'jam_pulang' => null,
                'is_absen_mobile' => true,
                'keterangan' => 'Terlambat masuk kerja',
            ]);
        }

        // 4) Pegawai Dinas Luar (Lengkap Masuk & Pulang)
        foreach ($dinasLuarUsers as $user) {
            AbsensiHarian::create([
                'user_id' => $user->id,
                'tanggal' => $todayStr,
                'status' => 'dinas_luar',
                'jenis_kehadiran' => 'dinas_luar',
                'jam_masuk' => '07:30:00',
                'jam_pulang' => '16:00:00',
                'keterangan' => 'Tugas Dinas Luar Koordinasi Pemprov Sumbar',
            ]);
        }

        // 5) Pegawai Izin
        foreach ($izinUsers as $user) {
            AbsensiHarian::create([
                'user_id' => $user->id,
                'tanggal' => $todayStr,
                'status' => 'izin',
                'jenis_kehadiran' => 'wfo',
                'jam_masuk' => null,
                'jam_pulang' => null,
                'keterangan' => 'Izin keperluan urusan keluarga',
            ]);
        }

        // 6) Pegawai Sakit
        foreach ($sakitUsers as $user) {
            AbsensiHarian::create([
                'user_id' => $user->id,
                'tanggal' => $todayStr,
                'status' => 'sakit',
                'jenis_kehadiran' => 'wfo',
                'jam_masuk' => null,
                'jam_pulang' => null,
                'keterangan' => 'Sakit demam / istirahat dokter',
            ]);
        }

        // 7) Pegawai Cuti
        foreach ($cutiUsers as $user) {
            AbsensiHarian::create([
                'user_id' => $user->id,
                'tanggal' => $todayStr,
                'status' => 'cuti',
                'jenis_kehadiran' => 'wfo',
                'jam_masuk' => null,
                'jam_pulang' => null,
                'keterangan' => 'Cuti tahunan yang disetujui',
            ]);
        }

        // 8) Pegawai Tanpa Keterangan / Alpa
        foreach ($alpaUsers as $user) {
            AbsensiHarian::create([
                'user_id' => $user->id,
                'tanggal' => $todayStr,
                'status' => 'tidak_hadir',
                'jenis_kehadiran' => 'wfo',
                'jam_masuk' => null,
                'jam_pulang' => null,
                'keterangan' => 'Tanpa keterangan (Alpa)',
            ]);
        }

        $this->command->info("Absensi berhasil di-seed! Hari ini ({$todayStr}): Variasi lengkap (Hadir, Terlambat, DL, Izin, Cuti, Sakit, Alpa) telah tersinkronisasi 100% dengan total pegawai.");
    }
}
