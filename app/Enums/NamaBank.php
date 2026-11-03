<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum NamaBank: string implements HasLabel
{
    case BANK_NAGARI = 'Bank Nagari (BPD Sumbar)';
    case BRI = 'Bank Rakyat Indonesia (BRI)';
    case BNI = 'Bank Negara Indonesia (BNI)';
    case MANDIRI = 'Bank Mandiri';
    case BSI = 'Bank Syariah Indonesia (BSI)';
    case BTN = 'Bank Tabungan Negara (BTN)';
    case BCA = 'Bank Central Asia (BCA)';
    case MUAMALAT = 'Bank Muamalat';
    case LAINNYA = 'Bank Lainnya';

    public function getLabel(): ?string
    {
        return $this->value;
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->getLabel()])
            ->all();
    }
}
