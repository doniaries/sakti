<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\PengajuanSpj;
use App\Services\ActivityLogger;

class PengajuanSpjObserver
{
    public function created(PengajuanSpj $spj): void
    {
        ActivityLogger::log(
            event: 'create',
            module: 'Pengajuan SPJ',
            description: "Membuat pengajuan SPJ baru nomor: {$spj->nomor_pengajuan} untuk keperluan: {$spj->keperluan}",
            subject: $spj,
            properties: [
                'nomor_pengajuan' => $spj->nomor_pengajuan,
                'total_belanja' => $spj->total_belanja,
                'status' => $spj->status,
                'jenis_belanja' => $spj->jenis_belanja,
            ]
        );
    }

    public function updated(PengajuanSpj $spj): void
    {
        if ($spj->wasChanged('status')) {
            $status = (string) $spj->status;
            $event = match ($status) {
                'menunggu_realisasi', 'menunggu_persetujuan_pa', 'menunggu_verifikasi_bendahara', 'disetujui' => 'verify',
                'revisi_ppk', 'revisi_pa', 'revisi_bendahara' => 'revisi',
                'dicairkan' => 'cair',
                'ditolak' => 'tolak',
                default => 'update',
            };

            $desc = match ($status) {
                'menunggu_realisasi' => "Surat Pesanan SPJ {$spj->nomor_pengajuan} disetujui PPTK (Menunggu Realisasi Belanja)",
                'menunggu_verifikasi_ppk' => "Pengajuan berkas realisasi SPJ {$spj->nomor_pengajuan} diajukan untuk verifikasi PPK",
                'menunggu_persetujuan_pa' => "Dokumen SPJ {$spj->nomor_pengajuan} diverifikasi & disetujui PPK, diteruskan ke PA",
                'menunggu_verifikasi_bendahara' => "Dokumen SPJ {$spj->nomor_pengajuan} disetujui & ditandatangani PA, diteruskan ke Bendahara Pengeluaran",
                'disetujui' => "Dokumen SPJ {$spj->nomor_pengajuan} diverifikasi & disetujui Bendahara (Siap Dicairkan)",
                'dicairkan' => "Dana SPJ {$spj->nomor_pengajuan} sebesar Rp " . number_format((float) ($spj->nominal_cair ?: $spj->total_belanja), 0, ',', '.') . " berhasil dicairkan oleh Bendahara",
                'revisi_ppk' => "Dokumen SPJ {$spj->nomor_pengajuan} dikembalikan oleh PPK untuk diperbaiki (Revisi: {$spj->catatan_penolakan})",
                'revisi_pa' => "Dokumen SPJ {$spj->nomor_pengajuan} dikembalikan oleh PA untuk diperbaiki (Revisi: {$spj->catatan_penolakan})",
                'revisi_bendahara' => "Dokumen SPJ {$spj->nomor_pengajuan} dikembalikan oleh Bendahara untuk diperbaiki (Revisi: {$spj->catatan_penolakan})",
                'ditolak' => "Pengajuan SPJ {$spj->nomor_pengajuan} ditolak permanen (Alasan: {$spj->catatan_penolakan})",
                default => "Status SPJ {$spj->nomor_pengajuan} diperbarui menjadi {$status}",
            };

            ActivityLogger::log(
                event: $event,
                module: 'Pengajuan SPJ',
                description: $desc,
                subject: $spj,
                properties: [
                    'status_baru' => $status,
                    'status_lama' => $spj->getOriginal('status'),
                    'catatan' => $spj->catatan_penolakan,
                ]
            );
        } elseif ($spj->wasChanged(['total_belanja', 'master_toko_id', 'rka_id', 'sub_kegiatan_id'])) {
            ActivityLogger::log(
                event: 'update',
                module: 'Pengajuan SPJ',
                description: "Memperbarui rincian data pengajuan SPJ nomor: {$spj->nomor_pengajuan}",
                subject: $spj,
                properties: [
                    'perubahan' => array_keys($spj->getChanges()),
                    'total_belanja' => $spj->total_belanja,
                ]
            );
        }
    }

    public function deleted(PengajuanSpj $spj): void
    {
        ActivityLogger::log(
            event: 'delete',
            module: 'Pengajuan SPJ',
            description: "Menghapus pengajuan SPJ nomor: {$spj->nomor_pengajuan}",
            subject: $spj,
            properties: [
                'nomor_pengajuan' => $spj->nomor_pengajuan,
                'total_belanja' => $spj->total_belanja,
            ]
        );
    }
}

