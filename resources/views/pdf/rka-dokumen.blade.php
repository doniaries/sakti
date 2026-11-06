<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak RKA - {{ $subKegiatan?->nama_sub_kegiatan ?? 'Rincian Belanja' }} (Tahun {{ $tahun }})</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 15mm 15mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
        }

        .no-print-bar {
            background-color: #1e293b;
            color: #fff;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #f59e0b;
            color: #111827;
        }

        .btn-primary:hover {
            background-color: #d97706;
        }

        .btn-secondary {
            background-color: #475569;
            color: #fff;
        }

        .btn-secondary:hover {
            background-color: #334155;
        }

        .page-container {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: #fff;
            padding: 15mm 15mm 20mm 15mm;
            box-sizing: border-box;
            box-shadow: 0 0 15px rgba(0,0,0,0.08);
        }

        .header-table, .meta-table, .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-box {
            border: 1.5px solid #000;
            margin-bottom: 8px;
        }

        .header-box td {
            padding: 6px 10px;
            text-align: center;
            font-weight: bold;
        }

        .header-title {
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        .header-subtitle {
            font-size: 11px;
            margin-top: 4px;
        }

        .meta-table td {
            padding: 2.5px 4px;
            vertical-align: top;
            font-size: 10.5px;
        }

        .meta-label {
            width: 160px;
            font-weight: bold;
        }

        .data-table {
            margin-top: 10px;
            border: 1px solid #000;
        }

        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 4.5px 6px;
            font-size: 10px;
        }

        .data-table th {
            background-color: #f8fafc;
            text-align: center;
            font-weight: bold;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        .group-header {
            background-color: #f1f5f9;
            font-weight: bold;
        }

        .subgroup-header {
            padding-left: 14px !important;
            font-weight: bold;
        }

        .item-row td:nth-child(2) {
            padding-left: 24px;
        }

        .signature-section {
            margin-top: 30px;
            width: 100%;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 250px;
            float: right;
            text-align: center;
            font-size: 10.5px;
        }

        .footer-note {
            margin-top: 40px;
            font-size: 9px;
            color: #64748b;
            border-top: 1px dashed #cbd5e1;
            padding-top: 6px;
        }

        @media print {
            body {
                background: none;
            }
            .no-print-bar {
                display: none !important;
            }
            .page-container {
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

    <!-- Bar Navigasi Preview (Tidak ikut tercetak) -->
    <div class="no-print-bar">
        <div style="font-size: 13px; font-weight: bold;">
            📄 PREVIEW DOKUMEN RKA SKPD (SIPD-RI TAHUN {{ $tahun }})
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" class="btn btn-primary">
                🖨️ Cetak / Simpan PDF (Print)
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                ❌ Tutup
            </button>
        </div>
    </div>

    <!-- Halaman Dokumen A4 -->
    <div class="page-container">
        
        <!-- Kop Formulir RKA SKPD -->
        <div class="header-box">
            <table class="header-table">
                <tr>
                    <td style="width: 75%; border-right: 1.5px solid #000;">
                        <div class="header-title">RENCANA KERJA DAN ANGGARAN<br>SATUAN KERJA PERANGKAT DAERAH</div>
                        <div class="header-subtitle">{{ strtoupper($instansi?->nama_instansi ?? 'Pemerintah Kabupaten Sijunjung') }}<br>Tahun Anggaran {{ $tahun }}</div>
                    </td>
                    <td style="width: 25%;">
                        <div style="font-size: 11px; font-weight: bold;">Formulir<br>RKA-BELANJA<br>SKPD</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Identitas Urusan, Organisasi, Program, Kegiatan -->
        <table class="meta-table" style="margin-bottom: 8px;">
            <tr>
                <td class="meta-label">Urusan Pemerintahan</td>
                <td style="width: 10px;">:</td>
                <td>3 URUSAN PEMERINTAHAN PILIHAN</td>
            </tr>
            <tr>
                <td class="meta-label">Bidang Urusan</td>
                <td>:</td>
                <td>3.26 URUSAN PEMERINTAHAN BIDANG PARIWISATA</td>
            </tr>
            <tr>
                <td class="meta-label">Unit Organisasi</td>
                <td>:</td>
                <td>{{ $instansi?->kode_skpd ?? '3.26.2.19.0.00.01.0000' }} {{ strtoupper($instansi?->nama_instansi ?? 'DINAS PARIWISATA, PEMUDA DAN OLAH RAGA') }}</td>
            </tr>
            <tr>
                <td class="meta-label">Program</td>
                <td>:</td>
                <td>{{ $subKegiatan?->kegiatan?->program?->kode_program ?? '3.26.03' }} {{ strtoupper($subKegiatan?->kegiatan?->program?->nama_program ?? 'PROGRAM PEMASARAN PARIWISATA') }}</td>
            </tr>
            <tr>
                <td class="meta-label">Kegiatan</td>
                <td>:</td>
                <td>{{ $subKegiatan?->kegiatan?->kode_kegiatan ?? '3.26.03.2.01' }} {{ strtoupper($subKegiatan?->kegiatan?->nama_kegiatan ?? 'Pemasaran Pariwisata Dalam dan Luar Negeri Daya Tarik, Destinasi dan Kawasan Strategis Pariwisata Kabupaten/Kota') }}</td>
            </tr>
            <tr>
                <td class="meta-label">Sub Kegiatan</td>
                <td>:</td>
                <td><strong>{{ $subKegiatan?->kode_sub_kegiatan ?? '3.26.03.2.01.0006' }} {{ strtoupper($subKegiatan?->nama_sub_kegiatan ?? 'Fasilitasi Kegiatan Pemasaran Pariwisata Baik Dalam dan Luar Negeri Pariwisata Kabupaten/Kota') }}</strong></td>
            </tr>
            <tr>
                <td class="meta-label">Sumber Pendanaan</td>
                <td>:</td>
                <td>Dana Alokasi Umum (DAU)</td>
            </tr>
            <tr>
                <td class="meta-label">Lokasi</td>
                <td>:</td>
                <td>Kab. Sijunjung, Semua Kecamatan, Semua Kel/Desa</td>
            </tr>
            <tr>
                <td class="meta-label">Waktu Pelaksanaan</td>
                <td>:</td>
                <td>Januari s.d Desember {{ $tahun }}</td>
            </tr>
        </table>

        <!-- Tabel Rincian Anggaran Belanja Kegiatan -->
        <table class="data-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 140px;">Kode Rekening</th>
                    <th rowspan="2">Uraian</th>
                    <th colspan="4">Rinci Perhitungan</th>
                    <th rowspan="2" style="width: 105px;">Jumlah (Rp)</th>
                </tr>
                <tr>
                    <th style="width: 45px;">Koef.</th>
                    <th style="width: 50px;">Satuan</th>
                    <th style="width: 75px;">Harga</th>
                    <th style="width: 35px;">PPN</th>
                </tr>
            </thead>
            <tbody>
                <!-- Header Rekening Induk Belanja Daerah -->
                <tr class="group-header">
                    <td><strong>5</strong></td>
                    <td colspan="5"><strong>BELANJA DAERAH</strong></td>
                    <td class="text-right font-bold">Rp {{ number_format($totalPagu, 0, ',', '.') }},00</td>
                </tr>
                <tr class="group-header">
                    <td><strong>5.1</strong></td>
                    <td colspan="5"><strong>BELANJA OPERASI</strong></td>
                    <td class="text-right font-bold">Rp {{ number_format($totalPagu, 0, ',', '.') }},00</td>
                </tr>
                <tr class="group-header">
                    <td><strong>5.1.02</strong></td>
                    <td colspan="5"><strong>Belanja Barang dan Jasa</strong></td>
                    <td class="text-right font-bold">Rp {{ number_format($totalPagu, 0, ',', '.') }},00</td>
                </tr>

                @foreach($groupedItems as $kodeRekening => $group)
                    @php
                        $first = $group->first();
                        $subtotalRekening = $group->sum('pagu_total');
                    @endphp
                    <!-- Header Kode Rekening Akun -->
                    <tr style="background-color: #f8fafc; font-weight: bold;">
                        <td>{{ $kodeRekening }}</td>
                        <td colspan="5">{{ $first->kodeRekening?->uraian ?? 'Belanja' }}</td>
                        <td class="text-right">Rp {{ number_format($subtotalRekening, 0, ',', '.') }},00</td>
                    </tr>

                    @foreach($group as $item)
                        <!-- Baris Rincian Objek Belanja -->
                        <tr class="item-row">
                            <td></td>
                            <td>
                                <div><strong>{{ $item->masterBarang?->nama_barang ?: $item->nama_item }}</strong></div>
                                @if($item->spesifikasi)
                                    <div style="font-size: 9px; color: #475569; margin-top: 1px;">Spesifikasi: {{ $item->spesifikasi }}</div>
                                @endif
                            </td>
                            <td class="text-center">{{ (float) $item->volume }}</td>
                            <td class="text-center">{{ $item->satuan }}</td>
                            <td class="text-right">{{ number_format((float) $item->harga_satuan, 0, ',', '.') }}</td>
                            <td class="text-center">0%</td>
                            <td class="text-right">Rp {{ number_format((float) $item->pagu_total, 0, ',', '.') }},00</td>
                        </tr>
                    @endforeach
                @endforeach

                <!-- Baris Total Akhir -->
                <tr style="background-color: #e2e8f0; font-weight: bold; font-size: 11px;">
                    <td colspan="6" class="text-right" style="padding: 7px 10px;"><strong>JUMLAH ALOKASI ANGGARAN (DPA/RKA) :</strong></td>
                    <td class="text-right" style="padding: 7px 10px;"><strong>Rp {{ number_format($totalPagu, 0, ',', '.') }},00</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Bagian Pengesahan Tanda Tangan -->
        <div class="signature-section">
            <div class="signature-box">
                <div>Muaro Sijunjung, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                <div style="font-weight: bold; margin-top: 4px;">Kepala {{ $instansi?->nama_instansi ?? 'Dinas Pariwisata, Pemuda dan Olah Raga' }}</div>
                <div style="height: 65px;"></div>
                <div style="font-weight: bold; text-decoration: underline;">{{ $instansi?->nama_kadis ?? 'AFRINELDI, SH' }}</div>
                <div>NIP. {{ $instansi?->nip_kadis ?? '19720407 199202 1 002' }}</div>
            </div>
            <div style="clear: both;"></div>
        </div>

        <div class="footer-note">
            Sistem Informasi Pemerintahan Daerah (SIPD-RI) Sinkronisasi SAKTI Kab. Sijunjung | Dicetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i:s') }} WIB
        </div>

    </div>

</body>
</html>

