<?php

namespace App\Filament\Resources\MasterBanks\Pages;

use App\Filament\Resources\MasterBanks\MasterBankResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMasterBank extends EditRecord
{
    protected static string $resource = MasterBankResource::class;

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
