<div class="pb-24">
    <div class="sticky top-0 z-20 bg-white/90 dark:bg-zinc-950/90 backdrop-blur-md border-b border-zinc-200 dark:border-zinc-800 px-4 py-3 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('mobile.dashboard') }}" wire:navigate
                class="w-9 h-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-700 dark:text-zinc-200">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Agenda Kegiatan</h1>
                <p class="text-[13px] text-zinc-500 dark:text-zinc-400">Jadwal kegiatan &amp; disposisi Anda</p>
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
                        <span class="px-2 py-0.5 rounded-md text-xs font-extrabold bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
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
                        <div class="rounded-lg bg-amber-50 dark:bg-amber-500/15 border border-amber-200/70 dark:border-amber-500/30 px-3 py-1.5 text-[13px] font-bold text-amber-800 dark:text-amber-300">
                            Disposisi: {{ $disposisi->instruksi }}
                        </div>
                    @endif
                    @if ($item->keterangan)
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $item->keterangan }}</p>
                    @endif
                    @if ($item->lampiran_file)
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($item->lampiran_file) }}" target="_blank" rel="noopener"
                            class="inline-flex items-center gap-1.5 text-[13px] font-bold text-sky-600 dark:text-sky-400 hover:underline">
                            <i class="fa-solid fa-paperclip"></i>
                            <span>Lihat Lampiran Surat</span>
                        </a>
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
                        <span class="px-2 py-0.5 rounded-md text-xs font-extrabold bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400">
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
                        <div class="rounded-lg bg-amber-50 dark:bg-amber-500/15 border border-amber-200/70 dark:border-amber-500/30 px-3 py-1.5 text-[13px] font-bold text-amber-800 dark:text-amber-300">
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
                    @error('judul') <p class="text-[13px] text-rose-500 mt-1">{{ $message }}</p> @enderror
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
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Selesai *</label>
                        <input type="time" wire:model="jam_selesai"
                            class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-2.5 text-xs text-zinc-900 dark:text-zinc-100">
                        @error('jam_selesai') <p class="text-[13px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Tempat *</label>
                    <input type="text" wire:model="tempat" placeholder="Lokasi kegiatan..."
                        class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 text-xs text-zinc-900 dark:text-zinc-100">
                    @error('tempat') <p class="text-[13px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Asal Undangan / Penyelenggara *</label>
                    <input type="text" wire:model="asal_undangan" placeholder="Nama instansi / penyelenggara..."
                        class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 text-xs text-zinc-900 dark:text-zinc-100">
                    @error('asal_undangan') <p class="text-[13px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Keterangan *</label>
                    <textarea wire:model="keterangan" rows="2" placeholder="Keterangan agenda..."
                        class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 p-2.5 text-xs text-zinc-900 dark:text-zinc-100"></textarea>
                    @error('keterangan') <p class="text-[13px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div x-data="{ open: false, q: '' }" @click.outside="open = false" class="relative">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300">Pegawai yang Diperintah (Disposisi) *</label>
                        <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300">
                            {{ count($disposisi_user_ids) }} dipilih
                        </span>
                    </div>
                    <button type="button" @click="open = !open"
                        class="w-full min-h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 py-2 text-left flex items-center justify-between gap-2 text-xs">
                        <span class="text-zinc-500 truncate">
                            {{ count($disposisi_user_ids) > 0 ? count($disposisi_user_ids) . ' pegawai dipilih' : 'Pilih pegawai yang ditugaskan...' }}
                        </span>
                        <i class="fa-solid fa-chevron-down text-zinc-400 text-xs"></i>
                    </button>
                    <div x-show="open" x-cloak
                        class="mt-1.5 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-2 shadow-lg">
                        <input type="search" x-model="q" placeholder="Cari nama atau jabatan..."
                            class="w-full h-8 mb-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-2.5 text-xs text-zinc-900 dark:text-zinc-100">
                        <div class="max-h-40 overflow-y-auto space-y-1">
                            @foreach ($pegawais as $p)
                                <label x-show="!q || '{{ strtolower($p->name . ' ' . ($p->jabatan?->nama ?? '')) }}'.includes(q.toLowerCase())"
                                    class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800/70 cursor-pointer">
                                    <input type="checkbox" wire:model.live="disposisi_user_ids" value="{{ $p->id }}"
                                        class="rounded border-zinc-300 text-sky-600 focus:ring-sky-500">
                                    <div class="min-w-0">
                                        <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200 truncate">{{ $p->name }}</div>
                                        @if ($p->jabatan?->nama)
                                            <div class="text-xs text-zinc-400 truncate">{{ $p->jabatan->nama }}</div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    @error('disposisi_user_ids') <p class="text-[13px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Instruksi Disposisi</label>
                    <input type="text" wire:model="instruksi" placeholder="Contoh: Hadir mewakili Kadis..."
                        class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 text-xs text-zinc-900 dark:text-zinc-100">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Foto Surat / PDF (Maks. 5MB)</label>
                    <input type="file" wire:model="lampiran_file" accept="image/jpeg,image/png,image/webp,application/pdf"
                        class="w-full text-xs text-zinc-600 dark:text-zinc-300 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                    <div wire:loading wire:target="lampiran_file" class="text-[13px] text-sky-600 mt-1">Mengunggah lampiran...</div>
                    @error('lampiran_file') <p class="text-[13px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" wire:loading.attr="disabled"
                    class="w-full h-10 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold cursor-pointer">
                    Simpan Agenda
                </button>
            </form>
        </div>
    @endif
</div>
