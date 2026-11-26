<?php

namespace App\Services;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    /**
     * Kirim notifikasi ke tabel database Filament & Push Notification FCM (jika firebase-credentials.json tersedia).
     *
     * @param  iterable<User>  $users
     * @param  array<string, string>  $data
     */
    public static function sendToUsers(iterable $users, string $title, string $body, array $data = [], string $status = 'info'): void
    {
        $recipients = collect($users)->filter(fn ($u) => $u instanceof User)->unique('id')->values();
        if (\App\Models\PengaturanAplikasi::getPengaturan()->mode_simulasi) {
            $recipients = $recipients->filter(
                fn (User $u) => $u->hasAnyRole(['super_admin', 'superadmin'])
            )->values();
        }
        if ($recipients->isEmpty()) {
            return;
        }

        $notif = Notification::make()
            ->title($title)
            ->body($body);

        match ($status) {
            'success' => $notif->success(),
            'warning' => $notif->warning(),
            'danger' => $notif->danger(),
            default => $notif->info(),
        };

        $notif->sendToDatabase($recipients);

        self::sendFcmBatch($recipients->all(), $title, $body, $data);
    }

    /**
     * Kirim Push Notification FCM ke perangkat Android tanpa membuat duplikasi notifikasi database.
     */
    public static function sendFcmToUsers(iterable $users, string $title, string $body, array $data = []): void
    {
        $recipients = collect($users)->filter(fn ($u) => $u instanceof User)->unique('id')->values();
        if (\App\Models\PengaturanAplikasi::getPengaturan()->mode_simulasi) {
            $recipients = $recipients->filter(
                fn (User $u) => $u->hasAnyRole(['super_admin', 'superadmin'])
            )->values();
        }
        if ($recipients->isEmpty()) {
            return;
        }

        self::sendFcmBatch($recipients->all(), $title, $body, $data);
    }

    /**
     * Kirim Push Notification via FCM HTTP v1 API tanpa library eksternal.
     *
     * @param  array<int, User>  $recipients
     * @param  array<string, string>  $data
     */
    private static function sendFcmBatch(array $recipients, string $title, string $body, array $data = []): void
    {
        $credentialsPath = storage_path('app/firebase-credentials.json');
        if (! is_file($credentialsPath)) {
            return;
        }

        $tokens = array_values(array_unique(array_filter(array_map(fn (User $u) => $u->fcm_token, $recipients))));
        if ($tokens === []) {
            return;
        }

        $creds = json_decode((string) file_get_contents($credentialsPath), true);
        if (! is_array($creds) || empty($creds['project_id'])) {
            return;
        }

        $accessToken = self::getGoogleAccessToken($creds);
        if (! $accessToken) {
            return;
        }

        $endpoint = "https://fcm.googleapis.com/v1/projects/{$creds['project_id']}/messages:send";

        foreach ($tokens as $token) {
            try {
                $response = Http::withToken($accessToken)
                    ->timeout(5)
                    ->post($endpoint, [
                        'message' => [
                            'token' => $token,
                            'notification' => [
                                'title' => $title,
                                'body' => $body,
                            ],
                            'data' => array_map('strval', $data),
                            'android' => [
                                'priority' => 'high',
                                'notification' => [
                                    'channel_id' => 'sakti_high_priority',
                                    'sound' => 'default',
                                    'default_vibrate_timings' => true,
                                    'notification_priority' => 'PRIORITY_MAX',
                                    'visibility' => 'PUBLIC',
                                ],
                            ],
                        ],
                    ]);

                if (! $response->successful()) {
                    Log::warning('FCM send rejected', ['status' => $response->status(), 'body' => $response->body()]);
                }
            } catch (\Throwable $e) {
                Log::warning('FCM send failed: ' . $e->getMessage());
            }
        }
    }

    private static function getGoogleAccessToken(array $creds): ?string
    {
        return Cache::remember('fcm_google_access_token', now()->addMinutes(50), function () use ($creds): ?string {
            $clientEmail = $creds['client_email'] ?? '';
            $privateKey = $creds['private_key'] ?? '';
            if ($clientEmail === '' || $privateKey === '') {
                return null;
            }

            $now = time();
            $header = self::base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $claim = self::base64UrlEncode(json_encode([
                'iss' => $clientEmail,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ]));

            $signature = '';
            if (! openssl_sign("{$header}.{$claim}", $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
                return null;
            }

            $jwt = "{$header}.{$claim}." . self::base64UrlEncode($signature);

            $res = Http::asForm()->timeout(5)->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            return $res->successful() ? $res->json('access_token') : null;
        });
    }

    private static function base64UrlEncode(string $input): string
    {
        return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
    }
}
