<?php

namespace App\Filament\Resources\PengajuanSpjs\Pages;

use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreatePengajuanSpj extends CreateRecord
{
    protected static string $resource = PengajuanSpjResource::class;

    protected Width | string | null $maxContentWidth = Width::Full;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Draft SPJ Berhasil Dibuat')
            ->body('Data pengajuan tersimpan sebagai Draft. Silakan klik tombol "Ajukan Pesanan" pada tabel untuk meneruskan berkas ke PPTK.');
    }
}

