<?php

namespace Database\Seeders;

use App\Models\AbsensiHarian;
use App\Models\Aktivitas;
use App\Models\JadwalKerja;
use App\Models\Lhk;
use App\Models\LogAbsensi;
use App\Models\RencanaAksi;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;

class LogAbsensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Menghasilkan data absensi realistis, variatif & dinamis:
     * - Periode: 30 Hari ke belakang hingga Hari Ini (Carbon::today()).
     * - Fleksibel: Kapan pun seeder dijalankan ulang, tanggal otomatis menyesuaikan hari ini!
     * - Status Variatif Lengkap:
     *   1. Hadir Tepat Waktu (Lengkap Masuk & Pulang) + LHK terisi
     *   2. Terlambat (Masuk lewat jam toleransi)
     *   3. Pulang Cepat (Pulang sebelum jam resmi)
     *   4. Belum Pulang (Hanya masuk, jam pulang kosong - siap diuji dengan bulk action)
     *   5. Dinas Luar (Hadir otomatis, jam kerja penuh, surat tugas terlampir, LHK terbuka)
     *   6. Cuti (Cuti Tahunan / Alasan Penting)
     *   7. Sakit (Surat Keterangan Dokter)
     *   8. Tidak Masuk / Alpa
     */
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            return;
        }

        $defaultJadwal = JadwalKerja::getDefault();
        $aktivitasList = Aktivitas::all();
        $rencanaAksiList = RencanaAksi::all();

        $hariIni = Carbon::today();
        // Rentang waktu: 30 hari ke belakang s.d Hari Ini
        $tanggalMulai = $hariIni->copy()->subDays(30);
        $tanggalSelesai = $hariIni->copy();

        $period = CarbonPeriod::create($tanggalMulai, $tanggalSelesai);

        $this->command->info("Menghasilkan data absensi variatif mulai {$tanggalMulai->format('d/m/Y')} s.d {$tanggalSelesai->format('d/m/Y')}...");

        foreach ($period as $currentDate) {
            // Lewati Sabtu & Minggu
            if ($currentDate->isWeekend()) {
                continue;
            }

            $tanggalStr = $currentDate->format('Y-m-d');
            $isJumat = ($currentDate->dayOfWeekIso === 5);
            $isHariIni = $currentDate->isToday();

            // Jam kerja resmi berdasarkan hari
            $jamMasukResmiStr = '07:30';
            $jamPulangResmiStr = $isJumat ? '16:30' : '16:00';

            foreach ($users as $user) {
                // Skema hash penentu variasi status agar tiap pegawai punya riwayat unik dan konsisten
                $hash = crc32($user->id . '_' . $tanggalStr);
                $variasi = abs($hash) % 100;

                // Hari Ini: Sebagian pegawai disimulasikan "Belum Pulang" agar Admin bisa mendemokan 1-Klik Bulk Action
                if ($isHariIni && $variasi < 35) {
                    $jamMasuk = $currentDate->copy()->setTime(7, 15 + ($variasi % 25), rand(0, 59));

                    $logMasuk = LogAbsensi::updateOrCreate(
                        ['user_id' => $user->id, 'jenis' => 'masuk', 'waktu_absensi' => $jamMasuk],
                        [
                            'mesin_absensi_id' => 1,
                            'nomor_absensi' => $user->id,
                            'perangkat' => 'mesin',
                            'metode_verifikasi' => 'fingerprint',
                        ]
                    );

                    AbsensiHarian::updateOrCreate(
                        ['user_id' => $user->id, 'tanggal' => $tanggalStr],
                        [
                            'jadwal_kerja_id' => $defaultJadwal?->id,
                            'jam_masuk' => $jamMasuk->format('H:i:s'),
                            'jam_pulang' => null, // Belum tap pulang
                            'log_masuk_id' => $logMasuk->id,
                            'log_pulang_id' => null,
                            'status' => 'hadir',
                            'jenis_kehadiran' => $user->status_kerja ?? 'wfo',
                            'keterangan' => 'Belum Absen Pulang (Siap Di-Set Massal Admin)',
                            'status_apel' => 'ikut apel',
                            'is_absen_mesin' => true,
                            'is_absen_mobile' => false,
                        ]
                    );
                    continue;
                }

                // Distribusi status hari-hari kerja:
                if ($variasi < 52) {
                    // ── 1. Hadir Tepat Waktu (Lengkap) ───────────────────────
                    $jamMasuk = $currentDate->copy()->setTime(7, 10 + ($variasi % 25), rand(0, 59));
                    $jamPulang = $isJumat
                        ? $currentDate->copy()->setTime(16, 30 + ($variasi % 25), rand(0, 59))
                        : $currentDate->copy()->setTime(16, 5 + ($variasi % 35), rand(0, 59));

                    $logMasuk = LogAbsensi::updateOrCreate(
                        ['user_id' => $user->id, 'jenis' => 'masuk', 'waktu_absensi' => $jamMasuk],
                        [
                            'mesin_absensi_id' => 1,
                            'nomor_absensi' => $user->id,
                            'perangkat' => 'mesin',
                            'metode_verifikasi' => 'fingerprint',
                        ]
                    );

                    $logPulang = LogAbsensi::updateOrCreate(
                        ['user_id' => $user->id, 'jenis' => 'pulang', 'waktu_absensi' => $jamPulang],
                        [
                            'nomor_absensi' => $user->id,
                            'perangkat' => 'mobile',
                            'metode_verifikasi' => 'gps',
                        ]
                    );

                    AbsensiHarian::updateOrCreate(
                        ['user_id' => $user->id, 'tanggal' => $tanggalStr],
                        [
                            'jadwal_kerja_id' => $defaultJadwal?->id,
                            'jam_masuk' => $jamMasuk->format('H:i:s'),
                            'jam_pulang' => $jamPulang->format('H:i:s'),
                            'log_masuk_id' => $logMasuk->id,
                            'log_pulang_id' => $logPulang->id,
                            'status' => 'hadir',
                            'jenis_kehadiran' => $user->status_kerja ?? 'wfo',
                            'keterangan' => 'Hadir Tepat Waktu',
                            'status_apel' => 'ikut apel',
                            'is_absen_mesin' => true,
                            'is_absen_mobile' => true,
                        ]
                    );

                    // Buatkan LHK jika bukan hari ini (sebagian besar sudah isi)
                    if (!$isHariIni && $variasi < 45) {
                        $this->createDummyLhk($user, $tanggalStr, $jamMasuk->format('H:i:s'), $jamPulang->format('H:i:s'), $logMasuk->id, $logPulang->id, $aktivitasList, $rencanaAksiList, 'Hadir');
                    }

                } elseif ($variasi < 65) {
                    // ── 2. Terlambat Masuk ──────────────────────────────────
                    $menitTerlambat = 16 + ($variasi % 35);
                    $jamMasuk = $currentDate->copy()->setTime(7, 30)->addMinutes($menitTerlambat);
                    $jamPulang = $isJumat
                        ? $currentDate->copy()->setTime(16, 35, rand(0, 59))
                        : $currentDate->copy()->setTime(16, 15, rand(0, 59));

                    $logMasuk = LogAbsensi::updateOrCreate(
                        ['user_id' => $user->id, 'jenis' => 'masuk', 'waktu_absensi' => $jamMasuk],
                        [
                            'mesin_absensi_id' => 1,
                            'nomor_absensi' => $user->id,
                            'perangkat' => 'mesin',
                            'metode_verifikasi' => 'fingerprint',
                        ]
                    );

                    $logPulang = LogAbsensi::updateOrCreate(
                        ['user_id' => $user->id, 'jenis' => 'pulang', 'waktu_absensi' => $jamPulang],
                        [
                            'nomor_absensi' => $user->id,
                            'perangkat' => 'mobile',
                            'metode_verifikasi' => 'gps',
                        ]
                    );

                    AbsensiHarian::updateOrCreate(
                        ['user_id' => $user->id, 'tanggal' => $tanggalStr],
                        [
                            'jadwal_kerja_id' => $defaultJadwal?->id,
                            'jam_masuk' => $jamMasuk->format('H:i:s'),
                            'jam_pulang' => $jamPulang->format('H:i:s'),
                            'log_masuk_id' => $logMasuk->id,
                            'log_pulang_id' => $logPulang->id,
                            'status' => 'terlambat',
                            'jenis_kehadiran' => $user->status_kerja ?? 'wfo',
                            'keterangan' => "Terlambat $menitTerlambat menit",
                            'status_apel' => 'tidak ikut apel',
                            'is_absen_mesin' => true,
                            'is_absen_mobile' => true,
                        ]
                    );

                    if (!$isHariIni && $variasi < 58) {
                        $this->createDummyLhk($user, $tanggalStr, $jamMasuk->format('H:i:s'), $jamPulang->format('H:i:s'), $logMasuk->id, $logPulang->id, $aktivitasList, $rencanaAksiList, 'Hadir');
                    }

                } elseif ($variasi < 75) {
                    // ── 3. Pulang Cepat (Early Leave) ───────────────────────
                    $jamMasuk = $currentDate->copy()->setTime(7, 20, rand(0, 59));
                    // Pulang 30-50 menit lebih awal dari jam resmi
                    $jamPulang = $isJumat
                        ? $currentDate->copy()->setTime(15, 45, rand(0, 59))
                        : $currentDate->copy()->setTime(15, 15, rand(0, 59));

                    $logMasuk = LogAbsensi::updateOrCreate(
                        ['user_id' => $user->id, 'jenis' => 'masuk', 'waktu_absensi' => $jamMasuk],
                        [
                            'mesin_absensi_id' => 1,
                            'nomor_absensi' => $user->id,
                            'perangkat' => 'mesin',
                            'metode_verifikasi' => 'fingerprint',
                        ]
                    );

                    $logPulang = LogAbsensi::updateOrCreate(
                        ['user_id' => $user->id, 'jenis' => 'pulang', 'waktu_absensi' => $jamPulang],
                        [
                            'nomor_absensi' => $user->id,
                            'perangkat' => 'mobile',
                            'metode_verifikasi' => 'gps',
                        ]
                    );

                    AbsensiHarian::updateOrCreate(
                        ['user_id' => $user->id, 'tanggal' => $tanggalStr],
                        [
                            'jadwal_kerja_id' => $defaultJadwal?->id,
                            'jam_masuk' => $jamMasuk->format('H:i:s'),
                            'jam_pulang' => $jamPulang->format('H:i:s'),
                            'log_masuk_id' => $logMasuk->id,
                            'log_pulang_id' => $logPulang->id,
                            'status' => 'hadir',
                            'jenis_kehadiran' => $user->status_kerja ?? 'wfo',
                            'keterangan' => 'Pulang Sebelum Waktunya (Izin Pimpinan)',
                            'status_apel' => 'ikut apel',
                            'is_absen_mesin' => true,
                            'is_absen_mobile' => true,
                        ]
                    );

                } elseif ($variasi < 85) {
                    // ── 4. Dinas Luar (Hadir Penuh, LHK Langsung Terbuka) ────
                    AbsensiHarian::updateOrCreate(
                        ['user_id' => $user->id, 'tanggal' => $tanggalStr],
                        [
                            'jadwal_kerja_id' => $defaultJadwal?->id,
                            'jam_masuk' => $jamMasukResmiStr . ':00',
                            'jam_pulang' => $jamPulangResmiStr . ':00',
                            'status' => 'hadir',
                            'jenis_kehadiran' => 'dinas_luar',
                            'keterangan' => 'Dinas Luar (Surat Tugas No. 090/ST/' . $currentDate->format('m/Y') . ')',
                            'status_apel' => 'ikut apel',
                            'is_absen_mesin' => false,
                            'is_absen_mobile' => false,
                        ]
                    );

                    if (!$isHariIni) {
                        $this->createDummyLhk($user, $tanggalStr, $jamMasukResmiStr . ':00', $jamPulangResmiStr . ':00', null, null, $aktivitasList, $rencanaAksiList, 'Dinas Luar');
                    }

                } elseif ($variasi < 90) {
                    // ── 5. Cuti Tahunan / Alasan Penting ────────────────────
                    AbsensiHarian::updateOrCreate(
                        ['user_id' => $user->id, 'tanggal' => $tanggalStr],
                        [
                            'jadwal_kerja_id' => $defaultJadwal?->id,
                            'jam_masuk' => null,
                            'jam_pulang' => null,
                            'status' => 'cuti',
                            'jenis_kehadiran' => 'wfo',
                            'keterangan' => 'Cuti Tahunan (Disetujui BKPSDM)',
                            'status_apel' => 'tidak ikut apel',
                        ]
                    );

                } elseif ($variasi < 95) {
                    // ── 6. Sakit (Surat Dokter) ─────────────────────────────
                    AbsensiHarian::updateOrCreate(
                        ['user_id' => $user->id, 'tanggal' => $tanggalStr],
                        [
                            'jadwal_kerja_id' => $defaultJadwal?->id,
                            'jam_masuk' => null,
                            'jam_pulang' => null,
                            'status' => 'sakit',
                            'jenis_kehadiran' => 'wfo',
                            'keterangan' => 'Sakit (Dilampirkan Surat Keterangan Dokter)',
                            'status_apel' => 'tidak ikut apel',
                        ]
                    );

                } else {
                    // ── 7. Tidak Hadir / Alpa ───────────────────────────────
                    AbsensiHarian::updateOrCreate(
                        ['user_id' => $user->id, 'tanggal' => $tanggalStr],
                        [
                            'jadwal_kerja_id' => $defaultJadwal?->id,
                            'jam_masuk' => null,
                            'jam_pulang' => null,
                            'status' => 'tidak_hadir',
                            'jenis_kehadiran' => 'wfo',
                            'keterangan' => 'Alpa / Tanpa Keterangan',
                            'status_apel' => 'tidak ikut apel',
                        ]
                    );
                }
            }
        }

        $this->command->info('Data seeder absensi harian variatif (sakit, cuti, dinas luar, terlambat, pulang cepat, lengkap, alpa) berhasil digenerate!');
    }

    /**
     * Helper untuk membuat data LHK otomatis
     *
     * @param \App\Models\User $user
     * @param string $tanggalStr
     * @param string $jamMasuk
     * @param string $jamPulang
     * @param int|null $logMasukId
     * @param int|null $logPulangId
     * @param \Illuminate\Database\Eloquent\Collection<int, \App\Models\Aktivitas> $aktivitasList
     * @param \Illuminate\Database\Eloquent\Collection<int, \App\Models\RencanaAksi> $rencanaAksiList
     * @param string $statusKehadiran
     */
    private function createDummyLhk(
        User $user,
        string $tanggalStr,
        string $jamMasuk,
        string $jamPulang,
        ?int $logMasukId,
        ?int $logPulangId,
        $aktivitasList,
        $rencanaAksiList,
        string $statusKehadiran = 'Hadir'
    ): void
    {
        $lhk = Lhk::updateOrCreate(
            ['user_id' => $user->id, 'tanggal' => $tanggalStr],
            [
                'jam_masuk' => $jamMasuk,
                'jam_pulang' => $jamPulang,
                'log_absensi_masuk_id' => $logMasukId,
                'log_absensi_pulang_id' => $logPulangId,
                'status_kehadiran' => $statusKehadiran,
            ]
        );

        if ($lhk->pekerjaans()->count() === 0 && $aktivitasList->isNotEmpty()) {
            $aktivitas1 = $aktivitasList->random();
            $aktivitas2 = $aktivitasList->random();
            $rencanaAksiId = $rencanaAksiList->first()?->id;

            $lhk->pekerjaans()->create([
                'rencana_aksi_id' => $rencanaAksiId,
                'aktivitas_id' => $aktivitas1->id,
                'lama_menit' => $aktivitas1->menit ?? 60,
                'jam_mulai' => '08:30',
                'jam_selesai' => '09:30',
                'hasil_pekerjaan' => 'Terselesaikannya kegiatan ' . $aktivitas1->nama_aktivitas . ' dengan baik dan tepat waktu.',
                'bukti_url' => 'https://drive.google.com/file/d/dummy-lhk-' . $user->id,
            ]);

            $lhk->pekerjaans()->create([
                'rencana_aksi_id' => $rencanaAksiId,
                'aktivitas_id' => $aktivitas2->id,
                'lama_menit' => $aktivitas2->menit ?? 90,
                'jam_mulai' => '10:00',
                'jam_selesai' => '11:30',
                'hasil_pekerjaan' => 'Laporan progress pelaksanaan kegiatan ' . $aktivitas2->nama_aktivitas . ' telah dikoordinasikan kepada atasan.',
                'bukti_url' => 'https://drive.google.com/file/d/dummy-lhk-dokumen-' . $user->id,
            ]);
        }
    }
}
