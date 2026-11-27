<?php

namespace App\Filament\Resources\AgendaKegiatans\Pages;

use App\Filament\Resources\AgendaKegiatans\AgendaKegiatanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAgendaKegiatan extends EditRecord
{
    protected static string $resource = AgendaKegiatanResource::class;

    /** @var array<int, int|string> */
    protected array $disposisiUserIds = [];

    protected ?string $disposisiInstruksi = null;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['disposisi_user_ids'] = $this->record->disposisis()->pluck('user_id')->all();
        $data['disposisi_instruksi'] = $this->record->disposisis()->whereNotNull('instruksi')->value('instruksi') ?? '';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->disposisiUserIds = $data['disposisi_user_ids'] ?? [];
        $this->disposisiInstruksi = $data['disposisi_instruksi'] ?? null;

        unset($data['disposisi_user_ids'], $data['disposisi_instruksi']);

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncDisposisis($this->disposisiUserIds, $this->disposisiInstruksi);
    }
}
