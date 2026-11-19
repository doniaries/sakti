<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Inventaris Gabungan</title>
    <style>
        @page {
            margin: 1cm;
            size: 935.43pt 609.45pt landscape;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7pt;
            line-height: 1.15;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header-table, .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .header-table td, .meta-table td { border: none; padding: 1px 0; }
        .title-main { font-size: 11pt; font-weight: bold; text-align: center; }
        .title-sub { font-size: 11pt; font-weight: bold; text-align: center; margin-bottom: 5px; }

        .content-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.5pt;
            table-layout: fixed;
            page-break-after: always;
        }
        .content-table:last-of-type {
            page-break-after: auto;
        }
        .content-table th, .content-table td {
            border: 0.5pt solid #000;
            padding: 2.5px 2px;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .content-table thead th {
            text-align: center;
            font-weight: bold;
            background-color: #f7f7f7;
        }
        .col-num th {
            background-color: #f0f0f0;
            font-size: 6pt;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }

        .badge-kondisi {
            display: inline-block;
            padding: 1px 4px;
            font-size: 6pt;
            font-weight: bold;
            border-radius: 2px;
        }
        .badge-baik { background-color: #dcfce7; color: #166534; }
        .badge-kb { background-color: #ffedd5; color: #9a3412; }
        .badge-rb { background-color: #fee2e2; color: #991b1b; }

        .legend-box {
            margin-top: 10px;
            border: 0.5pt solid #ccc;
            padding: 5px 8px;
            font-size: 6pt;
            page-break-inside: avoid;
        }
        .signature-table {
            width: 100%;
            margin-top: 15px;
            font-size: 7.5pt;
            page-break-inside: avoid;
        }
        .signature-table td { border: none; text-align: center; }
        .spacer { height: 45px; }
    </style>
</head>
<body>

@php
    $chunks = $items->chunk(25);
@endphp

@foreach($chunks as $pageIndex => $chunk)
    @if($loop->first)
        <div class="title-main">PEMERINTAH KABUPATEN SIJUNJUNG</div>
        <div class="title-sub">BUKU INVENTARIS GABUNGAN</div>
        <table class="meta-table">
            <tr>
                <td style="width: 140px;">Provinsi / Kab./Kota</td>
                <td>: {{ $provinsi }} / {{ $kabupaten }}</td>
                <td style="width: 120px;">Unit Organisasi</td>
                <td>: {{ $unitOrganisasi }}</td>
            </tr>
            <tr>
                <td>Bidang / UPB</td>
                <td>: {{ $bidang }} / {{ $upb }}</td>
                <td>Halaman</td>
                <td>: {{ $pageIndex + 1 }} dari {{ $chunks->count() }}</td>
            </tr>
        </table>
    @else
        <div style="font-size: 6.5pt; margin-bottom: 4px; font-weight: bold;">
            BUKU INVENTARIS GABUNGAN — {{ $unitOrganisasi }} (Halaman {{ $pageIndex + 1 }} dari {{ $chunks->count() }})
        </div>
    @endif

    <table class="content-table">
        <thead>
            <tr>
                <th colspan="3" style="width: 17%;">NOMOR</th>
                <th colspan="3" style="width: 35%;">SPESIFIKASI BARANG</th>
                <th rowspan="3" style="width: 5%;">Bahan</th>
                <th rowspan="3" style="width: 6%;">Asal Usul</th>
                <th rowspan="3" style="width: 4%;">Tahun</th>
                <th rowspan="3" style="width: 5%;">Ukuran (P/S/D)</th>
                <th rowspan="3" style="width: 4%;">Satuan</th>
                <th rowspan="3" style="width: 5%;">Kondisi</th>
                <th colspan="2" style="width: 11%;">JUMLAH</th>
                <th rowspan="3" style="width: 8%;">Keterangan</th>
            </tr>
            <tr>
                <th rowspan="2" style="width: 3%;">Urut</th>
                <th style="width: 8%;">Kode Barang</th>
                <th rowspan="2" style="width: 6%;">Register</th>
                <th rowspan="2" style="width: 15%;">Nama / Jenis Barang</th>
                <th rowspan="2" style="width: 9%;">Merk / Type</th>
                <th rowspan="2" style="width: 11%;">No. Sertifikat/Pabrik/Mesin</th>
                <th rowspan="2" style="width: 3%;">Barang</th>
                <th rowspan="2" style="width: 8%;">Harga</th>
            </tr>
            <tr><th>1.3.</th></tr>
            <tr class="col-num">
                <th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th>6</th><th>7</th><th>8</th><th>9</th><th>10</th><th>11</th><th>12</th><th>13</th><th>14</th><th>15</th>
            </tr>
        </thead>
        <tbody>
            @foreach($chunk as $item)
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
                    <td class="text-center">
                        @php
                            $kb = trim((string) ($item->keadaan_barang ?? 'Baik'));
                            $lowerKb = strtolower($kb);
                        @endphp
                        @if(in_array($lowerKb, ['baik', 'b']))
                            <span class="badge-kondisi badge-baik">Baik</span>
                        @elseif(in_array($lowerKb, ['kurang baik', 'kb']))
                            <span class="badge-kondisi badge-kb">Kurang Baik</span>
                        @elseif(in_array($lowerKb, ['rusak berat', 'rb']))
                            <span class="badge-kondisi badge-rb">Rusak Berat</span>
                        @else
                            <span>{{ $kb }}</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->jumlah_barang }}</td>
                    <td class="text-right font-bold">{{ number_format((float) $item->harga, 2, ',', '.') }}</td>
                    <td class="text-left">{{ $item->keterangan ?: '-' }}</td>
                </tr>
            @endforeach
        </tbody>
        @if($loop->last)
            <tfoot>
                <tr style="font-weight: bold; background-color: #f2f2f2;">
                    <td colspan="12" class="text-center">Total</td>
                    <td class="text-center">{{ $totalBarang }}</td>
                    <td class="text-right">{{ number_format($totalHarga, 2, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
@endforeach

<div class="legend-box">
    <strong>Arti Kode Ukuran Barang / Konstruksi (P, S, D) Kolom 10:</strong>
    P = Permanen (konstruksi kokoh dan tetap), S/SP = Semi Permanen (gabungan permanen & nonpermanen), D = Darurat (sementara/sederhana).
    &bull; <strong>Kondisi Kolom 12:</strong> Baik (B), Kurang Baik (KB), Rusak Berat (RB).
</div>

<table class="signature-table">
    <tr>
        <td style="width: 45%;">
            <div class="font-bold">MENGETAHUI</div>
            <div class="font-bold">{{ $jabatanKadis }}</div>
            <div class="spacer"></div>
            <div class="font-bold" style="text-decoration: underline;">{{ $namaKadis }}</div>
            <div>NIP. {{ $nipKadis }}</div>
        </td>
        <td style="width: 10%;"></td>
        <td style="width: 45%;">
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
