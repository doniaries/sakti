@php
    $record->loadMissing([
        'bidang',
        'kegiatan',
        'subKegiatan',
        'details.kodeRekening',
        'details.masterBarang.satuan',
        'user',
        'pptk',
        'ppk',
        'pa',
        'bendahara',
        'masterToko',
    ]);

    $previewBaseUrl = route('admin.pengajuan-spj.preview', $record);
    $downloadBaseUrl = route('admin.pengajuan-spj.cetak', $record);
    $status = $record->status;

    $hasKwitansi = !in_array($status, ['draft', 'diajukan', 'menunggu_realisasi'], true);
    $hasNpd = !in_array($status, ['draft', 'diajukan', 'menunggu_realisasi', 'menunggu_verifikasi_pptk', 'revisi_pptk'], true);

    $totalRencana = (float) $record->total_nilai_rencana;
    $totalRealisasi = (float) $record->total_nilai_realisasi;
    $totalNominal = $totalRealisasi > 0 ? $totalRealisasi : $totalRencana;

    $fileNotaUrl = $record->file_nota_penjual
        ? \Illuminate\Support\Facades\Storage::url($record->file_nota_penjual)
        : null;
    $fileDokumentasiUrl = $record->file_dokumentasi_barang
        ? \Illuminate\Support\Facades\Storage::url($record->file_dokumentasi_barang)
        : null;
    $fileEkatalogUrl = $record->file_ekatalog_bundel
        ? \Illuminate\Support\Facades\Storage::url($record->file_ekatalog_bundel)
        : null;

    $isNotaPdf = $fileNotaUrl && (bool) preg_match('/\.pdf$/i', $fileNotaUrl);
    $isNotaImage = $fileNotaUrl && (bool) preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $fileNotaUrl);

    $isDokumentasiPdf = $fileDokumentasiUrl && (bool) preg_match('/\.pdf$/i', $fileDokumentasiUrl);
    $isDokumentasiImage = $fileDokumentasiUrl && (bool) preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $fileDokumentasiUrl);

    $isEkatalogPdf = $fileEkatalogUrl && (bool) preg_match('/\.pdf$/i', $fileEkatalogUrl);
    $isEkatalogImage = $fileEkatalogUrl && (bool) preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $fileEkatalogUrl);

    // Tentukan dokumen default saat pertama dibuka
    $defaultDoc = match ($status) {
        'diajukan' => 'pesanan',
        'menunggu_realisasi' => 'pesanan',
        'menunggu_verifikasi_pptk' => 'semua',
        default => 'semua',
    };
    $defaultTitle = match ($defaultDoc) {
        'pesanan' => 'Surat Pesanan Barang',
        default => 'Semua Dokumen SPJ Lengkap',
    };
@endphp

