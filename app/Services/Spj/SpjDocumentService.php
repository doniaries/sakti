<?php

namespace App\Services\Spj;

use App\Models\PengajuanSpj;
use App\Models\PengaturanAplikasi;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfInstance;

class SpjDocumentService
{
    /**
     * Generate PDF SPJ: Nota Pesanan, Kwitansi, Nota Pencairan Dana (NPD)
     * Dapat difilter per jenis dokumen: 'pesanan', 'kwitansi', 'npd', atau 'semua'
     */
    public function generatePdf(PengajuanSpj $spj, ?string $tipe = 'semua'): DomPdfInstance
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

        $pdf = Pdf::loadView('pdf.spj-dokumen', [
            'spj' => $spj,
            'pengaturan' => $pengaturan,
            'totalNominal' => $totalNominal,
            'terbilang' => $terbilang,
            'tipeDokumen' => $tipe ?: 'semua',
        ]);

        // Standar Ukuran Kertas SAKTI (Kabupaten Sijunjung):
        // 1. Kertas F4 / Folio Standar Indonesia = 215 mm x 330 mm
        //    Konversi ke points (72 pt / 25.4 mm):
        //    Lebar:  215 mm = 609.45 pt
        //    Tinggi: 330 mm = 935.43 pt
        //
        // 2. Kertas F4 Bagi 2 Landscape (Kwitansi Setengah Folio):
        //    Lembar F4 dipotong 2 horizontal = 215 mm x 165 mm
        //    Lebar:  215 mm = 609.45 pt
        //    Tinggi: 165 mm = 467.72 pt
        if ($tipe === 'kwitansi') {
            return $pdf->setPaper([0, 0, 609.45, 467.72]);
        }

        // Surat Pesanan, NPD, dan SPJ Lengkap: F4 Portrait (215 mm x 330 mm)
        return $pdf->setPaper([0, 0, 609.45, 935.43], 'portrait');
    }
}
