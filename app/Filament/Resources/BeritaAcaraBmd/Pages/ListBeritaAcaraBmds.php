<?php

declare(strict_types=1);

namespace App\Filament\Resources\BeritaAcaraBmd\Pages;

use App\Filament\Resources\BeritaAcaraBmd\BeritaAcaraBmdResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBeritaAcaraBmds extends ListRecords
{
    protected static string $resource = BeritaAcaraBmdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Berita Acara')
                ->icon('heroicon-o-plus-circle'),
        ];
    }
}
