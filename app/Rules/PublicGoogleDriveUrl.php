<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class PublicGoogleDriveUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        $url = trim((string) $value);
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($scheme !== 'https' || ! in_array($host, ['drive.google.com', 'docs.google.com'], true)) {
            $fail('Link bukti wajib menggunakan tautan HTTPS Google Drive (drive.google.com atau docs.google.com).');

            return;
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; SAKTI-Disparpora/1.0)',
                ])
                ->withoutRedirecting()
                ->get($url);

            $status = $response->status();
            $location = strtolower((string) $response->header('Location'));

            if (in_array($status, [301, 302, 303, 307, 308], true)) {
                if (
                    str_contains($location, 'accounts.google.com')
                    || str_contains($location, 'servicelogin')
                    || str_contains($location, 'signin')
                ) {
                    $fail("Link Google Drive masih bersifat Privat. Ubah pengaturan berbagi menjadi 'Siapa saja yang memiliki link' (Pelihat) agar bukti dapat diperiksa.");

                    return;
                }
            }

            if (in_array($status, [401, 403], true)) {
                $fail("Akses Google Drive masih dibatasi (Privat). Ubah ke 'Siapa saja yang memiliki link' (Pelihat).");

                return;
            }

            if ($status === 404) {
                $fail('File atau folder Google Drive tidak ditemukan (404). Pastikan tautan benar dan belum dihapus.');

                return;
            }
        } catch (ConnectionException) {
            // Jika server sedang offline/timeout, tetap loloskan selama domain Google Drive valid.
        }
    }
}
