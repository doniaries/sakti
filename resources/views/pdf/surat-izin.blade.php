<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Permohonan Izin - {{ $izin->user?->name ?? 'Pegawai' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 18mm 15mm 18mm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            color: #000;
            line-height: 1.4;
        }
        .kop-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 6px;
            margin-bottom: 16px;
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
        .kop-pemkab {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-dinas {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-alamat {
            font-size: 9pt;
        }
        .judul {
            text-align: center;
            margin: 12px 0 18px 0;
            font-size: 12.5pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        table.biodata {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 14px 20px;
        }
        table.biodata td {
            padding: 3px 4px;
            vertical-align: top;
        }
        table.ttd {
            width: 100%;
            margin-top: 28px;
        }
        table.ttd td {
            width: 50%;
            vertical-align: top;
            text-align: center;
        }
        .ttd-space {
            height: 62px;
        }
    </style>
</head>
<body>
    <table class="kop-table">
        <tr>
            <td class="kop-logo">
                @if ($logoPath)
                    <img src="{{ $logoPath }}" alt="Logo" style="width: 60px; height: auto;">
                @endif
            </td>
            <td class="kop-text">
                <div class="kop-pemkab">PEMERINTAH KABUPATEN SIJUNJUNG</div>
                <div class="kop-dinas">{{ $instansi?->nama_dinas ?? 'DINAS PARIWISATA PEMUDA DAN OLAHRAGA' }}</div>
                <div class="kop-alamat">{{ $instansi?->alamat ?? 'Jl. Prof. M. Yamin, SH Muaro Sijunjung' }}</div>
            </td>
        </tr>
    </table>

    <div class="judul">SURAT PERMOHONAN {{ strtoupper($izin->jenisIzin?->nama ?? 'IZIN') }}</div>

    <p>Kepada Yth.<br>
    <strong>{{ $izin->user?->atasan?->jabatan?->nama ?? 'Kepala Dinas / Atasan Langsung' }}</strong><br>
    di Tempat</p>

    <p>Dengan hormat,<br>
    Saya yang bertanda tangan di bawah ini:</p>

    <table class="biodata">
        <tr>
            <td style="width: 28%;">Nama</td>
            <td style="width: 3%;">:</td>
            <td><strong>{{ $izin->user?->name ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td>NIP</td>
            <td>:</td>
            <td>{{ $izin->user?->nip ?: '-' }}</td>
        </tr>
        <tr>
            <td>Pangkat / Golongan</td>
            <td>:</td>
            <td>{{ $izin->user?->pangkat_golongan?->getLabel() ?? '-' }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td>{{ $izin->user?->jabatan?->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td>Bidang / Unit Kerja</td>
            <td>:</td>
            <td>{{ $izin->user?->bidang?->nama ?? '-' }}</td>
        </tr>
    </table>

    <p>
        Dengan ini mengajukan permohonan <strong>{{ $izin->jenisIzin?->nama ?? 'Izin' }}</strong> selama
        <strong>{{ $izin->jumlah_hari }} hari kerja</strong>, terhitung mulai tanggal
        <strong>{{ $izin->tanggal_mulai?->translatedFormat('d F Y') }}</strong>
        @if ($izin->tanggal_selesai && ! $izin->tanggal_selesai->equalTo($izin->tanggal_mulai))
            sampai dengan tanggal <strong>{{ $izin->tanggal_selesai?->translatedFormat('d F Y') }}</strong>
        @endif
        dengan alasan: <em>{{ $izin->alasan ?: '-' }}</em>.
    </p>

    <p>Demikian surat permohonan ini saya sampaikan. Atas perhatian dan izin yang diberikan, saya ucapkan terima kasih.</p>

    <table class="ttd">
        <tr>
            <td>
                <div>Mengetahui / Menyetujui,</div>
                <div style="font-weight: bold;">{{ $izin->approver?->jabatan?->nama ?? ($izin->user?->atasan?->jabatan?->nama ?? 'Atasan Langsung') }}</div>
                <div class="ttd-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">
                    {{ $izin->approver?->name ?? ($izin->user?->atasan?->name ?? '....................................') }}
                </div>
                @if ($izin->approver?->nip ?? $izin->user?->atasan?->nip)
                    <div>NIP. {{ $izin->approver?->nip ?? $izin->user?->atasan?->nip }}</div>
                @endif
            </td>
            <td>
                <div>Muaro Sijunjung, {{ $izin->created_at?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}</div>
                <div style="font-weight: bold;">Pemohon,</div>
                <div class="ttd-space"></div>
                <div style="font-weight: bold; text-decoration: underline;">{{ $izin->user?->name ?? '-' }}</div>
                @if ($izin->user?->nip)
                    <div>NIP. {{ $izin->user->nip }}</div>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