<div
    class="flex flex-col gap-3 w-full"
    x-data="{
        recordId: '{{ $record->id }}',
        activeTab: '{{ $defaultDoc }}',
        activeTitle: '{{ $defaultTitle }}',
        previewUrl: '{{ $previewBaseUrl }}?tipe={{ $defaultDoc }}',
        downloadUrl: '{{ $downloadBaseUrl }}?tipe={{ $defaultDoc }}',
        viewMode: 'iframe', // 'iframe' | 'image' | 'pdf'
        activeImageUrl: '',
        activePdfUrl: '',
        pdfLoading: false,
        pdfError: null,
        zoom: 100,
        zoomIn() {
            if (this.zoom < 160) this.zoom += 10;
        },
        zoomOut() {
            if (this.zoom > 60) this.zoom -= 10;
        },
        zoomReset() {
            this.zoom = 100;
        },
        setDocument(type, title, url, mode = 'iframe') {
            this.activeTab = type;
            this.activeTitle = title;
            this.viewMode = mode;
            if (mode === 'image') {
                this.activeImageUrl = url;
            } else if (mode === 'pdf') {
                this.activePdfUrl = url;
                this.$nextTick(() => {
                    this.renderPdf(url);
                });
            } else {
                this.previewUrl = url;
                this.downloadUrl = '{{ $downloadBaseUrl }}?tipe=' + (type === 'semua' || type === 'pesanan' || type === 'kwitansi' || type === 'npd' ? type : 'semua');
                const frame = document.getElementById('pedoman-preview-frame-{{ $record->id }}');
                if (frame) {
                    frame.src = url;
                }
            }
        },
        renderPdf(url) {
            if (!url) return;
            this.pdfLoading = true;
            this.pdfError = null;
            const container = document.getElementById('pedoman-pdf-container-{{ $record->id }}');
            if (container) container.innerHTML = '';

            const executeRender = () => {
                try {
                    window.pdfjsLib.GlobalWorkerOptions.workerSrc = '{{ asset('js/pdf.worker.min.js') }}';
                    window.pdfjsLib.getDocument(url).promise.then((pdf) => {
                        this.pdfLoading = false;
                        const totalPages = pdf.numPages;
                        for (let pageNum = 1; pageNum <= totalPages; pageNum++) {
                            pdf.getPage(pageNum).then((page) => {
                                const viewport = page.getViewport({ scale: 1.5 });
                                const canvas = document.createElement('canvas');
                                canvas.className = 'shadow-2xl rounded-lg mb-4 max-w-full block bg-white mx-auto border border-gray-400';
                                canvas.height = viewport.height;
                                canvas.width = viewport.width;
                                const ctx = canvas.getContext('2d');
                                page.render({
                                    canvasContext: ctx,
                                    viewport: viewport
                                });
                                if (container) container.appendChild(canvas);
                            });
                        }
                    }).catch((err) => {
                        console.error('PDF.js error:', err);
                        this.pdfLoading = false;
                        this.pdfError = 'Gagal memuat pratinjau PDF: ' + (err.message || 'Format tidak terbaca.');
                    });
                } catch (e) {
                    console.error('PDF.js execution error:', e);
                    this.pdfLoading = false;
                    this.pdfError = 'Kesalahan saat merender PDF.';
                }
            };

            if (typeof window.pdfjsLib !== 'undefined') {
                executeRender();
            } else {
                const script = document.createElement('script');
                script.src = '{{ asset('js/pdf.min.js') }}';
                script.onload = () => executeRender();
                script.onerror = () => {
                    this.pdfLoading = false;
                    this.pdfError = 'Gagal memuat engine render PDF. Silakan gunakan tombol Buka di Tab Baru.';
                };
                document.head.appendChild(script);
            }
        },
        printIframe() {
            if (this.viewMode === 'iframe') {
                const frame = document.getElementById('pedoman-preview-frame-{{ $record->id }}');
                if (frame && frame.contentWindow) {
                    frame.contentWindow.focus();
                    frame.contentWindow.print();
                } else {
                    window.open(this.previewUrl, '_blank');
                }
            } else if (this.viewMode === 'pdf') {
                window.open(this.activePdfUrl, '_blank');
            } else if (this.viewMode === 'image') {
                const win = window.open(this.activeImageUrl, '_blank');
                if (win) {
                    win.focus();
                }
            }
        }
    }"
