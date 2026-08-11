<?php

namespace App\Filament\Resources\RencanaAksis\Pages;

use App\Filament\Resources\RencanaAksis\RencanaAksiResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRencanaAksi extends EditRecord
{
    protected static string $resource = RencanaAksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
