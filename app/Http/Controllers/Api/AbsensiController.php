<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiHarian;
use App\Models\HariLibur;
use App\Models\JadwalKerja;
use App\Models\LogAbsensi;
use App\Models\PengaturanAplikasi;
use App\Services\AttendancePunchService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

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
     * Ambil status absensi hari ini + konfigurasi lokasi & jadwal kerja
     */
    public function today(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->loadMissing(['jabatan', 'jadwalKerja']);

        $todayStr = today()->format('Y-m-d');
        $absensi = AbsensiHarian::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->with(['logMasuk', 'logPulang'])
            ->first();

        $pengaturan = PengaturanAplikasi::getPengaturan();
        $hariLibur = HariLibur::where('tanggal', $todayStr)->where('is_aktif', true)->first();
        $jadwal = $user->getJadwalKerja() ?? JadwalKerja::getDefault();

        $bukaMasuk = $jadwal ? substr($jadwal->buka_absen_masuk, 0, 5) : '06:00';
        $tutupMasuk = $jadwal ? substr($jadwal->tutup_absen_masuk, 0, 5) : '09:00';
        $jamMasukResmi = $jadwal ? $jadwal->getJamMasukHari($todayStr) : '07:30';
        $jamPulangResmi = $jadwal ? $jadwal->getJamPulangHari($todayStr) : '16:00';
        $tutupPulang = $jadwal ? substr($jadwal->tutup_absen_pulang, 0, 5) : '23:59';

        $isAdmin = $user->hasAnyRole(['super_admin', 'admin']);
        $modeSimulasi = (bool) $pengaturan->mode_simulasi;
        $nowTime = ($modeSimulasi && ! empty($pengaturan->simulasi_jam_aktif))
            ? substr($pengaturan->simulasi_jam_aktif, 0, 5)
            : Carbon::now()->format('H:i');

        $sudahMasuk = $absensi?->log_masuk_id !== null || ! empty($absensi?->jam_masuk);
        $sudahPulang = $absensi?->log_pulang_id !== null || ! empty($absensi?->jam_pulang);

        return response()->json([
            'success' => true,
            'data'    => [
                'tanggal'           => $todayStr,
                'tanggal_format'    => today()->translatedFormat('l, d F Y'),
                'sudah_masuk'       => $sudahMasuk,
                'sudah_pulang'      => $sudahPulang,
                'jam_masuk'         => $absensi?->jam_masuk ?? $absensi?->logMasuk?->waktu_absensi?->format('H:i:s'),
                'jam_pulang'        => $absensi?->jam_pulang ?? $absensi?->logPulang?->waktu_absensi?->format('H:i:s'),
                'status'            => $absensi?->status?->value ?? $absensi?->status,
                'office_lat'        => (float) ($pengaturan->latitude_kantor ?? -0.69752300),
                'office_lng'        => (float) ($pengaturan->longitude_kantor ?? 100.98547200),
                'max_radius'        => (int) ($pengaturan->radius_meter ?? 150),
                'jam_masuk_resmi'   => $jamMasukResmi,
                'jam_pulang_resmi'  => $jamPulangResmi,
                'hari_libur'        => $hariLibur?->nama,
                'jabatan_nama'      => $user->jabatan?->nama,
                'nomor_absensi'     => $user->nomor_absensi,
                'is_admin'          => $isAdmin,
                'mode_simulasi'     => $modeSimulasi,
                'lewat_batas_masuk' => (! $isAdmin && ! $modeSimulasi && ! $sudahMasuk && $nowTime > $tutupMasuk),
                'belum_buka_masuk'  => (! $isAdmin && ! $modeSimulasi && $nowTime < $bukaMasuk),
                'belum_jam_pulang'  => (! $isAdmin && ! $modeSimulasi && $nowTime < $jamPulangResmi),
                'lewat_batas_pulang'=> (! $isAdmin && ! $modeSimulasi && $nowTime > $tutupPulang),
            ],
        ]);
    }

    /**
     * Generate signed URL untuk cetak/download PDF riwayat absensi
     */
    public function riwayatPdfUrl(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $url = URL::temporarySignedRoute('mobile.riwayat.signed-pdf', now()->addMinutes(15), [
            'uid' => $user->id,
            'bulan' => $request->query('bulan', now()->month),
            'tahun' => $request->query('tahun', now()->year),
            'status' => $request->query('status', 'semua'),
        ]);

        return response()->json([
            'success' => true,
            'data' => ['url' => $url],
        ]);
    }

    /**
     * Fitur Admin: Reset presensi hari ini untuk uji ulang
     */
    public function resetToday(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        if (! $user->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Admin yang dapat melakukan reset presensi.',
            ], 403);
        }

        AbsensiHarian::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data absensi hari ini berhasil dikosongkan.',
        ]);
    }

    /**
     * Fitur Admin: Perbarui titik koordinat kantor & radius
     */
    public function setOfficeLocation(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        if (! $user->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Admin yang dapat mengubah lokasi kantor.',
            ], 403);
        }

        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meter' => 'nullable|integer|min:10|max:5000',
        ]);

        $pengaturan = PengaturanAplikasi::getPengaturan();
        $update = [
            'latitude_kantor' => $validated['latitude'],
            'longitude_kantor' => $validated['longitude'],
        ];
        if (isset($validated['radius_meter'])) {
            $update['radius_meter'] = $validated['radius_meter'];
        }
        $pengaturan->update($update);

        return response()->json([
            'success' => true,
            'message' => 'Titik koordinat kantor berhasil diperbarui.',
            'data' => [
                'office_lat' => (float) $pengaturan->latitude_kantor,
                'office_lng' => (float) $pengaturan->longitude_kantor,
                'max_radius' => (int) $pengaturan->radius_meter,
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
        $pengaturan = PengaturanAplikasi::getPengaturan();
        $officeLat = (float) ($pengaturan->latitude_kantor ?? config('absensi.kantor_lat', -0.69752300));
        $officeLng = (float) ($pengaturan->longitude_kantor ?? config('absensi.kantor_lng', 100.98547200));
        $maxRadius = (int) ($pengaturan->radius_meter ?? config('absensi.radius_meter', 150));

        $isWfh = ($user->status_kerja === 'wfh');
        $jarakMeter = $this->hitungJarak(
            $request->latitude, $request->longitude,
            $officeLat,
            $officeLng,
        );

        if (!$isWfh && $jarakMeter > $maxRadius) {
            return response()->json([
                'success' => false,
                'message' => "Anda berada di luar area kantor. Jarak Anda: {$jarakMeter}m dari kantor. (Presensi WFO wajib berada di dalam radius {$maxRadius}m)",
            ], 422);
        }

        // ── 2. Delegasikan ke AttendancePunchService (sama persis dengan PWA) ──
        $absensiHarian = AbsensiHarian::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->first();

        $sudahMasuk = $absensiHarian && (! empty($absensiHarian->jam_masuk) || $absensiHarian->log_masuk_id !== null);
        $sudahPulang = $absensiHarian && (! empty($absensiHarian->jam_pulang) || $absensiHarian->log_pulang_id !== null);

        if ($sudahMasuk && $sudahPulang) {
            return response()->json([
                'success' => false,
                'message' => 'Absensi hari ini sudah lengkap (Masuk & Pulang).',
            ], 422);
        }

        $jenis = $sudahMasuk ? 'pulang' : 'masuk';
        $result = AttendancePunchService::punch(
            $user,
            $jenis,
            (float) $request->latitude,
            (float) $request->longitude,
            false
        );

        if (! ($result['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Presensi gagal diproses.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'] ?? ('Absensi ' . ($jenis === 'masuk' ? 'Masuk' : 'Pulang') . ' berhasil dicatat.'),
            'data'    => [
                'jenis'   => $jenis,
                'waktu'   => now()->format('H:i:s'),
                'jarak_m' => $jarakMeter,
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
