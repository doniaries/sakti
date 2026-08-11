<?php

namespace App\Filament\Resources\LogAbsensis\Pages;

use App\Filament\Resources\LogAbsensis\LogAbsensiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\LogAbsensis\Widgets\MesinSyncStatWidget;

class ListLogAbsensis extends ListRecords
{
    protected static string $resource = LogAbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MesinSyncStatWidget::class,
        ];
    }
}
