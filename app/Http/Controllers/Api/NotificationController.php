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
            ->limit(20)
            ->get(['id', 'data', 'read_at', 'created_at'])
            ->map(fn ($n): array => [
                'id' => $n->id,
                'title' => $n->data['title'] ?? 'Notifikasi',
                'body' => $n->data['body'] ?? '',
                'read' => $n->read_at !== null,
                'created_at' => $n->created_at?->diffForHumans(),
            ]);

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
