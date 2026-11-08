<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Instansi;
use App\Models\PengaturanAplikasi;
use App\Models\User;
use App\Services\Laporan\LrfkCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LaporanPrintController extends Controller
{
    public function __construct(
        protected LrfkCalculationService $calculationService
    ) {}

    /**
     * Cetak Laporan Realisasi Fisik dan Keuangan (LRFK) Landscape PDF
     */
    public function cetakLrfk(Request $request): Response
    {
        $tahun = (int) ($request->query('tahun') ?: date('Y'));
        $bulan = (int) ($request->query('bulan') ?: date('n'));
        $bidangId = $request->query('bidang') ? (int) $request->query('bidang') : null;
        $tipe = (string) ($request->query('tipe') ?: 'lengkap'); // 'matriks', 'lampiran1', 'kemajuan', 'lengkap'

        $matriks = $this->calculationService->getMatriksLrfkKegiatan($tahun, $bulan, $bidangId);
        $lampiran1 = $this->calculationService->getLampiran1JenisBelanja($tahun, $bulan, $bidangId);
        $kemajuan = $this->calculationService->getKemajuanPekerjaanFisik($tahun, $bulan, $bidangId);

        $instansi = Instansi::first() ?? PengaturanAplikasi::first();
        $namaBulan = Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F');
        $tglKondisi = Carbon::create($tahun, $bulan)->endOfMonth()->locale('id')->translatedFormat('d F Y');
        $tglCetak = Carbon::now()->locale('id')->translatedFormat('d F Y');

        $kadis = User::role('pa')->first();
        $sekretaris = User::role('pptk')->whereHas('bidang', fn ($b) => $b->where('nama', 'like', '%Sekretariat%'))->first();
        $bendahara = User::role('bendahara')->first();

        $pdf = Pdf::loadView('pdf.laporan-lrfk', [
            'tahun' => $tahun,
            'bulan' => $bulan,
            'namaBulan' => $namaBulan,
            'tglKondisi' => $tglKondisi,
            'tglCetak' => $tglCetak,
            'instansi' => $instansi,
            'matriks' => $matriks,
            'lampiran1' => $lampiran1,
            'kemajuan' => $kemajuan,
            'tipe' => $tipe,
            'kadis' => $kadis,
            'sekretaris' => $sekretaris,
            'bendahara' => $bendahara,
        ]);

        // Kertas F4 / Folio Landscape (330 mm x 215 mm) = 935.43 pt x 609.45 pt
        $pdf->setPaper([0, 0, 609.45, 935.43], 'landscape');

        $filename = "LRFK_{$namaBulan}_{$tahun}.pdf";

        return $pdf->stream($filename);
    }

    /**
     * Cetak Laporan Pertanggungjawaban Fungsional (LPJ Fungsional Bendahara) Landscape PDF
     */
    public function cetakLpjFungsional(Request $request): Response
    {
        $tahun = (int) ($request->query('tahun') ?: date('Y'));
        $bulan = (int) ($request->query('bulan') ?: date('n'));

        $data = $this->calculationService->getLpjFungsional($tahun, $bulan);

        $instansi = Instansi::first() ?? PengaturanAplikasi::first();
        $namaBulan = Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F');
        $tglKondisi = Carbon::create($tahun, $bulan)->endOfMonth()->locale('id')->translatedFormat('d F Y');
        $tglCetak = Carbon::now()->locale('id')->translatedFormat('d F Y');

        $kadis = User::role('pa')->first();
        $bendahara = User::role('bendahara')->first();

        $pdf = Pdf::loadView('pdf.laporan-fungsional', [
            'tahun' => $tahun,
            'bulan' => $bulan,
            'namaBulan' => $namaBulan,
            'tglKondisi' => $tglKondisi,
            'tglCetak' => $tglCetak,
            'instansi' => $instansi,
            'data' => $data,
            'kadis' => $kadis,
            'bendahara' => $bendahara,
        ]);

        // Kertas F4 Landscape
        $pdf->setPaper([0, 0, 609.45, 935.43], 'landscape');

        $filename = "LPJ_Fungsional_{$namaBulan}_{$tahun}.pdf";

        return $pdf->stream($filename);
    }
}

