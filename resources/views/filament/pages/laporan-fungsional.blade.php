<x-filament-panels::page>
    <div x-data="{ activeTab: @entangle('activeTab').live }" class="space-y-4">

        {{-- BARIS FILTER TAHUN DAN BULAN --}}
        <div class="laporan-card p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:!text-zinc-200 mb-1.5">Tahun Anggaran</label>
                    <select wire:model.live="tahun"
                        class="w-full text-sm rounded-lg border border-gray-300 dark:!border-zinc-700 bg-white dark:!bg-zinc-800 text-gray-900 dark:!text-white py-2 px-3 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        @for($y = (int) date('Y') - 1; $y <= (int) date('Y') + 2; $y++)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:!text-zinc-200 mb-1.5">Periode s/d Bulan</label>
                    <select wire:model.live="bulan"
                        class="w-full text-sm rounded-lg border border-gray-300 dark:!border-zinc-700 bg-white dark:!bg-zinc-800 text-gray-900 dark:!text-white py-2 px-3 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        @foreach($daftarBulan as $bKey => $bVal)
                            <option value="{{ $bKey }}">{{ $bVal }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- TAB NAVIGASI PERMANEN (SELALU TAMPIL, MENGGUNAKAN ALPINE SEHINGGA TIDAK HILANG SAAT DIKLIK) --}}
        <div wire:key="fungsional-tabs-navigation" class="flex items-center gap-2 border-b border-gray-200 dark:!border-zinc-800 pb-2">
            <button type="button"
                @click="activeTab = 'rekening'"
                :class="activeTab === 'rekening' ? 'active' : ''"
                class="laporan-tab-btn flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <span>1. Realisasi Belanja (14 Kolom SIPD-RI)</span>
            </button>

            <button type="button"
                @click="activeTab = 'pajak'"
                :class="activeTab === 'pajak' ? 'active' : ''"
                class="laporan-tab-btn flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"></path>
                </svg>
                <span>2. Rekapitulasi Pajak & Potongan</span>
            </button>
        </div>

        {{-- TAB 1: REALISASI PER KODE REKENING (14 KOLOM SIPD-RI) --}}
        <div x-show="activeTab === 'rekening'" wire:key="tab-panel-rekening" class="space-y-4">
            <div class="laporan-card">
                <div class="laporan-card-header flex flex-col md:flex-row md:justify-between md:items-center gap-2">
                    <div>
                        <h3 class="laporan-card-title text-base">Laporan Pertanggungjawaban Bendahara Pengeluaran (LPJ Fungsional)</h3>
                        <p class="laporan-card-sub text-xs mt-0.5">
                            Periode: s/d <strong>{{ $namaBulanAktif }} {{ $tahun }}</strong> (Standar Penatausahaan SIPD-RI / Permendagri 77/2020)
                        </p>
                    </div>
                </div>

                {{-- TABEL 14 KOLOM SIPD DENGAN STICKY HEADER & DUA KOLOM STICKY KIRI PRESISI --}}
                <div class="overflow-auto max-h-[720px] relative">
                    <table class="w-full text-left text-xs border-collapse laporan-table laporan-fungsional-table">
                        <thead>
                            <tr class="border-b border-gray-200 dark:!border-zinc-700 text-center uppercase tracking-wider text-[11px] font-bold">
                                <th rowspan="2" class="p-2.5 fungsional-col-1 border-r border-b">Kode Rekening</th>
                                <th rowspan="2" class="p-2.5 fungsional-col-2 border-r border-b text-left">Uraian</th>
                                <th rowspan="2" class="p-2.5 w-32 border-r border-b text-right">Jumlah Anggaran</th>
                                <th colspan="3" class="p-1.5 border-r border-b text-center">SPJ - LS Gaji</th>
                                <th colspan="3" class="p-1.5 border-r border-b text-center">SPJ - LS Barang Jasa</th>
                                <th colspan="3" class="p-1.5 border-r border-b text-center">SPJ - UP/GU/TU</th>
                                <th rowspan="2" class="p-2.5 w-36 border-r border-b text-right leading-tight">Jumlah SPJ (LS+UP/GU/TU)<br><span class="text-[10px] font-normal">s.d Bulan ini</span></th>
                                <th rowspan="2" class="p-2.5 w-32 border-b text-right">Sisa Pagu Anggaran</th>
                            </tr>
                            <tr class="text-[10px] border-b border-gray-300 dark:!border-zinc-700 text-center font-semibold">
                                {{-- LS Gaji --}}
                                <th class="p-1.5 border-r border-b w-28">s.d. Bulan Lalu</th>
                                <th class="p-1.5 border-r border-b w-28">Bulan Ini</th>
                                <th class="p-1.5 border-r border-b w-28">s.d. Bulan Ini</th>
                                {{-- LS Barang Jasa --}}
                                <th class="p-1.5 border-r border-b w-28">s.d. Bulan Lalu</th>
                                <th class="p-1.5 border-r border-b w-28">Bulan Ini</th>
                                <th class="p-1.5 border-r border-b w-28">s.d. Bulan Ini</th>
                                {{-- UP / GU / TU --}}
                                <th class="p-1.5 border-r border-b w-28">s.d. Bulan Lalu</th>
                                <th class="p-1.5 border-r border-b w-28">Bulan Ini</th>
                                <th class="p-1.5 border-r border-b w-28">s.d. Bulan Ini</th>
                            </tr>
                            <tr class="text-[10px] border-b border-gray-300 dark:!border-zinc-700 text-center font-semibold bg-gray-100 dark:!bg-zinc-800">
                                <th class="p-1 fungsional-col-1 border-r border-b">1</th>
                                <th class="p-1 fungsional-col-2 border-r border-b">2</th>
                                <th class="p-1 border-r border-b">3</th>
                                <th class="p-1 border-r border-b">4</th>
                                <th class="p-1 border-r border-b">5</th>
                                <th class="p-1 border-r border-b">6=(4+5)</th>
                                <th class="p-1 border-r border-b">7</th>
                                <th class="p-1 border-r border-b">8</th>
                                <th class="p-1 border-r border-b">9=(7+8)</th>
                                <th class="p-1 border-r border-b">10</th>
                                <th class="p-1 border-r border-b">11</th>
                                <th class="p-1 border-r border-b">12=(10+11)</th>
                                <th class="p-1 border-r border-b">13=(6+9+12)</th>
                                <th class="p-1 border-b">14=(3-13)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:!divide-zinc-800 text-[11px]">
                            @forelse($data['rows'] as $r)
                                @php
                                    $rowClass = match($r['type']) {
                                        'summary_header' => 'bg-header-row font-bold text-gray-900 dark:!text-white',
                                        'skpd' => 'bg-skpd-row font-bold text-amber-950 dark:!text-amber-200',
                                        'program' => 'bg-prog-row font-bold text-gray-900 dark:!text-white',
                                        'kegiatan' => 'font-bold text-gray-900 dark:!text-white bg-slate-50/60 dark:!bg-zinc-800/40',
                                        'sub_kegiatan' => 'font-semibold text-gray-900 dark:!text-zinc-100',
                                        'akun_parent' => 'font-medium text-gray-800 dark:!text-zinc-200',
                                        default => 'text-gray-700 dark:!text-zinc-300',
                                    };
                                    $indentPx = ($r['indent'] ?? 0) * 14;
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    {{-- Col 1: Kode Rekening --}}
                                    <td class="p-2 fungsional-col-1 border-r font-mono whitespace-nowrap {{ $r['is_bold'] ? 'font-bold' : '' }}">
                                        {{ $r['kode'] }}
                                    </td>
                                    {{-- Col 2: Uraian --}}
                                    <td class="p-2 fungsional-col-2 border-r {{ $r['is_bold'] ? 'font-bold' : '' }}">
                                        <div style="padding-left: {{ $indentPx }}px;">
                                            {{ $r['uraian'] }}
                                        </div>
                                    </td>
                                    {{-- Col 3: Anggaran --}}
                                    <td class="p-2 text-right font-mono border-r whitespace-nowrap {{ $r['is_bold'] ? 'font-bold text-gray-900 dark:!text-white' : '' }}">
                                        {{ $r['pagu'] > 0 ? number_format($r['pagu'], 2, ',', '.') : '0,00' }}
                                    </td>
                                    {{-- Col 4..6: LS Gaji --}}
                                    <td class="p-2 text-right font-mono border-r whitespace-nowrap text-gray-600 dark:!text-zinc-400">
                                        {{ $r['ls_gaji_lalu'] > 0 ? number_format($r['ls_gaji_lalu'], 2, ',', '.') : '0,00' }}
                                    </td>
                                    <td class="p-2 text-right font-mono border-r whitespace-nowrap text-gray-600 dark:!text-zinc-400">
                                        {{ $r['ls_gaji_ini'] > 0 ? number_format($r['ls_gaji_ini'], 2, ',', '.') : '0,00' }}
                                    </td>
                                    <td class="p-2 text-right font-mono border-r whitespace-nowrap font-semibold text-gray-900 dark:!text-white">
                                        {{ $r['ls_gaji_sd_ini'] > 0 ? number_format($r['ls_gaji_sd_ini'], 2, ',', '.') : '0,00' }}
                                    </td>
                                    {{-- Col 7..9: LS Barang Jasa --}}
                                    <td class="p-2 text-right font-mono border-r whitespace-nowrap text-gray-600 dark:!text-zinc-400">
                                        {{ $r['ls_bj_lalu'] > 0 ? number_format($r['ls_bj_lalu'], 2, ',', '.') : '0,00' }}
                                    </td>
                                    <td class="p-2 text-right font-mono border-r whitespace-nowrap text-gray-600 dark:!text-zinc-400">
                                        {{ $r['ls_bj_ini'] > 0 ? number_format($r['ls_bj_ini'], 2, ',', '.') : '0,00' }}
                                    </td>
                                    <td class="p-2 text-right font-mono border-r whitespace-nowrap font-semibold text-gray-900 dark:!text-white">
                                        {{ $r['ls_bj_sd_ini'] > 0 ? number_format($r['ls_bj_sd_ini'], 2, ',', '.') : '0,00' }}
                                    </td>
                                    {{-- Col 10..12: UP/GU/TU --}}
                                    <td class="p-2 text-right font-mono border-r whitespace-nowrap text-gray-600 dark:!text-zinc-400">
                                        {{ $r['up_lalu'] > 0 ? number_format($r['up_lalu'], 2, ',', '.') : '0,00' }}
                                    </td>
                                    <td class="p-2 text-right font-mono border-r whitespace-nowrap text-gray-600 dark:!text-zinc-400">
                                        {{ $r['up_ini'] > 0 ? number_format($r['up_ini'], 2, ',', '.') : '0,00' }}
                                    </td>
                                    <td class="p-2 text-right font-mono border-r whitespace-nowrap font-semibold text-gray-900 dark:!text-white">
                                        {{ $r['up_sd_ini'] > 0 ? number_format($r['up_sd_ini'], 2, ',', '.') : '0,00' }}
                                    </td>
                                    {{-- Col 13: Jumlah SPJ s.d Bulan Ini --}}
                                    <td class="p-2 text-right font-mono border-r whitespace-nowrap font-bold text-emerald-600 dark:!text-emerald-400">
                                        {{ $r['total_spj'] > 0 ? number_format($r['total_spj'], 2, ',', '.') : '0,00' }}
                                    </td>
                                    {{-- Col 14: Sisa Pagu Anggaran --}}
                                    <td class="p-2 text-right font-mono whitespace-nowrap font-semibold text-gray-900 dark:!text-white">
                                        {{ number_format($r['sisa'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" class="p-8 text-center text-gray-500 dark:!text-zinc-400">Belum ada realisasi anggaran belanja pada tahun {{ $tahun }}.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="font-bold border-t-2 sticky bottom-0 z-20 shadow-lg">
                                {{-- Sticky Footer Col 1 & 2 terpisah dengan teks JUMLAH di Col 1 dan Uraian di Col 2 tanpa overlap --}}
                                <td class="p-2.5 fungsional-col-1 text-center font-bold border-r bg-slate-100 dark:!bg-zinc-900">
                                    JUMLAH
                                </td>
                                <td class="p-2.5 fungsional-col-2 font-bold border-r bg-slate-100 dark:!bg-zinc-900 uppercase">
                                    Total Belanja Daerah
                                </td>
                                <td class="p-2.5 text-right font-mono whitespace-nowrap border-r text-amber-600 dark:!text-amber-400 font-bold bg-slate-100 dark:!bg-zinc-900">
                                    {{ number_format($data['summary']['pagu'], 2, ',', '.') }}
                                </td>
                                <td class="p-2.5 text-right font-mono whitespace-nowrap border-r bg-slate-100 dark:!bg-zinc-900">
                                    {{ number_format($data['summary']['ls_gaji_lalu'], 2, ',', '.') }}
                                </td>
                                <td class="p-2.5 text-right font-mono whitespace-nowrap border-r bg-slate-100 dark:!bg-zinc-900">
                                    {{ number_format($data['summary']['ls_gaji_ini'], 2, ',', '.') }}
                                </td>
                                <td class="p-2.5 text-right font-mono whitespace-nowrap border-r font-semibold bg-slate-100 dark:!bg-zinc-900">
                                    {{ number_format($data['summary']['ls_gaji_sd_ini'], 2, ',', '.') }}
                                </td>
                                <td class="p-2.5 text-right font-mono whitespace-nowrap border-r bg-slate-100 dark:!bg-zinc-900">
                                    {{ number_format($data['summary']['ls_bj_lalu'], 2, ',', '.') }}
                                </td>
                                <td class="p-2.5 text-right font-mono whitespace-nowrap border-r bg-slate-100 dark:!bg-zinc-900">
                                    {{ number_format($data['summary']['ls_bj_ini'], 2, ',', '.') }}
                                </td>
                                <td class="p-2.5 text-right font-mono whitespace-nowrap border-r font-semibold bg-slate-100 dark:!bg-zinc-900">
                                    {{ number_format($data['summary']['ls_bj_sd_ini'], 2, ',', '.') }}
                                </td>
                                <td class="p-2.5 text-right font-mono whitespace-nowrap border-r bg-slate-100 dark:!bg-zinc-900">
                                    {{ number_format($data['summary']['up_lalu'], 2, ',', '.') }}
                                </td>
                                <td class="p-2.5 text-right font-mono whitespace-nowrap border-r bg-slate-100 dark:!bg-zinc-900">
                                    {{ number_format($data['summary']['up_ini'], 2, ',', '.') }}
                                </td>
                                <td class="p-2.5 text-right font-mono whitespace-nowrap border-r font-semibold bg-slate-100 dark:!bg-zinc-900">
                                    {{ number_format($data['summary']['up_sd_ini'], 2, ',', '.') }}
                                </td>
                                <td class="p-2.5 text-right font-mono whitespace-nowrap border-r font-bold text-emerald-600 dark:!text-emerald-400 bg-slate-100 dark:!bg-zinc-900">
                                    {{ number_format($data['summary']['total_spj'], 2, ',', '.') }}
                                </td>
                                <td class="p-2.5 text-right font-mono whitespace-nowrap font-bold text-gray-900 dark:!text-white bg-slate-100 dark:!bg-zinc-900">
                                    {{ number_format($data['summary']['sisa'], 2, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- BAGIAN LAMPIRAN REKAPITULASI PENERIMAAN & PENGELUARAN (STANDAR SIPD-RI) --}}
                <div class="p-4 border-t border-gray-200 dark:!border-zinc-800 grid grid-cols-1 lg:grid-cols-2 gap-6 bg-slate-50/50 dark:!bg-zinc-900/40">
                    {{-- KOLOM KIRI: PENERIMAAN --}}
                    <div class="rounded-lg border border-gray-200 dark:!border-zinc-700 p-4 bg-white dark:!bg-zinc-800 shadow-sm">
                        <h4 class="font-bold text-sm text-gray-900 dark:!text-white border-b pb-2 mb-3">Penerimaan :</h4>
                        <table class="w-full text-xs">
                            <tbody>
                                <tr class="font-semibold text-gray-700 dark:!text-zinc-200">
                                    <td colspan="4" class="py-1">- SP2D :</td>
                                </tr>
                                <tr class="text-gray-600 dark:!text-zinc-300">
                                    <td class="pl-4 py-1">SP2D UP</td>
                                    <td class="text-right font-mono">{{ number_format($data['penerimaan']['up']['lalu'], 2, ',', '.') }}</td>
                                    <td class="text-right font-mono">{{ number_format($data['penerimaan']['up']['ini'], 2, ',', '.') }}</td>
                                    <td class="text-right font-mono font-semibold">{{ number_format($data['penerimaan']['up']['sd_ini'], 2, ',', '.') }}</td>
                                </tr>
                                <tr class="text-gray-600 dark:!text-zinc-300">
                                    <td class="pl-4 py-1">SP2D GU</td>
                                    <td class="text-right font-mono">{{ number_format($data['penerimaan']['gu']['lalu'], 2, ',', '.') }}</td>
                                    <td class="text-right font-mono">{{ number_format($data['penerimaan']['gu']['ini'], 2, ',', '.') }}</td>
                                    <td class="text-right font-mono font-semibold">{{ number_format($data['penerimaan']['gu']['sd_ini'], 2, ',', '.') }}</td>
                                </tr>
                                <tr class="text-gray-600 dark:!text-zinc-300">
                                    <td class="pl-4 py-1">SP2D TU</td>
                                    <td class="text-right font-mono">{{ number_format($data['penerimaan']['tu']['lalu'], 2, ',', '.') }}</td>
                                    <td class="text-right font-mono">{{ number_format($data['penerimaan']['tu']['ini'], 2, ',', '.') }}</td>
                                    <td class="text-right font-mono font-semibold">{{ number_format($data['penerimaan']['tu']['sd_ini'], 2, ',', '.') }}</td>
                                </tr>
                                <tr class="text-gray-600 dark:!text-zinc-300">
                                    <td class="pl-4 py-1">SP2D LS</td>
                                    <td class="text-right font-mono">{{ number_format($data['penerimaan']['ls']['lalu'], 2, ',', '.') }}</td>
                                    <td class="text-right font-mono">{{ number_format($data['penerimaan']['ls']['ini'], 2, ',', '.') }}</td>
                                    <td class="text-right font-mono font-semibold">{{ number_format($data['penerimaan']['ls']['sd_ini'], 2, ',', '.') }}</td>
                                </tr>
                                <tr class="font-semibold text-gray-700 dark:!text-zinc-200 border-t">
                                    <td colspan="4" class="py-1 pt-2">- Potongan :</td>
                                </tr>
                                <tr class="text-gray-600 dark:!text-zinc-300">
                                    <td class="pl-4 py-1">PPN (11%)</td>
                                    <td colspan="3" class="text-right font-mono">{{ number_format($data['pajak']['ppn'], 2, ',', '.') }}</td>
                                </tr>
                                <tr class="text-gray-600 dark:!text-zinc-300">
                                    <td class="pl-4 py-1">PPh Pasal 22 (1,5%)</td>
                                    <td colspan="3" class="text-right font-mono">{{ number_format($data['pajak']['pph22'], 2, ',', '.') }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="font-bold border-t text-gray-900 dark:!text-white">
                                    <td class="py-2">JUMLAH PENERIMAAN</td>
                                    <td colspan="3" class="text-right font-mono text-emerald-600 dark:!text-emerald-400">
                                        Rp {{ number_format($data['penerimaan']['total_bruto'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- KOLOM KANAN: PENGELUARAN & SALDO KAS --}}
                    <div class="rounded-lg border border-gray-200 dark:!border-zinc-700 p-4 bg-white dark:!bg-zinc-800 shadow-sm">
                        <h4 class="font-bold text-sm text-gray-900 dark:!text-white border-b pb-2 mb-3">Pengeluaran :</h4>
                        <table class="w-full text-xs">
                            <tbody>
                                <tr class="text-gray-600 dark:!text-zinc-300">
                                    <td class="py-1">- SPJ (UP/GU)</td>
                                    <td class="text-right font-mono">{{ number_format($data['pengeluaran']['spj_up_gu'], 2, ',', '.') }}</td>
                                </tr>
                                <tr class="text-gray-600 dark:!text-zinc-300">
                                    <td class="py-1">- SPJ (TU)</td>
                                    <td class="text-right font-mono">{{ number_format($data['pengeluaran']['spj_tu'], 2, ',', '.') }}</td>
                                </tr>
                                <tr class="text-gray-600 dark:!text-zinc-300">
                                    <td class="py-1">- SPJ (LS)</td>
                                    <td class="text-right font-mono">{{ number_format($data['pengeluaran']['spj_ls'], 2, ',', '.') }}</td>
                                </tr>
                                <tr class="text-gray-600 dark:!text-zinc-300">
                                    <td class="py-1">- Penyetoran Pajak</td>
                                    <td class="text-right font-mono">{{ number_format($data['pajak']['total_pajak'], 2, ',', '.') }}</td>
                                </tr>
                                <tr class="text-gray-600 dark:!text-zinc-300">
                                    <td class="py-1">- Pengembalian Kasda (STS)</td>
                                    <td class="text-right font-mono">{{ number_format($data['pengeluaran']['pengembalian_sts'], 2, ',', '.') }}</td>
                                </tr>
                                <tr class="font-bold border-t text-gray-900 dark:!text-white">
                                    <td class="py-2">JUMLAH PENGELUARAN</td>
                                    <td class="text-right font-mono text-amber-600 dark:!text-amber-400">
                                        Rp {{ number_format($data['pengeluaran']['total_pengeluaran'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr class="font-bold border-t text-gray-900 dark:!text-white bg-slate-50 dark:!bg-zinc-700/50">
                                    <td class="py-2 px-1">SALDO KAS (UP/GU/TU)</td>
                                    <td class="text-right font-mono px-1 text-emerald-600 dark:!text-emerald-400">
                                        Rp {{ number_format($data['pengeluaran']['saldo_kas'], 2, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB 2: REKAPITULASI PAJAK & POTONGAN (DILENGKAPI STATISTIK & TABEL RINCIAN TRANSAKSI SPJ) --}}
        <div x-show="activeTab === 'pajak'" x-cloak wire:key="tab-panel-pajak" class="space-y-4">
            {{-- KARTU STATISTIK RINGKASAN PAJAK --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="laporan-card p-4">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:!text-zinc-400 block mb-1">Total PPN 11% Dipotong</span>
                    <div class="text-xl font-bold font-mono text-amber-600 dark:!text-amber-400">Rp {{ number_format($data['pajak']['ppn'], 2, ',', '.') }}</div>
                    <p class="text-[11px] text-gray-500 dark:!text-zinc-400 mt-1">PPN atas transaksi belanja barang di atas Rp 2 Juta.</p>
                </div>

                <div class="laporan-card p-4">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:!text-zinc-400 block mb-1">Total PPh 22 (1,5%) Dipotong</span>
                    <div class="text-xl font-bold font-mono text-blue-600 dark:!text-blue-400">Rp {{ number_format($data['pajak']['pph22'], 2, ',', '.') }}</div>
                    <p class="text-[11px] text-gray-500 dark:!text-zinc-400 mt-1">PPh Pasal 22 belanja barang rekanan penyedia.</p>
                </div>

                <div class="laporan-card p-4">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:!text-zinc-400 block mb-1">Total Seluruh Potongan Pajak</span>
                    <div class="text-xl font-bold font-mono text-emerald-600 dark:!text-emerald-400">Rp {{ number_format($data['pajak']['total_pajak'], 2, ',', '.') }}</div>
                    <p class="text-[11px] text-gray-500 dark:!text-zinc-400 mt-1">Akumulasi pemotongan pajak yang disetor ke Kas Negara.</p>
                </div>

                <div class="laporan-card p-4">
                    <span class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:!text-zinc-400 block mb-1">Total Nilai Bersih Dibayarkan</span>
                    <div class="text-xl font-bold font-mono text-purple-600 dark:!text-purple-400">Rp {{ number_format($data['pajak']['nilai_bersih'], 2, ',', '.') }}</div>
                    <p class="text-[11px] text-gray-500 dark:!text-zinc-400 mt-1">Netto pembayaran ke pihak rekanan penyedia / bendahara.</p>
                </div>
            </div>

            {{-- TABEL RINCIAN PEMOTONGAN PAJAK PER SPJ --}}
            <div class="laporan-card">
                <div class="laporan-card-header flex justify-between items-center">
                    <div>
                        <h3 class="laporan-card-title text-base">Daftar Rincian Pemotongan & Penyetoran Pajak SPJ</h3>
                        <p class="laporan-card-sub text-xs mt-0.5">
                            Data transaksi pencairan SPJ yang dikenakan potongan PPN dan PPh s/d bulan <strong>{{ $namaBulanAktif }} {{ $tahun }}</strong>
                        </p>
                    </div>
                </div>

                <div class="overflow-auto max-h-[550px]">
                    <table class="w-full text-left text-xs border-collapse laporan-table">
                        <thead>
                            <tr class="border-b border-gray-200 dark:!border-zinc-700 text-center uppercase tracking-wider text-[11px] font-bold bg-gray-50 dark:!bg-zinc-800">
                                <th class="p-2.5 w-12 border-r">No</th>
                                <th class="p-2.5 w-28 border-r">Tgl Cair</th>
                                <th class="p-2.5 w-48 border-r">Nomor SPJ / Bukti</th>
                                <th class="p-2.5 border-r text-left min-w-[200px]">Keperluan Belanja</th>
                                <th class="p-2.5 border-r text-left w-48">Penyedia / Toko</th>
                                <th class="p-2.5 w-32 border-r text-right">Nilai Bruto</th>
                                <th class="p-2.5 w-28 border-r text-right">PPN (11%)</th>
                                <th class="p-2.5 w-28 border-r text-right">PPh 22 (1,5%)</th>
                                <th class="p-2.5 w-32 border-r text-right">Total Pajak</th>
                                <th class="p-2.5 w-32 text-right">Nilai Bersih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:!divide-zinc-800 text-[11px]">
                            @forelse($data['pajak']['items'] ?? [] as $idx => $p)
                                <tr class="hover:bg-amber-50/50 dark:hover:!bg-zinc-800/60">
                                    <td class="p-2 text-center border-r text-gray-500">{{ $idx + 1 }}</td>
                                    <td class="p-2 text-center border-r font-mono whitespace-nowrap">{{ $p['tanggal_dicairkan'] }}</td>
                                    <td class="p-2 border-r font-mono font-semibold text-gray-900 dark:!text-white whitespace-nowrap">{{ $p['nomor_pengajuan'] }}</td>
                                    <td class="p-2 border-r text-gray-800 dark:!text-zinc-200">{{ $p['keperluan'] }}</td>
                                    <td class="p-2 border-r font-medium text-gray-700 dark:!text-zinc-300">{{ $p['nama_toko'] }}</td>
                                    <td class="p-2 text-right border-r font-mono whitespace-nowrap">Rp {{ number_format($p['total_belanja'], 2, ',', '.') }}</td>
                                    <td class="p-2 text-right border-r font-mono text-amber-600 dark:!text-amber-400 whitespace-nowrap">{{ $p['nilai_ppn'] > 0 ? 'Rp ' . number_format($p['nilai_ppn'], 2, ',', '.') : '-' }}</td>
                                    <td class="p-2 text-right border-r font-mono text-blue-600 dark:!text-blue-400 whitespace-nowrap">{{ $p['nilai_pph22'] > 0 ? 'Rp ' . number_format($p['nilai_pph22'], 2, ',', '.') : '-' }}</td>
                                    <td class="p-2 text-right border-r font-mono font-bold text-emerald-600 dark:!text-emerald-400 whitespace-nowrap">{{ $p['total_pajak'] > 0 ? 'Rp ' . number_format($p['total_pajak'], 2, ',', '.') : '-' }}</td>
                                    <td class="p-2 text-right font-mono font-bold text-gray-900 dark:!text-white whitespace-nowrap">Rp {{ number_format($p['nilai_bersih'], 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="p-8 text-center text-gray-500 dark:!text-zinc-400">
                                        Belum ada data pemotongan pajak pada transaksi SPJ yang dicairkan s/d periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="font-bold border-t-2 bg-slate-100 dark:!bg-zinc-900">
                                <td colspan="5" class="p-2.5 text-center border-r font-bold">TOTAL REKAPITULASI PAJAK</td>
                                <td class="p-2.5 text-right font-mono border-r whitespace-nowrap">
                                    Rp {{ number_format(collect($data['pajak']['items'] ?? [])->sum('total_belanja'), 2, ',', '.') }}
                                </td>
                                <td class="p-2.5 text-right font-mono text-amber-600 dark:!text-amber-400 border-r whitespace-nowrap">
                                    Rp {{ number_format($data['pajak']['ppn'], 2, ',', '.') }}
                                </td>
                                <td class="p-2.5 text-right font-mono text-blue-600 dark:!text-blue-400 border-r whitespace-nowrap">
                                    Rp {{ number_format($data['pajak']['pph22'], 2, ',', '.') }}
                                </td>
                                <td class="p-2.5 text-right font-mono text-emerald-600 dark:!text-emerald-400 border-r whitespace-nowrap font-bold">
                                    Rp {{ number_format($data['pajak']['total_pajak'], 2, ',', '.') }}
                                </td>
                                <td class="p-2.5 text-right font-mono font-bold text-gray-900 dark:!text-white whitespace-nowrap">
                                    Rp {{ number_format($data['pajak']['nilai_bersih'], 2, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
