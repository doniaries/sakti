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
            font-size: 11pt;
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
            margin-bottom: 16px;
        }
        .header-kop h2 {
            margin: 0;
            font-size: 14pt;
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
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin-top: 10px;
            margin-bottom: 4px;
        }
        .doc-subtitle {
            text-align: center;
            font-size: 10pt;
            margin-bottom: 18px;
        }
        table.meta-table {
            width: 100%;
            margin-bottom: 14px;
            font-size: 10pt;
        }
        table.meta-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 16px;
            font-size: 10pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 6px 8px;
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
            padding: 8px 12px;
            background-color: #fafafa;
            font-style: italic;
            margin-bottom: 20px;
            font-size: 10pt;
        }
        .signature-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
            font-size: 10pt;
        }
        .signature-table td {
            vertical-align: top;
            text-align: center;
            padding: 0 10px;
        }
        .signature-space {
            height: 65px;
        }
    </style>
</head>
<body>

    {{-- ==================== HALAMAN 1: NOTA PESANAN ==================== --}}
    <div class="page">
        <div class="header-kop">
            <h2>PEMERINTAH DAERAH PROVINSI / KABUPATEN</h2>
            <h3>{{ $pengaturan?->nama_dinas ?? 'DINAS PEMUDA DAN OLAHRAGA' }}</h3>
            <p>{{ $pengaturan?->alamat_kantor ?? 'Jl. Pemuda No. 10 - Telp/Fax. (021) 1234567' }}</p>
        </div>

        <div class="doc-title">SURAT PESANAN / NOTA PESANAN</div>
        <div class="doc-subtitle">Nomor: {{ $spj->nomor_pengajuan }}/NP/{{ date('Y') }}</div>

        <table class="meta-table">
            <tr>
                <td style="width: 25%;">Kepada Yth. Penyedia</td>
                <td style="width: 2%;">:</td>
                <td style="width: 73%;" class="font-bold">{{ $spj->nama_toko ?? 'Penyedia / Toko Rekanan' }}</td>
            </tr>
            <tr>
                <td>Alamat Penyedia</td>
                <td>:</td>
                <td>{{ $spj->alamat_toko ?? '-' }}</td>
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
                <td>Tanggal Pemesanan</td>
                <td>:</td>
                <td>{{ \Carbon\Carbon::parse($spj->tanggal_pengajuan)->translatedFormat('d F Y') }}</td>
            </tr>
        </table>

        <p style="font-size: 10pt; margin-bottom: 6px;">Dengan ini kami memesan barang / keperluan operasional dinas dengan rincian sebagai berikut:</p>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 20%;">Kode Rekening</th>
                    <th style="width: 40%;">Nama Barang & Spesifikasi</th>
                    <th style="width: 10%;">Volume</th>
                    <th style="width: 25%;">Perkiraan Biaya</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($spj->details as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->kodeRekening?->kode_lengkap ?? '-' }}</td>
                        <td>{{ $item->masterBarang?->nama_barang ?? '-' }}</td>
                        <td class="text-center">{{ $item->kuantitas }} {{ $item->masterBarang?->satuan?->nama ?? 'Unit' }}</td>
                        <td class="text-right">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right font-bold">Total Rencana Pesanan:</td>
                    <td class="text-right font-bold">Rp {{ number_format((float) $spj->total_nilai_rencana, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <table class="signature-table">
            <tr>
                <td style="width: 50%;">
                    Menyetujui / Menerima Pesanan<br>
                    <strong>Pihak Penyedia / Toko</strong>
                    <div class="signature-space"></div>
                    <strong>( {{ $spj->penerima_nama ?: ($spj->nama_toko ?: 'Pimpinan Toko') }} )</strong>
                </td>
                <td style="width: 50%;">
                    Ditetapkan di Tempat, {{ \Carbon\Carbon::parse($spj->tanggal_pengajuan)->translatedFormat('d F Y') }}<br>
                    Pejabat Pelaksana Teknis Kegiatan (PPTK)<br>
                    <strong>Kepala {{ $spj->bidang?->nama ?? 'Bidang' }}</strong>
                    <div class="signature-space" style="padding-top: 15px; font-size: 8.5pt; color: #333;">
                        @if (!in_array($spj->status, ['draft', 'diajukan'], true))
                            <em>[Telah Ditandatangani Secara Elektronik oleh PPTK]</em>
                        @else
                            <em>[Menunggu TTD Kepala Bidang / PPTK]</em>
                        @endif
                    </div>
                    <strong><u>{{ $spj->pptk?->name ?? ($spj->bidang?->kepala?->name ?? '..................................') }}</u></strong><br>
                    NIP. {{ $spj->pptk?->nip ?? ($spj->bidang?->kepala?->nip ?? '.................................') }}
                </td>
            </tr>
        </table>
    </div>

    @if (!in_array($spj->status, ['draft', 'diajukan'], true))
    {{-- ==================== HALAMAN 2: KWITANSI PEMBAYARAN (DIPROSES SETELAH SURAT PESANAN DI-TTD PPTK) ==================== --}}
    <div class="page">
        <div class="header-kop">
            <h2>PEMERINTAH DAERAH PROVINSI / KABUPATEN</h2>
            <h3>{{ $pengaturan?->nama_dinas ?? 'DINAS PEMUDA DAN OLAHRAGA' }}</h3>
            <p>{{ $pengaturan?->alamat_kantor ?? 'Jl. Pemuda No. 10 - Telp/Fax. (021) 1234567' }}</p>
        </div>

        <div class="doc-title">KWITANSI / BUKTI PEMBAYARAN</div>
        <div class="doc-subtitle">Nomor Bukti Kas: {{ $spj->nomor_pengajuan }}/KW/{{ date('Y') }}</div>

        <table class="meta-table">
            <tr>
                <td style="width: 25%;">Tahun Anggaran</td>
                <td style="width: 2%;">:</td>
                <td style="width: 73%;" class="font-bold">{{ date('Y', strtotime($spj->tanggal_pengajuan)) }}</td>
            </tr>
            <tr>
                <td>Kode Rekening Belanja</td>
                <td>:</td>
                <td>{{ $spj->details->first()?->kodeRekening?->kode_lengkap ?? '-' }} - {{ $spj->details->first()?->kodeRekening?->uraian ?? '-' }}</td>
            </tr>
            <tr>
                <td>Sudah Terima Dari</td>
                <td>:</td>
                <td><strong>Bendahara Pengeluaran {{ $pengaturan?->nama_dinas ?? 'Dinas' }}</strong></td>
            </tr>
            <tr>
                <td>Uang Sebesar</td>
                <td>:</td>
                <td class="font-bold" style="font-size: 11pt;">Rp {{ number_format((float) $totalNominal, 0, ',', '.') }},-</td>
            </tr>
        </table>

        <div class="box-terbilang">
            <strong>Terbilang:</strong> {{ $terbilang }}
        </div>

        <p style="font-size: 10pt; margin-bottom: 6px;">
            Yaitu untuk pembayaran belanja kebutuhan barang/jasa kegiatan <strong>{{ $spj->kegiatan?->nama_kegiatan }}</strong>, Sub Kegiatan <strong>{{ $spj->subKegiatan?->nama_sub_kegiatan }}</strong> pada Bidang <strong>{{ $spj->bidang?->nama }}</strong> dengan rincian barang terlampir pada nota/faktur toko.
        </p>

        <table class="signature-table" style="margin-top: 20px;">
            <tr>
                <td style="width: 50%;">
                    Mengetahui / Menyetujui,<br>
                    <strong>Pengguna Anggaran (PA)</strong>
                    <div class="signature-space"></div>
                    <strong><u>{{ $spj->pa?->name ?? ($pengaturan?->nama_kepala_dinas ?? '..................................') }}</u></strong><br>
                    NIP. {{ $spj->pa?->nip ?? ($pengaturan?->kepala?->nip ?? '.................................') }}
                </td>
                <td style="width: 50%;">
                    Lunas Dibayar Tgl: {{ $spj->tanggal_dicairkan ? \Carbon\Carbon::parse($spj->tanggal_dicairkan)->translatedFormat('d F Y') : '........................' }}<br>
                    <strong>Bendahara Pengeluaran</strong>
                    <div class="signature-space"></div>
                    <strong><u>{{ $spj->bendahara?->name ?? '..................................' }}</u></strong><br>
                    NIP. {{ $spj->bendahara?->nip ?? '.................................' }}
                </td>
            </tr>
            <tr>
                <td style="padding-top: 20px;">
                    Pejabat Pelaksana Teknis Kegiatan<br>
                    <strong>( PPTK )</strong>
                    <div class="signature-space"></div>
                    <strong><u>{{ $spj->pptk?->name ?? ($spj->bidang?->kepala?->name ?? '..................................') }}</u></strong><br>
                    NIP. {{ $spj->pptk?->nip ?? ($spj->bidang?->kepala?->nip ?? '.................................') }}
                </td>
                <td style="padding-top: 20px;">
                    Yang Menerima Pembayaran,<br>
                    <strong>Pihak Rekanan / Toko</strong>
                    <div class="signature-space"></div>
                    <strong><u>{{ $spj->penerima_nama ?: ($spj->nama_toko ?: '..................................') }}</u></strong><br>
                    Identitas: {{ $spj->penerima_identitas ?: 'Penyedia Resmi' }}
                </td>
            </tr>
        </table>
    </div>

    {{-- ==================== HALAMAN 3: NOTA PENCAIRAN DANA (NPD) ==================== --}}
    <div class="page">
        <div class="header-kop">
            <h2>PEMERINTAH DAERAH PROVINSI / KABUPATEN</h2>
            <h3>{{ $pengaturan?->nama_dinas ?? 'DINAS PEMUDA DAN OLAHRAGA' }}</h3>
            <p>{{ $pengaturan?->alamat_kantor ?? 'Jl. Pemuda No. 10 - Telp/Fax. (021) 1234567' }}</p>
        </div>

        <div class="doc-title">NOTA PENCAIRAN DANA (NPD)</div>
        <div class="doc-subtitle">Nomor: {{ $spj->nomor_pengajuan }}/NPD/{{ date('Y') }}</div>

        <table class="meta-table">
            <tr>
                <td style="width: 25%;">Kepada Yth.</td>
                <td style="width: 2%;">:</td>
                <td style="width: 73%;">Bendahara Pengeluaran {{ $pengaturan?->nama_dinas ?? 'Dinas' }}</td>
            </tr>
            <tr>
                <td>Dari</td>
                <td>:</td>
                <td>Pejabat Pelaksana Teknis Kegiatan (PPTK)</td>
            </tr>
            <tr>
                <td>Program / Kegiatan</td>
                <td>:</td>
                <td>{{ $spj->kegiatan?->label ?? '-' }}</td>
            </tr>
            <tr>
                <td>Sub Kegiatan</td>
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
            Sehubungan dengan realisasi belanja operasional/keperluan dinas yang telah diperiksa dan diverifikasi kesesuaian fisik serta kelengkapan dokumen pertanggungjawabannya, mohon bantuan Saudara untuk mencairkan dana kas kepada pihak terkait dengan perincian item:
        </p>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 6%;">No</th>
                    <th style="width: 48%;">Nama Barang / Jasa</th>
                    <th style="width: 14%;">Vol</th>
                    <th style="width: 16%;">Harga Satuan</th>
                    <th style="width: 16%;">Total Realisasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($spj->details as $idx => $d)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $d->masterBarang?->nama_barang ?? '-' }}</td>
                        <td class="text-center">{{ $d->kuantitas_realisasi ?: $d->kuantitas }} {{ $d->masterBarang?->satuan?->nama ?? 'Unit' }}</td>
                        <td class="text-right">Rp {{ number_format((float) ($d->harga_realisasi ?: $d->harga_satuan), 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format((float) ($d->subtotal_realisasi ?: $d->subtotal), 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right font-bold">Total Nominal Pencairan:</td>
                    <td class="text-right font-bold">Rp {{ number_format((float) $totalNominal, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <table class="signature-table" style="margin-top: 25px;">
            <tr>
                <td style="width: 50%;">
                    Disetujui Oleh,<br>
                    <strong>Pengguna Anggaran (PA)</strong>
                    <div class="signature-space"></div>
                    <strong><u>{{ $spj->pa?->name ?? ($pengaturan?->nama_kepala_dinas ?? '..................................') }}</u></strong><br>
                    NIP. {{ $spj->pa?->nip ?? ($pengaturan?->kepala?->nip ?? '.................................') }}
                </td>
                <td style="width: 50%;">
                    Diajukan Oleh,<br>
                    <strong>Pejabat Pelaksana Teknis Kegiatan (PPTK)</strong>
                    <div class="signature-space"></div>
                    <strong><u>{{ $spj->pptk?->name ?? ($spj->bidang?->kepala?->name ?? '..................................') }}</u></strong><br>
                    NIP. {{ $spj->pptk?->nip ?? ($spj->bidang?->kepala?->nip ?? '.................................') }}
                </td>
            </tr>
        </table>
    </div>
    @endif

</body>
</html>
