<div class="pb-28">
    <div class="sticky top-0 z-20 bg-white/90 dark:bg-zinc-950/90 backdrop-blur-md border-b border-zinc-200/80 dark:border-zinc-800 px-4 py-3 flex items-center gap-3">
        <a href="{{ route('mobile.surat-tugas.index') }}" wire:navigate
            class="w-9 h-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-700 dark:text-zinc-200 active:scale-95 transition-transform">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div class="min-w-0">
            <h1 class="text-base font-bold text-zinc-900 dark:text-zinc-100 truncate">Buat Surat Tugas</h1>
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Lengkapi 3 langkah singkat di bawah ini</p>
        </div>
    </div>

    <form wire:submit="save" class="p-4 space-y-4">
        {{-- Langkah 1: Informasi Utama Kegiatan --}}
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 p-4 space-y-4 shadow-xs">
            <div class="flex items-center gap-2.5 pb-2 border-b border-zinc-100 dark:border-zinc-800">
                <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center shrink-0">1</span>
                <div>
                    <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Informasi Kegiatan</h2>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Detail acara yang akan diliput / dilaksanakan</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                    Nama / Perihal Kegiatan <span class="text-rose-500">*</span>
                </label>
                <input type="text" wire:model="perihal_surat_undangan" placeholder="Contoh: Liputan Rapat Paripurna DPRD"
                    class="w-full h-11 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-950 px-3.5 text-sm text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-zinc-900 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none transition-all">
                @error('perihal_surat_undangan') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                    Lokasi Acara <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <i class="fa-solid fa-location-dot text-xs text-teal-600 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" wire:model="lokasi" placeholder="Contoh: Geopark Silokek"
                        class="w-full h-11 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-950 pl-9 pr-3.5 text-sm text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-zinc-900 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none transition-all">
                </div>
                @error('lokasi') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                        Tanggal Mulai <span class="text-rose-500">*</span>
                    </label>
                    <x-mobile.date-picker wire:model="tanggal_mulai" placeholder="Pilih tanggal..." />
                    @error('tanggal_mulai') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                        Tanggal Selesai <span class="text-rose-500">*</span>
                    </label>
                    <x-mobile.date-picker wire:model="tanggal_selesai" placeholder="Pilih tanggal..." />
                    @error('tanggal_selesai') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Jam Acara</label>
                <button type="button" @click="open = !open"
                    class="w-full h-11 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 text-sm flex items-center justify-between text-left transition-all focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                    <span class="{{ $jam_acara ? 'text-zinc-900 dark:text-zinc-100 font-medium' : 'text-zinc-400' }}">
                        {{ $jam_acara ? $jam_acara . ' WIB' : 'Pilih jam...' }}
                    </span>
                    <i class="fa-regular fa-clock text-sky-500 text-sm"></i>
                </button>
                <div x-show="open" x-transition.opacity.duration.150ms style="display: none;"
                    class="absolute left-0 right-0 z-30 mt-1.5 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-2.5 shadow-xl">
                    <div class="grid grid-cols-4 gap-1.5">
                        @foreach ($jamOptions as $jam)
                            <button type="button" wire:click="$set('jam_acara', '{{ $jam }}')" @click="open = false"
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
                @error('jam_acara') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Langkah 2: Dasar Surat Tugas --}}
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 p-4 space-y-4 shadow-xs">
            <div class="flex items-center gap-2.5 pb-2 border-b border-zinc-100 dark:border-zinc-800">
                <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center shrink-0">2</span>
                <div>
                    <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Dasar Surat Tugas</h2>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Pilih apakah ada surat dasar atau perintah lisan pimpinan</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <button type="button" wire:click="$set('jenis_dasar', 'lisan')"
                    class="py-2.5 px-3 rounded-xl border text-xs font-semibold transition-all text-center {{ $jenis_dasar === 'lisan' ? 'border-teal-600 bg-teal-50 dark:bg-teal-500/15 text-teal-700 dark:text-teal-300' : 'border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400' }}">
                    <i class="fa-solid fa-microphone-lines mr-1"></i> Perintah Lisan
                </button>
                <button type="button" wire:click="$set('jenis_dasar', 'ada_surat')"
                    class="py-2.5 px-3 rounded-xl border text-xs font-semibold transition-all text-center {{ $jenis_dasar === 'ada_surat' ? 'border-teal-600 bg-teal-50 dark:bg-teal-500/15 text-teal-700 dark:text-teal-300' : 'border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400' }}">
                    <i class="fa-solid fa-file-lines mr-1"></i> Ada Surat Dasar
                </button>
            </div>

            @if ($jenis_dasar === 'lisan')
                <div class="rounded-xl bg-teal-50/70 dark:bg-teal-500/10 border border-teal-200/70 dark:border-teal-800/50 px-3.5 py-2.5 text-xs text-teal-800 dark:text-teal-200">
                    <i class="fa-solid fa-circle-info mr-1"></i> Dasar surat akan dicatat sebagai <strong>Perintah Lisan Pimpinan</strong>.
                </div>
            @else
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Nomor Surat Dasar</label>
                        <input type="text" wire:model="dasar_surat_undangan" placeholder="Contoh: 005/123/Kominfo-2026"
                            class="w-full h-11 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-950 px-3.5 text-sm text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-zinc-900 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none transition-all">
                        @error('dasar_surat_undangan') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Asal Surat</label>
                            <input type="text" wire:model="asal_surat_undangan" placeholder="Contoh: Setda"
                                class="w-full h-11 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-950 px-3 text-sm text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-zinc-900 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none transition-all">
                            @error('asal_surat_undangan') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Tanggal Surat</label>
                            <x-mobile.date-picker wire:model="tanggal_surat_undangan" placeholder="Pilih tanggal..." />
                            @error('tanggal_surat_undangan') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Upload Bukti Surat Dasar</label>
                        <label class="flex flex-col items-center justify-center gap-1.5 rounded-xl border-2 border-dashed border-zinc-200 dark:border-zinc-700 bg-zinc-50/70 dark:bg-zinc-950 px-4 py-4 text-center cursor-pointer hover:border-teal-500 transition-colors">
                            <i class="fa-solid fa-cloud-arrow-up text-xl text-teal-600"></i>
                            <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                {{ $bukti_surat_undangan ? $bukti_surat_undangan->getClientOriginalName() : 'Ketuk untuk pilih file surat dasar' }}
                            </span>
                            <span class="text-[10px] text-zinc-500">PDF, JPG, PNG, WEBP (Maks. 5 MB)</span>
                            <input type="file" wire:model="bukti_surat_undangan" accept=".pdf,.jpg,.jpeg,.png,.webp" class="hidden">
                        </label>
                        <div wire:loading wire:target="bukti_surat_undangan" class="text-xs text-teal-600 font-medium mt-1">
                            <i class="fa-solid fa-spinner fa-spin mr-1"></i> Mengunggah file...
                        </div>
                        @error('bukti_surat_undangan') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            @endif
        </div>

        {{-- Langkah 3: Tim yang Bertugas --}}
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 p-4 space-y-4 shadow-xs">
            <div class="flex items-center gap-2.5 pb-2 border-b border-zinc-100 dark:border-zinc-800">
                <span class="w-6 h-6 rounded-full bg-teal-600 text-white text-xs font-bold flex items-center justify-center shrink-0">3</span>
                <div>
                    <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Tim yang Bertugas</h2>
                    <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Pilih penanggung jawab & personil tim kreatif</p>
                </div>
            </div>

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
                <div x-show="open"
                    x-effect="if (open) $nextTick(() => $el.scrollIntoView({ behavior: 'smooth', block: 'center' }))"
                    x-transition.opacity.duration.150ms style="display: none;"
                    class="mt-1.5 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-2.5 shadow-xl">
                    <input type="search" x-model="q" placeholder="Cari nama atau jabatan..."
                        class="w-full h-9 mb-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 text-xs text-zinc-900 dark:text-zinc-100 outline-none focus:border-teal-500">
                    <div class="max-h-60 overflow-y-auto space-y-1">
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
                @error('penanggung_jawab_ids') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
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
                            <label x-show="!q || '{{ strtolower($anggota->name . ' ' . ($anggota->jabatan_tim_kreatif?->value ?? '')) }}'.includes(q.toLowerCase())"
                                class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-800/70 cursor-pointer">
                                <input type="checkbox" wire:model.live="pegawai_ids" value="{{ $anggota->id }}"
                                    class="rounded border-zinc-300 text-teal-600 focus:ring-teal-500">
                                <div class="min-w-0">
                                    <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200 truncate">{{ $anggota->name }}</div>
                                    <div class="text-[10px] text-zinc-500 dark:text-zinc-400 truncate">{{ $anggota->jabatan_tim_kreatif?->value }}</div>
                                </div>
                            </label>
                        @empty
                            <p class="text-xs text-zinc-500 text-center py-2">Belum ada data Tim Kreatif.</p>
                        @endforelse
                    </div>
                </div>
                @error('pegawai_ids') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Keterangan Tambahan</label>
                <textarea wire:model="keterangan" rows="3" placeholder="Catatan tambahan (opsional)..."
                    class="w-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-950 p-3 text-sm text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-zinc-900 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 outline-none transition-all"></textarea>
                @error('keterangan') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div x-data="{ open: false, q: '' }" @click.outside="open = false" class="relative">
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">
                    Pejabat Penandatangan (Sekretaris / Kabid)
                </label>
                @php
                    $selectedTtd = $penandatangans->firstWhere('id', $penandatangan_id);
                @endphp
                <button type="button" @click="open = !open"
                    class="w-full min-h-11 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2 text-left flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        @if ($selectedTtd)
                            <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200 truncate">{{ $selectedTtd->name }}</div>
                            @if ($selectedTtd->jabatan?->nama)
                                <div class="text-[10px] text-zinc-400 truncate">{{ $selectedTtd->jabatan->nama }}</div>
                            @endif
                        @else
                            <span class="text-sm text-zinc-400">Otomatis Kepala Dinas (Default)</span>
                        @endif
                    </div>
                    <i class="fa-solid fa-chevron-down text-xs text-zinc-400 shrink-0"></i>
                </button>
                <div x-show="open"
                    x-effect="if (open) $nextTick(() => $el.scrollIntoView({ behavior: 'smooth', block: 'center' }))"
                    x-transition.opacity.duration.150ms style="display: none;"
                    class="mt-1.5 rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-2.5 shadow-xl">
                    <input type="search" x-model="q" placeholder="Cari nama atau jabatan..."
                        class="w-full h-9 mb-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-950 px-3 text-xs text-zinc-900 dark:text-zinc-100 outline-none focus:border-teal-500">
                    <div class="max-h-60 overflow-y-auto space-y-1">
                        <label @click="$wire.set('penandatangan_id', null); open = false"
                            class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-800/70 cursor-pointer">
                            <input type="radio" name="penandatangan_id_radio" @checked(! $penandatangan_id)
                                class="border-zinc-300 text-teal-600 focus:ring-teal-500">
                            <div class="min-w-0">
                                <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Otomatis Kepala Dinas (Default)</div>
                            </div>
                        </label>
                        @foreach ($penandatangans as $ttd)
                            <label x-show="!q || '{{ strtolower($ttd->name . ' ' . ($ttd->jabatan?->nama ?? '')) }}'.includes(q.toLowerCase())"
                                @click="$wire.set('penandatangan_id', {{ $ttd->id }}); open = false"
                                class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-zinc-50 dark:hover:bg-zinc-800/70 cursor-pointer">
                                <input type="radio" name="penandatangan_id_radio" @checked((int) $penandatangan_id === (int) $ttd->id)
                                    class="border-zinc-300 text-teal-600 focus:ring-teal-500">
                                <div class="min-w-0">
                                    <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200 truncate">{{ $ttd->name }}</div>
                                    @if ($ttd->jabatan?->nama)
                                        <div class="text-[10px] text-zinc-400 truncate">{{ $ttd->jabatan->nama }}</div>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
                @error('penandatangan_id') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <button type="submit" wire:loading.attr="disabled"
            class="w-full h-12 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm shadow-lg shadow-teal-600/25 active:scale-[0.99] transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-paper-plane"></i>
            <span wire:loading.remove wire:target="save">Simpan Surat Tugas</span>
            <span wire:loading wire:target="save">Menyimpan...</span>
        </button>
    </form>
</div>
