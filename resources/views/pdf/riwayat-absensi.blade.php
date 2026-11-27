<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Presensi - {{ $user->name }} - {{ $periode }}</title>
    <style>
        @page { margin: 12mm 14mm; size: A4 portrait; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; color: #1e293b; line-height: 1.4; }
        .kop { border-bottom: 2.5px solid #0f172a; padding-bottom: 8px; margin-bottom: 12px; text-align: center; }
        .kop .pemkab { font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }
        .kop .dinas { font-size: 14px; font-weight: bold; text-transform: uppercase; color: #0f172a; margin: 2px 0; }
        .kop .alamat { font-size: 9px; color: #475569; }
        .judul { text-align: center; margin-bottom: 12px; }
        .judul h2 { font-size: 12px; font-weight: bold; text-transform: uppercase; margin: 0 0 2px; text-decoration: underline; }
        .judul p { font-size: 10px; margin: 0; color: #475569; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 9.5px; }
        .info-table td { padding: 2px 4px; vertical-align: top; }
        .rekap-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .rekap-table td { border: 1px solid #cbd5e1; padding: 6px 4px; text-align: center; width: 16.66%; }
        .rekap-num { font-size: 13px; font-weight: bold; display: block; }
        .rekap-lbl { font-size: 8px; text-transform: uppercase; color: #475569; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .data-table th, .data-table td { border: 1px solid #cbd5e1; padding: 5px 6px; font-size: 9.5px; }
        .data-table th { background: #f1f5f9; font-weight: bold; text-transform: uppercase; font-size: 8.5px; }
        .text-center { text-align: center; }
        .ttd-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .ttd-table td { width: 50%; text-align: center; vertical-align: top; font-size: 9.5px; }
        .ttd-space { height: 52px; }
        .footer { margin-top: 16px; border-top: 1px solid #e2e8f0; padding-top: 6px; font-size: 8px; color: #64748b; display: flex; justify-content: space-between; }
    </style>
</head>
<body>
    <div class="kop">
        <div class="pemkab">Pemerintah Kabupaten Sijunjung</div>
        <div class="dinas">{{ $instansi->nama ?? 'Dinas Pariwisata Pemuda dan Olahraga' }}</div>
        <div class="alamat">{{ $instansi->alamat ?? 'Jl. Prof. M. Yamin, SH Muaro Sijunjung' }}</div>
    </div>

    <div class="judul">
        <h2>Laporan Rekapitulasi Kehadiran Pegawai</h2>
        <p>Periode: {{ $periode }} &bull; Filter: {{ $status === 'semua' ? 'Semua Status' : strtoupper(str_replace('_', ' ', $status)) }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 16%;">Nama Pegawai</td>
            <td style="width: 34%; font-weight: bold;">: {{ trim(($user->gelar_depan ? $user->gelar_depan.' ' : '').$user->name.($user->gelar_belakang ? ', '.$user->gelar_belakang : '')) }}</td>
            <td style="width: 16%;">Bidang / Unit</td>
            <td style="width: 34%; font-weight: bold;">: {{ $user->bidang->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td>NIP</td>
            <td>: {{ $user->nip ?? '-' }}</td>
            <td>Jabatan</td>
            <td>: {{ $user->jabatan->nama ?? '-' }}</td>
        </tr>
    </table>

    <table class="rekap-table">
        <tr>
            <td><span class="rekap-num">{{ $rekap['hadir'] }}</span><span class="rekap-lbl">Hadir</span></td>
            <td><span class="rekap-num">{{ $rekap['terlambat'] }}</span><span class="rekap-lbl">Telat</span></td>
            <td><span class="rekap-num">{{ $rekap['dinas_luar'] }}</span><span class="rekap-lbl">Dinas Luar</span></td>
            <td><span class="rekap-num">{{ $rekap['izin_sakit'] }}</span><span class="rekap-lbl">Izin/Sakit</span></td>
            <td><span class="rekap-num">{{ $rekap['cuti'] ?? 0 }}</span><span class="rekap-lbl">Cuti</span></td>
            <td><span class="rekap-num">{{ $rekap['alpa'] }}</span><span class="rekap-lbl">Alpa</span></td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;">No</th>
                <th style="width: 28%; text-align: left;">Hari / Tanggal</th>
                <th style="width: 14%;">Jam Masuk</th>
                <th style="width: 14%;">Jam Pulang</th>
                <th style="width: 16%;">Status</th>
                <th style="width: 22%; text-align: left;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riwayat as $index => $item)
                @php
                    $st = is_object($item->status) ? $item->status->value : (string) $item->status;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('l, d F Y') }}</td>
                    <td class="text-center">{{ $item->jam_masuk ? substr((string) $item->jam_masuk, 0, 5) : '-' }}</td>
                    <td class="text-center">{{ $item->jam_pulang ? substr((string) $item->jam_pulang, 0, 5) : '-' }}</td>
                    <td class="text-center" style="font-weight: bold; text-transform: uppercase;">{{ str_replace('_', ' ', $st) }}</td>
                    <td>{{ $item->keterangan ?? ($st === 'hadir' ? 'Kehadiran Normal' : '-') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data kehadiran pada filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd-table">
        <tr>
            <td>
                Mengetahui,<br>
                <strong>Atasan Langsung / Kepala Dinas</strong>
                <div class="ttd-space"></div>
                <strong style="text-decoration: underline;">{{ $user->atasan->name ?? '( ________________________ )' }}</strong><br>
                NIP. {{ $user->atasan->nip ?? '........................................' }}
            </td>
            <td>
                Muaro Sijunjung, {{ now()->translatedFormat('d F Y') }}<br>
                <strong>Pegawai Bersangkutan</strong>
                <div class="ttd-space"></div>
                <strong style="text-decoration: underline;">{{ trim(($user->gelar_depan ? $user->gelar_depan.' ' : '').$user->name.($user->gelar_belakang ? ', '.$user->gelar_belakang : '')) }}</strong><br>
                NIP. {{ $user->nip ?? '........................................' }}
            </td>
        </tr>
    </table>

    <div class="footer">
        <span>Dicetak otomatis melalui {{ $pengaturan->nama_aplikasi ?? 'SAKTI' }} — {{ now()->translatedFormat('d/m/Y H:i') }} WIB</span>
    </div>
</body>
</html>
