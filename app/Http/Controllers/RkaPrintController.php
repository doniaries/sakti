<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\Instansi;
use App\Models\MasterSubKegiatan;
use App\Models\PengaturanAplikasi;
use App\Models\RincianAnggaranBelanja;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class RkaPrintController extends Controller
{
    /**
     * Preview interaktif dokumen Cetak RKA SIPD-RI sebelum dicetak/disimpan ke PDF.
     */
    public function preview(Request $request): View
    {
        $tahun = (int) $request->query('tahun', date('Y'));
        $subKegiatanId = $request->query('sub_kegiatan_id');
        $bidangId = $request->query('bidang_id');

        $query = RincianAnggaranBelanja::query()
            ->with(['bidang', 'subKegiatan.kegiatan.program', 'kodeRekening', 'masterBarang.satuan'])
            ->where('tahun_anggaran', $tahun);

        if ($subKegiatanId) {
            $query->where('sub_kegiatan_id', $subKegiatanId);
        }

        if ($bidangId) {
            $query->where('bidang_id', $bidangId);
        }

        $items = $query->orderBy('kode_rekening_id', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Ambil data sub kegiatan terpilih atau pertama
        $subKegiatan = $subKegiatanId 
            ? MasterSubKegiatan::with('kegiatan.program')->find($subKegiatanId) 
            : $items->first()?->subKegiatan ?? MasterSubKegiatan::with('kegiatan.program')->first();

        $bidang = $bidangId 
            ? Bidang::find($bidangId) 
            : $subKegiatan?->kegiatan?->bidang ?? Bidang::where('nama', 'LIKE', '%Pemasaran%')->first();

        $instansi = Instansi::first();
        $pengaturan = PengaturanAplikasi::first();

        // Mengelompokkan item belanja per kode rekening belanja
        $groupedItems = $items->groupBy(fn ($item) => $item->kodeRekening?->kode_lengkap ?? 'Lainnya');

        $totalPagu = $items->sum('pagu_total');

        return view('pdf.rka-dokumen', [
            'tahun' => $tahun,
            'subKegiatan' => $subKegiatan,
            'bidang' => $bidang,
            'instansi' => $instansi,
            'pengaturan' => $pengaturan,
            'items' => $items,
            'groupedItems' => $groupedItems,
            'totalPagu' => $totalPagu,
        ]);
    }
}

