<?php

namespace App\Livewire\Mobile;

use App\Models\AbsensiHarian;
use App\Models\Lhk;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Dashboard - Absensi LHK Mobile')]
class Dashboard extends Component
{
    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('mobile.login');
    }

    public function render()
    {
        $user = Auth::user();
        $today = Carbon::today();
        
        // Data hari ini
        $absensiHariIni = AbsensiHarian::where('user_id', $user->id)
            ->where('tanggal', $today->format('Y-m-d'))
            ->first();

        $sudahMasuk = $absensiHariIni && $absensiHariIni->jam_masuk;
        $sudahPulang = $absensiHariIni && $absensiHariIni->jam_pulang;

        // Statistik Bulan Ini
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        $absensiBulanIni = AbsensiHarian::where('user_id', $user->id)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->get();

        $statistik = [
            'hadir' => $absensiBulanIni->where('status', 'hadir')->count(),
            'terlambat' => $absensiBulanIni->where('status', 'terlambat')->count(),
            'izin' => $absensiBulanIni->where('status', 'izin')->count(),
            'sakit' => $absensiBulanIni->where('status', 'sakit')->count(),
            'lhk_dibuat' => Lhk::where('user_id', $user->id)
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->count(),
        ];

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
            ],
            'statistik' => $statistik,
            'riwayat7Hari' => $riwayat7Hari,
        ]);
    }
}
