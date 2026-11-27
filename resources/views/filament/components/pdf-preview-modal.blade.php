@php
    $enumStr = fn (mixed $v): string => $v instanceof \BackedEnum ? (string) $v->value : (string) ($v ?? '');
@endphp

<div class="space-y-3">
    <!-- Toolbar Tailwind -->
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/90 dark:bg-slate-800/70 px-4 py-3">
        <div class="flex items-center gap-3 min-w-0">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-100 dark:bg-sky-500/20 text-sky-600 dark:text-sky-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                </svg>
            </span>
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate">{{ $title }}</span>
                    <span class="inline-flex items-center rounded-full bg-sky-100 dark:bg-sky-500/20 px-2.5 py-0.5 text-[11px] font-bold text-sky-700 dark:text-sky-300">
                        {{ $pegawais->count() }} Pegawai
                    </span>
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $subtitle }}</div>
            </div>
        </div>

        <a
            href="{{ $url }}"
            target="_blank"
            rel="noopener"
            class="inline-flex items-center gap-1.5 rounded-xl bg-sky-600 hover:bg-sky-700 px-4 py-2 text-xs font-bold text-white shadow-sm transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            <span>Cetak / Download PDF</span>
        </a>
    </div>

    <!-- Tabel Preview Instan (Livewire HTML) -->
    <div class="max-h-[68vh] overflow-auto rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-inner">
        @if ($mode === 'bezeting')
            <table class="w-full text-left border-collapse text-[11px]">
                <thead class="sticky top-0 z-10 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 uppercase text-[10px]">
                    <tr>
                        <th class="border-b border-r border-slate-200 dark:border-slate-700 px-2 py-2 text-center">No</th>
                        <th class="border-b border-r border-slate-200 dark:border-slate-700 px-2.5 py-2">Nama / TTL</th>
                        <th class="border-b border-r border-slate-200 dark:border-slate-700 px-2.5 py-2">NIP / Status</th>
                        <th class="border-b border-r border-slate-200 dark:border-slate-700 px-2 py-2 text-center">TMT CPNS</th>
                        <th class="border-b border-r border-slate-200 dark:border-slate-700 px-2 py-2 text-center">TMT PNS</th>
                        <th class="border-b border-r border-slate-200 dark:border-slate-700 px-2 py-2 text-center">Gol / TMT</th>
                        <th class="border-b border-r border-slate-200 dark:border-slate-700 px-2 py-2 text-center">Masa Kerja</th>
                        <th class="border-b border-r border-slate-200 dark:border-slate-700 px-2.5 py-2">Jabatan / SK Terakhir</th>
                        <th class="border-b border-r border-slate-200 dark:border-slate-700 px-2.5 py-2">Pendidikan</th>
                        <th class="border-b border-r border-slate-200 dark:border-slate-700 px-2.5 py-2">PNJJ / Diklat</th>
                        <th class="border-b border-r border-slate-200 dark:border-slate-700 px-2 py-2 text-center">JK</th>
                        <th class="border-b border-slate-200 dark:border-slate-700 px-2 py-2 text-center">Eselon</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-200">
                    @foreach ($pegawais as $idx => $p)
                        @php
                            $cpnsRow = $p->riwayatGolongan->first(fn ($g) => in_array($g->jenis_sk, ['cpns', 'pppk'], true));
                            $pnsRow = $p->riwayatGolongan->firstWhere('jenis_sk', 'pns');
                            $gol = $p->riwayatGolongan->firstWhere('is_golongan_terakhir', true) ?? $p->riwayatGolongan->first();
                            $jab = $p->riwayatJabatan->firstWhere('is_jabatan_sekarang', true) ?? $p->riwayatJabatan->first();
                            $pend = $p->riwayatPendidikan->firstWhere('is_pendidikan_terakhir', true) ?? $p->riwayatPendidikan->first();
                            $pnjj = $p->riwayatSertifikat->firstWhere('jenis', 'pnjj') ?? $p->riwayatSertifikat->first();

                            $golRuang = $gol?->golongan instanceof \BackedEnum ? $gol->golongan->value : ($gol?->golongan ?: ($p->pangkat_golongan ?: '-'));
                            $tmtGol = $gol?->tmt_golongan ? \Carbon\Carbon::parse($gol->tmt_golongan)->format('d/m/Y') : '-';
                            $mk = ($gol && (filled($gol->masa_kerja_tahun) || filled($gol->masa_kerja_bulan)))
                                ? ((int) ($gol->masa_kerja_tahun ?? 0)) . 't ' . ((int) ($gol->masa_kerja_bulan ?? 0)) . 'b'
                                : '-';
                            $pendText = $pend
                                ? trim($enumStr($pend->jenjang) . ' ' . ($pend->jurusan ?? '') . ($pend->tahun_lulus ? ' (' . $pend->tahun_lulus . ')' : ''))
                                : '-';
                            $pnjjTahun = $pnjj?->tahun ?: ($pnjj?->tanggal_terbit ? \Carbon\Carbon::parse($pnjj->tanggal_terbit)->format('Y') : null);
                            $pnjjText = $pnjj
                                ? trim(($pnjj->nama_sertifikat ?? '') . ($pnjjTahun ? ' (' . $pnjjTahun . ')' : '') . ($pnjj->jumlah_jam ? ' • ' . $pnjj->jumlah_jam . 'j' : ''))
                                : '-';
                            $tglLahir = $p->tanggal_lahir ? \Carbon\Carbon::parse($p->tanggal_lahir)->format('d/m/Y') : '-';
                        @endphp
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="border-r border-slate-200 dark:border-slate-800 px-2 py-2 text-center font-semibold">{{ $idx + 1 }}</td>
                            <td class="border-r border-slate-200 dark:border-slate-800 px-2.5 py-2">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $p->name }}</div>
                                <div class="text-[10px] text-slate-500">{{ $p->tempat_lahir ?: '-' }}, {{ $tglLahir }}</div>
                            </td>
                            <td class="border-r border-slate-200 dark:border-slate-800 px-2.5 py-2 font-mono">
                                <div>{{ $p->nip ?: '-' }}</div>
                                <div class="text-[10px] text-sky-600 font-bold uppercase">{{ $enumStr($p->status_kepegawaian) ?: '-' }}</div>
                            </td>
                            <td class="border-r border-slate-200 dark:border-slate-800 px-2 py-2 text-center">{{ $cpnsRow?->tmt_golongan ? \Carbon\Carbon::parse($cpnsRow->tmt_golongan)->format('d/m/Y') : '-' }}</td>
                            <td class="border-r border-slate-200 dark:border-slate-800 px-2 py-2 text-center">{{ $pnsRow?->tmt_golongan ? \Carbon\Carbon::parse($pnsRow->tmt_golongan)->format('d/m/Y') : '-' }}</td>
                            <td class="border-r border-slate-200 dark:border-slate-800 px-2 py-2 text-center">
                                <span class="inline-block rounded bg-amber-50 dark:bg-amber-500/15 px-1.5 py-0.5 font-bold text-amber-700 dark:text-amber-300">{{ $golRuang }}</span>
                                <div class="text-[10px] text-slate-500 mt-0.5">{{ $tmtGol }}</div>
                            </td>
                            <td class="border-r border-slate-200 dark:border-slate-800 px-2 py-2 text-center">{{ $mk }}</td>
                            <td class="border-r border-slate-200 dark:border-slate-800 px-2.5 py-2">
                                <div class="font-semibold">{{ $jab?->nama_jabatan ?: ($p->jabatan?->nama ?: '-') }}</div>
                                <div class="text-[10px] text-slate-500">{{ $jab?->nomor_sk ?: '-' }}</div>
                            </td>
                            <td class="border-r border-slate-200 dark:border-slate-800 px-2.5 py-2">{{ $pendText }}</td>
                            <td class="border-r border-slate-200 dark:border-slate-800 px-2.5 py-2">{{ $pnjjText }}</td>
                            <td class="border-r border-slate-200 dark:border-slate-800 px-2 py-2 text-center font-bold">{{ strtoupper($enumStr($p->jenis_kelamin) ?: '-') }}</td>
                            <td class="px-2 py-2 text-center font-semibold">{{ $enumStr($p->eselon) ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <table class="w-full text-left border-collapse text-xs">
                <thead class="sticky top-0 z-10 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 uppercase text-[10px]">
                    <tr>
                        <th class="border-b border-r border-slate-200 dark:border-slate-700 px-3 py-2.5 text-center w-12">No</th>
                        <th class="border-b border-r border-slate-200 dark:border-slate-700 px-3 py-2.5">Nama Pegawai</th>
                        <th class="border-b border-r border-slate-200 dark:border-slate-700 px-3 py-2.5">Bidang / Jabatan</th>
                        <th class="border-b border-slate-200 dark:border-slate-700 px-3 py-2.5">Data yang Belum Diisi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-200">
                    @forelse ($pegawais as $idx => $p)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="border-r border-slate-200 dark:border-slate-800 px-3 py-2.5 text-center font-semibold">{{ $idx + 1 }}</td>
                            <td class="border-r border-slate-200 dark:border-slate-800 px-3 py-2.5">
                                <div class="font-bold text-slate-900 dark:text-white">{{ $p->name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $p->email }}</div>
                            </td>
                            <td class="border-r border-slate-200 dark:border-slate-800 px-3 py-2.5">
                                <div>{{ $p->bidang?->nama ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $p->jabatan?->nama ?? '-' }}</div>
                            </td>
                            <td class="px-3 py-2.5">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($p->missingKepegawaianFields() as $field)
                                        <span class="inline-flex items-center rounded-md bg-rose-50 dark:bg-rose-500/15 px-2 py-0.5 text-[10px] font-bold text-rose-700 dark:text-rose-300 border border-rose-200/70 dark:border-rose-500/30">
                                            {{ $field }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">Seluruh pegawai aktif sudah melengkapi data kepegawaian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>
</div>
