<?php

namespace App\Filament\Resources\MasterTokos\Pages;

use App\Filament\Resources\MasterTokos\MasterTokoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterToko extends CreateRecord
{
    protected static string $resource = MasterTokoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
