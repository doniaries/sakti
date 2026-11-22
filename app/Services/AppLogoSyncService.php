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
                self::writeResizedPng($sourceImage, $targetPath, $size);
            }
        } catch (\Throwable $e) {
            Log::warning('AppLogoSyncService error: ' . $e->getMessage());
        } finally {
            imagedestroy($sourceImage);
        }
    }

    private static function writeResizedPng(\GdImage $source, string $destination, int $size): void
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
        imagepng($canvas, $destination, 6);
        imagedestroy($canvas);
    }
}
