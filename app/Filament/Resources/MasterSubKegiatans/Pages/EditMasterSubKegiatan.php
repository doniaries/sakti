<?php

namespace App\Filament\Resources\MasterSubKegiatans\Pages;

use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditMasterSubKegiatan extends EditRecord
{
    protected static string $resource = MasterSubKegiatanResource::class;

    public function getTitle(): string|Htmlable
    {
        return "RKA Sub Kegiatan: {$this->record->kode_sub_kegiatan}";
    }

    public function getSubheading(): ?string
    {
        return $this->record->nama_sub_kegiatan;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('kembali')
                ->label('Kembali ke Daftar Sub Kegiatan')
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
