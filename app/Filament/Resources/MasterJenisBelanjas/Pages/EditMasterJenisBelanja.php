<?php

namespace App\Filament\Resources\MasterJenisBelanjas\Pages;

use App\Filament\Resources\MasterJenisBelanjas\MasterJenisBelanjaResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditMasterJenisBelanja extends EditRecord
{
    protected static string $resource = MasterJenisBelanjaResource::class;

    public function getTitle(): string|Htmlable
    {
        return "Kelola Syarat Dokumen — {$this->record->nama}";
    }

    public function getSubheading(): ?string
    {
        return "Atur berkas persyaratan SOP yang wajib dan opsional diunggah staf untuk pekerjaan {$this->record->nama}.";
    }

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
