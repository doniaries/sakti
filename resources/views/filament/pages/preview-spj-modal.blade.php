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
                @click="setDocument('semua', 'Semua Dokumen SPJ Lengkap', '{{ $previewBaseUrl }}?tipe=semua', false)"
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
                @click="setDocument('pesanan', 'Surat Pesanan Barang (F4 Portrait)', '{{ $previewBaseUrl }}?tipe=pesanan', false)"
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
                    @click="setDocument('kwitansi', 'Kwitansi Pembayaran (F4 Bagi 2 Landscape - 4 Rangkap)', '{{ $previewBaseUrl }}?tipe=kwitansi', false)"
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
                    @click="setDocument('npd', 'Nota Pencairan Dana (F4 Portrait)', '{{ $previewBaseUrl }}?tipe=npd', false)"
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

            {{-- 5. Faktur Toko --}}
            @if ($fileNotaUrl)
                <button
                    type="button"
                    @click="setDocument('nota_toko', 'Faktur / Nota Toko Asli', '{{ $fileNotaUrl }}', {{ $isNotaImage ? 'true' : 'false' }})"
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

            {{-- 6. Dokumentasi Barang --}}
            @if ($fileDokumentasiUrl)
                <button
                    type="button"
                    @click="setDocument('foto_barang', 'Foto Fisik Barang', '{{ $fileDokumentasiUrl }}', {{ $isDokumentasiImage ? 'true' : 'false' }})"
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

        {{-- CARD 4: KETENTUAN PERPAJAKAN & PEMBAYARAN BERSIH (LENGKAP SEPERTI DI KWITANSI) --}}
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
                    <span class="text-[10px] font-mono font-bold bg-amber-50 dark:bg-amber-950/70 text-amber-700 dark:text-amber-300 px-2 py-0.5 rounded-full border border-amber-200 dark:border-amber-800">
                        PPN {{ $ppnPersen }}% + PPh {{ $pphPersen }}%
                    </span>
                </div>

                <div class="space-y-1.5 text-xs font-mono">
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Subtotal Barang (DPP):</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">Rp {{ number_format($subtotalBarang, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>PPN ({{ $ppnPersen }}%):</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200">+ Rp {{ number_format($nilaiPpn, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between items-center py-1 border-y border-dashed border-gray-200 dark:border-white/10 font-bold bg-gray-50/70 dark:bg-gray-800/40 px-1 rounded">
                        <span class="text-gray-700 dark:text-gray-300 font-sans font-semibold">Total Pesanan (Bruto):</span>
                        <span class="text-gray-900 dark:text-white">Rp {{ number_format($totalBruto, 0, ',', '.') }}</span>
                    </div>

                    <div class="pt-1 space-y-1">
                        <div class="flex justify-between text-[11px] text-gray-500 dark:text-gray-400">
                            <span>• Potongan PPN ({{ $ppnPersen }}%):</span>
                            <span class="text-danger-600 dark:text-danger-400 font-semibold">- Rp {{ number_format($nilaiPpn, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-[11px] text-gray-500 dark:text-gray-400">
                            <span>• Potongan PPh 22 ({{ $pphPersen }}%):</span>
                            <span class="text-danger-600 dark:text-danger-400 font-semibold">- Rp {{ number_format($nilaiPph, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-danger-700 dark:text-danger-400 font-bold">
                            <span>Total Potongan Kas:</span>
                            <span>- Rp {{ number_format($totalPajak, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="flex justify-between items-baseline pt-2 border-t-2 border-gray-200 dark:border-white/10 font-bold">
                        <span class="text-xs text-gray-700 dark:text-gray-200 font-sans font-bold">Diterima Bersih:</span>
                        <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format($nilaiBersih, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            @else
                <div class="flex items-center gap-2.5 p-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800/80">
                    <span class="text-lg text-emerald-600 dark:text-emerald-400 font-bold">✓</span>
                    <div class="flex-1">
                        <div class="text-xs font-extrabold text-emerald-800 dark:text-emerald-300">
                            Bebas Pajak (Belanja ≤ Rp 2 Jt)
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
