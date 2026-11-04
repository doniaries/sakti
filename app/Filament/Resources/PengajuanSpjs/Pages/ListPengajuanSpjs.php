<?php

namespace App\Filament\Resources\PengajuanSpjs\Pages;

use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Filament\Resources\PengajuanSpjs\Widgets\KeuanganBidangOverviewWidget;
use Filament\Resources\Pages\ListRecords;

class ListPengajuanSpjs extends ListRecords
{
    protected static string $resource = PengajuanSpjResource::class;

    public function mount(): void
    {
        parent::mount();

        $bidangId = request()->query('bidang')
            ?? data_get(request()->query('filters'), 'bidang_id.value')
            ?? data_get(request()->query('tableFilters'), 'bidang_id.value');

        if (filled($bidangId)) {
            $filters = $this->tableFilters ?? [];
            $filters['bidang_id'] = ['value' => (string) $bidangId];
            $this->tableFilters = $filters;
            $this->getTableFiltersForm()->fill($this->tableFilters);
        }
    }

    protected function getHeaderWidgets(): array
    {
        return [
            KeuanganBidangOverviewWidget::class,
        ];
    }
}
