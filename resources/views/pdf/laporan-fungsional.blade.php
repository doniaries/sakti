<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pertanggungjawaban Fungsional (LPJ) - {{ $namaBulan }} {{ $tahun }}</title>
    <style>
        @page {
            margin: 15mm 15mm 15mm 15mm;
            size: 330mm 215mm landscape;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #111827;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .header-title {
            text-align: center;
            margin-bottom: 12px;
        }
        .header-title h2 {
            margin: 0 0 4px 0;
            font-size: 11pt;
            letter-spacing: 0.5px;
        }
        .header-title h3 {
            margin: 0 0 4px 0;
            font-size: 9.5pt;
            font-weight: normal;
        }
        .header-meta {
            width: 100%;
            margin-bottom: 8px;
            font-size: 8.5pt;
        }
        .header-meta td {
            padding: 2px 0;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8pt;
        }
        table.data-table th, table.data-table td {
            border: 0.5pt solid #374151;
            padding: 4px 5px;
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

        .ttd-table {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .ttd-table td {
            vertical-align: top;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header-title">
        <h2 class="uppercase">LAPORAN PERTANGGUNGJAWABAN BENDAHARA PENGELUARAN (LPJ FUNGSIONAL)</h2>
        <h3>PERIODE : S/D BULAN {{ strtoupper($namaBulan) }} {{ $tahun }}</h3>
    </div>

    <table class="header-meta">
        <tr>
            <td style="width: 60%; font-weight: bold;">SKPD : {{ strtoupper($instansi?->nama_dinas ?? 'DINAS PARIWISATA PEMUDA DAN OLAHRAGA') }}</td>
            <td style="width: 40%; text-align: right; font-weight: bold;">Tahun Anggaran : {{ $tahun }}</td>
        </tr>
    </table>

    {{-- TABEL REALISASI PER KODE REKENING --}}
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 30px;">NO</th>
                <th rowspan="2" style="width: 130px;">KODE REKENING</th>
                <th rowspan="2">URAIAN REKENING BELANJA</th>
                <th rowspan="2" style="width: 110px;">ANGGARAN DPA (Rp.)</th>
                <th colspan="3">REALISASI BELANJA (SPJ)</th>
                <th rowspan="2" style="width: 105px;">SISA ANGGARAN (Rp.)</th>
                <th rowspan="2" style="width: 50px;">SERAPAN (%)</th>
            </tr>
            <tr>
                <th style="width: 95px;">s/d Bulan Lalu</th>
                <th style="width: 95px;">Bulan Ini</th>
                <th style="width: 105px;">Total s/d Bulan Ini</th>
            </tr>
            <tr style="background-color: #e5e7eb; font-size: 7pt;">
                <th>1</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
                <th>5</th>
                <th>6</th>
                <th>7=5+6</th>
                <th>8=4-7</th>
                <th>9=7/4*100</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data['rekening_rows'] as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $row['kode'] }}</td>
                    <td>{{ $row['uraian'] }}</td>
                    <td class="text-right">{{ number_format($row['pagu'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['realisasi_lalu'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['realisasi_ini'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($row['total_sd_ini'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['sisa'], 0, ',', '.') }}</td>
                    <td class="text-center font-bold">{{ number_format($row['persen'], 2, ',', '.') }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 15px;">Belum ada realisasi anggaran belanja pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="bg-total">
                <td colspan="3" class="text-center font-bold">TOTAL PENGELUARAN BELANJA</td>
                <td class="text-right font-bold">{{ number_format($data['summary']['total_pagu'], 0, ',', '.') }}</td>
                <td class="text-right font-bold">{{ number_format($data['summary']['total_lalu'], 0, ',', '.') }}</td>
                <td class="text-right font-bold">{{ number_format($data['summary']['total_ini'], 0, ',', '.') }}</td>
                <td class="text-right font-bold">{{ number_format($data['summary']['total_sd_ini'], 0, ',', '.') }}</td>
                <td class="text-right font-bold">{{ number_format($data['summary']['total_sisa'], 0, ',', '.') }}</td>
                <td class="text-center font-bold">{{ number_format($data['summary']['persen'], 2, ',', '.') }}%</td>
            </tr>
        </tfoot>
    </table>

    {{-- REKAPITULASI PEMOTONGAN PAJAK --}}
    <div style="width: 450px; margin-top: 10px;">
        <div class="font-bold" style="margin-bottom: 4px; font-size: 8.5pt;">REKAPITULASI PENERIMAAN DAN PENYETORAN PAJAK :</div>
        <table class="data-table" style="font-size: 8pt; margin-bottom: 0;">
            <thead>
                <tr>
                    <th style="width: 30px;">NO</th>
                    <th>JENIS PAJAK</th>
                    <th style="width: 140px;">JUMLAH DIPOTONG (Rp.)</th>
                    <th style="width: 140px;">JUMLAH DISETOR (Rp.)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">1</td>
                    <td>PPN (Pajak Pertambahan Nilai 11%)</td>
                    <td class="text-right font-bold">{{ number_format($data['pajak']['ppn'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($data['pajak']['ppn'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="text-center">2</td>
                    <td>PPh Pasal 22 (Pajak Penghasilan 1,5%)</td>
                    <td class="text-right font-bold">{{ number_format($data['pajak']['pph22'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($data['pajak']['pph22'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="bg-total">
                    <td colspan="2" class="text-center font-bold">TOTAL PAJAK</td>
                    <td class="text-right font-bold">{{ number_format($data['pajak']['total_pajak'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($data['pajak']['total_pajak'], 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- TANDA TANGAN PA & BENDAHARA --}}
    <table class="ttd-table">
        <tr>
            <td style="width: 50%;">
                <div>Mengetahui / Menyetujui,</div>
                <div class="font-bold">PENGGUNA ANGGARAN (PA)</div>
                <div class="font-bold">{{ strtoupper($instansi?->nama_dinas ?? 'DINAS PARIWISATA PEMUDA DAN OLAHRAGA') }}</div>
                <div style="height: 50px;"></div>
                <div class="font-bold" style="text-decoration: underline;">{{ $kadis?->name ?? 'AFRINELDI, SH' }}</div>
                <div>NIP. {{ $kadis?->nip ?? '19720407 199202 1 002' }}</div>
            </td>
            <td style="width: 50%;">
                <div>Muaro Sijunjung, {{ $tglCetak }}</div>
                <div>Dibuat oleh,</div>
                <div class="font-bold">BENDAHARA PENGELUARAN</div>
                <div style="height: 50px;"></div>
                <div class="font-bold" style="text-decoration: underline;">{{ $bendahara?->name ?? 'WIWIRNA' }}</div>
                <div>NIP. {{ $bendahara?->nip ?? '19850723 200901 2 007' }}</div>
            </td>
        </tr>
    </table>

</body>
</html>

