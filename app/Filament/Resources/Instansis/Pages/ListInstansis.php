<?php

namespace App\Filament\Resources\Instansis\Pages;

use App\Filament\Resources\Instansis\InstansiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInstansis extends ListRecords
{
    protected static string $resource = InstansiResource::class;

    protected function getHeaderActions(): array
    {
        $instansi = \App\Models\Instansi::first();

        return [
            \Filament\Actions\Action::make('edit_instansi')
                ->label('Edit Profil Instansi')
                ->icon('heroicon-m-pencil-square')
                ->color('primary')
                ->visible(fn (): bool => $instansi !== null)
                ->url(fn (): string => $instansi ? InstansiResource::getUrl('edit', ['record' => $instansi]) : ''),

            CreateAction::make()
                ->visible(fn (): bool => \App\Models\Instansi::count() === 0),
        ];
    }
}
