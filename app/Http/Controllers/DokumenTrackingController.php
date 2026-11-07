<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSpj;
use App\Models\PengaturanAplikasi;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DokumenTrackingController extends Controller
{
    /**
     * Halaman pelacakan dan verifikasi keaslian dokumen transaksi/SPJ (Akses Publik via QR Code).
     */
    public function track(Request $request, ?string $kode = null): View
    {
        $input = $request->query('nomor')
            ?? $request->query('kode')
            ?? $request->query('id')
            ?? $kode;

        $nomorDitelusuri = trim((string) $input);
        $possibleDecoded = null;

        if (filled($nomorDitelusuri)) {
            // Cek jika nomor di-encode menggunakan Base64 URL-safe
            $decoded = base64_decode(strtr($nomorDitelusuri, '-_', '+/'), true);
            if ($decoded && preg_match('/^[A-Za-z0-9_\-\/\.\s]+$/', $decoded)) {
                $possibleDecoded = trim($decoded);
            }
        }

        $spj = null;
        if (filled($nomorDitelusuri)) {
            $spj = PengajuanSpj::query()
                ->with([
                    'bidang',
                    'kegiatan',
                    'subKegiatan',
                    'details.kodeRekening',
                    'details.masterBarang.satuan',
                    'user',
                    'pptk',
                    'ppk',
                    'pa',
                    'bendahara',
                    'masterToko',
                ])
                ->where('nomor_pengajuan', $nomorDitelusuri)
                ->orWhere('id', $nomorDitelusuri)
                ->when($possibleDecoded, fn ($q) => $q->orWhere('nomor_pengajuan', $possibleDecoded))
                ->first();
        }

        $pengaturan = PengaturanAplikasi::first();

        return view('dokumen.tracking', [
            'spj' => $spj,
            'pengaturan' => $pengaturan,
            'nomorDitelusuri' => $nomorDitelusuri,
            'isValid' => $spj !== null,
        ]);
    }
}

