<?php

namespace App\Filament\Resources\AgendaKegiatans\Pages;

use App\Filament\Resources\AgendaKegiatans\AgendaKegiatanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAgendaKegiatan extends CreateRecord
{
    protected static string $resource = AgendaKegiatanResource::class;

    /** @var array<int, int|string> */
    protected array $disposisiUserIds = [];

    protected ?string $disposisiInstruksi = null;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->disposisiUserIds = $data['disposisi_user_ids'] ?? [];
        $this->disposisiInstruksi = $data['disposisi_instruksi'] ?? null;

        unset($data['disposisi_user_ids'], $data['disposisi_instruksi']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncDisposisis($this->disposisiUserIds, $this->disposisiInstruksi);
    }
}
