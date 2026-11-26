<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pegawai Belum Melengkapi Data Kepegawaian</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e293b; }
        .header { text-align: center; border-bottom: 2px solid #0f172a; padding-bottom: 8px; margin-bottom: 14px; }
        .header h2 { margin: 0; font-size: 13px; text-transform: uppercase; }
        .header p { margin: 3px 0 0; font-size: 10px; color: #475569; }
        .meta { margin-bottom: 10px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 7px; text-align: left; vertical-align: top; }
        th { background: #f1f5f9; font-weight: bold; font-size: 9.5px; text-transform: uppercase; }
        .badge { color: #b91c1c; font-weight: bold; }
        .footer { margin-top: 18px; text-align: right; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $pengaturan->nama_dinas ?? $pengaturan->nama_instansi ?? 'Dinas Pariwisata, Pemuda dan Olahraga' }}</h2>
        <p>Laporan Pegawai yang Belum Melengkapi Data Kepegawaian</p>
    </div>

    <div class="meta">
        <strong>Tanggal Cetak:</strong> {{ now('Asia/Jakarta')->format('d/m/Y H:i') }} WIB &nbsp;|&nbsp;
        <strong>Total Pegawai Belum Lengkap:</strong> {{ $pegawais->count() }} orang
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 28px;">No</th>
                <th>Nama Pegawai</th>
                <th>Bidang / Jabatan</th>
                <th>Data yang Belum Diisi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pegawais as $idx => $p)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $p->name }}</strong><br>
                        <span style="color:#64748b;">{{ $p->email }}</span>
                    </td>
                    <td>
                        {{ $p->bidang?->nama ?? '-' }}<br>
                        <span style="color:#64748b;">{{ $p->jabatan?->nama ?? '-' }}</span>
                    </td>
                    <td class="badge">{{ implode(', ', $p->missingKepegawaianFields()) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center; padding:14px;">Seluruh pegawai aktif sudah melengkapi data kepegawaian.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak oleh: <strong>{{ $dicetakOleh->name }}</strong>
    </div>
</body>
</html>
