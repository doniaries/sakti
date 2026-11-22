<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'       => 'required|email',
            'password'    => 'required|string',
            'device_id'   => 'nullable|string|max:128',
            'device_name' => 'nullable|string|max:150',
        ]);

        $email = strtolower(trim((string) $request->input('email')));
        $matchedUser = \App\Models\User::findByLoginEmail($email);

        if (! Auth::attempt(['email' => $matchedUser?->email ?? $email, 'password' => $request->input('password')])) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau kata sandi tidak ditemukan di database.',
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->aktif) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda sedang dinonaktifkan. Hubungi Admin Kepegawaian.',
            ], 403);
        }

        $deviceId = trim((string) ($request->input('device_id') ?? $request->header('X-Device-Id', '')));
        $deviceName = trim((string) ($request->input('device_name') ?? $request->header('X-Device-Name', '')));

        if (Cache::get('sakti_device_binding_enabled', true) && $deviceId !== '') {
            $deviceCheck = $user->bindOrVerifyDevice($deviceId, $deviceName !== '' ? $deviceName : null);
            if (! $deviceCheck['ok']) {
                return response()->json([
                    'success' => false,
                    'code'    => 'DEVICE_MISMATCH',
                    'message' => $deviceCheck['message'],
                ], 403);
            }
        }

        $isSuperAdminBypass = strtolower((string) $user->email) === 'superadmin@gmail.com'
            || $user->hasRole('super_admin');
        if (! $isSuperAdminBypass) {
            $user->tokens()->where('name', 'mobile-app')->delete();
        }
        $token = $user->createToken('mobile-app', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'success' => true,
            'data'    => [
                'token' => $token,
                'user'  => [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'nip'          => $user->nip,
                    'jabatan'      => $user->jabatan?->nama ?? 'Pegawai',
                    'status_kerja' => $user->status_kerja ?? 'wfo',
                    'avatar_url'   => $user->avatar_image_url,
                    'roles'        => $user->getRoleNames(),
                ],
            ],
        ]);
    }

    public function me(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->loadMissing('jabatan');

        return response()->json([
            'success' => true,
            'data'    => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'nip'          => $user->nip,
                'jabatan'      => $user->jabatan?->nama ?? 'Pegawai',
                'status_kerja' => $user->status_kerja ?? 'wfo',
                'avatar_url'   => $user->avatar_image_url,
                'roles'        => $user->getRoleNames(),
            ],
        ]);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $path = $request->file('avatar')->store("dokumen_pegawai/{$user->id}/avatars", 'public');
        if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        $user->update(['avatar_url' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Foto profil berhasil diperbarui.',
            'data' => [
                'avatar_url' => $user->fresh()->avatar_image_url,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout.',
        ]);
    }

    public function getDeviceBinding(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        if (! $user->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'enabled' => (bool) Cache::get('sakti_device_binding_enabled', true),
            ],
        ]);
    }

    public function setDeviceBinding(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        if (! $user->hasAnyRole(['super_admin', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $enabled = $request->boolean('enabled', true);
        Cache::forever('sakti_device_binding_enabled', $enabled);

        return response()->json([
            'success' => true,
            'message' => $enabled
                ? 'Pembatasan 1 akun 1 perangkat diaktifkan.'
                : 'Pembatasan 1 akun 1 perangkat dinonaktifkan.',
            'data' => [
                'enabled' => $enabled,
            ],
        ]);
    }
}
