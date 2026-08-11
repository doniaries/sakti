<?php

namespace App\Filament\Resources\MesinAbsensis\Pages;

use App\Filament\Resources\MesinAbsensis\MesinAbsensiResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMesinAbsensi extends ViewRecord
{
    protected static string $resource = MesinAbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
