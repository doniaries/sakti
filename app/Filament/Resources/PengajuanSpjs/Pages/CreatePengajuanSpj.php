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

    public function mount(): void
    {
        parent::mount();

        $rkaId = request()->query('rka_id');
        if ($rkaId) {
            $rka = \App\Models\RincianAnggaranBelanja::with(['subKegiatan.kegiatan.program', 'kodeRekening'])->find($rkaId);
            if ($rka) {
                $subKeg = $rka->subKegiatan;
                $keg = $subKeg?->kegiatan;
                $bidangId = $rka->bidang_id ?? $keg?->bidang_id;
                $kuantitasDefault = min(1, (float) $rka->sisa_volume);

                $this->form->fill([
                    'tahun_anggaran' => $rka->tahun_anggaran,
                    'bidang_id' => $bidangId,
                    'program_id' => $keg?->program_id,
                    'kegiatan_id' => $keg?->id,
                    'sub_kegiatan_id' => $rka->sub_kegiatan_id,
                    'details' => [
                        [
                            'kode_rekening_id' => $rka->kode_rekening_id,
                            'rincian_anggaran_belanja_id' => $rka->id,
                            'nama_item_belanja' => $rka->nama_item,
                            'satuan' => $rka->satuan,
                            'harga_satuan' => (float) $rka->harga_satuan,
                            'kuantitas' => $kuantitasDefault,
                            'subtotal' => (float) $rka->harga_satuan * $kuantitasDefault,
                            'stok_tersedia' => (float) $rka->sisa_volume,
                        ],
                    ],
                ]);
            }
        }
    }

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

