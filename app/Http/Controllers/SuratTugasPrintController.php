<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Instansi;
use App\Models\PengaturanAplikasi;
use App\Models\SuratTugas;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SuratTugasPrintController extends Controller
{
    public function verifikasi(Request $request, ?string $kode = null): View
    {
        $input = trim((string) ($request->query('nomor') ?? $request->query('kode') ?? $kode ?? ''));
        $surat = null;

        if (filled($input)) {
            $decoded = base64_decode(strtr($input, '-_', '+/'), true);
            $idDecoded = ($decoded !== false && ctype_digit($decoded)) ? (int) $decoded : null;

            $surat = SuratTugas::query()
                ->with(['pegawais.jabatan', 'penandatangan.jabatan'])
                ->where('nomor_surat', $input)
                ->when($idDecoded !== null, fn ($q) => $q->orWhere('id', $idDecoded))
                ->when(ctype_digit($input), fn ($q) => $q->orWhere('id', (int) $input))
                ->first();
        }

        return view('dokumen.verifikasi-surat-tugas', [
            'surat' => $surat,
            'instansi' => Instansi::first(),
            'pengaturan' => PengaturanAplikasi::first(),
            'kode' => $input,
            'isValid' => $surat !== null,
        ]);
    }
    public function cetak(Request $request, SuratTugas $suratTugas)
    {
        $suratTugas->tandaiDibacaOleh(Auth::id());

        // Jika PDF resmi ber-TTE SRIKANDI sudah di-upload dan bukan permintaan draf, tampilkan langsung file TTE tersebut
        if (! $request->boolean('draft') && filled($suratTugas->file_tte) && Storage::disk('public')->exists($suratTugas->file_tte)) {
            $path = Storage::disk('public')->path($suratTugas->file_tte);
            $tanggal = $suratTugas->tanggal_mulai?->format('Y-m-d') ?? now()->format('Y-m-d');
            $suffix = filled($suratTugas->nomor_surat) ? str($suratTugas->nomor_surat)->slug() : 'id-' . $suratTugas->id;
            $filename = "Surat-Tugas-TTE-{$tanggal}-{$suffix}.pdf";

            return response()->file($path, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => ($request->boolean('download') ? 'attachment' : 'inline') . '; filename="' . $filename . '"',
            ]);
        }

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
            'qrBase64' => $suratTugas->status === 'terbit' ? $suratTugas->getQrTrackingBase64() : null,
            'trackingUrl' => $suratTugas->getTrackingUrl(),
            'srikandiMode' => $request->boolean('srikandi'),
        ];

        if ($request->boolean('preview')) {
            return view('pdf.surat-tugas', $viewData);
        }

        $pdf = Pdf::loadView('pdf.surat-tugas', $viewData)->setPaper([0, 0, 609.45, 935.43], 'portrait');
        $tanggal = $suratTugas->tanggal_mulai?->format('Y-m-d') ?? now()->format('Y-m-d');
        $suffix = filled($suratTugas->nomor_surat) ? str($suratTugas->nomor_surat)->slug() : 'draft-' . $suratTugas->id;
        $namaFile = "Surat-Tugas-{$tanggal}-{$suffix}.pdf";

        return $pdf->stream($namaFile);
    }

    public function cetakLaporan(SuratTugas $suratTugas)
    {
        $suratTugas->load(['pegawais', 'laporans.user', 'penanggungJawab.jabatan']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.laporan-surat-tugas', [
            'surat' => $suratTugas,
            'instansi' => \App\Models\Instansi::query()->first(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('laporan-kegiatan-' . ($suratTugas->id) . '.pdf');
    }

    public function downloadWord(SuratTugas $suratTugas)
    {
        $suratTugas->load(['pegawais.jabatan', 'pegawais.bidang', 'penandatangan.jabatan']);
        $instansi = Instansi::first();

        $logoPath = null;
        if (! empty($instansi?->logo) && file_exists(public_path('storage/' . $instansi->logo))) {
            $logoPath = asset('storage/' . $instansi->logo);
        } elseif (file_exists(public_path('images/logo-kabupaten.png'))) {
            $logoPath = asset('images/logo-kabupaten.png');
        }

        $html = view('pdf.surat-tugas', [
            'surat' => $suratTugas,
            'instansi' => $instansi,
            'logoPath' => $logoPath,
            'srikandiMode' => true,
        ])->render();

        $filename = 'Template-SRIKANDI-' . str($suratTugas->nama_kegiatan)->limit(40, '')->slug() . '.doc';

        return response($html, 200, [
            'Content-Type' => 'application/msword; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
