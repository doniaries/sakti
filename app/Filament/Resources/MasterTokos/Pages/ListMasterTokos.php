<?php

namespace App\Filament\Resources\MasterTokos\Pages;

use App\Filament\Resources\MasterTokos\MasterTokoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMasterTokos extends ListRecords
{
    protected static string $resource = MasterTokoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Toko / Rekanan'),
        ];
    }
}

