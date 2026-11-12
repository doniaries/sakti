@php
    $getFn = $get ?? null;
    $bulanVal = $getFn ? ($getFn('modal_bulan') ?: request('bulan', date('n'))) : request('bulan', date('n'));
    $penandatanganVal = $getFn ? ($getFn('modal_penandatangan') ?: 'sekretaris') : 'sekretaris';
    $tipeVal = $getFn ? ($getFn('modal_tipe') ?: 'lengkap') : 'lengkap';
    $tahunVal = $tahun ?? date('Y');
    $jenisLaporan = $jenis ?? 'lrfk';

    if ($jenisLaporan === 'fungsional') {
        // Preview URL mengembalikan HTML langsung (100% bebas intersepsi IDM otomatis)
        $previewUrl = route('admin.laporan.fungsional.preview', [
            'tahun' => $tahunVal,
            'bulan' => $bulanVal,
        ]);
        // Download URL hanya dipanggil saat tombol unduh/cetak ditekan
        $downloadUrl = route('admin.laporan.fungsional.cetak', [
            'tahun' => $tahunVal,
            'bulan' => $bulanVal,
        ]);
        $judulLaporan = 'Pratinjau LPJ Fungsional Bendahara';
    } else {
        // Preview URL mengembalikan HTML langsung (100% bebas intersepsi IDM otomatis)
        // Rekapan selalu satu dinas penuh tanpa pemisahan nama bidang
        $previewUrl = route('admin.laporan.lrfk.preview', [
            'tahun' => $tahunVal,
            'bulan' => $bulanVal,
            'penandatangan' => $penandatanganVal,
            'tipe' => $tipeVal,
        ]);
        // Download URL hanya dipanggil saat tombol unduh/cetak ditekan
        $downloadUrl = route('admin.laporan.lrfk.cetak', [
            'tahun' => $tahunVal,
            'bulan' => $bulanVal,
            'penandatangan' => $penandatanganVal,
            'tipe' => $tipeVal,
        ]);
        $judulLaporan = 'Pratinjau Laporan Realisasi Fisik dan Keuangan (LRFK)';
    }
@endphp

<div wire:key="pdf-embed-box-{{ md5($previewUrl) }}" class="w-full flex flex-col gap-2 mt-1">
    {{-- BILAH NAVIGASI ATAS DENGAN INFORMASI RESMI & AKSI CEPAT --}}
    <div class="flex flex-wrap items-center justify-between gap-3 p-2.5 bg-gray-50 dark:bg-zinc-800/90 rounded-xl border border-gray-200 dark:border-zinc-700/80 shadow-sm">
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-500/30">
                F4 Folio Landscape (330 &times; 215 mm)
            </span>
            <span class="text-xs text-gray-600 dark:text-zinc-300">
                Pratinjau lembar cetak aktif &bull; Gunakan scroll vertikal untuk memeriksa seluruh halaman dokumen secara utuh.
            </span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ $downloadUrl }}" target="_blank" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white dark:bg-zinc-700 text-gray-700 dark:text-gray-100 border border-gray-300 dark:border-zinc-600 hover:bg-gray-100 dark:hover:bg-zinc-600 transition shadow-sm">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Buka Tab Penuh (PDF)
            </a>
            <a href="{{ $downloadUrl }}" download 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-white dark:bg-amber-500 dark:text-zinc-950 dark:hover:bg-amber-400 transition shadow-sm font-bold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh PDF
            </a>
        </div>
    </div>

    {{-- BINGKAI IFRAME PRATINJAU DOKUMEN (FIT DI LAYAR) --}}
    <div class="w-full relative rounded-xl overflow-hidden border border-gray-300 dark:border-zinc-700 shadow-lg bg-slate-100 dark:bg-zinc-900" style="height: 72vh; min-height: 550px;">
        <iframe 
            src="{{ $previewUrl }}" 
            class="w-full h-full border-0 bg-white" 
            loading="lazy"
            title="{{ $judulLaporan }}">
        </iframe>
    </div>
</div>
