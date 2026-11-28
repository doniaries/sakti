<div class="pb-24">
    <div class="sticky top-0 z-20 bg-white/90 dark:bg-zinc-950/90 backdrop-blur-md border-b border-zinc-200 dark:border-zinc-800 px-4 py-3 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <a href="{{ route('mobile.dashboard') }}" wire:navigate
                class="w-9 h-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-700 dark:text-zinc-200 shrink-0">
                <span class="material-symbols-rounded text-[20px]">arrow_back</span>
            </a>
            <div class="min-w-0">
                <h1 class="text-base font-bold text-zinc-900 dark:text-zinc-100 truncate">Data Pegawai</h1>
                <p class="text-[13px] text-zinc-500 dark:text-zinc-400 truncate">Total {{ $totalAktif }} pegawai aktif</p>
            </div>
        </div>
        @if ($canManage)
            <button type="button" wire:click="openCreate"
                class="shrink-0 whitespace-nowrap inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-sm active:scale-95 transition cursor-pointer">
                <span class="material-symbols-rounded text-[16px]">person_add</span>
                <span>Tambah</span>
            </button>
        @endif
    </div>

    <div class="p-4 space-y-3.5">
        <div class="relative">
            <span class="material-symbols-rounded text-[18px] text-zinc-400 absolute left-3.5 top-1/2 -translate-y-1/2">search</span>
            <input type="search" wire:model.live.debounce.250ms="search" placeholder="Cari nama, NIP, jabatan, atau bidang..."
                class="w-full h-11 pl-10 pr-4 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 text-sm text-zinc-900 dark:text-zinc-100 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none shadow-2xs">
        </div>

        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-0.5">
            <button type="button" wire:click="$set('bidangFilter', '')"
                class="shrink-0 whitespace-nowrap px-3 py-1.5 rounded-full text-xs font-bold border transition cursor-pointer {{ $bidangFilter === '' ? 'bg-sky-600 border-sky-600 text-white shadow-xs' : 'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300' }}">
                Semua ({{ $totalAktif }})
            </button>
            @foreach ($bidangs as $bidang)
                <button type="button" wire:click="$set('bidangFilter', '{{ $bidang->id }}')"
                    class="shrink-0 whitespace-nowrap px-3 py-1.5 rounded-full text-xs font-bold border transition cursor-pointer {{ (string) $bidangFilter === (string) $bidang->id ? 'bg-sky-600 border-sky-600 text-white shadow-xs' : 'bg-white dark:bg-zinc-900 border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300' }}">
                    {{ $bidang->nama }} ({{ $bidang->users_count }})
                </button>
            @endforeach
        </div>

        <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400 px-1">
            <span>Menampilkan <strong class="text-zinc-800 dark:text-zinc-200">{{ $pegawais->count() }}</strong> pegawai</span>
        </div>

        @if ($pegawais->isEmpty())
            <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-6 text-center">
                <span class="material-symbols-rounded text-4xl text-sky-500 mb-2">group_off</span>
                <p class="text-sm font-bold text-slate-800 dark:text-white">Pegawai tidak ditemukan</p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Ubah kata kunci pencarian atau filter bidang.</p>
            </div>
        @else
            <div class="space-y-2.5">
                @foreach ($pegawais as $item)
                    @php
                        $namaLengkap = $item->name;
                        if ($item->gelar_depan && stripos($namaLengkap, $item->gelar_depan) === false) {
                            $namaLengkap = $item->gelar_depan . ' ' . $namaLengkap;
                        }
                        if ($item->gelar_belakang && stripos($namaLengkap, $item->gelar_belakang) === false) {
                            $namaLengkap .= ', ' . $item->gelar_belakang;
                        }
                    @endphp
                    <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-3.5 shadow-xs flex items-start gap-3">
                        <img src="{{ $item->avatar_image_url }}" alt="{{ $item->name }}"
                            class="w-11 h-11 rounded-full object-cover ring-2 ring-sky-100 dark:ring-zinc-800 shrink-0">
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white truncate">{{ trim($namaLengkap) }}</h3>
                                    <div class="text-xs font-mono text-slate-500 dark:text-zinc-400 truncate">
                                        NIP: {{ $item->nip ?: '-' }}
                                    </div>
                                </div>
                                @if ($canManage)
                                    <button type="button" wire:click="openEdit({{ $item->id }})" title="Ubah Data Pegawai"
                                        class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-zinc-800 hover:bg-sky-50 text-slate-600 dark:text-zinc-300 hover:text-sky-600 flex items-center justify-center shrink-0 transition cursor-pointer">
                                        <span class="material-symbols-rounded text-[18px]">edit_square</span>
                                    </button>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                                @if ($item->jabatan?->nama)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300 text-xs font-semibold">
                                        <span class="material-symbols-rounded text-[13px]">badge</span>
                                        <span>{{ $item->jabatan->nama }}</span>
                                    </span>
                                @endif
                                @if ($item->bidang?->nama)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300 text-xs font-semibold">
                                        <span class="material-symbols-rounded text-[13px]">apartment</span>
                                        <span>{{ $item->bidang->nama }}</span>
                                    </span>
                                @endif
                                @if (! $item->aktif)
                                    <span class="px-2 py-0.5 rounded-lg bg-rose-50 text-rose-700 text-xs font-bold">Nonaktif</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @if ($canManage && $showFormModal)
        <template x-teleport="body">
            <div class="fixed inset-0 z-[105] bg-slate-950/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4"
                wire:click.self="$set('showFormModal', false)">
                <div class="w-full max-w-md max-h-[90dvh] overflow-y-auto rounded-t-3xl sm:rounded-3xl bg-white dark:bg-zinc-900 p-5 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-extrabold text-slate-900 dark:text-white">
                            {{ $editingId ? 'Ubah Data Utama Pegawai' : 'Tambah Pegawai Baru' }}
                        </h2>
                        <button type="button" wire:click="$set('showFormModal', false)"
                            class="w-8 h-8 rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-500 flex items-center justify-center cursor-pointer">
                            <span class="material-symbols-rounded text-[18px]">close</span>
                        </button>
                    </div>

                    <form wire:submit="savePegawai" class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                            <input type="text" wire:model="name" class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 text-sm">
                            @error('name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">NIP</label>
                                <input type="text" wire:model="nip" class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 text-sm font-mono">
                                @error('nip') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Status Kerja</label>
                                <select wire:model="status_kerja" class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 text-sm">
                                    <option value="wfo">WFO</option>
                                    <option value="wfh">WFH</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Email <span class="text-rose-500">*</span></label>
                            <input type="email" wire:model="email" class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 text-sm">
                            @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Jabatan</label>
                                <select wire:model="jabatan_id" class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-2.5 text-xs">
                                    <option value="">-- Pilih Jabatan --</option>
                                    @foreach ($jabatans as $j)
                                        <option value="{{ $j->id }}">{{ $j->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Bidang</label>
                                <select wire:model="bidang_id" class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-2.5 text-xs">
                                    <option value="">-- Pilih Bidang --</option>
                                    @foreach ($bidangs as $b)
                                        <option value="{{ $b->id }}">{{ $b->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Atasan Langsung</label>
                            <select wire:model="atasan_id" class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-2.5 text-xs">
                                <option value="">-- Tanpa Atasan --</option>
                                @foreach ($atasans as $at)
                                    <option value="{{ $at->id }}">{{ $at->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">
                                Password {{ $editingId ? '(Kosongkan jika tidak diubah)' : '*' }}
                            </label>
                            <input type="password" wire:model="password" class="w-full h-10 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 text-sm">
                            @error('password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <label class="flex items-center gap-2 text-xs font-semibold text-zinc-700 dark:text-zinc-300 cursor-pointer">
                            <input type="checkbox" wire:model="aktif" class="rounded border-zinc-300 text-sky-600 focus:ring-sky-500">
                            <span>Akun Pegawai Aktif</span>
                        </label>

                        <div class="flex items-center gap-2 pt-2">
                            <button type="button" wire:click="$set('showFormModal', false)"
                                class="w-1/3 h-11 rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-200 text-xs font-bold cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" wire:loading.attr="disabled"
                                class="flex-1 h-11 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold cursor-pointer">
                                {{ $editingId ? 'Simpan Perubahan' : 'Tambah Pegawai' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    @endif
</div>
