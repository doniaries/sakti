<?php

namespace App\Filament\Resources\MasterJenisBelanjas\Pages;

use App\Filament\Resources\MasterJenisBelanjas\MasterJenisBelanjaResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterJenisBelanja extends CreateRecord
{
    protected static string $resource = MasterJenisBelanjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('kembali')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn (): string => static::getResource()::getUrl('index')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

