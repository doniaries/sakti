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

        if ($scheme !== 'https' || $host !== 'drive.google.com') {
            $fail('Link bukti wajib berupa tautan file gambar dari Google Drive (https://drive.google.com/...).');

            return;
        }

        if (str_contains($url, '/folders/')) {
            $fail('Link bukti harus berupa file gambar langsung, bukan tautan folder Google Drive.');

            return;
        }

        $fileId = $this->extractFileId($url);
        if ($fileId === null) {
            $fail('Format tautan file Google Drive tidak valid. Salin link langsung dari file gambar di Google Drive.');

            return;
        }

        try {
            $response = Http::timeout(6)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; SAKTI-Disparpora/1.0)',
                    'Range' => 'bytes=0-31',
                ])
                ->get("https://drive.google.com/uc?export=view&id={$fileId}");

            $status = $response->status();
            $effectiveUri = strtolower((string) $response->effectiveUri());
            $contentType = strtolower((string) $response->header('Content-Type'));

            if (
                str_contains($effectiveUri, 'accounts.google.com')
                || str_contains($effectiveUri, 'servicelogin')
                || str_contains($effectiveUri, 'signin')
                || in_array($status, [401, 403], true)
            ) {
                $fail("Link Google Drive masih bersifat Privat. Ubah akses berbagi menjadi 'Siapa saja yang memiliki link' (Pelihat) agar gambar dapat diperiksa.");

                return;
            }

            if ($status === 404) {
                $fail('File gambar Google Drive tidak ditemukan (404). Pastikan tautan benar dan belum dihapus.');

                return;
            }

            if (! str_starts_with($contentType, 'image/')) {
                $fail('File bukti Google Drive harus berupa gambar (JPG, PNG, atau WEBP). File non-gambar (PDF, dokumen, atau video) tidak diterima.');

                return;
            }
        } catch (ConnectionException) {
            // Jika server sedang offline/timeout, tetap loloskan selama format file ID Google Drive valid.
        }
    }

    private function extractFileId(string $url): ?string
    {
        if (preg_match('~drive\.google\.com/file/d/([a-zA-Z0-9_-]+)~', $url, $m)) {
            return $m[1];
        }

        if (preg_match('~[?&]id=([a-zA-Z0-9_-]+)~', $url, $m)) {
            return $m[1];
        }

        return null;
    }
}
