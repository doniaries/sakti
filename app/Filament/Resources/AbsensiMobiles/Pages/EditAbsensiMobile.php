<?php

namespace App\Filament\Resources\AbsensiMobiles\Pages;

use App\Filament\Resources\AbsensiMobiles\AbsensiMobileResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAbsensiMobile extends EditRecord
{
    protected static string $resource = AbsensiMobileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
