<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->limit(25)
            ->get(['id', 'data', 'read_at', 'created_at'])
            ->map(function ($n): array {
                $title = (string) ($n->data['title'] ?? 'Notifikasi');
                $rawUrl = (string) ($n->data['actions'][0]['url'] ?? '');
                $lower = strtolower($title . ' ' . $rawUrl);

                $target = 'dashboard';
                if (str_contains($lower, 'persetujuan') || str_contains($lower, 'menunggu') || str_contains($lower, 'tinjau') || str_contains($lower, 'baru:')) {
                    $target = 'persetujuan';
                } elseif (str_contains($lower, 'izin') || str_contains($lower, 'cuti') || str_contains($lower, 'sakit')) {
                    $target = 'izin';
                } elseif (str_contains($lower, 'lhk')) {
                    $target = 'lhk';
                } elseif (str_contains($lower, 'absen') || str_contains($lower, 'presensi')) {
                    $target = 'riwayat';
                }

                return [
                    'id' => $n->id,
                    'title' => $title,
                    'body' => $n->data['body'] ?? '',
                    'target' => $target,
                    'read' => $n->read_at !== null,
                    'created_at' => $n->created_at?->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
            'data' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->notifications()->where('id', $id)->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function clearAll(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->notifications()->delete();

        return response()->json(['success' => true]);
    }

    public function saveFcmToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fcm_token' => ['required', 'string', 'max:512'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->update(['fcm_token' => $validated['fcm_token']]);

        return response()->json(['success' => true]);
    }
}
