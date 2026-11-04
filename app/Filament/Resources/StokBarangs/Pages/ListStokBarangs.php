<?php

declare(strict_types=1);

namespace App\Filament\Resources\StokBarangs\Pages;

use App\Filament\Resources\PengajuanSpjs\Widgets\KeuanganBidangOverviewWidget;
use App\Filament\Resources\StokBarangs\StokBarangResource;
use Filament\Resources\Pages\ListRecords;

class ListStokBarangs extends ListRecords
{
    protected static string $resource = StokBarangResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            KeuanganBidangOverviewWidget::class,
        ];
    }
}

