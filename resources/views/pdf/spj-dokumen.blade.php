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
            margin-bottom: 14px;
        }
        .header-kop h2 {
            margin: 0;
            font-size: 12.5pt;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header-kop h3 {
            margin: 2px 0;
            font-size: 13.5pt;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header-kop p {
            margin: 0;
            font-size: 9pt;
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
        .kwitansi-title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            text-decoration: underline;
            letter-spacing: 1px;
            margin: 8px 0 16px 0;
        }
        .box-uang-angka {
            border: 2px solid #000;
            padding: 5px 14px;
            font-weight: bold;
            font-size: 11pt;
            display: inline-block;
            min-width: 110px;
            text-align: center;
        }
        .box-uang-terbilang {
            border: 2px solid #000;
            padding: 5px 12px;
            font-weight: bold;
            font-size: 9.5pt;
            display: inline-block;
            width: 68%;
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

    $namaDinas = $pengaturan?->nama_dinas ?? 'DINAS PARIWISATA PEMUDA DAN OLAHRAGA';
    $tahunAnggaran = date('Y', strtotime((string) $spj->tanggal_pengajuan));
    $firstDetail = $spj->details->first();
    $kodeRekeningUtama = $firstDetail?->kodeRekening?->kode_lengkap ?? '5.1.02.01.001.00024';
    $uraianRekeningUtama = $firstDetail?->kodeRekening?->uraian ?? 'Belanja Alat/Bahan untuk Kegiatan Kantor';
    $namaBarangRingkas = $spj->details->map(fn ($d) => $d->masterBarang?->nama_barang)->filter()->implode(', ');

    $namaPptk = $spj->pptk?->name ?? ($spj->bidang?->kepala?->name ?? 'DESMAWATI, SE, M.Si');
    $nipPptk = $spj->pptk?->nip ?? ($spj->bidang?->kepala?->nip ?? '19691224 199503 2 002');

    $namaPa = $spj->pa?->name ?? ($pengaturan?->nama_kepala_dinas ?? 'AFRINELDI, SH');
    $nipPa = $spj->pa?->nip ?? ($pengaturan?->kepala?->nip ?? '19720407 199202 1 002');

    $namaBendahara = $spj->bendahara?->name ?? 'WIWIRNA';
    $nipBendahara = $spj->bendahara?->nip ?? '19850723 200901 2 007';
@endphp

    {{-- ==================== HALAMAN 1: SURAT PESANAN / NOTA PESANAN ==================== --}}
    <div class="page">
        <div class="header-kop">
            <h2>PEMERINTAH KABUPATEN SIJUNJUNG</h2>
            <h3>{{ $namaDinas }}</h3>
            <p>{{ $pengaturan?->alamat_kantor ?? 'Muaro Sijunjung, Kabupaten Sijunjung' }}</p>
        </div>

        <div class="doc-title">SURAT PESANAN / NOTA PESANAN</div>
        <div class="doc-subtitle">Nomor: {{ $spj->nomor_pengajuan }}/NP/{{ $tahunAnggaran }}</div>

        <table class="meta-table">
            <tr>
                <td style="width: 24%;">Kepada Yth. Toko / Rekanan</td>
                <td style="width: 2%;">:</td>
                <td style="width: 74%;" class="font-bold">{{ $spj->nama_toko ?: '................................................' }}</td>
            </tr>
            <tr>
                <td>Nama Pemilik Toko</td>
                <td>:</td>
                <td>{{ $spj->penerima_nama ?: '................................................' }}</td>
            </tr>
            <tr>
                <td>Alamat Toko</td>
                <td>:</td>
                <td>{{ $spj->alamat_toko ?: '................................................' }}</td>
            </tr>
            <tr>
                <td>Bidang Pengusul</td>
                <td>:</td>
                <td>{{ $spj->bidang?->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td>Kegiatan</td>
                <td>:</td>
                <td>{{ $spj->kegiatan?->label ?? '-' }}</td>
            </tr>
            <tr>
                <td>Sub Kegiatan</td>
                <td>:</td>
                <td>{{ $spj->subKegiatan?->label ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tanggal Pesanan</td>
                <td>:</td>
                <td>{{ \Carbon\Carbon::parse($spj->tanggal_pengajuan)->translatedFormat('d F Y') }}</td>
            </tr>
        </table>

        <p style="font-size: 9.5pt; margin-bottom: 6px;">
            Dengan ini kami memesan barang kebutuhan kegiatan dinas dengan rincian sebagai berikut:
        </p>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 6%;">No</th>
                    <th style="width: 20%;">Kode Rekening</th>
                    <th style="width: 36%;">Nama Barang</th>
                    <th style="width: 10%;">Volume</th>
                    <th style="width: 10%;">Satuan</th>
                    <th style="width: 18%;">Jumlah (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($spj->details as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->kodeRekening?->kode_lengkap ?? '-' }}</td>
                        <td>{{ $item->masterBarang?->nama_barang ?? '-' }}</td>
                        <td class="text-center">{{ $item->kuantitas }}</td>
                        <td class="text-center">{{ $item->masterBarang?->satuan?->nama ?? 'Unit' }}</td>
                        <td class="text-right">{{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right font-bold">Jumlah Total Pesanan:</td>
                    <td class="text-right font-bold">Rp {{ number_format((float) $spj->total_nilai_rencana, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <table class="signature-table" style="margin-top: 22px;">
            <tr>
                <td style="width: 50%;">
                    Menerima Pesanan,<br>
                    <strong>{{ $spj->nama_toko ?: 'Penyedia / Toko' }}</strong>
                    <div class="signature-space"></div>
                    <strong><u>{{ $spj->penerima_nama ?: '..................................' }}</u></strong><br>
                    Pemilik / Pimpinan Toko
                </td>
                <td style="width: 50%;">
                    Muaro Sijunjung, {{ \Carbon\Carbon::parse($spj->tanggal_pengajuan)->translatedFormat('d F Y') }}<br>
                    <strong>Pejabat Pelaksana Teknis Kegiatan (PPTK)</strong>
                    <div class="signature-space">
                        @if ($ttdPptk)
                            <img src="{{ $ttdPptk }}" class="ttd-img" alt="TTD PPTK">
                        @endif
                    </div>
                    <strong><u>{{ $namaPptk }}</u></strong><br>
                    NIP. {{ $nipPptk }}
                </td>
            </tr>
        </table>
    </div>

    @if (!in_array($spj->status, ['draft', 'diajukan', 'menunggu_realisasi'], true))
    {{-- ==================== HALAMAN 2: DEPAN KWITANSI (FORMAT BAKU KAB. SIJUNJUNG) ==================== --}}
    <div class="page">
        <table style="width: 100%; font-size: 9.5pt; margin-bottom: 4px;">
            <tr>
                <td style="width: 50%;">D ......................</td>
                <td style="width: 50%; text-align: right;">
                    No. pada s.p.d<br>
                    <strong>Rek {{ $kodeRekeningUtama }}</strong>
                </td>
            </tr>
        </table>

        <div class="kwitansi-title">KWITANSI KE I</div>

        <table class="meta-table" style="font-size: 10pt;">
            <tr>
                <td style="width: 22%; font-style: italic;">Sudah terima dari :</td>
                <td style="width: 78%;">
                    Bendahara Pengeluaran {{ ucwords(strtolower($namaDinas)) }}<br>
                    Kabupaten Sijunjung Tahun Anggaran {{ $tahunAnggaran }}
                </td>
            </tr>
            <tr>
                <td style="font-style: italic; padding-top: 8px;">Uang sejumlah Rp,</td>
                <td style="padding-top: 8px;">
                    <span class="box-uang-angka">{{ number_format((float) $totalNominal, 0, ',', '.') }}</span>
                    &nbsp;
                    <span class="box-uang-terbilang">{{ ucfirst(strtolower($terbilang)) }}</span>
                </td>
            </tr>
            <tr>
                <td style="font-style: italic; padding-top: 8px;">Sebab dari :</td>
                <td style="padding-top: 8px; text-align: justify;">
                    Pembayaran Biaya Belanja {{ $namaBarangRingkas ?: $uraianRekeningUtama }}
                    @if ($spj->penerima_identitas)
                        No. Rek. {{ $spj->penerima_identitas }}
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

                    <table style="width: 100%; font-size: 9pt;">
                        <tr>
                            <td style="width: 38%;">Nama terang</td>
                            <td style="width: 4%;">:</td>
                            <td style="width: 58%;" class="font-bold">{{ $spj->penerima_nama ?: '....................' }}</td>
                        </tr>
                        <tr>
                            <td>Alamat terang</td>
                            <td>:</td>
                            <td>{{ $spj->alamat_toko ?: 'Muaro Sijunjung' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

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

    @if (!in_array($spj->status, ['draft', 'diajukan', 'menunggu_realisasi', 'menunggu_verifikasi_pptk', 'revisi_pptk'], true))
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
                            <td>3.26.2.19.0.00.01.0000 - {{ strtoupper($namaDinas) }}</td>
                        </tr>
                        <tr>
                            <td>Nama Unit SKPD</td>
                            <td>:</td>
                            <td>3.26.2.19.0.00.01.0000 - {{ strtoupper($namaDinas) }}</td>
                        </tr>
                        <tr>
                            <td>Nama PPTK</td>
                            <td>:</td>
                            <td>{{ $namaPptk }}</td>
                        </tr>
                        <tr>
                            <td>Nomor DPA</td>
                            <td>:</td>
                            <td>DPPA/A.4/3.26.2.19.0.00.01.0000/001/{{ $tahunAnggaran }}</td>
                        </tr>
                        <tr>
                            <td>Program</td>
                            <td>:</td>
                            <td>3.26.01 - PROGRAM PENUNJANG URUSAN PEMERINTAHAN DAERAH KABUPATEN/KOTA</td>
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
