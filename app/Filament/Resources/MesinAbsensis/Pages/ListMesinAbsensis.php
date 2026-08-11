<?php

namespace App\Filament\Resources\MesinAbsensis\Pages;

use App\Filament\Resources\MesinAbsensis\MesinAbsensiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMesinAbsensis extends ListRecords
{
    protected static string $resource = MesinAbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
