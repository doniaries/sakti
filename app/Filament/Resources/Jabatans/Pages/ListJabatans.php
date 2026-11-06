<?php

namespace App\Filament\Resources\Jabatans\Pages;

use App\Filament\Resources\Bidangs\BidangResource;
use App\Filament\Resources\Jabatans\JabatanResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJabatans extends ListRecords
{
    protected static string $resource = JabatanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Jabatan')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}
