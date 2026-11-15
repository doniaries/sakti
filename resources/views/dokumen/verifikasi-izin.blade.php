<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Surat Izin / Cuti — {{ $instansi?->nama_dinas ?? 'SAKTI' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4">
    <div class="max-w-2xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-5 {{ $isValid ? 'bg-emerald-600' : 'bg-rose-600' }} text-white">
            <div class="text-xs uppercase tracking-wider opacity-90">
                {{ $instansi?->nama_dinas ?? 'DINAS PARIWISATA PEMUDA DAN OLAHRAGA' }}
            </div>
            <h1 class="text-lg font-bold mt-1">
                {{ $isValid ? 'Dokumen Valid — Ditandatangani Secara Elektronik (SAKTI)' : 'Dokumen Tidak Valid / Belum Disetujui' }}
            </h1>
        </div>

        <div class="p-6 space-y-5">
            @if ($izin)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-slate-500 text-xs">Nama Pegawai</div>
                        <div class="font-semibold text-slate-900">{{ $izin->user?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500 text-xs">NIP</div>
                        <div class="font-medium text-slate-800">{{ $izin->user?->nip ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500 text-xs">Jenis Izin / Cuti</div>
                        <div class="font-semibold text-slate-900">{{ $izin->jenisIzin?->nama ?? '-' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-500 text-xs">Status Persetujuan</div>
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $isValid ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ strtoupper($izin->status ?? '-') }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="text-slate-500 text-xs">Tanggal</div>
                        <div class="font-medium text-slate-800">
                            @if ($izin->tanggal_mulai && $izin->tanggal_selesai && $izin->tanggal_mulai->equalTo($izin->tanggal_selesai))
                                {{ $izin->tanggal_mulai->translatedFormat('d F Y') }}
                            @else
                                {{ $izin->tanggal_mulai?->translatedFormat('d F Y') }} s.d. {{ $izin->tanggal_selesai?->translatedFormat('d F Y') }}
                            @endif
                            ({{ $izin->jumlah_hari }} hari kerja)
                        </div>
                    </div>
                    <div>
                        <div class="text-slate-500 text-xs">Disetujui Oleh</div>
                        <div class="font-medium text-slate-800">
                            {{ $izin->approver?->name ?? '-' }}
                            @if ($izin->disetujui_at)
                                <div class="text-xs text-slate-500">{{ $izin->disetujui_at->translatedFormat('d F Y H:i') }} WIB</div>
                            @endif
                        </div>
                    </div>
                    @if ($izin->hash_dokumen)
                        <div class="sm:col-span-2">
                            <div class="text-slate-500 text-xs">Hash Dokumen (SHA-256)</div>
                            <div class="font-mono text-xs text-slate-700 break-all bg-slate-50 p-2 rounded border border-slate-200 mt-1">
                                {{ $izin->hash_dokumen }}
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <p class="text-sm text-slate-600">
                    Data Surat Izin / Cuti dengan kode <strong>{{ $kode ?: '-' }}</strong> tidak ditemukan di sistem SAKTI.
                </p>
            @endif
        </div>
    </div>
</body>
</html>
