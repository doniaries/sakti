<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JenisIzin;
use App\Models\PengajuanIzin;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class IzinController extends Controller
{
    public function jenis(Request $request): JsonResponse
    {
        $kategori = $request->query('kategori');

        $items = Cache::remember('api_jenis_izin_aktif', 3600, function () {
            return JenisIzin::query()
                ->where('aktif', true)
                ->orderBy('nama')
                ->get(['id', 'kode', 'nama', 'kategori', 'kuota_hari', 'wajib_lampiran']);
        });

        if ($kategori && $kategori !== 'semua') {
            $items = $items->where('kategori', $kategori)->values();
        }

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $items = PengajuanIzin::query()
            ->with('jenisIzin:id,nama,kategori')
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit(25)
            ->get()
            ->map(fn (PengajuanIzin $item): array => [
                'id' => $item->id,
                'jenis' => $item->jenisIzin?->nama ?? 'Pengajuan',
                'kategori' => $item->jenisIzin?->kategori ?? 'izin',
                'tanggal_mulai' => $item->tanggal_mulai?->format('d/m/Y'),
                'tanggal_selesai' => $item->tanggal_selesai?->format('d/m/Y'),
                'jumlah_hari' => (int) $item->jumlah_hari,
                'alasan' => $item->alasan,
                'status' => $item->status,
                'pdf_url' => route('pengajuan-izin.cetak-pdf', ['pengajuanIzin' => $item, 'preview' => 1]),
            ]);

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $validated = $request->validate([
            'jenis_izin_id' => ['required', 'exists:jenis_izins,id'],
            'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'alasan' => ['required', 'string', 'max:1000'],
        ], [
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        $mulai = Carbon::parse($validated['tanggal_mulai']);
        $selesai = Carbon::parse($validated['tanggal_selesai']);
        $jumlahHari = max(1, (int) $mulai->diffInDays($selesai) + 1);

        $izin = PengajuanIzin::create([
            'user_id' => $user->id,
            'jenis_izin_id' => $validated['jenis_izin_id'],
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
            'jumlah_hari' => $jumlahHari,
            'alasan' => $validated['alasan'],
            'status' => 'menunggu',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil dikirim.',
            'data' => ['id' => $izin->id],
        ], 201);
    }
}
