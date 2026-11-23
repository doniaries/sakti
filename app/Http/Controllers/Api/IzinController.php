<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JenisIzin;
use App\Models\PengajuanIzin;
use App\Models\User;
use App\Services\PushNotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;

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
                'pdf_url' => URL::temporarySignedRoute('mobile.izin.signed-pdf', now()->addMinutes(30), [
                    'pengajuanIzin' => $item->id,
                    'uid' => $user->id,
                    'download' => 1,
                ]),
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
            'lampiran' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ], [
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'lampiran.mimes' => 'Format lampiran harus JPG, PNG, atau PDF.',
            'lampiran.max' => 'Ukuran lampiran maksimal 2 MB.',
        ]);

        $jenisIzin = JenisIzin::find($validated['jenis_izin_id']);
        if ($jenisIzin?->wajib_lampiran && ! $request->hasFile('lampiran')) {
            return response()->json([
                'success' => false,
                'message' => 'Lampiran surat keterangan dokter/bukti wajib diunggah.',
            ], 422);
        }

        $lampiranPath = $request->hasFile('lampiran')
            ? $request->file('lampiran')->store('lampiran-izin', 'public')
            : null;

        $mulai = Carbon::parse($validated['tanggal_mulai']);
        $selesai = Carbon::parse($validated['tanggal_selesai']);
        $jumlahHari = max(1, (int) $mulai->diffInDays($selesai) + 1);

        $jenisIzin = JenisIzin::find($validated['jenis_izin_id']);
        $isCuti = ($jenisIzin?->kategori === 'cuti');

        $izin = PengajuanIzin::create([
            'user_id' => $user->id,
            'jenis_izin_id' => $validated['jenis_izin_id'],
            'tanggal_mulai' => $mulai->toDateString(),
            'tanggal_selesai' => $selesai->toDateString(),
            'jumlah_hari' => $jumlahHari,
            'alasan' => $validated['alasan'],
            'lampiran' => $lampiranPath,
            'status' => $isCuti ? 'menunggu' : 'disetujui',
            'disetujui_at' => $isCuti ? null : now(),
        ]);

        if ($isCuti) {
            $recipients = User::role(['super_admin', 'admin'])->get();
            if ($user->atasan) {
                $recipients->push($user->atasan);
            }

            PushNotificationService::sendToUsers(
                $recipients,
                'Pengajuan Cuti Baru',
                "{$user->name} mengajukan cuti ({$mulai->format('d/m/Y')} - {$selesai->format('d/m/Y')}).",
                ['type' => 'persetujuan', 'id' => (string) $izin->id],
                'info'
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil dikirim.',
            'data' => ['id' => $izin->id],
        ], 201);
    }
}
