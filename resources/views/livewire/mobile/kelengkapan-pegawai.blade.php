<div class="p-4 pb-28 space-y-4 text-slate-800 dark:text-slate-100" x-data="{ copied: false }">
    <div class="flex items-center gap-2.5">
        <a href="{{ route('mobile.profil') }}" wire:navigate
            class="w-9 h-9 rounded-xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 flex items-center justify-center text-slate-600 dark:text-slate-200">
            <i class="fa-solid fa-arrow-left text-xs"></i>
        </a>
        <div>
            <h1 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">Kelengkapan Data Pegawai</h1>
            <p class="text-[11px] text-slate-500 dark:text-zinc-400">Daftar pegawai yang belum melengkapi data kepegawaian</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 p-3.5">
            <div class="text-[11px] text-slate-500 dark:text-zinc-400">Belum Lengkap</div>
            <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-0.5">{{ $belumLengkap->count() }} <span class="text-xs font-semibold">pegawai</span></div>
        </div>
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 p-3.5">
            <div class="text-[11px] text-slate-500 dark:text-zinc-400">Total Pegawai Aktif</div>
            <div class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5">{{ $totalPegawai }} <span class="text-xs font-semibold">pegawai</span></div>
        </div>
    </div>

    @if ($belumLengkap->isNotEmpty())
        <div class="grid grid-cols-2 gap-2">
            <a href="https://wa.me/?text={{ urlencode($waText) }}" target="_blank" rel="noopener"
                class="py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span>Kirim ke WhatsApp</span>
            </a>
            <button type="button"
                @click="navigator.clipboard.writeText(@js($waText)); copied = true; setTimeout(() => copied = false, 2000);"
                class="py-2.5 px-3 rounded-xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 text-slate-700 dark:text-zinc-200 text-xs font-bold flex items-center justify-center gap-1.5 cursor-pointer">
                <i class="fa-regular fa-copy"></i>
                <span x-text="copied ? 'Tersalin!' : 'Salin Daftar'"></span>
            </button>
        </div>
    @endif

    <div class="space-y-2.5">
        @forelse ($belumLengkap as $item)
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 p-3.5 space-y-2">
                <div>
                    <div class="text-sm font-bold text-slate-900 dark:text-white">{{ $item['name'] }}</div>
                    @if ($item['nip'])
                        <div class="text-[11px] text-slate-400 font-mono">NIP. {{ $item['nip'] }}</div>
                    @endif
                </div>
                <div class="flex flex-wrap gap-1.5">
                    @foreach ($item['kurang'] as $k)
                        <span class="px-2 py-0.5 rounded-full bg-rose-50 dark:bg-rose-500/15 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-500/30 text-[10px] font-bold">
                            Belum: {{ $k }}
                        </span>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 p-8 text-center">
                <i class="fa-solid fa-circle-check text-3xl text-emerald-500 mb-2"></i>
                <p class="text-sm font-bold text-slate-800 dark:text-white">Semua Data Pegawai Lengkap!</p>
            </div>
        @endforelse
    </div>
</div>
