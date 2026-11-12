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
     * Pratinjau Dokumen HTML LRFK untuk Iframe (Bebas Intersepsi IDM)
     */
    public function previewLrfk(Request $request): \Illuminate\Contracts\View\View
    {
        $tahun = (int) ($request->query('tahun') ?: date('Y'));
        $bulan = (int) ($request->query('bulan') ?: date('n'));
        $tipe = (string) ($request->query('tipe') ?: 'lengkap');
        $penandatangan = (string) ($request->query('penandatangan') ?: 'sekretaris');
        $bidangId = $request->query('bidang_id') ? (int) $request->query('bidang_id') : null;
        $programId = $request->query('program_id') ? (int) $request->query('program_id') : null;
        $kegiatanId = $request->query('kegiatan_id') ? (int) $request->query('kegiatan_id') : null;

        $matriks = $this->calculationService->getMatriksLrfkKegiatan($tahun, $bulan, $bidangId, $programId, $kegiatanId);
        $lampiran1 = $this->calculationService->getLampiran1JenisBelanja($tahun, $bulan, $bidangId, $programId, $kegiatanId);
        $kemajuan = $this->calculationService->getKemajuanPekerjaanFisik($tahun, $bulan, $bidangId, $programId, $kegiatanId);

        $instansi = Instansi::first() ?? PengaturanAplikasi::first();
        $namaBulan = Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F');
        $tglKondisi = Carbon::create($tahun, $bulan)->endOfMonth()->locale('id')->translatedFormat('d F Y');
        $tglCetak = Carbon::now()->locale('id')->translatedFormat('d F Y');

        $kadis = User::role('pa')->first();
        $sekretaris = User::where('name', 'like', '%Desmawati%')->first()
            ?? User::role('pptk')->whereHas('bidang', fn ($b) => $b->where('nama', 'like', '%Sekretariat%'))->first();
        $bendahara = User::role('bendahara')->first();

        return view('pdf.laporan-lrfk', [
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
            'penandatangan' => $penandatangan,
            'kadis' => $kadis,
            'sekretaris' => $sekretaris,
            'bendahara' => $bendahara,
            'isPreview' => true,
        ]);
    }

    /**
     * Cetak Laporan Realisasi Fisik dan Keuangan (LRFK) Landscape PDF
     */
    public function cetakLrfk(Request $request): Response
    {
        $tahun = (int) ($request->query('tahun') ?: date('Y'));
        $bulan = (int) ($request->query('bulan') ?: date('n'));
        $tipe = (string) ($request->query('tipe') ?: 'lengkap');
        $penandatangan = (string) ($request->query('penandatangan') ?: 'sekretaris');
        $bidangId = $request->query('bidang_id') ? (int) $request->query('bidang_id') : null;
        $programId = $request->query('program_id') ? (int) $request->query('program_id') : null;
        $kegiatanId = $request->query('kegiatan_id') ? (int) $request->query('kegiatan_id') : null;

        $matriks = $this->calculationService->getMatriksLrfkKegiatan($tahun, $bulan, $bidangId, $programId, $kegiatanId);
        $lampiran1 = $this->calculationService->getLampiran1JenisBelanja($tahun, $bulan, $bidangId, $programId, $kegiatanId);
        $kemajuan = $this->calculationService->getKemajuanPekerjaanFisik($tahun, $bulan, $bidangId, $programId, $kegiatanId);

        $instansi = Instansi::first() ?? PengaturanAplikasi::first();
        $namaBulan = Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F');
        $tglKondisi = Carbon::create($tahun, $bulan)->endOfMonth()->locale('id')->translatedFormat('d F Y');
        $tglCetak = Carbon::now()->locale('id')->translatedFormat('d F Y');

        $kadis = User::role('pa')->first();
        $sekretaris = User::where('name', 'like', '%Desmawati%')->first()
            ?? User::role('pptk')->whereHas('bidang', fn ($b) => $b->where('nama', 'like', '%Sekretariat%'))->first();
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
            'penandatangan' => $penandatangan,
            'kadis' => $kadis,
            'sekretaris' => $sekretaris,
            'bendahara' => $bendahara,
        ]);

        $pdf->setPaper([0, 0, 609.45, 935.43], 'landscape');

        $filename = "LRFK_{$namaBulan}_{$tahun}.pdf";

        if ($request->query('download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    /**
     * Pratinjau Dokumen HTML LPJ Fungsional untuk Iframe (Bebas Intersepsi IDM)
     */
    public function previewLpjFungsional(Request $request): \Illuminate\Contracts\View\View
    {
        $tahun = (int) ($request->query('tahun') ?: date('Y'));
        $bulan = (int) ($request->query('bulan') ?: date('n'));
        $bidangId = $request->query('bidang_id') ? (int) $request->query('bidang_id') : null;
        $programId = $request->query('program_id') ? (int) $request->query('program_id') : null;
        $kegiatanId = $request->query('kegiatan_id') ? (int) $request->query('kegiatan_id') : null;
        $subKegiatanId = $request->query('sub_kegiatan_id') ? (int) $request->query('sub_kegiatan_id') : null;

        $data = $this->calculationService->getLpjFungsional($tahun, $bulan, $bidangId, $programId, $kegiatanId, $subKegiatanId);

        $instansi = Instansi::first() ?? PengaturanAplikasi::first();
        $namaBulan = Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F');
        $tglKondisi = Carbon::create($tahun, $bulan)->endOfMonth()->locale('id')->translatedFormat('d F Y');
        $tglCetak = Carbon::now()->locale('id')->translatedFormat('d F Y');

        $kadis = User::role('pa')->first();
        $bendahara = User::role('bendahara')->first();

        return view('pdf.laporan-fungsional', [
            'tahun' => $tahun,
            'bulan' => $bulan,
            'namaBulan' => $namaBulan,
            'tglKondisi' => $tglKondisi,
            'tglCetak' => $tglCetak,
            'instansi' => $instansi,
            'data' => $data,
            'kadis' => $kadis,
            'bendahara' => $bendahara,
            'isPreview' => true,
        ]);
    }

    /**
     * Cetak Laporan Pertanggungjawaban Fungsional (LPJ Fungsional Bendahara) Landscape PDF
     */
    public function cetakLpjFungsional(Request $request): Response
    {
        $tahun = (int) ($request->query('tahun') ?: date('Y'));
        $bulan = (int) ($request->query('bulan') ?: date('n'));
        $bidangId = $request->query('bidang_id') ? (int) $request->query('bidang_id') : null;
        $programId = $request->query('program_id') ? (int) $request->query('program_id') : null;
        $kegiatanId = $request->query('kegiatan_id') ? (int) $request->query('kegiatan_id') : null;
        $subKegiatanId = $request->query('sub_kegiatan_id') ? (int) $request->query('sub_kegiatan_id') : null;

        $data = $this->calculationService->getLpjFungsional($tahun, $bulan, $bidangId, $programId, $kegiatanId, $subKegiatanId);

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

        if ($request->query('download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}

