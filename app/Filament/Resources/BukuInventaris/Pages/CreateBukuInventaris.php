<?php

declare(strict_types=1);

namespace App\Filament\Resources\BukuInventaris\Pages;

use App\Filament\Resources\BukuInventaris\BukuInventarisResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBukuInventaris extends CreateRecord
{
    protected static string $resource = BukuInventarisResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
