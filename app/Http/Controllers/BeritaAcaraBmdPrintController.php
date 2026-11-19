<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BeritaAcaraBmd;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BeritaAcaraBmdPrintController extends Controller
{
    public function cetak(Request $request, BeritaAcaraBmd $beritaAcaraBmd): Response
    {
        $beritaAcaraBmd->load('items');

        $logoPath = public_path('images/logo-sijunjung.png');
        $logoBase64 = file_exists($logoPath)
            ? 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoPath))
            : null;

        $pdf = Pdf::loadView('pdf.berita-acara-bmd', [
            'ba' => $beritaAcaraBmd,
            'logoBase64' => $logoBase64,
        ])->setPaper('a4', 'portrait');

        $filename = 'BA_' . strtoupper($beritaAcaraBmd->jenis) . '_' . preg_replace('/[^A-Za-z0-9\-]/', '_', $beritaAcaraBmd->nomor_surat) . '.pdf';

        if ($request->boolean('preview')) {
            return $pdf->stream($filename);
        }

        return $pdf->download($filename);
    }
}
