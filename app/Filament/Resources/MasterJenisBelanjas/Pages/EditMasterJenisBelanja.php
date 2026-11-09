<?php

namespace App\Filament\Resources\MasterJenisBelanjas\Pages;

use App\Filament\Resources\MasterJenisBelanjas\MasterJenisBelanjaResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMasterJenisBelanja extends EditRecord
{
    protected static string $resource = MasterJenisBelanjaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('kembali')
                ->label('Kembali ke Daftar Pekerjaan')
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

