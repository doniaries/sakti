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
        @media screen {
            html {
                background: #e2e8f0;
                padding: 20px 12px;
            }
            body {
                max-width: 210mm;
                min-height: 297mm;
                margin: 0 auto;
                background: #ffffff;
                padding: 15mm 18mm;
                box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);
                box-sizing: border-box;
            }
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
    <div style="width: 100%; border-bottom: 3px solid #000; padding-bottom: 8px; margin-bottom: 16px;">
        <table style="margin: 0 auto; border-collapse: collapse;">
            <tr>
                @if ($logoPath)
                    <td style="padding-right: 16px; vertical-align: middle;">
                        <img src="{{ $logoPath }}" alt="Logo" style="width: 64px; height: auto;">
                    </td>
                @endif
                <td style="text-align: center; vertical-align: middle;">
                    <div style="font-size: 13.5pt; font-weight: bold; line-height: 1.2;">PEMERINTAH KABUPATEN SIJUNJUNG</div>
                    <div style="font-size: 13.5pt; font-weight: bold; text-transform: uppercase; line-height: 1.2;">{{ $instansi?->nama_dinas ?? 'DINAS PARIWISATA, PEMUDA DAN OLAH RAGA' }}</div>
                    <div style="font-size: 9.5pt; margin-top: 2px;">{{ $instansi?->alamat ?? 'Jl. Prof. M. Yamin, SH Muaro Sijunjung' }}</div>
                </td>
            </tr>
        </table>
    </div>

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

    @if ($izin->status === 'disetujui' && ! empty($qrBase64))
        <div style="margin-top: 24px; border-top: 1px solid #d1d5db; padding-top: 6px; page-break-inside: avoid;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 96px; vertical-align: middle;">
                        <img src="{{ $qrBase64 }}" alt="QR Verifikasi" style="width: 84px; height: 84px;">
                    </td>
                    <td style="vertical-align: middle; font-size: 8.5pt; color: #4b5563;">
                        <strong>Ditandatangani Secara Elektronik melalui SAKTI</strong><br>
                        Disetujui oleh {{ $izin->approver?->name ?? 'Atasan Langsung' }}
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
