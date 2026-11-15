<?php

declare(strict_types=1);

namespace App\Filament\Resources\SuratTugas\Pages;

use App\Filament\Resources\SuratTugas\SuratTugasResource;
use Filament\Resources\Pages\EditRecord;

class EditSuratTugas extends EditRecord
{
    protected static string $resource = SuratTugasResource::class;

    protected function afterSave(): void
    {
        if ($this->record->wasChanged('file_tte') && filled($this->record->file_tte)) {
            $this->record->kirimNotifikasiKeTim();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
