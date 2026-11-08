<?php

namespace App\Filament\Resources\RincianAnggaranBelanjas\Pages;

use App\Filament\Resources\RincianAnggaranBelanjas\RincianAnggaranBelanjaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRincianAnggaranBelanja extends CreateRecord
{
    protected static string $resource = RincianAnggaranBelanjaResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        // Fitur Cepat Filament: Simpan konteks pembebanan anggaran terakhir ke session
        // agar saat user menginput banyak item berturut-turut (Buat & buat lainnya),
        // Sub Kegiatan, Sumber Dana, dan Tahun tidak perlu dipilih ulang.
        session()->put('rka_last_context', [
            'tahun_anggaran' => $this->data['tahun_anggaran'] ?? null,
            'sumber_dana' => $this->data['sumber_dana'] ?? null,
            'bidang_id' => $this->data['bidang_id'] ?? null,
            'sub_kegiatan_id' => $this->data['sub_kegiatan_id'] ?? null,
            'kode_rekening_id' => $this->data['kode_rekening_id'] ?? null,
        ]);
    }
}
