<?php

namespace App\Filament\Resources\MasterTujuanBbms\Pages;

use App\Filament\Resources\MasterTujuanBbms\MasterTujuanBbmResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMasterTujuanBbm extends EditRecord
{
    protected static string $resource = MasterTujuanBbmResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

