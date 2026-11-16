<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengajuanIzin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersetujuanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $isAdminOrKadis = $user->hasAnyRole(['super_admin', 'admin_kepegawaian', 'pa']);

        $query = PengajuanIzin::query()
            ->with(['user:id,name,nip,atasan_id', 'jenisIzin:id,nama,kategori'])
            ->where('status', 'menunggu')
            ->latest('id');

        if (! $isAdminOrKadis) {
            $query->whereHas('user', fn($q) => $q->where('atasan_id', $user->id));
        }

        $items = $query->limit(30)->get()->map(fn(PengajuanIzin $item): array => [
            'id' => $item->id,
            'nama' => $item->user?->name ?? 'Pegawai',
            'nip' => $item->user?->nip ?? '-',
            'jenis' => $item->jenisIzin?->nama ?? 'Pengajuan',
            'kategori' => $item->jenisIzin?->kategori ?? 'izin',
            'tanggal_mulai' => $item->tanggal_mulai?->format('d/m/Y'),
            'tanggal_selesai' => $item->tanggal_selesai?->format('d/m/Y'),
            'jumlah_hari' => (int) $item->jumlah_hari,
            'alasan' => $item->alasan,
        ]);

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function approve(Request $request, PengajuanIzin $pengajuanIzin): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $pengajuanIzin->update([
            'status' => 'disetujui',
            'disetujui_oleh' => $user->id,
            'disetujui_at' => now(),
            'catatan_atasan' => $request->input('catatan'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil disetujui.',
        ]);
    }

    public function reject(Request $request, PengajuanIzin $pengajuanIzin): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $pengajuanIzin->update([
            'status' => 'ditolak',
            'disetujui_oleh' => $user->id,
            'disetujui_at' => now(),
            'catatan_atasan' => $request->input('catatan', 'Ditolak melalui aplikasi mobile.'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil ditolak.',
        ]);
    }
}
