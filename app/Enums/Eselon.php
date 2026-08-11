<?php

namespace App\Enums;

enum Eselon: string
{
    case II_A = 'II.a';
    case II_B = 'II.b';
    case III_A = 'III.a';
    case III_B = 'III.b';
    case IV_A = 'IV.a';
    case IV_B = 'IV.b';
    case NON_ESELON = 'Non Eselon';

    public function getLabel(): string
    {
        return $this->value;
    }
}
