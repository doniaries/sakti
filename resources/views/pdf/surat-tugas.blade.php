<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Tugas - {{ $surat->nomor_surat }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 18mm 15mm 18mm;
        }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            color: #000;
            line-height: 1.35;
        }
        .kop-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 6px;
            margin-bottom: 14px;
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
        .judul-surat {
            text-align: center;
            margin: 10px 0 14px 0;
        }
        .judul-surat h2 {
            margin: 0;
            font-size: 13pt;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .judul-surat .nomor {
            font-size: 11pt;
            margin-top: 2px;
        }
        table.meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.meta td {
            vertical-align: top;
            padding: 3px 4px;
        }
        table.pegawai {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 12px 0;
        }
        table.pegawai th,
        table.pegawai td {
            border: 1px solid #000;
            padding: 5px 6px;
            font-size: 10pt;
            vertical-align: top;
        }
        table.pegawai th {
            background-color: #f3f4f6;
            text-align: center;
        }
        .ttd-wrap {
            width: 45%;
            margin-left: 55%;
            margin-top: 20px;
            text-align: left;
        }
        .ttd-space {
            height: 60px;
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
                <div class="kop-dinas">{{ $instansi?->nama_instansi ?? 'DINAS PARIWISATA PEMUDA DAN OLAHRAGA' }}</div>
                <div class="kop-alamat">{{ $instansi?->alamat ?? 'Jl. Prof. M. Yamin, SH Muaro Sijunjung' }}</div>
            </td>
        </tr>
    </table>

    <div class="judul-surat">
        <h2>SURAT TUGAS</h2>
        <div class="nomor">Nomor : {{ $surat->nomor_surat }}</div>
    </div>

    <table class="meta">
        <tr>
            <td style="width: 16%;">Dasar</td>
            <td style="width: 3%;">:</td>
            <td>{{ $surat->dasar_surat ?: \App\Models\SuratTugas::defaultDasarSurat() }}</td>
        </tr>
    </table>

    <p style="text-align: center; font-weight: bold; margin: 10px 0;">MEMERINTAHKAN :</p>

    <table class="meta">
        <tr>
            <td style="width: 16%;">Kepada</td>
            <td style="width: 3%;">:</td>
            <td>Nama-nama pegawai tersebut di bawah ini:</td>
        </tr>
    </table>

    <table class="pegawai">
        <thead>
            <tr>
                <th style="width: 6%;">No.</th>
                <th style="width: 30%;">Nama / NIP</th>
                <th style="width: 24%;">Pangkat / Golongan</th>
                <th style="width: 40%;">Jabatan / Peran dalam Tim</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($surat->pegawais as $index => $pegawai)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}.</td>
                    <td>
                        <strong>{{ $pegawai->name }}</strong>
                        @if ($pegawai->nip)
                            <br><span style="font-size: 9pt;">NIP. {{ $pegawai->nip }}</span>
                        @endif
                    </td>
                    <td>
                        {{ $pegawai->pangkat_golongan?->getLabel() ?? '-' }}
                    </td>
                    <td>
                        {{ $pegawai->jabatan?->nama ?? '-' }}
                        @if ($pegawai->jabatan_tim_kreatif)
                            <br><em>({{ $pegawai->jabatan_tim_kreatif->getLabel() }})</em>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">Belum ada pegawai yang ditugaskan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="meta">
        <tr>
            <td style="width: 16%;">Untuk</td>
            <td style="width: 3%;">:</td>
            <td>
                Melaksanakan tugas <strong>{{ $surat->nama_kegiatan }}</strong>
                @if ($surat->lokasi)
                    bertempat di <strong>{{ $surat->lokasi }}</strong>
                @endif
                pada tanggal
                <strong>
                    @if ($surat->tanggal_mulai->equalTo($surat->tanggal_selesai))
                        {{ $surat->tanggal_mulai->translatedFormat('d F Y') }}
                    @else
                        {{ $surat->tanggal_mulai->translatedFormat('d F Y') }} s.d. {{ $surat->tanggal_selesai->translatedFormat('d F Y') }}
                    @endif
                </strong>.
            </td>
        </tr>
        @if ($surat->keterangan)
            <tr>
                <td>Catatan</td>
                <td>:</td>
                <td>{{ $surat->keterangan }}</td>
            </tr>
        @endif
    </table>

    <p>Demikian Surat Tugas ini dibuat untuk dilaksanakan dengan penuh rasa tanggung jawab.</p>

    <div class="ttd-wrap">
        <div>Ditetapkan di : Muaro Sijunjung</div>
        <div>Pada tanggal : {{ $surat->created_at?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y') }}</div>
        <div style="margin-top: 6px; font-weight: bold;">
            {{ $surat->penandatangan?->jabatan?->nama ?? 'KEPALA DINAS' }},
        </div>
        <div class="ttd-space"></div>
        <div style="font-weight: bold; text-decoration: underline;">
            {{ $surat->penandatangan?->name ?? ($instansi?->nama_kadis ?? 'KEPALA DINAS') }}
        </div>
        @if ($surat->penandatangan?->nip)
            <div>NIP. {{ $surat->penandatangan->nip }}</div>
        @endif
    </div>
</body>
</html>
