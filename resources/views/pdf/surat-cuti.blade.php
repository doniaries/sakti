<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir &amp; Surat Izin Cuti - {{ $izin->user?->name ?? 'Pegawai' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 14mm 15mm 14mm 15mm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            color: #000;
            line-height: 1.25;
        }
        .page-break {
            page-break-before: always;
        }
        .right-header {
            width: 52%;
            margin-left: 48%;
            font-size: 9.5pt;
            margin-bottom: 8px;
        }
        .title-center {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin: 6px 0 8px 0;
            text-transform: uppercase;
        }
        table.form-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        table.form-table th,
        table.form-table td {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: top;
            font-size: 9.5pt;
        }
        .section-hdr {
            font-weight: bold;
            background-color: #f3f4f6;
        }
        .check-cell {
            text-align: center;
            font-weight: bold;
            font-family: DejaVu Sans, sans-serif;
            width: 28px;
        }
        .ttd-box {
            height: 46px;
        }
        .kop-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .kop-logo {
            width: 68px;
            text-align: center;
            vertical-align: middle;
        }
        .kop-text {
            text-align: center;
            vertical-align: middle;
        }
        .kop-text .instansi {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-text .alamat {
            font-size: 9pt;
        }
    </style>
</head>
<body>
    @php
        $pegawai = $izin->user;
        $jenisKode = $izin->jenisIzin?->kode ?? '';
        $isDisetujui = $izin->status === 'disetujui';
        $isDitolak = $izin->status === 'ditolak';
        $atasan = $izin->approver ?? $pegawai?->atasan;
        $kadis = \App\Models\User::where('id', $instansi?->kepala_id)->first()
            ?? \App\Models\User::role('pa')->first();
        $namaInstansi = $instansi?->nama_dinas ?? $pengaturan?->nama_dinas ?? 'DINAS PARIWISATA PEMUDA DAN OLAH RAGA';
        $alamatInstansi = $instansi?->alamat ?? $pengaturan?->alamat_kantor ?? 'Kabupaten Sijunjung';

        $isKadis = $pegawai && ($pegawai->id === $instansi?->kepala_id || $pegawai->hasRole('pa'));
        if ($isKadis) {
            $atasanJabatan = 'SEKRETARIS DAERAH';
            $atasanNama = $instansi?->nama_sekda ?: '-';
            $atasanNip = $instansi?->nip_sekda ?: '-';

            $pejabatJabatan = 'SEKRETARIS DAERAH KABUPATEN SIJUNJUNG';
            $pejabatNama = $instansi?->nama_sekda ?: '-';
            $pejabatNip = $instansi?->nip_sekda ?: '-';
        } else {
            $atasanJabatan = 'ATASAN LANGSUNG,';
            $atasanNama = $atasan?->name ?? 'Kepala Bidang';
            $atasanNip = $atasan?->nip ?? '-';

            $pejabatJabatan = 'KEPALA ' . strtoupper($namaInstansi);
            $pejabatNama = $kadis?->name ?? ($instansi?->nama_kepala_dinas ?? '-');
            $pejabatNip = $kadis?->nip ?? '-';
        }
        $kota = 'Muaro Sijunjung';
        $tglSurat = $izin->created_at ? $izin->created_at->translatedFormat('d F Y') : now()->translatedFormat('d F Y');
        $tglMulai = $izin->tanggal_mulai ? $izin->tanggal_mulai->translatedFormat('d F Y') : '-';
        $tglSelesai = $izin->tanggal_selesai ? $izin->tanggal_selesai->translatedFormat('d F Y') : '-';
    @endphp

    <!-- HALAMAN 1: ANAK LAMPIRAN 1.B PERATURAN BKN NOMOR 24 TAHUN 2017 -->
    <div class="right-header">
        ANAK LAMPIRAN 1.B<br>
        PERATURAN BADAN KEPEGAWAIAN NEGARA<br>
        REPUBLIK INDONESIA NOMOR 24 TAHUN 2017<br>
        TATA CARA PEMBERIAN CUTI PEGAWAI NEGERI SIPIL<br><br>
        {{ $kota }}, {{ $tglSurat }}<br>
        Kepada<br>
        Yth. KEPALA {{ strtoupper($namaInstansi) }}<br>
        Di {{ $kota }}
    </div>

    <div class="title-center">FORMULIR PERMINTAAN DAN PEMBERIAN CUTI</div>

    <table class="form-table">
        <tr>
            <td colspan="4" class="section-hdr">I. DATA PEGAWAI</td>
        </tr>
        <tr>
            <td style="width: 16%;">Nama</td>
            <td style="width: 38%;">{{ $pegawai?->name ?? '-' }}</td>
            <td style="width: 14%;">NIP</td>
            <td style="width: 32%;">{{ $pegawai?->nip ?? '-' }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>{{ $pegawai?->jabatan?->nama ?? '-' }}</td>
            <td>Masa Kerja</td>
            <td>{{ $pegawai?->masa_kerja ?? '-' }}</td>
        </tr>
        <tr>
            <td>Unit Kerja</td>
            <td colspan="3">{{ $pegawai?->bidang?->nama ?? $namaInstansi }}</td>
        </tr>
    </table>

    <table class="form-table">
        <tr>
            <td colspan="4" class="section-hdr">II. JENIS CUTI YANG DIAMBIL **</td>
        </tr>
        <tr>
            <td style="width: 42%;">1. Cuti Tahunan</td>
            <td class="check-cell">{{ $jenisKode === 'cuti_tahunan' ? '✓' : '' }}</td>
            <td style="width: 42%;">2. Cuti Besar</td>
            <td class="check-cell">{{ $jenisKode === 'cuti_besar' ? '✓' : '' }}</td>
        </tr>
        <tr>
            <td>3. Cuti Sakit</td>
            <td class="check-cell">{{ in_array($jenisKode, ['sakit', 'sakit_tanpa_surat'], true) ? '✓' : '' }}</td>
            <td>4. Cuti Melahirkan</td>
            <td class="check-cell">{{ $jenisKode === 'cuti_melahirkan' ? '✓' : '' }}</td>
        </tr>
        <tr>
            <td>5. Cuti Karena Alasan Penting</td>
            <td class="check-cell">{{ $jenisKode === 'cuti_alasan_penting' ? '✓' : '' }}</td>
            <td>6. Cuti di Luar Tanggungan Negara</td>
            <td class="check-cell">{{ $jenisKode === 'cuti_cltn' ? '✓' : '' }}</td>
        </tr>
    </table>

    <table class="form-table">
        <tr>
            <td class="section-hdr">III. ALASAN CUTI</td>
        </tr>
        <tr>
            <td style="min-height: 28px;">{{ $izin->alasan ?: '-' }}</td>
        </tr>
    </table>

    <table class="form-table">
        <tr>
            <td colspan="6" class="section-hdr">IV. LAMANYA CUTI</td>
        </tr>
        <tr>
            <td style="width: 14%;">Selama</td>
            <td style="width: 10%; text-align: center; font-weight: bold;">{{ $izin->jumlah_hari ?? 1 }}</td>
            <td style="width: 14%;">(Hari)*</td>
            <td style="width: 16%;">Mulai tanggal</td>
            <td colspan="2" style="width: 46%;">{{ $tglMulai }} s/d {{ $tglSelesai }}</td>
        </tr>
    </table>

    <table class="form-table">
        <tr>
            <td colspan="5" class="section-hdr">V. CATATAN ***</td>
        </tr>
        <tr>
            <td colspan="3" style="width: 55%; font-weight: bold;">1. CUTI TAHUNAN</td>
            <td style="width: 35%;">2. CUTI BESAR</td>
            <td style="width: 10%;"></td>
        </tr>
        <tr>
            <td style="width: 15%; text-align: center;">Tahun</td>
            <td style="width: 15%; text-align: center;">Sisa</td>
            <td style="width: 25%; text-align: center;">Keterangan</td>
            <td>3. CUTI SAKIT</td>
            <td></td>
        </tr>
        <tr>
            <td style="text-align: center;">N-2</td>
            <td style="text-align: center;">0</td>
            <td></td>
            <td>4. CUTI MELAHIRKAN</td>
            <td></td>
        </tr>
        <tr>
            <td style="text-align: center;">N-1</td>
            <td style="text-align: center;">0</td>
            <td></td>
            <td>5. CUTI KARENA ALASAN PENTING</td>
            <td></td>
        </tr>
        <tr>
            <td style="text-align: center;">N ({{ now()->year }})</td>
            <td style="text-align: center;">{{ $sisaCutiTahunan }}</td>
            <td>Kuota 12 hari/tahun</td>
            <td>6. CUTI DILUAR TANGGUNGAN NEGARA</td>
            <td></td>
        </tr>
    </table>

    <table class="form-table">
        <tr>
            <td colspan="3" class="section-hdr">VI. ALAMAT SELAMA MENJALANKAN CUTI</td>
        </tr>
        <tr>
            <td rowspan="2" style="width: 55%;">{{ $pegawai?->alamat ?? $alamatInstansi }}</td>
            <td style="width: 12%;">TELP</td>
            <td style="width: 33%;">{{ $pegawai?->no_hp ?? '-' }}</td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center;">
                Hormat saya,<br>
                <div class="ttd-box"></div>
                <strong>{{ $pegawai?->name ?? '-' }}</strong><br>
                NIP. {{ $pegawai?->nip ?? '-' }}
            </td>
        </tr>
    </table>

    <table class="form-table">
        <tr>
            <td colspan="4" class="section-hdr">VII. PERTIMBANGAN ATASAN LANGSUNG (KEPALA BIDANG)</td>
        </tr>
        <tr>
            <td style="width: 25%; text-align: center; font-weight: bold;">DISETUJUI</td>
            <td style="width: 25%; text-align: center; font-weight: bold;">PERUBAHAN ****</td>
            <td style="width: 25%; text-align: center; font-weight: bold;">DI TANGGUHKAN ****</td>
            <td style="width: 25%; text-align: center; font-weight: bold;">TIDAK SETUJU ****</td>
        </tr>
        <tr>
            <td class="check-cell" style="height: 22px; font-size: 13pt;">{{ $isDisetujui ? '✓' : '' }}</td>
            <td class="check-cell"></td>
            <td class="check-cell"></td>
            <td class="check-cell" style="font-size: 13pt;">{{ $isDitolak ? '✓' : '' }}</td>
        </tr>
        <tr>
            <td colspan="3">{{ $izin->catatan_atasan ?? '' }}</td>
            <td style="text-align: center;">
                {{ $atasanJabatan }}<br>
                <div class="ttd-box"></div>
                <strong>{{ $atasanNama }}</strong><br>
                NIP. {{ $atasanNip }}
            </td>
        </tr>
    </table>

    <table class="form-table">
        <tr>
            <td colspan="4" class="section-hdr">VIII. KEPUTUSAN PEJABAT YANG BERWENANG MEMBERIKAN CUTI</td>
        </tr>
        <tr>
            <td style="width: 25%; text-align: center; font-weight: bold;">DISETUJUI</td>
            <td style="width: 25%; text-align: center; font-weight: bold;">PERUBAHAN ****</td>
            <td style="width: 25%; text-align: center; font-weight: bold;">DI TANGGUHKAN ****</td>
            <td style="width: 25%; text-align: center; font-weight: bold;">TIDAK DISETUJUI ****</td>
        </tr>
        <tr>
            <td class="check-cell" style="height: 22px; font-size: 13pt;">{{ $isDisetujui ? '✓' : '' }}</td>
            <td class="check-cell"></td>
            <td class="check-cell"></td>
            <td class="check-cell" style="font-size: 13pt;">{{ $isDitolak ? '✓' : '' }}</td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td style="text-align: center;">
                {{ $pejabatJabatan }}<br>
                <div class="ttd-box"></div>
                <strong>{{ $pejabatNama }}</strong><br>
                NIP. {{ $pejabatNip }}
            </td>
        </tr>
    </table>

    <div style="font-size: 8.5pt; margin-top: 4px;">
        Catatan:<br>
        * Coret yang tidak perlu &nbsp;&nbsp;
        ** Pilih salah satu dengan memberi tanda centang (✓)<br>
        *** Diisi oleh pejabat yang menangani bidang kepegawaian &nbsp;&nbsp;
        **** Diberi tanda centang (✓) dan alasannya
    </div>

    <!-- HALAMAN 2: SURAT IZIN CUTI -->
    <div class="page-break"></div>

    <table class="kop-table">
        <tr>
            @if (!empty($logoPath))
                <td class="kop-logo">
                    <img src="{{ $logoPath }}" alt="Logo" style="width: 58px; height: auto;">
                </td>
            @endif
            <td class="kop-text">
                <div style="font-size: 11pt; font-weight: bold;">PEMERINTAH KABUPATEN SIJUNJUNG</div>
                <div class="instansi">{{ $namaInstansi }}</div>
                <div class="alamat">{{ $alamatInstansi }}</div>
            </td>
        </tr>
    </table>

    <div style="width: 50%; margin-left: 50%; font-size: 9pt; margin-bottom: 14px;">
        LAMPIRAN : SURAT EDARAN KEPALA BADAN<br>
        ADMINISTRASI KEPEGAWAIAN NEGARA<br>
        NOMOR : 01/SE/1977<br>
        TANGGAL : 25 FEBRUARI 1977
    </div>

    <div class="title-center" style="text-decoration: underline; margin-bottom: 2px;">
        SURAT IZIN {{ strtoupper($izin->jenisIzin?->nama ?? 'CUTI') }}
    </div>
    <div style="text-align: center; font-size: 10pt; margin-bottom: 16px;">
        NOMOR : &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; /{{ date('m/Y') }}
    </div>

    <p>1. Diberikan {{ $izin->jenisIzin?->nama ?? 'Cuti' }} kepada Pegawai Negeri Sipil / PPPK :</p>

    <table style="width: 100%; margin-left: 18px; margin-bottom: 12px; font-size: 10pt;">
        <tr>
            <td style="width: 28%;">Nama</td>
            <td style="width: 3%;">:</td>
            <td><strong>{{ $pegawai?->name ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td>NIP</td>
            <td>:</td>
            <td>{{ $pegawai?->nip ?? '-' }}</td>
        </tr>
        <tr>
            <td>Pangkat / Gol</td>
            <td>:</td>
            <td>{{ trim(($pegawai?->pangkat ?? '-') . ($pegawai?->golongan ? ' - ' . $pegawai->golongan : '')) }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td>{{ $pegawai?->jabatan?->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td>Satuan Organisasi</td>
            <td>:</td>
            <td>{{ $pegawai?->bidang?->nama ?? $namaInstansi }}</td>
        </tr>
    </table>

    <p style="text-align: justify;">
        Selama <strong>{{ $izin->jumlah_hari ?? 1 }}</strong> Hari kerja terhitung mulai tanggal
        <strong>{{ $tglMulai }}</strong> s.d. <strong>{{ $tglSelesai }}</strong>, dengan ketentuan sebagai berikut :
    </p>
    <ol type="a" style="margin-left: 18px;">
        <li>Selama menjalankan cuti wajib menyerahkan pekerjaan kepada atasan langsungnya atau pejabat lain yang ditunjuk.</li>
        <li>Setelah menjalankan cuti wajib melaporkan diri kepada atasan langsungnya dan bekerja kembali sebagaimana biasa.</li>
    </ol>

    <p>2. Demikian surat izin ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>

    <div style="width: 46%; margin-left: 54%; text-align: center; margin-top: 24px;">
        {{ $kota }}, {{ $tglSurat }}<br>
        {{ $pejabatJabatan }}<br>
        <div style="height: 56px;"></div>
        <strong><u>{{ $pejabatNama }}</u></strong><br>
        NIP. {{ $pejabatNip }}
    </div>

    <div style="margin-top: 28px; font-size: 9.5pt;">
        Tembusan :<br>
        1. Kepala BKPSDM Kabupaten Sijunjung<br>
        2. Arsip Kepegawaian {{ $namaInstansi }}
    </div>

    @if ($izin->status === 'disetujui' && ! empty($qrBase64))
        <div style="margin-top: 18px; border-top: 1px solid #d1d5db; padding-top: 6px; page-break-inside: avoid;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 96px; vertical-align: middle;">
                        <img src="{{ $qrBase64 }}" alt="QR Verifikasi" style="width: 84px; height: 84px;">
                    </td>
                    <td style="vertical-align: middle; font-size: 8.5pt; color: #4b5563;">
                        <strong>Ditandatangani Secara Elektronik melalui SAKTI</strong><br>
                        Disetujui oleh {{ $izin->approver?->name ?? $pejabatNama }}
                        @if ($izin->disetujui_at)
                            pada {{ $izin->disetujui_at->translatedFormat('d F Y H:i') }} WIB
                        @endif
                        <br>Scan QR Code di samping untuk verifikasi keaslian dokumen.
                        @if (! empty($trackingUrl))
                            <br><span style="font-size: 7.5pt; color: #6b7280;">{{ $trackingUrl }}</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    @endif
</body>
</html>

