<?php

namespace App\Filament\Resources\MesinAbsensis\Pages;

use App\Filament\Resources\MesinAbsensis\MesinAbsensiResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMesinAbsensi extends EditRecord
{
    protected static string $resource = MesinAbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
