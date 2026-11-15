<?php

declare(strict_types=1);

namespace App\Support;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeHelper
{
    /**
     * Generate QR Code PNG (base64 data URI) dengan logo Geopark Silokek di tengah.
     */
    public static function generateWithGeoparkLogo(string $url): ?string
    {
        try {
            $options = new QROptions([
                'version' => QRCode::VERSION_AUTO,
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel' => QRCode::ECC_H,
                'scale' => 6,
                'imageBase64' => false,
                'imageTransparent' => false,
            ]);

            $qrBinary = (new QRCode($options))->render($url);
            $logoPath = public_path('images/geopark-silokek.png');

            if (! extension_loaded('gd') || ! file_exists($logoPath)) {
                return 'data:image/png;base64,' . base64_encode($qrBinary);
            }

            $qrImage = @imagecreatefromstring($qrBinary);
            $logoImage = @imagecreatefrompng($logoPath);

            if (! $qrImage || ! $logoImage) {
                return 'data:image/png;base64,' . base64_encode($qrBinary);
            }

            $qrWidth = imagesx($qrImage);
            $qrHeight = imagesy($qrImage);
            $logoWidth = imagesx($logoImage);
            $logoHeight = imagesy($logoImage);

            // Ukuran area logo sekitar 22% dari lebar QR agar tetap aman terbaca oleh ECC_H
            $targetSize = (int) round($qrWidth * 0.22);
            $padding = (int) max(4, round($targetSize * 0.14));
            $boxSize = $targetSize + ($padding * 2);

            $boxX = (int) round(($qrWidth - $boxSize) / 2);
            $boxY = (int) round(($qrHeight - $boxSize) / 2);
            $logoX = (int) round(($qrWidth - $targetSize) / 2);
            $logoY = (int) round(($qrHeight - $targetSize) / 2);

            $white = imagecolorallocate($qrImage, 255, 255, 255);
            imagefilledrectangle($qrImage, $boxX, $boxY, $boxX + $boxSize, $boxY + $boxSize, $white);

            imagealphablending($qrImage, true);
            imagecopyresampled(
                $qrImage,
                $logoImage,
                $logoX,
                $logoY,
                0,
                0,
                $targetSize,
                $targetSize,
                $logoWidth,
                $logoHeight
            );

            ob_start();
            imagepng($qrImage);
            $finalPng = (string) ob_get_clean();

            imagedestroy($qrImage);
            imagedestroy($logoImage);

            return 'data:image/png;base64,' . base64_encode($finalPng);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }
}
