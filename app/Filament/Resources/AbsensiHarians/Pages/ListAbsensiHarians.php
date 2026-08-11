<?php

namespace App\Filament\Resources\AbsensiHarians\Pages;

use App\Filament\Resources\AbsensiHarians\AbsensiHarianResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Leek\FilamentHeaderFilters\Concerns\HasHeaderFilters;

class ListAbsensiHarians extends ListRecords
{
    // HasHeaderFilters tidak diperlukan karena filter sudah menggunakan AboveContent layout

    protected static string $resource = AbsensiHarianResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\AbsensiHarians\Widgets\AbsensiHarianStats::class,
        ];
    }

    public function getWidgetData(): array
    {
        $data = parent::getWidgetData();
        
        $data['tableColumnSearches'] = $data['tableColumnSearches'] ?? [];
        $data['tableFilters'] = $data['tableFilters'] ?? [];
        $data['tableSearch'] = $data['tableSearch'] ?? '';
        
        return $data;
    }


    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
