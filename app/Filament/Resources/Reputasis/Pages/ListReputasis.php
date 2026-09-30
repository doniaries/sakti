<?php

namespace App\Filament\Resources\Reputasis\Pages;

use App\Filament\Resources\Reputasis\ReputasiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReputasis extends ListRecords
{
    protected static string $resource = ReputasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
