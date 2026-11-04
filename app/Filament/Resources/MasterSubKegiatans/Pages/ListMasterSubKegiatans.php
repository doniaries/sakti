<?php

namespace App\Filament\Resources\MasterSubKegiatans\Pages;

use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Filament\Resources\PengajuanSpjs\Widgets\KeuanganBidangOverviewWidget;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListMasterSubKegiatans extends ListRecords
{
    protected static string $resource = MasterSubKegiatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Sub Kegiatan')
                ->icon('heroicon-m-plus'),
        ];
    }

    public function getTabs(): array
    {
        $currentYear = (int) date('Y');

        return [
            (string) $currentYear => Tab::make("Tahun {$currentYear} (Aktif)")
                ->modifyQueryUsing(fn ($query) => $query->where('tahun_anggaran', $currentYear)),
            (string) ($currentYear - 1) => Tab::make("Tahun " . ($currentYear - 1))
                ->modifyQueryUsing(fn ($query) => $query->where('tahun_anggaran', $currentYear - 1)),
            (string) ($currentYear + 1) => Tab::make("Tahun " . ($currentYear + 1))
                ->modifyQueryUsing(fn ($query) => $query->where('tahun_anggaran', $currentYear + 1)),
            'all' => Tab::make('Semua Tahun'),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return (string) date('Y');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            KeuanganBidangOverviewWidget::class,
        ];
    }
}
