<?php

namespace App\Filament\Resources\PengajuanSpjs\Pages;

use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePengajuanSpj extends CreateRecord
{
    protected static string $resource = PengajuanSpjResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

