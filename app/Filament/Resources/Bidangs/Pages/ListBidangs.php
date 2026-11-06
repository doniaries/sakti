<?php

namespace App\Filament\Resources\Bidangs\Pages;

use App\Filament\Resources\Bidangs\BidangResource;
use App\Filament\Resources\Jabatans\JabatanResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBidangs extends ListRecords
{
    protected static string $resource = BidangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Bidang')
                ->icon('heroicon-o-plus')
                ->color('primary'),
        ];
    }
}
