<?php

namespace App\Filament\Resources\MasterTujuanBbms\Pages;

use App\Filament\Resources\MasterTujuanBbms\MasterTujuanBbmResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterTujuanBbm extends CreateRecord
{
    protected static string $resource = MasterTujuanBbmResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

