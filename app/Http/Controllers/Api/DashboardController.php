<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiHarian;
use App\Models\Lhk;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->loadMissing('jabatan');

        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        // Statistik absensi bulan ini
        $absensiQuery = AbsensiHarian::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun);

        $totalHadir   = (clone $absensiQuery)->whereIn('status', ['hadir', 'terlambat'])->count();
        $totalTerlambat = (clone $absensiQuery)->where('status', 'terlambat')->count();
        $totalIzin    = (clone $absensiQuery)->where('status', 'izin')->count();
        $totalSakit   = (clone $absensiQuery)->where('status', 'sakit')->count();
        $totalLhk     = Lhk::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->count();

        // Absensi hari ini
        $absensiHariIni = AbsensiHarian::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->with(['logMasuk', 'logPulang'])
            ->first();

        // Riwayat 7 hari terakhir
        $riwayat7Hari = AbsensiHarian::where('user_id', $user->id)
            ->whereBetween('tanggal', [now()->subDays(6)->format('Y-m-d'), today()->format('Y-m-d')])
            ->with(['logMasuk', 'logPulang'])
            ->orderBy('tanggal')
            ->get()
            ->map(fn($row) => [
                'tanggal'     => $row->tanggal?->format('Y-m-d'),
                'hari'        => $row->tanggal?->translatedFormat('D'),
                'status'      => $row->status,
                'jam_masuk'   => $row->logMasuk?->waktu_absensi?->format('H:i'),
                'jam_pulang'  => $row->logPulang?->waktu_absensi?->format('H:i'),
            ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'user'            => [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'nip'          => $user->nip,
                    'jabatan'      => $user->jabatan?->nama ?? 'Pegawai',
                    'status_kerja' => $user->status_kerja ?? 'wfo',
                    'avatar_url'   => $user->avatar_image_url,
                    'roles'        => $user->getRoleNames(),
                ],
                'bulan'           => $bulan,
                'tahun'           => $tahun,
                'statistik'       => [
                    'hadir'      => $totalHadir,
                    'terlambat'  => $totalTerlambat,
                    'izin'       => $totalIzin,
                    'sakit'      => $totalSakit,
                    'lhk_dibuat' => $totalLhk,
                ],
                'hari_ini'        => [
                    'sudah_masuk'  => $absensiHariIni?->log_masuk_id !== null,
                    'sudah_pulang' => $absensiHariIni?->log_pulang_id !== null,
                    'jam_masuk'    => $absensiHariIni?->logMasuk?->waktu_absensi?->format('H:i:s'),
                    'jam_pulang'   => $absensiHariIni?->logPulang?->waktu_absensi?->format('H:i:s'),
                ],
                'riwayat_7_hari'  => $riwayat7Hari,
            ],
        ]);
    }
}
