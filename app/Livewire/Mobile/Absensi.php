<?php

namespace App\Livewire\Mobile;

use App\Models\AbsensiHarian;
use App\Models\LogAbsensi;
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
    
    // Konstanta lokasi kantor (bisa ambil dari DB juga)
    private $officeLat = -6.200000;
    private $officeLng = 106.816666;
    private $maxRadius = 100; // meter

    public function mount()
    {
        // init
    }

    public function punch($lat, $lng)
    {
        $this->latitude = $lat;
        $this->longitude = $lng;

        $user = Auth::user();
        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now()->format('H:i:s');
        
        $distance = $this->calculateDistance($this->officeLat, $this->officeLng, $lat, $lng);
        
        if ($distance > $this->maxRadius) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Anda berada di luar radius kantor (' . round($distance) . 'm).']);
            return;
        }

        $absenHarian = AbsensiHarian::where('user_id', $user->id)
            ->where('tanggal', $today)
            ->first();

        // 1. Jika belum pernah absen masuk sama sekali
        if (!$absenHarian || empty($absenHarian->jam_masuk)) {
            // Ambil jadwal kerja untuk menentukan apakah sudah masuk jam pulang
            $jadwal = $user->getJadwalKerja();
            $jamMasukResmi = $jadwal ? $jadwal->getJamMasukHari($today) : '07:30';
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
                'status_lokasi' => 'dalam_radius'
            ]);
            $absenHarian->log_masuk_id = $log->id;
            $absenHarian->jam_masuk = $now;
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
                'status_lokasi' => 'dalam_radius'
            ]);
            $absenHarian->log_pulang_id = $log->id;
            $absenHarian->jam_pulang = $now;
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

    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }

    public function render()
    {
        $today = Carbon::today()->format('Y-m-d');
        $absenHarian = AbsensiHarian::where('user_id', Auth::id())->where('tanggal', $today)->first();

        $sudahMasuk = $absenHarian && $absenHarian->log_masuk_id;
        $sudahPulang = $absenHarian && $absenHarian->log_pulang_id;
        $selesai = $sudahMasuk && $sudahPulang;

        return view('livewire.mobile.absensi', [
            'absenHarian' => $absenHarian,
            'sudahMasuk' => $sudahMasuk,
            'sudahPulang' => $sudahPulang,
            'selesai' => $selesai,
        ]);
    }
}
