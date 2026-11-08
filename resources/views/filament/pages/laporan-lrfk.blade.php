<x-filament-panels::page>
    {{-- BARIS FILTER TAHUN & KONDISI S/D BULAN (REKAPAN 1 DINAS PENUH) --}}
    <div class="laporan-card p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:!text-zinc-200 mb-1.5">Tahun
                    Anggaran</label>
                <select wire:model.live="tahun"
                    class="w-full text-sm rounded-lg border border-gray-300 dark:!border-zinc-700 bg-white dark:!bg-zinc-800 text-gray-900 dark:!text-white py-2 px-3 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    @for ($y = (int) date('Y') - 1; $y <= (int) date('Y') + 2; $y++)
                        <option value="{{ $y }}">Tahun Anggaran {{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:!text-zinc-200 mb-1.5">Kondisi s/d
                    Bulan (Rekapitulasi 1 Dinas Penuh)</label>
                <select wire:model.live="bulan"
                    class="w-full text-sm rounded-lg border border-gray-300 dark:!border-zinc-700 bg-white dark:!bg-zinc-800 text-gray-900 dark:!text-white py-2 px-3 shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    @foreach ($daftarBulan as $bKey => $bVal)
                        <option value="{{ $bKey }}">{{ $bVal }} (s/d Akhir Bulan)</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- TAB NAVIGASI LEMBAR LAPORAN (SESUAI PEDOMAN RESMI PAGE 1, 2, 3) --}}
    <div class="flex items-center gap-2 border-b border-gray-200 dark:!border-zinc-800 pb-2">
        <button type="button" wire:click="setTab('lampiran1')"
            class="laporan-tab-btn {{ $activeTab === 'lampiran1' ? 'active' : '' }}">
            Laporan Realisasi Belanja
        </button>

        <button type="button" wire:click="setTab('kemajuan')"
            class="laporan-tab-btn {{ $activeTab === 'kemajuan' ? 'active' : '' }}">
            Laporan Kemajuan Pekerjaan
        </button>

        <button type="button" wire:click="setTab('matriks')"
            class="laporan-tab-btn {{ $activeTab === 'matriks' ? 'active' : '' }}">
            Laporan Realisasi Fisik dan Keuangan (LRFK)
        </button>
    </div>

    {{-- ======================================================== --}}
    {{-- TAB 1: LAPORAN REALISASI BELANJA (SESUAI PAGE 1.PDF)     --}}
    {{-- ======================================================== --}}
    @if ($activeTab === 'lampiran1')
        <div class="laporan-card">
            <div class="laporan-card-header flex flex-col md:flex-row md:justify-between md:items-center gap-2">
                <div>
                    <h3 class="laporan-card-title text-base">LAPORAN REALISASI BELANJA TAHUN ANGGARAN
                        {{ $tahun }}</h3>
                    <p class="laporan-card-sub text-xs mt-0.5">
                        PD : DINAS PARIWISATA PEMUDA DAN OLAHRAGA &bull; Kondisi s/d Bulan :
                        <strong>{{ $namaBulanAktif }} {{ $tahun }}</strong>
                    </p>
                </div>
                {{-- <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:!bg-emerald-950/80 text-emerald-800 dark:!text-emerald-300 border border-emerald-300 dark:!border-emerald-700/60 w-fit">
                    ● Halaman 1 Resmi
                </span> --}}
            </div>

            <div class="overflow-auto max-h-[720px] relative">
                <table class="w-full text-left text-xs border-collapse laporan-table">
                    <thead class="sticky top-0 z-30">
                        <tr
                            class="border-b border-gray-300 dark:!border-zinc-700 uppercase tracking-wider text-[11px] font-bold text-center">
                            <th class="p-3 text-center sticky-col-1 border-r border-b w-14">NO.</th>
                            <th class="p-3 sticky-col-2 border-r border-b text-left">URAIAN BELANJA</th>
                            <th class="p-3 text-right w-48 border-r border-b">ANGGARAN (Rp.)</th>
                            <th class="p-3 text-right w-48 border-r border-b">REALISASI (Rp.)<br><span
                                    class="text-[10px] font-normal">S/d Bulan Ini</span></th>
                            <th class="p-3 text-center w-28 border-b">KET.</th>
                        </tr>
                        <tr
                            class="border-b border-gray-300 dark:!border-zinc-700 text-center text-[10px] font-semibold bg-gray-100 dark:!bg-zinc-800">
                            <th class="p-1 sticky-col-1 border-r border-b">1</th>
                            <th class="p-1 sticky-col-2 border-r border-b">2</th>
                            <th class="p-1 border-r border-b">3</th>
                            <th class="p-1 border-r border-b">4</th>
                            <th class="p-1 border-b">5</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:!divide-zinc-800">
                        @foreach ($lampiran1['kelompok'] as $kel)
                            <tr class="font-bold text-gray-900 dark:!text-white bg-gray-50 dark:!bg-zinc-800/60">
                                <td class="p-2.5 text-center sticky-col-1 border-r">{{ $kel['nomor'] }}</td>
                                <td class="p-2.5 sticky-col-2 border-r uppercase">{{ $kel['uraian'] }}</td>
                                <td class="p-2.5 text-right font-mono border-r">Rp
                                    {{ number_format($kel['anggaran'], 2, ',', '.') }}</td>
                                <td class="p-2.5 text-right font-mono text-emerald-600 dark:!text-emerald-400 border-r">
                                    Rp {{ number_format($kel['realisasi'], 2, ',', '.') }}</td>
                                <td class="p-2.5 text-center font-bold">
                                    {{ number_format($kel['persen'], 2, ',', '.') }}%</td>
                            </tr>
                            @foreach ($kel['sub_items'] as $sub)
                                <tr class="text-gray-700 dark:!text-zinc-300">
                                    <td class="p-2 text-center sticky-col-1 border-r text-gray-400 dark:!text-zinc-500">
                                        -</td>
                                    <td
                                        class="p-2 pl-8 font-normal sticky-col-2 border-r text-gray-900 dark:!text-zinc-200">
                                        - {{ $sub['nama'] }}</td>
                                    <td class="p-2 text-right font-mono border-r">
                                        {{ $sub['anggaran'] > 0 ? 'Rp ' . number_format($sub['anggaran'], 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="p-2 text-right font-mono border-r">
                                        {{ $sub['realisasi'] > 0 ? 'Rp ' . number_format($sub['realisasi'], 2, ',', '.') : '-' }}
                                    </td>
                                    <td class="p-2 text-center">
                                        {{ $sub['anggaran'] > 0 ? number_format($sub['persen'], 2, ',', '.') . '%' : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="font-bold border-t-2 sticky bottom-0 z-20">
                            <td colspan="2" class="p-3 text-center sticky-col-1 sticky-col-2 border-r">TOTAL BELANJA
                            </td>
                            <td class="p-3 text-right text-amber-600 dark:!text-amber-400 font-mono border-r">Rp
                                {{ number_format($lampiran1['total_anggaran'], 2, ',', '.') }}</td>
                            <td class="p-3 text-right text-emerald-600 dark:!text-emerald-400 font-mono border-r">Rp
                                {{ number_format($lampiran1['total_realisasi'], 2, ',', '.') }}</td>
                            <td class="p-3 text-center">{{ number_format($lampiran1['persen'], 2, ',', '.') }}%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- TAB 2: LAPORAN KEMAJUAN PEKERJAAN (SESUAI PAGE 2.PDF)    --}}
    {{-- ======================================================== --}}
    @if ($activeTab === 'kemajuan')
        <div class="laporan-card">
            <div class="laporan-card-header flex flex-col md:flex-row md:justify-between md:items-center gap-2">
                <div>
                    <h3 class="laporan-card-title text-base">LAPORAN KEMAJUAN PEKERJAAN TA {{ $tahun }}</h3>
                    <p class="laporan-card-sub text-xs mt-0.5">
                        OPD: DINAS PARIWISATA PEMUDA DAN OLAHRAGA &bull; KONDISI s.d {{ $namaBulanAktif }}
                        {{ $tahun }}
                    </p>
                </div>
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 dark:!bg-blue-950/80 text-blue-800 dark:!text-blue-300 border border-blue-300 dark:!border-blue-700/60 w-fit">
                    ● Halaman 2 Resmi
                </span>
            </div>

            <div class="overflow-auto max-h-[720px] relative">
                <table class="w-full text-left text-xs border-collapse laporan-table">
                    <thead class="sticky top-0 z-30">
                        <tr
                            class="border-b border-gray-300 dark:!border-zinc-700 text-center uppercase tracking-wider text-[11px] font-bold">
                            <th rowspan="3" class="p-2 sticky-col-1 border-r border-b align-middle w-12">NO</th>
                            <th rowspan="3"
                                class="p-2 min-w-[220px] sticky-col-2 border-r border-b text-left align-middle">
                                KEGIATAN/ PEKERJAAN</th>
                            <th rowspan="3" class="p-2 border-r border-b w-32 align-middle">PAGU (Rp.)</th>
                            <th rowspan="3" class="p-2 border-r border-b w-32 align-middle">NILAI KONTRAK/<br>BUKTI
                                PERJANJIAN (Rp.)</th>
                            <th rowspan="3" class="p-2 border-r border-b w-28 align-middle">SISA DANA (Rp.)</th>
                            <th rowspan="3" class="p-2 border-r border-b w-36 align-middle">PENYEDIA (PT/CV/..)</th>
                            <th rowspan="3" class="p-2 border-r border-b w-36 align-middle">NOMOR KONTRAK</th>
                            <th colspan="2" class="p-1 border-r border-b">PELAKSANAAN KONTRAK</th>
                            <th colspan="5" class="p-1 border-r border-b">REALISASI (Sampai bulan ini)</th>
                            <th rowspan="3" class="p-2 w-20 border-b align-middle">KET</th>
                        </tr>
                        <tr class="border-b border-gray-300 dark:!border-zinc-700 text-center text-[10px] font-bold">
                            <th rowspan="2" class="p-1.5 border-r border-b w-20 align-middle">MULAI</th>
                            <th rowspan="2" class="p-1.5 border-r border-b w-20 align-middle">SELESAI</th>
                            <th colspan="3" class="p-1.5 border-r border-b">FISIK (%)</th>
                            <th colspan="2" class="p-1.5 border-r border-b">KEUANGAN</th>
                        </tr>
                        <tr class="text-[10px] border-b border-gray-300 dark:!border-zinc-700 text-center font-medium">
                            <th class="p-1 border-r border-b w-16">Bobot Rencana</th>
                            <th class="p-1 border-r border-b w-16 font-semibold">Bobot Realisasi</th>
                            <th class="p-1 border-r border-b w-16 font-semibold">Deviasi (+/-)</th>
                            <th class="p-1 border-r border-b w-28">Rp.</th>
                            <th class="p-1 border-r border-b w-14 font-semibold">%</th>
                        </tr>
                        <tr
                            class="border-b border-gray-300 dark:!border-zinc-700 text-center text-[10px] font-semibold bg-gray-100 dark:!bg-zinc-800">
                            <th class="p-1 sticky-col-1 border-r border-b">1</th>
                            <th class="p-1 sticky-col-2 border-r border-b">2</th>
                            <th class="p-1 border-r border-b">3</th>
                            <th class="p-1 border-r border-b">4</th>
                            <th class="p-1 border-r border-b">5=3-4</th>
                            <th class="p-1 border-r border-b">6</th>
                            <th class="p-1 border-r border-b">7</th>
                            <th class="p-1 border-r border-b">8</th>
                            <th class="p-1 border-r border-b">9</th>
                            <th class="p-1 border-r border-b">10</th>
                            <th class="p-1 border-r border-b">11</th>
                            <th class="p-1 border-r border-b">12=11-10</th>
                            <th class="p-1 border-r border-b">13</th>
                            <th class="p-1 border-r border-b">14=13/4*100</th>
                            <th class="p-1 border-b">15</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:!divide-zinc-800">
                        @forelse($kemajuan['rows'] as $k)
                            <tr>
                                <td class="p-2 text-center sticky-col-1 border-r text-gray-500 dark:!text-zinc-400">
                                    {{ $k['no'] }}</td>
                                <td class="p-2 sticky-col-2 border-r">
                                    <span
                                        class="font-semibold text-gray-900 dark:!text-white block">{{ $k['nama_pekerjaan'] }}</span>
                                    <span
                                        class="text-[10px] text-gray-500 dark:!text-zinc-400">{{ $k['kegiatan'] }}</span>
                                </td>
                                <td class="p-2 text-right border-r font-mono text-gray-900 dark:!text-zinc-100">Rp
                                    {{ number_format($k['pagu'], 0, ',', '.') }}</td>
                                <td class="p-2 text-right border-r font-bold font-mono text-gray-900 dark:!text-white">
                                    Rp {{ number_format($k['nilai_kontrak'], 0, ',', '.') }}</td>
                                <td class="p-2 text-right border-r font-mono text-gray-600 dark:!text-zinc-400">Rp
                                    {{ number_format($k['sisa_dana'], 0, ',', '.') }}</td>
                                <td class="p-2 border-r font-medium text-gray-900 dark:!text-zinc-200">
                                    {{ $k['penyedia'] ?: '-' }}</td>
                                <td class="p-2 border-r font-mono text-[10px] text-gray-700 dark:!text-zinc-300">
                                    {{ $k['nomor_kontrak'] ?: '-' }}</td>
                                <td class="p-2 text-center border-r text-gray-600 dark:!text-zinc-400">
                                    {{ $k['tgl_mulai'] ?: '-' }}</td>
                                <td class="p-2 text-center border-r text-gray-600 dark:!text-zinc-400">
                                    {{ $k['tgl_selesai'] ?: '-' }}</td>
                                <td class="p-2 text-center border-r text-gray-600 dark:!text-zinc-400">
                                    {{ number_format($k['bobot_rencana'], 0, ',', '.') }}</td>
                                <td class="p-2 text-center font-bold text-blue-600 dark:!text-blue-400 border-r">
                                    {{ number_format($k['bobot_realisasi'], 0, ',', '.') }}</td>
                                <td
                                    class="p-2 text-center font-bold {{ $k['deviasi'] < 0 ? 'text-red-500' : 'text-emerald-500' }} border-r">
                                    {{ $k['deviasi'] != 0 ? number_format($k['deviasi'], 0, ',', '.') : '-' }}</td>
                                <td
                                    class="p-2 text-right font-bold text-emerald-600 dark:!text-emerald-400 border-r font-mono">
                                    Rp {{ number_format($k['realisasi_keu_rp'], 0, ',', '.') }}</td>
                                <td class="p-2 text-center font-bold border-r text-gray-900 dark:!text-white">
                                    {{ number_format($k['realisasi_keu_persen'], 0, ',', '.') }}</td>
                                <td class="p-2 text-center text-gray-500 dark:!text-zinc-400">
                                    {{ $k['status'] ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="15" class="p-8 text-center text-gray-500 dark:!text-zinc-400">Belum ada
                                    data paket pekerjaan kontraktual pada tahun {{ $tahun }}.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="font-bold border-t-2 sticky bottom-0 z-20">
                            <td colspan="2" class="p-3 text-center sticky-col-1 sticky-col-2 border-r">JUMLAH</td>
                            <td class="p-3 text-right font-mono text-gray-900 dark:!text-zinc-100 border-r">Rp
                                {{ number_format($kemajuan['summary']['total_pagu'], 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-mono text-amber-600 dark:!text-amber-400 border-r">Rp
                                {{ number_format($kemajuan['summary']['total_kontrak'], 0, ',', '.') }}</td>
                            <td class="p-3 text-right font-mono text-gray-700 dark:!text-zinc-300 border-r">Rp
                                {{ number_format($kemajuan['summary']['total_sisa'], 0, ',', '.') }}</td>
                            <td colspan="4" class="p-3 text-center border-r">-</td>
                            <td class="p-3 text-center border-r">0.00</td>
                            <td class="p-3 text-center border-r">0.00</td>
                            <td class="p-3 text-center border-r">0.00</td>
                            <td class="p-3 text-right font-mono text-emerald-600 dark:!text-emerald-400 border-r">Rp
                                {{ number_format($kemajuan['summary']['total_realisasi_keu'], 0, ',', '.') }}</td>
                            <td class="p-3 text-center font-bold border-r">
                                {{ $kemajuan['summary']['total_kontrak'] > 0 ? number_format(($kemajuan['summary']['total_realisasi_keu'] / $kemajuan['summary']['total_kontrak']) * 100, 2, ',', '.') : '100.00' }}
                            </td>
                            <td class="p-3 text-center">-</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    {{-- ======================================================== --}}
    {{-- TAB 3: MATRIKS UTAMA LRFK (SESUAI PAGE 3.PDF)            --}}
    {{-- ======================================================== --}}
    @if ($activeTab === 'matriks')
        <div class="laporan-card">
            <div class="laporan-card-header flex flex-col md:flex-row md:justify-between md:items-center gap-2">
                <div>
                    <h3 class="laporan-card-title text-base">LAPORAN REALISASI FISIK DAN KEUANGAN (LRFK)</h3>
                    <p class="laporan-card-sub text-xs mt-0.5">
                        OPD: DINAS PARIWISATA PEMUDA DAN OLAHRAGA KABUPATEN SIJUNJUNG &bull; Kondisi s.d Bulan :
                        <strong>{{ $namaBulanAktif }} {{ $tahun }}</strong>
                    </p>
                </div>
                <span
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:!bg-emerald-950/80 text-emerald-800 dark:!text-emerald-300 border border-emerald-300 dark:!border-emerald-700/60 w-fit">
                    ● Halaman 3 Resmi
                </span>
            </div>

            <div class="overflow-auto max-h-[720px] relative">
                <table class="w-full text-left text-xs border-collapse laporan-table">
                    <thead class="sticky top-0 z-30">
                        <tr
                            class="border-b border-gray-300 dark:!border-zinc-700 text-center uppercase tracking-wider text-[11px] font-bold">
                            <th rowspan="3" class="p-2 sticky-col-1 border-r border-b align-middle w-12">NO</th>
                            <th rowspan="3"
                                class="p-2 min-w-[280px] sticky-col-2 border-r border-b text-left align-middle">
                                KEGIATAN</th>
                            <th rowspan="3" class="p-2 border-r border-b w-36 align-middle">PAGU (Rp.)</th>
                            <th colspan="8" class="p-2 border-r border-b font-bold">REALISASI (sampai bulan ini)
                            </th>
                            <th rowspan="3" class="p-2 border-r border-b w-28 align-middle">Serapan Anggaran<br>(%)
                            </th>
                            <th rowspan="3" class="p-2 w-16 border-b align-middle">KET</th>
                        </tr>
                        <tr class="border-b border-gray-300 dark:!border-zinc-700 text-center text-[10px] font-bold">
                            <th colspan="3" class="p-1.5 border-r border-b">FISIK (%)</th>
                            <th colspan="4" class="p-1.5 border-r border-b">KEUANGAN (Rp.)</th>
                        </tr>
                        <tr class="text-[10px] border-b border-gray-300 dark:!border-zinc-700 text-center font-medium">
                            <th class="p-1 border-r border-b w-20">TARGET<br><span
                                    class="text-[9px] font-normal">(sesuai ROPK)</span></th>
                            <th class="p-1 border-r border-b w-16 font-semibold">REALISASI</th>
                            <th class="p-1 border-r border-b w-16 font-semibold">Capaian (%)</th>
                            <th class="p-1 border-r border-b w-28">TARGET (Sesuai ROPK)<br><span
                                    class="text-[9px] font-normal">Rp.</span></th>
                            <th class="p-1 border-r border-b w-14 font-semibold">%</th>
                            <th class="p-1 border-r border-b w-28 font-semibold">REALISASI<br><span
                                    class="text-[9px] font-normal">Rp.</span></th>
                            <th class="p-1 border-r border-b w-14 font-semibold">%</th>
                        </tr>
                        <tr
                            class="border-b border-gray-300 dark:!border-zinc-700 text-center text-[10px] font-semibold bg-gray-100 dark:!bg-zinc-800">
                            <th class="p-1 sticky-col-1 border-r border-b">1</th>
                            <th class="p-1 sticky-col-2 border-r border-b">2</th>
                            <th class="p-1 border-r border-b">3</th>
                            <th class="p-1 border-r border-b">4</th>
                            <th class="p-1 border-r border-b">5</th>
                            <th class="p-1 border-r border-b">6=(5/4)*100</th>
                            <th class="p-1 border-r border-b">7</th>
                            <th class="p-1 border-r border-b">8=7/3*100</th>
                            <th class="p-1 border-r border-b">9</th>
                            <th class="p-1 border-r border-b">10=9/3*100</th>
                            <th class="p-1 border-r border-b">11=(9/7)* 100</th>
                            <th class="p-1 border-b">12</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:!divide-zinc-800">
                        @forelse($matriks['rows'] as $idx => $r)
                            <tr>
                                <td class="p-2.5 text-center sticky-col-1 border-r">{{ $idx + 1 }}</td>
                                <td class="p-2.5 sticky-col-2 border-r">
                                    <span
                                        class="font-medium text-gray-900 dark:!text-white block">{{ $r['nama'] }}</span>
                                    <span
                                        class="font-mono text-[10px] text-gray-400 dark:!text-zinc-500">{{ $r['kode'] }}</span>
                                </td>
                                <td
                                    class="p-2.5 text-right font-mono border-r font-semibold text-gray-900 dark:!text-zinc-100">
                                    Rp {{ number_format($r['pagu'], 2, ',', '.') }}
                                </td>
                                <td class="p-2 text-center border-r text-gray-600 dark:!text-zinc-400">
                                    {{ number_format($r['target_fisik'], 2, ',', '.') }}
                                </td>
                                <td class="p-2 text-center font-bold text-blue-600 dark:!text-blue-400 border-r">
                                    {{ number_format($r['realisasi_fisik'], 2, ',', '.') }}
                                </td>
                                <td class="p-2 text-center font-bold text-emerald-600 dark:!text-emerald-400 border-r">
                                    {{ number_format($r['capaian_fisik'], 2, ',', '.') }}
                                </td>
                                <td class="p-2 text-right font-mono border-r text-gray-600 dark:!text-zinc-400">
                                    Rp {{ number_format($r['target_keu_rp'], 2, ',', '.') }}
                                </td>
                                <td class="p-2 text-center border-r text-gray-600 dark:!text-zinc-400">
                                    {{ number_format($r['target_keu_persen'], 2, ',', '.') }}
                                </td>
                                <td
                                    class="p-2 text-right font-bold font-mono text-emerald-600 dark:!text-emerald-400 border-r">
                                    {{ $r['realisasi_keu_rp'] > 0 ? 'Rp ' . number_format($r['realisasi_keu_rp'], 2, ',', '.') : 'Rp0,00' }}
                                </td>
                                <td class="p-2 text-center font-bold border-r text-gray-900 dark:!text-white">
                                    {{ $r['realisasi_keu_persen'] > 0 ? number_format($r['realisasi_keu_persen'], 2, ',', '.') : '-' }}
                                </td>
                                <td class="p-2 text-center font-bold border-r text-gray-900 dark:!text-white">
                                    {{ $r['serapan_anggaran'] > 0 ? number_format($r['serapan_anggaran'], 2, ',', '.') : '-' }}
                                </td>
                                <td class="p-2 text-center text-gray-500 dark:!text-zinc-400">-</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="p-8 text-center text-gray-500 dark:!text-zinc-400">
                                    Tidak ada data kegiatan anggaran untuk tahun {{ $tahun }}.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="font-bold border-t-2 sticky bottom-0 z-20">
                            <td colspan="2" class="p-3 text-center sticky-col-1 sticky-col-2 border-r">TOTAL</td>
                            <td class="p-3 text-right border-r font-mono text-amber-600 dark:!text-amber-400">
                                Rp {{ number_format($matriks['summary']['total_pagu'], 2, ',', '.') }}
                            </td>
                            <td class="p-3 text-center border-r">
                                {{ number_format($matriks['summary']['target_fisik'], 2, ',', '.') }}
                            </td>
                            <td class="p-3 text-center border-r text-blue-600 dark:!text-blue-400">
                                {{ number_format($matriks['summary']['realisasi_fisik'], 2, ',', '.') }}
                            </td>
                            <td class="p-3 text-center border-r text-emerald-600 dark:!text-emerald-400">
                                {{ number_format($matriks['summary']['capaian_fisik'], 2, ',', '.') }}
                            </td>
                            <td class="p-3 text-right border-r font-mono">
                                Rp {{ number_format($matriks['summary']['target_keu_rp'], 2, ',', '.') }}
                            </td>
                            <td class="p-3 text-center border-r">
                                {{ number_format($matriks['summary']['target_keu_persen'], 2, ',', '.') }}
                            </td>
                            <td class="p-3 text-right border-r text-emerald-600 dark:!text-emerald-400 font-mono">
                                Rp {{ number_format($matriks['summary']['realisasi_keu_rp'], 2, ',', '.') }}
                            </td>
                            <td class="p-3 text-center border-r text-emerald-600 dark:!text-emerald-400">
                                {{ number_format($matriks['summary']['realisasi_keu_persen'], 2, ',', '.') }}
                            </td>
                            <td class="p-3 text-center border-r">
                                {{ number_format($matriks['summary']['serapan_anggaran'], 2, ',', '.') }}
                            </td>
                            <td class="p-3 text-center">-</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif
</x-filament-panels::page>
