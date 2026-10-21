<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiHarian;
use App\Models\LogAbsensi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    /**
     * Daftar absensi bulan ini (riwayat)
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $bulan  = $request->get('bulan', now()->month);
        $tahun  = $request->get('tahun', now()->year);
        $status = $request->get('status');

        $query = AbsensiHarian::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->with(['logMasuk', 'logPulang']);

        if ($status && $status !== 'semua') {
            if ($status === 'hadir') {
                $query->whereIn('status', ['hadir', 'terlambat']);
            } elseif ($status === 'izin_sakit') {
                $query->whereIn('status', ['izin', 'sakit']);
            } elseif ($status === 'tidak_hadir') {
                $query->whereIn('status', ['tidak_hadir', 'alpa']);
            } else {
                $query->where('status', $status);
            }
        }

        $allBulanIni = AbsensiHarian::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->get();

        $rekap = [
            'total' => $allBulanIni->count(),
            'hadir' => $allBulanIni->filter(fn($a) => in_array($a->status?->value ?? $a->status, ['hadir', 'terlambat']))->count(),
            'terlambat' => $allBulanIni->filter(fn($a) => ($a->status?->value ?? $a->status) === 'terlambat')->count(),
            'dinas_luar' => $allBulanIni->filter(fn($a) => in_array($a->status?->value ?? $a->status, ['dinas_luar', 'dl']))->count(),
            'izin_sakit' => $allBulanIni->filter(fn($a) => in_array($a->status?->value ?? $a->status, ['izin', 'sakit']))->count(),
            'alpa' => $allBulanIni->filter(fn($a) => in_array($a->status?->value ?? $a->status, ['tidak_hadir', 'alpa']))->count(),
        ];

        $data = $query->orderBy('tanggal', 'desc')
            ->get()
            ->map(fn($row) => [
                'id'             => $row->id,
                'tanggal'        => $row->tanggal?->format('Y-m-d'),
                'tanggal_format' => $row->tanggal?->translatedFormat('l, d F Y'),
                'status'         => $row->status?->value ?? $row->status,
                'jenis_kehadiran'=> $row->jenis_kehadiran?->value ?? $row->jenis_kehadiran,
                'jam_masuk'      => $row->jam_masuk ?? $row->logMasuk?->waktu_absensi?->format('H:i:s'),
                'jam_pulang'     => $row->jam_pulang ?? $row->logPulang?->waktu_absensi?->format('H:i:s'),
                'perangkat_masuk'=> $row->logMasuk?->perangkat,
                'perangkat_pulang'=> $row->logPulang?->perangkat,
                'keterangan'     => $row->keterangan,
                'sudah_masuk'    => !empty($row->jam_masuk) || $row->log_masuk_id !== null,
                'sudah_pulang'   => !empty($row->jam_pulang) || $row->log_pulang_id !== null,
            ]);

        return response()->json([
            'success' => true, 
            'rekap'   => $rekap,
            'data'    => $data
        ]);
    }

    /**
     * Ambil status absensi hari ini
     */
    public function today(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $absensi = AbsensiHarian::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->with(['logMasuk', 'logPulang'])
            ->first();

        return response()->json([
            'success' => true,
            'data'    => [
                'tanggal'        => today()->format('Y-m-d'),
                'tanggal_format' => today()->translatedFormat('l, d F Y'),
                'sudah_masuk'    => $absensi?->log_masuk_id !== null,
                'sudah_pulang'   => $absensi?->log_pulang_id !== null,
                'jam_masuk'      => $absensi?->logMasuk?->waktu_absensi?->format('H:i:s'),
                'jam_pulang'     => $absensi?->logPulang?->waktu_absensi?->format('H:i:s'),
                'status'         => $absensi?->status,
            ],
        ]);
    }

    /**
     * Tap absensi — masuk atau pulang (otomatis)
     */
    public function store(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        // ── 1. Validasi jarak dari kantor (Bebas radius jika status_kerja = 'wfh') ─────
        $isWfh = ($user->status_kerja === 'wfh');
        $jarakMeter = $this->hitungJarak(
            $request->latitude, $request->longitude,
            config('absensi.kantor_lat', -0.9492),
            config('absensi.kantor_lng', 104.0200),
        );

        if (!$isWfh && $jarakMeter > config('absensi.radius_meter', 100)) {
            return response()->json([
                'success' => false,
                'message' => "Anda berada di luar area kantor. Jarak Anda: {$jarakMeter}m dari kantor. (Presensi WFO wajib berada di dalam radius kantor)",
            ], 422);
        }

        // ── 2. Cek absensi hari ini ───────────────────────────────────────────
        $absensiHarian = AbsensiHarian::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->first();

        $jenis = ($absensiHarian?->log_masuk_id === null) ? 'masuk' : 'pulang';

        // Jika sudah lengkap, tolak
        if ($absensiHarian?->log_masuk_id && $absensiHarian?->log_pulang_id) {
            return response()->json([
                'success' => false,
                'message' => 'Absensi hari ini sudah lengkap (Masuk & Pulang).',
            ], 422);
        }

        // ── 3. Buat LogAbsensi ───────────────────────────────────────────────
        $log = LogAbsensi::create([
            'user_id'           => $user->id,
            'waktu_absensi'     => now(),
            'jenis'             => $jenis,
            'perangkat'         => 'mobile',
            'metode_verifikasi' => 'gps',
            'sumber'            => 'Mobile App',
            'data_asli'         => [
                'latitude'  => $request->latitude,
                'longitude' => $request->longitude,
                'jarak_m'   => $jarakMeter,
            ],
        ]);

        // ── 4. Upsert AbsensiHarian ──────────────────────────────────────────
        $kehadiranTipe = ($user->status_kerja === 'wfh') ? 'wfh' : 'wfo';

        if ($jenis === 'masuk') {
            $absensiHarian = AbsensiHarian::updateOrCreate(
                ['user_id' => $user->id, 'tanggal' => today()],
                [
                    'log_masuk_id'    => $log->id,
                    'jam_masuk'       => now()->format('H:i:s'),
                    'status'          => 'hadir',
                    'jenis_kehadiran' => $kehadiranTipe,
                    'jadwal_kerja_id' => $user->jadwal_kerja_id,
                ]
            );
        } else {
            $absensiHarian->update([
                'log_pulang_id'   => $log->id,
                'jam_pulang'      => now()->format('H:i:s'),
                'jenis_kehadiran' => $absensiHarian->jenis_kehadiran ?? $kehadiranTipe,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Absensi ' . ($jenis === 'masuk' ? 'Masuk' : 'Pulang') . ' berhasil dicatat.',
            'data'    => [
                'jenis'    => $jenis,
                'waktu'    => $log->waktu_absensi->format('H:i:s'),
                'jarak_m'  => $jarakMeter,
            ],
        ]);
    }

    /**
     * Hitung jarak Haversine antara dua koordinat (meter)
     */
    private function hitungJarak(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 2);
    }
}
