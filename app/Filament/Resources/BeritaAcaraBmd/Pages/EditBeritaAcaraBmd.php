<?php

declare(strict_types=1);

namespace App\Filament\Resources\BeritaAcaraBmd\Pages;

use App\Filament\Resources\BeritaAcaraBmd\BeritaAcaraBmdResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBeritaAcaraBmd extends EditRecord
{
    protected static string $resource = BeritaAcaraBmdResource::class;

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
