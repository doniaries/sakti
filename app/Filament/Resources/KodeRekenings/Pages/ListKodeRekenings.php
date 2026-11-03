<?php

namespace App\Filament\Resources\KodeRekenings\Pages;

use App\Filament\Resources\KodeRekenings\KodeRekeningResource;
use App\Filament\Resources\PengajuanSpjs\Widgets\KeuanganBidangOverviewWidget;
use Filament\Resources\Pages\ListRecords;

class ListKodeRekenings extends ListRecords
{
    protected static string $resource = KodeRekeningResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            KeuanganBidangOverviewWidget::class,
        ];
    }
}
