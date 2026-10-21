<?php

namespace App\Filament\Resources\PengaturanAplikasis\Pages;

use App\Filament\Resources\PengaturanAplikasis\PengaturanAplikasiResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPengaturanAplikasi extends EditRecord
{
    protected static string $resource = PengaturanAplikasiResource::class;

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
