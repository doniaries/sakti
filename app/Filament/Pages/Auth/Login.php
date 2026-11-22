<?php

namespace App\Filament\Pages\Auth;

use App\Models\PengaturanAplikasi;
use App\Models\User;
use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class Login extends BaseLogin
{
    public function hasLogo(): bool
    {
        return false;
    }

    public function getHeading(): string | Htmlable
    {
        if (filled($this->userUndertakingMultiFactorAuthentication)) {
            return __('filament-panels::auth/pages/login.multi_factor.heading');
        }

        /** @var PengaturanAplikasi|null $pengaturan */
        $pengaturan = PengaturanAplikasi::query()->first();

        $namaAplikasi = $pengaturan?->nama_aplikasi ?: 'SAKTI';
        $kepanjangan = $pengaturan?->sub_nama 
            ?: ($pengaturan?->deskripsi ?: 'Sistem Administrasi Kinerja Terintegrasi');
        $namaDinas = $pengaturan?->nama_dinas ?: 'DINAS PARIWISATA, PEMUDA DAN OLAH RAGA';

        $logoUrl = asset('images/logo-instansi.png');

        if (!empty($pengaturan?->logo)) {
            $publicPath = storage_path('app/public/' . $pengaturan->logo);
            $privatePath = storage_path('app/private/' . $pengaturan->logo);
            $publicStorageLink = public_path('storage');

            // Gunakan custom logo jika file fisiknya ada
            if (file_exists($publicPath) || file_exists($privatePath)) {
                if (is_link($publicStorageLink) || is_dir($publicStorageLink)) {
                    $logoUrl = asset('storage/' . $pengaturan->logo);
                } else {
                    $logoUrl = url('/media-file/' . $pengaturan->logo);
                }
            }
        }

        return new HtmlString(
            view('filament.pages.auth.login-heading', [
                'namaAplikasi' => $namaAplikasi,
                'kepanjangan' => $kepanjangan,
                'namaDinas' => $namaDinas,
                'logoUrl' => $logoUrl,
                'defaultHeading' => __('filament-panels::auth/pages/login.heading'),
            ])->render()
        );
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $inputEmail = strtolower(trim((string) ($data['email'] ?? '')));
        $user = User::findByLoginEmail($inputEmail);

        return [
            'email' => $user?->email ?? $inputEmail,
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw \Illuminate\Validation\ValidationException::withMessages([
            'data.email' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }
}

