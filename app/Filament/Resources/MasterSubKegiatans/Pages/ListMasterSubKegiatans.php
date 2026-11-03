<?php

namespace App\Filament\Resources\MasterSubKegiatans\Pages;

use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Filament\Resources\PengajuanSpjs\Widgets\KeuanganBidangOverviewWidget;
use Filament\Resources\Pages\ListRecords;

class ListMasterSubKegiatans extends ListRecords
{
    protected static string $resource = MasterSubKegiatanResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            KeuanganBidangOverviewWidget::class,
        ];
    }
}
