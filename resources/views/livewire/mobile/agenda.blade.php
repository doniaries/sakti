<div class="pb-24">
    <div class="sticky top-0 z-20 bg-white/90 dark:bg-zinc-950/90 backdrop-blur-md border-b border-zinc-200 dark:border-zinc-800 px-4 py-3 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('mobile.dashboard') }}" wire:navigate
                class="w-9 h-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-700 dark:text-zinc-200 shrink-0">
                <span class="material-symbols-rounded text-[20px]">arrow_back</span>
            </a>
            <div class="min-w-0">
                <h1 class="text-base font-bold text-zinc-900 dark:text-zinc-100 truncate">Agenda Kegiatan</h1>
                <p class="text-[13px] text-zinc-500 dark:text-zinc-400 truncate">Jadwal kegiatan &amp; disposisi Anda</p>
            </div>
        </div>
        <a href="{{ route('mobile.agenda.create') }}" wire:navigate.hover
            class="shrink-0 whitespace-nowrap inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-sm active:scale-95 transition-all cursor-pointer">
            <span class="material-symbols-rounded text-[16px]">add</span>
            <span>Tambah Agenda</span>
        </a>
    </div>

    <div class="p-4 space-y-4">

    @if ($todayAgendas->isEmpty() && $upcomingAgendas->isEmpty() && $pastAgendas->isEmpty())
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-6 text-center">
            <span class="material-symbols-rounded text-4xl text-sky-500 mb-2">event_available</span>
            <p class="text-sm font-bold text-slate-800 dark:text-white">Belum ada agenda kegiatan</p>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Jadwal kegiatan dan disposisi Anda akan tampil di sini.</p>
        </div>
    @endif

    @php
        $sections = [
            [
                'title' => 'Agenda Hari Ini',
                'badge' => 'HARI INI',
                'headingClass' => 'text-emerald-600 dark:text-emerald-400',
                'badgeClass' => 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400',
                'cardClass' => '',
                'items' => $todayAgendas,
            ],
            [
                'title' => 'Agenda Mendatang',
                'badge' => 'MENDATANG',
                'headingClass' => 'text-sky-600 dark:text-sky-400',
                'badgeClass' => 'bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400',
                'cardClass' => '',
                'items' => $upcomingAgendas,
            ],
            [
                'title' => 'Riwayat Agenda',
                'badge' => 'SELESAI',
                'headingClass' => 'text-slate-500 dark:text-slate-400',
                'badgeClass' => 'bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-300',
                'cardClass' => 'opacity-90',
                'items' => $pastAgendas,
            ],
        ];
    @endphp

    @foreach ($sections as $section)
        @if ($section['items']->isNotEmpty())
            <div class="space-y-2.5">
                <h2 class="text-xs font-extrabold uppercase tracking-wider {{ $section['headingClass'] }}">
                    {{ $section['title'] }} ({{ $section['items']->count() }})
                </h2>
                @foreach ($section['items'] as $item)
                    @php
                        $myDisposisi = $item->disposisis->firstWhere('user_id', auth()->id());
                        $sharedInstruksi = $myDisposisi?->instruksi ?: $item->disposisis->pluck('instruksi')->filter()->first();
                        $jam = substr((string) $item->jam_mulai, 0, 5) . ($item->jam_selesai ? ' - ' . substr((string) $item->jam_selesai, 0, 5) : ' - Selesai');
                    @endphp
                    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-4 space-y-2 shadow-sm {{ $section['cardClass'] }}">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="px-2 py-0.5 rounded-md text-xs font-extrabold {{ $section['badgeClass'] }}">
                                    {{ $section['badge'] }}
                                </span>
                                <span class="text-xs font-extrabold text-slate-900 dark:text-white truncate">
                                    {{ $item->tanggal?->translatedFormat('d M Y') }} • {{ $jam }}
                                </span>
                            </div>
                            @if ($isKadisOrAdmin || (int) $item->created_by === (int) auth()->id())
                                <div class="flex items-center gap-1 shrink-0">
                                    <a href="{{ route('mobile.agenda.edit', $item->id) }}" wire:navigate.hover title="Ubah Agenda"
                                        class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-zinc-800 hover:bg-sky-50 text-slate-600 dark:text-zinc-300 hover:text-sky-600 flex items-center justify-center transition">
                                        <span class="material-symbols-rounded text-[18px]">edit_square</span>
                                    </a>
                                    <button type="button" title="Hapus Agenda"
                                        @click="$dispatch('sakti-confirm', { title: 'Hapus Agenda?', message: 'Agenda kegiatan beserta daftar disposisinya akan dihapus.', wireId: '{{ $this->getId() }}', method: 'hapusAgenda', params: [{{ $item->id }}] })"
                                        class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-zinc-800 hover:bg-rose-50 text-slate-600 dark:text-zinc-300 hover:text-rose-600 flex items-center justify-center transition cursor-pointer">
                                        <span class="material-symbols-rounded text-[18px]">delete</span>
                                    </button>
                                </div>
                            @endif
                        </div>
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">{{ $item->judul }}</h3>
                        @if ($item->keterangan)
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item->keterangan }}</p>
                        @endif
                        <div class="flex items-start justify-between gap-3">
                            <div class="text-xs text-slate-600 dark:text-slate-300 space-y-1 min-w-0">
                                <div class="flex items-center gap-1.5"><span class="material-symbols-rounded text-[16px] text-slate-400">location_on</span><span>{{ $item->tempat }}</span></div>
                                @if ($item->asal_undangan)
                                    <div class="flex items-center gap-1.5"><span class="material-symbols-rounded text-[16px] text-slate-400">apartment</span><span>{{ $item->asal_undangan }}</span></div>
                                @endif
                            </div>
                            @if ($item->lampiran_file)
                                @php
                                    $suratUrl = url('/media-file/' . ltrim((string) $item->lampiran_file, '/'));
                                @endphp
                                <a href="{{ $suratUrl }}" download target="_blank" rel="noopener"
                                    class="shrink-0 inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-sky-50 dark:bg-sky-500/15 border border-sky-200 dark:border-sky-500/30 text-sky-700 dark:text-sky-300 hover:bg-sky-100 text-xs font-bold transition">
                                    <span class="material-symbols-rounded text-[16px]">download</span>
                                    <span>Surat Undangan</span>
                                </a>
                            @endif
                        </div>
                        @if ($sharedInstruksi)
                            <div class="rounded-lg bg-amber-50 dark:bg-amber-500/15 border border-amber-200/70 dark:border-amber-500/30 px-3 py-1.5 text-[13px] font-bold text-amber-800 dark:text-amber-300">
                                Instruksi: {{ $sharedInstruksi }}
                            </div>
                        @endif
                        @if ($item->disposisis->isNotEmpty())
                            <div class="pt-1.5 border-t border-slate-100 dark:border-zinc-800 space-y-1.5">
                                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">
                                    Penerima Disposisi ({{ $item->disposisis->count() }})
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($item->disposisis as $disp)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-zinc-800 text-xs font-semibold text-slate-700 dark:text-zinc-200">
                                            <span class="material-symbols-rounded text-[14px] text-sky-500">person</span>
                                            <span>{{ $disp->user?->name ?? 'Pegawai' }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    @endforeach
    </div>
</div>
