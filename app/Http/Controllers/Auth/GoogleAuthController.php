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
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Menangani callback autentikasi dari Google.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
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

            return redirect()->to('/admin/login');
        }

        $googleId = (string) $googleUser->getId();
        $googleEmail = strtolower((string) $googleUser->getEmail());

        if (empty($googleEmail)) {
            Notification::make()
                ->title('Email Tidak Ditemukan')
                ->body('Akun Google Anda tidak membagikan informasi alamat email.')
                ->danger()
                ->send();

            return redirect()->to('/admin/login');
        }

        // Cari user berdasarkan google_id, email kedinasan, atau email pribadi
        /** @var User|null $user */
        $user = User::query()
            ->where('google_id', $googleId)
            ->orWhereRaw('LOWER(email) = ?', [$googleEmail])
            ->orWhereRaw('LOWER(email_pribadi) = ?', [$googleEmail])
            ->first();

        // Jika email belum terdaftar di database SAKTI
        if (! $user) {
            Notification::make()
                ->title('Akun Tidak Ditemukan')
                ->body("Email Google ({$googleEmail}) belum terdaftar pada data pegawai SAKTI. Silakan login menggunakan akun kedinasan Anda atau hubungi Administrator.")
                ->warning()
                ->persistent()
                ->send();

            return redirect()->to('/admin/login');
        }

        // Cek status keaktifan pegawai
        if (! $user->aktif) {
            Notification::make()
                ->title('Akun Nonaktif')
                ->body('Akun pegawai Anda sedang dinonaktifkan. Silakan hubungi Administrator Kepegawaian.')
                ->danger()
                ->persistent()
                ->send();

            return redirect()->to('/admin/login');
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

        return redirect()->intended('/admin');
    }
}

