<?php

namespace App\Http\Controllers;

use App\Models\PengajuanSpj;
use App\Services\Spj\SpjDocumentService;
use Illuminate\Http\Response;

class SpjPrintController extends Controller
{
    public function print(PengajuanSpj $record, SpjDocumentService $documentService): Response
    {
        abort_if(
            in_array($record->status, ['draft', 'diajukan', 'ditolak'], true),
            403,
            'Dokumen belum dapat dicetak karena Surat Pesanan belum disetujui dan ditandatangani oleh Kepala Bidang (PPTK).'
        );

        $pdf = $documentService->generatePdf($record);
        $filename = 'SPJ-' . str_replace(['/', '\\'], '-', $record->nomor_pengajuan) . '.pdf';

        return $pdf->stream($filename);
    }
}
