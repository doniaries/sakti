<?php

declare(strict_types=1);

namespace App\Services\Mobile;

use App\Models\RiwayatGolongan;
use App\Models\RiwayatJabatan;
use App\Models\RiwayatPendidikan;
use Smalot\PdfParser\Parser;

class ProfilService
{
    public static function normalizeScientificNumber(?string $value): string
    {
        if (blank($value)) {
            return '';
        }

        $str = (string) $value;

        return str_contains(strtoupper($str), 'E')
            ? number_format((float) $str, 0, '', '')
            : $str;
    }

    /**
     * @return array{tahun: int|null, bulan: int|null}
     */
    public function extractMasaKerjaFromSk(string $fullPath): array
    {
        try {
            $text = (new Parser())->parseFile($fullPath)->getText();
            if (preg_match('/Masa\s+Kerja\s+Golongan\s*:?\s*(\d+)\s*Tahun\s*(\d+)\s*Bulan/i', $text, $m)) {
                return ['tahun' => (int) $m[1], 'bulan' => (int) $m[2]];
            }
        } catch (\Throwable) {
            // Abaikan jika PDF hasil scan (tidak memiliki layer teks)
        }

        return ['tahun' => null, 'bulan' => null];
    }

    public function deleteRiwayat(string $modelClass, int $userId, int $id): void
    {
        $modelClass::where('user_id', $userId)->where('id', $id)->delete();
    }
}
