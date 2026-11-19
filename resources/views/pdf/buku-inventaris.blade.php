<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Inventaris Gabungan - Dinas Pariwisata, Pemuda dan Olah Raga</title>
    <style>
        @page {
            margin: 1.2cm 1.2cm 1.2cm 1.2cm;
            size: 935.43pt 609.45pt landscape; /* F4 / Folio Landscape */
        }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            font-size: 7.5pt;
            line-height: 1.2;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .header-table td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }
        .logo-cell {
            width: 65px;
            text-align: left;
        }
        .logo-img {
            height: 60px;
            width: auto;
        }
        .title-cell {
            text-align: center;
        }
        .title-main {
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .title-sub {
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8pt;
        }
        .meta-table td {
            border: none;
            padding: 1.5px 0;
            vertical-align: top;
        }
        .meta-label {
            width: 160px;
        }
        .meta-sep {
            width: 15px;
            text-align: center;
        }
        .meta-val {
            font-weight: normal;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7pt;
            table-layout: fixed;
        }
        .content-table th, 
        .content-table td {
            border: 0.5pt solid #000;
            padding: 3px 2px;
            vertical-align: middle;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .content-table thead th {
            text-align: center;
            font-weight: bold;
            background-color: #f7f7f7;
        }
        .col-num {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            font-size: 6.5pt;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .nowrap { white-space: nowrap; }

        .total-row td {
            font-weight: bold;
            background-color: #f2f2f2;
            padding: 4px 2px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            font-size: 8pt;
            page-break-inside: avoid;
        }
        .signature-table td {
            border: none;
            vertical-align: top;
            padding: 0;
        }
        .signature-col-left {
            width: 45%;
            text-align: center;
        }
        .signature-col-mid {
            width: 10%;
        }
        .signature-col-right {
            width: 45%;
            text-align: center;
        }
        .spacer {
            height: 60px;
        }
    </style>
</head>
<body>

    <!-- Header Instansi -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if(!empty($logoBase64))
                    <img src="data:image/png;base64,{{ $logoBase64 }}" class="logo-img" alt="Logo">
                @endif
            </td>
            <td class="title-cell">
                <div class="title-main">PEMERINTAH KABUPATEN SIJUNJUNG</div>
                <div class="title-sub">BUKU INVENTARIS GABUNGAN</div>
            </td>
            <td style="width: 65px;"></td>
        </tr>
    </table>

    <!-- Metadata OPD -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Provinsi</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $provinsi }}</td>
        </tr>
        <tr>
            <td class="meta-label">Kab./Kota</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $kabupaten }}</td>
        </tr>
        <tr>
            <td class="meta-label">Bidang</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $bidang }}</td>
        </tr>
        <tr>
            <td class="meta-label">Unit Organisasi</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $unitOrganisasi }}</td>
        </tr>
        <tr>
            <td class="meta-label">Sub Unit Organisasi</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $subUnitOrganisasi }}</td>
        </tr>
        <tr>
            <td class="meta-label">U P B</td>
            <td class="meta-sep">:</td>
            <td class="meta-val">{{ $upb }}</td>
        </tr>
    </table>

    <!-- Tabel 15 Kolom Inventaris -->
    <table class="content-table">
        <thead>
            <tr>
                <th colspan="3" style="width: 17%;">NOMOR</th>
                <th colspan="3" style="width: 35%;">SPESIFIKASI BARANG</th>
                <th rowspan="3" style="width: 5%;">Bahan</th>
                <th rowspan="3" style="width: 6%;">Asal/Cara<br>Perolehan<br>Barang</th>
                <th rowspan="3" style="width: 4%;">Tahun<br>Pem-<br>belian</th>
                <th rowspan="3" style="width: 5%;">Ukuran<br>Barang/<br>Konstruksi<br>(P, S, D)</th>
                <th rowspan="3" style="width: 4%;">Satuan</th>
                <th rowspan="3" style="width: 5%;">Keadaan<br>Barang<br>(B/KB/RB)</th>
                <th colspan="2" style="width: 11%;">JUMLAH</th>
                <th rowspan="3" style="width: 8%;">Keterangan</th>
            </tr>
            <tr>
                <th rowspan="2" style="width: 3%;">Urut</th>
                <th style="width: 8%;">Kode Barang</th>
                <th rowspan="2" style="width: 6%;">Register</th>
                <th rowspan="2" style="width: 15%;">Nama / Jenis Barang</th>
                <th rowspan="2" style="width: 9%;">Merk /<br>Type</th>
                <th rowspan="2" style="width: 11%;">No. Sertifikat<br>No. Pabrik<br>No. Chasis<br>No. Mesin</th>
                <th rowspan="2" style="width: 3%;">Barang</th>
                <th rowspan="2" style="width: 8%;">Harga</th>
            </tr>
            <tr>
                <th>1.3.</th>
            </tr>
            <tr class="col-num">
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
                <th>13</th>
                <th>14</th>
                <th>15</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    <td class="text-center">{{ $item->nomor_urut }}</td>
                    <td class="text-center">{{ $item->kode_barang }}</td>
                    <td class="text-center">{{ $item->register ?: '-' }}</td>
                    <td class="text-left font-bold">{{ $item->nama_barang }}</td>
                    <td class="text-center">{{ $item->merk_type ?: '-' }}</td>
                    <td class="text-center">{{ $item->nomor_sertifikat_pabrik_chasis_mesin ?: '-' }}</td>
                    <td class="text-center">{{ $item->bahan ?: '-' }}</td>
                    <td class="text-center">{{ $item->asal_perolehan ?: '-' }}</td>
                    <td class="text-center">{{ $item->tahun_pembelian ?: '-' }}</td>
                    <td class="text-center">{{ $item->ukuran_konstruksi ?: '-' }}</td>
                    <td class="text-center">{{ $item->satuan ?: '-' }}</td>
                    <td class="text-center">{{ $item->keadaan_barang ?: 'Baik' }}</td>
                    <td class="text-center">{{ $item->jumlah_barang }}</td>
                    <td class="text-right font-bold">{{ number_format((float) $item->harga, 2, ',', '.') }}</td>
                    <td class="text-left">{{ $item->keterangan ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="15" class="text-center" style="padding: 15px;">Tidak ada data inventaris yang ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="12" class="text-center">Total</td>
                <td class="text-center">{{ $totalBarang }}</td>
                <td class="text-right">{{ number_format($totalHarga, 2, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <!-- Tanda Tangan Resmi -->
    <table class="signature-table">
        <tr>
            <td class="signature-col-left">
                <div class="font-bold">MENGETAHUI</div>
                <div class="font-bold">{{ $jabatanKadis }}</div>
                <div class="spacer"></div>
                <div class="font-bold" style="text-decoration: underline;">{{ $namaKadis }}</div>
                <div>NIP. {{ $nipKadis }}</div>
            </td>
            <td class="signature-col-mid"></td>
            <td class="signature-col-right">
                <div>Muaro Sijunjung, {{ $tanggalCetak }}</div>
                <div class="font-bold">{{ $jabatanPengurus }}</div>
                <div class="spacer"></div>
                <div class="font-bold" style="text-decoration: underline;">{{ $namaPengurus }}</div>
                <div>NIP. {{ $nipPengurus }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
