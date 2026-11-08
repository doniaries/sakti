<x-filament-panels::page>
    {{-- BARIS FILTER TAHUN, BULAN, BIDANG --}}
    <div class="p-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Tahun Anggaran</label>
                <select wire:model.live="tahun" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2 px-3 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    @for($y = (int) date('Y') - 1; $y <= (int) date('Y') + 2; $y++)
                        <option value="{{ $y }}">Tahun Anggaran {{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Kondisi s/d Bulan</label>
                <select wire:model.live="bulan" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2 px-3 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    @foreach($daftarBulan as $bKey => $bVal)
                        <option value="{{ $bKey }}">{{ $bVal }} (s/d Akhir Bulan)</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Filter Bidang Dinas</label>
                <select wire:model.live="bidangId" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white py-2 px-3 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <option value="">Semua Bidang (Rekap Dinas)</option>
                    @foreach($daftarBidang as $b)
                        <option value="{{ $b->id }}">{{ $b->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- TAB NAVIGASI LEMBAR LAPORAN --}}
    <div class="flex items-center gap-2 border-b border-gray-200 dark:border-white/10 pb-2">
        <button type="button" wire:click="setTab('matriks')"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors {{ $activeTab === 'matriks' ? 'bg-amber-500 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
            1. Matriks LRFK Kegiatan
        </button>

        <button type="button" wire:click="setTab('lampiran1')"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors {{ $activeTab === 'lampiran1' ? 'bg-amber-500 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
            2. Lampiran 1 (Jenis Belanja)
        </button>

        <button type="button" wire:click="setTab('kemajuan')"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors {{ $activeTab === 'kemajuan' ? 'bg-amber-500 text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
            3. Kemajuan Fisik & Kontrak
        </button>
    </div>

    {{-- TAB 1: MATRIKS UTAMA LRFK KEGIATAN --}}
    @if($activeTab === 'matriks')
    <div class="overflow-x-auto bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm">
        <div class="p-4 border-b border-gray-200 dark:border-white/10 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white text-base">Laporan Realisasi Fisik dan Keuangan (LRFK)</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400">Periode Kondisi: s/d <strong>{{ $namaBulanAktif }} {{ $tahun }}</strong> (Target ROPK Kumulatif: <strong>{{ number_format($matriks['summary']['target_fisik'], 2, ',', '.') }}%</strong>)</p>
            </div>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300">
                100% Otomatis dari SPJ & RKA
            </span>
        </div>

        <table class="w-full text-left text-xs border-collapse">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 text-center">
                    <th rowspan="3" class="p-2 border-r border-gray-200 dark:border-gray-700 w-10">No</th>
                    <th rowspan="3" class="p-2 border-r border-gray-200 dark:border-gray-700 text-left min-w-[280px]">Kegiatan DPA</th>
                    <th rowspan="3" class="p-2 border-r border-gray-200 dark:border-gray-700 w-32">Pagu (Rp)</th>
                    <th colspan="8" class="p-2 border-b border-gray-200 dark:border-gray-700">Realisasi s/d Bulan Ini</th>
                    <th rowspan="3" class="p-2 w-28">Keterangan</th>
                </tr>
                <tr class="bg-gray-50 dark:bg-gray-800/80 text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 text-center">
                    <th colspan="3" class="p-1 border-r border-gray-200 dark:border-gray-700">Fisik (%)</th>
                    <th colspan="4" class="p-1 border-r border-gray-200 dark:border-gray-700">Keuangan (Rp)</th>
                    <th rowspan="2" class="p-1 border-r border-gray-200 dark:border-gray-700 w-24">Serapan Kas (%)<br><span class="text-[10px] font-normal">(Realisasi/Target)</span></th>
                </tr>
                <tr class="bg-gray-50 dark:bg-gray-800/80 text-gray-600 dark:text-gray-400 text-[11px] border-b border-gray-200 dark:border-gray-700 text-center">
                    <th class="p-1 border-r border-gray-200 dark:border-gray-700 w-16">Target</th>
                    <th class="p-1 border-r border-gray-200 dark:border-gray-700 w-16 font-semibold">Realisasi</th>
                    <th class="p-1 border-r border-gray-200 dark:border-gray-700 w-16 font-semibold">Capaian %</th>
                    <th class="p-1 border-r border-gray-200 dark:border-gray-700 w-28">Target Rp</th>
                    <th class="p-1 border-r border-gray-200 dark:border-gray-700 w-14">Target %</th>
                    <th class="p-1 border-r border-gray-200 dark:border-gray-700 w-28 font-semibold">Realisasi Rp</th>
                    <th class="p-1 border-r border-gray-200 dark:border-gray-700 w-14 font-semibold">Real %</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse($matriks['rows'] as $idx => $r)
                    <tr class="hover:bg-amber-50/40 dark:hover:bg-amber-950/20 transition-colors">
                        <td class="p-2 text-center text-gray-500 border-r border-gray-200 dark:border-gray-800">{{ $idx + 1 }}</td>
                        <td class="p-2 border-r border-gray-200 dark:border-gray-800">
                            <span class="font-mono text-[10px] text-amber-600 dark:text-amber-400 font-semibold block">{{ $r['kode'] }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $r['nama'] }}</span>
                        </td>
                        <td class="p-2 text-right font-semibold text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-800">
                            Rp {{ number_format($r['pagu'], 0, ',', '.') }}
                        </td>
                        <td class="p-2 text-center border-r border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400">
                            {{ number_format($r['target_fisik'], 1, ',', '.') }}%
                        </td>
                        <td class="p-2 text-center font-bold text-blue-600 dark:text-blue-400 border-r border-gray-200 dark:border-gray-800">
                            {{ number_format($r['realisasi_fisik'], 2, ',', '.') }}%
                        </td>
                        <td class="p-2 text-center font-bold text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-800">
                            {{ number_format($r['capaian_fisik'], 1, ',', '.') }}%
                        </td>
                        <td class="p-2 text-right border-r border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400">
                            Rp {{ number_format($r['target_keu_rp'], 0, ',', '.') }}
                        </td>
                        <td class="p-2 text-center border-r border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400">
                            {{ number_format($r['target_keu_persen'], 1, ',', '.') }}%
                        </td>
                        <td class="p-2 text-right font-bold text-emerald-600 dark:text-emerald-400 border-r border-gray-200 dark:border-gray-800">
                            Rp {{ number_format($r['realisasi_keu_rp'], 0, ',', '.') }}
                        </td>
                        <td class="p-2 text-center font-bold text-emerald-600 dark:text-emerald-400 border-r border-gray-200 dark:border-gray-800">
                            {{ number_format($r['realisasi_keu_persen'], 2, ',', '.') }}%
                        </td>
                        <td class="p-2 text-center font-bold text-gray-900 dark:text-white border-r border-gray-200 dark:border-gray-800">
                            {{ number_format($r['serapan_anggaran'], 2, ',', '.') }}%
                        </td>
                        <td class="p-2 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium {{ $r['realisasi_keu_rp'] > 0 ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">
                                {{ $r['keterangan'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="p-8 text-center text-gray-500">Belum ada kegiatan pada tahun anggaran {{ $tahun }}.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white font-bold border-t-2 border-gray-300 dark:border-gray-600">
                    <td colspan="2" class="p-3 text-center border-r border-gray-200 dark:border-gray-700">TOTAL SELURUH KEGIATAN</td>
                    <td class="p-3 text-right border-r border-gray-200 dark:border-gray-700 text-amber-600 dark:text-amber-400 font-mono">
                        Rp {{ number_format($matriks['summary']['total_pagu'], 0, ',', '.') }}
                    </td>
                    <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">
                        {{ number_format($matriks['summary']['target_fisik'], 1, ',', '.') }}%
                    </td>
                    <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700 text-blue-600 dark:text-blue-400">
                        {{ number_format($matriks['summary']['realisasi_fisik'], 2, ',', '.') }}%
                    </td>
                    <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">
                        {{ number_format($matriks['summary']['capaian_fisik'], 1, ',', '.') }}%
                    </td>
                    <td class="p-3 text-right border-r border-gray-200 dark:border-gray-700 font-mono">
                        Rp {{ number_format($matriks['summary']['target_keu_rp'], 0, ',', '.') }}
                    </td>
                    <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">
                        {{ number_format($matriks['summary']['target_keu_persen'], 1, ',', '.') }}%
                    </td>
                    <td class="p-3 text-right border-r border-gray-200 dark:border-gray-700 text-emerald-600 dark:text-emerald-400 font-mono">
                        Rp {{ number_format($matriks['summary']['realisasi_keu_rp'], 0, ',', '.') }}
                    </td>
                    <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700 text-emerald-600 dark:text-emerald-400">
                        {{ number_format($matriks['summary']['realisasi_keu_persen'], 2, ',', '.') }}%
                    </td>
                    <td class="p-3 text-center border-r border-gray-200 dark:border-gray-700">
                        {{ number_format($matriks['summary']['serapan_anggaran'], 2, ',', '.') }}%
                    </td>
                    <td class="p-3 text-center">-</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- TAB 2: LAMPIRAN 1 (JENIS BELANJA) --}}
    @if($activeTab === 'lampiran1')
    <div class="overflow-x-auto bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm">
        <div class="p-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-gray-800/50">
            <h3 class="font-bold text-gray-900 dark:text-white text-base">Lampiran 1: Laporan Realisasi Belanja per Jenis Belanja</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Mengelompokkan akun belanja operasi, modal, tidak terduga, dan transfer sesuai format resmi.</p>
        </div>

        <table class="w-full text-left text-xs border-collapse">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                    <th class="p-3 text-center w-14">No</th>
                    <th class="p-3">Uraian Kelompok Belanja</th>
                    <th class="p-3 text-right w-44">Anggaran DPA (Rp)</th>
                    <th class="p-3 text-right w-44">Realisasi s/d Bulan Ini (Rp)</th>
                    <th class="p-3 text-center w-28">Keterangan (%)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @foreach($lampiran1['kelompok'] as $kel)
                    <tr class="bg-gray-50 dark:bg-gray-800/60 font-bold text-gray-900 dark:text-white">
                        <td class="p-2.5 text-center">{{ $kel['nomor'] }}</td>
                        <td class="p-2.5">{{ $kel['uraian'] }}</td>
                        <td class="p-2.5 text-right font-mono">Rp {{ number_format($kel['anggaran'], 0, ',', '.') }}</td>
                        <td class="p-2.5 text-right font-mono text-emerald-600 dark:text-emerald-400">Rp {{ number_format($kel['realisasi'], 0, ',', '.') }}</td>
                        <td class="p-2.5 text-center">{{ number_format($kel['persen'], 2, ',', '.') }}%</td>
                    </tr>
                    @foreach($kel['sub_items'] as $sub)
                        <tr class="hover:bg-amber-50/40 dark:hover:bg-amber-950/20 text-gray-700 dark:text-gray-300">
                            <td class="p-2 text-center text-gray-400">-</td>
                            <td class="p-2 pl-8 font-normal">{{ $sub['nama'] }}</td>
                            <td class="p-2 text-right font-mono">Rp {{ number_format($sub['anggaran'], 0, ',', '.') }}</td>
                            <td class="p-2 text-right font-mono">Rp {{ number_format($sub['realisasi'], 0, ',', '.') }}</td>
                            <td class="p-2 text-center">{{ number_format($sub['persen'], 2, ',', '.') }}%</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white font-bold border-t-2 border-gray-300 dark:border-gray-600">
                    <td colspan="2" class="p-3 text-center">TOTAL BELANJA DAERAH</td>
                    <td class="p-3 text-right text-amber-600 dark:text-amber-400 font-mono">Rp {{ number_format($lampiran1['total_anggaran'], 0, ',', '.') }}</td>
                    <td class="p-3 text-right text-emerald-600 dark:text-emerald-400 font-mono">Rp {{ number_format($lampiran1['total_realisasi'], 0, ',', '.') }}</td>
                    <td class="p-3 text-center">{{ number_format($lampiran1['persen'], 2, ',', '.') }}%</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- TAB 3: KEMAJUAN FISIK & KONTRAK --}}
    @if($activeTab === 'kemajuan')
    <div class="overflow-x-auto bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-xl shadow-sm">
        <div class="p-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-gray-800/50">
            <h3 class="font-bold text-gray-900 dark:text-white text-base">Laporan Kemajuan Pekerjaan (Kontraktual / Penyedia)</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Daftar transaksi belanja barang/jasa dan belanja modal pihak ketiga beserta pemantauan bobot fisik.</p>
        </div>

        <table class="w-full text-left text-xs border-collapse">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700 text-center">
                    <th rowspan="2" class="p-2 border-r border-gray-200 dark:border-gray-700 w-10">No</th>
                    <th rowspan="2" class="p-2 border-r border-gray-200 dark:border-gray-700 text-left min-w-[200px]">Pekerjaan / Belanja</th>
                    <th rowspan="2" class="p-2 border-r border-gray-200 dark:border-gray-700 w-28">Pagu (Rp)</th>
                    <th rowspan="2" class="p-2 border-r border-gray-200 dark:border-gray-700 w-28">Nilai SPK (Rp)</th>
                    <th rowspan="2" class="p-2 border-r border-gray-200 dark:border-gray-700 w-24">Sisa Dana (Rp)</th>
                    <th rowspan="2" class="p-2 border-r border-gray-200 dark:border-gray-700 w-32">Penyedia / Toko</th>
                    <th colspan="3" class="p-1 border-b border-gray-200 dark:border-gray-700">Fisik (%)</th>
                    <th colspan="2" class="p-1 border-b border-gray-200 dark:border-gray-700">Keuangan</th>
                    <th rowspan="2" class="p-2 w-24">Status SPJ</th>
                </tr>
                <tr class="bg-gray-50 dark:bg-gray-800/80 text-gray-600 dark:text-gray-400 text-[11px] border-b border-gray-200 dark:border-gray-700 text-center">
                    <th class="p-1 border-r border-gray-200 dark:border-gray-700 w-14">Rencana</th>
                    <th class="p-1 border-r border-gray-200 dark:border-gray-700 w-14 font-semibold">Realisasi</th>
                    <th class="p-1 border-r border-gray-200 dark:border-gray-700 w-14 font-semibold">Deviasi</th>
                    <th class="p-1 border-r border-gray-200 dark:border-gray-700 w-28">Realisasi Rp</th>
                    <th class="p-1 border-r border-gray-200 dark:border-gray-700 w-14">%</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse($kemajuan['rows'] as $k)
                    <tr class="hover:bg-amber-50/40 dark:hover:bg-amber-950/20">
                        <td class="p-2 text-center text-gray-500 border-r border-gray-200 dark:border-gray-800">{{ $k['no'] }}</td>
                        <td class="p-2 border-r border-gray-200 dark:border-gray-800">
                            <span class="font-semibold text-gray-900 dark:text-white block">{{ $k['nama_pekerjaan'] }}</span>
                            <span class="text-[10px] text-gray-500">{{ $k['kegiatan'] }}</span>
                        </td>
                        <td class="p-2 text-right border-r border-gray-200 dark:border-gray-800 font-mono">Rp {{ number_format($k['pagu'], 0, ',', '.') }}</td>
                        <td class="p-2 text-right border-r border-gray-200 dark:border-gray-800 font-bold font-mono">Rp {{ number_format($k['nilai_kontrak'], 0, ',', '.') }}</td>
                        <td class="p-2 text-right border-r border-gray-200 dark:border-gray-800 font-mono text-gray-600 dark:text-gray-400">Rp {{ number_format($k['sisa_dana'], 0, ',', '.') }}</td>
                        <td class="p-2 border-r border-gray-200 dark:border-gray-800 font-medium">{{ $k['penyedia'] ?: '-' }}</td>
                        <td class="p-2 text-center border-r border-gray-200 dark:border-gray-800">{{ number_format($k['bobot_rencana'], 1, ',', '.') }}%</td>
                        <td class="p-2 text-center font-bold text-blue-600 dark:text-blue-400 border-r border-gray-200 dark:border-gray-800">{{ number_format($k['bobot_realisasi'], 1, ',', '.') }}%</td>
                        <td class="p-2 text-center font-bold {{ $k['deviasi'] < 0 ? 'text-red-500' : 'text-emerald-500' }} border-r border-gray-200 dark:border-gray-800">{{ number_format($k['deviasi'], 1, ',', '.') }}%</td>
                        <td class="p-2 text-right font-bold text-emerald-600 dark:text-emerald-400 border-r border-gray-200 dark:border-gray-800 font-mono">Rp {{ number_format($k['realisasi_keu_rp'], 0, ',', '.') }}</td>
                        <td class="p-2 text-center font-bold border-r border-gray-200 dark:border-gray-800">{{ number_format($k['realisasi_keu_persen'], 1, ',', '.') }}%</td>
                        <td class="p-2 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                {{ $k['status'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="p-8 text-center text-gray-500">Belum ada data paket pekerjaan kontraktual pada tahun {{ $tahun }}.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white font-bold border-t-2 border-gray-300 dark:border-gray-600">
                    <td colspan="2" class="p-3 text-center">JUMLAH KONTRAKTUAL</td>
                    <td class="p-3 text-right font-mono">Rp {{ number_format($kemajuan['summary']['total_pagu'], 0, ',', '.') }}</td>
                    <td class="p-3 text-right font-mono text-amber-600 dark:text-amber-400">Rp {{ number_format($kemajuan['summary']['total_kontrak'], 0, ',', '.') }}</td>
                    <td class="p-3 text-right font-mono">Rp {{ number_format($kemajuan['summary']['total_sisa'], 0, ',', '.') }}</td>
                    <td colspan="4" class="p-3 text-center">-</td>
                    <td class="p-3 text-right font-mono text-emerald-600 dark:text-emerald-400">Rp {{ number_format($kemajuan['summary']['total_realisasi_keu'], 0, ',', '.') }}</td>
                    <td colspan="2" class="p-3 text-center">-</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
</x-filament-panels::page>

