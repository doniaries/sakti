<?php

declare(strict_types=1);

namespace App\Filament\AvatarProviders;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class SaktiAvatarProvider implements AvatarProvider
{
    /**
     * Menghasilkan URL Avatar default menggunakan inisial nama dan warna HEX valid (#0284c7).
     * Mencegah bug Filament v4/v5 bawaan yang mengirim kode warna CSS oklch(...) ke ui-avatars.com
     * yang menyebabkan respon HTTP 400 Bad Request (avatar gambar rusak).
     */
    public function get(Model | Authenticatable $record): string
    {
        $rawName = (string) Filament::getNameForDefaultAvatar($record);
        $cleanName = (string) preg_replace('/[^a-zA-Z0-9\s]/', '', $rawName);

        $initials = collect(explode(' ', trim($cleanName)))
            ->filter()
            ->take(2)
            ->map(fn(string $segment): string => strtoupper(mb_substr($segment, 0, 1)))
            ->join('');

        return 'https://ui-avatars.com/api/?name=' . urlencode($initials ?: 'U') . '&background=0284c7&color=ffffff&size=160';
    }
}
