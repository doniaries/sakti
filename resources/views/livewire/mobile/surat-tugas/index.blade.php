<div class="pb-24">
    <div class="sticky top-0 z-20 bg-white/90 dark:bg-zinc-950/90 backdrop-blur-md border-b border-zinc-200 dark:border-zinc-800 px-4 py-3 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('mobile.dashboard') }}" wire:navigate class="w-9 h-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-700 dark:text-zinc-200">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Surat Tugas</h1>
                <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Daftar Penugasan Tim Kreatif</p>
            </div>
        </div>

        @if ($canCreate)
            <a href="{{ route('mobile.surat-tugas.create') }}" wire:navigate
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm active:scale-95 transition-all">
                <i class="fa-solid fa-plus"></i>
                <span>Buat Surat Tugas</span>
            </a>
        @endif
    </div>

    <div class="p-4 space-y-3">
        @if (session('success'))
            <div class="rounded-xl bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-800 px-3.5 py-2.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @forelse ($suratTugasList as $surat)
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-4 space-y-2.5 shadow-xs">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="text-[11px] font-mono text-zinc-500 dark:text-zinc-400">{{ $surat->nomor_surat ?? 'Draft' }}</div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 leading-snug mt-0.5">{{ $surat->nama_kegiatan }}</h3>
                    </div>
                    <span class="shrink-0 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $surat->status === 'terbit' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' }}">
                        {{ $surat->status ?? 'draft' }}
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-zinc-600 dark:text-zinc-400">
                    <span><i class="fa-regular fa-calendar mr-1 text-teal-600"></i>{{ $surat->tanggal_mulai?->format('d/m/Y') }}</span>
                    @if ($surat->jam_acara)
                        <span><i class="fa-regular fa-clock mr-1 text-teal-600"></i>{{ $surat->jam_acara }}</span>
                    @endif
                    @if ($surat->lokasi)
                        <span><i class="fa-solid fa-location-dot mr-1 text-teal-600"></i>{{ $surat->lokasi }}</span>
                    @endif
                </div>

                @if ($surat->status === 'terbit')
                    <div class="pt-1 flex items-center gap-2">
                        <a href="{{ route('surat-tugas.cetak-pdf', $surat) }}" target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 text-xs font-semibold">
                            <i class="fa-solid fa-file-pdf text-rose-500"></i>
                            <span>Lihat Surat</span>
                        </a>
                    </div>
                @endif
            </div>
        @empty
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-8 text-center">
                <i class="fa-solid fa-file-signature text-3xl text-zinc-300 dark:text-zinc-700 mb-2"></i>
                <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Belum ada Surat Tugas</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Daftar surat tugas akan muncul di sini.</p>
            </div>
        @endforelse
    </div>
</div>
