<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Nominatif / Bezeting Pegawai</title>
    <style>
        @page { margin: 14px 16px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 7.5px; color: #0f172a; }
        .header { text-align: center; margin-bottom: 10px; }
        .header h2 { margin: 0; font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px; }
        .header h3 { margin: 2px 0 0; font-size: 10px; text-transform: uppercase; }
        .header p { margin: 2px 0 0; font-size: 9px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #334155; padding: 3.5px 4px; vertical-align: middle; }
        th { background: #e2e8f0; font-weight: bold; font-size: 7px; text-align: center; text-transform: uppercase; }
        td.center { text-align: center; }
        .muted { color: #475569; font-size: 6.8px; }
        .signature { margin-top: 16px; width: 100%; }
        .signature td { border: none; padding: 2px 0; }
        .sig-box { width: 260px; float: right; text-align: left; font-size: 8.5px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>DAFTAR NOMINATIF / BEZETING PEGAWAI APARATUR SIPIL NEGARA</h2>
        <h3>{{ strtoupper($pengaturan->nama_dinas ?? $pengaturan->nama_instansi ?? 'DINAS PARIWISATA PEMUDA DAN OLAHRAGA KABUPATEN SIJUNJUNG') }}</h3>
        <p>TAHUN {{ now('Asia/Jakarta')->year }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 18px;">NO</th>
                <th rowspan="2" style="width: 120px;">NAMA / TEMPAT, TGL LAHIR</th>
                <th rowspan="2" style="width: 88px;">NIP / STATUS</th>
                <th rowspan="2" style="width: 48px;">TMT CPNS / PPPK</th>
                <th rowspan="2" style="width: 48px;">TMT PNS</th>
                <th colspan="2">REALISASI PANGKAT TERAKHIR</th>
                <th colspan="2">MASA KERJA</th>
                <th rowspan="2" style="width: 110px;">JABATAN / BIDANG</th>
                <th colspan="3">REALISASI SK JABATAN TERAKHIR</th>
                <th rowspan="2" style="width: 82px;">PENDIDIKAN (NAMA/TAHUN)</th>
                <th colspan="2">PENDIDIKAN PNJJ</th>
                <th colspan="2">JK</th>
                <th rowspan="2" style="width: 38px;">AGAMA</th>
                <th rowspan="2" style="width: 30px;">ESELON</th>
            </tr>
            <tr>
                <th style="width: 38px;">GOL/RUANG</th>
                <th style="width: 46px;">T.M.T</th>
                <th style="width: 22px;">THN</th>
                <th style="width: 22px;">BLN</th>
                <th style="width: 68px;">NOMOR</th>
                <th style="width: 46px;">TANGGAL</th>
                <th style="width: 46px;">T.M.T</th>
                <th style="width: 56px;">NAMA/TAHUN</th>
                <th style="width: 24px;">JAM</th>
                <th style="width: 14px;">L</th>
                <th style="width: 14px;">P</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pegawais as $idx => $p)
                @php
                    $cpnsRow = $p->riwayatGolongan->first(fn ($g) => in_array($g->jenis_sk, ['cpns', 'pppk'], true));
                    $pnsRow = $p->riwayatGolongan->firstWhere('jenis_sk', 'pns');
                    $tmtCpns = $cpnsRow?->tmt_golongan ? \Carbon\Carbon::parse($cpnsRow->tmt_golongan)->format('d/m/Y') : '-';
                    $tmtPns = $pnsRow?->tmt_golongan ? \Carbon\Carbon::parse($pnsRow->tmt_golongan)->format('d/m/Y') : '-';

                    $gol = $p->riwayatGolongan->firstWhere('is_golongan_terakhir', true) ?? $p->riwayatGolongan->first();
                    $jab = $p->riwayatJabatan->firstWhere('is_jabatan_sekarang', true) ?? $p->riwayatJabatan->first();
                    $pend = $p->riwayatPendidikan->firstWhere('is_pendidikan_terakhir', true) ?? $p->riwayatPendidikan->first();
                    $pnjj = $p->riwayatSertifikat->firstWhere('jenis', 'pnjj') ?? $p->riwayatSertifikat->first();

                    $golRuang = $gol?->golongan instanceof \BackedEnum ? $gol->golongan->value : ($gol?->golongan ?: ($p->pangkat_golongan ?: '-'));
                    $tmtGol = $gol?->tmt_golongan ? \Carbon\Carbon::parse($gol->tmt_golongan)->format('d/m/Y') : ($p->tmt_pangkat ? \Carbon\Carbon::parse($p->tmt_pangkat)->format('d/m/Y') : '-');
                    $mkThn = $gol?->masa_kerja_tahun ?? '-';
                    $mkBln = $gol?->masa_kerja_bulan ?? '-';

                    $namaJabatan = $jab?->nama_jabatan ?: ($p->jabatan?->nama ?: '-');
                    $skNo = $jab?->nomor_sk ?: '-';
                    $skTgl = $jab?->tanggal_sk ? \Carbon\Carbon::parse($jab->tanggal_sk)->format('d/m/Y') : '-';
                    $skTmt = $jab?->tmt_jabatan ? \Carbon\Carbon::parse($jab->tmt_jabatan)->format('d/m/Y') : '-';

                    $enumStr = fn (mixed $v): string => $v instanceof \BackedEnum ? (string) $v->value : (string) ($v ?? '');
                    $pendText = $pend
                        ? trim($enumStr($pend->jenjang) . ' ' . ($pend->jurusan ?? '') . ($pend->tahun_lulus ? ' (' . $pend->tahun_lulus . ')' : ''))
                        : trim($enumStr($p->jenjang_pendidikan) . ' ' . ($p->pendidikan ?? ''));
                    if ($pendText === '') {
                        $pendText = '-';
                    }

                    $pnjjTahun = $pnjj?->tahun ?: ($pnjj?->tanggal_terbit ? \Carbon\Carbon::parse($pnjj->tanggal_terbit)->format('Y') : null);
                    $pnjjNama = $pnjj
                        ? trim(($pnjj->nama_sertifikat ?? '') . ($pnjjTahun ? ' (' . $pnjjTahun . ')' : ''))
                        : '-';
                    $pnjjJam = $pnjj?->jumlah_jam ?? '-';

                    $tglLahir = $p->tanggal_lahir ? \Carbon\Carbon::parse($p->tanggal_lahir)->translatedFormat('d F Y') : '-';
                    $ttl = trim(($p->tempat_lahir ?: '-') . ', ' . $tglLahir, ', ');
                    $jk = strtoupper($enumStr($p->jenis_kelamin));
                    $isL = in_array($jk, ['L', 'LAKI-LAKI', 'PRIA'], true);
                    $isP = in_array($jk, ['P', 'PEREMPUAN', 'WANITA'], true);
                    $statusPeg = strtoupper($enumStr($p->status_kepegawaian) ?: '-');
                    $agama = strtoupper($enumStr($p->agama) ?: 'ISLAM');
                    $eselon = $p->eselon ?: '-';
                @endphp
                <tr>
                    <td class="center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $p->name }}</strong><br>
                        <span class="muted">{{ $ttl }}</span>
                    </td>
                    <td class="center">
                        {{ $p->nip ?: '-' }}<br>
                        <span class="muted">{{ $statusPeg }}</span>
                    </td>
                    <td class="center">{{ $tmtCpns }}</td>
                    <td class="center">{{ $tmtPns }}</td>
                    <td class="center">{{ $golRuang }}</td>
                    <td class="center">{{ $tmtGol }}</td>
                    <td class="center">{{ $mkThn }}</td>
                    <td class="center">{{ $mkBln }}</td>
                    <td>
                        {{ $namaJabatan }}
                        @if ($p->bidang?->nama)
                            <br><span class="muted">{{ $p->bidang->nama }}</span>
                        @endif
                    </td>
                    <td class="center">{{ $skNo }}</td>
                    <td class="center">{{ $skTgl }}</td>
                    <td class="center">{{ $skTmt }}</td>
                    <td>{{ $pendText }}</td>
                    <td class="center">{{ $pnjjNama }}</td>
                    <td class="center">{{ $pnjjJam }}</td>
                    <td class="center">{{ $isL ? '✓' : '' }}</td>
                    <td class="center">{{ $isP ? '✓' : '' }}</td>
                    <td class="center">{{ $agama }}</td>
                    <td class="center">{{ $eselon }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature">
        <div class="sig-box">
            <div>Muaro Sijunjung, {{ now('Asia/Jakarta')->translatedFormat('F Y') }}</div>
            <div style="font-weight: bold; margin-top: 2px;">KEPALA {{ strtoupper($pengaturan->nama_dinas ?? 'DINAS PARIWISATA PEMUDA DAN OLAHRAGA') }}</div>
            <div style="font-weight: bold;">KABUPATEN SIJUNJUNG</div>
            <div style="height: 44px;"></div>
            <div style="font-weight: bold; text-decoration: underline;">{{ $kepalaDinas?->name ?? $pengaturan->nama_kepala_dinas ?? 'AFRINELDI, SH' }}</div>
            <div>NIP. {{ $kepalaDinas?->nip ?? '19720407 199202 1 002' }}</div>
        </div>
    </div>
</body>
</html>
