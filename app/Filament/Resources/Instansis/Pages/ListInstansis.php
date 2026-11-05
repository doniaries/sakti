<?php

namespace App\Filament\Resources\Instansis\Pages;

use App\Filament\Resources\Instansis\InstansiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInstansis extends ListRecords
{
    protected static string $resource = InstansiResource::class;

    public function mount(): void
    {
        $instansi = \App\Models\Instansi::first();
        if ($instansi) {
            $this->redirect(InstansiResource::getUrl('edit', ['record' => $instansi]));
            return;
        }

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => \App\Models\Instansi::count() === 0),
        ];
    }
}
