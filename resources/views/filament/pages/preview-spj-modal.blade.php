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
    $selisih = $totalRealisasi > 0 ? ($totalRealisasi - $totalRencana) : 0;

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

    $rawBank = $record->nama_bank ?: $record->masterToko?->nama_bank;
    $bankName = $rawBank instanceof \BackedEnum ? $rawBank->value : ($rawBank ?: '-');
@endphp

<div
    style="display: flex; flex-direction: row; gap: 14px; width: 100%; height: calc(100dvh - 180px); min-height: 420px; align-items: stretch;"
    x-data="{
        recordId: '{{ $record->id }}',
        activeTab: 'semua',
        activeTitle: 'Semua Dokumen SPJ Lengkap',
        previewUrl: '{{ $previewBaseUrl }}?tipe=semua',
        downloadUrl: '{{ $downloadBaseUrl }}?tipe=semua',
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
                const frame = document.getElementById('spj-preview-frame-{{ $record->id }}');
                if (frame) {
                    frame.src = url;
                }
            }
        },
        renderPdf(url) {
            if (!url) return;
            this.pdfLoading = true;
            this.pdfError = null;
            const container = document.getElementById('modal-pdf-container-{{ $record->id }}');
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
                    this.pdfError = 'Kesalahan sistem saat memproses berkas PDF.';
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
                const frame = document.getElementById('spj-preview-frame-{{ $record->id }}');
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
    {{-- ==================== KOLOM KIRI: RINGKASAN & PILIH DOKUMEN (FIT DI LAYAR) ==================== --}}
    <div style="width: 320px; min-width: 300px; max-width: 330px; display: flex; flex-direction: column; gap: 10px; height: 100%; overflow-y: auto; padding-right: 2px;">
        
        {{-- CARD 1: INFORMASI UTAMA TRANSAKSI SPJ --}}
        <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-xs dark:border-white/10 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-2 dark:border-white/5">
                <span class="font-extrabold text-gray-900 dark:text-white text-sm tracking-tight truncate">{{ $record->nomor_pengajuan }}</span>
                <span class="inline-flex rounded-md px-2 py-0.5 text-[10px] font-bold bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                    {{ strtoupper(str_replace('_', ' ', $record->status)) }}
                </span>
            </div>

            <div class="space-y-1.5 text-xs">
                <div class="flex justify-between items-center">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Tanggal:</span>
                    <span class="text-gray-900 dark:text-gray-100 font-bold">
                        {{ \Carbon\Carbon::parse($record->tanggal_pengajuan)->format('d/m/Y') }}
                    </span>
                </div>

                <div class="flex justify-between items-center">
                    <span class="text-gray-500 dark:text-gray-400 font-medium">Toko / Rekanan:</span>
                    <span class="font-bold text-gray-900 dark:text-gray-100 truncate" style="max-width: 170px;">
                        {{ $record->nama_toko ?: ($record->masterToko?->nama_toko ?? '-') }}
                    </span>
                </div>

                <div class="flex justify-between items-baseline pt-2 border-t border-gray-100 dark:border-white/5">
                    <span class="text-gray-500 dark:text-gray-400 font-semibold">Total Belanja:</span>
                    <span class="text-base font-black text-emerald-600 dark:text-emerald-400">
                        Rp {{ number_format((float) $totalNominal, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- CARD 2: PILIH DOKUMEN PRATINJAU --}}
        <div class="rounded-xl border border-gray-200 bg-white p-2.5 shadow-xs dark:border-white/10 dark:bg-gray-900 space-y-1.5">
            <div class="font-extrabold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-[10px] mb-1 px-1">
                Pilih Dokumen Pratinjau
            </div>

            {{-- 1. Semua Dokumen --}}
            <button
                type="button"
                @click="setDocument('semua', 'Semua Dokumen SPJ Lengkap', '{{ $previewBaseUrl }}?tipe=semua', 'iframe')"
                :class="activeTab === 'semua' ? 'border-primary-600 bg-primary-50 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300 dark:border-primary-500 font-bold' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800'"
                class="w-full flex items-center justify-between rounded-lg border p-2 text-xs transition cursor-pointer text-left"
            >
                <div class="flex items-center gap-2 truncate">
                    <span class="text-base">📄</span>
                    <span class="font-bold text-xs truncate">Semua Dokumen SPJ</span>
                </div>
                <span x-show="activeTab === 'semua'" class="text-primary-600 dark:text-primary-400 font-bold text-sm">✓</span>
            </button>

            {{-- 2. Surat Pesanan --}}
            <button
                type="button"
                @click="setDocument('pesanan', 'Surat Pesanan Barang (F4 Portrait)', '{{ $previewBaseUrl }}?tipe=pesanan', 'iframe')"
                :class="activeTab === 'pesanan' ? 'border-primary-600 bg-primary-50 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300 dark:border-primary-500 font-bold' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800'"
                class="w-full flex items-center justify-between rounded-lg border p-2 text-xs transition cursor-pointer text-left"
            >
                <div class="flex items-center gap-2 truncate">
                    <span class="text-base">📝</span>
                    <span class="font-bold text-xs truncate">Surat Pesanan (F4)</span>
                </div>
                <span x-show="activeTab === 'pesanan'" class="text-primary-600 dark:text-primary-400 font-bold text-sm">✓</span>
            </button>

            {{-- 3. Kwitansi --}}
            @if ($hasKwitansi)
                <button
                    type="button"
                    @click="setDocument('kwitansi', 'Kwitansi Pembayaran (F4 Bagi 2 Landscape - 4 Rangkap)', '{{ $previewBaseUrl }}?tipe=kwitansi', 'iframe')"
                    :class="activeTab === 'kwitansi' ? 'border-primary-600 bg-primary-50 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300 dark:border-primary-500 font-bold' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800'"
                    class="w-full flex items-center justify-between rounded-lg border p-2 text-xs transition cursor-pointer text-left"
                >
                    <div class="flex items-center gap-2 truncate">
                        <span class="text-base">🧾</span>
                        <span class="font-bold text-xs truncate">Kwitansi (F4/2 Landscape)</span>
                    </div>
                    <span x-show="activeTab === 'kwitansi'" class="text-primary-600 dark:text-primary-400 font-bold text-sm">✓</span>
                </button>
            @endif

            {{-- 4. NPD --}}
            @if ($hasNpd)
                <button
                    type="button"
                    @click="setDocument('npd', 'Nota Pencairan Dana (F4 Portrait)', '{{ $previewBaseUrl }}?tipe=npd', 'iframe')"
                    :class="activeTab === 'npd' ? 'border-primary-600 bg-primary-50 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300 dark:border-primary-500 font-bold' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800'"
                    class="w-full flex items-center justify-between rounded-lg border p-2 text-xs transition cursor-pointer text-left"
                >
                    <div class="flex items-center gap-2 truncate">
                        <span class="text-base">💰</span>
                        <span class="font-bold text-xs truncate">NPD (F4 Portrait)</span>
                    </div>
                    <span x-show="activeTab === 'npd'" class="text-primary-600 dark:text-primary-400 font-bold text-sm">✓</span>
                </button>
            @endif

            {{-- 5. Faktur Toko (Otomatis Deteksi PDF vs Gambar) --}}
            @if ($fileNotaUrl)
                @php
                    $notaMode = $isNotaPdf ? 'pdf' : ($isNotaImage ? 'image' : 'iframe');
                @endphp
                <button
                    type="button"
                    @click="setDocument('nota_toko', 'Faktur / Nota Toko Asli', '{{ $fileNotaUrl }}', '{{ $notaMode }}')"
                    :class="activeTab === 'nota_toko' ? 'border-primary-600 bg-primary-50 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300 dark:border-primary-500 font-bold' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800'"
                    class="w-full flex items-center justify-between rounded-lg border p-2 text-xs transition cursor-pointer text-left"
                >
                    <div class="flex items-center gap-2 truncate">
                        <span class="text-base">📎</span>
                        <span class="font-bold text-xs truncate">Faktur / Nota Toko</span>
                    </div>
                    <span x-show="activeTab === 'nota_toko'" class="text-primary-600 dark:text-primary-400 font-bold text-xs">✓</span>
                </button>
            @endif

            {{-- 6. Dokumentasi Barang (Otomatis Deteksi PDF vs Gambar) --}}
            @if ($fileDokumentasiUrl)
                @php
                    $dokMode = $isDokumentasiPdf ? 'pdf' : ($isDokumentasiImage ? 'image' : 'iframe');
                @endphp
                <button
                    type="button"
                    @click="setDocument('foto_barang', 'Foto Fisik Barang', '{{ $fileDokumentasiUrl }}', '{{ $dokMode }}')"
                    :class="activeTab === 'foto_barang' ? 'border-primary-600 bg-primary-50 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300 dark:border-primary-500 font-bold' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800'"
                    class="w-full flex items-center justify-between rounded-lg border p-2 text-xs transition cursor-pointer text-left"
                >
                    <div class="flex items-center gap-2 truncate">
                        <span class="text-base">📷</span>
                        <span class="font-bold text-xs truncate">Foto Fisik Barang</span>
                    </div>
                    <span x-show="activeTab === 'foto_barang'" class="text-primary-600 dark:text-primary-400 font-bold text-xs">✓</span>
                </button>
            @endif

            {{-- 7. Dokumen / Bundel E-Katalog --}}
            @if ($fileEkatalogUrl)
                @php
                    $ekatMode = $isEkatalogPdf ? 'pdf' : ($isEkatalogImage ? 'image' : 'iframe');
                @endphp
                <button
                    type="button"
                    @click="setDocument('ekatalog', 'Dokumen / Bundel E-Katalog', '{{ $fileEkatalogUrl }}', '{{ $ekatMode }}')"
                    :class="activeTab === 'ekatalog' ? 'border-primary-600 bg-primary-50 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300 dark:border-primary-500 font-bold' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800'"
                    class="w-full flex items-center justify-between rounded-lg border p-2 text-xs transition cursor-pointer text-left"
                >
                    <div class="flex items-center gap-2 truncate">
                        <span class="text-base">🛍️</span>
                        <span class="font-bold text-xs truncate">Bundel E-Katalog</span>
                    </div>
                    <span x-show="activeTab === 'ekatalog'" class="text-primary-600 dark:text-primary-400 font-bold text-xs">✓</span>
                </button>
            @endif
        </div>

        {{-- CARD 3: TOMBOL AKSI CETAK & UNDUH PDF RESMI --}}
        <div class="space-y-2 pt-0.5">
            <button
                type="button"
                @click="printIframe()"
                class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-xs font-bold text-white shadow-xs transition hover:bg-primary-500 cursor-pointer"
            >
                <x-filament::icon icon="heroicon-o-printer" class="h-4 w-4" />
                <span class="text-xs font-bold">Cetak Dokumen</span>
            </button>

            <a
                :href="downloadUrl"
                target="_blank"
                class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-xs font-bold text-gray-700 shadow-xs transition hover:bg-gray-50 dark:border-white/15 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 cursor-pointer"
            >
                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4 text-gray-500" />
                <span class="text-xs font-bold">Unduh File PDF</span>
            </a>
        </div>

        {{-- CARD 4: KETENTUAN PERPAJAKAN & PEMBAYARAN BERSIH --}}
        @php
            $subtotalBarang = (float) $totalNominal;
            $ppnPersen = (float) ($record->ppn_persen ?? 0);
            $pphPersen = (float) ($record->pph22_persen ?? 0);
            $isKenaPajak = $subtotalBarang > 2000000 && ($ppnPersen > 0 || $pphPersen > 0);
            $nilaiPpn = $isKenaPajak ? (float) ($record->nilai_ppn ?: round($subtotalBarang * ($ppnPersen / 100))) : 0;
            $nilaiPph = $isKenaPajak ? (float) ($record->nilai_pph22 ?: round($subtotalBarang * ($pphPersen / 100))) : 0;
            $totalPajak = $nilaiPpn + $nilaiPph;
            $totalBruto = $subtotalBarang + $nilaiPpn;
            $nilaiBersih = max(0, $totalBruto - $totalPajak);
        @endphp

        <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-xs dark:border-white/10 dark:bg-gray-900">
            @if ($isKenaPajak)
                <div class="flex items-center justify-between pb-2 mb-2 border-b border-gray-100 dark:border-white/5">
                    <span class="text-xs font-extrabold text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                        <span class="text-sm">⚠️</span> Wajib Pungut Pajak
                    </span>
                    <span class="text-[10px] bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 font-bold px-1.5 py-0.5 rounded">
                        > Rp 2.000.000
                    </span>
                </div>

                <div class="space-y-1 text-xs">
                    <div class="flex justify-between items-center text-gray-500 dark:text-gray-400">
                        <span>Dasar Pengenaan Pajak (DPP):</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-200">Rp {{ number_format($subtotalBarang, 0, ',', '.') }}</span>
                    </div>

                    @if ($nilaiPpn > 0)
                        <div class="flex justify-between items-center text-amber-700 dark:text-amber-400">
                            <span>PPN ({{ $ppnPersen }}%):</span>
                            <span class="font-bold">+ Rp {{ number_format($nilaiPpn, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    @if ($nilaiPph > 0)
                        <div class="flex justify-between items-center text-rose-600 dark:text-rose-400">
                            <span>PPh 22 ({{ $pphPersen }}%):</span>
                            <span class="font-bold">- Rp {{ number_format($nilaiPph, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between items-center pt-1.5 border-t border-gray-100 dark:border-white/5 text-[11px] font-bold text-gray-600 dark:text-gray-400">
                        <span>Total Pajak Disetor:</span>
                        <span class="text-amber-600 dark:text-amber-400 font-mono">Rp {{ number_format($totalPajak, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between items-center pt-1.5 border-t border-gray-200 dark:border-white/10 text-xs font-extrabold">
                        <span class="text-gray-900 dark:text-white">Diterima Bersih Toko:</span>
                        <span class="text-emerald-600 dark:text-emerald-400 text-sm font-black font-mono">
                            Rp {{ number_format($nilaiBersih, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            @else
                <div class="flex items-start gap-2">
                    <span class="text-base text-emerald-500">✓</span>
                    <div>
                        <div class="text-xs font-bold text-gray-800 dark:text-gray-200">
                            Bebas Potongan Pajak
                        </div>
                        <div class="text-[11px] text-gray-600 dark:text-gray-400 mt-0.5">
                            Bebas pungutan PPN 0% & PPh 22 0%. Rekanan menerima pembayaran utuh 100%.
                        </div>
                        <div class="text-xs text-emerald-700 dark:text-emerald-400 font-bold mt-1 font-mono">
                            Diterima Bersih: Rp {{ number_format($subtotalBarang, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ==================== KOLOM KANAN: PREVIEW DOKUMEN (HTML A4 / IMAGE / PDF CANVAS) DENGAN TOOLBAR ==================== --}}
    <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; height: 100%;">
        
        {{-- Toolbar Atas --}}
        <div class="flex items-center justify-between bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-t-xl border border-b-0 border-gray-300 dark:border-white/10" style="font-size: 11px;">
            <div class="flex items-center gap-2 truncate">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                <span class="font-bold text-gray-800 dark:text-gray-200 truncate text-[11px]" x-text="activeTitle"></span>
            </div>

            <div class="flex items-center gap-2">
                {{-- Zoom Controls --}}
                <div class="inline-flex items-center rounded-lg border border-gray-300 bg-white dark:border-white/15 dark:bg-gray-900 overflow-hidden shadow-xs">
                    <button
                        type="button"
                        @click="zoomOut()"
                        class="px-2 py-0.5 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 font-bold transition cursor-pointer"
                        title="Perkecil (-)"
                    >
                        &minus;
                    </button>
                    <span class="px-2 py-0.5 text-[10px] font-mono font-semibold text-gray-700 dark:text-gray-300 select-none border-x border-gray-200 dark:border-white/10" x-text="zoom + '%'"></span>
                    <button
                        type="button"
                        @click="zoomIn()"
                        class="px-2 py-0.5 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 font-bold transition cursor-pointer"
                        title="Perbesar (+)"
                    >
                        +
                    </button>
                    <button
                        type="button"
                        @click="zoomReset()"
                        class="px-2 py-0.5 text-[9.5px] text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 border-l border-gray-200 dark:border-white/10 transition cursor-pointer"
                        title="Reset 100%"
                    >
                        100%
                    </button>
                </div>

                {{-- Buka Layar Penuh Tab Baru --}}
                <a
                    :href="viewMode === 'image' ? activeImageUrl : (viewMode === 'pdf' ? activePdfUrl : previewUrl)"
                    target="_blank"
                    class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-2 py-0.5 text-[10.5px] font-semibold text-gray-700 hover:bg-gray-50 dark:border-white/15 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800 shadow-xs cursor-pointer"
                    title="Buka pratinjau di tab baru"
                >
                    <x-filament::icon icon="heroicon-o-arrows-pointing-out" class="h-3 w-3" />
                    <span>Layar Penuh</span>
                </a>
            </div>
        </div>

        {{-- Preview Body --}}
        <div style="flex: 1; width: 100%; border-radius: 0 0 12px 12px; overflow: hidden; border: 1px solid rgba(156, 163, 175, 0.3); background-color: #525659; position: relative;">
            
            {{-- Tampilan Dokumen HTML A4 (Mencegah Auto-Download) --}}
            <div x-show="viewMode === 'iframe'" style="width: 100%; height: 100%; overflow: auto;">
                <iframe
                    id="spj-preview-frame-{{ $record->id }}"
                    src="{{ $previewBaseUrl }}?tipe=semua"
                    :src="previewUrl"
                    style="width: 100%; height: 100%; border: none; display: block; background-color: #525659;"
                    :style="'transform: scale(' + (zoom / 100) + '); transform-origin: top center; width: ' + (10000 / zoom) + '%; height: ' + (10000 / zoom) + '%;'"
                    allow="fullscreen"
                ></iframe>
            </div>

            {{-- Tampilan Dokumen PDF Asli (Faktur Toko / Bundel E-Katalog) via Canvas PDF.js (Anti-Download) --}}
            <div x-show="viewMode === 'pdf'" style="width: 100%; height: 100%; overflow: auto; padding: 16px; background-color: #525659; text-align: center;">
                <div x-show="pdfLoading" class="py-16 flex flex-col items-center justify-center text-gray-200">
                    <svg class="animate-spin h-8 w-8 text-amber-400 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span class="text-xs font-semibold">Memuat dan merender pratinjau dokumen PDF...</span>
                </div>
                <div x-show="pdfError" class="py-12 text-rose-300 text-xs px-4" x-text="pdfError"></div>
                <div
                    id="modal-pdf-container-{{ $record->id }}"
                    class="inline-block transition-transform duration-150"
                    :style="'transform: scale(' + (zoom / 100) + '); transform-origin: top center;'"
                ></div>
            </div>

            {{-- Tampilan Bukti Gambar (Foto Nota / Fisik Barang) --}}
            <div x-show="viewMode === 'image'" style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; overflow: auto; padding: 20px; background-color: #2b2e31;">
                <img
                    :src="activeImageUrl"
                    alt="Bukti Lampiran SPJ"
                    style="max-width: 95%; max-height: 95%; object-fit: contain; border-radius: 8px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4); transition: transform 0.2s;"
                    :style="'transform: scale(' + (zoom / 100) + ');'"
                />
            </div>
        </div>
    </div>
</div>
