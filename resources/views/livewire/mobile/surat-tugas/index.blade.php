<div class="pb-24">
    <x-mobile.page-header title="Surat Tugas" subtitle="Daftar Penugasan Tim Kreatif">
        @if ($canCreate)
            <x-slot:actions>
                <a href="{{ route('mobile.surat-tugas.create') }}" wire:navigate.hover
                    class="shrink-0 whitespace-nowrap inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold shadow-sm active:scale-95 transition-all">
                    <span class="material-symbols-rounded text-[16px]">add</span>
                    <span>Buat Surat Tugas</span>
                </a>
            </x-slot:actions>
        @endif
    </x-mobile.page-header>

    <div class="p-4 space-y-3">
        {{-- Ringkasan Penugasan Pribadi --}}
        <div class="grid grid-cols-2 gap-2.5">
            <div
                class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 p-3 flex items-center gap-3 shadow-xs">
                <div
                    class="w-9 h-9 rounded-xl bg-teal-50 dark:bg-teal-500/15 text-teal-600 dark:text-teal-400 flex items-center justify-center shrink-0">
                    <span class="material-symbols-rounded text-[20px]">task</span>
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Tugas Saya</div>
                    <div class="text-base font-extrabold text-zinc-900 dark:text-zinc-100 leading-tight">
                        {{ $myTotalTerbit }} <span class="text-[13px] font-medium text-zinc-500">kali</span></div>
                </div>
            </div>
            <div
                class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 p-3 flex items-center gap-3 shadow-xs">
                <div
                    class="w-9 h-9 rounded-xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                    <span class="material-symbols-rounded text-[20px]">calendar_month</span>
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                        Bulan Ini</div>
                    <div class="text-base font-extrabold text-zinc-900 dark:text-zinc-100 leading-tight">
                        {{ $myBulanIni }} <span class="text-[13px] font-medium text-zinc-500">kali</span></div>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div
                class="rounded-xl bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-800 px-3.5 py-2.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @forelse ($suratTugasList as $surat)
            <div
                class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-4 space-y-2.5 shadow-xs">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="text-[13px] font-mono text-zinc-500 dark:text-zinc-400">
                            {{ $surat->nomor_surat ?? 'Draft' }}</div>
                        <h3 class="text-sm font-bold text-zinc-900 dark:text-zinc-100 leading-snug mt-0.5">
                            {{ $surat->nama_kegiatan }}</h3>
                    </div>
                    <span
                        class="shrink-0 px-2 py-0.5 rounded-full text-xs font-bold uppercase {{ $surat->status === 'terbit' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' }}">
                        {{ $surat->status ?? 'draft' }}
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-zinc-600 dark:text-zinc-400">
                    <span><i
                            class="fa-regular fa-calendar mr-1 text-teal-600"></i>{{ $surat->tanggal_mulai?->format('d/m/Y') }}</span>
                    @if ($surat->jam_acara)
                        <span><i class="fa-regular fa-clock mr-1 text-teal-600"></i>{{ $surat->jam_acara }}</span>
                    @endif
                    @if ($surat->lokasi)
                        <span><i class="fa-solid fa-location-dot mr-1 text-teal-600"></i>{{ $surat->lokasi }}</span>
                    @endif
                </div>
                @if ($surat->created_at)
                    <div class="text-[13px] text-zinc-400 dark:text-zinc-500">
                        <i class="fa-regular fa-paper-plane mr-1"></i>Dibuat:
                        {{ $surat->created_at->format('d/m/Y H:i') }} WIB
                    </div>
                @endif

                <div class="pt-1 flex items-center gap-2">
                    <button type="button" wire:click="showDetail({{ $surat->id }})"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-teal-50 dark:bg-teal-500/15 text-teal-700 dark:text-teal-300 text-xs font-semibold">
                        <i class="fa-solid fa-circle-info"></i>
                        <span>Detail</span>
                    </button>
                    @if ($surat->status === 'terbit')
                        <a href="{{ route('surat-tugas.cetak-pdf', $surat) }}" target="_blank"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 text-xs font-semibold">
                            <i class="fa-solid fa-file-pdf text-rose-500"></i>
                            <span>Lihat Surat</span>
                        </a>
                    @elseif (auth()->user()?->canManageSuratTugas())
                        <a href="{{ route('mobile.surat-tugas.edit', $surat) }}" wire:navigate
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300 text-xs font-semibold">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span>Edit</span>
                        </a>
                    @endif
                    @if (auth()->user()?->canManageSuratTugas() &&
                            ($surat->status !== 'terbit' ||
                                auth()->user()
                                    ?->hasAnyRole(['super_admin', 'superadmin', 'admin'])))
                        <button type="button"
                            @click.prevent="$dispatch('sakti-confirm', { message: 'Yakin ingin menghapus surat tugas ini?', wireId: '{{ $this->getId() }}', method: 'hapus', params: [{{ $surat->id }}] })"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-50 dark:bg-rose-500/15 text-rose-700 dark:text-rose-300 text-xs font-semibold cursor-pointer">
                        <i class="fa-solid fa-trash"></i>
                        <span>Hapus</span>
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div
                class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-8 text-center">
                <i class="fa-solid fa-file-signature text-3xl text-zinc-300 dark:text-zinc-700 mb-2"></i>
                <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Belum ada Surat Tugas</p>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">Daftar surat tugas akan muncul di sini.</p>
            </div>
        @endforelse
    </div>

    @if ($selectedSurat)
        <div x-data="{ open: false }" x-init="$nextTick(() => open = true);
        $watch('open', v => { if (!v) setTimeout(() => $wire.closeDetail(), 160); })">
            <x-mobile.rhoe-modal model="open" :title="$selectedSurat->nama_kegiatan" :subtitle="$selectedSurat->nomor_surat ?? 'Draft'">
                <div class="space-y-4">
                    @if ($selectedSurat->status === 'koreksi' && filled($selectedSurat->catatan_koreksi))
                        <div
                            class="rounded-xl bg-amber-50 dark:bg-amber-500/15 border border-amber-200 dark:border-amber-800 p-3 text-xs text-amber-800 dark:text-amber-200">
                            <div class="font-bold mb-0.5"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Catatan
                                Koreksi:</div>
                            <div>{{ $selectedSurat->catatan_koreksi }}</div>
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-2 text-[13px]">
                        <span
                            class="px-2.5 py-0.5 rounded-full font-bold uppercase {{ $selectedSurat->status === 'terbit' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' }}">
                            {{ $selectedSurat->status ?? 'draft' }}
                        </span>
                        @if ($selectedSurat->creator)
                            <span class="text-zinc-500 dark:text-zinc-400">
                                Pengusul: <strong
                                    class="text-zinc-700 dark:text-zinc-200">{{ $selectedSurat->creator->name }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <div class="text-zinc-500 dark:text-zinc-400">Tanggal</div>
                            <div class="font-semibold text-zinc-800 dark:text-zinc-200">
                                {{ $selectedSurat->tanggal_mulai?->format('d/m/Y') }} -
                                {{ $selectedSurat->tanggal_selesai?->format('d/m/Y') }}</div>
                        </div>
                        <div>
                            <div class="text-zinc-500 dark:text-zinc-400">Jam Acara</div>
                            <div class="font-semibold text-zinc-800 dark:text-zinc-200">
                                {{ $selectedSurat->jam_acara ?: '-' }}</div>
                        </div>
                        <div class="col-span-2">
                            <div class="text-zinc-500 dark:text-zinc-400">Lokasi</div>
                            <div class="font-semibold text-zinc-800 dark:text-zinc-200">
                                {{ $selectedSurat->lokasi ?: '-' }}</div>
                        </div>
                        <div class="col-span-2">
                            <div class="text-zinc-500 dark:text-zinc-400">Dasar Surat</div>
                            <div class="font-semibold text-zinc-800 dark:text-zinc-200">
                                {{ $selectedSurat->dasar_surat_undangan ?: '-' }}</div>
                        </div>
                        @if (filled($selectedSurat->asal_surat_undangan) || $selectedSurat->tanggal_surat_undangan)
                            <div>
                                <div class="text-zinc-500 dark:text-zinc-400">Asal Surat</div>
                                <div class="font-semibold text-zinc-800 dark:text-zinc-200">
                                    {{ $selectedSurat->asal_surat_undangan ?: '-' }}</div>
                            </div>
                            <div>
                                <div class="text-zinc-500 dark:text-zinc-400">Tgl. Surat</div>
                                <div class="font-semibold text-zinc-800 dark:text-zinc-200">
                                    {{ $selectedSurat->tanggal_surat_undangan?->format('d/m/Y') ?: '-' }}</div>
                            </div>
                        @endif
                        @if (filled($selectedSurat->keterangan))
                            <div class="col-span-2">
                                <div class="text-zinc-500 dark:text-zinc-400">Keterangan</div>
                                <div class="font-semibold text-zinc-800 dark:text-zinc-200">
                                    {{ $selectedSurat->keterangan }}</div>
                            </div>
                        @endif
                    </div>

                    @php
                        $pjNames = !empty($selectedSurat->penanggung_jawab_ids)
                            ? \App\Models\User::whereIn('id', $selectedSurat->penanggung_jawab_ids)->pluck('name')
                            : collect();
                    @endphp
                    @if ($pjNames->isNotEmpty())
                        <div>
                            <div class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1.5">Penanggung Jawab
                                ({{ $pjNames->count() }})</div>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach ($pjNames as $pjName)
                                    <span
                                        class="px-2.5 py-1 rounded-lg bg-sky-50 dark:bg-sky-500/15 text-xs font-medium text-sky-700 dark:text-sky-300">{{ $pjName }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if (filled($selectedSurat->bukti_surat_undangan))
                        <div>
                            <a href="{{ asset('storage/' . $selectedSurat->bukti_surat_undangan) }}" target="_blank"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-xs font-semibold text-zinc-700 dark:text-zinc-200">
                                <i class="fa-solid fa-paperclip text-sky-500"></i>
                                <span>Lihat Lampiran Undangan</span>
                            </a>
                        </div>
                    @endif

                    <div>
                        <div class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1.5">Personil Tim Kreatif
                            ({{ $selectedSurat->pegawais->count() }})</div>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse ($selectedSurat->pegawais as $p)
                                <span
                                    class="px-2.5 py-1 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-xs font-medium text-zinc-700 dark:text-zinc-200">{{ $p->name }}</span>
                            @empty
                                <span class="text-xs text-zinc-400">Belum ada personil</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </x-mobile.rhoe-modal>
        </div>
    @endif
</div>
