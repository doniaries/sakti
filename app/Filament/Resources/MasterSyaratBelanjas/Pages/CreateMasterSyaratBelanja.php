<?php

namespace App\Filament\Resources\MasterSyaratBelanjas\Pages;

use App\Filament\Resources\MasterSyaratBelanjas\MasterSyaratBelanjaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterSyaratBelanja extends CreateRecord
{
    protected static string $resource = MasterSyaratBelanjaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
