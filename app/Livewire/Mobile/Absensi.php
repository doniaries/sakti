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

        $absenHarian = AbsensiHarian::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        // 1. Jika belum pernah absen masuk sama sekali
        if (!$absenHarian || empty($absenHarian->jam_masuk)) {
            $jadwal = $user->getJadwalKerja();
            $jamPulangResmi = $jadwal ? $jadwal->getJamPulangHari($today) : '16:00';

            // Jika pegawai mencoba absen pada jam pulang tetapi tidak ada absen masuk pagi:
            if ($now >= $jamPulangResmi) {
                $this->dispatch('notify', [
                    'type' => 'error',
                    'title' => 'Absen Pulang Ditolak!',
                    'message' => 'Anda tidak dapat mengambil absen pulang karena tidak mengambil absen masuk pagi ini. Status Anda hari ini tercatat Tidak Hadir / Alpa.',
                ]);
                return;
            }

            // Ambil absen masuk
            $absenHarian = AbsensiHarian::firstOrCreate(
                ['user_id' => $user->id, 'tanggal' => $today],
                ['status' => 'hadir']
            );

            $log = LogAbsensi::create([
                'user_id' => $user->id,
                'tanggal' => $today,
                'jam' => $now,
                'jenis' => 'masuk',
                'latitude' => $lat,
                'longitude' => $lng,
                'status_lokasi' => $statusLokasi,
            ]);

            $absenHarian->log_masuk_id = $log->id;
            $absenHarian->jam_masuk = $now;
            $absenHarian->latitude_masuk = $lat;
            $absenHarian->longitude_masuk = $lng;
            $absenHarian->save();

            $this->dispatch('notify', [
                'type' => 'success',
                'title' => 'Berhasil!',
                'message' => 'Absen Masuk Pagi Berhasil dicatat pada pukul ' . $now . ' WIB.',
            ]);
        } elseif (empty($absenHarian->jam_pulang)) {
            // 2. Ambil absen pulang (karena sudah absen masuk)
            $log = LogAbsensi::create([
                'user_id' => $user->id,
                'tanggal' => $today,
                'jam' => $now,
                'jenis' => 'pulang',
                'latitude' => $lat,
                'longitude' => $lng,
                'status_lokasi' => $statusLokasi,
            ]);

            $absenHarian->log_pulang_id = $log->id;
            $absenHarian->jam_pulang = $now;
            $absenHarian->latitude_pulang = $lat;
            $absenHarian->longitude_pulang = $lng;
            $absenHarian->save();

            $this->dispatch('notify', [
                'type' => 'success',
                'title' => 'Berhasil!',
                'message' => 'Absen Pulang Berhasil dicatat pada pukul ' . $now . ' WIB.',
            ]);
        } else {
            $this->dispatch('notify', [
                'type' => 'info',
                'title' => 'Lengkap',
                'message' => 'Anda sudah menyelesaikan absensi masuk dan pulang hari ini.',
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
        $absenHarian = AbsensiHarian::where('user_id', $user->id)->where('tanggal', $today)->first();

        $sudahMasuk = $absenHarian && $absenHarian->log_masuk_id;
        $sudahPulang = $absenHarian && $absenHarian->log_pulang_id;
        $selesai = $sudahMasuk && $sudahPulang;

        $pengaturan = PengaturanAplikasi::getPengaturan();

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
        ]);
    }
}
