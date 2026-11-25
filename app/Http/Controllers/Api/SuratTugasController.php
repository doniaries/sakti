<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanSuratTugas;
use App\Models\SuratTugas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SuratTugasController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = SuratTugas::query()
            ->with(['pegawais:id,name', 'laporans.user:id,name'])
            ->where('status', 'terbit')
            ->latest('tanggal_mulai');

        if (! $user->hasAnyRole(['super_admin', 'pa', 'kadis', 'admin'])) {
            $query->whereHas('pegawais', fn ($q) => $q->where('users.id', $user->id));
        }

        $items = $query->paginate(20)->through(function (SuratTugas $surat) use ($user): array {
            $pivot = $surat->pegawais->firstWhere('id', $user->id)?->pivot;

            return [
                'id' => $surat->id,
                'nomor_surat' => $surat->nomor_surat,
                'nama_kegiatan' => $surat->nama_kegiatan,
                'tanggal_mulai' => $surat->tanggal_mulai?->toDateString(),
                'tanggal_selesai' => $surat->tanggal_selesai?->toDateString(),
                'jam_acara' => $surat->jam_acara,
                'lokasi' => $surat->lokasi,
                'bukti_surat_undangan_url' => filled($surat->bukti_surat_undangan)
                    ? Storage::disk('public')->url($surat->bukti_surat_undangan)
                    : null,
                'surat_tugas_pdf_url' => route('surat-tugas.cetak-pdf', $surat),
                'laporan_pdf_url' => $surat->laporans->isNotEmpty()
                    ? route('surat-tugas.laporan-pdf', $surat)
                    : null,
                'share_wa_surat_url' => 'https://wa.me/?text=' . urlencode("Surat Tugas: {$surat->nama_kegiatan}\n" . route('surat-tugas.cetak-pdf', $surat)),
                'share_wa_laporan_url' => $surat->laporans->isNotEmpty()
                    ? 'https://wa.me/?text=' . urlencode("Laporan Kegiatan: {$surat->nama_kegiatan}\n" . route('surat-tugas.laporan-pdf', $surat))
                    : null,
                'status_kehadiran' => $pivot?->status_kehadiran ?? 'belum_konfirmasi',
                'catatan_kehadiran' => $pivot?->catatan_kehadiran,
                'dikonfirmasi_at' => $pivot?->dikonfirmasi_at,
                'sudah_lapor' => $surat->laporans->isNotEmpty(),
                'jumlah_laporan' => $surat->laporans->count(),
            ];
        });

        return response()->json($items);
    }

    public function show(Request $request, SuratTugas $suratTugas): JsonResponse
    {
        $suratTugas->load(['pegawais:id,name', 'laporans.user:id,name']);
        $user = $request->user();
        $suratTugas->tandaiDibacaOleh($user->id);

        return response()->json([
            'data' => [
                'id' => $suratTugas->id,
                'nomor_surat' => $suratTugas->nomor_surat,
                'nama_kegiatan' => $suratTugas->nama_kegiatan,
                'tanggal_mulai' => $suratTugas->tanggal_mulai?->toDateString(),
                'tanggal_selesai' => $suratTugas->tanggal_selesai?->toDateString(),
                'jam_acara' => $suratTugas->jam_acara,
                'lokasi' => $suratTugas->lokasi,
                'bukti_surat_undangan_url' => filled($suratTugas->bukti_surat_undangan)
                    ? Storage::disk('public')->url($suratTugas->bukti_surat_undangan)
                    : null,
                'tim' => $suratTugas->pegawais->map(fn ($p): array => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'status_kehadiran' => $p->pivot->status_kehadiran ?? 'belum_konfirmasi',
                    'catatan_kehadiran' => $p->pivot->catatan_kehadiran,
                    'dikonfirmasi_at' => $p->pivot->dikonfirmasi_at,
                ]),
                'laporans' => $suratTugas->laporans->map(fn (LaporanSuratTugas $l): array => [
                    'id' => $l->id,
                    'pelapor' => $l->user?->name,
                    'ringkasan_kegiatan' => $l->ringkasan_kegiatan,
                    'link_dokumentasi' => $l->link_dokumentasi,
                    'foto_dokumentasi_urls' => collect($l->foto_dokumentasi ?? [])
                        ->map(fn (string $path): string => Storage::disk('public')->url($path))
                        ->values(),
                    'dilaporkan_at' => $l->dilaporkan_at?->toIso8601String(),
                ]),
            ],
        ]);
    }

    public function konfirmasiKehadiran(Request $request, SuratTugas $suratTugas): JsonResponse
    {
        $validated = $request->validate([
            'status_kehadiran' => ['required', 'in:hadir,berhalangan'],
            'catatan_kehadiran' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();

        if (! $suratTugas->pegawais()->where('users.id', $user->id)->exists()) {
            return response()->json(['message' => 'Anda tidak terdaftar pada surat tugas ini.'], 403);
        }

        $catatan = $validated['status_kehadiran'] === 'berhalangan'
            ? ($validated['catatan_kehadiran'] ?? null)
            : null;

        $suratTugas->pegawais()->updateExistingPivot($user->id, [
            'status_kehadiran' => $validated['status_kehadiran'],
            'catatan_kehadiran' => $catatan,
            'dikonfirmasi_at' => now(),
            'dibaca_at' => now(),
        ]);

        $suratTugas->kirimNotifikasiKehadiran(
            $user,
            $validated['status_kehadiran'],
            $catatan
        );

        return response()->json([
            'message' => 'Konfirmasi kehadiran berhasil disimpan.',
            'status_kehadiran' => $validated['status_kehadiran'],
        ]);
    }

    public function storeLaporan(Request $request, SuratTugas $suratTugas): JsonResponse
    {
        $validated = $request->validate([
            'ringkasan_kegiatan' => ['required', 'string', 'max:5000'],
            'link_dokumentasi' => ['nullable', 'url', 'max:500'],
            'foto_dokumentasi' => ['nullable', 'array', 'max:5'],
            'foto_dokumentasi.*' => ['image', 'max:5120'],
        ]);

        $user = $request->user();

        if (! $suratTugas->pegawais()->where('users.id', $user->id)->exists()) {
            return response()->json(['message' => 'Anda tidak terdaftar pada surat tugas ini.'], 403);
        }

        $fotoPaths = [];
        if ($request->hasFile('foto_dokumentasi')) {
            foreach ($request->file('foto_dokumentasi') as $file) {
                $fotoPaths[] = $file->store('surat-tugas/laporan', 'public');
            }
        }

        $laporan = LaporanSuratTugas::create([
            'surat_tugas_id' => $suratTugas->id,
            'user_id' => $user->id,
            'ringkasan_kegiatan' => $validated['ringkasan_kegiatan'],
            'link_dokumentasi' => $validated['link_dokumentasi'] ?? null,
            'foto_dokumentasi' => $fotoPaths,
            'dilaporkan_at' => now(),
        ]);

        return response()->json([
            'message' => 'Laporan kegiatan berhasil dikirim.',
            'data' => $laporan,
        ], 201);
    }
}
