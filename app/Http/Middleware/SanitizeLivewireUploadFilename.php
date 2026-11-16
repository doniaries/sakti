<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class SanitizeLivewireUploadFilename
{
    /**
     * Handle an incoming request.
     * Sanitizes uploaded file original names so that Livewire's base64-embedded
     * temporary filenames never exceed the 255-character limit on Windows NTFS.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->files->count() > 0) {
            foreach ($request->files->all() as $key => $file) {
                $request->files->set($key, $this->sanitizeFiles($file));
            }
        }

        return $next($request);
    }

    protected function sanitizeFiles(mixed $files): mixed
    {
        if (is_array($files)) {
            return array_map(fn ($f) => $this->sanitizeFiles($f), $files);
        }

        return $this->sanitizeFile($files);
    }

    protected function sanitizeFile(mixed $file): mixed
    {
        if (! ($file instanceof UploadedFile) || ! $file->isValid()) {
            return $file;
        }

        $originalName = $file->getClientOriginalName();

        // Jika nama berkas asli panjang (melebihi 50 karakter), potong menjadi aman
        // Livewire membuat nama berkas sementara dengan pola:
        // [30-char-hash]-meta[base64(original_name)]-.[ext]
        // Jika originalName > 120 karakter, base64-nya menjadi 160+ karakter,
        // sehingga total nama file > 255 karakter dan gagal disimpan di filesystem Windows NTFS.
        if (mb_strlen($originalName) > 50) {
            $ext = $file->getClientOriginalExtension() ?: pathinfo($originalName, PATHINFO_EXTENSION) ?: 'tmp';
            $baseName = pathinfo($originalName, PATHINFO_FILENAME);
            $cleanBase = preg_replace('/[^\p{L}\p{N}_\-\.]/u', '_', $baseName) ?? 'file';
            $shortName = mb_substr($cleanBase, 0, 40) . '.' . $ext;

            return new UploadedFile(
                $file->getPathname(),
                $shortName,
                $file->getClientMimeType(),
                $file->getError(),
                true
            );
        }

        return $file;
    }
}

