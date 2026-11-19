<?php

declare(strict_types=1);

namespace App\Filament\Resources\BukuInventaris\Pages;

use App\Filament\Resources\BukuInventaris\BukuInventarisResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBukuInventaris extends EditRecord
{
    protected static string $resource = BukuInventarisResource::class;

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
