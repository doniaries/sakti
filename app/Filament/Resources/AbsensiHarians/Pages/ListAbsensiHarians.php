<?php

namespace App\Filament\Resources\AbsensiHarians\Pages;

use App\Filament\Resources\AbsensiHarians\AbsensiHarianResource;
use App\Models\AbsensiHarian;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListAbsensiHarians extends ListRecords
{
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
        return [];
    }
}
