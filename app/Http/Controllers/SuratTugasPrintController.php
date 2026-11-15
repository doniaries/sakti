<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Instansi;
use App\Models\SuratTugas;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SuratTugasPrintController extends Controller
{
    public function cetak(Request $request, SuratTugas $suratTugas)
    {
        $suratTugas->load(['pegawais.jabatan', 'pegawais.bidang', 'penandatangan.jabatan']);
        $instansi = Instansi::first();

        $logoPath = null;
        if (! empty($instansi?->logo) && file_exists(public_path('storage/' . $instansi->logo))) {
            $logoPath = $request->boolean('preview')
                ? asset('storage/' . $instansi->logo)
                : public_path('storage/' . $instansi->logo);
        } elseif (file_exists(public_path('images/logo-kabupaten.png'))) {
            $logoPath = $request->boolean('preview')
                ? asset('images/logo-kabupaten.png')
                : public_path('images/logo-kabupaten.png');
        }

        $viewData = [
            'surat' => $suratTugas,
            'instansi' => $instansi,
            'logoPath' => $logoPath,
        ];

        if ($request->boolean('preview')) {
            return view('pdf.surat-tugas', $viewData);
        }

        $pdf = Pdf::loadView('pdf.surat-tugas', $viewData)->setPaper('a4', 'portrait');
        $namaFile = 'Surat-Tugas-' . str($suratTugas->nomor_surat)->slug() . '.pdf';

        return $pdf->stream($namaFile);
    }
}
