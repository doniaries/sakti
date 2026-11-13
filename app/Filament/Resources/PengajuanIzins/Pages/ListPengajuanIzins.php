<?php

namespace App\Filament\Resources\PengajuanIzins\Pages;

use App\Filament\Resources\PengajuanIzins\PengajuanIzinResource;
use App\Filament\Resources\PengajuanIzins\Widgets\PengajuanIzinStatsWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanIzins extends ListRecords
{
    protected static string $resource = PengajuanIzinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PengajuanIzinStatsWidget::class,
        ];
    }
}
