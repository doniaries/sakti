<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class GoogleDriveEvidenceService
{
    /**
     * Unggah foto bukti LHK ke Google Drive milik pegawai, buat folder
     * sakti-lhk-evidence-{tanggal}, buka akses publiknya, dan kembalikan URL Drive.
     */
    public static function uploadEvidencePhoto(User $user, UploadedFile $file, string $tanggal): string
    {
        $accessToken = self::getValidAccessToken($user);
        if (! $accessToken) {
            throw new RuntimeException('Akun Google Drive belum terhubung atau sesi akses telah berakhir. Silakan hubungkan ulang Google Drive Anda.');
        }

        $folderName = 'sakti-lhk-evidence-' . $tanggal;
        $folderId = self::findOrCreatePublicFolder($accessToken, $folderName);

        $ext = $file->getClientOriginalExtension() ?: 'jpg';
        $fileName = 'lhk-' . $tanggal . '-' . now()->format('His') . '-' . substr(md5((string) $user->id . microtime()), 0, 6) . '.' . $ext;
        $mimeType = $file->getMimeType() ?: 'image/jpeg';
        $binary = (string) file_get_contents($file->getRealPath());

        $boundary = 'sakti_drive_' . bin2hex(random_bytes(8));
        $metadata = json_encode([
            'name' => $fileName,
            'parents' => [$folderId],
        ]);

        $multipartBody = "--{$boundary}\r\n"
            . "Content-Type: application/json; charset=UTF-8\r\n\r\n"
            . "{$metadata}\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: {$mimeType}\r\n\r\n"
            . "{$binary}\r\n"
            . "--{$boundary}--";

        $uploadRes = Http::withToken($accessToken)
            ->timeout(30)
            ->withBody($multipartBody, "multipart/related; boundary={$boundary}")
            ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id,webViewLink');

        if (! $uploadRes->successful()) {
            Log::warning('Google Drive upload failed', ['status' => $uploadRes->status(), 'body' => $uploadRes->body()]);
            throw new RuntimeException('Gagal mengunggah foto ke Google Drive. Pastikan kuota Drive cukup atau hubungkan ulang akun Google Anda.');
        }

        $fileId = (string) $uploadRes->json('id');
        if ($fileId === '') {
            throw new RuntimeException('ID file Google Drive tidak ditemukan setelah unggah.');
        }

        self::makeAnyoneReader($accessToken, $fileId);

        return "https://drive.google.com/file/d/{$fileId}/view?usp=sharing";
    }

    /**
     * Dapatkan access token valid; refresh otomatis bila sudah kedaluwarsa.
     */
    private static function getValidAccessToken(User $user): ?string
    {
        if (
            filled($user->google_drive_token)
            && $user->google_drive_token_expires_at
            && $user->google_drive_token_expires_at->isFuture()
        ) {
            return (string) $user->google_drive_token;
        }

        if (blank($user->google_drive_refresh_token)) {
            return filled($user->google_drive_token) ? (string) $user->google_drive_token : null;
        }

        $res = Http::asForm()
            ->timeout(10)
            ->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'refresh_token' => (string) $user->google_drive_refresh_token,
                'grant_type' => 'refresh_token',
            ]);

        if (! $res->successful() || blank($res->json('access_token'))) {
            Log::warning('Google Drive token refresh failed', ['user_id' => $user->id, 'body' => $res->body()]);

            return null;
        }

        $newToken = (string) $res->json('access_token');
        $expiresIn = max(60, (int) $res->json('expires_in', 3500));

        $user->forceFill([
            'google_drive_token' => $newToken,
            'google_drive_token_expires_at' => now()->addSeconds($expiresIn - 60),
        ])->save();

        return $newToken;
    }

    /**
     * Cari folder di Google Drive user; jika belum ada, buat baru dan buka akses publiknya.
     */
    private static function findOrCreatePublicFolder(string $accessToken, string $folderName): string
    {
        $escapedName = str_replace("'", "\\'", $folderName);
        $query = "name='{$escapedName}' and mimeType='application/vnd.google-apps.folder' and trashed=false";

        $searchRes = Http::withToken($accessToken)
            ->timeout(10)
            ->get('https://www.googleapis.com/drive/v3/files', [
                'q' => $query,
                'fields' => 'files(id,name)',
                'pageSize' => 1,
            ]);

        $existingId = $searchRes->successful() ? (string) ($searchRes->json('files.0.id') ?? '') : '';
        if ($existingId !== '') {
            return $existingId;
        }

        $createRes = Http::withToken($accessToken)
            ->timeout(10)
            ->post('https://www.googleapis.com/drive/v3/files?fields=id', [
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
            ]);

        if (! $createRes->successful() || blank($createRes->json('id'))) {
            Log::warning('Google Drive folder creation failed', ['body' => $createRes->body()]);
            throw new RuntimeException("Gagal membuat folder {$folderName} di Google Drive.");
        }

        $folderId = (string) $createRes->json('id');
        self::makeAnyoneReader($accessToken, $folderId);

        return $folderId;
    }

    /**
     * Buka akses berbagi file/folder menjadi 'Siapa saja yang memiliki link' (reader).
     */
    private static function makeAnyoneReader(string $accessToken, string $fileId): void
    {
        $res = Http::withToken($accessToken)
            ->timeout(10)
            ->post("https://www.googleapis.com/drive/v3/files/{$fileId}/permissions", [
                'role' => 'reader',
                'type' => 'anyone',
            ]);

        if (! $res->successful()) {
            Log::warning('Google Drive permission update failed', ['file_id' => $fileId, 'body' => $res->body()]);
        }
    }
}
