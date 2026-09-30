<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiHarian;
use App\Models\Aktivitas;
use App\Models\Lhk;
use App\Models\LhkPekerjaan;
use Illuminate\Http\Request;

class LhkController extends Controller
{
    /**
     * Daftar LHK milik user (bulan ini)
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $bulan = $request->get('bulan', now()->month);
        $tahun = $request->get('tahun', now()->year);

        $data = Lhk::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->with(['pekerjaans'])
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(fn($lhk) => [
                'id'              => $lhk->id,
                'tanggal'         => $lhk->tanggal,
                'tanggal_format'  => \Carbon\Carbon::parse($lhk->tanggal)->translatedFormat('d F Y'),
                'status_kehadiran' => $lhk->status_kehadiran,
                'jam_masuk'       => $lhk->jam_masuk,
                'jam_pulang'      => $lhk->jam_pulang,
                'jumlah_kegiatan' => $lhk->pekerjaans->count(),
            ]);

        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Detail LHK + pekerjaan
     */
    public function show(Request $request, Lhk $lhk)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if ($lhk->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'               => $lhk->id,
                'tanggal'          => $lhk->tanggal,
                'status_kehadiran' => $lhk->status_kehadiran,
                'jam_masuk'        => $lhk->jam_masuk,
                'jam_pulang'       => $lhk->jam_pulang,
                'pekerjaans'       => $lhk->pekerjaans->map(fn($p) => [
                    'id'             => $p->id,
                    'jam_mulai'      => $p->jam_mulai,
                    'jam_selesai'    => $p->jam_selesai,
                    'lama_menit'     => $p->lama_menit,
                    'rencana_aksi'   => $p->rencana_aksi_id,
                    'aktivitas_id'   => $p->aktivitas_id,
                    'hasil_pekerjaan' => $p->hasil_pekerjaan,
                    'bukti_url'      => $p->bukti_url,
                ]),
            ],
        ]);
    }

    /**
     * Data absensi hari tertentu (untuk isi form LHK)
     */
    public function absensiTanggal(Request $request)
    {
        $request->validate(['tanggal' => 'required|date']);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $absensi = AbsensiHarian::where('user_id', $user->id)
            ->whereDate('tanggal', $request->tanggal)
            ->with(['logMasuk', 'logPulang'])
            ->first();

        if (! $absensi || ! $absensi->log_masuk_id || ! $absensi->log_pulang_id) {
            return response()->json([
                'success' => false,
                'message' => 'Absensi pada tanggal tersebut belum lengkap.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'log_absensi_masuk_id'  => $absensi->log_masuk_id,
                'log_absensi_pulang_id' => $absensi->log_pulang_id,
                'jam_masuk'             => $absensi->logMasuk?->waktu_absensi?->format('H:i:s'),
                'jam_pulang'            => $absensi->logPulang?->waktu_absensi?->format('H:i:s'),
                'status'                => $absensi->status,
            ],
        ]);
    }

    /**
     * Daftar aktivitas untuk pilihan form LHK
     */
    public function aktivitas()
    {
        $data = Aktivitas::orderBy('nama')->get(['id', 'nama']);
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Simpan LHK baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal'                  => 'required|date',
            'log_absensi_masuk_id'     => 'required|exists:log_absensi,id',
            'log_absensi_pulang_id'    => 'required|exists:log_absensi,id',
            'status_kehadiran'         => 'required|string',
            'pekerjaans'               => 'required|array|min:1',
            'pekerjaans.*.jam_mulai'   => 'required|date_format:H:i',
            'pekerjaans.*.jam_selesai' => 'required|date_format:H:i',
            'pekerjaans.*.rencana_aksi_id' => 'nullable|integer',
            'pekerjaans.*.aktivitas_id'    => 'nullable|integer',
            'pekerjaans.*.hasil_pekerjaan' => 'nullable|string',
            'pekerjaans.*.bukti_url'       => 'nullable|url',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        // Pastikan absensi sudah ada dan lengkap
        $absensi = AbsensiHarian::where('user_id', $user->id)
            ->whereDate('tanggal', $validated['tanggal'])
            ->first();

        if (! $absensi || ! $absensi->log_masuk_id || ! $absensi->log_pulang_id) {
            return response()->json(['success' => false, 'message' => 'Absensi belum lengkap.'], 422);
        }

        // Cek LHK sudah dibuat untuk tanggal ini
        $existing = Lhk::where('user_id', $user->id)->where('tanggal', $validated['tanggal'])->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'LHK untuk tanggal ini sudah dibuat.'], 422);
        }

        $lhk = Lhk::create([
            'user_id'                => $user->id,
            'tanggal'                => $validated['tanggal'],
            'log_absensi_masuk_id'   => $validated['log_absensi_masuk_id'],
            'log_absensi_pulang_id'  => $validated['log_absensi_pulang_id'],
            'status_kehadiran'       => $validated['status_kehadiran'],
            'jam_masuk'              => $absensi->logMasuk?->waktu_absensi?->format('H:i:s'),
            'jam_pulang'             => $absensi->logPulang?->waktu_absensi?->format('H:i:s'),
        ]);

        foreach ($validated['pekerjaans'] as $p) {
            $jamMulai   = \Carbon\Carbon::createFromFormat('H:i', $p['jam_mulai']);
            $jamSelesai = \Carbon\Carbon::createFromFormat('H:i', $p['jam_selesai']);
            $lama = max(0, $jamMulai->diffInMinutes($jamSelesai));

            LhkPekerjaan::create([
                'lhk_id'          => $lhk->id,
                'jam_mulai'       => $p['jam_mulai'],
                'jam_selesai'     => $p['jam_selesai'],
                'lama_menit'      => $lama,
                'rencana_aksi_id' => $p['rencana_aksi_id'] ?? null,
                'aktivitas_id'    => $p['aktivitas_id'] ?? null,
                'hasil_pekerjaan' => $p['hasil_pekerjaan'] ?? null,
                'bukti_url'       => $p['bukti_url'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'LHK berhasil disimpan.',
            'data'    => ['id' => $lhk->id],
        ], 201);
    }
}
