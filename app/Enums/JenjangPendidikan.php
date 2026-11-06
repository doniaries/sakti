<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JenjangPendidikan: string implements HasLabel, HasColor
{
    case SD = 'SD';
    case SLTP = 'SLTP';
    case SLTA = 'SLTA';
    case D1 = 'D1';
    case D2 = 'D2';
    case D3 = 'D3';
    case D4 = 'D4';
    case S1 = 'S1';
    case S2 = 'S2';
    case S3 = 'S3';

    public function getLabel(): string
    {
        return match ($this) {
            self::SD => 'SD (Sekolah Dasar)',
            self::SLTP => 'SLTP / SMP / MTs',
            self::SLTA => 'SLTA / SMA / SMK / MA',
            self::D1 => 'D1 (Diploma I)',
            self::D2 => 'D2 (Diploma II)',
            self::D3 => 'D3 (Diploma III)',
            self::D4 => 'D4 / Sarjana Terapan',
            self::S1 => 'S1 (Sarjana)',
            self::S2 => 'S2 (Magister)',
            self::S3 => 'S3 (Doktoral)',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::S3, self::S2 => 'primary',
            self::S1, self::D4 => 'success',
            self::D3, self::D2, self::D1 => 'info',
            self::SLTA => 'warning',
            default => 'gray',
        };
    }
}

