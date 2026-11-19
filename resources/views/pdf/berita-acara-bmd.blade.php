<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara {{ $ba->jenis === 'peminjaman' ? 'Peminjaman Barang' : 'Penggunaan BMD' }}</title>
    <style>
        @page {
            margin: 1.5cm 2cm 1.5cm 2cm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
        }
        .kop-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .kop-logo {
            width: 72px;
            text-align: center;
            vertical-align: middle;
        }
        .kop-logo img {
            width: 65px;
            height: auto;
        }
        .kop-text {
            text-align: center;
            vertical-align: middle;
        }
        .kop-pemkab {
            font-size: 13pt;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .kop-dinas {
            font-size: 14pt;
            font-weight: bold;
        }
        .kop-alamat {
            font-size: 9.5pt;
        }
        .judul {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            text-decoration: underline;
            margin-top: 6px;
        }
        .nomor {
            text-align: center;
            font-size: 11pt;
            margin-bottom: 14px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .info-table td {
            vertical-align: top;
            padding: 1.5px 4px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 12px 0;
        }
        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 5px 7px;
            vertical-align: top;
            font-size: 10.5pt;
        }
        .items-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .ttd-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .ttd-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .ttd-space {
            height: 60px;
        }
        .mengetahui {
            margin-top: 16px;
            text-align: center;
        }
    </style>
</head>
<body>
@php
    $tanggal = $ba->tanggal;
    $hariMap = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
    ];
    $bulanMap = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $terbilang = function (int $angka) use (&$terbilang): string {
        $satuan = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        if ($angka < 12) return $satuan[$angka];
        if ($angka < 20) return $terbilang($angka - 10) . ' Belas';
        if ($angka < 100) return trim($terbilang(intdiv($angka, 10)) . ' Puluh ' . $terbilang($angka % 10));
        if ($angka < 200) return trim('Seratus ' . $terbilang($angka - 100));
        if ($angka < 1000) return trim($terbilang(intdiv($angka, 100)) . ' Ratus ' . $terbilang($angka % 100));
        if ($angka < 2000) return trim('Seribu ' . $terbilang($angka - 1000));
        return trim($terbilang(intdiv($angka, 1000)) . ' Ribu ' . $terbilang($angka % 1000));
    };
    $namaHari = $hariMap[$tanggal->format('l')] ?? $tanggal->format('l');
    $tglKata = $terbilang((int) $tanggal->format('j'));
    $namaBulan = $bulanMap[(int) $tanggal->format('n')] ?? $tanggal->format('F');
    $tahunKata = $terbilang((int) $tanggal->format('Y'));
    $tglFormatted = $tanggal->format('j') . ' ' . $namaBulan . ' ' . $tanggal->format('Y');
@endphp

<table class="kop-table">
    <tr>
        <td class="kop-logo">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo Sijunjung">
            @endif
        </td>
        <td class="kop-text">
            <div class="kop-pemkab">PEMERINTAH KABUPATEN SIJUNJUNG</div>
            <div class="kop-dinas">DINAS PARIWISATA PEMUDA DAN OLAHRAGA</div>
            <div class="kop-alamat">
                Alamat: Gedung Bersama Lantai III Komplek Pasar Inpres Muaro<br>
                Telp (0754) 20531, 20533 &nbsp;&bull;&nbsp; Muaro Sijunjung, 27511
            </div>
        </td>
    </tr>
</table>

@if($ba->jenis === 'peminjaman')
    <div class="judul">BERITA ACARA PEMINJAMAN BARANG</div>
@else
    <div class="judul">BERITA ACARA PENGGUNAAN BARANG MILIK DAERAH (BMD)</div>
@endif
<div class="nomor">Nomor : {{ $ba->nomor_surat }}</div>

@if($ba->jenis === 'peminjaman')
    <p style="margin-bottom: 8px;">
        Pada hari ini <strong>{{ $namaHari }}</strong> Tanggal <strong>{{ $tglKata }}</strong> Bulan <strong>{{ $namaBulan }}</strong> Tahun <strong>{{ $tahunKata }}</strong>, kami yang bertanda tangan di bawah ini:
    </p>
@else
    <p style="margin-bottom: 8px;">Yang bertanda tangan di bawah ini:</p>
@endif

