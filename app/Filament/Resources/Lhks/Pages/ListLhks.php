<?php

namespace App\Filament\Resources\Lhks\Pages;

use App\Filament\Resources\Lhks\LhkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use ZeeshanTariq\FilamentStickyColumns\Concerns\InteractsWithStickyableColumns;

class ListLhks extends ListRecords
{
    use InteractsWithStickyableColumns;

    protected static string $resource = LhkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
