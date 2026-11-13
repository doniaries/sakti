<?php

namespace App\Filament\Resources\MasterSubKegiatans\Pages;

use App\Filament\Resources\MasterSubKegiatans\MasterSubKegiatanResource;
use App\Models\MasterSubKegiatan;
use App\Models\PengaturanAplikasi;
use Filament\Resources\Pages\CreateRecord;

class CreateMasterSubKegiatan extends CreateRecord
{
    protected static string $resource = MasterSubKegiatanResource::class;

    public function mount(): void
    {
        $tahunAktif = (int) PengaturanAplikasi::getTahunAnggaranAktif();
        $isDpaLocked = MasterSubKegiatan::where('tahun_anggaran', $tahunAktif)->exists()
            && ! MasterSubKegiatan::where('tahun_anggaran', $tahunAktif)->where('is_locked', false)->exists();

        abort_if($isDpaLocked, 403, "DPA Tahun {$tahunAktif} sedang dikunci.");

        parent::mount();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

