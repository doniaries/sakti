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

    $isNotaImage = $fileNotaUrl && preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $fileNotaUrl);
    $isDokumentasiImage = $fileDokumentasiUrl && preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $fileDokumentasiUrl);

    $rawBank = $record->nama_bank ?: $record->masterToko?->nama_bank;
    $bankName = $rawBank instanceof \BackedEnum ? $rawBank->value : ($rawBank ?: '-');
@endphp

<div
    style="display: flex; flex-direction: row; gap: 14px; width: 100%; height: 72vh; max-height: 580px; min-height: 460px; align-items: stretch;"
    x-data="{
        activeTab: 'semua',
        activeTitle: 'Semua Dokumen SPJ Lengkap',
        previewUrl: '{{ $previewBaseUrl }}?tipe=semua',
        downloadUrl: '{{ $downloadBaseUrl }}?tipe=semua',
        isImage: false,
        imageUrl: '',
        zoom: 100,
        zoomIn() {
            if (this.zoom < 150) this.zoom += 10;
        },
        zoomOut() {
            if (this.zoom > 60) this.zoom -= 10;
        },
        zoomReset() {
            this.zoom = 100;
        },
        setDocument(type, title, url, isImg = false) {
            this.activeTab = type;
            this.activeTitle = title;
            this.isImage = isImg;
            if (isImg) {
                this.imageUrl = url;
            } else {
                this.previewUrl = url;
                this.downloadUrl = '{{ $downloadBaseUrl }}?tipe=' + (type === 'semua' || type === 'pesanan' || type === 'kwitansi' || type === 'npd' ? type : 'semua');
                const frame = document.getElementById('spj-preview-frame-{{ $record->id }}');
                if (frame) {
                    frame.src = url;
                }
            }
        },
        printIframe() {
            const frame = document.getElementById('spj-preview-frame-{{ $record->id }}');
            if (frame && frame.contentWindow) {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            } else {
                window.open(this.previewUrl, '_blank');
            }
        }
    }"
