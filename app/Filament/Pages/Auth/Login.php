<?php

namespace App\Filament\Pages\Auth;

use App\Models\PengaturanAplikasi;
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
            ?: ($pengaturan?->deskripsi ?: 'Sistem Administrasi, Kinerja, dan Tata Informasi');
        $namaDinas = $pengaturan?->nama_dinas ?: 'DINAS PARIWISATA, PEMUDA DAN OLAH RAGA';

        $logoUrl = null;
        if (!empty($pengaturan?->logo)) {
            $logoUrl = asset('storage/' . $pengaturan->logo);
        } elseif (file_exists(public_path('images/kabupaten-sijunjung.png'))) {
            $logoUrl = asset('images/kabupaten-sijunjung.png');
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
}

