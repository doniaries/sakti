<?php

namespace App\Filament\Resources\AbsensiMobiles\Pages;

use App\Filament\Resources\AbsensiMobiles\AbsensiMobileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAbsensiMobile extends CreateRecord
{
    protected static string $resource = AbsensiMobileResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
