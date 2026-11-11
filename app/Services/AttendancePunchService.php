<?php

namespace App\Services;

use App\Models\AbsensiHarian;
use App\Models\JadwalKerja;
use App\Models\LogAbsensi;
use App\Models\PengaturanAplikasi;
use App\Models\User;
use Carbon\Carbon;

class AttendancePunchService
{
    /**
     * Hitung jarak Haversine antara dua titik koordinat (meter)
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * Eksekusi presensi masuk atau pulang
     *
     * @param User $user
     * @param string $action 'masuk'|'pulang'|'auto'
     * @param float|null $lat
     * @param float|null $lng
     * @param bool $force Bypass jam / radius (untuk admin / simulasi)
     * @return array
     */
    public static function punch(User $user, string $action = 'auto', ?float $lat = null, ?float $lng = null, bool $force = false): array
    {
        $pengaturan = PengaturanAplikasi::getPengaturan();
        $officeLat = (float) ($pengaturan->latitude_kantor ?? -0.69752300);
        $officeLng = (float) ($pengaturan->longitude_kantor ?? 100.98547200);
        $maxRadius = (int) ($pengaturan->radius_meter ?? 150);

        $isAdmin = $user->hasAnyRole(['super_admin', 'admin']);
        $isSimulasi = (bool) ($pengaturan->mode_simulasi || session('mode_simulasi_absensi', false));
        $bypass = $force || $isSimulasi;

        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now()->format('H:i:s');
        $nowTime = substr($now, 0, 5);

        // Jika koordinat belum terdeteksi di browser/device, fallback ke koordinat kantor (terutama di localhost / admin bypass)
        if ($lat === null || $lng === null) {
            $lat = $officeLat;
            $lng = $officeLng;
        }

        // 1. Validasi Radius Geofencing (Kecuali WFH atau mode bypass)
        $isWfh = ($user->status_kerja === 'wfh');
        $distance = self::calculateDistance($officeLat, $officeLng, $lat, $lng);

        if (!$isWfh && !$bypass && $distance > $maxRadius) {
            $distMeters = round($distance);
            return [
                'success' => false,
                'type' => 'error',
                'title' => 'Di Luar Radius Kantor!',
                'message' => "Jarak Anda {$distMeters}m dari kantor (Radius toleransi resmi: {$maxRadius}m). Presensi WFO wajib dilakukan di area kantor.",
            ];
        }

        $statusLokasi = ($isWfh || $bypass) ? 'wfh' : 'dalam_radius';

        // 2. Ambil Jadwal Kerja
        $jadwal = $user->getJadwalKerja() ?? JadwalKerja::getDefault();
        $bukaMasuk = $jadwal ? substr($jadwal->buka_absen_masuk, 0, 5) : '06:00';
        $tutupMasuk = $jadwal ? substr($jadwal->tutup_absen_masuk, 0, 5) : '09:00';
        $jamMasukResmi = $jadwal ? $jadwal->getJamMasukHari($today) : '07:30';
        $jamPulangResmi = $jadwal ? $jadwal->getJamPulangHari($today) : '16:00';
        $tutupPulang = $jadwal ? substr($jadwal->tutup_absen_pulang, 0, 5) : '23:59';
        $toleransi = $jadwal ? (int) $jadwal->toleransi_terlambat_menit : 15;

        $absenHarian = AbsensiHarian::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        $sudahMasuk = $absenHarian && !empty($absenHarian->jam_masuk);
        $sudahPulang = $absenHarian && !empty($absenHarian->jam_pulang);

        // Tentukan target action jika auto
        if ($action === 'auto') {
            $action = !$sudahMasuk ? 'masuk' : 'pulang';
        }

        $nomorAbsensi = (string) ($user->nomor_absensi ?? $user->id);

        // ── AKSI ABSEN MASUK ──────────────────────────────────────────────────
        if ($action === 'masuk') {
            if ($sudahMasuk) {
                return [
                    'success' => true,
                    'already' => true,
                    'type' => 'info',
                    'title' => 'Sudah Absen Masuk',
                    'message' => "Anda sudah melakukan absen masuk hari ini pada pukul {$absenHarian->jam_masuk} WIB.",
                    'jam' => $absenHarian->jam_masuk,
                ];
            }

            if (!$bypass) {
                // Belum jam buka masuk
                if ($nowTime < $bukaMasuk) {
                    return [
                        'success' => false,
                        'type' => 'warning',
                        'title' => 'Absen Masuk Belum Dibuka',
                        'message' => "Presensi masuk pagi dibuka mulai pukul {$bukaMasuk} WIB s.d {$tutupMasuk} WIB.",
                    ];
                }

                // Lewat jam tutup masuk
                if ($nowTime > $tutupMasuk) {
                    if ($absenHarian) {
                        $absenHarian->update(['status' => 'tidak_hadir']);
                    } else {
                        AbsensiHarian::create([
                            'user_id' => $user->id,
                            'tanggal' => $today,
                            'status' => 'tidak_hadir',
                            'keterangan' => 'Alpa / Terlewat Batas Absen Masuk (' . $tutupMasuk . ' WIB)',
                        ]);
                    }

                    return [
                        'success' => false,
                        'type' => 'error',
                        'title' => 'Batas Absen Masuk Berakhir!',
                        'message' => "Batas akhir absen masuk adalah pukul {$tutupMasuk} WIB. Anda tercatat Tidak Hadir / Alpa hari ini.",
                    ];
                }
            }

            // Hitung toleransi keterlambatan
            $batasTepatWaktu = Carbon::parse("$today $jamMasukResmi:00")->addMinutes($toleransi)->format('H:i:s');
            $status = ($bypass || $now <= $batasTepatWaktu) ? 'hadir' : 'terlambat';

            $log = LogAbsensi::create([
                'user_id' => $user->id,
                'nomor_absensi' => $nomorAbsensi,
                'waktu_absensi' => Carbon::now(),
                'perangkat' => 'mobile',
                'metode_verifikasi' => 'gps',
                'jenis' => 'masuk',
                'data_asli' => [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'status_lokasi' => $statusLokasi,
                    'is_simulasi' => $bypass,
                ],
            ]);

            $absenHarian = AbsensiHarian::firstOrCreate(
                ['user_id' => $user->id, 'tanggal' => $today],
                ['status' => $status]
            );

            $absenHarian->update([
                'status' => $status,
                'jenis_kehadiran' => $isWfh ? 'wfh' : 'wfo',
                'log_masuk_id' => $log->id,
                'jam_masuk' => $now,
                'latitude_masuk' => $lat,
                'longitude_masuk' => $lng,
                'is_absen_mobile' => true,
                'jadwal_kerja_id' => $jadwal?->id,
            ]);

            $labelStatus = ($status === 'hadir') ? 'Tepat Waktu' : 'Terlambat';
            $extraMsg = $bypass ? ' (Mode Simulasi)' : '';

            return [
                'success' => true,
                'type' => 'success',
                'title' => 'Absen Masuk Berhasil!',
                'message' => "Tercatat pada pukul {$now} WIB ({$labelStatus}){$extraMsg}. Selamat bertugas!",
                'jam' => $now,
                'status' => $status,
            ];
        }

        // ── AKSI ABSEN KELUAR / PULANG ─────────────────────────────────────────
        if ($action === 'pulang') {
            if (!$sudahMasuk) {
                return [
                    'success' => false,
                    'type' => 'warning',
                    'title' => 'Belum Absen Masuk!',
                    'message' => 'Anda belum mengambil absen masuk hari ini. Silakan tekan tombol Absen Masuk terlebih dahulu.',
                ];
            }

            if ($sudahPulang) {
                return [
                    'success' => true,
                    'already' => true,
                    'type' => 'info',
                    'title' => 'Sudah Absen Keluar',
                    'message' => "Anda sudah menyelesaikan absen keluar hari ini pada pukul {$absenHarian->jam_pulang} WIB.",
                    'jam' => $absenHarian->jam_pulang,
                ];
            }

            // Validasi jam buka & tutup pulang (baru aktif jika sudah masuk jam pulang)
            if (!$bypass && $nowTime < $jamPulangResmi) {
                return [
                    'success' => false,
                    'type' => 'warning',
                    'title' => 'Belum Jam Pulang!',
                    'message' => "Tombol absen pulang baru aktif ketika jam pulang sudah masuk. Jam pulang resmi hari ini adalah pukul {$jamPulangResmi} WIB.",
                ];
            }

            if (!$bypass && $nowTime > $tutupPulang) {
                return [
                    'success' => false,
                    'type' => 'error',
                    'title' => 'Waktu Absen Pulang Lewat!',
                    'message' => "Batas akhir absen pulang adalah pukul {$tutupPulang} WIB. Waktu presensi hari ini telah berakhir.",
                ];
            }

            $isPulangCepat = ($nowTime < $jamPulangResmi);

            $log = LogAbsensi::create([
                'user_id' => $user->id,
                'nomor_absensi' => $nomorAbsensi,
                'waktu_absensi' => Carbon::now(),
                'perangkat' => 'mobile',
                'metode_verifikasi' => 'gps',
                'jenis' => 'pulang',
                'data_asli' => [
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'status_lokasi' => $statusLokasi,
                    'is_simulasi' => $bypass,
                    'is_pulang_cepat' => $isPulangCepat,
                ],
            ]);

            $updateData = [
                'log_pulang_id' => $log->id,
                'jam_pulang' => $now,
                'latitude_pulang' => $lat,
                'longitude_pulang' => $lng,
                'is_absen_mobile' => true,
            ];

            if ($isWfh) {
                $updateData['jenis_kehadiran'] = 'wfh';
            }

            if ($extraKet && empty($absenHarian->keterangan)) {
                $updateData['keterangan'] = $extraKet;
            }

            $absenHarian->update($updateData);

            $noteMsg = $isPulangCepat ? ' (Catatan: Pulang Cepat)' : '';
            $extraMsg = $bypass ? ' (Mode Simulasi)' : '';

            return [
                'success' => true,
                'type' => 'success',
                'title' => 'Absen Keluar Berhasil!',
                'message' => "Tercatat pada pukul {$now} WIB{$noteMsg}{$extraMsg}. Terima kasih atas dedikasi dan kerja keras Anda hari ini!",
                'jam' => $now,
                'is_pulang_cepat' => $isPulangCepat,
            ];
        }

        return [
            'success' => false,
            'type' => 'error',
            'title' => 'Aksi Tidak Dikenal',
            'message' => 'Parameter aksi presensi tidak valid.',
        ];
    }
}

