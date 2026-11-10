@php
    /** @var \App\Models\PengajuanSpj $record */
    $record->loadMissing([
        'bidang',
        'kegiatan.program',
        'subKegiatan',
        'user',
        'pptk',
        'ppk',
        'pa',
        'bendahara',
        'masterToko',
        'details.masterBarang.satuan',
        'details.rincianAnggaranBelanja',
    ]);

    $totalNominal = (float) ($record->total_nilai_realisasi ?: $record->total_nilai_rencana);
    $totalItem = $record->details ? $record->details->count() : 0;
    $totalVolume = $record->details ? $record->details->sum(fn ($d) => (float) ($d->kuantitas_realisasi ?: $d->kuantitas)) : 0;

    $rawBank = $record->nama_bank ?: $record->masterToko?->nama_bank;
    $namaBank = $rawBank instanceof \BackedEnum ? $rawBank->value : ($rawBank ?: '-');
@endphp

<div x-data="{ activeTab: 'ringkasan' }" class="space-y-3 text-xs text-gray-800 dark:text-gray-200">
    {{-- 1. HEADER: STATUS BIASA & JUMLAH UANG --}}
    <div class="rounded-xl border border-gray-200 bg-gray-50/80 p-3.5 shadow-2xs dark:border-white/10 dark:bg-white/5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Status Pengajuan:
                </span>
                <div class="mt-1 flex items-center gap-2">
                    <span
                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold
                        {{ match ($record->status_color) {
                            'success' => 'bg-green-100 text-green-800 dark:bg-green-950/60 dark:text-green-300',
                            'danger' => 'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300',
                            'warning' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300',
                            'info' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300',
                            default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
                        } }}">
                        <span class="h-1.5 w-1.5 rounded-full {{ match ($record->status_color) {
                            'success' => 'bg-green-600',
                            'danger' => 'bg-red-600',
                            'warning' => 'bg-amber-600',
                            'info' => 'bg-blue-600',
                            default => 'bg-gray-600',
                        } }}"></span>
                        {{ $record->status_label }}
                    </span>
                </div>
                <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                    Nomor: <strong class="font-mono text-gray-800 dark:text-gray-200">{{ $record->nomor_pengajuan }}</strong>
                    &bull; Tanggal: {{ $record->tanggal_pengajuan ? $record->tanggal_pengajuan->format('d/m/Y') : '-' }}
                </div>
            </div>

            <div class="text-right">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Jumlah Uang (Total Belanja):
                </span>
                <div class="text-lg font-black text-primary-600 dark:text-primary-400">
                    Rp {{ number_format($totalNominal, 0, ',', '.') }}
                </div>
                <div class="text-[11px] text-gray-500 dark:text-gray-400">
                    Volume: <strong class="text-gray-800 dark:text-gray-200">{{ number_format($totalVolume, 0, ',', '.') }} unit/item</strong> ({{ $totalItem }} Rincian Barang)
                </div>
            </div>
        </div>

        {{-- Alert Catatan Revisi jika ada --}}
        @if (filled($record->catatan_revisi) && str_contains($record->status, 'revisi'))
            <div class="mt-2.5 rounded-lg border border-red-300 bg-red-100/70 p-2 text-xs text-red-900 dark:border-red-700 dark:bg-red-950/60 dark:text-red-200">
                <strong class="font-bold">⚠️ Catatan Revisi:</strong>
                <span class="italic font-mono pl-1">"{{ $record->catatan_revisi }}"</span>
            </div>
        @endif
    </div>

    {{-- 2. TAB NAVIGASI RINGKAS --}}
    <div class="flex items-center gap-2 border-b border-gray-200 dark:border-white/10 pt-1 text-xs">
        <button
            type="button"
            x-on:click="activeTab = 'ringkasan'"
            :class="activeTab === 'ringkasan' ? 'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400 border-b-2 font-bold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 font-medium'"
            class="px-3 pb-2 transition-colors cursor-pointer"
        >
            Informasi Pengajuan
        </button>
        <button
            type="button"
            x-on:click="activeTab = 'rincian'"
            :class="activeTab === 'rincian' ? 'border-primary-600 text-primary-600 dark:border-primary-400 dark:text-primary-400 border-b-2 font-bold' : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 font-medium'"
            class="px-3 pb-2 transition-colors cursor-pointer"
        >
            Rincian Barang & Volume ({{ $totalItem }})
        </button>
    </div>

    {{-- TAB 1: INFORMASI RINGKASAN --}}
    <div x-show="activeTab === 'ringkasan'" class="space-y-3 pt-1">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            {{-- Kiri: Pengusul & Yang Diusulkan --}}
            <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-2xs dark:border-white/10 dark:bg-gray-900 space-y-2">
                <h4 class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5 border-b border-gray-100 pb-1.5 dark:border-white/5">
                    <svg class="h-4 w-4 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Pengusul & Yang Diusulkan
                </h4>
                <div class="space-y-1 text-[11px]">
                    <div class="flex justify-between"><span class="text-gray-500">Pengusul (Pemohon):</span><strong class="text-gray-900 dark:text-white">{{ $record->user?->name ?? '-' }}</strong></div>
                    <div class="flex justify-between"><span class="text-gray-500">Bidang Pengusul:</span><span class="font-semibold">{{ $record->bidang?->nama ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">PPTK Ditugaskan:</span><span class="font-semibold">{{ $record->pptk?->name ?? '-' }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Tahun Anggaran:</span><strong class="text-primary-600 dark:text-primary-400">{{ $record->tahun_anggaran }}</strong></div>
                    <div class="flex justify-between"><span class="text-gray-500">Jenis Belanja:</span><span>{{ $record->jenis_belanja_label }} ({{ $record->metode_pengadaan_label }})</span></div>
                    
                    <div class="pt-1.5 border-t border-gray-100 dark:border-white/5">
                        <span class="text-gray-500 block text-[10px]">Yang Diusulkan (Sub Kegiatan SIPD):</span>
                        <strong class="text-gray-900 dark:text-white block mt-0.5">{{ $record->subKegiatan?->nama_sub_kegiatan ?? '-' }}</strong>
                        @if ($record->kegiatan)
                            <span class="text-gray-400 block text-[10px] mt-0.5">Kegiatan: {{ $record->kegiatan->nama_kegiatan }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Kanan: Rekanan Toko & Rekapitulasi Pembayaran --}}
            <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-2xs dark:border-white/10 dark:bg-gray-900 space-y-2">
                <h4 class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5 border-b border-gray-100 pb-1.5 dark:border-white/5">
                    <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Penyedia, Rekening & Pajak
                </h4>
                <div class="space-y-1 text-[11px]">
                    <div class="flex justify-between"><span class="text-gray-500">Penyedia / Toko:</span><strong class="text-gray-900 dark:text-white">{{ $record->nama_toko ?: ($record->masterToko?->nama_toko ?: '-') }}</strong></div>
                    <div class="flex justify-between"><span class="text-gray-500">Telepon Toko:</span><span>{{ $record->telepon_toko ?: ($record->masterToko?->telepon ?: '-') }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Bank Tujuan:</span><span class="font-semibold">{{ $namaBank }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">No. Rek / Penerima:</span><span class="font-mono">{{ $record->penerima_identitas ?: '-' }} ({{ $record->penerima_nama ?: '-' }})</span></div>

                    <div class="pt-1.5 border-t border-gray-100 dark:border-white/5 grid grid-cols-2 gap-1.5">
                        <div>
                            <span class="text-gray-500 block text-[10px]">PPN 11%:</span>
                            <strong class="font-mono text-gray-800 dark:text-gray-200">Rp {{ number_format((float) $record->nilai_ppn, 0, ',', '.') }}</strong>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-[10px]">PPh 22 (1.5%):</span>
                            <strong class="font-mono text-gray-800 dark:text-gray-200">Rp {{ number_format((float) $record->nilai_pph22, 0, ',', '.') }}</strong>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-[10px]">Total Pajak:</span>
                            <strong class="font-mono text-red-600 dark:text-red-400">Rp {{ number_format((float) $record->total_pajak, 0, ',', '.') }}</strong>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-[10px]">Nilai Bersih Rekanan:</span>
                            <strong class="font-mono text-emerald-600 dark:text-emerald-400">Rp {{ number_format((float) $record->nilai_bersih, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 2: RINCIAN ITEM & VOLUME --}}
    <div x-show="activeTab === 'rincian'" class="space-y-3 pt-1" x-cloak>
        @if ($record->details && $record->details->isNotEmpty())
            <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-2xs dark:border-white/10 dark:bg-gray-900">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[11px] border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400">
                                <th class="py-1.5 pr-2 w-8 text-center">No</th>
                                <th class="py-1.5 px-2">Uraian Barang / Yang Diusulkan</th>
                                <th class="py-1.5 px-2">Keterangan</th>
                                <th class="py-1.5 px-2 text-center">Volume</th>
                                <th class="py-1.5 px-2 text-right">Harga Satuan</th>
                                <th class="py-1.5 pl-2 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach ($record->details as $idx => $item)
                                @php
                                    $qty = (float) ($item->kuantitas_realisasi ?: $item->kuantitas);
                                    $harga = (float) ($item->harga_realisasi ?: $item->harga_satuan);
                                    $sub = (float) ($item->subtotal_realisasi ?: $item->subtotal);
                                    $satuan = $item->satuan_nama;
                                @endphp
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5">
                                    <td class="py-1.5 pr-2 text-center text-gray-400">{{ $idx + 1 }}</td>
                                    <td class="py-1.5 px-2 font-semibold text-gray-900 dark:text-white">
                                        {{ $item->nama_barang }}
                                    </td>
                                    <td class="py-1.5 px-2 text-gray-500">{{ $item->keterangan ?: '-' }}</td>
                                    <td class="py-1.5 px-2 text-center font-mono font-bold text-gray-900 dark:text-white">
                                        {{ number_format($qty, 0, ',', '.') }} {{ $satuan }}
                                    </td>
                                    <td class="py-1.5 px-2 text-right font-mono text-gray-700 dark:text-gray-300">
                                        Rp {{ number_format($harga, 0, ',', '.') }}
                                    </td>
                                    <td class="py-1.5 pl-2 text-right font-mono font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($sub, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-200 dark:border-white/10 font-bold text-gray-900 dark:text-white bg-gray-50/50 dark:bg-white/5">
                                <td colspan="3" class="py-2 px-2 text-right">Total Keseluruhan:</td>
                                <td class="py-2 px-2 text-center font-mono text-primary-600 dark:text-primary-400">
                                    {{ number_format($totalVolume, 0, ',', '.') }} unit/item
                                </td>
                                <td></td>
                                <td class="py-2 pl-2 text-right font-mono text-primary-600 dark:text-primary-400">
                                    Rp {{ number_format($totalNominal, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @else
            <div class="p-6 text-center text-gray-500 dark:text-gray-400 italic bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10">
                Belum ada rincian item barang yang diusulkan.
            </div>
        @endif
    </div>
</div>
