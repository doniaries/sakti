<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum StatusKehadiran: string implements HasLabel, HasColor, HasIcon
{
    case HADIR = 'hadir';
    case TERLAMBAT = 'terlambat';
    case TIDAK_HADIR = 'tidak_hadir';
    case DINAS_LUAR = 'dinas_luar';
    case CUTI = 'cuti';
    case IZIN = 'izin';
    case SAKIT = 'sakit';
    case LIBUR = 'libur';

    public function getLabel(): string
    {
        return match ($this) {
            self::HADIR => 'Hadir',
            self::TERLAMBAT => 'Terlambat',
            self::TIDAK_HADIR => 'Tidak Hadir',
            self::DINAS_LUAR => 'Dinas Luar',
            self::CUTI => 'Cuti',
            self::IZIN => 'Izin',
            self::SAKIT => 'Sakit',
            self::LIBUR => 'Libur',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::HADIR => 'success',
            self::TERLAMBAT, self::CUTI, self::IZIN, self::SAKIT => 'warning',
            self::TIDAK_HADIR => 'danger',
            self::DINAS_LUAR => 'info',
            self::LIBUR => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::HADIR => 'heroicon-m-check-circle',
            self::TERLAMBAT => 'heroicon-m-clock',
            self::TIDAK_HADIR => 'heroicon-m-x-circle',
            self::DINAS_LUAR => 'heroicon-m-briefcase',
            self::CUTI, self::IZIN => 'heroicon-m-document-text',
            self::SAKIT => 'heroicon-m-plus-circle',
            self::LIBUR => 'heroicon-m-calendar',
        };
    }
}
