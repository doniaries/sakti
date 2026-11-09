<?php

namespace App\Filament\Resources\MasterSyaratBelanjas\Pages;

use App\Filament\Resources\MasterSyaratBelanjas\MasterSyaratBelanjaResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMasterSyaratBelanja extends EditRecord
{
    protected static string $resource = MasterSyaratBelanjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('kembali')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn (): string => static::getResource()::getUrl('index')),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
