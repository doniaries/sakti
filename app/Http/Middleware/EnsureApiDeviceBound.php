<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiDeviceBound
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Cache::get('sakti_device_binding_enabled', true)) {
            return $next($request);
        }

        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $headerDeviceId = trim((string) $request->header('X-Device-Id', ''));
        $headerDeviceName = trim((string) $request->header('X-Device-Name', ''));

        if ($headerDeviceId !== '') {
            $check = $user->bindOrVerifyDevice($headerDeviceId, $headerDeviceName !== '' ? $headerDeviceName : null);
            if (! $check['ok']) {
                return response()->json([
                    'success' => false,
                    'code' => 'DEVICE_MISMATCH',
                    'message' => $check['message'] ?? 'Perangkat tidak diizinkan untuk akun ini.',
                ], 403);
            }
        } elseif (! empty($user->device_id)) {
            return response()->json([
                'success' => false,
                'code' => 'DEVICE_REQUIRED',
                'message' => 'Identitas perangkat diperlukan. Silakan perbarui aplikasi atau login ulang.',
            ], 403);
        }

        return $next($request);
    }
}
