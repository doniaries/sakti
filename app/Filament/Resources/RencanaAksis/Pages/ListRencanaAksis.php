<?php

namespace App\Filament\Resources\RencanaAksis\Pages;

use App\Filament\Resources\RencanaAksis\RencanaAksiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRencanaAksis extends ListRecords
{
    protected static string $resource = RencanaAksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
