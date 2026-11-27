<div class="pb-24">
    <div class="sticky top-0 z-20 bg-white/90 dark:bg-zinc-950/90 backdrop-blur-md border-b border-zinc-200 dark:border-zinc-800 px-4 py-3 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('mobile.dashboard') }}" wire:navigate
                class="w-9 h-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-700 dark:text-zinc-200">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Agenda Kegiatan</h1>
                <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Jadwal kegiatan &amp; disposisi Anda</p>
            </div>
        </div>
        <button type="button" wire:click="openCreate"
            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-sm active:scale-95 transition-all cursor-pointer">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah Agenda</span>
        </button>
    </div>

    <div class="p-4 space-y-4">

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

    @if ($showForm)
        <div class="fixed inset-0 z-[100] bg-slate-900/65 backdrop-blur-xs flex items-end sm:items-center justify-center p-4"
            wire:click.self="$set('showForm', false)">
            <form wire:submit="simpanAgenda"
                class="w-full max-w-md rounded-3xl bg-white dark:bg-zinc-900 p-5 shadow-2xl border border-slate-100 dark:border-zinc-800 space-y-3 max-h-[85vh] overflow-y-auto">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-extrabold text-slate-800 dark:text-white">Tambah Agenda Kegiatan</h3>
                    <button type="button" wire:click="$set('showForm', false)"
                        class="w-8 h-8 rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-500 flex items-center justify-center cursor-pointer">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Judul Agenda *</label>
                    <input type="text" wire:model="judul" placeholder="Contoh: Rapat Koordinasi..."
                        class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 text-xs text-zinc-900 dark:text-zinc-100">
                    @error('judul') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Tanggal *</label>
                        <input type="date" wire:model="tanggal"
                            class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-2.5 text-xs text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Mulai *</label>
                        <input type="time" wire:model="jam_mulai"
                            class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-2.5 text-xs text-zinc-900 dark:text-zinc-100">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Selesai</label>
                        <input type="time" wire:model="jam_selesai"
                            class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-2.5 text-xs text-zinc-900 dark:text-zinc-100">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Tempat *</label>
                    <input type="text" wire:model="tempat" placeholder="Lokasi kegiatan..."
                        class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 text-xs text-zinc-900 dark:text-zinc-100">
                    @error('tempat') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Asal Undangan / Penyelenggara</label>
                    <input type="text" wire:model="asal_undangan" placeholder="Opsional..."
                        class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 text-xs text-zinc-900 dark:text-zinc-100">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Keterangan</label>
                    <textarea wire:model="keterangan" rows="2" placeholder="Catatan tambahan (opsional)..."
                        class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"></textarea>
                </div>

                <button type="submit" wire:loading.attr="disabled"
                    class="w-full h-10 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold cursor-pointer">
                    Simpan Agenda
                </button>
            </form>
        </div>
    @endif
</div>
