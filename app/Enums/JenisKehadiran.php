<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JenisKehadiran: string implements HasLabel, HasColor
{
    case WFO = 'wfo';
    case WFH = 'wfh';
    case DINAS_LUAR = 'dinas_luar';

    public function getLabel(): string
    {
        return match ($this) {
            self::WFO => 'WFO',
            self::WFH => 'WFH',
            self::DINAS_LUAR => 'DINAS LUAR',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::WFO => 'success',
            self::WFH => 'warning',
            self::DINAS_LUAR => 'info',
        };
    }
}
