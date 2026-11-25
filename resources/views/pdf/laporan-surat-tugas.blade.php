<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kegiatan - {{ $surat->nama_kegiatan }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.45; color: #111; margin: 28px 36px; }
        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 18px; }
        .header h2 { margin: 0; font-size: 14pt; text-transform: uppercase; letter-spacing: 0.5px; }
        .header p { margin: 2px 0 0; font-size: 10pt; }
        .title { text-align: center; font-weight: bold; font-size: 13pt; text-decoration: underline; margin-bottom: 4px; }
        .subtitle { text-align: center; font-size: 11pt; margin-bottom: 18px; }
        table.meta { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.meta td { padding: 4px 6px; vertical-align: top; }
        table.meta td.label { width: 26%; font-weight: bold; }
        table.meta td.sep { width: 3%; }
        .section-title { font-weight: bold; margin: 14px 0 6px; text-transform: uppercase; font-size: 11pt; border-bottom: 1px solid #999; padding-bottom: 2px; }
        table.grid { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 11pt; }
        table.grid th, table.grid td { border: 1px solid #444; padding: 6px 8px; text-align: left; vertical-align: top; }
        table.grid th { background: #f3f4f6; }
        table.ttd { width: 100%; border-collapse: collapse; margin-top: 28px; page-break-inside: avoid; }
        table.ttd td { width: 50%; text-align: center; vertical-align: top; padding: 0 12px; }
        .ttd-space { height: 64px; }
    </style>
</head>
<body>
    @php
        $logoSijunjungPath = public_path('images/logo-sijunjung.png');
        $logoSijunjungDataUri = file_exists($logoSijunjungPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoSijunjungPath))
            : null;
    @endphp
    <div style="width: 100%; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 18px;">
        <table style="margin: 0 auto; border-collapse: collapse;">
            <tr>
                @if (! empty($logoSijunjungDataUri))
                    <td style="padding-right: 16px; vertical-align: middle;">
                        <img src="{{ $logoSijunjungDataUri }}" alt="Logo Kabupaten Sijunjung" style="width: 62px; height: auto;">
                    </td>
                @endif
                <td style="text-align: center; vertical-align: middle;">
                    <div style="font-size: 13pt; font-weight: bold; line-height: 1.2;">PEMERINTAH KABUPATEN SIJUNJUNG</div>
                    <div style="font-size: 14pt; font-weight: bold; text-transform: uppercase; line-height: 1.2;">DINAS PARIWISATA PEMUDA DAN OLAHRAGA</div>
                    <div style="font-size: 9.5pt; margin-top: 2px;">Alamat: Gedung Bersama Lantai III Komplek Pasar Inpres Muaro</div>
                    <div style="font-size: 9.5pt;">Telp (0754) 20531, 20533 &nbsp; Muaro Sijunjung, 27511</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="title">LAPORAN HASIL KEGIATAN</div>
    <div class="subtitle">Nomor Surat Tugas: {{ $surat->nomor_surat ?? 'Draft' }}</div>

    <table class="meta">
        <tr>
            <td class="label">Nama Kegiatan</td>
            <td class="sep">:</td>
            <td><strong>{{ $surat->nama_kegiatan }}</strong></td>
        </tr>
        <tr>
            <td class="label">Tanggal Pelaksanaan</td>
            <td class="sep">:</td>
            <td>
                @if ($surat->tanggal_mulai?->equalTo($surat->tanggal_selesai))
                    {{ $surat->tanggal_mulai?->translatedFormat('d F Y') }}
                @else
                    {{ $surat->tanggal_mulai?->translatedFormat('d F Y') }} s.d. {{ $surat->tanggal_selesai?->translatedFormat('d F Y') }}
                @endif
                @if (filled($surat->jam_acara))
                    (Pukul {{ \Illuminate\Support\Carbon::parse($surat->jam_acara)->format('H.i') }} WIB)
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Lokasi</td>
            <td class="sep">:</td>
            <td>{{ $surat->lokasi ?? '-' }}</td>
        </tr>
    </table>

    <div class="section-title">I. Tim yang Bertugas</div>
    <table class="grid">
        <thead>
            <tr>
                <th style="width: 6%;">No</th>
                <th>Nama Pegawai</th>
                <th style="width: 28%;">Status Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($surat->pegawais as $idx => $pegawai)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>{{ $pegawai->name }}</td>
                    <td>{{ ($pegawai->pivot->status_kehadiran ?? '') === 'hadir' ? 'Hadir' : 'Belum Konfirmasi' }}</td>
                </tr>
            @empty
                <tr><td colspan="3">Tidak ada data pelaksana.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">II. Hasil Pekerjaan</div>
    <table class="grid">
        <thead>
            <tr>
                <th style="width: 6%;">No</th>
                <th style="width: 24%;">Pelapor</th>
                <th>Ringkasan Hasil Pekerjaan & Dokumentasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($surat->laporans as $idx => $laporan)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>
                        {{ $laporan->user?->name ?? '-' }}<br>
                        <small>{{ $laporan->dilaporkan_at?->translatedFormat('d/m/Y H:i') }}</small>
                    </td>
                    <td>
                        {{ $laporan->ringkasan_kegiatan }}
                        @if (filled($laporan->link_dokumentasi))
                            <br><small>Dokumentasi: {{ $laporan->link_dokumentasi }}</small>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="3">Belum ada laporan kegiatan yang dikirim.</td></tr>
            @endforelse
        </tbody>
    </table>

    @php
        $koordinator = filled($surat->koordinator_acara_ids)
            ? \App\Models\User::with('jabatan')->whereIn('id', $surat->koordinator_acara_ids)->first()
            : null;
        $penanggungJawab = filled($surat->penanggung_jawab_ids)
            ? \App\Models\User::with('jabatan')->whereIn('id', $surat->penanggung_jawab_ids)->first()
            : $surat->penanggungJawab;
    @endphp

    <table class="ttd">
        <tr>
            <td>
                Mengetahui,<br>
                Koordinator Acara
                <div class="ttd-space"></div>
                <strong><u>{{ $koordinator?->name ?? ($surat->koordinator_acara ?: '..............................') }}</u></strong><br>
                <span>{{ $koordinator?->jabatan?->nama ?? '' }}</span>
            </td>
            <td>
                Sijunjung, {{ now()->translatedFormat('d F Y') }}<br>
                Penanggung Jawab Kegiatan
                <div class="ttd-space"></div>
                <strong><u>{{ $penanggungJawab?->name ?? '..............................' }}</u></strong><br>
                <span>{{ $penanggungJawab?->jabatan?->nama ?? '' }}</span>
            </td>
        </tr>
    </table>
</body>
</html>
