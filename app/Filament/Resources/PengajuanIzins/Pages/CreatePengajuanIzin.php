<?php

namespace App\Filament\Resources\PengajuanIzins\Pages;

use App\Filament\Resources\PengajuanIzins\PengajuanIzinResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePengajuanIzin extends CreateRecord
{
    protected static string $resource = PengajuanIzinResource::class;

    protected function getRedirectUrl(): string
    {
        if ($this->record->jenisIzin?->kategori === 'cuti') {
            return route('pengajuan-izin.cetak-pdf', $this->record);
        }

        if ($this->record->jenisIzin?->kategori === 'cuti') {
            return route('pengajuan-izin.cetak-pdf', $this->record);
        }

        return $this->getResource()::getUrl('index');
    }
}
