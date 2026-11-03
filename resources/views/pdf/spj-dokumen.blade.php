<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dokumen SPJ - {{ $spj->nomor_pengajuan }}</title>
    <style>
        @page {
            margin: 1.5cm 2cm;
            size: a4 portrait;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5pt;
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
        .header-kop {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .header-kop h2 {
            margin: 0;
            font-size: 13pt;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header-kop h3 {
            margin: 2px 0;
            font-size: 12pt;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header-kop p {
            margin: 0;
            font-size: 9pt;
        }
        .doc-title {
            text-align: center;
            font-size: 12.5pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin-top: 8px;
            margin-bottom: 3px;
        }
        .doc-subtitle {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 14px;
        }
        table.meta-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 10pt;
        }
        table.meta-table td {
            padding: 2.5px 0;
            vertical-align: top;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 14px;
            font-size: 9.5pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 5px 7px;
        }
        table.data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        .box-terbilang {
            border: 1px solid #000;
            padding: 7px 12px;
            background-color: #fafafa;
            font-style: italic;
            margin-bottom: 14px;
            font-size: 10pt;
        }
        .signature-table {
            width: 100%;
            margin-top: 18px;
            border-collapse: collapse;
            font-size: 9.5pt;
        }
        .signature-table td {
            vertical-align: top;
            text-align: center;
            padding: 0 8px;
        }
        .signature-space {
            height: 62px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .ttd-img {
            max-height: 56px;
            max-width: 150px;
            margin: 3px auto;
            display: block;
        }
    </style>
</head>
<body>
@php
    $ttdPptk = $spj->getSpecimenTtdBase64('pptk');
    $ttdPpk = $spj->getSpecimenTtdBase64('ppk');
    $ttdPa = $spj->getSpecimenTtdBase64('pa');
    $ttdBendahara = $spj->getSpecimenTtdBase64('bendahara');
@endphp

    {{-- ==================== HALAMAN 1: SURAT PESANAN / NOTA PESANAN ==================== --}}
    <div class="page">
        <div class="header-kop">
            <h2>PEMERINTAH DAERAH PROVINSI / KABUPATEN</h2>
            <h3>{{ $pengaturan?->nama_dinas ?? 'DINAS PARIWISATA, PEMUDA DAN OLAHRAGA' }}</h3>
            <p>{{ $pengaturan?->alamat_kantor ?? 'Alamat Kantor Dinas - Telp/Fax. Resmi' }}</p>
        </div>

        <div class="doc-title">SURAT PESANAN / NOTA PESANAN BARANG</div>
        <div class="doc-subtitle">Nomor: {{ $spj->nomor_pengajuan }}/NP/{{ date('Y') }}</div>

        <table class="meta-table">
            <tr>
                <td style="width: 24%;">Kepada Yth. Toko / Rekanan</td>
                <td style="width: 2%;">:</td>
                <td style="width: 74%;" class="font-bold">{{ $spj->nama_toko ?: '................................................' }}</td>
            </tr>
            <tr>
                <td>Nama Pemilik / Pimpinan</td>
                <td>:</td>
                <td>{{ $spj->penerima_nama ?: '................................................' }}</td>
            </tr>
            <tr>
                <td>Alamat Toko / Rekanan</td>
                <td>:</td>
                <td>{{ $spj->alamat_toko ?: '................................................' }}</td>
            </tr>
            <tr>
                <td>Bidang Pengusul</td>
                <td>:</td>
                <td>{{ $spj->bidang?->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td>Kegiatan DPA</td>
                <td>:</td>
                <td>{{ $spj->kegiatan?->label ?? '-' }}</td>
            </tr>
            <tr>
                <td>Sub Kegiatan DPA</td>
                <td>:</td>
                <td>{{ $spj->subKegiatan?->label ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tanggal Surat Pesanan</td>
                <td>:</td>
                <td>{{ \Carbon\Carbon::parse($spj->tanggal_pengajuan)->translatedFormat('d F Y') }}</td>
            </tr>
        </table>

        <p style="font-size: 10pt; margin-bottom: 6px;">
            Dengan hormat, bersama surat ini kami memesan barang persediaan / kebutuhan kegiatan dinas dengan rincian sebagai berikut:
        </p>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 18%;">Kode Rekening</th>
                    <th style="width: 35%;">Nama Barang</th>
                    <th style="width: 10%;">Volume</th>
                    <th style="width: 10%;">Satuan</th>
                    <th style="width: 22%;">Harga Perkiraan</th>
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
                        <td class="text-right">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right font-bold">Total Nilai Pesanan:</td>
                    <td class="text-right font-bold">Rp {{ number_format((float) $spj->total_nilai_rencana, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <p style="font-size: 9.5pt; margin-bottom: 10px;">
            Demikian Surat Pesanan ini dibuat untuk dapat dipenuhi sesuai spesifikasi dan kuantitas yang dipesan.
        </p>

        <table class="signature-table">
            <tr>
                <td style="width: 50%;">
                    Menerima & Menyetujui Pesanan,<br>
                    <strong>{{ $spj->nama_toko ?: 'Pihak Toko / Penyedia' }}</strong>
                    <div class="signature-space"></div>
                    <strong><u>{{ $spj->penerima_nama ?: '..................................' }}</u></strong><br>
                    {{ $spj->penerima_identitas ?: 'Pemilik / Pimpinan Toko' }}
                </td>
                <td style="width: 50%;">
                    Ditetapkan Tanggal: {{ \Carbon\Carbon::parse($spj->tanggal_pengajuan)->translatedFormat('d F Y') }}<br>
                    Pejabat Pelaksana Teknis Kegiatan (PPTK)<br>
                    <strong>Kepala {{ $spj->bidang?->nama ?? 'Bidang' }}</strong>
                    <div class="signature-space">
                        @if ($ttdPptk)
                            <img src="{{ $ttdPptk }}" class="ttd-img" alt="TTD PPTK">
                        @endif
                    </div>
                    <strong><u>{{ $spj->pptk?->name ?? ($spj->bidang?->kepala?->name ?? '..................................') }}</u></strong><br>
                    NIP. {{ $spj->pptk?->nip ?? ($spj->bidang?->kepala?->nip ?? '.................................') }}
                </td>
            </tr>
        </table>
    </div>

    @if (!in_array($spj->status, ['draft', 'diajukan', 'menunggu_realisasi'], true))
    {{-- ==================== HALAMAN 2: KWITANSI PEMBAYARAN (DEPAN) ==================== --}}
    <div class="page">
        <div class="header-kop">
            <h2>PEMERINTAH DAERAH PROVINSI / KABUPATEN</h2>
            <h3>{{ $pengaturan?->nama_dinas ?? 'DINAS PARIWISATA, PEMUDA DAN OLAHRAGA' }}</h3>
            <p>{{ $pengaturan?->alamat_kantor ?? 'Alamat Kantor Dinas - Telp/Fax. Resmi' }}</p>
        </div>

        <div class="doc-title">KWITANSI / BUKTI PEMBAYARAN</div>
        <div class="doc-subtitle">
            Nomor Bukti Kas: {{ $spj->nomor_pengajuan }}/KW/{{ date('Y') }}
            @if ($spj->nomor_bku)
                &nbsp;|&nbsp; Nomor BKU: {{ $spj->nomor_bku }}
            @endif
        </div>

        <table class="meta-table">
            <tr>
                <td style="width: 24%;">Tahun Anggaran</td>
                <td style="width: 2%;">:</td>
                <td style="width: 74%;" class="font-bold">{{ date('Y', strtotime((string) $spj->tanggal_pengajuan)) }}</td>
            </tr>
            <tr>
                <td>Kode Rekening Belanja</td>
                <td>:</td>
                <td>{{ $spj->details->first()?->kodeRekening?->kode_lengkap ?? '-' }} — {{ $spj->details->first()?->kodeRekening?->uraian ?? '-' }}</td>
            </tr>
            <tr>
                <td>Sudah Terima Dari</td>
                <td>:</td>
                <td><strong>Pengguna Anggaran / Kuasa Pengguna Anggaran {{ $pengaturan?->nama_dinas ?? 'Dinas' }}</strong></td>
            </tr>
            <tr>
                <td>Uang Sebesar</td>
                <td>:</td>
                <td class="font-bold" style="font-size: 11.5pt;">Rp {{ number_format((float) $totalNominal, 0, ',', '.') }},-</td>
            </tr>
        </table>

        <div class="box-terbilang">
            <strong>Terbilang:</strong> # {{ $terbilang }} #
        </div>

        <table class="meta-table">
            <tr>
                <td style="width: 24%;">Untuk Pembayaran</td>
                <td style="width: 2%;">:</td>
                <td style="width: 74%;">
                    Belanja persediaan/kebutuhan kegiatan <strong>{{ $spj->kegiatan?->nama_kegiatan }}</strong>,
                    Sub Kegiatan <strong>{{ $spj->subKegiatan?->nama_sub_kegiatan }}</strong>
                    pada <strong>{{ $spj->bidang?->nama }}</strong> sesuai Surat Pesanan Nomor {{ $spj->nomor_pengajuan }}/NP/{{ date('Y') }}
                    kepada <strong>{{ $spj->nama_toko ?: '-' }}</strong>.
                </td>
            </tr>
        </table>

        <table class="signature-table" style="margin-top: 16px;">
            <tr>
                <td style="width: 50%;">
                    Mengetahui / Menyetujui,<br>
                    <strong>Pengguna Anggaran / KPA</strong>
                    <div class="signature-space">
                        @if ($ttdPa)
                            <img src="{{ $ttdPa }}" class="ttd-img" alt="TTD PA/KPA">
                        @endif
                    </div>
                    <strong><u>{{ $spj->pa?->name ?? ($pengaturan?->nama_kepala_dinas ?? '..................................') }}</u></strong><br>
                    NIP. {{ $spj->pa?->nip ?? ($pengaturan?->kepala?->nip ?? '.................................') }}
                </td>
                <td style="width: 50%;">
                    Lunas Dibayar Tanggal: {{ $spj->tanggal_dicairkan ? \Carbon\Carbon::parse($spj->tanggal_dicairkan)->translatedFormat('d F Y') : '........................' }}<br>
                    <strong>Bendahara Pengeluaran</strong>
                    <div class="signature-space">
                        @if ($ttdBendahara)
                            <img src="{{ $ttdBendahara }}" class="ttd-img" alt="TTD Bendahara">
                        @endif
                    </div>
                    <strong><u>{{ $spj->bendahara?->name ?? '..................................' }}</u></strong><br>
                    NIP. {{ $spj->bendahara?->nip ?? '.................................' }}
                </td>
            </tr>
            <tr>
                <td style="padding-top: 18px;">
                    Pejabat Pelaksana Teknis Kegiatan<br>
                    <strong>( PPTK )</strong>
                    <div class="signature-space">
                        @if ($ttdPptk)
                            <img src="{{ $ttdPptk }}" class="ttd-img" alt="TTD PPTK">
                        @endif
                    </div>
                    <strong><u>{{ $spj->pptk?->name ?? ($spj->bidang?->kepala?->name ?? '..................................') }}</u></strong><br>
                    NIP. {{ $spj->pptk?->nip ?? ($spj->bidang?->kepala?->nip ?? '.................................') }}
                </td>
                <td style="padding-top: 18px;">
                    Yang Menerima Pembayaran,<br>
                    <strong>{{ $spj->nama_toko ?: 'Pihak Rekanan / Toko' }}</strong>
                    <div class="signature-space"></div>
                    <strong><u>{{ $spj->penerima_nama ?: '..................................' }}</u></strong><br>
                    {{ $spj->penerima_identitas ?: 'Pemilik Toko / Rekanan' }}
                </td>
            </tr>
        </table>
    </div>

    {{-- ==================== HALAMAN 3: BELAKANG KWITANSI (VERIFIKASI & RINCIAN PERHITUNGAN) ==================== --}}
    <div class="page">
        <div class="doc-title">LEMBAR VERIFIKASI & RINCIAN BELAKANG KWITANSI</div>
        <div class="doc-subtitle">Lampiran Bukti Kas Nomor: {{ $spj->nomor_pengajuan }}/KW/{{ date('Y') }}</div>

        <table class="meta-table">
            <tr>
                <td style="width: 24%;">Nama Toko / Penyedia</td>
                <td style="width: 2%;">:</td>
                <td style="width: 74%;" class="font-bold">{{ $spj->nama_toko ?: '-' }} ({{ $spj->penerima_nama ?: '-' }})</td>
            </tr>
            <tr>
                <td>Alamat Toko</td>
                <td>:</td>
                <td>{{ $spj->alamat_toko ?: '-' }}</td>
            </tr>
            <tr>
                <td>Kegiatan / Sub Kegiatan</td>
                <td>:</td>
                <td>{{ $spj->kegiatan?->nama_kegiatan ?? '-' }} / {{ $spj->subKegiatan?->nama_sub_kegiatan ?? '-' }}</td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 35%;">Uraian Barang / Spesifikasi</th>
                    <th style="width: 12%;">Vol Pesanan</th>
                    <th style="width: 12%;">Vol Realisasi</th>
                    <th style="width: 18%;">Harga Satuan (Rp)</th>
                    <th style="width: 18%;">Jumlah Harga (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($spj->details as $idx => $d)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $d->masterBarang?->nama_barang ?? '-' }}</td>
                        <td class="text-center">{{ $d->kuantitas }} {{ $d->masterBarang?->satuan?->nama ?? '' }}</td>
                        <td class="text-center">{{ $d->kuantitas_realisasi ?: $d->kuantitas }} {{ $d->masterBarang?->satuan?->nama ?? '' }}</td>
                        <td class="text-right">{{ number_format((float) ($d->harga_realisasi ?: $d->harga_satuan), 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format((float) ($d->subtotal_realisasi ?: $d->subtotal), 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right font-bold">Jumlah Total Realisasi Kwitansi:</td>
                    <td class="text-right font-bold">Rp {{ number_format((float) $totalNominal, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        @if ($spj->justifikasi_selisih)
            <p style="font-size: 9.5pt; margin-bottom: 10px;">
                <strong>Catatan / Justifikasi Selisih:</strong> {{ $spj->justifikasi_selisih }}
            </p>
        @endif

        <p style="font-size: 9.5pt; margin-bottom: 10px;">
            Telah diperiksa kelengkapan bukti fisik, kesesuaian spesifikasi barang dengan Surat Pesanan, serta kebenaran perhitungan administrasi keuangan:
        </p>

        <table class="signature-table" style="margin-top: 20px;">
            <tr>
                <td style="width: 50%;">
                    Telah Diverifikasi Administrasi & Anggaran,<br>
                    <strong>Pejabat Penatausahaan Keuangan (PPK)</strong>
                    <div class="signature-space">
                        @if ($ttdPpk)
                            <img src="{{ $ttdPpk }}" class="ttd-img" alt="TTD PPK">
                        @endif
                    </div>
                    <strong><u>{{ $spj->ppk?->name ?? '..................................' }}</u></strong><br>
                    NIP. {{ $spj->ppk?->nip ?? '.................................' }}
                </td>
                <td style="width: 50%;">
                    Yang Memeriksa Barang / Pekerjaan,<br>
                    <strong>Pejabat Pelaksana Teknis Kegiatan (PPTK)</strong>
                    <div class="signature-space">
                        @if ($ttdPptk)
                            <img src="{{ $ttdPptk }}" class="ttd-img" alt="TTD PPTK">
                        @endif
                    </div>
                    <strong><u>{{ $spj->pptk?->name ?? ($spj->bidang?->kepala?->name ?? '..................................') }}</u></strong><br>
                    NIP. {{ $spj->pptk?->nip ?? ($spj->bidang?->kepala?->nip ?? '.................................') }}
                </td>
            </tr>
        </table>
    </div>
    @endif

    @if (!in_array($spj->status, ['draft', 'diajukan', 'menunggu_realisasi', 'menunggu_verifikasi_pptk', 'revisi_pptk'], true))
    {{-- ==================== HALAMAN 4: NOTA PENCAIRAN DANA (NPD) ==================== --}}
    <div class="page">
        <div class="header-kop">
            <h2>PEMERINTAH DAERAH PROVINSI / KABUPATEN</h2>
            <h3>{{ $pengaturan?->nama_dinas ?? 'DINAS PARIWISATA, PEMUDA DAN OLAHRAGA' }}</h3>
            <p>{{ $pengaturan?->alamat_kantor ?? 'Alamat Kantor Dinas - Telp/Fax. Resmi' }}</p>
        </div>

        <div class="doc-title">NOTA PENCAIRAN DANA (NPD)</div>
        <div class="doc-subtitle">Nomor: {{ $spj->nomor_pengajuan }}/NPD/{{ date('Y') }}</div>

        <table class="meta-table">
            <tr>
                <td style="width: 24%;">Kepada Yth.</td>
                <td style="width: 2%;">:</td>
                <td style="width: 74%;">Pengguna Anggaran / Kuasa Pengguna Anggaran {{ $pengaturan?->nama_dinas ?? 'Dinas' }}</td>
            </tr>
            <tr>
                <td>Melalui</td>
                <td>:</td>
                <td>Pejabat Penatausahaan Keuangan (PPK) & Bendahara Pengeluaran</td>
            </tr>
            <tr>
                <td>Dari</td>
                <td>:</td>
                <td>Pejabat Pelaksana Teknis Kegiatan (PPTK) — {{ $spj->bidang?->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td>Kegiatan DPA</td>
                <td>:</td>
                <td>{{ $spj->kegiatan?->label ?? '-' }}</td>
            </tr>
            <tr>
                <td>Sub Kegiatan DPA</td>
                <td>:</td>
                <td>{{ $spj->subKegiatan?->label ?? '-' }}</td>
            </tr>
            <tr>
                <td>Jumlah Dana Dimohon</td>
                <td>:</td>
                <td class="font-bold" style="font-size: 11pt;">Rp {{ number_format((float) $totalNominal, 0, ',', '.') }},-</td>
            </tr>
            <tr>
                <td>Terbilang</td>
                <td>:</td>
                <td style="font-style: italic;">{{ $terbilang }}</td>
            </tr>
        </table>

        <p style="font-size: 10pt; margin-bottom: 6px;">
            Sehubungan dengan pelaksanaan kegiatan dan pemenuhan pesanan barang pada <strong>{{ $spj->nama_toko ?: 'Penyedia' }}</strong>, bersama ini kami mengajukan Nota Pencairan Dana (NPD) dengan rincian sebagai berikut:
        </p>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 20%;">Kode Rekening</th>
                    <th style="width: 39%;">Uraian Barang / Belanja</th>
                    <th style="width: 10%;">Volume</th>
                    <th style="width: 13%;">Harga Satuan</th>
                    <th style="width: 13%;">Jumlah Pencairan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($spj->details as $idx => $d)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $d->kodeRekening?->kode_lengkap ?? '-' }}</td>
                        <td>{{ $d->masterBarang?->nama_barang ?? '-' }}</td>
                        <td class="text-center">{{ $d->kuantitas_realisasi ?: $d->kuantitas }} {{ $d->masterBarang?->satuan?->nama ?? 'Unit' }}</td>
                        <td class="text-right">Rp {{ number_format((float) ($d->harga_realisasi ?: $d->harga_satuan), 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) ($d->subtotal_realisasi ?: $d->subtotal), 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right font-bold">Total Pencairan Dana (NPD):</td>
                    <td class="text-right font-bold">Rp {{ number_format((float) $totalNominal, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <table class="signature-table" style="margin-top: 22px;">
            <tr>
                <td style="width: 50%;">
                    Menyetujui Pencairan,<br>
                    <strong>Pengguna Anggaran / KPA</strong>
                    <div class="signature-space">
                        @if ($ttdPa)
                            <img src="{{ $ttdPa }}" class="ttd-img" alt="TTD PA/KPA">
                        @endif
                    </div>
                    <strong><u>{{ $spj->pa?->name ?? ($pengaturan?->nama_kepala_dinas ?? '..................................') }}</u></strong><br>
                    NIP. {{ $spj->pa?->nip ?? ($pengaturan?->kepala?->nip ?? '.................................') }}
                </td>
                <td style="width: 50%;">
                    Diajukan Oleh,<br>
                    <strong>Pejabat Pelaksana Teknis Kegiatan (PPTK)</strong>
                    <div class="signature-space">
                        @if ($ttdPptk)
                            <img src="{{ $ttdPptk }}" class="ttd-img" alt="TTD PPTK">
                        @endif
                    </div>
                    <strong><u>{{ $spj->pptk?->name ?? ($spj->bidang?->kepala?->name ?? '..................................') }}</u></strong><br>
                    NIP. {{ $spj->pptk?->nip ?? ($spj->bidang?->kepala?->nip ?? '.................................') }}
                </td>
            </tr>
        </table>
    </div>
    @endif

</body>
</html>
