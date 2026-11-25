<div class="pb-24">
    <div
        class="sticky top-0 z-20 bg-white/90 dark:bg-zinc-950/90 backdrop-blur-md border-b border-zinc-200 dark:border-zinc-800 px-4 py-3 flex items-center gap-3">
        <a href="{{ route('mobile.surat-tugas.index') }}" wire:navigate
            class="w-9 h-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-700 dark:text-zinc-200">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Buat Surat Tugas</h1>
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Penugasan Tim Kreatif</p>
        </div>
    </div>

    <form wire:submit="save" class="p-4 space-y-4">
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-4 space-y-4">
            <div class="text-xs font-bold uppercase tracking-wider text-teal-600 dark:text-teal-400">Dasar Surat
                Undangan (Opsional)</div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nomor Surat
                    Undangan</label>
                <input type="text" wire:model="dasar_surat_undangan" placeholder="Contoh: 005/123/Kominfo-2026"
                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2.5 text-sm text-zinc-900 dark:text-zinc-100">
                @error('dasar_surat_undangan')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Asal Surat</label>
                <input type="text" wire:model="asal_surat_undangan"
                    placeholder="Contoh: Sekretariat Daerah Kab. Sijunjung"
                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2.5 text-sm text-zinc-900 dark:text-zinc-100">
                @error('asal_surat_undangan')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Perihal Surat / Nama
                    Kegiatan <span class="text-rose-500">*</span></label>
                <input type="text" wire:model="perihal_surat_undangan" placeholder="Contoh: Undangan Rapat evaluasi"
                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2.5 text-sm text-zinc-900 dark:text-zinc-100">
                @error('perihal_surat_undangan')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Tanggal
                        Surat</label>
                    <x-mobile.date-picker wire:model="tanggal_surat_undangan" placeholder="Pilih tanggal..." />
                    @error('tanggal_surat_undangan')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Jam Acara</label>
                    <button type="button" @click="open = !open"
                        class="w-full h-11 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 text-sm flex items-center justify-between text-left transition-all focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                        <span class="{{ $jam_acara ? 'text-zinc-900 dark:text-zinc-100 font-medium' : 'text-zinc-400' }}">
                            {{ $jam_acara ? $jam_acara . ' WIB' : 'Pilih jam...' }}
                        </span>
                        <i class="fa-regular fa-clock text-sky-500 text-sm"></i>
                    </button>
                    <div x-show="open" x-transition.opacity.duration.150ms style="display: none;"
                        class="absolute right-0 z-30 mt-1.5 w-64 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-2.5 shadow-xl">
                        <div class="grid grid-cols-3 gap-1.5">
                            @foreach ($jamOptions as $jam)
                                <button type="button"
                                    wire:click="$set('jam_acara', '{{ $jam }}')"
                                    @click="open = false"
                                    class="py-2 px-1 rounded-lg text-xs font-semibold transition-colors {{ $jam_acara === $jam ? 'bg-teal-600 text-white' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 hover:bg-teal-50 dark:hover:bg-teal-500/20' }}">
                                    {{ $jam }}
                                </button>
                            @endforeach
                        </div>
                        @if ($jam_acara)
                            <button type="button" wire:click="$set('jam_acara', '')" @click="open = false"
                                class="mt-2 w-full py-1.5 rounded-lg text-[11px] font-semibold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10">
                                Hapus Pilihan Jam
                            </button>
                        @endif
                    </div>
                    @error('jam_acara')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Lokasi Acara / Kegiatan
                    <span class="text-rose-500">*</span></label>
                <input type="text" wire:model="lokasi" placeholder="Contoh: Geopark Silokek"
                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2.5 text-sm text-zinc-900 dark:text-zinc-100">
                @error('lokasi')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Tanggal Mulai
                        Kegiatan <span class="text-rose-500">*</span></label>
                    <x-mobile.date-picker wire:model="tanggal_mulai" placeholder="Pilih tanggal..." />
                    @error('tanggal_mulai')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Tanggal Selesai
                        Kegiatan <span class="text-rose-500">*</span></label>
                    <x-mobile.date-picker wire:model="tanggal_selesai" placeholder="Pilih tanggal..." />
                    @error('tanggal_selesai')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Upload Bukti Surat
                    Undangan</label>
                <input type="file" wire:model="bukti_surat_undangan" accept=".pdf,.jpg,.jpeg,.png,.webp"
                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2 text-xs text-zinc-700 dark:text-zinc-300">
                <p class="text-[11px] text-zinc-500 mt-1">Format: PDF, JPG, PNG, atau WEBP (maks. 5 MB).</p>
                @error('bukti_surat_undangan')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-4 space-y-4">
            <div class="text-xs font-bold uppercase tracking-wider text-teal-600 dark:text-teal-400">Detail Kegiatan &
                Tim Kreatif</div>

            <div x-data="{ open: false, q: '' }" @click.outside="open = false" class="relative">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                        Penanggung Jawab Bidang <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-teal-50 dark:bg-teal-500/15 text-teal-700 dark:text-teal-300">
                        {{ count($penanggung_jawab_ids) }} dipilih
                    </span>
                </div>
                <button type="button" @click="open = !open"
                    class="w-full min-h-11 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2 text-left flex items-center justify-between gap-2">
                    <div class="flex flex-wrap gap-1.5 min-w-0">
                        @forelse ($penanggungJawabs->whereIn('id', $penanggung_jawab_ids) as $selectedPj)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-teal-50 dark:bg-teal-500/20 text-teal-700 dark:text-teal-300 text-[11px] font-semibold">
                                {{ $selectedPj->name }}
                            </span>
                        @empty
                            <span class="text-sm text-zinc-400">Pilih penanggung jawab...</span>
                        @endforelse
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs text-zinc-400 shrink-0"></i>
                </button>
                <div x-show="open" x-transition.opacity.duration.150ms style="display: none;"
                    class="mt-1.5 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-2.5 shadow-xl">
                    <input type="search" x-model="q" placeholder="Cari nama atau jabatan..."
                        class="w-full h-9 mb-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 text-xs text-zinc-900 dark:text-zinc-100 outline-none focus:border-teal-500">
                    <div class="max-h-44 overflow-y-auto space-y-1">
                        @foreach ($penanggungJawabs as $pj)
                            <label x-show="!q || '{{ strtolower($pj->name . ' ' . ($pj->jabatan?->nama ?? '')) }}'.includes(q.toLowerCase())"
                                class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-800/70 cursor-pointer">
                                <input type="checkbox" wire:model.live="penanggung_jawab_ids" value="{{ $pj->id }}"
                                    class="rounded border-zinc-300 text-teal-600 focus:ring-teal-500">
                                <div class="min-w-0">
                                    <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200 truncate">{{ $pj->name }}</div>
                                    @if ($pj->jabatan?->nama)
                                        <div class="text-[10px] text-zinc-500 dark:text-zinc-400 truncate">{{ $pj->jabatan->nama }}</div>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
                @error('penanggung_jawab_ids')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div x-data="{ open: false, q: '' }" @click.outside="open = false" class="relative">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Personil Tim Kreatif</label>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-teal-50 dark:bg-teal-500/15 text-teal-700 dark:text-teal-300">
                        {{ count($pegawai_ids) }} dipilih
                    </span>
                </div>
                <button type="button" @click="open = !open"
                    class="w-full min-h-11 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2 text-left flex items-center justify-between gap-2">
                    <div class="flex flex-wrap gap-1.5 min-w-0">
                        @forelse ($timKreatif->whereIn('id', $pegawai_ids) as $selectedTim)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-teal-50 dark:bg-teal-500/20 text-teal-700 dark:text-teal-300 text-[11px] font-semibold">
                                {{ $selectedTim->name }}
                            </span>
                        @empty
                            <span class="text-sm text-zinc-400">Pilih personil tim kreatif...</span>
                        @endforelse
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs text-zinc-400 shrink-0"></i>
                </button>
                <div x-show="open" x-transition.opacity.duration.150ms style="display: none;"
                    class="mt-1.5 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-2.5 shadow-xl">
                    <input type="search" x-model="q" placeholder="Cari personil tim kreatif..."
                        class="w-full h-9 mb-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 text-xs text-zinc-900 dark:text-zinc-100 outline-none focus:border-teal-500">
                    <div class="max-h-48 overflow-y-auto space-y-1">
                        @forelse ($timKreatif as $anggota)
                            <label x-show="!q || '{{ strtolower($anggota->name . ' ' . $anggota->jabatan_tim_kreatif) }}'.includes(q.toLowerCase())"
                                class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-800/70 cursor-pointer">
                                <input type="checkbox" wire:model.live="pegawai_ids" value="{{ $anggota->id }}"
                                    class="rounded border-zinc-300 text-teal-600 focus:ring-teal-500">
                                <div class="min-w-0">
                                    <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200 truncate">{{ $anggota->name }}</div>
                                    <div class="text-[10px] text-zinc-500 dark:text-zinc-400 truncate">{{ $anggota->jabatan_tim_kreatif }}</div>
                                </div>
                            </label>
                        @empty
                            <p class="text-xs text-zinc-500 text-center py-2">Belum ada data Tim Kreatif.</p>
                        @endforelse
                    </div>
                </div>
                @error('pegawai_ids')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Keterangan</label>
                <textarea wire:model="keterangan" rows="3" placeholder="Catatan tambahan..."
                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2.5 text-sm text-zinc-900 dark:text-zinc-100"></textarea>
                @error('keterangan')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <button type="submit" wire:loading.attr="disabled"
            class="w-full py-3 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm shadow-lg shadow-teal-600/20 active:scale-[0.99] transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-paper-plane"></i>
            <span wire:loading.remove wire:target="save">Simpan Surat Tugas</span>
            <span wire:loading wire:target="save">Menyimpan...</span>
        </button>
    </form>
</div>
