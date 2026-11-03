<?php

namespace App\Filament\Resources\MasterBanks\Pages;

use App\Filament\Resources\MasterBanks\MasterBankResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterBanks extends ListRecords
{
    protected static string $resource = MasterBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Bank'),
        ];
    }
}