>
    {{-- ==================== KOLOM KIRI: RINGKASAN & PILIH DOKUMEN (FIT DI LAYAR) ==================== --}}
    <div style="width: 310px; min-width: 290px; max-width: 320px; display: flex; flex-direction: column; gap: 8px; height: 100%; overflow-y: auto; padding-right: 4px;">
        
        {{-- CARD 1: INFORMASI RINGKAS TRANSAKSI & ANGGARAN --}}
        <div class="rounded-xl border border-gray-200 bg-white p-2.5 shadow-xs dark:border-white/10 dark:bg-gray-900" style="font-size: 11px;">
            <div class="flex items-center justify-between border-b border-gray-100 pb-1 mb-1.5 dark:border-white/5">
                <span class="font-bold text-gray-900 dark:text-white truncate text-[11px]">{{ $record->nomor_pengajuan }}</span>
                <span class="inline-flex rounded px-1.5 py-0.5 text-[9px] font-bold bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                    {{ strtoupper(str_replace('_', ' ', $record->status)) }}
                </span>
            </div>

            <div class="space-y-1 leading-tight text-[10.5px]">
                <div class="flex justify-between items-baseline">
                    <span class="text-gray-400 text-[10px]">Tanggal:</span>
                    <span class="text-gray-800 dark:text-gray-200 font-medium">
                        {{ \Carbon\Carbon::parse($record->tanggal_pengajuan)->format('d/m/Y') }}
                    </span>
                </div>

                <div>
                    <span class="text-gray-400 text-[9.5px] block">Sub Kegiatan:</span>
                    <span class="font-medium text-gray-800 dark:text-gray-200 block truncate" title="{{ $record->subKegiatan?->nama_sub_kegiatan }}">
                        {{ $record->subKegiatan?->nama_sub_kegiatan ?? '-' }}
                    </span>
                </div>

                @php
                    $firstRekening = $record->details->first()?->kodeRekening;
                @endphp
                @if ($firstRekening)
                    <div>
                        <span class="text-gray-400 text-[9.5px] block">Rekening Belanja:</span>
                        <span class="font-mono text-[10px] font-semibold text-primary-600 dark:text-primary-400 block truncate" title="{{ $firstRekening->uraian }}">
                            {{ $firstRekening->kode_lengkap ?? $firstRekening->kode_rekening }}
                        </span>
                    </div>
                @endif

                <div>
                    <span class="text-gray-400 text-[9.5px] block">Penyedia / Toko:</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200 block truncate">
                        {{ $record->nama_toko ?: ($record->masterToko?->nama_toko ?? '-') }}
                    </span>
                </div>

                <div class="flex justify-between items-baseline pt-1 border-t border-gray-100 dark:border-white/5">
                    <span class="text-gray-400 text-[10px]">Total Belanja:</span>
                    <span class="text-xs font-extrabold text-emerald-600 dark:text-emerald-400">
                        Rp {{ number_format((float) $totalNominal, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- CARD 2: PILIH DOKUMEN PRATINJAU --}}
        <div class="rounded-xl border border-gray-200 bg-white p-2 shadow-xs dark:border-white/10 dark:bg-gray-900 space-y-1">
            <div class="font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-[9px] mb-1 px-1">
                Pilih Dokumen Pratinjau
            </div>

            {{-- 1. Semua Dokumen --}}
            <button
                type="button"
                @click="setDocument('semua', 'Semua Dokumen SPJ Lengkap', '{{ $previewBaseUrl }}?tipe=semua', false)"
                :class="activeTab === 'semua' ? 'border-primary-600 bg-primary-50 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300 dark:border-primary-500 font-bold' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800'"
                class="w-full flex items-center justify-between rounded-lg border p-1.5 text-xs transition cursor-pointer text-left"
            >
                <div class="flex items-center gap-1.5 truncate">
                    <span class="text-sm">📄</span>
                    <span class="font-semibold text-[11px] truncate">Semua Dokumen SPJ</span>
                </div>
                <span x-show="activeTab === 'semua'" class="text-primary-600 dark:text-primary-400 font-bold text-xs">✓</span>
            </button>

            {{-- 2. Surat Pesanan --}}
            <button
                type="button"
                @click="setDocument('pesanan', 'Surat Pesanan Barang (Nota Pesanan)', '{{ $previewBaseUrl }}?tipe=pesanan', false)"
                :class="activeTab === 'pesanan' ? 'border-primary-600 bg-primary-50 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300 dark:border-primary-500 font-bold' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800'"
                class="w-full flex items-center justify-between rounded-lg border p-1.5 text-xs transition cursor-pointer text-left"
            >
                <div class="flex items-center gap-1.5 truncate">
                    <span class="text-sm">📝</span>
                    <span class="font-semibold text-[11px] truncate">Surat Pesanan Barang</span>
                </div>
                <span x-show="activeTab === 'pesanan'" class="text-primary-600 dark:text-primary-400 font-bold text-xs">✓</span>
            </button>

            {{-- 3. Kwitansi --}}
            @if ($hasKwitansi)
                <button
                    type="button"
                    @click="setDocument('kwitansi', 'Kwitansi Pembayaran (4 Rangkap)', '{{ $previewBaseUrl }}?tipe=kwitansi', false)"
                    :class="activeTab === 'kwitansi' ? 'border-primary-600 bg-primary-50 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300 dark:border-primary-500 font-bold' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800'"
                    class="w-full flex items-center justify-between rounded-lg border p-1.5 text-xs transition cursor-pointer text-left"
                >
                    <div class="flex items-center gap-1.5 truncate">
                        <span class="text-sm">🧾</span>
                        <span class="font-semibold text-[11px] truncate">Kwitansi (4 Rangkap)</span>
                    </div>
                    <span x-show="activeTab === 'kwitansi'" class="text-primary-600 dark:text-primary-400 font-bold text-xs">✓</span>
                </button>
            @endif

            {{-- 4. NPD --}}
            @if ($hasNpd)
                <button
                    type="button"
                    @click="setDocument('npd', 'Nota Pencairan Dana (NPD)', '{{ $previewBaseUrl }}?tipe=npd', false)"
                    :class="activeTab === 'npd' ? 'border-primary-600 bg-primary-50 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300 dark:border-primary-500 font-bold' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800'"
                    class="w-full flex items-center justify-between rounded-lg border p-1.5 text-xs transition cursor-pointer text-left"
                >
                    <div class="flex items-center gap-1.5 truncate">
                        <span class="text-sm">💰</span>
                        <span class="font-semibold text-[11px] truncate">Nota Pencairan Dana</span>
                    </div>
                    <span x-show="activeTab === 'npd'" class="text-primary-600 dark:text-primary-400 font-bold text-xs">✓</span>
                </button>
            @endif

            {{-- 5. Faktur Toko --}}
            @if ($fileNotaUrl)
                <button
                    type="button"
                    @click="setDocument('nota_toko', 'Faktur / Nota Toko Asli', '{{ $fileNotaUrl }}', {{ $isNotaImage ? 'true' : 'false' }})"
                    :class="activeTab === 'nota_toko' ? 'border-primary-600 bg-primary-50 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300 dark:border-primary-500 font-bold' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800'"
                    class="w-full flex items-center justify-between rounded-lg border p-1.5 text-xs transition cursor-pointer text-left"
                >
                    <div class="flex items-center gap-1.5 truncate">
                        <span class="text-sm">📎</span>
                        <span class="font-semibold text-[11px] truncate">Faktur / Nota Toko</span>
                    </div>
                    <span x-show="activeTab === 'nota_toko'" class="text-primary-600 dark:text-primary-400 font-bold text-xs">✓</span>
                </button>
            @endif

            {{-- 6. Dokumentasi Barang --}}
            @if ($fileDokumentasiUrl)
                <button
                    type="button"
                    @click="setDocument('foto_barang', 'Foto Fisik Barang', '{{ $fileDokumentasiUrl }}', {{ $isDokumentasiImage ? 'true' : 'false' }})"
                    :class="activeTab === 'foto_barang' ? 'border-primary-600 bg-primary-50 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300 dark:border-primary-500 font-bold' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800'"
                    class="w-full flex items-center justify-between rounded-lg border p-1.5 text-xs transition cursor-pointer text-left"
                >
                    <div class="flex items-center gap-1.5 truncate">
                        <span class="text-sm">📷</span>
                        <span class="font-semibold text-[11px] truncate">Foto Fisik Barang</span>
                    </div>
                    <span x-show="activeTab === 'foto_barang'" class="text-primary-600 dark:text-primary-400 font-bold text-xs">✓</span>
                </button>
            @endif
        </div>

        {{-- CARD 3: TOMBOL AKSI CETAK & UNDUH PDF RESMI --}}
        <div class="space-y-1.5 pt-0.5">
            <button
                type="button"
                @click="printIframe()"
                class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-3 py-2 text-xs font-bold text-white shadow-xs transition hover:bg-primary-500 cursor-pointer"
            >
                <x-filament::icon icon="heroicon-o-printer" class="h-3.5 w-3.5" />
                <span>Cetak Dokumen</span>
            </button>

            <a
                :href="downloadUrl"
                target="_blank"
                class="w-full inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-xs transition hover:bg-gray-50 dark:border-white/15 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 cursor-pointer"
            >
                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-3.5 w-3.5 text-gray-500" />
                <span>Unduh File PDF</span>
            </a>
        </div>
    </div>

    {{-- ==================== KOLOM KANAN: PREVIEW DOKUMEN (HTML A4 / IMAGE) DENGAN TOOLBAR ==================== --}}
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
                    :href="isImage ? imageUrl : previewUrl"
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
            <template x-if="!isImage">
                <div style="width: 100%; height: 100%; overflow: auto;">
                    <iframe
                        id="spj-preview-frame-{{ $record->id }}"
                        :src="previewUrl"
                        style="width: 100%; height: 100%; border: none; display: block; background-color: #525659;"
                        :style="'transform: scale(' + (zoom / 100) + '); transform-origin: top center; width: ' + (10000 / zoom) + '%; height: ' + (10000 / zoom) + '%;'"
                        allow="fullscreen"
                    ></iframe>
                </div>
            </template>

            {{-- Tampilan Bukti Gambar (Foto Nota / Fisik Barang) --}}
            <template x-if="isImage">
                <div style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; overflow: auto; padding: 20px; background-color: #2b2e31;">
                    <img
                        :src="imageUrl"
                        alt="Bukti Lampiran SPJ"
                        style="max-width: 95%; max-height: 95%; object-fit: contain; border-radius: 8px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4); transition: transform 0.2s;"
                        :style="'transform: scale(' + (zoom / 100) + ');'"
                    />
                </div>
            </template>
        </div>
    </div>
</div>
