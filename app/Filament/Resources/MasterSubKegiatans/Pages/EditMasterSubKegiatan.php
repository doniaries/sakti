<?php

namespace App\Filament\Resources\MasterSubKegiatans\Pages;

use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Filament\Resources\MasterSubKegiatans\Schemas\MasterSubKegiatanForm;
use App\Filament\Resources\MasterSubKegiatans\Widgets\SubKegiatanAnggaranOverviewWidget;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditMasterSubKegiatan extends EditRecord
{
    protected static string $resource = MasterSubKegiatanResource::class;

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
            Action::make('ubah_sub_kegiatan')
                ->label('Ubah Identitas Sub Kegiatan')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->slideOver()
                ->modalWidth('4xl')
                ->modalHeading("Ubah Sub Kegiatan: {$this->record->kode_sub_kegiatan}")
                ->modalDescription('Perbarui data identitas Sub Kegiatan & penugasan JF via SlideOver.')
                ->fillForm(fn (): array => [
                    'kode_sub_kegiatan' => $this->record->kode_sub_kegiatan,
                    'tahun_anggaran' => $this->record->tahun_anggaran,
                    'kegiatan_id' => $this->record->kegiatan_id,
                    'jf_id' => $this->record->jf_id,
                    'nama_sub_kegiatan' => $this->record->nama_sub_kegiatan,
                ])
                ->form(MasterSubKegiatanForm::getFields(disabled: false))
                ->action(function (array $data): void {
                    $this->record->update($data);
                    Notification::make()
                        ->title('Identitas Sub Kegiatan berhasil diperbarui')
                        ->success()
                        ->send();
                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                }),

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
