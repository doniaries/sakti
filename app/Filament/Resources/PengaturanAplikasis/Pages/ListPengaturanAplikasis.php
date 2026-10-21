<?php

namespace App\Filament\Resources\PengaturanAplikasis\Pages;

use App\Filament\Resources\PengaturanAplikasis\PengaturanAplikasiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPengaturanAplikasis extends ListRecords
{
    protected static string $resource = PengaturanAplikasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
