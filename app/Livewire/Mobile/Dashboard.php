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

        $jadwalDefault = $user->getJadwalKerja();

        $statistik = [
            'hadir' => $absensiBulanIni->filter(fn($a) => in_array($a->status?->value ?? $a->status, ['hadir', 'terlambat']))->count(),
            'terlambat' => $absensiBulanIni->filter(fn($a) => ($a->status?->value ?? $a->status) === 'terlambat')->count(),
            'dinas_luar' => $absensiBulanIni->filter(fn($a) => ($a->status?->value ?? $a->status) === 'dinas_luar' || ($a->jenis_kehadiran?->value ?? $a->jenis_kehadiran) === 'dinas_luar')->count(),
            'pulang_cepat' => $absensiBulanIni->filter(function($a) use ($jadwalDefault) {
                if (empty($a->jam_pulang)) return false;
                $jamPulangResmi = $jadwalDefault ? $jadwalDefault->getJamPulangHari($a->tanggal) : '16:00';
                return $a->jam_pulang < $jamPulangResmi;
            })->count(),
            'tanpa_keterangan' => $absensiBulanIni->filter(fn($a) => in_array($a->status?->value ?? $a->status, ['tidak_hadir', 'alpa']) || (empty($a->jam_masuk) && empty($a->jam_pulang) && !in_array($a->status?->value ?? $a->status, ['izin', 'sakit', 'cuti', 'libur', 'dinas_luar'])))->count(),
            'izin' => $absensiBulanIni->filter(fn($a) => ($a->status?->value ?? $a->status) === 'izin')->count(),
            'sakit' => $absensiBulanIni->filter(fn($a) => ($a->status?->value ?? $a->status) === 'sakit')->count(),
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
