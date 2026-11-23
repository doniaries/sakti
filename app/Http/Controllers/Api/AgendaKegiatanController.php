<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgendaKegiatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class AgendaKegiatanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = Carbon::today()->toDateString();

        $isKadisOrAdmin = $user && method_exists($user, 'hasAnyRole')
            ? $user->hasAnyRole(['super_admin', 'admin', 'kepala_dinas', 'kadis'])
            : false;

        $baseQuery = AgendaKegiatan::query()
            ->with(['disposisis' => fn ($q) => $q->where('user_id', $user?->id)])
            ->when(! $isKadisOrAdmin && $user, function ($query) use ($user) {
                $query->whereHas('disposisis', fn ($q) => $q->where('user_id', $user->id));
            });

        $todayAgendas = (clone $baseQuery)
            ->whereDate('tanggal', $today)
            ->orderBy('jam_mulai')
            ->get()
            ->map(fn (AgendaKegiatan $item) => $this->formatItem($item));

        $upcomingAgendas = (clone $baseQuery)
            ->whereDate('tanggal', '>', $today)
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->limit(15)
            ->get()
            ->map(fn (AgendaKegiatan $item) => $this->formatItem($item));

        return response()->json([
            'success' => true,
            'data' => [
                'today_count' => $todayAgendas->count(),
                'today' => $todayAgendas->values(),
                'upcoming' => $upcomingAgendas->values(),
            ],
        ]);
    }

    private function formatItem(AgendaKegiatan $item): array
    {
        $disposisi = $item->disposisis->first();

        return [
            'id' => $item->id,
            'judul' => $item->judul,
            'tanggal' => $item->tanggal?->format('Y-m-d'),
            'tanggal_label' => $item->tanggal?->translatedFormat('d M Y'),
            'jam_mulai' => substr((string) $item->jam_mulai, 0, 5),
            'jam_selesai' => $item->jam_selesai ? substr((string) $item->jam_selesai, 0, 5) : null,
            'tempat' => $item->tempat,
            'asal_undangan' => $item->asal_undangan,
            'keterangan' => $item->keterangan,
            'instruksi' => $disposisi?->instruksi,
            'lampiran_url' => $item->lampiran_file
                ? Storage::disk('public')->url($item->lampiran_file)
                : null,
        ];
    }
}
