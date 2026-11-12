<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Pertanggungjawaban Fungsional (LPJ) - {{ $namaBulan }} {{ $tahun }}</title>
    <style>
        @page {
            margin: 10mm 12mm 10mm 12mm;
            size: 330mm 215mm landscape;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7.5pt;
            color: #111827;
            line-height: 1.2;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .font-bold {
            font-weight: bold;
        }

        .uppercase {
            text-transform: uppercase;
        }

        .whitespace-nowrap {
            white-space: nowrap;
        }

        .header-title {
            text-align: center;
            margin-bottom: 8px;
        }

        .header-title h2 {
            margin: 0 0 2px 0;
            font-size: 10.5pt;
            letter-spacing: 0.5px;
        }

        .header-title h3 {
            margin: 0 0 2px 0;
            font-size: 9.5pt;
            font-weight: bold;
        }

        .header-title h4 {
            margin: 0 0 2px 0;
            font-size: 8.5pt;
            font-weight: normal;
        }

        .header-meta {
            width: 100%;
            margin-bottom: 6px;
            font-size: 7.5pt;
            border-collapse: collapse;
        }

        .header-meta td {
            padding: 1.5px 2px;
            vertical-align: top;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 6.8pt;
            page-break-inside: auto;
        }

        table.data-table thead {
            display: table-header-group;
        }

        table.data-table tfoot {
            display: table-row-group;
            page-break-inside: avoid;
        }

        table.data-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        table.data-table th,
        table.data-table td {
            border: 0.5pt solid #374151;
            padding: 2.5px 3px;
            page-break-inside: avoid;
        }

        table.data-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }

        .bg-total {
            background-color: #e5e7eb;
            font-weight: bold;
        }

        .rekap-container {
            width: 100%;
            margin-top: 6px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        .rekap-container td {
            vertical-align: top;
            padding: 0 4px;
        }

        table.sub-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
        }

        table.sub-table th,
        table.sub-table td {
            border: 0.5pt solid #374151;
            padding: 2px 3px;
        }

        .ttd-table {
            width: 100%;
            margin-top: 14px;
            page-break-inside: avoid;
            font-size: 7.5pt;
        }

        .ttd-table td {
            vertical-align: top;
            text-align: center;
        }

        @media screen {
            html,
            body {
                background-color: #ffffff !important;
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }

            .page-sheet {
                background: #ffffff !important;
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
                padding: 6mm 8mm !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
            }
        }

        @media print {
            html,
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            .page-sheet {
                width: 100% !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="page-sheet">

        {{-- KOP DOKUMEN SESUAI SIPD-RI (JUDUL DITENGAH, BULAN & TAHUN BERDEKATAN) --}}
        <div style="text-align: center; margin-bottom: 8px;">
            <div style="font-weight: bold; font-size: 10pt; letter-spacing: 0.5px;">PEMERINTAH KABUPATEN SIJUNJUNG</div>
            <div style="font-size: 8.5pt; font-weight: bold; margin-top: 1px;">
                SKPD : {{ strtoupper($instansi?->nama_dinas ?? 'DINAS PARIWISATA, PEMUDA DAN OLAH RAGA') }}
            </div>
            <div style="font-weight: bold; font-size: 9.5pt; margin-top: 3px; letter-spacing: 0.5px;">
                LAPORAN PERTANGGUNGJAWABAN BENDAHARA PENGELUARAN
            </div>
            <div style="font-weight: bold; font-size: 9pt; letter-spacing: 0.5px;">
                (SPJ FUNGSIONAL)
            </div>
            <div style="font-size: 8.5pt; font-weight: bold; margin-top: 3px;">
                Bulan : {{ $namaBulan }} &nbsp;&bull;&nbsp; Tahun Anggaran : {{ $tahun }}
            </div>
        </div>

        {{-- TABEL 14 KOLOM STANDAR SIPD-RI --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 85px;">Kode Rekening</th>
                    <th rowspan="2">Uraian</th>
                    <th rowspan="2" style="width: 68px;">Jumlah Anggaran</th>
                    <th colspan="3">SPJ - LS Gaji</th>
                    <th colspan="3">SPJ - LS Barang &amp; Jasa</th>
                    <th colspan="3">SPJ - UP/GU/TU</th>
                    <th rowspan="2" style="width: 72px;">Jumlah SPJ (LS + UP/GU/TU)<br>s.d Bulan ini</th>
                    <th rowspan="2" style="width: 68px;">Sisa Pagu Anggaran</th>
                </tr>
                <tr>
                    <th style="width: 52px;">s.d. Bulan Lalu</th>
                    <th style="width: 52px;">Bulan Ini</th>
                    <th style="width: 52px;">s.d. Bulan Ini</th>
                    <th style="width: 52px;">s.d. Bulan Lalu</th>
                    <th style="width: 52px;">Bulan Ini</th>
                    <th style="width: 52px;">s.d. Bulan Ini</th>
                    <th style="width: 52px;">s.d. Bulan Lalu</th>
                    <th style="width: 52px;">Bulan Ini</th>
                    <th style="width: 52px;">s.d. Bulan Ini</th>
                </tr>
                <tr style="background-color: #e5e7eb; font-size: 6pt;">
                    <th>1</th>
                    <th>2</th>
                    <th>3</th>
                    <th>4</th>
                    <th>5</th>
                    <th>6 = (4+5)</th>
                    <th>7</th>
                    <th>8</th>
                    <th>9 = (7+8)</th>
                    <th>10</th>
                    <th>11</th>
                    <th>12 = (10+11)</th>
                    <th>13 = (6+9+12)</th>
                    <th>14 = (3-13)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data['rows'] as $row)
                    @php
                        $isBold = (bool) ($row['is_bold'] ?? false);
                        $type = $row['type'] ?? '';
                        $bgStyle = match ($type) {
                            'summary_header' => 'background-color: #f3f4f6;',
                            'skpd' => 'background-color: #f3f4f6;',
                            'sub_kegiatan' => 'background-color: #f3f4f6;',
                            'program' => 'background-color: #f9fafb;',
                            'kegiatan' => 'background-color: #ffffff;',
                            default => '',
                        };
                        $indentPx = (int) (($row['indent'] ?? 0) * 8);
                    @endphp
                    @if ($type === 'sub_kegiatan')
                        <tr
                            style="background-color: #f3f4f6; font-weight: bold; border-top: 0.5pt solid #9ca3af; border-bottom: 0.5pt solid #9ca3af;">
                            <td class="font-mono text-center" style="font-weight: bold; vertical-align: middle;">
                                {{ $row['kode'] }}</td>
                            <td style="padding: 3px 4px;">
                                <div
                                    style="font-size: 7pt; font-weight: bold; color: #374151; text-transform: uppercase;">
                                    BIDANG : {{ $row['bidang_nama'] ?? 'SEKRETARIAT' }}
                                </div>
                                <div
                                    style="font-size: 7.5pt; font-weight: bold; color: #111827; margin-top: 1px;">
                                    SUB KEGIATAN : {{ ucwords(strtolower($row['uraian'])) }}
                                </div>
                            </td>
                            <td class="text-right whitespace-nowrap font-bold">
                                {{ $row['pagu'] > 0 ? number_format($row['pagu'], 2, ',', '.') : '0,00' }}</td>
                            <td class="text-right whitespace-nowrap">
                                {{ $row['ls_gaji_lalu'] > 0 ? number_format($row['ls_gaji_lalu'], 2, ',', '.') : '0,00' }}
                            </td>
                            <td class="text-right whitespace-nowrap">
                                {{ $row['ls_gaji_ini'] > 0 ? number_format($row['ls_gaji_ini'], 2, ',', '.') : '0,00' }}
                            </td>
                            <td class="text-right whitespace-nowrap font-bold">
                                {{ $row['ls_gaji_sd_ini'] > 0 ? number_format($row['ls_gaji_sd_ini'], 2, ',', '.') : '0,00' }}
                            </td>
                            <td class="text-right whitespace-nowrap">
                                {{ $row['ls_bj_lalu'] > 0 ? number_format($row['ls_bj_lalu'], 2, ',', '.') : '0,00' }}
                            </td>
                            <td class="text-right whitespace-nowrap">
                                {{ $row['ls_bj_ini'] > 0 ? number_format($row['ls_bj_ini'], 2, ',', '.') : '0,00' }}
                            </td>
                            <td class="text-right whitespace-nowrap font-bold">
                                {{ $row['ls_bj_sd_ini'] > 0 ? number_format($row['ls_bj_sd_ini'], 2, ',', '.') : '0,00' }}
                            </td>
                            <td class="text-right whitespace-nowrap">
                                {{ $row['up_lalu'] > 0 ? number_format($row['up_lalu'], 2, ',', '.') : '0,00' }}</td>
                            <td class="text-right whitespace-nowrap">
                                {{ $row['up_ini'] > 0 ? number_format($row['up_ini'], 2, ',', '.') : '0,00' }}</td>
                            <td class="text-right whitespace-nowrap font-bold">
                                {{ $row['up_sd_ini'] > 0 ? number_format($row['up_sd_ini'], 2, ',', '.') : '0,00' }}
                            </td>
                            <td class="text-right whitespace-nowrap font-bold">
                                {{ $row['total_spj'] > 0 ? number_format($row['total_spj'], 2, ',', '.') : '0,00' }}
                            </td>
                            <td class="text-right whitespace-nowrap">{{ number_format($row['sisa'], 2, ',', '.') }}
                            </td>
                        </tr>
                    @else
                        <tr style="{{ $bgStyle }} {{ $isBold ? 'font-weight: bold;' : '' }}">
                            <td class="font-mono whitespace-nowrap">{{ $row['kode'] }}</td>
                            <td>
                                <div style="padding-left: {{ $indentPx }}px;">
                                    @if ($type === 'kegiatan')
                                        KEGIATAN : {{ $row['uraian'] }}
                                    @else
                                        {{ $row['uraian'] }}
                                    @endif
                                </div>
                            </td>
                            <td class="text-right whitespace-nowrap">
                                {{ $row['pagu'] > 0 ? number_format($row['pagu'], 2, ',', '.') : '0,00' }}</td>
                            <td class="text-right whitespace-nowrap">
                                {{ $row['ls_gaji_lalu'] > 0 ? number_format($row['ls_gaji_lalu'], 2, ',', '.') : '0,00' }}
                            </td>
                            <td class="text-right whitespace-nowrap">
                                {{ $row['ls_gaji_ini'] > 0 ? number_format($row['ls_gaji_ini'], 2, ',', '.') : '0,00' }}
                            </td>
                            <td class="text-right whitespace-nowrap font-bold">
                                {{ $row['ls_gaji_sd_ini'] > 0 ? number_format($row['ls_gaji_sd_ini'], 2, ',', '.') : '0,00' }}
                            </td>
                            <td class="text-right whitespace-nowrap">
                                {{ $row['ls_bj_lalu'] > 0 ? number_format($row['ls_bj_lalu'], 2, ',', '.') : '0,00' }}
                            </td>
                            <td class="text-right whitespace-nowrap">
                                {{ $row['ls_bj_ini'] > 0 ? number_format($row['ls_bj_ini'], 2, ',', '.') : '0,00' }}
                            </td>
                            <td class="text-right whitespace-nowrap font-bold">
                                {{ $row['ls_bj_sd_ini'] > 0 ? number_format($row['ls_bj_sd_ini'], 2, ',', '.') : '0,00' }}
                            </td>
                            <td class="text-right whitespace-nowrap">
                                {{ $row['up_lalu'] > 0 ? number_format($row['up_lalu'], 2, ',', '.') : '0,00' }}</td>
                            <td class="text-right whitespace-nowrap">
                                {{ $row['up_ini'] > 0 ? number_format($row['up_ini'], 2, ',', '.') : '0,00' }}</td>
                            <td class="text-right whitespace-nowrap font-bold">
                                {{ $row['up_sd_ini'] > 0 ? number_format($row['up_sd_ini'], 2, ',', '.') : '0,00' }}
                            </td>
                            <td class="text-right whitespace-nowrap font-bold">
                                {{ $row['total_spj'] > 0 ? number_format($row['total_spj'], 2, ',', '.') : '0,00' }}
                            </td>
                            <td class="text-right whitespace-nowrap">{{ number_format($row['sisa'], 2, ',', '.') }}
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="14" class="text-center" style="padding: 12px;">Belum ada realisasi anggaran
                            belanja pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-total">
                    <td colspan="2" class="text-center font-bold">JUMLAH</td>
                    <td class="text-right font-bold whitespace-nowrap">
                        {{ number_format($data['summary']['pagu'], 2, ',', '.') }}</td>
                    <td class="text-right font-bold whitespace-nowrap">
                        {{ number_format($data['summary']['ls_gaji_lalu'], 2, ',', '.') }}</td>
                    <td class="text-right font-bold whitespace-nowrap">
                        {{ number_format($data['summary']['ls_gaji_ini'], 2, ',', '.') }}</td>
                    <td class="text-right font-bold whitespace-nowrap">
                        {{ number_format($data['summary']['ls_gaji_sd_ini'], 2, ',', '.') }}</td>
                    <td class="text-right font-bold whitespace-nowrap">
                        {{ number_format($data['summary']['ls_bj_lalu'], 2, ',', '.') }}</td>
                    <td class="text-right font-bold whitespace-nowrap">
                        {{ number_format($data['summary']['ls_bj_ini'], 2, ',', '.') }}</td>
                    <td class="text-right font-bold whitespace-nowrap">
                        {{ number_format($data['summary']['ls_bj_sd_ini'], 2, ',', '.') }}</td>
                    <td class="text-right font-bold whitespace-nowrap">
                        {{ number_format($data['summary']['up_lalu'], 2, ',', '.') }}</td>
                    <td class="text-right font-bold whitespace-nowrap">
                        {{ number_format($data['summary']['up_ini'], 2, ',', '.') }}</td>
                    <td class="text-right font-bold whitespace-nowrap">
                        {{ number_format($data['summary']['up_sd_ini'], 2, ',', '.') }}</td>
                    <td class="text-right font-bold whitespace-nowrap">
                        {{ number_format($data['summary']['total_spj'], 2, ',', '.') }}</td>
                    <td class="text-right font-bold whitespace-nowrap">
                        {{ number_format($data['summary']['sisa'], 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- BAGIAN PENERIMAAN & PENGELUARAN (STANDAR SIPD-RI) --}}
        <table class="rekap-container">
            <tr>
                {{-- PENERIMAAN --}}
                <td style="width: 52%;">
                    <div class="font-bold" style="margin-bottom: 2px;">Penerimaan :</div>
                    <table class="sub-table">
                        <thead>
                            <tr style="background-color: #f3f4f6;">
                                <th>Uraian</th>
                                <th style="width: 65px;">s.d Bulan Lalu</th>
                                <th style="width: 65px;">Bulan Ini</th>
                                <th style="width: 70px;">s.d Bulan Ini</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="font-bold">
                                <td colspan="4">- SP2D :</td>
                            </tr>
                            <tr>
                                <td style="padding-left: 12px;">SP2D UP</td>
                                <td class="text-right">
                                    {{ number_format($data['penerimaan']['up']['lalu'], 2, ',', '.') }}</td>
                                <td class="text-right">
                                    {{ number_format($data['penerimaan']['up']['ini'], 2, ',', '.') }}</td>
                                <td class="text-right font-bold">
                                    {{ number_format($data['penerimaan']['up']['sd_ini'], 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td style="padding-left: 12px;">SP2D GU</td>
                                <td class="text-right">
                                    {{ number_format($data['penerimaan']['gu']['lalu'], 2, ',', '.') }}</td>
                                <td class="text-right">
                                    {{ number_format($data['penerimaan']['gu']['ini'], 2, ',', '.') }}</td>
                                <td class="text-right font-bold">
                                    {{ number_format($data['penerimaan']['gu']['sd_ini'], 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td style="padding-left: 12px;">SP2D TU</td>
                                <td class="text-right">
                                    {{ number_format($data['penerimaan']['tu']['lalu'], 2, ',', '.') }}</td>
                                <td class="text-right">
                                    {{ number_format($data['penerimaan']['tu']['ini'], 2, ',', '.') }}</td>
                                <td class="text-right font-bold">
                                    {{ number_format($data['penerimaan']['tu']['sd_ini'], 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td style="padding-left: 12px;">SP2D LS</td>
                                <td class="text-right">
                                    {{ number_format($data['penerimaan']['ls']['lalu'], 2, ',', '.') }}</td>
                                <td class="text-right">
                                    {{ number_format($data['penerimaan']['ls']['ini'], 2, ',', '.') }}</td>
                                <td class="text-right font-bold">
                                    {{ number_format($data['penerimaan']['ls']['sd_ini'], 2, ',', '.') }}</td>
                            </tr>
                            <tr class="font-bold">
                                <td colspan="4">- Potongan :</td>
                            </tr>
                            <tr>
                                <td style="padding-left: 12px;">PPN (11%)</td>
                                <td colspan="3" class="text-right">
                                    {{ number_format($data['pajak']['ppn'], 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td style="padding-left: 12px;">PPh Pasal 22 (1,5%)</td>
                                <td colspan="3" class="text-right">
                                    {{ number_format($data['pajak']['pph22'], 2, ',', '.') }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-total">
                                <td>JUMLAH PENERIMAAN</td>
                                <td colspan="3" class="text-right font-bold">
                                    {{ number_format($data['penerimaan']['total_bruto'], 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </td>

                {{-- PENGELUARAN --}}
                <td style="width: 48%;">
                    <div class="font-bold" style="margin-bottom: 2px;">Pengeluaran :</div>
                    <table class="sub-table">
                        <thead>
                            <tr style="background-color: #f3f4f6;">
                                <th>Uraian</th>
                                <th style="width: 100px;">Jumlah (Rp.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>- SPJ (UP/GU)</td>
                                <td class="text-right">
                                    {{ number_format($data['pengeluaran']['spj_up_gu'], 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>- SPJ (TU)</td>
                                <td class="text-right">
                                    {{ number_format($data['pengeluaran']['spj_tu'], 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>- SPJ (LS)</td>
                                <td class="text-right">
                                    {{ number_format($data['pengeluaran']['spj_ls'], 2, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <td>- Penyetoran Pajak</td>
                                <td class="text-right">{{ number_format($data['pajak']['total_pajak'], 2, ',', '.') }}
                                </td>
                            </tr>
                            <tr>
                                <td>- Pengembalian Kasda (STS)</td>
                                <td class="text-right">
                                    {{ number_format($data['pengeluaran']['pengembalian_sts'], 2, ',', '.') }}</td>
                            </tr>
                            <tr class="bg-total">
                                <td>JUMLAH PENGELUARAN</td>
                                <td class="text-right font-bold">
                                    {{ number_format($data['pengeluaran']['total_pengeluaran'], 2, ',', '.') }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #d1fae5; font-weight: bold;">
                                <td>SALDO KAS (UP/GU/TU)</td>
                                <td class="text-right font-bold">
                                    {{ number_format($data['pengeluaran']['saldo_kas'], 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
        </table>

        {{-- TANDA TANGAN PA & BENDAHARA --}}
        <table class="ttd-table">
            <tr>
                <td style="width: 50%;">
                    <div>Menyetujui,</div>
                    <div class="font-bold">PENGGUNA ANGGARAN</div>
                    {{-- <div class="font-bold">{{ strtoupper($instansi?->nama_dinas ?? 'DINAS PARIWISATA, PEMUDA DAN OLAH RAGA') }}</div> --}}
                    <div style="height: 40px;"></div>
                    <div class="font-bold" style="text-decoration: underline;">{{ $kadis?->name ?? 'AFRINELDI, SH' }}
                    </div>
                    <div>NIP. {{ $kadis?->nip ?? '19720407 199202 1 002' }}</div>
                </td>
                <td style="width: 50%;">
                    <div>Muaro Sijunjung, {{ $tglCetak }}</div>
                    <div>Disiapkan oleh,</div>
                    <div class="font-bold">BENDAHARA PENGELUARAN</div>
                    <div style="height: 40px;"></div>
                    <div class="font-bold" style="text-decoration: underline;">{{ $bendahara?->name ?? 'WIWIRNA' }}
                    </div>
                    <div>NIP. {{ $bendahara?->nip ?? '19850723 200901 2 007' }}</div>
                </td>
            </tr>
        </table>

    </div>
</body>

</html>
