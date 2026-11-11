<?php

namespace App\Livewire\Mobile;

use App\Models\AbsensiHarian;
use App\Models\Lhk;
use App\Models\User;
use App\Services\AttendancePunchService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Dashboard - Absensi LHK Mobile')]
class Dashboard extends Component
{
    public $tabKepegawaian = 'utama'; // 'utama', 'golongan', 'jabatan', 'pendidikan', 'keluarga'

    public function setTabKepegawaian($tab)
    {
        $this->tabKepegawaian = $tab;
    }

    /**
     * Ambil presensi masuk atau pulang langsung dari kartu dashboard
     */
    #[On('trigger-punch')]
    public function ambilAbsen(string $jenis = 'auto', $lat = null, $lng = null)
    {
        /** @var User $user */
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $result = AttendancePunchService::punch(
            $user,
            $jenis,
            $lat !== null ? (float) $lat : null,
            $lng !== null ? (float) $lng : null
        );

        $this->dispatch('notify', [
            'type' => $result['type'] ?? ($result['success'] ? 'success' : 'error'),
            'title' => $result['title'] ?? 'Presensi Kehadiran',
            'message' => $result['message'] ?? '',
        ]);
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('mobile.login');
    }

    public function render()
    {
        /** @var User $user */
        $user = User::with(['jabatan', 'bidang', 'atasan', 'jadwalKerja'])->find(Auth::id()) ?? Auth::user();
        $today = Carbon::today();
        
        // Data hari ini
        $absensiHariIni = AbsensiHarian::where('user_id', $user->id)
            ->where('tanggal', $today->format('Y-m-d'))
            ->first();

        $sudahMasuk = $absensiHariIni && !empty($absensiHariIni->jam_masuk);
        $sudahPulang = $absensiHariIni && !empty($absensiHariIni->jam_pulang);

        // Parameter Jadwal Kerja & Batas Waktu
        $jadwal = $user->getJadwalKerja() ?? \App\Models\JadwalKerja::getDefault();
        $bukaMasuk = $jadwal ? substr($jadwal->buka_absen_masuk, 0, 5) : '06:00';
        $tutupMasuk = $jadwal ? substr($jadwal->tutup_absen_masuk, 0, 5) : '09:00';
        $jamPulangResmi = $jadwal ? $jadwal->getJamPulangHari($today->format('Y-m-d')) : '16:00';
        $tutupPulang = $jadwal ? substr($jadwal->tutup_absen_pulang, 0, 5) : '23:59';
        $nowTime = Carbon::now()->format('H:i');

        $isAdmin = $user->hasAnyRole(['super_admin', 'admin']);
        $belumBukaMasuk = (!$isAdmin && $nowTime < $bukaMasuk);
        $lewatBatasMasuk = (!$isAdmin && !$sudahMasuk && $nowTime > $tutupMasuk);
        $belumJamPulang = (!$isAdmin && $nowTime < $jamPulangResmi);
        $lewatBatasPulang = (!$isAdmin && $sudahMasuk && !$sudahPulang && $nowTime > $tutupPulang);

        // Riwayat 7 Hari Terakhir
        $riwayat7Hari = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $absen = AbsensiHarian::where('user_id', $user->id)
                ->where('tanggal', $date->format('Y-m-d'))
                ->first();
            
            $riwayat7Hari[] = [
                'hari' => $date->format('D'),
                'status' => $absen ? $absen->status : 'tidak_hadir',
                'jam_masuk' => $absen ? $absen->jam_masuk : null,
            ];
        }

        return view('livewire.mobile.dashboard', [
            'user' => $user,
            'hariIni' => [
                'sudah_masuk' => $sudahMasuk,
                'sudah_pulang' => $sudahPulang,
                'jam_masuk' => $absensiHariIni->jam_masuk ?? null,
                'jam_pulang' => $absensiHariIni->jam_pulang ?? null,
                'status' => $absensiHariIni->status ?? null,
                'buka_masuk' => $bukaMasuk,
                'tutup_masuk' => $tutupMasuk,
                'jam_pulang_resmi' => $jamPulangResmi,
                'tutup_pulang' => $tutupPulang,
                'belum_buka_masuk' => $belumBukaMasuk,
                'lewat_batas_masuk' => $lewatBatasMasuk,
                'belum_jam_pulang' => $belumJamPulang,
                'lewat_batas_pulang' => $lewatBatasPulang,
                'is_admin' => $isAdmin,
            ],
            'riwayat7Hari' => $riwayat7Hari,
        ]);
    }
}
