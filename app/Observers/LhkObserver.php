<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Lhk;
use App\Services\ActivityLogger;

class LhkObserver
{
    public function created(Lhk $lhk): void
    {
        $tgl = $lhk->tanggal ? \Illuminate\Support\Carbon::parse($lhk->tanggal)->format('d/m/Y') : '-';
        $namaUser = $lhk->user?->name ?? 'Pegawai';
        $statusStr = $lhk->status instanceof \BackedEnum ? $lhk->status->value : (string) $lhk->status;

        ActivityLogger::log(
            event: 'create',
            module: 'LHK',
            description: "Pegawai {$namaUser} membuat Laporan Harian Kinerja (LHK) untuk tanggal: {$tgl}",
            subject: $lhk,
            properties: [
                'tanggal' => $lhk->tanggal,
                'status' => $statusStr,
                'user_id' => $lhk->user_id,
            ]
        );
    }

    public function updated(Lhk $lhk): void
    {
        $tgl = $lhk->tanggal ? \Illuminate\Support\Carbon::parse($lhk->tanggal)->format('d/m/Y') : '-';
        $namaUser = $lhk->user?->name ?? 'Pegawai';

        if ($lhk->wasChanged('status')) {
            $status = $lhk->status instanceof \BackedEnum ? $lhk->status->value : (string) $lhk->status;
            $event = match ($status) {
                'disetujui' => 'verify',
                'ditolak' => 'tolak',
                default => 'update',
            };

            $desc = match ($status) {
                'disetujui' => "Atasan menyetujui LHK tanggal {$tgl} milik pegawai: {$namaUser}",
                'ditolak' => "Atasan menolak LHK tanggal {$tgl} milik pegawai: {$namaUser} (Catatan: {$lhk->catatan_atasan})",
                default => "Status LHK tanggal {$tgl} pegawai {$namaUser} diubah menjadi {$status}",
            };

            ActivityLogger::log(
                event: $event,
                module: 'LHK',
                description: $desc,
                subject: $lhk,
                properties: [
                    'status' => $status,
                    'catatan_atasan' => $lhk->catatan_atasan,
                ]
            );
        }
    }

    public function deleted(Lhk $lhk): void
    {
        $tgl = $lhk->tanggal ? \Illuminate\Support\Carbon::parse($lhk->tanggal)->format('d/m/Y') : '-';
        $namaUser = $lhk->user?->name ?? 'Pegawai';

        ActivityLogger::log(
            event: 'delete',
            module: 'LHK',
            description: "Menghapus LHK tanggal {$tgl} milik pegawai: {$namaUser}",
            subject: $lhk
        );
    }
}

