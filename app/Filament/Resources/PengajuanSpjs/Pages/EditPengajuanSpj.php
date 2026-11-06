<?php

namespace App\Filament\Resources\PengajuanSpjs\Pages;

use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditPengajuanSpj extends EditRecord
{
    protected static string $resource = PengajuanSpjResource::class;

    protected Width | string | null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetak')
                ->label(function (): string {
                    return match ($this->record->status) {
                        'menunggu_realisasi' => 'Preview & Cetak Pesanan',
                        'menunggu_verifikasi_pptk', 'revisi_pptk' => 'Preview & Cetak Pesanan / Kwitansi',
                        default => 'Preview & Cetak Dokumen (SPJ / Kwitansi / NPD)',
                    };
                })
                ->icon('heroicon-o-document-magnifying-glass')
                ->color('info')
                ->visible(fn (): bool => ! in_array($this->record->status, ['draft', 'diajukan', 'ditolak'], true))
                ->modalHeading(fn (): string => "Pratinjau Dokumen SPJ — {$this->record->nomor_pengajuan}")
                ->modalDescription('Periksa isi dokumen sebelum dicetak. Gunakan tombol zoom untuk memperbesar dan beralih antar dokumen.')
                ->modalWidth(\Filament\Support\Enums\Width::SevenExtraLarge)
                ->modalContent(fn () => view('filament.pages.preview-spj-modal', [
                    'record' => $this->record,
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup'),

            DeleteAction::make()
                ->visible(fn (): bool => $this->record->status === 'draft'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
