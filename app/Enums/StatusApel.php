<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StatusApel: string implements HasLabel, HasColor
{
    case IKUT = 'ikut apel';
    case TIDAK_IKUT = 'tidak ikut apel';

    public function getLabel(): string
    {
        return match ($this) {
            self::IKUT => 'Ikut Apel',
            self::TIDAK_IKUT => 'Tidak Ikut Apel',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::IKUT => 'success',
            self::TIDAK_IKUT => 'danger',
        };
    }
}
