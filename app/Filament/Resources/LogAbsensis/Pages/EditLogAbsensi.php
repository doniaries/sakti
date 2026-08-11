<?php

namespace App\Filament\Resources\LogAbsensis\Pages;

use App\Filament\Resources\LogAbsensis\LogAbsensiResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditLogAbsensi extends EditRecord
{
    protected static string $resource = LogAbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
