<?php

namespace App\Filament\Resources\JadwalKerjas\Pages;

use App\Filament\Resources\JadwalKerjas\JadwalKerjaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateJadwalKerja extends CreateRecord
{
    protected static string $resource = JadwalKerjaResource::class;

    public function getMaxContentWidth(): \Filament\Support\Enums\Width|string|null
    {
        return \Filament\Support\Enums\Width::Full;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
