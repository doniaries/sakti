<?php

namespace App\Filament\Resources\PengajuanSpjs\Pages;

use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewPengajuanSpj extends ViewRecord
{
    protected static string $resource = PengajuanSpjResource::class;

    protected Width | string | null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('kembali')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn(): string => static::getResource()::getUrl('index')),

            Action::make('cetak')
                ->label('Preview Dokumen')
                ->icon('heroicon-o-document-magnifying-glass')
                ->color('info')
                ->visible(fn(): bool => ! in_array($this->record->status, ['draft', 'diajukan', 'ditolak'], true))
                ->modalHeading(fn(): string => "Pratinjau Dokumen SPJ — {$this->record->nomor_pengajuan}")
                ->modalDescription('Periksa isi dokumen sebelum dicetak. Gunakan tombol zoom untuk memperbesar dan beralih antar dokumen.')
                ->modalWidth(\Filament\Support\Enums\Width::SevenExtraLarge)
                ->modalContent(fn() => view('filament.pages.preview-spj-modal', [
                    'record' => $this->record,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup'),

            EditAction::make(),
        ];
    }
}
