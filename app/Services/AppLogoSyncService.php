<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AppLogoSyncService
{
    /**
     * Sinkronkan logo aplikasi yang diunggah ke favicon web, ikon PWA, dan ikon aplikasi Android (jika tersedia).
     */
    public static function syncFromStoragePath(?string $storagePath): void
    {
        if (blank($storagePath) || ! extension_loaded('gd')) {
            return;
        }

        $sourceFile = Storage::disk('public')->path($storagePath);
        if (! is_file($sourceFile)) {
            return;
        }

        $raw = @file_get_contents($sourceFile);
        if ($raw === false) {
            return;
        }

        $sourceImage = @imagecreatefromstring($raw);
        if (! $sourceImage) {
            return;
        }

        try {
            $targets = [
                public_path('favicon.png') => 64,
                public_path('icons/icon-192x192.png') => 192,
                public_path('icons/icon-512x512.png') => 512,
                public_path('images/logo-instansi.png') => 512,
            ];

            $androidResDir = base_path('../sakti_mobile/android/app/src/main/res');
            if (is_dir($androidResDir)) {
                $targets[$androidResDir . '/mipmap-mdpi/ic_launcher.png'] = 48;
                $targets[$androidResDir . '/mipmap-hdpi/ic_launcher.png'] = 72;
                $targets[$androidResDir . '/mipmap-xhdpi/ic_launcher.png'] = 96;
                $targets[$androidResDir . '/mipmap-xxhdpi/ic_launcher.png'] = 144;
                $targets[$androidResDir . '/mipmap-xxxhdpi/ic_launcher.png'] = 192;
            }

            $flutterAsset = base_path('../sakti_mobile/assets/images/logo-sakti.png');
            if (is_dir(dirname($flutterAsset))) {
                $targets[$flutterAsset] = 512;
            }

            foreach ($targets as $targetPath => $size) {
                self::writeResizedPng($sourceImage, $targetPath, $size, false);
            }

            $pwaTargets = [
                public_path('icons/pwa-192x192.png') => 192,
                public_path('icons/pwa-512x512.png') => 512,
            ];

            foreach ($pwaTargets as $targetPath => $size) {
                self::writeResizedPng($sourceImage, $targetPath, $size, true);
            }
        } catch (\Throwable $e) {
            Log::warning('AppLogoSyncService error: ' . $e->getMessage());
        } finally {
            imagedestroy($sourceImage);
        }
    }

    /**
     * Generate ikon PWA ber-badge WEB dari ikon master yang sudah ada di public/icons/icon-512x512.png.
     */
    public static function syncPwaBadgeFromPublicIcon(): void
    {
        if (! extension_loaded('gd')) {
            return;
        }

        $sourceFile = public_path('icons/icon-512x512.png');
        if (! is_file($sourceFile)) {
            return;
        }

        $raw = @file_get_contents($sourceFile);
        if ($raw === false) {
            return;
        }

        $sourceImage = @imagecreatefromstring($raw);
        if (! $sourceImage) {
            return;
        }

        try {
            foreach ([public_path('icons/pwa-192x192.png') => 192, public_path('icons/pwa-512x512.png') => 512] as $targetPath => $size) {
                self::writeResizedPng($sourceImage, $targetPath, $size, true);
            }
        } finally {
            imagedestroy($sourceImage);
        }
    }

    private static function writeResizedPng(\GdImage $source, string $destination, int $size, bool $withWebBadge = false): void
    {
        $dir = dirname($destination);
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $canvas = imagecreatetruecolor($size, $size);
        if (! $canvas) {
            return;
        }

        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $size, $size, $transparent);
        imagealphablending($canvas, true);

        $srcW = imagesx($source);
        $srcH = imagesy($source);
        $scale = min($size / max(1, $srcW), $size / max(1, $srcH));
        $dstW = max(1, (int) round($srcW * $scale));
        $dstH = max(1, (int) round($srcH * $scale));
        $dstX = (int) floor(($size - $dstW) / 2);
        $dstY = (int) floor(($size - $dstH) / 2);

        imagecopyresampled($canvas, $source, $dstX, $dstY, 0, 0, $dstW, $dstH, $srcW, $srcH);

        if ($withWebBadge) {
            self::drawWebBadge($canvas, $size);
        }

        imagepng($canvas, $destination, 6);
        imagedestroy($canvas);
    }

    private static function drawWebBadge(\GdImage $canvas, int $size): void
    {
        $badgeW = (int) round($size * 0.44);
        $badgeH = (int) round($size * 0.21);
        $margin = (int) round($size * 0.04);
        $x1 = $size - $badgeW - $margin;
        $y1 = $size - $badgeH - $margin;
        $x2 = $size - $margin;
        $y2 = $size - $margin;
        $radius = (int) round($badgeH / 2);

        $white = imagecolorallocate($canvas, 255, 255, 255);
        $blue = imagecolorallocate($canvas, 2, 132, 199); // #0284C7

        // White border pill
        self::fillRoundedRect($canvas, $x1 - 2, $y1 - 2, $x2 + 2, $y2 + 2, $radius + 2, $white);
        // Blue pill background
        self::fillRoundedRect($canvas, $x1, $y1, $x2, $y2, $radius, $blue);

        // Crisp "WEB" label centered inside pill using GD built-in font 5 scaled cleanly
        $font = 5;
        $text = 'WEB';
        $tw = imagefontwidth($font) * strlen($text);
        $th = imagefontheight($font);
        $scale = max(1, (int) round(($badgeH * 0.58) / max(1, $th)));

        $tmp = imagecreatetruecolor($tw, $th);
        if ($tmp) {
            imagealphablending($tmp, false);
            imagesavealpha($tmp, true);
            $trans = imagecolorallocatealpha($tmp, 0, 0, 0, 127);
            imagefilledrectangle($tmp, 0, 0, $tw, $th, $trans);
            $tmpWhite = imagecolorallocate($tmp, 255, 255, 255);
            imagestring($tmp, $font, 0, 0, $text, $tmpWhite);

            $targetTw = $tw * $scale;
            $targetTh = $th * $scale;
            $tx = (int) round($x1 + ($badgeW - $targetTw) / 2);
            $ty = (int) round($y1 + ($badgeH - $targetTh) / 2);
            imagecopyresized($canvas, $tmp, $tx, $ty, 0, 0, $targetTw, $targetTh, $tw, $th);
            imagedestroy($tmp);
        }
    }

    private static function fillRoundedRect(\GdImage $canvas, int $x1, int $y1, int $x2, int $y2, int $r, int $color): void
    {
        imagefilledrectangle($canvas, $x1 + $r, $y1, $x2 - $r, $y2, $color);
        imagefilledrectangle($canvas, $x1, $y1 + $r, $x2, $y2 - $r, $color);
        imagefilledellipse($canvas, $x1 + $r, $y1 + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($canvas, $x2 - $r, $y1 + $r, $r * 2, $r * 2, $color);
        imagefilledellipse($canvas, $x1 + $r, $y2 - $r, $r * 2, $r * 2, $color);
        imagefilledellipse($canvas, $x2 - $r, $y2 - $r, $r * 2, $r * 2, $color);
    }
}
