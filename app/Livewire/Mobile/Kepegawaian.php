<?php

declare(strict_types=1);

namespace App\Livewire\Mobile;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.mobile')]
#[Title('Data Kepegawaian - SAKTI Mobile')]
class Kepegawaian extends Profil
{
    public bool $isKepegawaianPage = true;
}
