<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dokumen SPJ - {{ $spj->nomor_pengajuan }}</title>
    <style>
        @page {
            margin: 1.2cm 1.5cm;
            size: a4 portrait;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #000;
        }
        .page {
            page-break-after: always;
            position: relative;
        }
        .page:last-child {
            page-break-after: avoid;
        }

        @media screen {
            html, body {
                background-color: #525659;
                margin: 0;
                padding: 16px 8px;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .page {
                background-color: #ffffff;
                width: 210mm;
                max-width: 100%;
                min-height: 297mm;
                margin: 0 auto 20px auto;
                padding: 1.2cm 1.5cm;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
                box-sizing: border-box;
                border-radius: 2px;
                position: relative;
            }
            .kwitansi-watermark-wrapper {
                pointer-events: none !important;
                z-index: 1 !important;
            }
            .kwitansi-watermark-img {
                opacity: 0.12 !important;
            }
        }

        @media print {
            html, body {
                background-color: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .page {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                width: auto !important;
                min-height: auto !important;
            }
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .underline { text-decoration: underline; }

        /* Kop Surat Pesanan */
        .header-kop {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .kop-logo-container {
            text-align: center;
            margin-bottom: 4px;
        }
        .kop-logo {
            height: 55px;
            width: auto;
            display: inline-block;
        }
        .header-kop h2 {
            margin: 0;
            font-size: 12pt;
            text-transform: uppercase;
            font-weight: bold;
            line-height: 1.2;
        }
        .header-kop h3 {
            margin: 2px 0 3px 0;
            font-size: 13pt;
            text-transform: uppercase;
            font-weight: bold;
            line-height: 1.2;
        }
        .header-kop p {
            margin: 0;
            font-size: 8.5pt;
            line-height: 1.25;
        }

        .doc-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin-top: 6px;
            margin-bottom: 2px;
        }
        .doc-subtitle {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 14px;
        }

        table.meta-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 9.5pt;
            border-collapse: collapse;
        }
        table.meta-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 12px;
            font-size: 9.5pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 5px 6px;
        }
        table.data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        .signature-table {
            width: 100%;
            margin-top: 14px;
            border-collapse: collapse;
            font-size: 9.5pt;
        }
        .signature-table td {
            vertical-align: top;
            text-align: center;
            padding: 0 6px;
        }
        .signature-space {
            height: 60px;
        }
        .ttd-img {
            max-height: 55px;
            max-width: 145px;
            margin: 2px auto;
            display: block;
        }

        /* Styling Khusus Kwitansi Sijunjung */
        .kwitansi-watermark-wrapper {
            position: absolute;
            top: 200px;
            left: 0;
            width: 100%;
            text-align: center;
            z-index: -100;
        }
        .kwitansi-watermark-img {
            width: 410px;
            opacity: 0.18;
        }
        .kwitansi-title {
            text-align: center;
            font-size: 15pt;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 1.5px;
            margin: 8px 0 16px 0;
        }
        .box-uang-angka {
            border: 2px solid #000;
            padding: 5px 12px;
            font-weight: bold;
            font-size: 11pt;
            text-align: center;
            white-space: nowrap;
            display: block;
        }
        .box-uang-terbilang {
            border: 2px solid #000;
            padding: 5px 10px;
            font-weight: bold;
            font-size: 9.5pt;
            line-height: 1.35;
            text-align: left;
            display: block;
            width: 100%;
            box-sizing: border-box;
        }
        table.kwitansi-left-box {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }
        table.kwitansi-left-box td {
            border: 1px solid #000;
            padding: 5px 6px;
            text-align: left;
            vertical-align: top;
        }

        /* Styling Khusus NPD Sijunjung */
        .npd-outer-box {
            border: 1px solid #000;
            width: 100%;
            border-collapse: collapse;
        }
        .npd-header-cell {
            border-bottom: 1px solid #000;
            padding: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 10.5pt;
            line-height: 1.4;
        }
        .npd-section-bar {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 4px;
            text-align: center;
            font-weight: bold;
            font-size: 9.5pt;
            background-color: #fafafa;
        }
    </style>
</head>
<body>
@php
    $ttdPptk = $spj->getSpecimenTtdBase64('pptk');
    $ttdPpk = $spj->getSpecimenTtdBase64('ppk');
    $ttdPa = $spj->getSpecimenTtdBase64('pa');
    $ttdBendahara = $spj->getSpecimenTtdBase64('bendahara');

    $namaDinas = $pengaturan?->nama_dinas ?? 'DINAS PARIWISATA PEMUDA DAN OLAHRAGA KABUPATEN SIJUNJUNG';
    $alamatKantor = $pengaturan?->alamat_kantor ?? 'Jl. Prof. M. Yamin, SH, Muaro Sijunjung, Kabupaten Sijunjung';
    $teleponKantor = $pengaturan?->telepon ?? '(0754) 20054';
    $emailKantor = $pengaturan?->email ?? 'disparpora@sijunjung.go.id';
    $websiteKantor = $pengaturan?->website ?? 'parpora.sijunjung.go.id';

    $tahunAnggaran = date('Y', strtotime((string) $spj->tanggal_pengajuan));
    $firstDetail = $spj->details->first();
    $kodeRekeningUtama = $firstDetail?->kodeRekening?->kode_lengkap ?? '5.1.02.01.001.00024';
    $uraianRekeningUtama = $firstDetail?->kodeRekening?->uraian ?? 'Belanja Alat/Bahan untuk Kegiatan Kantor';
    $namaBarangRingkas = $spj->details->map(fn ($d) => $d->masterBarang?->nama_barang)->filter()->implode(', ');

    $namaPptk = $spj->pptk?->name ?? ($spj->bidang?->kepala?->name ?? 'DESMAWATI, SE, M.Si');
    $nipPptk = $spj->pptk?->nip ?? ($spj->bidang?->kepala?->nip ?? '19691224 199503 2 002');
    $jabatanPptk = 'PPTK / Kepala ' . ($spj->bidang?->nama ?? 'Bidang');

    $namaPa = $spj->pa?->name ?? ($pengaturan?->nama_kepala_dinas ?? 'AFRINELDI, SH');
    $nipPa = $spj->pa?->nip ?? ($pengaturan?->kepala?->nip ?? '19720407 199202 1 002');

    $namaBendahara = $spj->bendahara?->name ?? 'WIWIRNA';
    $nipBendahara = $spj->bendahara?->nip ?? '19850723 200901 2 007';

    $subtotalPesanan = (float) $spj->total_nilai_rencana;
    $ppnPersen = (float) ($spj->ppn_persen ?? 0);
    $nilaiPpn = round($subtotalPesanan * ($ppnPersen / 100));
    $totalSetelahPpn = $subtotalPesanan + $nilaiPpn;

    $tanggalBatasKirim = $spj->tanggal_batas_pengiriman
        ? \Carbon\Carbon::parse($spj->tanggal_batas_pengiriman)->translatedFormat('d F Y')
        : \Carbon\Carbon::parse($spj->tanggal_pengajuan)->addDays(7)->translatedFormat('d F Y');
    $metodeBayar = $spj->metode_pembayaran ?: 'Transfer Bank / Tempo 14 Hari setelah barang diterima';
    $rawBank = $spj->nama_bank ?: $spj->masterToko?->nama_bank;
    $namaBankToko = $rawBank instanceof \BackedEnum ? $rawBank->value : ($rawBank ?: null);

    $logoKabupatenPath = public_path('images/kabupaten-sijunjung.png');
    $logoKabupaten = file_exists($logoKabupatenPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoKabupatenPath))
        : null;

    $logoWatermarkPath = public_path('images/kabupaten-sijunjung-watermark.png');
    $logoWatermark = file_exists($logoWatermarkPath)
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoWatermarkPath))
        : ($logoKabupaten ?: null);
