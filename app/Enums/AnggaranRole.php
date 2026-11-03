<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AnggaranRole: string implements HasLabel, HasColor
{
    case PA = 'PA';
    case PPK = 'PPK';
    case PPTK = 'PPTK';
    case BENDAHARA = 'BENDAHARA';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PA => 'PA (Pengguna Anggaran)',
            self::PPK => 'PPK (Pejabat Pembuat Komitmen)',
            self::PPTK => 'PPTK (Pejabat Pelaksana Teknis Kegiatan)',
            self::BENDAHARA => 'Bendahara Pengeluaran',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PA => 'danger',
            self::PPK => 'warning',
            self::PPTK => 'info',
            self::BENDAHARA => 'success',
        };
    }
}

