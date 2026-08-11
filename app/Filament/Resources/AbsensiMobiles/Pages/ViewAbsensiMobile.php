<?php

namespace App\Filament\Resources\AbsensiMobiles\Pages;

use App\Filament\Resources\AbsensiMobiles\AbsensiMobileResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAbsensiMobile extends ViewRecord
{
    protected static string $resource = AbsensiMobileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