@endphp

    {{-- ==================== HALAMAN 1: SURAT PESANAN BARANG ==================== --}}
    @if (in_array($tipeDokumen ?? 'semua', ['semua', 'pesanan'], true))
    <div class="page">
        <div class="header-kop">
            @if ($logoKabupaten)
                <div class="kop-logo-container">
                    <img src="{{ $logoKabupaten }}" class="kop-logo" alt="Logo Kabupaten Sijunjung">
                </div>
            @endif
            <h2>PEMERINTAH KABUPATEN SIJUNJUNG</h2>
            <h3>{{ $namaDinas }}</h3>
            <p>{{ $alamatKantor }} | Telp: {{ $teleponKantor }} | Email: {{ $emailKantor }} | Web: {{ $websiteKantor }}</p>
        </div>

        <div class="doc-title">SURAT PESANAN BARANG</div>
        <div class="doc-subtitle" style="margin-bottom: 12px;">
            <strong>Nomor:</strong> {{ $spj->nomor_surat_pesanan }}<br>
            <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($spj->tanggal_pengajuan)->translatedFormat('d F Y') }}
        </div>

        <div style="font-size: 9.5pt; margin-bottom: 10px; line-height: 1.45;">
            Kepada Yth.<br>
            <strong>{{ $spj->nama_toko ?: '[Nama Perusahaan/Toko Pemasok]' }}</strong><br>
            {{ $spj->alamat_toko ?: '[Alamat Pemasok]' }}<br>
            Up. <strong>{{ $spj->penerima_nama ?: '[Nama Kontak Person Pemasok]' }}</strong><br>
            {{ $spj->telepon_toko ?: '[Nomor Telepon Pemasok]' }}
        </div>

        <p style="font-size: 9.5pt; margin-top: 6px; margin-bottom: 6px; text-align: justify;">
            Dengan hormat,<br>
            Merujuk pada penawaran harga yang telah disepakati sebelumnya, bersama surat ini kami bermaksud untuk memesan barang-barang dengan rincian sebagai berikut:
        </p>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No.</th>
                    <th style="width: 31%;">Nama Barang / Spesifikasi</th>
                    <th style="width: 14%;">Kode/SKU</th>
                    <th style="width: 8%;">Jumlah</th>
                    <th style="width: 10%;">Satuan</th>
                    <th style="width: 16%;">Harga Satuan (Rp)</th>
                    <th style="width: 16%;">Total Harga (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($spj->details as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}.</td>
                        <td>
                            <strong>{{ $item->masterBarang?->nama_barang ?? '-' }}</strong>
                            @if ($item->masterBarang?->spesifikasi)
                                <br><span style="font-size: 8.5pt; color: #333;">{{ $item->masterBarang->spesifikasi }}</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->masterBarang?->kode_barang ?? ($item->kodeRekening?->kode_lengkap ?? '-') }}</td>
                        <td class="text-center">{{ $item->kuantitas }}</td>
                        <td class="text-center">{{ $item->masterBarang?->satuan?->nama ?? 'Unit' }}</td>
                        <td class="text-right">{{ number_format((float) $item->harga_satuan, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right font-bold">Subtotal</td>
                    <td class="text-right font-bold">{{ number_format($subtotalPesanan, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="6" class="text-right">
                        <strong>PPN ({{ rtrim(rtrim(number_format($ppnPersen, 2, ',', '.'), '0'), ',') }}%)</strong>
                        <span style="font-size: 8.5pt; font-style: italic;">(Sesuaikan tarif pajak)</span>
                    </td>
                    <td class="text-right">{{ number_format($nilaiPpn, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="6" class="text-right font-bold" style="background-color: #f2f2f2;">TOTAL</td>
                    <td class="text-right font-bold" style="background-color: #f2f2f2;">{{ number_format($totalSetelahPpn, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <div style="font-size: 9.5pt; margin-top: 8px; margin-bottom: 10px; line-height: 1.45;">
            <strong>Ketentuan Pengiriman &amp; Pembayaran:</strong>
            <ul style="margin: 4px 0 6px 18px; padding: 0;">
                <li><strong>Lokasi Pengiriman:</strong> Barang mohon dikirimkan ke alamat kantor kami di {{ $alamatKantor }} ({{ $spj->bidang?->nama ?? $namaDinas }}).</li>
                <li><strong>Waktu Pengiriman:</strong> Paling lambat barang sudah diterima pada {{ $tanggalBatasKirim }}.</li>
                <li><strong>Metode Pembayaran:</strong> Pembayaran akan dilakukan via {{ $metodeBayar }} ke rekening resmi perusahaan Anda{{ $spj->penerima_identitas ? ' (' . ($namaBankToko ? $namaBankToko . ' — ' : '') . 'No. Rek: ' . $spj->penerima_identitas . ')' : '' }}.</li>
            </ul>
            Demikian surat pesanan ini kami sampaikan. Atas perhatian dan kerja samanya, kami ucapkan terima kasih.
        </div>

        <table class="signature-table" style="margin-top: 18px;">
            <tr>
                <td style="width: 50%;">
                    Mengetahui<br>
                    Pengguna Anggaran
                    <div class="signature-space">
                        @if ($ttdPa)
                            <img src="{{ $ttdPa }}" class="ttd-img" alt="TTD Pengguna Anggaran">
                        @endif
                    </div>
                    {{ $namaPa }}<br>
                    <span style="font-size: 8.5pt;">NIP. {{ $nipPa }}</span>
                </td>
                <td style="width: 50%;">
                    Muaro Sijunjung, {{ \Carbon\Carbon::parse($spj->tanggal_pengajuan)->translatedFormat('d F Y') }}<br>
                    PPTK
                    <div class="signature-space">
                        @if ($ttdPptk)
                            <img src="{{ $ttdPptk }}" class="ttd-img" alt="TTD PPTK">
                        @endif
                    </div>
                    {{ $namaPptk }}<br>
                    <span style="font-size: 8.5pt;">NIP. {{ $nipPptk }}</span>
                </td>
            </tr>
        </table>
    </div>
    @endif

    @if (!in_array($spj->status, ['draft', 'diajukan', 'menunggu_realisasi'], true) && in_array($tipeDokumen ?? 'semua', ['semua', 'kwitansi'], true))
    {{-- ==================== HALAMAN KWITANSI (4 RANGKAP: KWITANSI 1 S.D. 4) ==================== --}}
    @php
        $daftarKwitansi = [
            'KWITANSI 1',
            'KWITANSI 2',
            'KWITANSI 3',
            'KWITANSI 4',
        ];
    @endphp

    @foreach ($daftarKwitansi as $indexKwitansi => $judulKwitansi)
    <div class="page">
        @if ($logoWatermark)
            <div class="kwitansi-watermark-wrapper">
                <img src="{{ $logoWatermark }}" class="kwitansi-watermark-img" alt="Watermark Sijunjung">
            </div>
        @endif

        <table style="width: 100%; font-size: 9.5pt; margin-bottom: 4px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    PP.<br>
                    No. .........................................
                </td>
                <td style="width: 50%; text-align: right; vertical-align: top;">
                    No. pada s.p.d.<br>
                    <strong>Kode Rek. {{ $kodeRekeningUtama }}</strong>
                </td>
            </tr>
        </table>

        <div class="kwitansi-title">{{ $judulKwitansi }}</div>

        <table class="meta-table" style="font-size: 10pt;">
            <tr>
                <td style="width: 22%; font-style: italic;">Sudah terima dari :</td>
                <td style="width: 78%;">
                    Bendahara Pengeluaran {{ ucwords(strtolower($namaDinas)) }}<br>
                    Kabupaten Sijunjung Tahun Anggaran {{ $tahunAnggaran }}
                </td>
            </tr>
            <tr>
                <td style="font-style: italic; padding-top: 8px; vertical-align: top;">Uang sejumlah Rp,</td>
                <td style="padding-top: 8px; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; padding: 0;">
                        <tr>
                            <td style="width: 1%; white-space: nowrap; border: none; padding: 0 8px 0 0; vertical-align: top;">
                                <div class="box-uang-angka">{{ number_format((float) $totalNominal, 0, ',', '.') }}</div>
                            </td>
                            <td style="border: none; padding: 0; vertical-align: top;">
                                <div class="box-uang-terbilang">{{ ucwords(strtolower($terbilang)) }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="font-style: italic; padding-top: 8px;">Sebab dari :</td>
                <td style="padding-top: 8px; text-align: justify;">
                    Pembayaran Biaya Belanja {{ $namaBarangRingkas ?: $uraianRekeningUtama }}
                    @if ($spj->penerima_identitas)
                        No. Rek. {{ $spj->penerima_identitas }}{{ $namaBankToko ? ' (' . $namaBankToko . ')' : '' }}
                    @endif
                    an. {{ $spj->penerima_nama ?: '-' }}
                    Pada {{ $spj->nama_toko ?: '-' }} {{ $spj->alamat_toko ?: 'Muaro Sijunjung' }},
                    sesuai DPA Dinas Parpora No Rek yang tercantum diatas ( Faktur terlampir ).
                </td>
            </tr>
            <tr>
                <td style="font-style: italic; padding-top: 8px;">Untuk pekerjaan :</td>
                <td style="padding-top: 8px;">
                    Kegiatan {{ $spj->kegiatan?->nama_kegiatan ?? '-' }}<br>
                    Sub Kegiatan {{ $spj->subKegiatan?->nama_sub_kegiatan ?? '-' }}
                </td>
            </tr>
        </table>

        <table style="width: 100%; margin-top: 16px; border-collapse: collapse; font-size: 9.5pt;">
            <tr>
                {{-- KOLOM KIRI: KOTAK INVENTARIS/PEMBUKUAN --}}
                <td style="width: 34%; vertical-align: top; padding-right: 8px;">
                    <table class="kwitansi-left-box">
                        <tr>
                            <td>
                                Diterima<br>
                                Dibayar &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Tgl. {{ $spj->tanggal_dicairkan ? \Carbon\Carbon::parse($spj->tanggal_dicairkan)->format('d/m/Y') : '..............' }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Dibukukan Tgl. ............................<br>
                                No. Folio Buku Kas. {{ $spj->nomor_bku ?: '....................' }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Barang-barang yang dibeli ini telah diterima dalam keadaan baik dan telah dibukukan sebagai barang inventaris/stock dalam daftar inventaris/stock<br><br>
                                No. .............. Tgl. .....................<br>
                                Oleh ........................................
                                <div style="height: 45px;"></div>
                                <div class="text-center">Bendaharawan</div>
                            </td>
                        </tr>
                    </table>
                </td>

                {{-- KOLOM TENGAH: SETUJU DIBAYAR (KPA) & LUNAS DIBAYAR (BENDAHARA) --}}
                <td style="width: 34%; vertical-align: top; text-align: center; padding: 0 4px;">
                    <div>
                        Setuju dibayar,<br>
                        Kuasa Pengguna Anggaran
                        <div class="signature-space">
                            @if ($ttdPa)
                                <img src="{{ $ttdPa }}" class="ttd-img" alt="TTD KPA">
                            @endif
                        </div>
                        <strong><u>{{ $namaPa }}</u></strong><br>
                        NIP. {{ $nipPa }}
                    </div>

                    <div style="margin-top: 18px;">
                        Lunas dibayar,<br>
                        Bendahara pengeluaran
                        <div class="signature-space">
                            @if ($ttdBendahara)
                                <img src="{{ $ttdBendahara }}" class="ttd-img" alt="TTD Bendahara">
                            @endif
                        </div>
                        <strong><u>{{ $namaBendahara }}</u></strong><br>
                        NIP. {{ $nipBendahara }}
                    </div>
                </td>

                {{-- KOLOM KANAN: YANG TERIMA (TOKO & PEMILIK TOKO) --}}
                <td style="width: 32%; vertical-align: top; padding-left: 6px;">
                    <div>
                        Dibebankan pada<br>
                        Ma. Sijunjung, {{ \Carbon\Carbon::parse($spj->tanggal_dicairkan ?: $spj->tanggal_pengajuan)->translatedFormat('d F Y') }}
                    </div>

                    <div style="margin-top: 22px; text-align: center;">
                        Lunas<br>
                        Yang terima
                        <div class="signature-space"></div>
                    </div>

                    <table style="width: 100%; font-size: 9pt; border-collapse: collapse;">
                        <tr>
                            <td style="width: 38%; vertical-align: top; padding: 2px 0;">Nama terang</td>
                            <td style="width: 5%; vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                            <td style="width: 57%; vertical-align: top; padding: 2px 0;" class="font-bold">{{ $spj->penerima_nama ?: '....................' }}</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: top; padding: 2px 0;">Alamat terang</td>
                            <td style="vertical-align: top; padding: 2px 0; text-align: center;">:</td>
                            <td style="vertical-align: top; padding: 2px 0; line-height: 1.35;">{{ $spj->alamat_toko ?: 'Muaro Sijunjung' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    @endforeach

    {{-- ==================== HALAMAN 3: BELAKANG KWITANSI (TTD PPTK SESUAI CONTOH) ==================== --}}
    <div class="page">
        <div style="margin-top: 160px; text-align: center;">
            <div style="font-size: 11pt; font-weight: bold; margin-bottom: 5px;">PPTK</div>
            <div class="signature-space" style="height: 70px;">
                @if ($ttdPptk)
                    <img src="{{ $ttdPptk }}" class="ttd-img" alt="TTD PPTK">
                @endif
            </div>
            <div style="font-size: 11pt; font-weight: bold; text-decoration: underline;">{{ strtoupper($namaPptk) }}</div>
            <div style="font-size: 10.5pt; font-weight: bold;">NIP. {{ $nipPptk }}</div>
        </div>
    </div>
    @endif

    @if (!in_array($spj->status, ['draft', 'diajukan', 'menunggu_realisasi', 'menunggu_verifikasi_pptk', 'revisi_pptk'], true) && in_array($tipeDokumen ?? 'semua', ['semua', 'npd'], true))
    {{-- ==================== HALAMAN 4: NOTA PENCAIRAN DANA / NPD (FORMAT BAKU KAB. SIJUNJUNG) ==================== --}}
    <div class="page">
        <table class="npd-outer-box">
            <tr>
                <td class="npd-header-cell">
                    KAB. SIJUNJUNG<br>
                    NOTA PENCAIRAN DANA (NPD)<br>
                    TAHUN ANGGARAN {{ $tahunAnggaran }}
                </td>
            </tr>
            <tr>
                <td style="padding: 10px 14px;">
                    <table class="meta-table" style="font-size: 9.5pt; margin-bottom: 4px;">
                        <tr>
                            <td style="width: 22%;">Nomor</td>
                            <td style="width: 2%;">:</td>
                            <td style="width: 76%;">{{ $spj->nomor_pengajuan }}/NPD-NONPANJAR/3.26.2.19.0.00.01.0000/{{ $tahunAnggaran }}</td>
                        </tr>
                        <tr>
                            <td>Tanggal</td>
                            <td>:</td>
                            <td>{{ \Carbon\Carbon::parse($spj->tanggal_pengajuan)->translatedFormat('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td>Jenis NPD</td>
                            <td>:</td>
                            <td>Non Panjar</td>
                        </tr>
                        <tr>
                            <td>Nama SKPD</td>
                            <td>:</td>
                            <td>{{ $pengaturan?->kode_instansi ?? '3.26.2.19.0.00.01.0000' }} - {{ strtoupper($namaDinas) }}</td>
                        </tr>
                        <tr>
                            <td>Unit Organisasi</td>
                            <td>:</td>
                            <td>{{ $pengaturan?->kode_instansi ?? '3.26.2.19.0.00.01.0000' }} {{ strtoupper($namaDinas) }}</td>
                        </tr>
                        <tr>
                            <td>Sub Unit Organisasi</td>
                            <td>:</td>
                            <td>{{ $pengaturan?->sub_unit_organisasi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td>Nama PPTK</td>
                            <td>:</td>
                            <td>{{ $namaPptk }}</td>
                        </tr>
                        <tr>
                            <td>Nomor DPA</td>
                            <td>:</td>
                            <td>DPPA/A.4/{{ $pengaturan?->kode_instansi ?? '3.26.2.19.0.00.01.0000' }}/001/{{ $tahunAnggaran }}</td>
                        </tr>
                        <tr>
                            <td>Program</td>
                            <td>:</td>
                            <td>{{ $spj->kegiatan?->program ? ($spj->kegiatan->program->kode_program . ' ' . $spj->kegiatan->program->nama_program) : ($spj->kegiatan?->nama_kegiatan ?? '-') }}</td>
                        </tr>
                        <tr>
                            <td>Kegiatan</td>
                            <td>:</td>
                            <td>{{ $spj->kegiatan?->kode_kegiatan }} - {{ $spj->kegiatan?->nama_kegiatan }}</td>
                        </tr>
                        <tr>
                            <td>Sub Kegiatan</td>
                            <td>:</td>
                            <td>{{ $spj->subKegiatan?->kode_sub_kegiatan }} - {{ $spj->subKegiatan?->nama_sub_kegiatan }}</td>
                        </tr>
                        <tr>
                            <td>Keterangan</td>
                            <td>:</td>
                            <td>Pembayaran belanja {{ $namaBarangRingkas }} Pada {{ $spj->nama_toko ?: '-' }} an. {{ $spj->penerima_nama ?: '-' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td class="npd-section-bar">RINCIAN BELANJA</td>
            </tr>
            <tr>
                <td style="padding: 0;">
                    <table class="data-table" style="margin: 0; border: none;">
                        <thead>
                            <tr>
                                <th style="width: 5%; border-left: none;">No</th>
                                <th style="width: 19%;">Kode Rekening</th>
                                <th style="width: 34%;">Uraian</th>
                                <th style="width: 14%;">Anggaran</th>
                                <th style="width: 14%;">Sisa Anggaran</th>
                                <th style="width: 14%; border-right: none;">Rencana Penarikan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($spj->details as $idx => $d)
                                @php
                                    $anggaranItem = (float) $d->subtotal;
                                    $realisasiItem = (float) ($d->subtotal_realisasi ?: $d->subtotal);
                                @endphp
                                <tr>
                                    <td class="text-center" style="border-left: none;">{{ $idx + 1 }}</td>
                                    <td>{{ $d->kodeRekening?->kode_lengkap ?? '-' }}</td>
                                    <td>{{ $d->kodeRekening?->uraian ?? '-' }} — {{ $d->masterBarang?->nama_barang ?? '-' }}</td>
                                    <td class="text-right">Rp{{ number_format($anggaranItem, 2, ',', '.') }}</td>
                                    <td class="text-right">Rp{{ number_format($anggaranItem, 2, ',', '.') }}</td>
                                    <td class="text-right" style="border-right: none;">Rp{{ number_format($realisasiItem, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-right font-bold" style="border-left: none;">Jumlah</td>
                                <td class="text-right font-bold">Rp{{ number_format((float) $spj->total_nilai_rencana, 2, ',', '.') }}</td>
                                <td class="text-right font-bold">Rp{{ number_format((float) $spj->total_nilai_rencana, 2, ',', '.') }}</td>
                                <td class="text-right font-bold" style="border-right: none;">Rp{{ number_format((float) $totalNominal, 2, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="padding: 20px 10px 25px 10px;">
                    <table class="signature-table" style="margin-top: 0;">
                        <tr>
                            <td style="width: 50%;">
                                Disetujui Oleh,<br>
                                <strong>PENGGUNA ANGGARAN/KUASA PENGGUNA ANGGARAN</strong>
                                <div class="signature-space" style="height: 65px;">
                                    @if ($ttdPa)
                                        <img src="{{ $ttdPa }}" class="ttd-img" alt="TTD PA/KPA">
                                    @endif
                                </div>
                                <strong><u>{{ $namaPa }}</u></strong><br>
                                {{ str_replace(' ', '', $nipPa) }}
                            </td>
                            <td style="width: 50%;">
                                Disiapkan Oleh,<br>
                                <strong>Pejabat Pelaksana Teknis Kegiatan</strong>
                                <div class="signature-space" style="height: 65px;">
                                    @if ($ttdPptk)
                                        <img src="{{ $ttdPptk }}" class="ttd-img" alt="TTD PPTK">
                                    @endif
                                </div>
                                <strong><u>{{ $namaPptk }}</u></strong><br>
                                {{ str_replace(' ', '', $nipPptk) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    @endif

</body>
</html>
