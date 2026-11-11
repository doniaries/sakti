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
            ],
            'riwayat7Hari' => $riwayat7Hari,
        ]);
    }
}
