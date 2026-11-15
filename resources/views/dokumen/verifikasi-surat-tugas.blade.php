<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Surat Tugas — {{ $instansi?->nama_dinas ?? 'SAKTI' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4">
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-5 {{ $isValid ? 'bg-emerald-600' : 'bg-rose-600' }} text-white">
            <div class="text-xs uppercase tracking-wider opacity-90">
                {{ $instansi?->nama_dinas ?? 'DINAS PARIWISATA PEMUDA DAN OLAHRAGA' }}
            </div>
            <h1 class="text-lg font-bold mt-1">
                {{ $isValid ? 'Surat Tugas Terverifikasi Asli' : 'Dokumen Tidak Ditemukan' }}
            </h1>
        </div>

        <div class="p-6 space-y-5">
            @if ($isValid && $surat)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-slate-500 text-xs">Nomor Surat</div>
                        <div class="font-semibold text-slate-900">{{ $surat->nomor_surat }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500 text-xs">Status Dokumen</div>
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $surat->status === 'terbit' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $surat->status === 'terbit' ? 'Resmi (TTE SRIKANDI)' : 'Draft Terdaftar di SAKTI' }}
                            </span>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <div class="text-slate-500 text-xs">Nama Kegiatan</div>
                        <div class="font-semibold text-slate-900">{{ $surat->nama_kegiatan }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500 text-xs">Tanggal Pelaksanaan</div>
                        <div class="font-medium text-slate-800">
                            @if ($surat->tanggal_mulai->equalTo($surat->tanggal_selesai))
                                {{ $surat->tanggal_mulai->translatedFormat('d F Y') }}
                            @else
                                {{ $surat->tanggal_mulai->translatedFormat('d F Y') }} s.d. {{ $surat->tanggal_selesai->translatedFormat('d F Y') }}
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="text-slate-500 text-xs">Lokasi</div>
                        <div class="font-medium text-slate-800">{{ $surat->lokasi ?: '-' }}</div>
                    </div>
                    <div class="sm:col-span-2">
                        <div class="text-slate-500 text-xs">Pejabat Penandatangan</div>
                        <div class="font-medium text-slate-800">
                            {{ $surat->penandatangan?->name ?? '-' }}
                            @if ($surat->penandatangan_id && $instansi?->kepala_id && (int) $surat->penandatangan_id !== (int) $instansi->kepala_id)
                                <span class="text-slate-500">(a.n. Kepala Dinas — {{ $surat->penandatangan?->jabatan?->nama }})</span>
                            @elseif ($surat->penandatangan?->jabatan?->nama)
                                <span class="text-slate-500">({{ $surat->penandatangan->jabatan->nama }})</span>
                            @endif
                            @if ($surat->ditandatangani_at)
                                <div class="text-xs text-slate-500 mt-0.5">
                                    Ditandatangani secara elektronik (SAKTI) pada {{ $surat->ditandatangani_at->translatedFormat('d F Y H:i') }} WIB
                                </div>
                            @endif
                        </div>
                    </div>
                    @if ($surat->hash_dokumen)
                        <div class="sm:col-span-2">
                            <div class="text-slate-500 text-xs">Hash Dokumen (SHA-256)</div>
                            <div class="font-mono text-xs text-slate-700 break-all bg-slate-50 p-2 rounded border border-slate-200 mt-1">
                                {{ $surat->hash_dokumen }}
                            </div>
                        </div>
                    @endif
                </div>

                <div class="border-t border-slate-200 pt-4">
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500 mb-2">
                        Tim yang Bertugas ({{ $surat->pegawais->count() }} Orang)
                    </div>
                    <ul class="divide-y divide-slate-100 text-sm">
                        @foreach ($surat->pegawais as $index => $pegawai)
                            <li class="py-2 flex items-center justify-between gap-2">
                                <div>
                                    <span class="text-slate-400 mr-1">{{ $index + 1 }}.</span>
                                    <span class="font-medium text-slate-800">{{ $pegawai->name }}</span>
                                </div>
                                @if ($pegawai->jabatan_tim_kreatif)
                                    <span class="text-xs px-2 py-0.5 rounded bg-sky-50 text-sky-700 border border-sky-200">
                                        {{ $pegawai->jabatan_tim_kreatif->getLabel() }}
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @else
                <p class="text-sm text-slate-600">
                    Data Surat Tugas dengan kode verifikasi <strong>{{ $kode ?: '-' }}</strong> tidak ditemukan di sistem SAKTI.
                </p>
            @endif
        </div>
    </div>
</body>
</html>
