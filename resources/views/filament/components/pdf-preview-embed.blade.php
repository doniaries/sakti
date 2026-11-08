@php
    $getFn = $get ?? null;
    $bulanVal = $getFn ? ($getFn('modal_bulan') ?: request('bulan', date('n'))) : request('bulan', date('n'));
    $bidangVal = $getFn ? $getFn('modal_bidang_id') : null;
    $tipeVal = $getFn ? ($getFn('modal_tipe') ?: 'lengkap') : 'lengkap';
    $tahunVal = $tahun ?? date('Y');
    $jenisLaporan = $jenis ?? 'lrfk';

    if ($jenisLaporan === 'fungsional') {
        $pdfUrl = route('admin.laporan.fungsional.cetak', [
            'tahun' => $tahunVal,
            'bulan' => $bulanVal,
        ]);
        $judulLaporan = 'Pratinjau LPJ Fungsional Bendahara';
    } else {
        $pdfUrl = route('admin.laporan.lrfk.cetak', [
            'tahun' => $tahunVal,
            'bulan' => $bulanVal,
            'bidang' => $bidangVal ?: null,
            'tipe' => $tipeVal,
        ]);
        $judulLaporan = 'Pratinjau Laporan Realisasi Fisik dan Keuangan (LRFK)';
    }
@endphp

<div wire:key="pdf-embed-box-{{ md5($pdfUrl) }}" class="w-full flex flex-col gap-2 mt-2">
    {{-- BILAH NAVIGASI ATAS DENGAN INFORMASI RESMI & AKSI CEPAT --}}
    <div class="flex flex-wrap items-center justify-between gap-3 p-3 bg-gray-50 dark:bg-zinc-800/90 rounded-xl border border-gray-200 dark:border-zinc-700/80 shadow-sm">
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-amber-500/15 text-amber-700 dark:text-amber-400 border border-amber-500/30">
                F4 Folio Landscape (330 &times; 215 mm)
            </span>
            <span class="text-xs text-gray-600 dark:text-zinc-300">
                Gunakan scroll vertikal atau thumbnail panel PDF untuk memeriksa <strong>seluruh halaman</strong> dokumen secara utuh.
            </span>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ $pdfUrl }}" target="_blank" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-white dark:bg-zinc-700 text-gray-700 dark:text-gray-100 border border-gray-300 dark:border-zinc-600 hover:bg-gray-100 dark:hover:bg-zinc-600 transition shadow-sm">
                <svg class="w-4 h-4 text-gray-500 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Buka di Tab Baru
            </a>
            <a href="{{ $pdfUrl }}" download 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-500 hover:bg-amber-600 text-white dark:bg-amber-500 dark:text-zinc-950 dark:hover:bg-amber-400 transition shadow-sm font-bold">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Unduh PDF
            </a>
        </div>
    </div>

    {{-- BINGKAI IFRAME PRATINJAU DOKUMEN PDF (LEBAR & TINGGI LEPAS SUPAYA SEMUA HALAMAN JELAS) --}}
    <div class="w-full relative rounded-xl overflow-hidden border-2 border-gray-300 dark:border-zinc-700 shadow-2xl bg-zinc-950" style="height: 68vh; min-height: 540px;">
        <iframe 
            src="{{ $pdfUrl }}#toolbar=1&navpanes=1&statusbar=1&view=FitH" 
            class="w-full h-full border-0" 
            loading="lazy"
            title="{{ $judulLaporan }}">
        </iframe>
    </div>
</div>

