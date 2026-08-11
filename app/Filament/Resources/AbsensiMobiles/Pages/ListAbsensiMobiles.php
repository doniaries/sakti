<?php

namespace App\Filament\Resources\AbsensiMobiles\Pages;

use App\Filament\Resources\AbsensiMobiles\AbsensiMobileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAbsensiMobiles extends ListRecords
{
    protected static string $resource = AbsensiMobileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
