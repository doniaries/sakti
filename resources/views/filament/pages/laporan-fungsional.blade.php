<x-filament-panels::page>
    {{-- BARIS FILTER TAHUN DAN BULAN --}}
    <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Tahun Anggaran</label>
                <select wire:model.live="tahun" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2 px-3 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    @for($y = (int) date('Y') - 1; $y <= (int) date('Y') + 2; $y++)
                        <option value="{{ $y }}">Tahun Anggaran {{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Periode s/d Bulan</label>
                <select wire:model.live="bulan" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2 px-3 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    @foreach($daftarBulan as $bKey => $bVal)
                        <option value="{{ $bKey }}">{{ $bVal }} (s/d Akhir Bulan)</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- TAB NAVIGASI --}}
    <div class="flex items-center gap-2 border-b border-gray-200 dark:border-white/10 pb-2">
        <button type="button" wire:click="setTab('rekening')"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors {{ $activeTab === 'rekening' ? 'bg-amber-500 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
            1. Realisasi Belanja per Kode Rekening
        </button>

        <button type="button" wire:click="setTab('pajak')"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors {{ $activeTab === 'pajak' ? 'bg-amber-500 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
            2. Rekapitulasi Pajak & Potongan
        </button>
    </div>

    {{-- TAB 1: REALISASI PER KODE REKENING --}}
    @if($activeTab === 'rekening')
    <div class="overflow-x-auto bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm">
        <div class="p-4 border-b border-gray-200 dark:border-white/10 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white text-base">Pertanggungjawaban Belanja per Kode Rekening (LPJ Fungsional)</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Periode: s/d <strong>{{ $namaBulanAktif }} {{ $tahun }}</strong> (Standar Permendagri 77/2020).</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300">
                100% Otomatis dari BKU & SPJ
            </span>
        </div>

        <table class="w-full text-left text-xs border-collapse">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 text-center">
                    <th rowspan="2" class="p-2 border-r border-gray-200 dark:border-gray-700 w-10">No</th>
                    <th rowspan="2" class="p-2 border-r border-gray-200 dark:border-gray-700 w-36">Kode Rekening</th>
                    <th rowspan="2" class="p-2 border-r border-gray-200 dark:border-gray-700 text-left min-w-[240px]">Uraian Rekening Belanja</th>
                    <th rowspan="2" class="p-2 border-r border-gray-200 dark:border-gray-700 w-32">Anggaran DPA (Rp)</th>
                    <th colspan="3" class="p-1 border-b border-gray-200 dark:border-gray-700">Realisasi SPJ Dicairkan</th>
                    <th rowspan="2" class="p-2 border-r border-gray-200 dark:border-gray-700 w-32">Sisa Anggaran (Rp)</th>
                    <th rowspan="2" class="p-2 w-20">Serapan (%)</th>
                </tr>
                <tr class="bg-gray-50 dark:bg-gray-800/80 text-gray-600 dark:text-gray-400 text-[11px] border-b border-gray-200 dark:border-gray-700 text-center">
                    <th class="p-1 border-r border-gray-200 dark:border-gray-700 w-28">s/d Bulan Lalu</th>
                    <th class="p-1 border-r border-gray-200 dark:border-gray-700 w-28">Bulan Ini</th>
                    <th class="p-1 border-r border-gray-200 dark:border-gray-700 w-28 font-semibold">Total s/d Bulan Ini</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse($data['rekening_rows'] as $idx => $r)
                    <tr class="hover:bg-amber-50/40 dark:hover:bg-amber-950/20">
                        <td class="p-2 text-center text-gray-500 border-r border-gray-200 dark:border-gray-800">{{ $idx + 1 }}</td>
                        <td class="p-2 border-r border-gray-200 dark:border-gray-800 font-mono text-amber-600 dark:text-amber-400 font-semibold text-center">
                            {{ $r['kode'] }}
                        </td>
                        <td class="p-2 border-r border-gray-200 dark:border-gray-800 text-gray-900 dark:text-white font-medium">
                            {{ $r['uraian'] }}
                        </td>
                        <td class="p-2 text-right border-r border-gray-200 dark:border-gray-800 font-mono font-semibold">
                            Rp {{ number_format($r['pagu'], 0, ',', '.') }}
                        </td>
                        <td class="p-2 text-right border-r border-gray-200 dark:border-gray-800 font-mono text-gray-500">
                            Rp {{ number_format($r['realisasi_lalu'], 0, ',', '.') }}
                        </td>
                        <td class="p-2 text-right border-r border-gray-200 dark:border-gray-800 font-mono text-blue-600 dark:text-blue-400">
                            Rp {{ number_format($r['realisasi_ini'], 0, ',', '.') }}
                        </td>
                        <td class="p-2 text-right border-r border-gray-200 dark:border-gray-800 font-mono font-bold text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format($r['total_sd_ini'], 0, ',', '.') }}
                        </td>
                        <td class="p-2 text-right border-r border-gray-200 dark:border-gray-800 font-mono text-gray-700 dark:text-gray-300">
                            Rp {{ number_format($r['sisa'], 0, ',', '.') }}
                        </td>
                        <td class="p-2 text-center font-bold text-gray-900 dark:text-white">
                            {{ number_format($r['persen'], 2, ',', '.') }}%
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-8 text-center text-gray-500">Belum ada realisasi anggaran belanja pada tahun {{ $tahun }}.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white font-bold border-t-2 border-gray-300 dark:border-gray-600">
                    <td colspan="3" class="p-3 text-center">TOTAL PENGELUARAN BELANJA</td>
                    <td class="p-3 text-right font-mono text-amber-600 dark:text-amber-400">Rp {{ number_format($data['summary']['total_pagu'], 0, ',', '.') }}</td>
                    <td class="p-3 text-right font-mono">Rp {{ number_format($data['summary']['total_lalu'], 0, ',', '.') }}</td>
                    <td class="p-3 text-right font-mono text-blue-600 dark:text-blue-400">Rp {{ number_format($data['summary']['total_ini'], 0, ',', '.') }}</td>
                    <td class="p-3 text-right font-mono text-emerald-600 dark:text-emerald-400">Rp {{ number_format($data['summary']['total_sd_ini'], 0, ',', '.') }}</td>
                    <td class="p-3 text-right font-mono">Rp {{ number_format($data['summary']['total_sisa'], 0, ',', '.') }}</td>
                    <td class="p-3 text-center">{{ number_format($data['summary']['persen'], 2, ',', '.') }}%</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- TAB 2: REKAPITULASI PAJAK --}}
    @if($activeTab === 'pajak')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block mb-1">Total PPN 11% Dipotong</span>
            <div class="text-2xl font-bold font-mono text-amber-600 dark:text-amber-400">Rp {{ number_format($data['pajak']['ppn'], 0, ',', '.') }}</div>
            <p class="text-xs text-gray-500 mt-2">Pajak Pertambahan Nilai belanja barang di atas Rp 2 Juta.</p>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block mb-1">Total PPh Pasal 22 (1,5%)</span>
            <div class="text-2xl font-bold font-mono text-blue-600 dark:text-blue-400">Rp {{ number_format($data['pajak']['pph22'], 0, ',', '.') }}</div>
            <p class="text-xs text-gray-500 mt-2">Pajak Penghasilan Pasal 22 atas belanja barang.</p>
        </div>

        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl p-5 shadow-sm">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 block mb-1">Total Seluruh Pajak</span>
            <div class="text-2xl font-bold font-mono text-emerald-600 dark:text-emerald-400">Rp {{ number_format($data['pajak']['total_pajak'], 0, ',', '.') }}</div>
            <p class="text-xs text-gray-500 mt-2">Akumulasi potongan pajak yang disetorkan ke Kas Negara.</p>
        </div>
    </div>
    @endif
</x-filament-panels::page>

