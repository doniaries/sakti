<x-filament-panels::page>
    {{-- BARIS FILTER TAHUN DAN BULAN --}}
    <div class="laporan-card p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:!text-zinc-200 mb-1.5">Tahun</label>
                <select wire:model.live="tahun"
                    class="w-full text-sm rounded-lg border border-gray-300 dark:!border-zinc-700 bg-white dark:!bg-zinc-800 text-gray-900 dark:!text-white py-2 px-3 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    @for($y = (int) date('Y') - 1; $y <= (int) date('Y') + 2; $y++)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:!text-zinc-200 mb-1.5">Bulan</label>
                <select wire:model.live="bulan"
                    class="w-full text-sm rounded-lg border border-gray-300 dark:!border-zinc-700 bg-white dark:!bg-zinc-800 text-gray-900 dark:!text-white py-2 px-3 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    @foreach($daftarBulan as $bKey => $bVal)
                        <option value="{{ $bKey }}">{{ $bVal }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- TAB NAVIGASI (ANTI WARNA HILANG DI DARK/LIGHT MODE) --}}
    <div class="flex items-center gap-2 border-b border-gray-200 dark:!border-zinc-800 pb-2">
        <button type="button" wire:click="setTab('rekening')"
            class="laporan-tab-btn {{ $activeTab === 'rekening' ? 'active' : '' }}">
            1. Realisasi Belanja per Kode Rekening
        </button>

        <button type="button" wire:click="setTab('pajak')"
            class="laporan-tab-btn {{ $activeTab === 'pajak' ? 'active' : '' }}">
            2. Rekapitulasi Pajak & Potongan
        </button>
    </div>

    {{-- TAB 1: REALISASI PER KODE REKENING --}}
    @if($activeTab === 'rekening')
    <div class="laporan-card">
        {{-- CARD HEADER DENGAN KONTRAS TINGGI DI DARK & LIGHT MODE --}}
        <div class="laporan-card-header flex flex-col md:flex-row md:justify-between md:items-center gap-2">
            <div>
                <h3 class="laporan-card-title text-base">Pertanggungjawaban Belanja per Kode Rekening (LPJ Fungsional)</h3>
                <p class="laporan-card-sub text-xs mt-0.5">
                    Periode: s/d <strong>{{ $namaBulanAktif }} {{ $tahun }}</strong> (Standar Permendagri 77/2020)
                </p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:!bg-emerald-950/80 text-emerald-800 dark:!text-emerald-300 border border-emerald-300 dark:!border-emerald-700/60 w-fit">
                ● 100% Otomatis dari BKU & SPJ
            </span>
        </div>

        {{-- TABEL DENGAN STICKY HEADER & STICKY COLUMNS --}}
        <div class="overflow-auto max-h-[720px] relative">
            <table class="w-full text-left text-xs border-collapse laporan-table">
                <thead>
                    <tr class="border-b border-gray-200 dark:!border-zinc-700 text-center uppercase tracking-wider text-[11px]">
                        {{-- Sticky Col 1: No --}}
                        <th rowspan="2" class="p-2.5 sticky-col-1 border-r border-b">No</th>
                        {{-- Sticky Col 2: Kode Rekening --}}
                        <th rowspan="2" class="p-2.5 w-36 sticky-col-2 border-r border-b">Kode Rekening</th>
                        {{-- Normal Cols --}}
                        <th rowspan="2" class="p-2.5 border-r border-b text-left min-w-[240px]">Uraian Rekening Belanja</th>
                        <th rowspan="2" class="p-2.5 w-32 border-r border-b">Anggaran DPA (Rp)</th>
                        <th colspan="3" class="p-1.5 border-b">Realisasi SPJ Dicairkan</th>
                        <th rowspan="2" class="p-2.5 w-32 border-r border-b">Sisa Anggaran (Rp)</th>
                        <th rowspan="2" class="p-2.5 w-20 border-b">Serapan (%)</th>
                    </tr>
                    <tr class="text-[10px] border-b border-gray-300 dark:!border-zinc-700 text-center">
                        <th class="p-1.5 border-r border-b w-28">s/d Bulan Lalu</th>
                        <th class="p-1.5 border-r border-b w-28">Bulan Ini</th>
                        <th class="p-1.5 border-r border-b w-28 font-semibold">Total s/d Bulan Ini</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:!divide-zinc-800">
                    @forelse($data['rekening_rows'] as $idx => $r)
                        <tr>
                            {{-- Sticky Col 1: No --}}
                            <td class="p-2.5 text-center sticky-col-1 border-r text-gray-500 dark:!text-zinc-400">{{ $idx + 1 }}</td>
                            {{-- Sticky Col 2: Kode Rekening --}}
                            <td class="p-2.5 sticky-col-2 border-r font-mono text-amber-600 dark:!text-amber-400 font-bold text-center">
                                {{ $r['kode'] }}
                            </td>
                            {{-- Normal Cols --}}
                            <td class="p-2.5 border-r text-gray-900 dark:!text-white font-medium">
                                {{ $r['uraian'] }}
                            </td>
                            <td class="p-2.5 text-right border-r font-mono font-semibold text-gray-900 dark:!text-zinc-100">
                                Rp {{ number_format($r['pagu'], 0, ',', '.') }}
                            </td>
                            <td class="p-2.5 text-right border-r font-mono text-gray-500 dark:!text-zinc-400">
                                Rp {{ number_format($r['realisasi_lalu'], 0, ',', '.') }}
                            </td>
                            <td class="p-2.5 text-right border-r font-mono text-blue-600 dark:!text-blue-400">
                                Rp {{ number_format($r['realisasi_ini'], 0, ',', '.') }}
                            </td>
                            <td class="p-2.5 text-right border-r font-mono font-bold text-emerald-600 dark:!text-emerald-400">
                                Rp {{ number_format($r['total_sd_ini'], 0, ',', '.') }}
                            </td>
                            <td class="p-2.5 text-right border-r font-mono text-gray-800 dark:!text-zinc-200">
                                Rp {{ number_format($r['sisa'], 0, ',', '.') }}
                            </td>
                            <td class="p-2.5 text-center font-bold text-gray-900 dark:!text-white">
                                {{ number_format($r['persen'], 2, ',', '.') }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-gray-500 dark:!text-zinc-400">Belum ada realisasi anggaran belanja pada tahun {{ $tahun }}.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="font-bold border-t-2 sticky bottom-0 z-20 bg-slate-100 dark:!bg-zinc-900 shadow-lg">
                        {{-- Sticky Footer Col 1 & 2 --}}
                        <td colspan="2" class="p-3 text-center sticky-col-1 sticky-col-2 border-r bg-slate-100 dark:!bg-zinc-900">
                            TOTAL
                        </td>
                        <td class="p-3 text-left border-r uppercase bg-slate-100 dark:!bg-zinc-900">
                            Pengeluaran Belanja Daerah
                        </td>
                        <td class="p-3 text-right font-mono text-amber-600 dark:!text-amber-400 border-r bg-slate-100 dark:!bg-zinc-900">
                            Rp {{ number_format($data['summary']['total_pagu'], 0, ',', '.') }}
                        </td>
                        <td class="p-3 text-right font-mono text-gray-700 dark:!text-zinc-300 border-r bg-slate-100 dark:!bg-zinc-900">
                            Rp {{ number_format($data['summary']['total_lalu'], 0, ',', '.') }}
                        </td>
                        <td class="p-3 text-right font-mono text-blue-600 dark:!text-blue-400 border-r bg-slate-100 dark:!bg-zinc-900">
                            Rp {{ number_format($data['summary']['total_ini'], 0, ',', '.') }}
                        </td>
                        <td class="p-3 text-right font-mono text-emerald-600 dark:!text-emerald-400 border-r bg-slate-100 dark:!bg-zinc-900">
                            Rp {{ number_format($data['summary']['total_sd_ini'], 0, ',', '.') }}
                        </td>
                        <td class="p-3 text-right font-mono text-gray-800 dark:!text-zinc-200 border-r bg-slate-100 dark:!bg-zinc-900">
                            Rp {{ number_format($data['summary']['total_sisa'], 0, ',', '.') }}
                        </td>
                        <td class="p-3 text-center text-gray-900 dark:!text-white bg-slate-100 dark:!bg-zinc-900">
                            {{ number_format($data['summary']['persen'], 2, ',', '.') }}%
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    {{-- TAB 2: REKAPITULASI PAJAK --}}
    @if($activeTab === 'pajak')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="laporan-card p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:!text-zinc-400 block mb-1">Total PPN 11% Dipotong</span>
            <div class="text-2xl font-bold font-mono text-amber-600 dark:!text-amber-400">Rp {{ number_format($data['pajak']['ppn'], 0, ',', '.') }}</div>
            <p class="text-xs text-gray-500 dark:!text-zinc-400 mt-2">Pajak Pertambahan Nilai belanja barang di atas Rp 2 Juta.</p>
        </div>

        <div class="laporan-card p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:!text-zinc-400 block mb-1">Total PPh Pasal 22 (1,5%)</span>
            <div class="text-2xl font-bold font-mono text-blue-600 dark:!text-blue-400">Rp {{ number_format($data['pajak']['pph22'], 0, ',', '.') }}</div>
            <p class="text-xs text-gray-500 dark:!text-zinc-400 mt-2">Pajak Penghasilan Pasal 22 atas belanja barang.</p>
        </div>

        <div class="laporan-card p-5">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-600 dark:!text-zinc-400 block mb-1">Total Seluruh Pajak</span>
            <div class="text-2xl font-bold font-mono text-emerald-600 dark:!text-emerald-400">Rp {{ number_format($data['pajak']['total_pajak'], 0, ',', '.') }}</div>
            <p class="text-xs text-gray-500 dark:!text-zinc-400 mt-2">Akumulasi potongan pajak yang disetorkan ke Kas Negara.</p>
        </div>
    </div>
    @endif
</x-filament-panels::page>
