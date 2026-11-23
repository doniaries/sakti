<div class="pb-24 px-4 pt-4 space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-extrabold text-slate-800 dark:text-white tracking-tight">Agenda Kegiatan</h1>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Jadwal kegiatan &amp; disposisi Anda</p>
        </div>
        <a href="{{ route('mobile.dashboard') }}" wire:navigate class="text-xs font-bold text-sky-600 dark:text-sky-400">
            <i class="fa-solid fa-arrow-left mr-1"></i> Beranda
        </a>
    </div>

    @if ($todayAgendas->isEmpty() && $upcomingAgendas->isEmpty())
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-6 text-center">
            <i class="fa-solid fa-calendar-check text-3xl text-sky-500 mb-2"></i>
            <p class="text-sm font-bold text-slate-800 dark:text-white">Belum ada agenda kegiatan</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Jadwal kegiatan dan disposisi Anda akan tampil di sini.</p>
        </div>
    @endif

    @if ($todayAgendas->isNotEmpty())
        <div class="space-y-2.5">
            <h2 class="text-xs font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                Agenda Hari Ini ({{ $todayAgendas->count() }})
            </h2>
            @foreach ($todayAgendas as $item)
                @php
                    $disposisi = $item->disposisis->first();
                    $jam = substr((string) $item->jam_mulai, 0, 5) . ($item->jam_selesai ? ' - ' . substr((string) $item->jam_selesai, 0, 5) : ' - Selesai');
                @endphp
                <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-4 space-y-2 shadow-sm">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                            HARI INI
                        </span>
                        <span class="text-xs font-extrabold text-slate-900 dark:text-white">
                            {{ $item->tanggal?->translatedFormat('d M Y') }} • {{ $jam }}
                        </span>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">{{ $item->judul }}</h3>
                    <div class="text-xs text-slate-600 dark:text-slate-300 space-y-1">
                        <div><i class="fa-solid fa-location-dot w-4 text-slate-400"></i> {{ $item->tempat }}</div>
                        @if ($item->asal_undangan)
                            <div><i class="fa-solid fa-building w-4 text-slate-400"></i> {{ $item->asal_undangan }}</div>
                        @endif
                    </div>
                    @if ($disposisi?->instruksi)
                        <div class="rounded-lg bg-amber-50 dark:bg-amber-500/15 border border-amber-200/70 dark:border-amber-500/30 px-3 py-1.5 text-[11px] font-bold text-amber-800 dark:text-amber-300">
                            Disposisi: {{ $disposisi->instruksi }}
                        </div>
                    @endif
                    @if ($item->keterangan)
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item->keterangan }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if ($upcomingAgendas->isNotEmpty())
        <div class="space-y-2.5">
            <h2 class="text-xs font-extrabold uppercase tracking-wider text-sky-600 dark:text-sky-400">
                Agenda Mendatang ({{ $upcomingAgendas->count() }})
            </h2>
            @foreach ($upcomingAgendas as $item)
                @php
                    $disposisi = $item->disposisis->first();
                    $jam = substr((string) $item->jam_mulai, 0, 5) . ($item->jam_selesai ? ' - ' . substr((string) $item->jam_selesai, 0, 5) : ' - Selesai');
                @endphp
                <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-4 space-y-2 shadow-sm">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400">
                            MENDATANG
                        </span>
                        <span class="text-xs font-extrabold text-slate-900 dark:text-white">
                            {{ $item->tanggal?->translatedFormat('d M Y') }} • {{ $jam }}
                        </span>
                    </div>
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">{{ $item->judul }}</h3>
                    <div class="text-xs text-slate-600 dark:text-slate-300 space-y-1">
                        <div><i class="fa-solid fa-location-dot w-4 text-slate-400"></i> {{ $item->tempat }}</div>
                        @if ($item->asal_undangan)
                            <div><i class="fa-solid fa-building w-4 text-slate-400"></i> {{ $item->asal_undangan }}</div>
                        @endif
                    </div>
                    @if ($disposisi?->instruksi)
                        <div class="rounded-lg bg-amber-50 dark:bg-amber-500/15 border border-amber-200/70 dark:border-amber-500/30 px-3 py-1.5 text-[11px] font-bold text-amber-800 dark:text-amber-300">
                            Disposisi: {{ $disposisi->instruksi }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
