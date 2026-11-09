<?php

namespace App\Filament\Resources\MasterSubKegiatans\Pages;

use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Filament\Resources\MasterSubKegiatans\Widgets\SubKegiatanAnggaranOverviewWidget;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;

class EditMasterSubKegiatan extends EditRecord
{
    protected static string $resource = MasterSubKegiatanResource::class;

    protected Width | string | null $maxContentWidth = Width::Full;

    protected function getHeaderWidgets(): array
    {
        return [
            SubKegiatanAnggaranOverviewWidget::class,
        ];
    }

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

    protected function getFormActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
