<?php

namespace App\Enums;

enum GolonganRuang: string
{
    // Golongan I (Juru)
    case I_A = 'I/a';
    case I_B = 'I/b';
    case I_C = 'I/c';
    case I_D = 'I/d';
    
    // Golongan II (Pengatur)
    case II_A = 'II/a';
    case II_B = 'II/b';
    case II_C = 'II/c';
    case II_D = 'II/d';
    
    // Golongan III (Penata)
    case III_A = 'III/a';
    case III_B = 'III/b';
    case III_C = 'III/c';
    case III_D = 'III/d';
    
    // Golongan IV (Pembina)
    case IV_A = 'IV/a';
    case IV_B = 'IV/b';
    case IV_C = 'IV/c';
    case IV_D = 'IV/d';
    case IV_E = 'IV/e';

    // PPPK Golongan
    case PPPK_I = 'I';
    case PPPK_II = 'II';
    case PPPK_III = 'III';
    case PPPK_IV = 'IV';
    case PPPK_V = 'V';
    case PPPK_VI = 'VI';
    case PPPK_VII = 'VII';
    case PPPK_VIII = 'VIII';
    case PPPK_IX = 'IX';
    case PPPK_X = 'X';
    case PPPK_XI = 'XI';
    case PPPK_XII = 'XII';
    case PPPK_XIII = 'XIII';
    case PPPK_XIV = 'XIV';
    case PPPK_XV = 'XV';
    case PPPK_XVI = 'XVI';
    case PPPK_XVII = 'XVII';

    case NON_ASN = '-'; // Untuk THL / Non ASN

    public function getLabel(): string
    {
        return match ($this) {
            self::I_A => 'I/a - Juru Muda',
            self::I_B => 'I/b - Juru Muda Tingkat I',
            self::I_C => 'I/c - Juru',
            self::I_D => 'I/d - Juru Tingkat I',
            
            self::II_A => 'II/a - Pengatur Muda',
            self::II_B => 'II/b - Pengatur Muda Tingkat I',
            self::II_C => 'II/c - Pengatur',
            self::II_D => 'II/d - Pengatur Tingkat I',
            
            self::III_A => 'III/a - Penata Muda',
            self::III_B => 'III/b - Penata Muda Tingkat I',
            self::III_C => 'III/c - Penata',
            self::III_D => 'III/d - Penata Tingkat I',
            
            self::IV_A => 'IV/a - Pembina',
            self::IV_B => 'IV/b - Pembina Tingkat I',
            self::IV_C => 'IV/c - Pembina Utama Muda',
            self::IV_D => 'IV/d - Pembina Utama Madya',
            self::IV_E => 'IV/e - Pembina Utama',
            
            default => $this->value
        };
    }
}
