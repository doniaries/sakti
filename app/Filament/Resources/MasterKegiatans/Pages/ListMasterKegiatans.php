<?php

namespace App\Filament\Resources\MasterKegiatans\Pages;

use App\Filament\Resources\MasterKegiatans\MasterKegiatanResource;
use App\Filament\Resources\PengajuanSpjs\Widgets\KeuanganBidangOverviewWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterKegiatans extends ListRecords
{
    protected static string $resource = MasterKegiatanResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            KeuanganBidangOverviewWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Kegiatan'),
        ];
    }
}

