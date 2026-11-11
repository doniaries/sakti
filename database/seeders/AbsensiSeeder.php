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
        $officeLat = (float) ($pengaturan->latitude_kantor ?? -0.69752300);
        $officeLng = (float) ($pengaturan->longitude_kantor ?? 100.98547200);

        $today = Carbon::today();
        $this->command->info("Memulai seeding absensi 1 bulan penuh dan 1 minggu aktif berjalan...");

        // ──────────────────────────────────────────────────────────────────────────
        // 1. SEED HISTORI 30 HARI KE BELAKANG (1 BULAN PENUH)
        // ──────────────────────────────────────────────────────────────────────────
        $this->command->info("1. Men-generate riwayat presensi 30 hari ke belakang (1 bulan penuh)...");

        // Bersihkan data absensi 35 hari terakhir agar tidak duplikat
        $startDate = $today->copy()->subDays(35);
        AbsensiHarian::whereBetween('tanggal', [$startDate->format('Y-m-d'), $today->format('Y-m-d')])->delete();
        LogAbsensi::whereBetween('waktu_absensi', [$startDate->format('Y-m-d 00:00:00'), $today->format('Y-m-d 23:59:59')])->delete();

        for ($i = 30; $i >= 1; $i--) {
            $currentDate = $today->copy()->subDays($i);

            // Lewati hari Sabtu dan Minggu (hari libur kerja reguler)
            if ($currentDate->isWeekend()) {
                continue;
            }

            $tglStr = $currentDate->format('Y-m-d');
            $isJumat = ($currentDate->dayOfWeekIso === 5);
            $jamPulangResmi = $isJumat ? '16:30:00' : '16:00:00';

            foreach ($users as $index => $user) {
                $nomorAbsensi = (string) ($user->nomor_absensi ?? $user->id);
                $isWfh = ($user->status_kerja === 'wfh');

                // Koordinat: jika WFO di kantor (ada sedikit jitter realistis), jika WFH koordinat bebas
                $userLat = $isWfh ? ($officeLat + (sin($user->id * 10 + $i) * 0.05)) : ($officeLat + (cos($i + $user->id) * 0.0001));
                $userLng = $isWfh ? ($officeLng + (cos($user->id * 10 + $i) * 0.05)) : ($officeLng + (sin($i + $user->id) * 0.0001));
                $lokasiStatus = $isWfh ? 'wfh' : 'dalam_radius';

                // Buat variasi status berbasis hash deterministik user + hari agar pola kehadiran natural
                $hash = ($user->id * 13 + $i * 7) % 100;

                // A. SAKIT (3%)
                if ($hash < 3) {
                    AbsensiHarian::create([
                        'user_id' => $user->id,
                        'tanggal' => $tglStr,
                        'status' => 'sakit',
                        'jenis_kehadiran' => $isWfh ? 'wfh' : 'wfo',
                        'keterangan' => 'Surat Keterangan Dokter (Sakit)',
                    ]);
                    continue;
                }

                // B. IZIN (4%)
                if ($hash < 7) {
                    AbsensiHarian::create([
                        'user_id' => $user->id,
                        'tanggal' => $tglStr,
                        'status' => 'izin',
                        'jenis_kehadiran' => $isWfh ? 'wfh' : 'wfo',
                        'keterangan' => 'Izin urusan keluarga',
                    ]);
                    continue;
                }

                // C. CUTI (2%)
                if ($hash < 9) {
                    AbsensiHarian::create([
                        'user_id' => $user->id,
                        'tanggal' => $tglStr,
                        'status' => 'cuti',
                        'jenis_kehadiran' => $isWfh ? 'wfh' : 'wfo',
                        'keterangan' => 'Cuti Tahunan',
                    ]);
                    continue;
                }

                // D. DINAS LUAR (8%)
                if ($hash < 17) {
                    AbsensiHarian::create([
                        'user_id' => $user->id,
                        'tanggal' => $tglStr,
                        'status' => 'dinas_luar',
                        'jenis_kehadiran' => 'dinas_luar',
                        'jam_masuk' => '07:30:00',
                        'jam_pulang' => $jamPulangResmi,
                        'keterangan' => 'Surat Tugas Dinas Luar',
                    ]);
                    continue;
                }

                // E. TIDAK HADIR / ALPA (2%)
                if ($hash < 19) {
                    AbsensiHarian::create([
                        'user_id' => $user->id,
                        'tanggal' => $tglStr,
                        'status' => 'tidak_hadir',
                        'jenis_kehadiran' => $isWfh ? 'wfh' : 'wfo',
                        'keterangan' => 'Tanpa Keterangan (Alpa)',
                    ]);
                    continue;
                }

                // F. PULANG CEPAT / TIDAK AMBIL ABSEN PULANG (10%)
                // Datang pagi tercatat, tapi tidak tap pulang (atau tap pulang sebelum jam resmi)
                if ($hash < 29) {
                    $jamMasukRandom = '07:' . str_pad((string) (15 + ($hash % 14)), 2, '0', STR_PAD_LEFT) . ':00';
                    $logMasuk = LogAbsensi::create([
                        'user_id' => $user->id,
                        'nomor_absensi' => $nomorAbsensi,
                        'waktu_absensi' => "{$tglStr} {$jamMasukRandom}",
                        'perangkat' => 'mobile',
                        'metode_verifikasi' => 'gps',
                        'jenis' => 'masuk',
                        'data_asli' => ['latitude' => $userLat, 'longitude' => $userLng, 'status_lokasi' => $lokasiStatus],
                    ]);

                    // Sebagian tidak tap pulang sama sekali (belum pulang / dianggap pulang cepat)
                    $tapSore = ($hash % 2 === 0);
                    $jamPulangCepat = $tapSore ? '14:' . str_pad((string) (10 + ($hash % 40)), 2, '0', STR_PAD_LEFT) . ':00' : null;

                    $logPulangId = null;
                    if ($tapSore) {
                        $logPulang = LogAbsensi::create([
                            'user_id' => $user->id,
                            'nomor_absensi' => $nomorAbsensi,
                            'waktu_absensi' => "{$tglStr} {$jamPulangCepat}",
                            'perangkat' => 'mobile',
                            'metode_verifikasi' => 'gps',
                            'jenis' => 'pulang',
                            'data_asli' => ['latitude' => $userLat, 'longitude' => $userLng, 'status_lokasi' => $lokasiStatus, 'is_pulang_cepat' => true],
                        ]);
                        $logPulangId = $logPulang->id;
                    }

                    AbsensiHarian::create([
                        'user_id' => $user->id,
                        'tanggal' => $tglStr,
                        'status' => 'hadir',
                        'jenis_kehadiran' => $isWfh ? 'wfh' : 'wfo',
                        'log_masuk_id' => $logMasuk->id,
                        'jam_masuk' => $jamMasukRandom,
                        'latitude_masuk' => $userLat,
                        'longitude_masuk' => $userLng,
                        'log_pulang_id' => $logPulangId,
                        'jam_pulang' => $jamPulangCepat,
                        'latitude_pulang' => $logPulangId ? $userLat : null,
                        'longitude_pulang' => $logPulangId ? $userLng : null,
                        'is_absen_mobile' => true,
                        'keterangan' => $tapSore ? 'Pulang Cepat' : 'Tidak Absen Pulang (Dianggap Pulang Cepat)',
                    ]);
                    continue;
                }

                // G. TERLAMBAT (11%)
                if ($hash < 40) {
                    $jamMasukTerlambat = '08:' . str_pad((string) (5 + ($hash % 30)), 2, '0', STR_PAD_LEFT) . ':00';
                    $logMasuk = LogAbsensi::create([
                        'user_id' => $user->id,
                        'nomor_absensi' => $nomorAbsensi,
                        'waktu_absensi' => "{$tglStr} {$jamMasukTerlambat}",
                        'perangkat' => 'mobile',
                        'metode_verifikasi' => 'gps',
                        'jenis' => 'masuk',
                        'data_asli' => ['latitude' => $userLat, 'longitude' => $userLng, 'status_lokasi' => $lokasiStatus],
                    ]);

                    $jamPulangNormal = $isJumat ? '16:35:00' : '16:08:00';
                    $logPulang = LogAbsensi::create([
                        'user_id' => $user->id,
                        'nomor_absensi' => $nomorAbsensi,
                        'waktu_absensi' => "{$tglStr} {$jamPulangNormal}",
                        'perangkat' => 'mobile',
                        'metode_verifikasi' => 'gps',
                        'jenis' => 'pulang',
                        'data_asli' => ['latitude' => $userLat, 'longitude' => $userLng, 'status_lokasi' => $lokasiStatus],
                    ]);

                    AbsensiHarian::create([
                        'user_id' => $user->id,
                        'tanggal' => $tglStr,
                        'status' => 'terlambat',
                        'jenis_kehadiran' => $isWfh ? 'wfh' : 'wfo',
                        'log_masuk_id' => $logMasuk->id,
                        'jam_masuk' => $jamMasukTerlambat,
                        'latitude_masuk' => $userLat,
                        'longitude_masuk' => $userLng,
                        'log_pulang_id' => $logPulang->id,
                        'jam_pulang' => $jamPulangNormal,
                        'latitude_pulang' => $userLat,
                        'longitude_pulang' => $userLng,
                        'is_absen_mobile' => true,
                        'keterangan' => 'Terlambat Masuk',
                    ]);
                    continue;
                }

                // H. HADIR NORMAL / TEPAT WAKTU (60%)
                $jamMasukNormal = '07:' . str_pad((string) (15 + ($hash % 14)), 2, '0', STR_PAD_LEFT) . ':00';
                $jamPulangNormal = $isJumat ? '16:32:' . str_pad((string) ($hash % 50), 2, '0', STR_PAD_LEFT) : '16:05:' . str_pad((string) ($hash % 50), 2, '0', STR_PAD_LEFT);

                $logMasuk = LogAbsensi::create([
                    'user_id' => $user->id,
                    'nomor_absensi' => $nomorAbsensi,
                    'waktu_absensi' => "{$tglStr} {$jamMasukNormal}",
                    'perangkat' => 'mobile',
                    'metode_verifikasi' => 'gps',
                    'jenis' => 'masuk',
                    'data_asli' => ['latitude' => $userLat, 'longitude' => $userLng, 'status_lokasi' => $lokasiStatus],
                ]);

                $logPulang = LogAbsensi::create([
                    'user_id' => $user->id,
                    'nomor_absensi' => $nomorAbsensi,
                    'waktu_absensi' => "{$tglStr} {$jamPulangNormal}",
                    'perangkat' => 'mobile',
                    'metode_verifikasi' => 'gps',
                    'jenis' => 'pulang',
                    'data_asli' => ['latitude' => $userLat, 'longitude' => $userLng, 'status_lokasi' => $lokasiStatus],
                ]);

                AbsensiHarian::create([
                    'user_id' => $user->id,
                    'tanggal' => $tglStr,
                    'status' => 'hadir',
                    'jenis_kehadiran' => $isWfh ? 'wfh' : 'wfo',
                    'log_masuk_id' => $logMasuk->id,
                    'jam_masuk' => $jamMasukNormal,
                    'latitude_masuk' => $userLat,
                    'longitude_masuk' => $userLng,
                    'log_pulang_id' => $logPulang->id,
                    'jam_pulang' => $jamPulangNormal,
                    'latitude_pulang' => $userLat,
                    'longitude_pulang' => $userLng,
                    'is_absen_mobile' => true,
                ]);
            }
        }

        // ──────────────────────────────────────────────────────────────────────────
        // 2. SEED KHUSUS HARI INI (RABU) — MINGGU AKTIF
        // ──────────────────────────────────────────────────────────────────────────
        $todayStr = $today->format('Y-m-d');
        $this->command->info("2. Men-generate presensi HARI INI ({$todayStr}): Status aktif siap uji...");

        // Pisahkan user: Doni Aries (User ID 5 atau user pertama)
        $doniUser = $users->firstWhere('id', 5) ?? $users->first();
        $otherUsers = $users->filter(fn ($u) => $u->id !== $doniUser->id)->values();

        // Variasi alokasi untuk hari ini
        $dinasLuarUsers = $otherUsers->slice(0, 4);   // 4 orang DL
        $izinUsers      = $otherUsers->slice(4, 3);   // 3 orang Izin
        $sakitUsers     = $otherUsers->slice(7, 2);   // 2 orang Sakit
        $cutiUsers      = $otherUsers->slice(9, 1);   // 1 orang Cuti
        $alpaUsers      = $otherUsers->slice(10, 2);  // 2 orang Belum Hadir / Alpa
        $terlambatUsers = $otherUsers->slice(12, 5);  // 5 orang Terlambat (sudah masuk, belum pulang)
        $tidakPulangUsers = $otherUsers->slice(17, 6); // 6 orang Tidak Tap Pulang (status Pulang Cepat untuk uji konfirmasi admin)
        $hadirLainUsers = $otherUsers->slice(23);     // Sisanya hadir normal (sudah masuk, siap absen pulang)

        // 1) Doni Aries: Hadir Masuk Terisi (07:22:15 WIB), Jam Pulang KOSONG
        // (Siap untuk mendemokan tombol ABSEN PULANG langsung dari layar aplikasi!)
        $logDoni = LogAbsensi::create([
            'user_id' => $doniUser->id,
            'jenis' => 'masuk',
            'waktu_absensi' => "{$todayStr} 07:22:15",
            'nomor_absensi' => (string) ($doniUser->nomor_absensi ?? $doniUser->id),
            'perangkat' => 'mobile',
            'metode_verifikasi' => 'gps',
            'data_asli' => [
                'latitude' => $officeLat,
                'longitude' => $officeLng,
                'status_lokasi' => 'dalam_radius',
            ],
        ]);

        AbsensiHarian::create([
            'user_id' => $doniUser->id,
            'tanggal' => $todayStr,
            'status' => 'hadir',
            'jenis_kehadiran' => $doniUser->status_kerja === 'wfh' ? 'wfh' : 'wfo',
            'log_masuk_id' => $logDoni->id,
            'jam_masuk' => '07:22:15',
            'latitude_masuk' => $officeLat,
            'longitude_masuk' => $officeLng,
            'log_pulang_id' => null,
            'jam_pulang' => null,
            'is_absen_mobile' => true,
            'keterangan' => 'Hadir Masuk (Siap Demo Absen Pulang)',
        ]);

        // 2) Pegawai Hadir Normal (Sudah Datang, Belum Pulang)
        foreach ($hadirLainUsers as $user) {
            $isWfh = ($user->status_kerja === 'wfh');
            $uLat = $isWfh ? ($officeLat + 0.04) : $officeLat;
            $uLng = $isWfh ? ($officeLng + 0.04) : $officeLng;

            $logMasuk = LogAbsensi::create([
                'user_id' => $user->id,
                'jenis' => 'masuk',
                'waktu_absensi' => "{$todayStr} 07:24:45",
                'nomor_absensi' => (string) ($user->nomor_absensi ?? $user->id),
                'perangkat' => 'mobile',
                'metode_verifikasi' => 'gps',
                'data_asli' => ['latitude' => $uLat, 'longitude' => $uLng, 'status_lokasi' => $isWfh ? 'wfh' : 'dalam_radius'],
            ]);

            AbsensiHarian::create([
                'user_id' => $user->id,
                'tanggal' => $todayStr,
                'status' => 'hadir',
                'jenis_kehadiran' => $isWfh ? 'wfh' : 'wfo',
                'log_masuk_id' => $logMasuk->id,
                'jam_masuk' => '07:24:45',
                'latitude_masuk' => $uLat,
                'longitude_masuk' => $uLng,
                'log_pulang_id' => null,
                'jam_pulang' => null,
                'is_absen_mobile' => true,
            ]);
        }

        // 3) Pegawai Tidak Ambil Absen Pulang (Dianggap Pulang Cepat — untuk demo Tombol Konfirmasi Admin)
        foreach ($tidakPulangUsers as $user) {
            $logMasuk = LogAbsensi::create([
                'user_id' => $user->id,
                'jenis' => 'masuk',
                'waktu_absensi' => "{$todayStr} 07:18:20",
                'nomor_absensi' => (string) ($user->nomor_absensi ?? $user->id),
                'perangkat' => 'mobile',
                'metode_verifikasi' => 'gps',
                'data_asli' => ['latitude' => $officeLat, 'longitude' => $officeLng, 'status_lokasi' => 'dalam_radius'],
            ]);

            AbsensiHarian::create([
                'user_id' => $user->id,
                'tanggal' => $todayStr,
                'status' => 'hadir',
                'jenis_kehadiran' => $user->status_kerja === 'wfh' ? 'wfh' : 'wfo',
                'log_masuk_id' => $logMasuk->id,
                'jam_masuk' => '07:18:20',
                'latitude_masuk' => $officeLat,
                'longitude_masuk' => $officeLng,
                'log_pulang_id' => null,
                'jam_pulang' => null,
                'is_absen_mobile' => true,
                'keterangan' => 'Belum Absen Pulang (Dianggap Pulang Cepat)',
            ]);
        }

        // 4) Terlambat Masuk (08:15:00 WIB)
        foreach ($terlambatUsers as $user) {
            $logMasuk = LogAbsensi::create([
                'user_id' => $user->id,
                'jenis' => 'masuk',
                'waktu_absensi' => "{$todayStr} 08:15:00",
                'nomor_absensi' => (string) ($user->nomor_absensi ?? $user->id),
                'perangkat' => 'mobile',
                'metode_verifikasi' => 'gps',
                'data_asli' => ['latitude' => $officeLat, 'longitude' => $officeLng, 'status_lokasi' => 'dalam_radius'],
            ]);

            AbsensiHarian::create([
                'user_id' => $user->id,
                'tanggal' => $todayStr,
                'status' => 'terlambat',
                'jenis_kehadiran' => $user->status_kerja === 'wfh' ? 'wfh' : 'wfo',
                'log_masuk_id' => $logMasuk->id,
                'jam_masuk' => '08:15:00',
                'latitude_masuk' => $officeLat,
                'longitude_masuk' => $officeLng,
                'log_pulang_id' => null,
                'jam_pulang' => null,
                'is_absen_mobile' => true,
                'keterangan' => 'Terlambat Masuk',
            ]);
        }

        // 5) Dinas Luar Hari Ini
        foreach ($dinasLuarUsers as $user) {
            AbsensiHarian::create([
                'user_id' => $user->id,
                'tanggal' => $todayStr,
                'status' => 'dinas_luar',
                'jenis_kehadiran' => 'dinas_luar',
                'jam_masuk' => '07:30:00',
                'jam_pulang' => '16:00:00',
                'keterangan' => 'Dinas Luar Resmi Disetujui',
            ]);
        }

        // 6) Izin, Sakit, Cuti, Alpa
        foreach ($izinUsers as $user) {
            AbsensiHarian::create([
                'user_id' => $user->id,
                'tanggal' => $todayStr,
                'status' => 'izin',
                'jenis_kehadiran' => 'wfo',
                'keterangan' => 'Izin Resmi Disetujui',
            ]);
        }

        foreach ($sakitUsers as $user) {
            AbsensiHarian::create([
                'user_id' => $user->id,
                'tanggal' => $todayStr,
                'status' => 'sakit',
                'jenis_kehadiran' => 'wfo',
                'keterangan' => 'Sakit dengan Surat Dokter',
            ]);
        }

        foreach ($cutiUsers as $user) {
            AbsensiHarian::create([
                'user_id' => $user->id,
                'tanggal' => $todayStr,
                'status' => 'cuti',
                'jenis_kehadiran' => 'wfo',
                'keterangan' => 'Cuti Tahunan Disetujui',
            ]);
        }

        foreach ($alpaUsers as $user) {
            AbsensiHarian::create([
                'user_id' => $user->id,
                'tanggal' => $todayStr,
                'status' => 'tidak_hadir',
                'jenis_kehadiran' => 'wfo',
                'keterangan' => 'Tidak Hadir / Alpa',
            ]);
        }

        $this->command->info("Seeder Presensi 1 Bulan Penuh & Minggu Aktif Berhasil Diselesaikan!");
    }
}
