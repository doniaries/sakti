<?php

namespace App\Filament\Resources\PengaturanAplikasis\Pages;

use App\Filament\Resources\PengaturanAplikasis\PengaturanAplikasiResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePengaturanAplikasi extends CreateRecord
{
    protected static string $resource = PengaturanAplikasiResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
