<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum SumberDana: string implements HasLabel, HasColor
{
    case DAU = 'Dana Alokasi Umum (DAU)';
    case DAK = 'Dana Alokasi Khusus (DAK)';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DAU => 'Dana Alokasi Umum (DAU)',
            self::DAK => 'Dana Alokasi Khusus (DAK)',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::DAU => 'info',
            self::DAK => 'success',
        };
    }
}
