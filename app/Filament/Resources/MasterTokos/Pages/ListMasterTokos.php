<?php

namespace App\Filament\Resources\MasterTokos\Pages;

use App\Filament\Resources\MasterTokos\MasterTokoResource;
use App\Filament\Resources\PengajuanSpjs\Widgets\KeuanganBidangOverviewWidget;
use Filament\Resources\Pages\ListRecords;

class ListMasterTokos extends ListRecords
{
    protected static string $resource = MasterTokoResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            KeuanganBidangOverviewWidget::class,
        ];
    }
}
