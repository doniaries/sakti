<?php

namespace App\Filament\Resources\MasterPrograms\Pages;

use App\Filament\Resources\MasterPrograms\MasterProgramResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListMasterPrograms extends ListRecords
{
    protected static string $resource = MasterProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Program')
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
}

