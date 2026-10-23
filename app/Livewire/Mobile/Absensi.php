<?php

namespace App\Livewire\Mobile;

use App\Models\AbsensiHarian;
use App\Models\LogAbsensi;
use App\Models\PengaturanAplikasi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Absensi - Absensi LHK Mobile')]
class Absensi extends Component
{
    public $latitude;
    public $longitude;

    public function punch($lat, $lng)
    {
        $this->latitude = $lat;
        $this->longitude = $lng;

        /** @var User $user */
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now()->format('H:i:s');
        $nowTime = substr($now, 0, 5);

        // Ambil koordinat dan radius kantor resmi dari PengaturanAplikasi
        $pengaturan = PengaturanAplikasi::getPengaturan();
        $officeLat = (float) ($pengaturan->latitude_kantor ?? -0.69752300);
        $officeLng = (float) ($pengaturan->longitude_kantor ?? 100.98547200);
        $maxRadius = (int) ($pengaturan->radius_meter ?? 150);

        // Cek status kerja: WFH mendapat dispensasi radius (bebas koordinat)
        $isWfh = ($user->status_kerja === 'wfh');
        $distance = $this->calculateDistance($officeLat, $officeLng, $lat, $lng);

        if (!$isWfh && $distance > $maxRadius) {
            $distMeters = round($distance);
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Di Luar Radius Kantor!',
                'message' => "Jarak Anda {$distMeters}m dari kantor (Radius toleransi: {$maxRadius}m).",
            ]);
            return;
        }

        $statusLokasi = $isWfh ? 'wfh' : 'dalam_radius';

        // Ambil jadwal kerja yang berlaku untuk user
        $jadwal = $user->getJadwalKerja() ?? \App\Models\JadwalKerja::getDefault();
        $bukaMasuk = $jadwal ? substr($jadwal->buka_absen_masuk, 0, 5) : '06:00';
        $tutupMasuk = $jadwal ? substr($jadwal->tutup_absen_masuk, 0, 5) : '09:00';
        $jamMasukResmi = $jadwal ? $jadwal->getJamMasukHari($today) : '07:30';
        $jamPulangResmi = $jadwal ? $jadwal->getJamPulangHari($today) : '16:00';
        $tutupPulang = $jadwal ? substr($jadwal->tutup_absen_pulang, 0, 5) : '23:59';
        $toleransi = $jadwal ? (int) $jadwal->toleransi_terlambat_menit : 15;

        $absenHarian = AbsensiHarian::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        try {
            $nomorAbsensi = (string) ($user->nomor_absensi ?? $user->id);

            // 1. Kasus: Belum pernah absen masuk pagi
            if (!$absenHarian || empty($absenHarian->jam_masuk)) {
                // Aturan 1A: Absen masuk belum buka (sebelum jam 06:00)
                if ($nowTime < $bukaMasuk) {
                    $this->dispatch('notify', [
                        'type' => 'warning',
                        'title' => 'Diluar Jam Absen (Belum Dibuka)',
                        'message' => "Saat ini belum memasuki jam presensi dinas. Absen masuk pagi dibuka mulai pukul {$bukaMasuk} WIB s.d {$tutupMasuk} WIB.",
                    ]);
                    return;
                }

                // Aturan 1B: Sudah lewat batas absen masuk (lewat jam 09:00)
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

                    $this->dispatch('notify', [
                        'type' => 'error',
                        'title' => 'Batas Absen Masuk Berakhir!',
                        'message' => "Batas akhir absen masuk adalah pukul {$tutupMasuk} WIB. Anda tercatat Tidak Hadir / Alpa hari ini dan tidak dapat mengambil absen pulang.",
                    ]);
                    return;
                }

                // Hitung toleransi keterlambatan
                $batasTepatWaktu = Carbon::parse("$today $jamMasukResmi:00")->addMinutes($toleransi)->format('H:i:s');
                $status = ($now <= $batasTepatWaktu) ? 'hadir' : 'terlambat';

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
                    ],
                ]);

                $absenHarian = AbsensiHarian::firstOrCreate(
                    ['user_id' => $user->id, 'tanggal' => $today],
                    ['status' => $status]
                );

                $absenHarian->update([
                    'status' => $status,
                    'log_masuk_id' => $log->id,
                    'jam_masuk' => $now,
                    'latitude_masuk' => $lat,
                    'longitude_masuk' => $lng,
                    'is_absen_mobile' => true,
                ]);

                $labelStatus = ($status === 'hadir') ? 'Tepat Waktu' : 'Terlambat';
                $this->dispatch('notify', [
                    'type' => 'success',
                    'title' => 'Absen Masuk Berhasil!',
                    'message' => "Tercatat pada pukul {$now} WIB ({$labelStatus}). Selamat bertugas!",
                ]);

            } elseif (empty($absenHarian->jam_pulang)) {
                // 2. Kasus: Sudah absen masuk, hendak ambil absen pulang
                // Aturan 2A: Belum jam pulang resmi
                if ($nowTime < $jamPulangResmi) {
                    $this->dispatch('notify', [
                        'type' => 'warning',
                        'title' => 'Belum Waktu Pulang!',
                        'message' => "Saat ini masih dalam jam kerja dinas. Presensi pulang hari ini baru dibuka mulai pukul {$jamPulangResmi} WIB.",
                    ]);
                    return;
                }

                // Aturan 2B: Lewat batas tutup absen pulang (23:59)
                if ($nowTime > $tutupPulang) {
                    $this->dispatch('notify', [
                        'type' => 'error',
                        'title' => 'Batas Absen Pulang Berakhir!',
                        'message' => "Batas akhir pengambilan absen pulang malam hari (pukul {$tutupPulang} WIB) telah berakhir.",
                    ]);
                    return;
                }

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
                    ],
                ]);

                $absenHarian->update([
                    'log_pulang_id' => $log->id,
                    'jam_pulang' => $now,
                    'latitude_pulang' => $lat,
                    'longitude_pulang' => $lng,
                    'is_absen_mobile' => true,
                ]);

                $this->dispatch('notify', [
                    'type' => 'success',
                    'title' => 'Absen Pulang Berhasil!',
                    'message' => "Tercatat pada pukul {$now} WIB. Terima kasih atas dedikasi Anda hari ini!",
                ]);
            } else {
                $this->dispatch('notify', [
                    'type' => 'info',
                    'title' => 'Presensi Lengkap',
                    'message' => 'Anda sudah menyelesaikan absensi masuk dan pulang hari ini.',
                ]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Punch error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Gagal Mencatat Absensi',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Fitur Admin: Set titik koordinat kantor langsung ke posisi GPS saat ini untuk kemudahan pengujian
     */
    public function setLokasiKantorKeSaya($lat, $lng)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user->hasAnyRole(['super_admin', 'admin'])) {
            $this->dispatch('notify', [
                'type' => 'error',
                'title' => 'Akses Ditolak',
                'message' => 'Hanya Administrator yang berhak mengubah koordinat kantor.',
            ]);
            return;
        }

        $pengaturan = PengaturanAplikasi::getPengaturan();
        $pengaturan->update([
            'latitude_kantor' => $lat,
            'longitude_kantor' => $lng,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'title' => 'Titik Kantor Diperbarui!',
            'message' => 'Koordinat kantor berhasil diset ke titik Anda (' . round($lat, 6) . ', ' . round($lng, 6) . '). Sekarang Anda berada di dalam radius!',
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public function render()
    {
        /** @var User $user */
        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');
        $nowTime = Carbon::now()->format('H:i');

        $absenHarian = AbsensiHarian::where('user_id', $user->id)->where('tanggal', $today)->first();

        $sudahMasuk = $absenHarian && !empty($absenHarian->jam_masuk);
        $sudahPulang = $absenHarian && !empty($absenHarian->jam_pulang);
        $selesai = $sudahMasuk && $sudahPulang;

        $pengaturan = PengaturanAplikasi::getPengaturan();

        // Parameter Jam Kerja
        $jadwal = $user->getJadwalKerja() ?? \App\Models\JadwalKerja::getDefault();
        $bukaMasuk = $jadwal ? substr($jadwal->buka_absen_masuk, 0, 5) : '06:00';
        $tutupMasuk = $jadwal ? substr($jadwal->tutup_absen_masuk, 0, 5) : '09:00';
        $jamMasukResmi = $jadwal ? $jadwal->getJamMasukHari($today) : '07:30';
        $jamPulangResmi = $jadwal ? $jadwal->getJamPulangHari($today) : '16:00';
        $tutupPulang = $jadwal ? substr($jadwal->tutup_absen_pulang, 0, 5) : '23:59';

        $lewatJamMasuk = (!$sudahMasuk && $nowTime > $tutupMasuk);
        $belumBukaMasuk = (!$sudahMasuk && $nowTime < $bukaMasuk);
        $belumJamPulang = ($sudahMasuk && !$sudahPulang && $nowTime < $jamPulangResmi);
        $lewatJamPulang = ($sudahMasuk && !$sudahPulang && $nowTime > $tutupPulang);

        $siapAbsenMasuk = (!$sudahMasuk && $nowTime >= $bukaMasuk && $nowTime <= $tutupMasuk);
        $siapAbsenPulang = ($sudahMasuk && !$sudahPulang && $nowTime >= $jamPulangResmi && $nowTime <= $tutupPulang);
        $bisaAbsen = $siapAbsenMasuk || $siapAbsenPulang;

        return view('livewire.mobile.absensi', [
            'user' => $user,
            'absenHarian' => $absenHarian,
            'sudahMasuk' => $sudahMasuk,
            'sudahPulang' => $sudahPulang,
            'selesai' => $selesai,
            'officeLat' => (float) ($pengaturan->latitude_kantor ?? -0.69752300),
            'officeLng' => (float) ($pengaturan->longitude_kantor ?? 100.98547200),
            'maxRadius' => (int) ($pengaturan->radius_meter ?? 150),
            'alamatKantor' => $pengaturan->alamat_kantor ?? 'Kantor Dinas',
            'isWfh' => ($user->status_kerja === 'wfh'),
            'isAdmin' => $user->hasAnyRole(['super_admin', 'admin']),
            'bukaMasuk' => $bukaMasuk,
            'tutupMasuk' => $tutupMasuk,
            'jamMasukResmi' => $jamMasukResmi,
            'jamPulangResmi' => $jamPulangResmi,
            'tutupPulang' => $tutupPulang,
            'lewatJamMasuk' => $lewatJamMasuk,
            'belumBukaMasuk' => $belumBukaMasuk,
            'belumJamPulang' => $belumJamPulang,
            'lewatJamPulang' => $lewatJamPulang,
            'siapAbsenMasuk' => $siapAbsenMasuk,
            'siapAbsenPulang' => $siapAbsenPulang,
            'bisaAbsen' => $bisaAbsen,
        ]);
    }

}
