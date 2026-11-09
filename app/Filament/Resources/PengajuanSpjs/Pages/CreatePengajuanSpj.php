<?php

namespace App\Filament\Resources\PengajuanSpjs\Pages;

use App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource;
use App\Models\PengajuanSpj;
use App\Models\RincianAnggaranBelanja;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Illuminate\Support\Str;

class CreatePengajuanSpj extends CreateRecord
{
    protected static string $resource = PengajuanSpjResource::class;

    protected Width | string | null $maxContentWidth = Width::Full;

    protected function fillForm(): void
    {
        parent::fillForm();

        $rkaId = request()->query('rka_id');
        if (! $rkaId) {
            return;
        }

        $rka = RincianAnggaranBelanja::with(['subKegiatan.kegiatan.program', 'kodeRekening'])->find($rkaId);
        if (! $rka) {
            return;
        }

        $subKeg = $rka->subKegiatan;
        $keg = $subKeg?->kegiatan;
        $bidangId = $rka->bidang_id ?? $keg?->bidang_id;
        $tahun = (int) ($rka->tahun_anggaran ?? date('Y'));

        // Deteksi jenis belanja otomatis dari nama item dan uraian rekening
        $namaLower = strtolower($rka->nama_item . ' ' . ($rka->kodeRekening?->uraian ?? ''));
        $jenisBelanja = 'barang_jasa';
        if (str_contains($namaLower, 'makan') || str_contains($namaLower, 'snack') || str_contains($namaLower, 'konsumsi')) {
            $jenisBelanja = 'makan_minum';
        } elseif (str_contains($namaLower, 'perjalanan') || str_contains($namaLower, 'sppd') || str_contains($namaLower, 'dinas')) {
            $jenisBelanja = 'sppd';
        } elseif (str_contains($namaLower, 'bbm') || str_contains($namaLower, 'bahan bakar') || str_contains($namaLower, 'bensin') || str_contains($namaLower, 'solar')) {
            $jenisBelanja = 'bbm';
        }

        $nomorPengajuan = PengajuanSpj::generateNomorPengajuan(
            $bidangId,
            $tahun,
            now()
        );

        $uuid = (string) Str::uuid();

        $prefillData = [
            'tahun_anggaran' => $tahun,
            'bidang_id' => $bidangId,
            'kegiatan_id' => $keg?->id,
            'sub_kegiatan_id' => $rka->sub_kegiatan_id,
            'metode_pengadaan' => 'non_katalog',
            'jenis_belanja' => $jenisBelanja,
            'nomor_pengajuan' => $nomorPengajuan,
            'tanggal_pengajuan' => now()->format('Y-m-d'),
            'details' => [
                $uuid => [
                    'kode_rekening_id' => $rka->kode_rekening_id,
                    'rincian_anggaran_belanja_id' => (string) $rka->id,
                    'master_barang_id' => $rka->master_barang_id,
                    'nama_item_belanja' => $rka->nama_item,
                    'satuan' => $rka->satuan,
                    'harga_satuan' => (float) $rka->harga_satuan,
                    'kuantitas' => null, // volume diisi manual oleh pengguna
                    'subtotal' => 0,
                    'stok_tersedia' => (float) $rka->sisa_volume,
                ],
            ],
        ];

        $this->data = array_merge($this->data ?? [], $prefillData);
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
