<?php

namespace App\Filament\Resources\RincianAnggaranBelanjas\Pages;

use App\Filament\Resources\RincianAnggaranBelanjas\RincianAnggaranBelanjaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRincianAnggaranBelanja extends CreateRecord
{
    protected static string $resource = RincianAnggaranBelanjaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

