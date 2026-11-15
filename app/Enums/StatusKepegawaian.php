<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusKepegawaian: string implements HasLabel, HasColor
{
    case PNS = 'pns';
    case PPPK = 'pppk';
    case KONTRAK = 'kontrak';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PNS => 'PNS',
            self::PPPK => 'PPPK (P3K)',
            self::KONTRAK => 'Kontrak / Non-ASN',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PNS => 'primary',
            self::PPPK => 'info',
            self::KONTRAK => 'warning',
        };
    }

    /**
     * Deteksi otomatis status kepegawaian dari format NIP BKN.
     */
    public static function detectFromNip(?string $nip): self
    {
        $digits = preg_replace('/\D+/', '', (string) $nip);

        if ($digits === '') {
            return self::KONTRAK;
        }

        // Format NIP PPPK BKN memiliki kode '21' pada digit ke-13 dan ke-14 (YYYYMMDD YYYY21 G NNN)
        if (strlen($digits) >= 14 && substr($digits, 12, 2) === '21') {
            return self::PPPK;
        }

        return self::PNS;
    }
}
