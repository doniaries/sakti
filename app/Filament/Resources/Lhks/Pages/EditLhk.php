<?php

namespace App\Filament\Resources\Lhks\Pages;

use App\Filament\Resources\Lhks\LhkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLhk extends EditRecord
{
    protected static string $resource = LhkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