<table class="info-table">
    <tr>
        <td colspan="3"><strong>Pihak Pertama :</strong></td>
    </tr>
    <tr>
        <td style="width: 150px; padding-left: 16px;">Nama Lengkap</td>
        <td style="width: 12px;">:</td>
        <td><strong>{{ $ba->pihak_pertama_nama }}</strong></td>
    </tr>
    @if($ba->pihak_pertama_nip)
    <tr>
        <td style="padding-left: 16px;">NIP</td>
        <td>:</td>
        <td>{{ $ba->pihak_pertama_nip }}</td>
    </tr>
    @endif
    @if($ba->pihak_pertama_pangkat)
    <tr>
        <td style="padding-left: 16px;">Pangkat / Golongan</td>
        <td>:</td>
        <td>{{ $ba->pihak_pertama_pangkat }}</td>
    </tr>
    @endif
    <tr>
        <td style="padding-left: 16px;">Jabatan</td>
        <td>:</td>
        <td>{{ $ba->pihak_pertama_jabatan ?: '-' }}</td>
    </tr>
    <tr>
        <td style="padding-left: 16px;">Nama Instansi</td>
        <td>:</td>
        <td>{{ $ba->pihak_pertama_instansi }}</td>
    </tr>
    <tr>
        <td colspan="3" style="padding-top: 6px;"><strong>Pihak Kedua :</strong></td>
    </tr>
    <tr>
        <td style="padding-left: 16px;">Nama Lengkap</td>
        <td>:</td>
        <td><strong>{{ $ba->pihak_kedua_nama }}</strong></td>
    </tr>
    @if($ba->pihak_kedua_nip)
    <tr>
        <td style="padding-left: 16px;">NIP</td>
        <td>:</td>
        <td>{{ $ba->pihak_kedua_nip }}</td>
    </tr>
    @endif
    @if($ba->pihak_kedua_pangkat)
    <tr>
        <td style="padding-left: 16px;">Pangkat / Golongan</td>
        <td>:</td>
        <td>{{ $ba->pihak_kedua_pangkat }}</td>
    </tr>
    @endif
    <tr>
        <td style="padding-left: 16px;">Jabatan</td>
        <td>:</td>
        <td>{{ $ba->pihak_kedua_jabatan ?: '-' }}</td>
    </tr>
    <tr>
        <td style="padding-left: 16px;">Nama Instansi</td>
        <td>:</td>
        <td>{{ $ba->pihak_kedua_instansi }}</td>
    </tr>
</table>

@if($ba->jenis === 'peminjaman')
    <p style="margin-bottom: 6px;">
        PIHAK PERTAMA selaku {{ $ba->pihak_pertama_jabatan ?: 'Pengurus Barang' }} menyerahkan/meminjamkan inventaris kantor berupa:
    </p>
@else
    <p style="margin-bottom: 6px;">
        Dengan ini menerangkan bahwa:<br>
        Pada hari ini, Tanggal {{ $tglKata }} bulan {{ $namaBulan }} Tahun {{ $tahunKata }}, telah dilakukan penyerahan dan penggunaan BMD, yang rinciannya sebagai berikut:
    </p>
@endif

<table class="items-table">
    <thead>
        <tr>
            <th style="width: 34px;">No.</th>
            <th>Nama Barang / Spesifikasi Barang</th>
            <th style="width: 80px;">Jumlah Barang</th>
            <th style="width: 115px;">Nilai Perolehan (Rp)</th>
            <th style="width: 115px;">Lokasi Penggunaan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ba->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}.</td>
                <td>
                    {{ $item->nama_barang }}
                    @if($item->keterangan)
                        <br><small style="color: #333;">{{ $item->keterangan }}</small>
                    @endif
                </td>
                <td class="text-center">{{ $item->jumlah }} {{ $item->satuan }}</td>
                <td class="text-right">{{ number_format((float) $item->nilai_perolehan, 0, ',', '.') }},-</td>
                <td class="text-center">{{ $item->lokasi_penggunaan ?: $ba->lokasi_penggunaan }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@if($ba->jenis === 'peminjaman')
    <p style="text-align: justify;">
        kepada PIHAK KEDUA selaku {{ $ba->pihak_kedua_jabatan ?: 'Penerima' }}. Setelah Berita Acara Peminjaman Barang ditandatangani maka PIHAK KEDUA wajib menjaga keamanan dan keselamatan aset tersebut.<br>
        Demikian Berita Acara ini dibuat dengan sesungguhnya dalam rangkap 2 (dua) untuk dipergunakan sebagaimana mestinya.
    </p>
@else
    <p style="text-align: justify; margin-bottom: 6px;">
        Pihak Pertama menyerahkan dan Pihak Kedua menerima barang tersebut dalam kondisi: <strong>{{ $ba->kondisi_barang }}</strong>.<br>
        Pihak Kedua bertanggung jawab untuk mengamankan, memanfaatkan dan menjaga barang tersebut sesuai dengan ketentuan yang berlaku.<br>
        Pihak Kedua akan mengembalikan barang tersebut kepada Pihak Pertama dalam kondisi yang sama dengan saat penyerahan (atau sesuai kesepakatan) saat penggunaan selesai.<br>
        Berita acara ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
    </p>
@endif

<div style="text-align: right; margin-top: 12px;">
    Muaro Sijunjung, {{ $tglFormatted }}
</div>

<table class="ttd-table">
    <tr>
        <td>
            PIHAK KEDUA<br>
            <div class="ttd-space"></div>
            <strong><u>{{ $ba->pihak_kedua_nama }}</u></strong><br>
            NIP. {{ $ba->pihak_kedua_nip ?: '-' }}
        </td>
        <td>
            PIHAK PERTAMA<br>
            <div class="ttd-space"></div>
            <strong><u>{{ $ba->pihak_pertama_nama }}</u></strong><br>
            NIP. {{ $ba->pihak_pertama_nip ?: '-' }}
        </td>
    </tr>
</table>

<div class="mengetahui">
    Diketahui Oleh:<br>
    <strong>KEPALA DINAS PARIWISATA<br>PEMUDA DAN OLAHRAGA</strong>
    <div class="ttd-space"></div>
    <strong><u>{{ $ba->kepala_dinas_nama }}</u></strong><br>
    NIP. {{ $ba->kepala_dinas_nip }}
</div>

</body>
</html>
