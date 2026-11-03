<?php

namespace App\Filament\Resources\KodeRekenings\Pages;

use App\Filament\Resources\KodeRekenings\KodeRekeningResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKodeRekening extends CreateRecord
{
    protected static string $resource = KodeRekeningResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

