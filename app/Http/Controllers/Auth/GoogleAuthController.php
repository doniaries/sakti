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

        return Socialite::driver('google')->redirect();
    }

    /**
     * Menangani callback autentikasi dari Google.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        $origin = session()->pull('google_auth_origin', 'admin');
        $failRedirect = ($origin === 'mobile') ? route('mobile.login') : '/admin/login';

        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            Log::warning('Google OAuth callback failed: ' . $e->getMessage());

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

        // Cari user berdasarkan google_id atau email user
        /** @var User|null $user */
        $user = User::query()
            ->where('google_id', $googleId)
            ->orWhereRaw('LOWER(email) = ?', [$googleEmail])
            ->first();

        // Jika email belum terdaftar di database SAKTI
        if (! $user) {
            Notification::make()
                ->title('Akun Tidak Ditemukan')
                ->body("Email Google ({$googleEmail}) belum terdaftar pada data pegawai SAKTI. Silakan login menggunakan akun kedinasan Anda atau hubungi Administrator.")
                ->warning()
                ->persistent()
                ->send();

            session()->flash('notify', [
                'type' => 'warning',
                'title' => 'Akun Tidak Terdaftar',
                'message' => "Email Google ({$googleEmail}) belum terdaftar pada data pegawai SAKTI. Silakan login menggunakan akun kedinasan Anda atau hubungi Administrator.",
            ]);

            return redirect()->to($failRedirect);
        }

        // Cek status keaktifan pegawai
        if (! $user->aktif) {
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
