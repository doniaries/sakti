<?php

namespace App\Filament\Resources\MasterPrograms\Pages;

use App\Filament\Resources\KodeRekenings\KodeRekeningResource;
use App\Filament\Resources\MasterKegiatans\MasterKegiatanResource;
use App\Filament\Resources\MasterPrograms\MasterProgramResource;
use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterPrograms extends ListRecords
{
    protected static string $resource = MasterProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Program')
                ->icon('heroicon-o-plus-circle')
                ->color('primary'),
        ];
    }
}
