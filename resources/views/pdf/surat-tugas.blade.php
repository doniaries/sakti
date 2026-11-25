<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Tugas - {{ $surat->nomor_surat }}</title>
    <style>
        @page {
            size: 215mm 330mm;
            margin: 10mm 16mm 10mm 16mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #000;
            line-height: 1.3;
        }
        @media screen {
            body {
                margin: 14px 22px;
            }
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
            font-size: 13.5pt;
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
            font-size: 12pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .judul-surat .nomor {
            font-size: 10.5pt;
            margin-top: 2px;
        }
        table.body-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.body-table td {
            vertical-align: top;
            padding: 3px 2px;
        }
        table.person-item {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        table.person-item td {
            padding: 1px 2px;
            vertical-align: top;
        }
        table.pegawai-lampiran {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.pegawai-lampiran th,
        table.pegawai-lampiran td {
            border: 1px solid #000;
            padding: 5px 6px;
            font-size: 9.5pt;
            vertical-align: top;
        }
        table.pegawai-lampiran th {
            background-color: #f3f4f6;
            text-align: center;
        }
        .ttd-wrap {
            width: 48%;
            margin-left: 52%;
            margin-top: 20px;
            text-align: left;
            page-break-inside: avoid;
        }
        .ttd-space {
            height: 58px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    @php
        $srikandiMode = $srikandiMode ?? false;
        $pegawais = $surat->pegawais;
        $pakaiLampiran = $pegawais->count() > 4;
        $tanggalStr = $surat->created_at?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y');
        $isAtasNama = $surat->penandatangan_id && $instansi?->kepala_id && (int) $surat->penandatangan_id !== (int) $instansi->kepala_id;
        $jabatanTtd = $surat->penandatangan?->jabatan?->nama ?? 'KEPALA DINAS';
    @endphp

    @php
        $logoSijunjungPath = public_path('images/logo-sijunjung.png');
        $logoSijunjungDataUri = file_exists($logoSijunjungPath)
            ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoSijunjungPath))
            : ($logoPath ?? null);
    @endphp
    <div style="width: 100%; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 14px;">
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

    <div class="judul-surat">
        <h2>SURAT TUGAS</h2>
        <div class="nomor">
            Nomor : {{ $srikandiMode ? '${nomor_naskah}' : ($surat->nomor_surat ?: \App\Models\SuratTugas::generateNomorSurat()) }}
        </div>
    </div>

    <table class="body-table">
        <tr>
            <td style="width: 14%;">Dasar</td>
            <td style="width: 3%;">:</td>
            <td>
                @php
                    $hasUndangan = filled($surat->dasar_surat_undangan)
                        || filled($surat->asal_surat_undangan)
                        || filled($surat->tanggal_surat_undangan);
                @endphp
                @if ($hasUndangan)
                    <table class="person-item">
                        <tr>
                            <td style="width: 20px;">1.</td>
                            <td>{{ $surat->dasar_surat ?: \App\Models\SuratTugas::defaultDasarSurat() }}</td>
                        </tr>
                        <tr>
                            <td>2.</td>
                            <td>
                                Surat Undangan
                                @if (filled($surat->asal_surat_undangan)) dari {{ $surat->asal_surat_undangan }}@endif
                                @if (filled($surat->dasar_surat_undangan)) Nomor {{ $surat->dasar_surat_undangan }}@endif
                                @if (filled($surat->tanggal_surat_undangan)) tanggal {{ $surat->tanggal_surat_undangan->translatedFormat('d F Y') }}@endif
                                @if (filled($surat->perihal_surat_undangan ?: $surat->ringkasan_isi_undangan)) perihal {{ $surat->perihal_surat_undangan ?: $surat->ringkasan_isi_undangan }}@endif.
                            </td>
                        </tr>
                    </table>
                @else
                    {{ $surat->dasar_surat ?: \App\Models\SuratTugas::defaultDasarSurat() }}
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="3" style="height: 8px;"></td>
        </tr>
        <tr>
            <td>Kepada</td>
            <td>:</td>
            <td>
                @php
                    $pjKepada = filled($surat->penanggung_jawab_ids)
                        ? \App\Models\User::whereIn('id', $surat->penanggung_jawab_ids)->orderBy('eselon')->orderBy('name')->get()
                        : ($surat->penanggungJawab ? collect([$surat->penanggungJawab]) : collect());
                    $koordinatorKepada = filled($surat->koordinator_acara_ids)
                        ? \App\Models\User::whereIn('id', $surat->koordinator_acara_ids)->orderBy('name')->get()
                        : collect();
                    $kepadaList = $pjKepada->merge($koordinatorKepada)->merge($pegawais)->unique('id')->values();
                @endphp
                @if ($kepadaList->isEmpty())
                    <em>Belum ada pegawai yang ditugaskan.</em>
                @elseif ($pakaiLampiran)
                    Daftar nama pegawai terlampir pada Lampiran Surat Tugas ini ({{ $kepadaList->count() }} orang).
                @else
                    @foreach ($kepadaList as $index => $pegawai)
                        <table class="person-item">
                            <tr>
                                <td style="width: 20px;">{{ $index + 1 }}.</td>
                                <td style="width: 110px;">Nama</td>
                                <td style="width: 12px;">:</td>
                                <td><strong>{{ $pegawai->name }}</strong></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>Pangkat/gol</td>
                                <td>:</td>
                                <td>{{ $pegawai->pangkat_golongan?->getLabel() ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>NIP</td>
                                <td>:</td>
                                <td>{{ $pegawai->nip ?: '-' }}</td>
                            </tr>
                        </table>
                    @endforeach
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="3" style="height: 6px;"></td>
        </tr>
        <tr>
            <td>Untuk</td>
            <td>:</td>
            <td>
                <table class="person-item" style="text-align: justify;">
                    <tr>
                        <td style="width: 20px;">1.</td>
                        <td>
                            Melaksanakan tugas peliputan, dokumentasi foto/video, dan publikasi pada:
                            <table class="person-item" style="margin-top: 4px; margin-bottom: 4px;">
                                <tr>
                                    <td style="width: 75px;">Acara</td>
                                    <td style="width: 10px;">:</td>
                                    <td><strong>{{ $surat->nama_kegiatan }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Tanggal</td>
                                    <td>:</td>
                                    <td>
                                        <strong>@if ($surat->tanggal_mulai->equalTo($surat->tanggal_selesai)){{ $surat->tanggal_mulai->translatedFormat('d F Y') }}@else{{ $surat->tanggal_mulai->translatedFormat('d F Y') }} s.d. {{ $surat->tanggal_selesai->translatedFormat('d F Y') }}@endif</strong>
                                    </td>
                                </tr>
                                @if (filled($surat->jam_acara))
                                    <tr>
                                        <td>Jam</td>
                                        <td>:</td>
                                        <td><strong>{{ \Illuminate\Support\Carbon::parse($surat->jam_acara)->format('H.i') }} WIB</strong></td>
                                    </tr>
                                @endif
                                @if ($surat->lokasi)
                                    <tr>
                                        <td>Tempat</td>
                                        <td>:</td>
                                        <td><strong>{{ $surat->lokasi }}</strong></td>
                                    </tr>
                                @endif
                            </table>
                        </td>
                    </tr>
                    @if (filled($surat->keterangan))
                        <tr>
                            <td>2.</td>
                            <td>
                                {{ $surat->keterangan }}
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td>{{ filled($surat->keterangan) ? '3.' : '2.' }}</td>
                        <td>
                            Mempersiapkan peralatan peliputan dan dokumentasi sebelum kegiatan berlangsung.
                        </td>
                    </tr>
                    <tr>
                        <td>{{ filled($surat->keterangan) ? '4.' : '3.' }}</td>
                        <td>
                            Hadir di lokasi setengah jam sebelum acara dimulai.
                        </td>
                    </tr>
                    <tr>
                        <td>{{ filled($surat->keterangan) ? '5.' : '4.' }}</td>
                        <td>
                            Membuat laporan kegiatan di Aplikasi SAKTI.
                        </td>
                    </tr>
                    <tr>
                        <td>{{ filled($surat->keterangan) ? '6.' : '5.' }}</td>
                        <td>
                            Melaksanakan tugas dengan penuh tanggung jawab dan saling mendukung lintas peran sesuai kebutuhan kegiatan di lapangan.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-top: 20px; page-break-inside: avoid;">
        <tr>
            <td style="width: 48%; vertical-align: bottom; text-align: left;"></td>
            <td style="width: 52%; vertical-align: top; text-align: center;">
                <div>Dikeluarkan di : Muaro Sijunjung</div>
                <div>Pada tanggal : {{ $srikandiMode ? '${tanggal_naskah}' : $tanggalStr }}</div>
                <div style="margin-top: 8px; font-weight: bold; text-transform: uppercase;">
                    @if ($isAtasNama)
                        a.n. KEPALA DINAS<br>
                        <span>{{ $jabatanTtd }}</span>
                    @else
                        {{ $jabatanTtd }}
                    @endif
                </div>
                @if ($srikandiMode)
                    <div style="margin: 22px 0;">${ttd_pengirim}</div>
                    <div style="font-weight: bold;">${nama_pengirim}</div>
                    <div>{{ $surat->penandatangan?->pangkat_golongan?->getLabel() ?? 'Pembina' }}</div>
                    <div>NIP. ${nip_pengirim}</div>
                @else
                    @if ($surat->status === 'terbit' && ! empty($qrBase64))
                        <div style="margin: 8px 0 6px 0; text-align: center;">
                            <img src="{{ $qrBase64 }}" alt="QR TTE SAKTI" style="width: 84px; height: 84px; display: block; margin: 0 auto;">
                            <div style="font-size: 8pt; font-style: italic; color: #374151; margin-top: 2px;">
                                Disetujui secara elektronik
                            </div>
                        </div>
                    @else
                        <div class="ttd-space"></div>
                    @endif
                    <div style="font-weight: bold; text-decoration: underline;">
                        {{ $surat->penandatangan?->name ?? ($instansi?->nama_kepala_dinas ?? 'KEPALA DINAS') }}
                    </div>
                    @if ($surat->penandatangan?->pangkat_golongan)
                        <div>{{ $surat->penandatangan->pangkat_golongan->getLabel() }}</div>
                    @endif
                    @if ($surat->penandatangan?->nip)
                        <div>NIP. {{ $surat->penandatangan->nip }}</div>
                    @endif
                @endif
            </td>
        </tr>
    </table>

    @if ($pakaiLampiran)
        <div class="page-break"></div>
        <div style="font-weight: bold; margin-bottom: 4px;">LAMPIRAN SURAT TUGAS</div>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 10pt;">
            <tr>
                <td style="width: 14%;">Nomor</td>
                <td style="width: 3%;">:</td>
                <td>{{ $srikandiMode ? '${nomor_naskah}' : ($surat->nomor_surat ?: 'DRAFT') }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>:</td>
                <td>{{ $srikandiMode ? '${tanggal_naskah}' : $tanggalStr }}</td>
            </tr>
            <tr>
                <td>Perihal</td>
                <td>:</td>
                <td>{{ $surat->nama_kegiatan }}</td>
            </tr>
        </table>

        <table class="pegawai-lampiran">
            <thead>
                <tr>
                    <th style="width: 6%;">No.</th>
                    <th style="width: 30%;">Nama / NIP</th>
                    <th style="width: 24%;">Pangkat / Golongan</th>
                    <th style="width: 40%;">Jabatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pegawais as $index => $pegawai)
                    <tr style="page-break-inside: avoid;">
                        <td style="text-align: center;">{{ $index + 1 }}.</td>
                        <td>
                            <strong>{{ $pegawai->name }}</strong>
                            @if ($pegawai->nip)
                                <br><span style="font-size: 8.5pt;">NIP. {{ $pegawai->nip }}</span>
                            @endif
                        </td>
                        <td>{{ $pegawai->pangkat_golongan?->getLabel() ?? '-' }}</td>
                        <td>{{ $pegawai->jabatan_tim_kreatif?->getLabel() ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="ttd-wrap" style="text-align: center;">
            <div style="font-weight: bold; text-transform: uppercase;">
                @if ($isAtasNama)
                    a.n. KEPALA DINAS<br>
                    <span>{{ $jabatanTtd }}</span>
                @else
                    {{ $jabatanTtd }}
                @endif
            </div>
            @if ($srikandiMode)
                <div style="margin: 22px 0;">${ttd_pengirim}</div>
                <div style="font-weight: bold;">${nama_pengirim}</div>
                <div>{{ $surat->penandatangan?->pangkat_golongan?->getLabel() ?? 'Pembina' }}</div>
                <div>NIP. ${nip_pengirim}</div>
            @else
                @if ($surat->status === 'terbit' && ! empty($qrBase64))
                    <div style="margin: 8px 0 6px 0; text-align: center;">
                        <img src="{{ $qrBase64 }}" alt="QR TTE SAKTI" style="width: 84px; height: 84px; display: block; margin: 0 auto;">
                        <div style="font-size: 8pt; font-style: italic; color: #374151; margin-top: 2px;">
                            Disetujui secara elektronik
                        </div>
                    </div>
                @else
                    <div class="ttd-space"></div>
                @endif
                <div style="font-weight: bold; text-decoration: underline;">
                    {{ $surat->penandatangan?->name ?? ($instansi?->nama_kepala_dinas ?? 'KEPALA DINAS') }}
                </div>
                @if ($surat->penandatangan?->nip)
                    <div>NIP. {{ $surat->penandatangan->nip }}</div>
                @endif
            @endif
        </div>
    @endif
</body>
</html>
