<?php

namespace App\Filament\Resources\MasterTujuanBbms\Pages;

use App\Filament\Resources\MasterTujuanBbms\MasterTujuanBbmResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterTujuanBbms extends ListRecords
{
    protected static string $resource = MasterTujuanBbmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

