<?php

namespace App\Filament\Resources\Reputasis\Pages;

use App\Filament\Resources\Reputasis\ReputasiResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReputasi extends EditRecord
{
    protected static string $resource = ReputasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
