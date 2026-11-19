<?php

declare(strict_types=1);

namespace App\Filament\Resources\BeritaAcaraBmd\Pages;

use App\Filament\Resources\BeritaAcaraBmd\BeritaAcaraBmdResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBeritaAcaraBmd extends CreateRecord
{
    protected static string $resource = BeritaAcaraBmdResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
