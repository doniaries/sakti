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

    $fileNotaUrl = $record->file_nota_penjual ? \Illuminate\Support\Facades\Storage::url($record->file_nota_penjual) : null;
    $fileDokumentasiUrl = $record->file_dokumentasi_barang ? \Illuminate\Support\Facades\Storage::url($record->file_dokumentasi_barang) : null;

    $isNotaImage = $fileNotaUrl && preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $fileNotaUrl);
    $isDokumentasiImage = $fileDokumentasiUrl && preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $fileDokumentasiUrl);

    $rawBank = $record->nama_bank ?: $record->masterToko?->nama_bank;
    $bankName = $rawBank instanceof \BackedEnum ? $rawBank->value : ($rawBank ?: '-');
@endphp

<div
    style="display: flex; flex-direction: row; gap: 16px; width: 100%; min-height: 750px; align-items: stretch;"
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
    {{-- ==================== KOLOM KIRI: INFORMASI TRANSAKSI & DAFTAR FILE (SCROLLABLE) ==================== --}}
    <div style="width: 360px; min-width: 340px; max-width: 380px; display: flex; flex-direction: column; gap: 12px; max-height: 760px; overflow-y: auto; padding-right: 6px;">
        
        {{-- CARD 1: STATUS & NOMOR PENGAJUAN --}}
        <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-sm dark:border-white/10 dark:bg-gray-900" style="font-size: 11.5px;">
            <div class="flex items-center justify-between border-b border-gray-100 pb-2 mb-2 dark:border-white/5">
                <span class="font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-[10px]">Informasi Transaksi SPJ</span>
                <span class="inline-flex rounded px-2 py-0.5 text-[10px] font-bold bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                    {{ strtoupper(str_replace('_', ' ', $record->status)) }}
                </span>
            </div>

            <div class="space-y-1.5 leading-relaxed">
                <div class="flex justify-between items-baseline">
                    <span class="text-gray-400 text-[10.5px]">No. Pengajuan:</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $record->nomor_pengajuan }}</span>
                </div>
                <div class="flex justify-between items-baseline">
                    <span class="text-gray-400 text-[10.5px]">Tanggal Pengajuan:</span>
                    <span class="text-gray-800 dark:text-gray-200 font-medium">
                        {{ \Carbon\Carbon::parse($record->tanggal_pengajuan)->translatedFormat('d F Y') }}
                    </span>
                </div>
                @if ($record->tanggal_batas_pengiriman)
                    <div class="flex justify-between items-baseline">
                        <span class="text-gray-400 text-[10.5px]">Batas Pengiriman:</span>
                        <span class="text-gray-800 dark:text-gray-200 font-medium">
                            {{ \Carbon\Carbon::parse($record->tanggal_batas_pengiriman)->translatedFormat('d F Y') }}
                        </span>
                    </div>
                @endif
                <div class="flex justify-between items-baseline pt-1.5 border-t border-gray-100 dark:border-white/5">
                    <span class="text-gray-400 text-[10.5px]">Total Nilai Belanja:</span>
                    <span class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400">
                        Rp {{ number_format((float) $totalNominal, 0, ',', '.') }}
                    </span>
                </div>
                @if ($selisih != 0)
                    <div class="flex justify-between items-baseline text-[10.5px]">
                        <span class="text-amber-600 dark:text-amber-400 font-medium">Deviasi / Selisih:</span>
                        <span class="font-bold {{ $selisih > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            {{ $selisih > 0 ? '+' : '' }}Rp {{ number_format((float) $selisih, 0, ',', '.') }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        {{-- CARD 2: KEGIATAN & KODE REKENING --}}
        <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-sm dark:border-white/10 dark:bg-gray-900" style="font-size: 11.5px;">
            <div class="font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-[10px] border-b border-gray-100 pb-1.5 mb-2 dark:border-white/5">
                Kegiatan & Anggaran
            </div>
            <div class="space-y-1.5 leading-tight">
                <div>
                    <span class="text-gray-400 text-[10px] block">Bidang Dinas:</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $record->bidang?->nama ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 text-[10px] block">Kegiatan:</span>
                    <span class="text-gray-800 dark:text-gray-200">{{ $record->kegiatan?->nama_kegiatan ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 text-[10px] block">Sub Kegiatan:</span>
                    <span class="text-gray-800 dark:text-gray-200">{{ $record->subKegiatan?->nama_sub_kegiatan ?? '-' }}</span>
                </div>
                @php
                    $firstRekening = $record->details->first()?->kodeRekening;
                @endphp
                @if ($firstRekening)
                    <div class="pt-1.5 border-t border-gray-100 dark:border-white/5">
                        <span class="text-gray-400 text-[10px] block">Kode Rekening Belanja:</span>
                        <span class="font-mono text-[10.5px] font-bold text-primary-600 dark:text-primary-400">{{ $firstRekening->kode_rekening }}</span>
                        <div class="text-[10px] text-gray-600 dark:text-gray-400 leading-snug">{{ $firstRekening->nama_rekening }}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- CARD 3: TOKO / REKANAN & PEMBAYARAN --}}
        <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-sm dark:border-white/10 dark:bg-gray-900" style="font-size: 11.5px;">
            <div class="font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-[10px] border-b border-gray-100 pb-1.5 mb-2 dark:border-white/5">
                Penyedia & Rekening Pembayaran
            </div>
            <div class="space-y-1.5 leading-tight">
                <div>
                    <span class="text-gray-400 text-[10px] block">Nama Toko / Rekanan:</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $record->nama_toko ?: ($record->masterToko?->nama_toko ?? '-') }}</span>
                </div>
                <div>
                    <span class="text-gray-400 text-[10px] block">Penerima Dana / Pemilik:</span>
                    <span class="text-gray-800 dark:text-gray-200 font-medium">{{ $record->penerima_nama ?: ($record->masterToko?->nama_pemilik ?? '-') }}</span>
                </div>
                <div>
                    <span class="text-gray-400 text-[10px] block">Bank & No. Rekening:</span>
                    <span class="text-gray-800 dark:text-gray-200 font-semibold">{{ $bankName }} — {{ $record->penerima_identitas ?: ($record->masterToko?->nomor_rekening ?? '-') }}</span>
                </div>
                @if ($record->alamat_toko || $record->masterToko?->alamat)
                    <div>
                        <span class="text-gray-400 text-[10px] block">Alamat Toko:</span>
                        <span class="text-[10.5px] text-gray-600 dark:text-gray-400 leading-snug">{{ $record->alamat_toko ?: $record->masterToko?->alamat }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- CARD 4: RINCIAN ITEM BARANG YANG DIBELANJAKAN --}}
        <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-sm dark:border-white/10 dark:bg-gray-900" style="font-size: 11.5px;">
            <div class="flex items-center justify-between border-b border-gray-100 pb-1.5 mb-2 dark:border-white/5">
                <span class="font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-[10px]">Rincian Barang ({{ $record->details->count() }} Item)</span>
                <span class="text-[10px] text-gray-400">Qty x Harga</span>
            </div>
            <div class="space-y-2 max-height-48 overflow-y-auto pr-1">
                @foreach ($record->details as $index => $detail)
                    @php
                        $qty = $detail->kuantitas_realisasi ?: $detail->kuantitas;
                        $harga = $detail->harga_realisasi ?: $detail->harga_satuan;
                        $subtotal = $detail->subtotal_realisasi ?: $detail->subtotal;
                        $namaBrg = $detail->nama_barang;
                        $satuan = $detail->satuan_nama;
                    @endphp
                    <div class="border-b border-gray-50 pb-1.5 last:border-b-0 dark:border-white/5">
                        <div class="font-semibold text-gray-900 dark:text-gray-100 text-[11px] leading-tight">
                            {{ $index + 1 }}. {{ $namaBrg }}
                        </div>
                        <div class="flex justify-between items-center text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">
                            <span>{{ $qty }} {{ $satuan }} &times; Rp {{ number_format((float) $harga, 0, ',', '.') }}</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200">Rp {{ number_format((float) $subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- CARD 5: PEJABAT PENANDATANGAN --}}
        <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-sm dark:border-white/10 dark:bg-gray-900" style="font-size: 11px;">
            <div class="font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-[10px] border-b border-gray-100 pb-1.5 mb-2 dark:border-white/5">
                Pejabat Penandatangan
            </div>
            <div class="grid grid-cols-2 gap-2 text-[10px]">
                <div>
                    <span class="text-gray-400 block text-[9.5px]">PPTK (Kabid):</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200 block truncate">{{ $record->pptk?->name ?? 'DONI ARIES, S.IP, M.CIO' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[9.5px]">PA (Kepala Dinas):</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200 block truncate">{{ $record->pa?->name ?? 'AFRINELDI, SH' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[9.5px]">PPK (Kasubag Keu):</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200 block truncate">{{ $record->ppk?->name ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[9.5px]">Bendahara:</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200 block truncate">{{ $record->bendahara?->name ?? 'WIWIRNA' }}</span>
                </div>
            </div>
        </div>

        {{-- CARD 6: DAFTAR DOKUMEN / FILE PRATINJAU --}}
        <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-sm dark:border-white/10 dark:bg-gray-900 space-y-1.5">
            <div class="flex items-center justify-between mb-1">
                <span class="font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-[10px]">Pilih Dokumen Pratinjau</span>
                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-medium">Bebas Unduh Otomatis</span>
            </div>

            {{-- 1. Semua Dokumen --}}
            <button
                type="button"
                @click="setDocument('semua', 'Semua Dokumen SPJ Lengkap', '{{ $previewBaseUrl }}?tipe=semua', false)"
                :class="activeTab === 'semua' ? 'border-primary-600 bg-primary-50 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300 dark:border-primary-500 font-bold' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800'"
                class="w-full flex items-center justify-between rounded-lg border p-2 text-xs transition cursor-pointer text-left"
            >
                <div class="flex items-center gap-2">
                    <span class="text-base">📄</span>
                    <div>
                        <div class="font-semibold text-[11.5px]">Semua Dokumen SPJ</div>
                        <div class="text-[10px] text-gray-500 dark:text-gray-400">Bundel lengkap SPJ</div>
                    </div>
                </div>
                <span x-show="activeTab === 'semua'" class="text-primary-600 dark:text-primary-400 font-bold">✓</span>
            </button>

            {{-- 2. Surat Pesanan --}}
            <button
                type="button"
                @click="setDocument('pesanan', 'Surat Pesanan Barang (Nota Pesanan)', '{{ $previewBaseUrl }}?tipe=pesanan', false)"
                :class="activeTab === 'pesanan' ? 'border-primary-600 bg-primary-50 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300 dark:border-primary-500 font-bold' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800'"
                class="w-full flex items-center justify-between rounded-lg border p-2 text-xs transition cursor-pointer text-left"
            >
                <div class="flex items-center gap-2">
                    <span class="text-base">📝</span>
                    <div>
                        <div class="font-semibold text-[11.5px]">Surat Pesanan Barang</div>
                        <div class="text-[10px] text-gray-500 dark:text-gray-400">Nota Pesanan TTD PPTK</div>
                    </div>
                </div>
                <span x-show="activeTab === 'pesanan'" class="text-primary-600 dark:text-primary-400 font-bold">✓</span>
            </button>

            {{-- 3. Kwitansi --}}
            @if ($hasKwitansi)
                <button
                    type="button"
                    @click="setDocument('kwitansi', 'Kwitansi Pembayaran (4 Rangkap)', '{{ $previewBaseUrl }}?tipe=kwitansi', false)"
                    :class="activeTab === 'kwitansi' ? 'border-primary-600 bg-primary-50 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300 dark:border-primary-500 font-bold' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800'"
                    class="w-full flex items-center justify-between rounded-lg border p-2 text-xs transition cursor-pointer text-left"
                >
                    <div class="flex items-center gap-2">
                        <span class="text-base">🧾</span>
                        <div>
                            <div class="font-semibold text-[11.5px]">Kwitansi (4 Rangkap)</div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400">Kwitansi 1 s.d. 4</div>
                        </div>
                    </div>
                    <span x-show="activeTab === 'kwitansi'" class="text-primary-600 dark:text-primary-400 font-bold">✓</span>
                </button>
            @endif

            {{-- 4. NPD --}}
            @if ($hasNpd)
                <button
                    type="button"
                    @click="setDocument('npd', 'Nota Pencairan Dana (NPD)', '{{ $previewBaseUrl }}?tipe=npd', false)"
                    :class="activeTab === 'npd' ? 'border-primary-600 bg-primary-50 text-primary-700 dark:bg-primary-950/70 dark:text-primary-300 dark:border-primary-500 font-bold' : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-gray-800/80 dark:text-gray-300 dark:hover:bg-gray-800'"
                    class="w-full flex items-center justify-between rounded-lg border p-2 text-xs transition cursor-pointer text-left"
                >
                    <div class="flex items-center gap-2">
                        <span class="text-base">💰</span>
                        <div>
                            <div class="font-semibold text-[11.5px]">Nota Pencairan Dana (NPD)</div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400">Otorisasi pencairan kas</div>
                        </div>
                    </div>
                    <span x-show="activeTab === 'npd'" class="text-primary-600 dark:text-primary-400 font-bold">✓</span>
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
                    <div class="flex items-center gap-2">
                        <span class="text-base">📎</span>
                        <div>
                            <div class="font-semibold text-[11.5px]">Faktur / Nota Toko Asli</div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400">Bukti struk/faktur rekanan</div>
                        </div>
                    </div>
                    <span x-show="activeTab === 'nota_toko'" class="text-primary-600 dark:text-primary-400 font-bold">✓</span>
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
                    <div class="flex items-center gap-2">
                        <span class="text-base">📷</span>
                        <div>
                            <div class="font-semibold text-[11.5px]">Foto Fisik Barang</div>
                            <div class="text-[10px] text-gray-500 dark:text-gray-400">Dokumentasi hasil belanja</div>
                        </div>
                    </div>
                    <span x-show="activeTab === 'foto_barang'" class="text-primary-600 dark:text-primary-400 font-bold">✓</span>
                </button>
            @endif
        </div>

        {{-- CARD 7: TOMBOL AKSI CETAK & UNDUH PDF RESMI --}}
        <div class="space-y-2 pt-1">
            <button
                type="button"
                @click="printIframe()"
                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-primary-500 cursor-pointer"
            >
                <x-filament::icon icon="heroicon-o-printer" class="h-4 w-4" />
                <span>Cetak Dokumen Sekarang</span>
            </button>

            <a
                :href="downloadUrl"
                target="_blank"
                class="w-full inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-white/15 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 cursor-pointer"
            >
                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="h-4 w-4 text-gray-500" />
                <span>Unduh File PDF Resmi</span>
            </a>
        </div>
    </div>

    {{-- ==================== KOLOM KANAN: PREVIEW DOKUMEN (HTML A4 / IMAGE) DENGAN TOOLBAR ==================== --}}
    <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; height: 760px; min-height: 650px;">
        
        {{-- Toolbar Atas --}}
        <div class="flex items-center justify-between bg-gray-100 dark:bg-gray-800 px-3 py-2 rounded-t-xl border border-b-0 border-gray-300 dark:border-white/10" style="font-size: 11.5px;">
            <div class="flex items-center gap-2 truncate">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                <span class="font-bold text-gray-800 dark:text-gray-200 truncate" x-text="activeTitle"></span>
            </div>

            <div class="flex items-center gap-2">
                {{-- Zoom Controls --}}
                <div class="inline-flex items-center rounded-lg border border-gray-300 bg-white dark:border-white/15 dark:bg-gray-900 overflow-hidden shadow-xs">
                    <button
                        type="button"
                        @click="zoomOut()"
                        class="px-2 py-1 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 font-bold transition cursor-pointer"
                        title="Perkecil (-)"
                    >
                        &minus;
                    </button>
                    <span class="px-2 py-1 text-[10.5px] font-mono font-semibold text-gray-700 dark:text-gray-300 select-none border-x border-gray-200 dark:border-white/10" x-text="zoom + '%'"></span>
                    <button
                        type="button"
                        @click="zoomIn()"
                        class="px-2 py-1 text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 font-bold transition cursor-pointer"
                        title="Perbesar (+)"
                    >
                        +
                    </button>
                    <button
                        type="button"
                        @click="zoomReset()"
                        class="px-2 py-1 text-[10px] text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800 border-l border-gray-200 dark:border-white/10 transition cursor-pointer"
                        title="Reset 100%"
                    >
                        100%
                    </button>
                </div>

                {{-- Buka Layar Penuh Tab Baru --}}
                <a
                    :href="isImage ? imageUrl : previewUrl"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-2.5 py-1 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 dark:border-white/15 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800 shadow-xs cursor-pointer"
                    title="Buka pratinjau di tab baru"
                >
                    <x-filament::icon icon="heroicon-o-arrows-pointing-out" class="h-3.5 w-3.5" />
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
                        style="width: 100%; height: 100%; min-height: 700px; border: none; display: block; background-color: #525659;"
                        :style="'transform: scale(' + (zoom / 100) + '); transform-origin: top center; width: ' + (10000 / zoom) + '%; height: ' + (10000 / zoom) + '%;'"
                        allow="fullscreen"
                    ></iframe>
                </div>
            </template>

            {{-- Tampilan Bukti Gambar (Foto Nota / Fisik Barang) --}}
            <template x-if="isImage">
                <div style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; overflow: auto; padding: 24px; background-color: #2b2e31;">
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
