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

        $canCreate = $user && (
            (method_exists($user, 'hasAnyRole') && $user->hasAnyRole([
                'super_admin', 'admin', 'pa', 'kadis', 'sekretaris', 'kabid', 'kepala_bidang',
            ])) ||
            (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('kelola_agenda'))
        );

        return response()->json([
            'success' => true,
            'data' => [
                'can_create' => $canCreate,
                'today_count' => $todayAgendas->count(),
                'today' => $todayAgendas->values(),
                'upcoming' => $upcomingAgendas->values(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        $canCreate = $user && (
            (method_exists($user, 'hasAnyRole') && $user->hasAnyRole([
                'super_admin', 'admin', 'pa', 'kadis', 'sekretaris', 'kabid', 'kepala_bidang',
            ])) ||
            (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('kelola_agenda'))
        );

        if (! $canCreate) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk membuat agenda.',
            ], 403);
        }

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'string', 'max:10'],
            'jam_selesai' => ['nullable', 'string', 'max:10'],
            'tempat' => ['required', 'string', 'max:255'],
            'asal_undangan' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $agenda = AgendaKegiatan::query()->create([
            ...$validated,
            'created_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Agenda kegiatan berhasil ditambahkan.',
            'data' => $this->formatItem($agenda),
        ], 201);
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
