<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BukuInventaris;
use App\Models\Instansi;
use App\Models\PengaturanAplikasi;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class BukuInventarisPrintController extends Controller
{
    /**
     * Cetak Buku Inventaris Gabungan ke PDF (Kertas F4 / Legal Landscape)
     */
    public function cetak(Request $request): Response
    {
        /** @var User|null $user */
        $user = auth()->user();
        abort_unless($user?->canViewAset() ?? false, 403);

        ini_set('memory_limit', '512M');
        set_time_limit(180);

        $query = BukuInventaris::query()->orderBy('nomor_urut', 'asc');

        if ($request->filled('tahun')) {
            $query->where('tahun_pembelian', (int) $request->query('tahun'));
        }

        if ($request->filled('keadaan')) {
            $query->where('keadaan_barang', $request->query('keadaan'));
        }

        if ($request->filled('asal')) {
            $query->where('asal_perolehan', $request->query('asal'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'like', "%{$search}%")
                    ->orWhere('kode_barang', 'like', "%{$search}%")
                    ->orWhere('register', 'like', "%{$search}%")
                    ->orWhere('merk_type', 'like', "%{$search}%")
                    ->orWhere('keterangan', 'like', "%{$search}%");
            });
        }

        $items = $query->get();
        $totalHarga = (float) $items->sum('harga');
        $totalBarang = (int) $items->sum('jumlah_barang');

        $instansi = Instansi::first() ?? PengaturanAplikasi::first();

        // Data Penandatangan Default sesuai Lampiran Resmi
        $namaKadis = $request->query('nama_kadis', 'AFRINELDI, SH');
        $nipKadis = $request->query('nip_kadis', '19720407 199202 1 002');
        $jabatanKadis = $request->query('jabatan_kadis', 'Kepala Dinas Parpora Kab. Sijunjung');

        $namaPengurus = $request->query('nama_pengurus', 'IMELDA SANGGETA, A. Md');
        $nipPengurus = $request->query('nip_pengurus', '19751009 200501 2 002');
        $jabatanPengurus = $request->query('jabatan_pengurus', 'Pengurus Barang');

        $tanggalCetak = $request->query('tanggal', '30 June 2026');

        // Logo Kabupaten Base64
        $logoBase64 = null;
        $logoPath = public_path('images/logo-kabupaten.png');
        if (file_exists($logoPath)) {
            $logoBase64 = base64_encode(file_get_contents($logoPath));
        }

        $viewData = [
            'items' => $items,
            'totalHarga' => $totalHarga,
            'totalBarang' => $totalBarang,
            'instansi' => $instansi,
            'logoBase64' => $logoBase64,
            'provinsi' => 'PROVINSI SUMATERA BARAT',
            'kabupaten' => 'PEMERINTAH KABUPATEN SIJUNJUNG',
            'bidang' => 'Bidang Pariwisata',
            'unitOrganisasi' => 'DINAS PARIWISATA, PEMUDA DAN OLAH RAGA',
            'subUnitOrganisasi' => 'DINAS PARIWISATA, PEMUDA DAN OLAH RAGA',
            'upb' => 'DINAS PARIWISATA, PEMUDA DAN OLAH RAGA',
            'namaKadis' => $namaKadis,
            'nipKadis' => $nipKadis,
            'jabatanKadis' => $jabatanKadis,
            'namaPengurus' => $namaPengurus,
            'nipPengurus' => $nipPengurus,
            'jabatanPengurus' => $jabatanPengurus,
            'tanggalCetak' => $tanggalCetak,
        ];

        // Format HTML View jika diminta pratinjau langsung di browser
        if ($request->boolean('preview')) {
            return response()->view('pdf.buku-inventaris', $viewData);
        }

        $pdf = Pdf::loadView('pdf.buku-inventaris', $viewData);

        // Kertas Folio / F4 Landscape
        $pdf->setPaper([0, 0, 609.45, 935.43], 'landscape');

        $filename = 'Buku_Inventaris_Gabungan_Disparpora_' . date('Ymd_His') . '.pdf';

        if ($request->boolean('download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}
