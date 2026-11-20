<div class="p-5 pb-28 relative min-h-screen">
    <div class="flex items-center justify-between gap-2 mb-5">
        <div class="flex items-center min-w-0">
            <a href="{{ route('mobile.lhk.index') }}" wire:navigate
               class="mr-3 w-9 h-9 rounded-xl bg-white dark:bg-[#132338] border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-sky-600 transition shrink-0">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div class="min-w-0">
                <h1 class="text-lg font-extrabold text-slate-800 dark:text-white tracking-tight truncate">Bank Aktivitas Saya</h1>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">Daftar aktivitas pribadi untuk pengisian LHK</p>
            </div>
        </div>
    </div>

    <!-- Action Bar: Tambah & Tempel Massal -->
    <div class="grid grid-cols-2 gap-2.5 mb-4">
        <button type="button" wire:click="openCreate"
            class="py-2.5 px-3 rounded-xl bg-[#1B6CA8] hover:bg-[#155685] text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm active:scale-95 transition">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah Aktivitas</span>
        </button>
        <button type="button" wire:click="openBulk"
            class="py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center justify-center gap-1.5 shadow-sm active:scale-95 transition">
            <i class="fa-solid fa-paste"></i>
            <span>Tempel / Impor</span>
        </button>
    </div>

    <!-- Search -->
    <div class="mb-4 relative">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" wire:model.live.debounce.250ms="search" placeholder="Cari kode atau uraian aktivitas..."
            class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white dark:bg-[#132338]/90 border border-slate-200/80 dark:border-slate-700/60 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-sky-500">
    </div>

    @if ($myAktivitas->isEmpty())
        <div class="bg-white dark:bg-[#132338]/90 rounded-3xl p-8 text-center border border-slate-200/80 dark:border-slate-700/60 shadow-xs">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-cyan-300 flex items-center justify-center text-2xl mb-3">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-white text-sm">Bank Aktivitas Pribadi Kosong</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                Anda dapat menambahkan aktivitas satu per satu atau salin-tempel langsung daftar dari aplikasi lama (`bankaktifitas.html`).
            </p>
        </div>
    @else
        <div class="space-y-2.5">
            @foreach ($myAktivitas as $item)
                <div wire:key="bank-akt-{{ $item->id }}"
                    class="bg-white dark:bg-[#132338]/90 p-3.5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-xs space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-md bg-sky-50 text-sky-700 dark:bg-cyan-500/15 dark:text-cyan-300 font-mono text-[10px] font-extrabold">
                                #{{ $item->kode ?? $item->id }}
                            </span>
                            <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300 text-[10px] font-bold">
                                {{ $item->menit }} Menit
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" wire:click="openEdit({{ $item->id }})"
                                class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-sky-600 flex items-center justify-center text-xs transition" title="Ubah">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button type="button" wire:click="deleteItem({{ $item->id }})"
                                wire:confirm="Hapus aktivitas ini dari Bank Aktivitas Anda?"
                                class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-500/15 text-rose-500 hover:text-rose-700 flex items-center justify-center text-xs transition" title="Hapus">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                    <p class="text-xs font-semibold text-slate-800 dark:text-slate-100 leading-relaxed">
                        {{ $item->nama_aktivitas }}
                    </p>
                </div>
            @endforeach
        </div>
    @endif

    <!-- MODAL TAMBAH / EDIT AKTIVITAS -->
    @if ($showFormModal)
        <div class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-3 sm:p-4 bg-slate-950/60 backdrop-blur-xs"
            x-data @keydown.escape.window="$wire.closeForm()">
            <div class="w-full max-w-[408px] bg-white dark:bg-[#132338] rounded-3xl p-5 shadow-2xl border border-slate-200/90 dark:border-slate-700/80 space-y-4">
                <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-700/60">
                    <h3 class="font-extrabold text-slate-800 dark:text-white text-sm">
                        {{ $editingId ? 'Ubah Aktivitas' : 'Tambah Aktivitas Baru' }}
                    </h3>
                    <button type="button" wire:click="closeForm" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 flex items-center justify-center">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">Durasi (Menit)</label>
                    <input type="number" min="1" wire:model="menit"
                        class="w-full rounded-xl p-2.5 text-xs bg-slate-50 dark:bg-[#0F1E33] border border-slate-200 dark:border-slate-700">
                    @error('menit') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">Nama / Uraian Aktivitas</label>
                    <textarea rows="3" wire:model="nama_aktivitas" placeholder="Contoh: Mengikuti Apel Pagi"
                        class="w-full rounded-xl p-2.5 text-xs bg-slate-50 dark:bg-[#0F1E33] border border-slate-200 dark:border-slate-700"></textarea>
                    @error('nama_aktivitas') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                </div>

                <button type="button" wire:click="saveItem" wire:loading.attr="disabled"
                    class="w-full py-3 rounded-xl bg-[#1B6CA8] hover:bg-[#155685] text-white font-bold text-xs shadow-md transition">
                    Simpan Aktivitas
                </button>
            </div>
        </div>
    @endif

    <!-- MODAL TEMPEL / IMPOR MASSAL -->
    @if ($showBulkModal)
        <div class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-3 sm:p-4 bg-slate-950/60 backdrop-blur-xs"
            x-data @keydown.escape.window="$wire.closeBulk()">
            <div class="w-full max-w-[408px] bg-white dark:bg-[#132338] rounded-3xl p-5 shadow-2xl border border-slate-200/90 dark:border-slate-700/80 max-h-[85vh] overflow-y-auto space-y-4">
                <div class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-700/60">
                    <div>
                        <h3 class="font-extrabold text-slate-800 dark:text-white text-sm">Tempel / Impor Bank Aktivitas</h3>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400">Copy-paste langsung dari tabel bankaktifitas.html atau Excel</p>
                    </div>
                    <button type="button" wire:click="closeBulk" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 flex items-center justify-center">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">Durasi Default (jika tanpa angka menit)</label>
                    <input type="number" min="1" wire:model="bulkDefaultMenit"
                        class="w-full rounded-xl p-2.5 text-xs bg-slate-50 dark:bg-[#0F1E33] border border-slate-200 dark:border-slate-700">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 mb-1">Tempel Baris Aktivitas</label>
                    <textarea rows="7" wire:model="bulkRaw"
                        placeholder="1&#9;Mengikuti Apel Pagi&#9;30&#10;2&#9;Memimpin rapat/FGD&#9;180"
                        class="w-full rounded-xl p-2.5 text-xs bg-slate-50 dark:bg-[#0F1E33] border border-slate-200 dark:border-slate-700 font-mono"></textarea>
                    @error('bulkRaw') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                </div>

                <button type="button" wire:click="importBulk" wire:loading.attr="disabled"
                    class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition">
                    Proses & Simpan ke Bank Aktivitas Saya
                </button>
            </div>
        </div>
    @endif
</div>

