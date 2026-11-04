<?php

declare(strict_types=1);

namespace App\Filament\Resources\StokBarangs\Pages;

use App\Filament\Resources\StokBarangs\StokBarangResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStokBarang extends CreateRecord
{
    protected static string $resource = StokBarangResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

