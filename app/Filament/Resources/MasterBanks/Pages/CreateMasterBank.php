<?php

namespace App\Filament\Resources\MasterBanks\Pages;

use App\Filament\Resources\MasterBanks\MasterBankResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterBank extends CreateRecord
{
    protected static string $resource = MasterBankResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
