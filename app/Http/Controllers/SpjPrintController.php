<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSpj;
use App\Models\PengaturanAplikasi;
use App\Services\Spj\SpjDocumentService;
use App\Services\Spj\SpjWorkflowService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SpjPrintController extends Controller
{
    /**
     * Preview interaktif dokumen SPJ dalam format HTML A4 (mencegah auto-download)
     */
    public function preview(Request $request, PengajuanSpj $record): View
    {
        abort_if(
            $record->status === 'ditolak',
            403,
            'Dokumen tidak dapat dilihat karena pengajuan SPJ telah ditolak permanen.'
        );

        $record->loadMissing([
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
        ]);

        $pengaturan = PengaturanAplikasi::first();
        $totalNominal = $record->total_nilai_realisasi ?: $record->total_nilai_rencana;
        $terbilang = SpjWorkflowService::terbilang((float) $totalNominal);
        $tipe = (string) $request->query('tipe', 'semua');

        return view('pdf.spj-dokumen', [
            'spj' => $record,
            'pengaturan' => $pengaturan,
            'totalNominal' => $totalNominal,
            'terbilang' => $terbilang,
            'tipeDokumen' => $tipe ?: 'semua',
            'isPreview' => true,
        ]);
    }

    /**
     * Download / Stream file PDF resmi menggunakan DomPDF
     */
    public function print(Request $request, PengajuanSpj $record, SpjDocumentService $documentService): Response
    {
        abort_if(
            $record->status === 'ditolak',
            403,
            'Dokumen tidak dapat dicetak karena pengajuan SPJ telah ditolak permanen.'
        );

        $tipe = (string) $request->query('tipe', 'semua');
        $pdf = $documentService->generatePdf($record, $tipe);
        $prefix = match ($tipe) {
            'pesanan' => 'SURAT-PESANAN-',
            'kwitansi' => 'KWITANSI-',
            'npd' => 'NPD-',
            default => 'SPJ-LENGKAP-',
        };
        $filename = $prefix . str_replace(['/', '\\'], '-', $record->nomor_pengajuan) . '.pdf';

        return $pdf->stream($filename);
    }
}
