<div class="p-5 pb-28 relative min-h-screen">
    <div class="flex items-center justify-between gap-2 mb-5">
        <div class="flex items-center min-w-0">
            <a href="{{ route('mobile.lhk.index') }}" wire:navigate
                class="mr-3 w-9 h-9 rounded-xl bg-white dark:bg-[#132338] border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-sky-600 transition shrink-0">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div class="min-w-0">
                <h1 class="text-lg md:text-2xl font-extrabold text-slate-800 dark:text-white tracking-tight truncate">
                    Bank Aktivitas Saya</h1>
                <p class="text-[13px] md:text-sm text-slate-500 dark:text-slate-400 truncate">Daftar aktivitas pribadi
                    untuk pengisian LHK</p>
            </div>
        </div>
    </div>

    <!-- Info Global vs Bidang / Tim Kreatif + Widget Jumlah -->
    <div class="grid grid-cols-2 gap-2.5 mb-3">
        <div
            class="rounded-2xl bg-indigo-50 dark:bg-indigo-500/15 border border-indigo-200/70 dark:border-indigo-500/30 p-3">
            <div class="text-xs md:text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-300">
                Aktivitas Global</div>
            <div class="text-lg md:text-2xl font-black text-indigo-900 dark:text-white mt-0.5">{{ $globalCount ?? 0 }}
            </div>
        </div>
        <div
            class="rounded-2xl bg-amber-50 dark:bg-amber-500/15 border border-amber-200/70 dark:border-amber-500/30 p-3">
            <div class="text-xs md:text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">
                Bidang &amp; Tim Kreatif</div>
            <div class="text-lg md:text-2xl font-black text-amber-900 dark:text-white mt-0.5">{{ $spesifikCount ?? 0 }}
            </div>
        </div>
    </div>
    <div
        class="mb-4 rounded-2xl bg-sky-50 dark:bg-sky-500/10 border border-sky-200/70 dark:border-sky-500/30 px-3.5 py-2.5 text-[13px] md:text-sm text-sky-800 dark:text-sky-200 leading-relaxed">
        <i class="fa-solid fa-circle-info mr-1 text-sky-600 dark:text-sky-400"></i>
        Aktivitas <strong>Global</strong> dapat dipilih oleh seluruh pegawai. Aktivitas <strong>Bidang</strong> dan
        <strong>Tim Kreatif</strong> diisi sesuai tugas masing-masing pegawai.
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
    <div class="mb-3 relative">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" wire:model.live.debounce.250ms="search" placeholder="Cari uraian aktivitas..."
            class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-white dark:bg-[#132338]/90 border border-slate-200/80 dark:border-slate-700/60 text-xs text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-sky-500">
    </div>

    <!-- Tab Filter Sub Kegiatan -->
    <div class="flex items-center gap-1.5 overflow-x-auto pb-2 mb-3 no-scrollbar">
        <button type="button" wire:click="$set('filterSubKegiatan', 'semua')"
            class="shrink-0 px-3 py-1.5 rounded-full text-[13px] font-bold transition cursor-pointer {{ $filterSubKegiatan === 'semua' ? 'bg-sky-600 text-white shadow-xs' : 'bg-white dark:bg-[#132338] text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
            Semua
        </button>
        <button type="button" wire:click="$set('filterSubKegiatan', 'global')"
            class="shrink-0 px-3 py-1.5 rounded-full text-[13px] font-bold transition cursor-pointer {{ $filterSubKegiatan === 'global' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white dark:bg-[#132338] text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
            Umum ({{ $globalCount ?? 0 }})
        </button>
        <button type="button" wire:click="$set('filterSubKegiatan', 'bidang')"
            class="shrink-0 px-3 py-1.5 rounded-full text-[13px] font-bold transition cursor-pointer {{ $filterSubKegiatan === 'bidang' ? 'bg-amber-600 text-white shadow-xs' : 'bg-white dark:bg-[#132338] text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
            {{ $userBidangNama ?? 'Bidang' }} ({{ $bidangCount ?? 0 }})
        </button>
        @if ($isTimKreatif ?? false)
            <button type="button" wire:click="$set('filterSubKegiatan', 'tim_kreatif')"
                class="shrink-0 px-3 py-1.5 rounded-full text-[13px] font-bold transition cursor-pointer {{ $filterSubKegiatan === 'tim_kreatif' ? 'bg-purple-600 text-white shadow-xs' : 'bg-white dark:bg-[#132338] text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700' }}">
                Tim Kreatif ({{ $timKreatifCount ?? 0 }})
            </button>
        @endif
    </div>

    @if ($filterSubKegiatan === 'bidang' && $subKegiatanList->isNotEmpty())
        <div class="mb-3">
            <select wire:model.live="selectedSubKegiatan"
                class="w-full rounded-xl px-3 py-2 text-xs font-semibold bg-white dark:bg-[#132338]/90 border border-slate-200/80 dark:border-slate-700/60 text-slate-700 dark:text-slate-200 focus:ring-2 focus:ring-amber-500">
                <option value="">Semua Sub Kegiatan — {{ $userBidangNama ?? 'Bidang' }} ({{ $bidangCount ?? 0 }})</option>
                @foreach ($subKegiatanList as $subKeg => $subCount)
                    <option value="{{ $subKeg }}">{{ $subKeg }} ({{ $subCount }})</option>
                @endforeach
            </select>
        </div>
    @endif

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
                        <h4 class="text-[13px] font-extrabold uppercase tracking-wide text-slate-600 dark:text-slate-300 leading-snug">
                            {{ $groupTitle }}
                        </h4>
                        <span class="shrink-0 px-2 py-0.5 rounded-full bg-slate-200/80 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold">
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
                                            ? "{$menitInt} mnt (" . intdiv($menitInt, 60) . 'j ' . $menitInt % 60 . 'm)'
                                            : "{$menitInt} mnt (" . intdiv($menitInt, 60) . ' jam)')
                                        : "{$menitInt} mnt";
                                $durasiBadge = match (true) {
                                    $menitInt >= 240
                                        => 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300',
                                    $menitInt >= 120
                                        => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
                                    $menitInt >= 60 => 'bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300',
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
                        @if (
                            ((int) $item->user_id === (int) auth()->id() && ($item->status ?? 'pending') !== 'disetujui') ||
                                auth()->user()
                                    ?->hasAnyRole(['super_admin', 'superadmin', 'admin']))
                            <div class="flex items-center gap-1.5">
                                <button type="button" wire:click="openEdit({{ $item->id }})"
                                    class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-sky-600 flex items-center justify-center text-xs transition"
                                    title="Ubah">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button type="button"
                                    @click.prevent="$dispatch('sakti-confirm', { message: 'Hapus aktivitas ini dari Bank Aktivitas Anda?', wireId: '{{ $this->getId() }}', method: 'deleteItem', params: [{{ $item->id }}] })"
                                    class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-500/15 text-rose-500 hover:text-rose-700 flex items-center justify-center text-xs transition"
                                    title="Hapus">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        @endif
                    </div>
                    <p class="text-xs md:text-sm font-semibold text-slate-800 dark:text-slate-100 leading-relaxed">
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

    <!-- MODAL TAMBAH / EDIT AKTIVITAS -->
    @if ($showFormModal)
        <div class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-3 sm:p-4 bg-slate-950/60 backdrop-blur-xs"
            x-data @keydown.escape.window="$wire.closeForm()">
            <div
                class="w-full max-w-[408px] bg-white dark:bg-[#132338] rounded-3xl p-5 shadow-2xl border border-slate-200/90 dark:border-slate-700/80 space-y-4">
                <div
                    class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-700/60">
                    <h3 class="font-extrabold text-slate-800 dark:text-white text-sm">
                        {{ $editingId ? 'Ubah Aktivitas' : 'Tambah Aktivitas Baru' }}
                    </h3>
                    <button type="button" wire:click="closeForm"
                        class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 flex items-center justify-center">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>

                @if (auth()->user()
                        ?->hasAnyRole(['super_admin', 'admin']))
                    <div>
                        <label class="block text-[13px] font-bold text-slate-600 dark:text-slate-300 mb-1.5">Tipe
                            Aktivitas</label>
                        @php
                            $bidangOptionLabel = ($userBidangNama ?? 'Bidang') !== 'Bidang'
                                ? 'Bidang ' . $userBidangNama
                                : 'Per Pegawai / Jabatan';
                            $tipeGroups = [
                                [
                                    'label' => '',
                                    'options' => [
                                        ['value' => 'khusus', 'label' => $bidangOptionLabel],
                                        ['value' => 'umum', 'label' => 'Umum (Semua Pegawai)'],
                                    ],
                                ],
                            ];
                        @endphp
                        <x-mobile.select
                            wire-model="tipe"
                            :value="$tipe"
                            search-placeholder="Cari tipe..."
                            :groups="$tipeGroups" />
                    </div>
                @endif

                <div>
                    <label class="block text-[13px] font-bold text-slate-600 dark:text-slate-300 mb-1.5">Rencana Aksi /
                        Sub Kegiatan (Opsional)</label>
                    @php
                        $modalRaGroups = $rencanaAksiOptions->map(fn ($items, $subKegGroup) => [
                            'label' => $subKegGroup,
                            'options' => $items->map(fn ($ra) => [
                                'value' => $ra->id,
                                'label' => $ra->rencana_aksi,
                            ])->values()->all(),
                        ])->values()->all();
                    @endphp
                    <x-mobile.select
                        wire-model="rencana_aksi_id"
                        :value="$rencana_aksi_id ?? ''"
                        empty-label="Umum"
                        search-placeholder="Cari rencana aksi..."
                        :groups="$modalRaGroups" />
                </div>

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
                    <textarea rows="3" wire:model="nama_aktivitas" placeholder="Contoh: Mengikuti Apel Pagi"
                        class="w-full rounded-xl p-2.5 text-xs bg-slate-50 dark:bg-[#0F1E33] border border-slate-200 dark:border-slate-700"></textarea>
                    @error('nama_aktivitas')
                        <span class="text-rose-500 text-xs">{{ $message }}</span>
                    @enderror
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
            <div
                class="w-full max-w-[408px] bg-white dark:bg-[#132338] rounded-3xl p-5 shadow-2xl border border-slate-200/90 dark:border-slate-700/80 max-h-[85vh] overflow-y-auto space-y-4">
                <div
                    class="flex items-center justify-between pb-2.5 border-b border-slate-100 dark:border-slate-700/60">
                    <div>
                        <h3 class="font-extrabold text-slate-800 dark:text-white text-sm">Tempel / Impor Bank Aktivitas
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Copy-paste langsung dari tabel
                            bankaktifitas.html atau Excel</p>
                    </div>
                    <button type="button" wire:click="closeBulk"
                        class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 flex items-center justify-center">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>

                <div>
                    <label class="block text-[13px] font-bold text-slate-500 dark:text-slate-400 mb-1">Durasi Default
                        (jika tanpa angka menit)</label>
                    <input type="number" min="1" wire:model="bulkDefaultMenit"
                        class="w-full rounded-xl p-2.5 text-xs bg-slate-50 dark:bg-[#0F1E33] border border-slate-200 dark:border-slate-700">
                </div>

                <div>
                    <label class="block text-[13px] font-bold text-slate-500 dark:text-slate-400 mb-1">Tempel Baris
                        Aktivitas</label>
                    <textarea rows="7" wire:model="bulkRaw"
                        placeholder="1&#9;Mengikuti Apel Pagi&#9;30&#10;2&#9;Memimpin rapat/FGD&#9;180"
                        class="w-full rounded-xl p-2.5 text-xs bg-slate-50 dark:bg-[#0F1E33] border border-slate-200 dark:border-slate-700 font-mono"></textarea>
                    @error('bulkRaw')
                        <span class="text-rose-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <button type="button" wire:click="importBulk" wire:loading.attr="disabled"
                    class="w-full py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition">
                    Proses & Simpan ke Bank Aktivitas Saya
                </button>
            </div>
        </div>
    @endif
</div>
