<div class="p-4 sm:p-5 pb-28 relative min-h-screen" x-data="{
    formModalOpen: @entangle('showFormModal'),
    moveModalOpen: @entangle('showMoveModal'),
}">
    <!-- Header -->
    <div class="flex items-center justify-between gap-3 mb-4">
        <div class="flex items-center min-w-0">
            <a href="{{ route('mobile.lhk.index') }}" wire:navigate
                class="mr-3 w-9 h-9 rounded-xl bg-white dark:bg-[#132338] border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-sky-600 transition shrink-0">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div class="min-w-0">
                <h1 class="text-lg font-extrabold text-slate-800 dark:text-white tracking-tight truncate">
                    Bank Aktivitas Saya</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 truncate">Daftar aktivitas untuk pengisian LHK</p>
            </div>
        </div>
        <button type="button" x-on:click="formModalOpen = true; $wire.openCreate()"
            class="shrink-0 px-3.5 py-2 rounded-xl bg-[#1B6CA8] hover:bg-[#155685] text-white text-xs font-bold flex items-center gap-1.5 shadow-sm active:scale-95 transition cursor-pointer">
            <i class="fa-solid fa-plus text-[11px]"></i>
            <span>Tambah</span>
        </button>
    </div>

    <!-- Ringkasan Jumlah -->
    <div class="grid grid-cols-2 gap-2.5 mb-4">
        <div class="rounded-2xl bg-indigo-50/90 dark:bg-indigo-500/15 border border-indigo-200/70 dark:border-indigo-500/30 px-3.5 py-2.5 flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-300">Global</div>
                <div class="text-xl font-black text-indigo-950 dark:text-white leading-tight mt-0.5">{{ $globalCount ?? 0 }}</div>
            </div>
            <div class="w-8 h-8 rounded-xl bg-indigo-100 dark:bg-indigo-500/25 text-indigo-600 dark:text-indigo-300 flex items-center justify-center text-xs">
                <i class="fa-solid fa-globe"></i>
            </div>
        </div>
        <div class="rounded-2xl bg-amber-50/90 dark:bg-amber-500/15 border border-amber-200/70 dark:border-amber-500/30 px-3.5 py-2.5 flex items-center justify-between">
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">Bidang &amp; Tim</div>
                <div class="text-xl font-black text-amber-950 dark:text-white leading-tight mt-0.5">{{ $spesifikCount ?? 0 }}</div>
            </div>
            <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-500/25 text-amber-700 dark:text-amber-300 flex items-center justify-center text-xs">
                <i class="fa-solid fa-briefcase"></i>
            </div>
        </div>
    </div>

    <!-- Pencarian & Filter -->
    <div class="space-y-2.5 mb-4">
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
            <input type="text" wire:model.live.debounce.250ms="search" placeholder="Cari uraian aktivitas..."
                class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white dark:bg-[#132338]/90 border border-slate-200/80 dark:border-slate-700/60 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-sky-500">
        </div>

        <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5 no-scrollbar">
            <button type="button" wire:click="$set('filterSubKegiatan', 'semua')"
                class="shrink-0 px-3 py-1.5 rounded-full text-xs font-bold transition cursor-pointer {{ $filterSubKegiatan === 'semua' ? 'bg-sky-600 text-white shadow-xs' : 'bg-white dark:bg-[#132338] text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
                Semua
            </button>
            <button type="button" wire:click="$set('filterSubKegiatan', 'global')"
                class="shrink-0 px-3 py-1.5 rounded-full text-xs font-bold transition cursor-pointer {{ $filterSubKegiatan === 'global' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white dark:bg-[#132338] text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
                Umum ({{ $globalCount ?? 0 }})
            </button>
            <button type="button" wire:click="$set('filterSubKegiatan', 'bidang')"
                class="shrink-0 px-3 py-1.5 rounded-full text-xs font-bold transition cursor-pointer {{ $filterSubKegiatan === 'bidang' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white dark:bg-[#132338] text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
                {{ $userBidangNama ?: 'Bidang' }} ({{ $bidangCount ?? 0 }})
            </button>
            @if ($isTimKreatif ?? false)
                <button type="button" wire:click="$set('filterSubKegiatan', 'tim_kreatif')"
                    class="shrink-0 px-3 py-1.5 rounded-full text-xs font-bold transition cursor-pointer {{ $filterSubKegiatan === 'tim_kreatif' ? 'bg-purple-600 text-white shadow-xs' : 'bg-white dark:bg-[#132338] text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
                    Tim Kreatif ({{ $timKreatifCount ?? 0 }})
                </button>
            @endif
        </div>

        @if ($subKegiatanList->isNotEmpty() && $filterSubKegiatan !== 'global')
            <select wire:model.live="selectedSubKegiatan"
                class="w-full rounded-xl px-3 py-2.5 text-xs font-semibold bg-white dark:bg-[#132338]/90 border border-slate-200/80 dark:border-slate-700/60 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-amber-500">
                <option value="">Semua Sub Kegiatan ({{ $bidangCount ?? 0 }})</option>
                @foreach ($subKegiatanList as $subKeg => $subCount)
                    <option value="{{ $subKeg }}">{{ $subKeg }} ({{ $subCount }})</option>
                @endforeach
            </select>
        @endif
    </div>

    @if ($myAktivitas->isEmpty())
        <div
            class="bg-white dark:bg-[#132338]/90 rounded-3xl p-8 text-center border border-slate-200/80 dark:border-slate-700/60 shadow-xs">
            <div
                class="w-14 h-14 mx-auto rounded-2xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-cyan-300 flex items-center justify-center text-2xl mb-3">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-white text-sm">Bank Aktivitas Pribadi Kosong</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                Anda dapat menambahkan aktivitas satu per satu atau salin-tempel langsung daftar dari aplikasi lama
                (`bankaktifitas.html`).
            </p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($groupedAktivitas as $groupTitle => $items)
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-2 px-1">
                        <h4
                            class="text-[13px] font-extrabold uppercase tracking-wide text-slate-600 dark:text-slate-300 leading-snug">
                            {{ $groupTitle }}
                        </h4>
                        <span
                            class="shrink-0 px-2 py-0.5 rounded-full bg-slate-200/80 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold">
                            {{ $items->count() }}
                        </span>
                    </div>
                    <div class="space-y-2">
                        @foreach ($items as $item)
                            <div wire:key="bank-akt-{{ $item->id }}"
                                class="bg-white dark:bg-[#132338]/90 p-3.5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-xs space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-center gap-2">
                                        @php
                                            $menitInt = (int) $item->menit;
                                            $durasiText =
                                                $menitInt >= 60
                                                    ? (intdiv($menitInt, 60) > 0 && $menitInt % 60 > 0
                                                        ? "{$menitInt} mnt (" .
                                                            intdiv($menitInt, 60) .
                                                            'j ' .
                                                            $menitInt % 60 .
                                                            'm)'
                                                        : "{$menitInt} mnt (" . intdiv($menitInt, 60) . ' jam)')
                                                    : "{$menitInt} mnt";
                                            $durasiBadge = match (true) {
                                                $menitInt >= 240
                                                    => 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
                                                $menitInt >= 120
                                                    => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
                                                $menitInt >= 60
                                                    => 'bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
                                                default
                                                    => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
                                            };
                                        @endphp
                                        <span class="px-2 py-0.5 rounded-md text-xs font-bold {{ $durasiBadge }}">
                                            {{ $durasiText }}
                                        </span>
                                        @if (is_null($item->user_id) && is_null($item->jabatan_id))
                                            <span
                                                class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300 text-xs font-bold">Global</span>
                                        @elseif ($item->rencanaAksi?->sasaran_sub_kegiatan === 'Tugas Tambahan')
                                            <span
                                                class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 text-xs font-bold">Tim
                                                Kreatif</span>
                                        @else
                                            <span
                                                class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300 text-xs font-bold truncate max-w-[140px]">{{ $item->bidang?->nama ?? 'Per Pegawai' }}</span>
                                        @endif
                                        @if ($item->bidang?->nama && $item->rencanaAksi?->sasaran_sub_kegiatan === 'Tugas Tambahan')
                                            <span
                                                class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold truncate max-w-[120px]">{{ $item->bidang->nama }}</span>
                                        @endif
                                    </div>
                                    @php
                                        $canManageAll = auth()->user()?->hasAnyRole(['super_admin', 'superadmin', 'admin']) ?? false;
                                        $isOwner = (int) $item->user_id === (int) auth()->id();
                                    @endphp
                                    @if ($isOwner || $canManageAll)
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" x-on:click="moveModalOpen = true; $wire.openMove({{ $item->id }})"
                                                class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-300 hover:bg-amber-100 flex items-center justify-center text-xs transition cursor-pointer"
                                                title="Pindah Sub Kegiatan">
                                                <i class="fa-solid fa-arrows-left-right"></i>
                                            </button>
                                            @if ($canManageAll || ($isOwner && ($item->status ?? 'pending') !== 'disetujui'))
                                                <button type="button" wire:click="openEdit({{ $item->id }})"
                                                    class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-sky-600 flex items-center justify-center text-xs transition cursor-pointer"
                                                    title="Ubah">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <button type="button"
                                                    @click.prevent="$dispatch('sakti-confirm', { message: 'Hapus aktivitas ini dari Bank Aktivitas Anda?', wireId: '{{ $this->getId() }}', method: 'deleteItem', params: [{{ $item->id }}] })"
                                                    class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-500/15 text-rose-500 hover:text-rose-700 flex items-center justify-center text-xs transition cursor-pointer"
                                                    title="Hapus">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <p
                                    class="text-xs md:text-sm font-semibold text-slate-800 dark:text-slate-100 leading-relaxed">
                                    {{ $item->nama_aktivitas }}
                                </p>
                                @if ($item->rencanaAksi?->sasaran_sub_kegiatan)
                                    <div
                                        class="text-xs md:text-xs font-medium text-teal-700 dark:text-teal-300 bg-teal-50/70 dark:bg-teal-500/10 rounded-lg px-2 py-1 inline-block">
                                        <i
                                            class="fa-solid fa-diagram-project mr-1"></i>{{ $item->rencanaAksi->sasaran_sub_kegiatan }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- MODAL TAMBAH / EDIT AKTIVITAS (Reusable Rhoe Modal) -->
    <x-mobile.rhoe-modal model="formModalOpen" :title="$editingId ? 'Ubah Aktivitas' : 'Tambah Aktivitas Baru'" maxWidth="max-w-[408px]">
        <div class="space-y-4">
            @php
                $canAddUmum =
                    auth()
                        ->user()
                        ?->hasAnyRole(['super_admin', 'superadmin', 'admin']) ?? false;
                $formatBidangLabel = static fn(string $nama): string => str_starts_with(
                    mb_strtolower($nama),
                    'bidang',
                ) || str_starts_with(mb_strtolower($nama), 'sekretariat')
                    ? $nama
                    : 'Bidang ' . $nama;

                $tipeOptions = [];
                if (filled($userBidangNama)) {
                    $tipeOptions[] = ['value' => 'khusus', 'label' => $formatBidangLabel($userBidangNama)];
                } elseif ($bidangList->isNotEmpty()) {
                    foreach ($bidangList as $b) {
                        $tipeOptions[] = ['value' => 'bidang:' . $b->id, 'label' => $formatBidangLabel($b->nama)];
                    }
                } else {
                    $tipeOptions[] = [
                        'value' => 'khusus',
                        'label' => auth()->user()?->jabatan?->nama ?: 'Lingkup Dinas',
                    ];
                }

                if ($canAddUmum) {
                    $tipeOptions[] = ['value' => 'umum', 'label' => 'Umum (Semua Pegawai)'];
                }

                $bidangOptionLabel = $tipeOptions[0]['label'];
                $tipeGroups = [
                    [
                        'label' => '',
                        'options' => $tipeOptions,
                    ],
                ];
            @endphp
            <div>
                <label class="block text-[13px] font-bold text-slate-600 dark:text-slate-300 mb-1.5">Tipe
                    Aktivitas</label>
                @if ($canAddUmum)
                    <x-mobile.select wire-model="tipe" :value="$tipe" search-placeholder="Cari tipe..."
                        :groups="$tipeGroups" />
                @else
                    <div
                        class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-100 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 flex items-center justify-between">
                        <span>{{ $bidangOptionLabel }}</span>
                        <i class="fa-solid fa-lock text-slate-400 text-[11px]"></i>
                    </div>
                @endif
            </div>

            @if (!($canAddUmum && $tipe === 'umum'))
                <div>
                    <label class="block text-[13px] font-bold text-slate-600 dark:text-slate-300 mb-1.5">Rencana Aksi
                        <span class="text-rose-500">*</span></label>
                    @php
                        $modalRaGroups = [
                            [
                                'label' => '',
                                'options' => $rencanaAksiList
                                    ->map(
                                        fn($ra) => [
                                            'value' => $ra->id,
                                            'label' => $ra->rencana_aksi,
                                        ],
                                    )
                                    ->values()
                                    ->all(),
                            ],
                        ];
                    @endphp
                    <x-mobile.select wire-model="rencana_aksi_id" :value="$rencana_aksi_id ?? ''"
                        placeholder="— Pilih Nama Rencana Aksi —" search-placeholder="Cari nama rencana aksi..."
                        :groups="$modalRaGroups" />
                    @error('rencana_aksi_id')
                        <p class="text-[12px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[13px] font-bold text-slate-600 dark:text-slate-300 mb-1.5">Sub
                        Kegiatan</label>
                    <div
                        class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-100 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 min-h-[42px] flex items-center">
                        @if (filled($selectedSubKegiatanNama))
                            <span>{{ $selectedSubKegiatanNama }}</span>
                        @else
                            <span class="text-slate-400 dark:text-slate-500 font-normal">Otomatis terisi sesuai Rencana
                                Aksi yang dipilih</span>
                        @endif
                    </div>
                </div>
            @endif

            <div>
                <label class="block text-[13px] font-bold text-slate-500 dark:text-slate-400 mb-1">Durasi
                    (Menit)</label>
                <input type="number" min="1" wire:model="menit"
                    class="w-full rounded-xl p-2.5 text-xs bg-slate-50 dark:bg-[#0F1E33] border border-slate-200 dark:border-slate-700">
                @error('menit')
                    <span class="text-rose-500 text-xs">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-[13px] font-bold text-slate-500 dark:text-slate-400 mb-1">Nama / Uraian
                    Aktivitas</label>
                <textarea rows="3" wire:model="nama_aktivitas" placeholder="Mempersiapkan spj kegiatan"
                    class="w-full rounded-xl p-2.5 text-xs bg-slate-50 dark:bg-[#0F1E33] border border-slate-200 dark:border-slate-700"></textarea>
                @error('nama_aktivitas')
                    <span class="text-rose-500 text-xs">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <x-slot:footer>
            <button type="button" wire:click="saveItem" wire:loading.attr="disabled"
                class="w-full py-3 rounded-xl bg-[#1B6CA8] hover:bg-[#155685] text-white font-bold text-xs shadow-md transition active:scale-95 cursor-pointer flex items-center justify-center gap-2">
                <i wire:loading wire:target="saveItem" class="fa-solid fa-spinner fa-spin"></i>
                <span>Simpan Aktivitas</span>
            </button>
        </x-slot:footer>
    </x-mobile.rhoe-modal>

    <!-- MODAL PINDAH SUB KEGIATAN -->
    <x-mobile.rhoe-modal model="moveModalOpen" title="Pindah Sub Kegiatan" maxWidth="max-w-[408px]">
        <div class="space-y-4">
            <div class="rounded-2xl bg-slate-50 dark:bg-slate-800/70 border border-slate-200/80 dark:border-slate-700 p-3 space-y-1.5">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Aktivitas</div>
                <p class="text-xs font-semibold text-slate-800 dark:text-slate-100 leading-snug">
                    {{ $movingNamaAktivitas ?: '-' }}
                </p>
                @if ($movingCurrentSubKegiatan)
                    <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-teal-50 dark:bg-teal-500/15 text-teal-700 dark:text-teal-300 text-[11px] font-semibold">
                        <i class="fa-solid fa-diagram-project"></i>
                        <span>Saat ini: {{ $movingCurrentSubKegiatan }}</span>
                    </div>
                @endif
            </div>

            <div>
                <label class="block text-[13px] font-bold text-slate-600 dark:text-slate-300 mb-1.5">Rencana Aksi Tujuan <span class="text-rose-500">*</span></label>
                @php
                    $moveRaGroups = [
                        [
                            'label' => '',
                            'options' => $rencanaAksiList->map(fn ($ra) => [
                                'value' => $ra->id,
                                'label' => $ra->rencana_aksi,
                            ])->values()->all(),
                        ],
                    ];
                @endphp
                <x-mobile.select
                    wire-model="moveRencanaAksiId"
                    :value="$moveRencanaAksiId ?? ''"
                    placeholder="— Pilih Rencana Aksi Tujuan —"
                    search-placeholder="Cari nama rencana aksi..."
                    :groups="$moveRaGroups" />
                @error('moveRencanaAksiId')
                    <p class="text-[12px] text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-[13px] font-bold text-slate-600 dark:text-slate-300 mb-1.5">Sub Kegiatan Tujuan</label>
                <div class="w-full px-3.5 py-2.5 rounded-2xl bg-slate-100 dark:bg-slate-800/70 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-200 min-h-[42px] flex items-center">
                    @if (filled($moveSelectedSubKegiatanNama))
                        <span>{{ $moveSelectedSubKegiatanNama }}</span>
                    @else
                        <span class="text-slate-400 dark:text-slate-500 font-normal">Otomatis terisi sesuai Rencana Aksi yang dipilih</span>
                    @endif
                </div>
            </div>
        </div>

        <x-slot:footer>
            <button type="button" wire:click="moveSubKegiatan" wire:loading.attr="disabled"
                class="w-full py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-md transition active:scale-95 cursor-pointer flex items-center justify-center gap-2">
                <i wire:loading wire:target="moveSubKegiatan" class="fa-solid fa-spinner fa-spin"></i>
                <span>Pindahkan Sekarang</span>
            </button>
        </x-slot:footer>
    </x-mobile.rhoe-modal>
</div>
