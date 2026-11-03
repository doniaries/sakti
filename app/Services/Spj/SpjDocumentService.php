<?php

namespace App\Services\Spj;

use App\Models\PengajuanSpj;
use App\Models\PengaturanAplikasi;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfInstance;

class SpjDocumentService
{
    /**
     * Generate 3 lembar PDF SPJ: Nota Pesanan, Kwitansi, Nota Pencairan Dana (NPD)
     */
    public function generatePdf(PengajuanSpj $spj): DomPdfInstance
    {
        $spj->loadMissing([
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
        ]);

        $pengaturan = PengaturanAplikasi::first();
        $totalNominal = $spj->total_nilai_realisasi ?: $spj->total_nilai_rencana;
        $terbilang = SpjWorkflowService::terbilang((float) $totalNominal);

        return Pdf::loadView('pdf.spj-dokumen', [
            'spj' => $spj,
            'pengaturan' => $pengaturan,
            'totalNominal' => $totalNominal,
            'terbilang' => $terbilang,
        ])->setPaper('a4', 'portrait');
    }
}
