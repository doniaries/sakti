<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JabatanTimKreatif: string implements HasLabel, HasColor
{
    case PEMBINA = 'pembina';
    case PENGARAH = 'pengarah';
    case PENANGGUNG_JAWAB_BIDANG = 'penanggung_jawab_bidang';
    case KOORDINATOR_TIM_KREATIF = 'koordinator_tim_kreatif';
    case KOORDINATOR_TEKNIS = 'koordinator_teknis';
    case KOORDINATOR_WEBSITE = 'koordinator_website';
    case KOORDINATOR_DESAIN_GRAFIS = 'koordinator_desain_grafis';
    case KOORDINATOR_DOKUMENTASI = 'koordinator_dokumentasi';
    case KOORDINATOR_MEDIA_SOSIAL = 'koordinator_media_sosial';
    case PELAKSANA_WEBSITE = 'pelaksana_website';
    case PENGELOLA_MEDIA_SOSIAL = 'pengelola_media_sosial';
    case DESAIN_GRAFIS = 'desain_grafis';
    case DOKUMENTASI = 'dokumentasi';
    case PENERJEMAH = 'penerjemah';
    case ADMINISTRASI = 'administrasi';
    case PENGELOLA_GEOPARK = 'pengelola_geopark';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PEMBINA => 'Pembina',
            self::PENGARAH => 'Pengarah',
            self::PENANGGUNG_JAWAB_BIDANG => 'Penanggung Jawab Kegiatan Bidang',
            self::KOORDINATOR_TIM_KREATIF => 'Koordinator Tim Kreatif',
            self::KOORDINATOR_TEKNIS => 'Koordinator Teknis',
            self::KOORDINATOR_WEBSITE => 'Koordinator Website Dinas',
            self::KOORDINATOR_DESAIN_GRAFIS => 'Koordinator Desain Grafis',
            self::KOORDINATOR_DOKUMENTASI => 'Koordinator Dokumentasi',
            self::KOORDINATOR_MEDIA_SOSIAL => 'Koordinator Media Sosial',
            self::PELAKSANA_WEBSITE => 'Pelaksana Website',
            self::PENGELOLA_MEDIA_SOSIAL => 'Pengelola Media Sosial',
            self::DESAIN_GRAFIS => 'Desain Grafis',
            self::DOKUMENTASI => 'Dokumentasi (Fotografer / Videografer)',
            self::PENERJEMAH => 'Penerjemah',
            self::ADMINISTRASI => 'Administrasi',
            self::PENGELOLA_GEOPARK => 'Pengelola Geopark',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::PEMBINA => 'danger',
            self::PENGARAH, self::PENANGGUNG_JAWAB_BIDANG => 'warning',
            self::KOORDINATOR_TIM_KREATIF, self::KOORDINATOR_TEKNIS => 'primary',
            self::KOORDINATOR_WEBSITE,
            self::KOORDINATOR_DESAIN_GRAFIS,
            self::KOORDINATOR_DOKUMENTASI,
            self::KOORDINATOR_MEDIA_SOSIAL => 'info',
            self::PELAKSANA_WEBSITE,
            self::PENGELOLA_MEDIA_SOSIAL,
            self::DESAIN_GRAFIS,
            self::DOKUMENTASI,
            self::PENERJEMAH,
            self::PENGELOLA_GEOPARK => 'success',
            self::ADMINISTRASI => 'gray',
        };
    }
}
