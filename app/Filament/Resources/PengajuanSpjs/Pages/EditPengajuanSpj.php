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

    protected Width | string | null $maxContentWidth = Width::FiveExtraLarge;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cetak')
                ->label(function (): string {
                    return match ($this->record->status) {
                        'menunggu_realisasi' => 'Cetak Surat Pesanan',
                        'menunggu_verifikasi_pptk', 'revisi_pptk' => 'Cetak Pesanan & Kwitansi',
                        default => 'Cetak Pesanan, Kwitansi & NPD',
                    };
                })
                ->icon('heroicon-o-printer')
                ->color('success')
                ->visible(fn (): bool => ! in_array($this->record->status, ['draft', 'diajukan', 'ditolak'], true))
                ->url(fn (): string => route('admin.pengajuan-spj.cetak', $this->record))
                ->openUrlInNewTab(),

            DeleteAction::make()
                ->visible(fn (): bool => $this->record->status === 'draft'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
