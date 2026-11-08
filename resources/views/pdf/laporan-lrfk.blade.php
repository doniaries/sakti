<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Realisasi Fisik dan Keuangan (LRFK) - {{ $namaBulan }} {{ $tahun }}</title>
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
            font-size: 12pt;
            letter-spacing: 0.5px;
        }
        .header-title h3 {
            margin: 0 0 4px 0;
            font-size: 10pt;
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
        .bg-gray-sub {
            background-color: #f9fafb;
            font-weight: bold;
        }
        .bg-total {
            background-color: #e5e7eb;
            font-weight: bold;
        }
        .page-break {
            page-break-after: always;
        }

        .ttd-container {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .ttd-box {
            float: right;
            width: 320px;
            text-align: center;
        }
    </style>
</head>
<body>

    {{-- ======================================================== --}}
    {{-- BAGIAN 1: MATRIKS UTAMA LRFK (SESUAI GAMBAR 2)           --}}
    {{-- ======================================================== --}}
    @if(in_array($tipe, ['matriks', 'lengkap']))
    <div>
        <div class="header-title">
            <h2 class="uppercase">LAPORAN REALISASI FISIK DAN KEUANGAN (LRFK)</h2>
            <h3>KABUPATEN SIJUNJUNG - TAHUN ANGGARAN {{ $tahun }}</h3>
        </div>

        <table class="header-meta">
            <tr>
                <td style="width: 60%; font-weight: bold;">OPD: {{ strtoupper($instansi?->nama_dinas ?? 'DINAS PARIWISATA PEMUDA DAN OLAHRAGA') }}</td>
                <td style="width: 40%; text-align: right; font-weight: bold;">Kondisi s.d Bulan : {{ $tglKondisi }}</td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th rowspan="3" style="width: 25px;">NO</th>
                    <th rowspan="3">KEGIATAN</th>
                    <th rowspan="3" style="width: 95px;">PAGU (Rp.)</th>
                    <th colspan="8">REALISASI (sampai bulan ini)</th>
                    <th rowspan="3" style="width: 65px;">KET</th>
                </tr>
                <tr>
                    <th colspan="3">FISIK (%)</th>
                    <th colspan="4">KEUANGAN (Rp.)</th>
                    <th rowspan="2" style="width: 60px;">Serapan<br>Anggaran (%)<br><span style="font-size: 6.5pt;">(9/7)*100</span></th>
                </tr>
                <tr>
                    <th style="width: 48px;">TARGET<br><span style="font-size: 6.5pt;">(ROPK)</span></th>
                    <th style="width: 50px;">REALISASI</th>
                    <th style="width: 50px;">Capaian (%)<br><span style="font-size: 6.5pt;">(5/4)*100</span></th>
                    <th style="width: 85px;">TARGET Rp.<br><span style="font-size: 6.5pt;">(ROPK)</span></th>
                    <th style="width: 38px;">%<br><span style="font-size: 6.5pt;">(7/3)*100</span></th>
                    <th style="width: 85px;">REALISASI Rp.</th>
                    <th style="width: 38px;">%<br><span style="font-size: 6.5pt;">(9/3)*100</span></th>
                </tr>
                <tr style="background-color: #e5e7eb; font-size: 7pt;">
                    <th>1</th>
                    <th>2</th>
                    <th>3</th>
                    <th>4</th>
                    <th>5</th>
                    <th>6</th>
                    <th>7</th>
                    <th>8</th>
                    <th>9</th>
                    <th>10</th>
                    <th>11</th>
                    <th>12</th>
                </tr>
            </thead>
            <tbody>
                @forelse($matriks['rows'] as $index => $row)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <div class="font-bold">{{ $row['kode'] }}</div>
                            <div>{{ $row['nama'] }}</div>
                        </td>
                        <td class="text-right font-bold">{{ number_format($row['pagu'], 0, ',', '.') }}</td>
                        <td class="text-center">{{ number_format($row['target_fisik'], 2, ',', '.') }}</td>
                        <td class="text-center font-bold">{{ number_format($row['realisasi_fisik'], 2, ',', '.') }}</td>
                        <td class="text-center font-bold">{{ number_format($row['capaian_fisik'], 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($row['target_keu_rp'], 0, ',', '.') }}</td>
                        <td class="text-center">{{ number_format($row['target_keu_persen'], 2, ',', '.') }}</td>
                        <td class="text-right font-bold">{{ number_format($row['realisasi_keu_rp'], 0, ',', '.') }}</td>
                        <td class="text-center font-bold">{{ number_format($row['realisasi_keu_persen'], 2, ',', '.') }}</td>
                        <td class="text-center font-bold">{{ number_format($row['serapan_anggaran'], 2, ',', '.') }}</td>
                        <td class="text-center" style="font-size: 7.5pt;">{{ $row['keterangan'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center" style="padding: 15px;">Belum ada data kegiatan pada tahun {{ $tahun }}.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-total">
                    <td colspan="2" class="text-center font-bold">JUMLAH TOTAL</td>
                    <td class="text-right font-bold">{{ number_format($matriks['summary']['total_pagu'], 0, ',', '.') }}</td>
                    <td class="text-center font-bold">{{ number_format($matriks['summary']['target_fisik'], 2, ',', '.') }}</td>
                    <td class="text-center font-bold">{{ number_format($matriks['summary']['realisasi_fisik'], 2, ',', '.') }}</td>
                    <td class="text-center font-bold">{{ number_format($matriks['summary']['capaian_fisik'], 2, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($matriks['summary']['target_keu_rp'], 0, ',', '.') }}</td>
                    <td class="text-center font-bold">{{ number_format($matriks['summary']['target_keu_persen'], 2, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($matriks['summary']['realisasi_keu_rp'], 0, ',', '.') }}</td>
                    <td class="text-center font-bold">{{ number_format($matriks['summary']['realisasi_keu_persen'], 2, ',', '.') }}</td>
                    <td class="text-center font-bold">{{ number_format($matriks['summary']['serapan_anggaran'], 2, ',', '.') }}</td>
                    <td class="text-center">-</td>
                </tr>
            </tfoot>
        </table>

        {{-- Blok Tanda Tangan Sekretaris Sesuai Gambar 3 & Excel Acuan --}}
        <div class="ttd-container">
            <div class="ttd-box">
                <div>Muaro Sijunjung, {{ $tglCetak }}</div>
                <div class="font-bold">a.n. KEPALA DINAS PARIWISATA PEMUDA DAN OLAHRAGA</div>
                <div class="font-bold">SEKRETARIS</div>
                <div style="height: 50px;"></div>
                <div class="font-bold" style="text-decoration: underline;">{{ $sekretaris?->name ?? 'DESMAWATI, SE. M.Si' }}</div>
                <div>NIP. {{ $sekretaris?->nip ?? '19691224 199503 2 002' }}</div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
    @endif

    {{-- ======================================================== --}}
    {{-- BAGIAN 2: LAMPIRAN 1 - REALISASI BELANJA (SESUAI GAMBAR 4) --}}
    {{-- ======================================================== --}}
    @if(in_array($tipe, ['lampiran1', 'lengkap']))
    <div class="{{ $tipe === 'lengkap' ? 'page-break' : '' }}">
        <div style="font-size: 8pt; font-weight: bold; margin-bottom: 2px;">Lampiran 1</div>
        <div class="header-title">
            <h2 class="uppercase">LAPORAN REALISASI BELANJA TAHUN ANGGARAN {{ $tahun }}</h2>
        </div>

        <table class="header-meta">
            <tr>
                <td style="width: 60%; font-weight: bold;">PD : {{ strtoupper($instansi?->nama_dinas ?? 'DINAS PARIWISATA PEMUDA DAN OLAHRAGA') }}</td>
                <td style="width: 40%; text-align: right; font-weight: bold;">Kondisi s/d Bulan : {{ $tglKondisi }}</td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 35px;">NO.</th>
                    <th>URAIAN BELANJA</th>
                    <th style="width: 150px;">ANGGARAN (Rp.)</th>
                    <th style="width: 150px;">REALISASI (Rp.)<br><span style="font-size: 7pt;">S/d Bulan Ini</span></th>
                    <th style="width: 80px;">KET. (%)</th>
                </tr>
                <tr style="background-color: #e5e7eb; font-size: 7pt;">
                    <th>1</th>
                    <th>2</th>
                    <th>3</th>
                    <th>4</th>
                    <th>5</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lampiran1['kelompok'] as $kelompok)
                    <tr class="bg-gray-sub">
                        <td class="text-center font-bold">{{ $kelompok['nomor'] }}</td>
                        <td class="font-bold">{{ $kelompok['uraian'] }}</td>
                        <td class="text-right font-bold">{{ number_format($kelompok['anggaran'], 0, ',', '.') }}</td>
                        <td class="text-right font-bold">{{ number_format($kelompok['realisasi'], 0, ',', '.') }}</td>
                        <td class="text-center font-bold">{{ number_format($kelompok['persen'], 2, ',', '.') }}%</td>
                    </tr>
                    @foreach($kelompok['sub_items'] as $sub)
                        <tr>
                            <td class="text-center">-</td>
                            <td style="padding-left: 20px;">- {{ $sub['nama'] }}</td>
                            <td class="text-right">{{ number_format($sub['anggaran'], 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($sub['realisasi'], 0, ',', '.') }}</td>
                            <td class="text-center">{{ number_format($sub['persen'], 2, ',', '.') }}%</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-total">
                    <td colspan="2" class="text-center font-bold">TOTAL BELANJA</td>
                    <td class="text-right font-bold">{{ number_format($lampiran1['total_anggaran'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($lampiran1['total_realisasi'], 0, ',', '.') }}</td>
                    <td class="text-center font-bold">{{ number_format($lampiran1['persen'], 2, ',', '.') }}%</td>
                </tr>
            </tfoot>
        </table>

        <div class="ttd-container">
            <div class="ttd-box">
                <div>Muaro Sijunjung, {{ $tglCetak }}</div>
                <div class="font-bold">a.n. KEPALA DINAS PARIWISATA PEMUDA DAN OLAHRAGA</div>
                <div class="font-bold">SEKRETARIS</div>
                <div style="height: 50px;"></div>
                <div class="font-bold" style="text-decoration: underline;">{{ $sekretaris?->name ?? 'DESMAWATI, SE. M.Si' }}</div>
                <div>NIP. {{ $sekretaris?->nip ?? '19691224 199503 2 002' }}</div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
    @endif

    {{-- ======================================================== --}}
    {{-- BAGIAN 3: KEMAJUAN PEKERJAAN FISIK (SESUAI GAMBAR 3)      --}}
    {{-- ======================================================== --}}
    @if(in_array($tipe, ['kemajuan', 'lengkap']))
    <div class="{{ $tipe === 'lengkap' ? 'page-break' : '' }}">
        <div class="header-title">
            <h2 class="uppercase">LAPORAN KEMAJUAN PEKERJAAN TA {{ $tahun }}</h2>
        </div>

        <table class="header-meta">
            <tr>
                <td style="width: 60%; font-weight: bold;">OPD: {{ strtoupper($instansi?->nama_dinas ?? 'DINAS PARIWISATA PEMUDA DAN OLAHRAGA') }}</td>
                <td style="width: 40%; text-align: right; font-weight: bold;">KONDISI s.d {{ $tglKondisi }}</td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 25px;">NO</th>
                    <th rowspan="2">KEGIATAN/ PEKERJAAN</th>
                    <th rowspan="2" style="width: 85px;">PAGU (Rp.)</th>
                    <th rowspan="2" style="width: 85px;">NILAI KONTRAK/<br>PERJANJIAN (Rp.)</th>
                    <th rowspan="2" style="width: 80px;">SISA DANA (Rp.)<br><span style="font-size: 6.5pt;">(3-4)</span></th>
                    <th rowspan="2" style="width: 100px;">PENYEDIA<br>(PT/CV/..)</th>
                    <th rowspan="2" style="width: 90px;">NOMOR KONTRAK</th>
                    <th colspan="2">PELAKSANAAN</th>
                    <th colspan="3">FISIK (%)</th>
                    <th colspan="2">KEUANGAN</th>
                    <th rowspan="2" style="width: 55px;">STATUS</th>
                </tr>
                <tr>
                    <th style="width: 55px;">MULAI</th>
                    <th style="width: 55px;">SELESAI</th>
                    <th style="width: 45px;">Bobot Rencana</th>
                    <th style="width: 45px;">Bobot Realisasi</th>
                    <th style="width: 45px;">Deviasi (+/-)</th>
                    <th style="width: 80px;">Rp.</th>
                    <th style="width: 40px;">%</th>
                </tr>
                <tr style="background-color: #e5e7eb; font-size: 6.5pt;">
                    <th>1</th>
                    <th>2</th>
                    <th>3</th>
                    <th>4</th>
                    <th>5=3-4</th>
                    <th>6</th>
                    <th>7</th>
                    <th>8</th>
                    <th>9</th>
                    <th>10</th>
                    <th>11</th>
                    <th>12=11-10</th>
                    <th>13</th>
                    <th>14=13/4*100</th>
                    <th>15</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kemajuan['rows'] as $row)
                    <tr>
                        <td class="text-center">{{ $row['no'] }}</td>
                        <td>
                            <div class="font-bold">{{ $row['nama_pekerjaan'] }}</div>
                            <div style="font-size: 7pt; color: #4b5563;">{{ $row['kegiatan'] }}</div>
                        </td>
                        <td class="text-right">{{ number_format($row['pagu'], 0, ',', '.') }}</td>
                        <td class="text-right font-bold">{{ number_format($row['nilai_kontrak'], 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($row['sisa_dana'], 0, ',', '.') }}</td>
                        <td>{{ $row['penyedia'] }}</td>
                        <td style="font-size: 7pt;">{{ $row['nomor_kontrak'] }}</td>
                        <td class="text-center" style="font-size: 7pt;">{{ $row['tgl_mulai'] }}</td>
                        <td class="text-center" style="font-size: 7pt;">{{ $row['tgl_selesai'] }}</td>
                        <td class="text-center">{{ number_format($row['bobot_rencana'], 1, ',', '.') }}</td>
                        <td class="text-center font-bold">{{ number_format($row['bobot_realisasi'], 1, ',', '.') }}</td>
                        <td class="text-center font-bold {{ $row['deviasi'] < 0 ? 'text-red' : '' }}">{{ number_format($row['deviasi'], 1, ',', '.') }}</td>
                        <td class="text-right font-bold">{{ number_format($row['realisasi_keu_rp'], 0, ',', '.') }}</td>
                        <td class="text-center font-bold">{{ number_format($row['realisasi_keu_persen'], 1, ',', '.') }}%</td>
                        <td class="text-center" style="font-size: 7pt;">{{ $row['status'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15" class="text-center" style="padding: 15px;">Belum ada data belanja kontraktual/rekanan toko pada tahun {{ $tahun }}.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-total">
                    <td colspan="2" class="text-center font-bold">JUMLAH</td>
                    <td class="text-right font-bold">{{ number_format($kemajuan['summary']['total_pagu'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($kemajuan['summary']['total_kontrak'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($kemajuan['summary']['total_sisa'], 0, ',', '.') }}</td>
                    <td colspan="4" class="text-center">-</td>
                    <td colspan="3" class="text-center">-</td>
                    <td class="text-right font-bold">{{ number_format($kemajuan['summary']['total_realisasi_keu'], 0, ',', '.') }}</td>
                    <td class="text-center font-bold">
                        {{ $kemajuan['summary']['total_kontrak'] > 0 ? number_format(($kemajuan['summary']['total_realisasi_keu'] / $kemajuan['summary']['total_kontrak']) * 100, 1, ',', '.') : 0 }}%
                    </td>
                    <td class="text-center">-</td>
                </tr>
            </tfoot>
        </table>

        <div class="ttd-container">
            <div class="ttd-box">
                <div>Muaro Sijunjung, {{ $tglCetak }}</div>
                <div class="font-bold">a.n. KEPALA DINAS PARIWISATA PEMUDA DAN OLAHRAGA</div>
                <div class="font-bold">SEKRETARIS</div>
                <div style="height: 50px;"></div>
                <div class="font-bold" style="text-decoration: underline;">{{ $sekretaris?->name ?? 'DESMAWATI, SE. M.Si' }}</div>
                <div>NIP. {{ $sekretaris?->nip ?? '19691224 199503 2 002' }}</div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
    @endif

</body>
</html>

