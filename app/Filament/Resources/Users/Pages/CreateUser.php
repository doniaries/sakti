<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('kembali')
                ->label('Kembali ke Index')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
            Action::make('create')
                ->label('Simpan')
                ->icon('heroicon-o-check')
                ->color('primary')
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['password_confirmation']);

        if (empty($data['status_kepegawaian'])) {
            $data['status_kepegawaian'] = \App\Enums\StatusKepegawaian::PNS->value;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record->hasRole('pegawai_kontrak')) {
            $this->record->updateQuietly([
                'status_kepegawaian' => \App\Enums\StatusKepegawaian::KONTRAK,
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