>
    {{-- BARIS 1: RINGKASAN TRANSAKSI CEPAT SEBAGAI PEDOMAN ANGGARAN --}}
    <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-3 dark:border-white/10 dark:bg-gray-900/80 shadow-xs">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200/80 pb-2 mb-2 dark:border-white/5">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center justify-center rounded-md bg-primary-100 p-1 text-primary-700 dark:bg-primary-950 dark:text-primary-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </span>
                <div>
                    <span class="font-extrabold text-sm text-gray-900 dark:text-white">{{ $record->nomor_pengajuan }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 ml-1.5 font-medium">
                        ({{ \Carbon\Carbon::parse($record->tanggal_pengajuan)->format('d/m/Y') }})
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">Total Belanja:</span>
                <span class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400">
                    Rp {{ number_format($totalNominal, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
            <div>
                <span class="text-gray-500 dark:text-gray-400 block text-[11px]">Rekanan / Toko:</span>
                <span class="font-bold text-gray-900 dark:text-gray-100 truncate block">
                    {{ $record->nama_toko ?: ($record->masterToko?->nama_toko ?? '-') }}
                </span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400 block text-[11px]">Sub Kegiatan:</span>
                <span class="font-bold text-gray-900 dark:text-gray-100 truncate block" title="{{ $record->subKegiatan?->nama_sub_kegiatan }}">
                    {{ $record->subKegiatan?->nama_sub_kegiatan ?? '-' }}
                </span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400 block text-[11px]">Rekening Belanja:</span>
                <span class="font-bold text-amber-600 dark:text-amber-400 truncate block font-mono">
                    {{ $record->details->first()?->kodeRekening?->kode_rekening ?? '-' }}
                </span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400 block text-[11px]">Status Pajak:</span>
                @if ($record->is_kena_pajak || $totalNominal > 2000000)
                    <span class="font-bold text-amber-600 dark:text-amber-400 block">
                        Pajak: Rp {{ number_format($record->total_pajak, 0, ',', '.') }}
                    </span>
                @else
                    <span class="font-bold text-emerald-600 dark:text-emerald-400 block">
                        Bebas Pajak (&le; 2 Jt)
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- BARIS 2: TAB NAVIGASI DOKUMEN & CONTROLS --}}
    <div class="flex flex-wrap items-center justify-between gap-1.5 bg-gray-100 p-1.5 rounded-lg dark:bg-gray-800/90 text-xs">
        <div class="flex flex-wrap items-center gap-1">
            {{-- Tab 1: Semua Dokumen --}}
            <button
                type="button"
                @click="setDocument('semua', 'Semua Dokumen SPJ', '{{ $previewBaseUrl }}?tipe=semua', 'iframe')"
                :class="activeTab === 'semua' ? 'bg-white text-primary-700 shadow-xs font-bold dark:bg-gray-700 dark:text-primary-300' : 'text-gray-600 hover:text-gray-900 hover:bg-white/50 dark:text-gray-400 dark:hover:text-gray-200'"
                class="px-2.5 py-1 rounded-md transition cursor-pointer flex items-center gap-1.5"
            >
                <span>📑</span>
                <span>Semua SPJ</span>
            </button>

            {{-- Tab 2: Surat Pesanan --}}
            <button
                type="button"
                @click="setDocument('pesanan', 'Surat Pesanan Barang', '{{ $previewBaseUrl }}?tipe=pesanan', 'iframe')"
                :class="activeTab === 'pesanan' ? 'bg-white text-primary-700 shadow-xs font-bold dark:bg-gray-700 dark:text-primary-300' : 'text-gray-600 hover:text-gray-900 hover:bg-white/50 dark:text-gray-400 dark:hover:text-gray-200'"
                class="px-2.5 py-1 rounded-md transition cursor-pointer flex items-center gap-1.5"
            >
                <span>📄</span>
                <span>Surat Pesanan</span>
            </button>

            {{-- Tab 3: Kwitansi --}}
            @if ($hasKwitansi)
                <button
                    type="button"
                    @click="setDocument('kwitansi', 'Kwitansi Pembayaran', '{{ $previewBaseUrl }}?tipe=kwitansi', 'iframe')"
                    :class="activeTab === 'kwitansi' ? 'bg-white text-primary-700 shadow-xs font-bold dark:bg-gray-700 dark:text-primary-300' : 'text-gray-600 hover:text-gray-900 hover:bg-white/50 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="px-2.5 py-1 rounded-md transition cursor-pointer flex items-center gap-1.5"
                >
                    <span>🧾</span>
                    <span>Kwitansi</span>
                </button>
            @endif

            {{-- Tab 4: NPD --}}
            @if ($hasNpd)
                <button
                    type="button"
                    @click="setDocument('npd', 'Nota Pencairan Dana (NPD)', '{{ $previewBaseUrl }}?tipe=npd', 'iframe')"
                    :class="activeTab === 'npd' ? 'bg-white text-primary-700 shadow-xs font-bold dark:bg-gray-700 dark:text-primary-300' : 'text-gray-600 hover:text-gray-900 hover:bg-white/50 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="px-2.5 py-1 rounded-md transition cursor-pointer flex items-center gap-1.5"
                >
                    <span>💰</span>
                    <span>NPD</span>
                </button>
            @endif

            {{-- Tab 5: Faktur / Nota Toko (Otomatis Deteksi PDF vs Gambar) --}}
            @if ($fileNotaUrl)
                @php
                    $notaMode = $isNotaPdf ? 'pdf' : ($isNotaImage ? 'image' : 'iframe');
                @endphp
                <button
                    type="button"
                    @click="setDocument('nota', 'Faktur / Nota Rekanan', '{{ $fileNotaUrl }}', '{{ $notaMode }}')"
                    :class="activeTab === 'nota' ? 'bg-white text-primary-700 shadow-xs font-bold dark:bg-gray-700 dark:text-primary-300' : 'text-gray-600 hover:text-gray-900 hover:bg-white/50 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="px-2.5 py-1 rounded-md transition cursor-pointer flex items-center gap-1.5"
                >
                    <span>🏬</span>
                    <span>Faktur Toko</span>
                </button>
            @endif

            {{-- Tab 6: Foto Fisik Barang (Otomatis Deteksi PDF vs Gambar) --}}
            @if ($fileDokumentasiUrl)
                @php
                    $dokMode = $isDokumentasiPdf ? 'pdf' : ($isDokumentasiImage ? 'image' : 'iframe');
                @endphp
                <button
                    type="button"
                    @click="setDocument('foto', 'Foto Dokumentasi Fisik Barang', '{{ $fileDokumentasiUrl }}', '{{ $dokMode }}')"
                    :class="activeTab === 'foto' ? 'bg-white text-primary-700 shadow-xs font-bold dark:bg-gray-700 dark:text-primary-300' : 'text-gray-600 hover:text-gray-900 hover:bg-white/50 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="px-2.5 py-1 rounded-md transition cursor-pointer flex items-center gap-1.5"
                >
                    <span>📸</span>
                    <span>Foto Barang</span>
                </button>
            @endif

            {{-- Tab 7: Dokumen / Bundel E-Katalog --}}
            @if ($fileEkatalogUrl)
                @php
                    $ekatMode = $isEkatalogPdf ? 'pdf' : ($isEkatalogImage ? 'image' : 'iframe');
                @endphp
                <button
                    type="button"
                    @click="setDocument('ekatalog', 'Dokumen / Bundel E-Katalog', '{{ $fileEkatalogUrl }}', '{{ $ekatMode }}')"
                    :class="activeTab === 'ekatalog' ? 'bg-white text-primary-700 shadow-xs font-bold dark:bg-gray-700 dark:text-primary-300' : 'text-gray-600 hover:text-gray-900 hover:bg-white/50 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="px-2.5 py-1 rounded-md transition cursor-pointer flex items-center gap-1.5"
                >
                    <span>🛍️</span>
                    <span>Bundel E-Katalog</span>
                </button>
            @endif
        </div>

        {{-- Tool Buttons: Zoom & Open New Tab --}}
        <div class="flex items-center gap-1">
            <button
                type="button"
                @click="zoomOut()"
                class="px-1.5 py-0.5 rounded text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 font-bold"
                title="Perkecil (-)"
            >
                -
            </button>
            <span class="text-[11px] font-mono text-gray-500 dark:text-gray-400" x-text="zoom + '%'"></span>
            <button
                type="button"
                @click="zoomIn()"
                class="px-1.5 py-0.5 rounded text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 font-bold"
                title="Perbesar (+)"
            >
                +
            </button>
            <button
                type="button"
                @click="zoomReset()"
                class="px-1.5 py-0.5 rounded text-[10px] text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 font-mono"
                title="Reset Zoom 100%"
            >
                100%
            </button>
            <a
                :href="viewMode === 'image' ? activeImageUrl : (viewMode === 'pdf' ? activePdfUrl : previewUrl)"
                target="_blank"
                class="ml-1 p-1 rounded text-gray-600 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700"
                title="Buka Dokumen di Tab Baru"
            >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                </svg>
            </a>
            <button
                type="button"
                @click="printIframe()"
                class="p-1 rounded text-gray-600 hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-700"
                title="Cetak Dokumen Ini"
            >
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                </svg>
            </button>
        </div>
    </div>

    {{-- BARIS 3: CONTAINER PRATINJAU DOKUMEN (IFRAME / GAMBAR / PDF CANVAS) --}}
    <div
        class="w-full rounded-xl border border-gray-300 dark:border-white/10 overflow-hidden shadow-inner"
        style="height: calc(85vh - 200px); min-height: 580px; position: relative; background-color: #525659;"
    >
        {{-- VIEW MODE 1: GAMBAR FOTO / NOTA ASLI --}}
        <div x-show="viewMode === 'image'" class="w-full h-full flex items-center justify-center p-3 bg-gray-950/40 overflow-auto">
            <img
                :src="activeImageUrl"
                :style="'transform: scale(' + (zoom / 100) + '); transform-origin: center center; transition: transform 0.15s ease; max-height: 100%;'"
                class="rounded-lg shadow-md object-contain border border-white/10"
                alt="Pratinjau Berkas Gambar"
            />
        </div>

        {{-- VIEW MODE 2: PDF ASLI (FAKTUR TOKO / DOKUMEN E-KATALOG) DIRENDER OLEH PDF.JS PADA CANVAS (ANTI-DOWNLOAD) --}}
        <div x-show="viewMode === 'pdf'" class="w-full h-full overflow-auto bg-[#525659] p-4 text-center">
            <div x-show="pdfLoading" class="py-16 flex flex-col items-center justify-center text-gray-200">
                <svg class="animate-spin h-8 w-8 text-amber-400 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span class="text-xs font-semibold">Memuat dan merender pratinjau dokumen PDF...</span>
            </div>
            <div x-show="pdfError" class="py-12 text-rose-300 text-xs px-4" x-text="pdfError"></div>
            <div
                id="pedoman-pdf-container-{{ $record->id }}"
                class="inline-block transition-transform duration-150"
                :style="'transform: scale(' + (zoom / 100) + '); transform-origin: top center;'"
            ></div>
        </div>

        {{-- VIEW MODE 3: IFRAME HTML DOKUMEN SPJ (SEMUA, PESANAN, KWITANSI, NPD) --}}
        <div x-show="viewMode === 'iframe'" class="w-full h-full overflow-auto bg-[#525659]">
            <iframe
                id="pedoman-preview-frame-{{ $record->id }}"
                src="{{ $previewBaseUrl }}?tipe=semua"
                :src="previewUrl"
                class="w-full h-full border-0 block"
                :style="zoom === 100 ? 'width: 100%; height: 100%; border: none; display: block; background-color: #525659;' : 'transform: scale(' + (zoom / 100) + '); transform-origin: top center; width: ' + (10000 / zoom) + '%; height: ' + (10000 / zoom) + '%; border: none; display: block; background-color: #525659;'"
                title="Pratinjau Dokumen SPJ"
            ></iframe>
        </div>
    </div>
</div>
