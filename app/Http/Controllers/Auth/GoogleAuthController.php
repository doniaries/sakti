<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Mengarahkan pengguna ke halaman autentikasi Google OAuth.
     */
    public function redirectToGoogle(Request $request): RedirectResponse
    {
        if ($request->has('from')) {
            session(['google_auth_origin' => $request->query('from')]);
        }
        if ($request->filled('device_id')) {
            session(['google_auth_device_id' => (string) $request->query('device_id')]);
        }
        if ($request->filled('device_name')) {
            session(['google_auth_device_name' => (string) $request->query('device_name')]);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Menangani callback autentikasi dari Google.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        $origin = session()->pull('google_auth_origin', 'admin');
        $flutterDeviceId = (string) session()->pull('google_auth_device_id', '');
        $flutterDeviceName = (string) session()->pull('google_auth_device_name', '');
        $failRedirect = ($origin === 'mobile') ? route('mobile.login') : '/admin/login';

        $flutterErrorRedirect = function (string $message): RedirectResponse {
            return redirect()->away('saktimobile://auth/callback?error=' . urlencode($message));
        };

        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            Log::warning('Google OAuth callback failed: ' . $e->getMessage());

            if ($origin === 'flutter') {
                return $flutterErrorRedirect('Otorisasi dengan akun Google dibatalkan atau terjadi kesalahan jaringan.');
            }

            Notification::make()
                ->title('Gagal Masuk')
                ->body('Otorisasi dengan akun Google dibatalkan atau terjadi kesalahan jaringan. Silakan coba kembali.')
                ->danger()
                ->send();

            session()->flash('notify', [
                'type' => 'error',
                'title' => 'Gagal Masuk',
                'message' => 'Otorisasi dengan akun Google dibatalkan atau terjadi kesalahan jaringan. Silakan coba kembali.',
            ]);

            return redirect()->to($failRedirect);
        }

        $googleId = (string) $googleUser->getId();
        $googleEmail = strtolower((string) $googleUser->getEmail());

        if (empty($googleEmail)) {
            if ($origin === 'flutter') {
                return $flutterErrorRedirect('Akun Google Anda tidak membagikan informasi alamat email.');
            }

            Notification::make()
                ->title('Email Tidak Ditemukan')
                ->body('Akun Google Anda tidak membagikan informasi alamat email.')
                ->danger()
                ->send();

            session()->flash('notify', [
                'type' => 'error',
                'title' => 'Email Tidak Ditemukan',
                'message' => 'Akun Google Anda tidak membagikan informasi alamat email.',
            ]);

            return redirect()->to($failRedirect);
        }

        // Cari user berdasarkan email utama atau email resmi (@sijunjung.go.id)
        /** @var User|null $user */
        $user = User::findByLoginEmail($googleEmail);

        if (! $user) {
            $linkedWithDifferentEmail = User::query()
                ->where('google_id', $googleId)
                ->exists();

            $message = $linkedWithDifferentEmail
                ? "Email Google ({$googleEmail}) berbeda dengan email akun yang terdaftar di SAKTI. Silakan masuk menggunakan akun Google dengan email yang sesuai atau hubungi Administrator."
                : "Email Google ({$googleEmail}) belum terdaftar pada data pegawai SAKTI. Silakan login menggunakan email akun Anda atau hubungi Administrator.";

            if ($origin === 'flutter') {
                return $flutterErrorRedirect($message);
            }

            Notification::make()
                ->title($linkedWithDifferentEmail ? 'Email Tidak Sesuai Akun' : 'Akun Tidak Ditemukan')
                ->body($message)
                ->warning()
                ->persistent()
                ->send();

            session()->flash('notify', [
                'type' => 'warning',
                'title' => $linkedWithDifferentEmail ? 'Email Tidak Sesuai Akun' : 'Akun Tidak Terdaftar',
                'message' => $message,
            ]);

            return redirect()->to($failRedirect);
        }

        // Cek status keaktifan pegawai
        if (! $user->aktif) {
            if ($origin === 'flutter') {
                return $flutterErrorRedirect('Akun pegawai Anda sedang dinonaktifkan. Silakan hubungi Administrator Kepegawaian.');
            }

            Notification::make()
                ->title('Akun Nonaktif')
                ->body('Akun pegawai Anda sedang dinonaktifkan. Silakan hubungi Administrator Kepegawaian.')
                ->danger()
                ->persistent()
                ->send();

            session()->flash('notify', [
                'type' => 'error',
                'title' => 'Akun Nonaktif',
                'message' => 'Akun pegawai Anda sedang dinonaktifkan. Silakan hubungi Administrator Kepegawaian.',
            ]);

            return redirect()->to($failRedirect);
        }

        // Tautkan google_id otomatis jika belum terisi
        if ($user->google_id !== $googleId) {
            $user->update(['google_id' => $googleId]);
        }

        if ($origin === 'flutter') {
            if ($flutterDeviceId !== '') {
                $deviceCheck = $user->bindOrVerifyDevice(
                    $flutterDeviceId,
                    $flutterDeviceName !== '' ? $flutterDeviceName : null
                );
                if (! $deviceCheck['ok']) {
                    return $flutterErrorRedirect((string) ($deviceCheck['message'] ?? 'Perangkat tidak cocok.'));
                }
            }

            $user->tokens()->where('name', 'mobile-app')->delete();
            $token = $user->createToken('mobile-app', ['*'], now()->addDays(30))->plainTextToken;
            return redirect()->away('saktimobile://auth/callback?token=' . urlencode($token));
        }

        // Login ke session aplikasi
        Auth::login($user, remember: true);
        $request->session()->regenerate();

        Notification::make()
            ->title('Berhasil Masuk')
            ->body("Selamat datang kembali, {$user->name}!")
            ->success()
            ->send();

        $targetRoute = ($origin === 'mobile' || ! $user->hasAnyRole(['super_admin', 'admin']))
            ? route('mobile.dashboard')
            : '/admin';

        return redirect()->intended($targetRoute);
    }
}
