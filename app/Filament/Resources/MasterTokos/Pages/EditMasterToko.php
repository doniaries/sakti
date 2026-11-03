<?php

namespace App\Filament\Resources\MasterTokos\Pages;

use App\Filament\Resources\MasterTokos\MasterTokoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMasterToko extends EditRecord
{
    protected static string $resource = MasterTokoResource::class;

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
