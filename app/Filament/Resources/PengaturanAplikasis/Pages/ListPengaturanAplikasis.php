<?php

namespace App\Filament\Resources\PengaturanAplikasis\Pages;

use App\Filament\Resources\PengaturanAplikasis\PengaturanAplikasiResource;
use App\Models\PengaturanAplikasi;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPengaturanAplikasis extends ListRecords
{
    protected static string $resource = PengaturanAplikasiResource::class;

    public function mount(): void
    {
        $pengaturan = PengaturanAplikasi::first();
        if ($pengaturan) {
            $this->redirect(PengaturanAplikasiResource::getUrl('edit', ['record' => $pengaturan]));
            return;
        }

        parent::mount();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => PengaturanAplikasi::count() === 0),
        ];
    }
}
