<?php

namespace App\Filament\Resources\LogAbsensis\Pages;

use App\Filament\Resources\LogAbsensis\LogAbsensiResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLogAbsensi extends ViewRecord
{
    protected static string $resource = LogAbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
