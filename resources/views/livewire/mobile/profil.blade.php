<div class="p-5 pb-28" x-data="{ confirmLogout: false }">
    <!-- Hero Biru Gradient Melengkung (Top Bar + Identitas Pegawai) -->
    <div
        class="-mx-5 -mt-5 mb-5 rounded-b-[28px] bg-gradient-to-br from-[#0F172A] via-[#0369A1] to-[#0284C7] px-5 pt-5 pb-7 text-white shadow-lg shadow-sky-950/20 relative isolate overflow-hidden">
        <x-mobile.logo-ornament />

        <!-- Top Bar -->
        <div class="flex items-center justify-between mb-4 relative z-10">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight">Profil Pegawai</h1>
                <p class="text-xs text-sky-100 font-medium">Statistik & Biodata Kepegawaian</p>
            </div>
            <div class="flex items-center space-x-2">
                @if (auth()->user()?->hasRole('super_admin'))
                    <a href="{{ route('mobile.pengaturan-modul') }}" wire:navigate
                        class="w-8 h-8 rounded-xl bg-white/15 hover:bg-white/25 text-white transition flex items-center justify-center border border-white/20 shadow-xs active:scale-95 cursor-pointer"
                        title="Pengaturan Modul Sistem" aria-label="Pengaturan Modul Sistem">
                        <i class="fa-solid fa-sliders text-xs"></i>
                    </a>
                @endif
                @if (auth()->user()?->hasRole('super_admin') && !session()->has('impersonate_original_id'))
                    <button type="button" wire:click="$set('showImpersonateModal', true)"
                        class="px-3 py-1.5 rounded-xl bg-white/15 hover:bg-white/25 text-white font-bold text-xs flex items-center gap-1.5 border border-white/20 active:scale-95 transition cursor-pointer"
                        title="Pindah Akun Pegawai">
                        <i class="fa-solid fa-user-secret text-xs"></i>
                        <span>Pindah Akun</span>
                    </button>
                @elseif (session()->has('impersonate_original_id'))
                    <button type="button" wire:click="stopImpersonating"
                        class="px-3 py-1.5 rounded-xl bg-rose-500 hover:bg-rose-400 text-white font-bold text-xs flex items-center gap-1.5 active:scale-95 transition cursor-pointer">
                        <i class="fa-solid fa-rotate-left text-xs"></i>
                        <span>Kembali</span>
                    </button>
                @endif
                <button type="button" @click="confirmLogout = true"
                    class="w-8 h-8 rounded-xl bg-rose-500/20 hover:bg-rose-500/35 text-rose-100 transition flex items-center justify-center border border-rose-300/30 shadow-xs active:scale-95 cursor-pointer"
                    title="Keluar dari Akun">
                    <i class="fa-solid fa-right-from-bracket text-xs"></i>
                </button>
            </div>
        </div>

        <div class="flex items-center space-x-4 relative z-10"
            x-data="{
                cropOpen: false,
                imgSrc: '',
                imgEl: null,
                zoom: 1,
                rotation: 0,
                offsetX: 0,
                offsetY: 0,
                dragging: false,
                startX: 0,
                startY: 0,
                saving: false,
                openCropper(e) {
                    const file = e.target.files?.[0];
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = (ev) => {
                        this.imgSrc = ev.target.result;
                        const img = new Image();
                        img.onload = () => {
                            this.imgEl = img;
                            this.zoom = 1;
                            this.rotation = 0;
                            this.offsetX = 0;
                            this.offsetY = 0;
                            this.cropOpen = true;
                            this.$nextTick(() => this.drawPreview());
                        };
                        img.src = this.imgSrc;
                    };
                    reader.readAsDataURL(file);
                    e.target.value = '';
                },
                startDrag(e) {
                    this.dragging = true;
                    const pt = e.touches ? e.touches[0] : e;
                    this.startX = pt.clientX - this.offsetX;
                    this.startY = pt.clientY - this.offsetY;
                },
                onDrag(e) {
                    if (!this.dragging) return;
                    const pt = e.touches ? e.touches[0] : e;
                    this.offsetX = pt.clientX - this.startX;
                    this.offsetY = pt.clientY - this.startY;
                    this.drawPreview();
                },
                endDrag() {
                    this.dragging = false;
                },
                drawPreview() {
                    const canvas = this.$refs.cropCanvas;
                    if (!canvas || !this.imgEl) return;
                    const size = 240;
                    canvas.width = size;
                    canvas.height = size;
                    const ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, size, size);
                    ctx.save();
                    ctx.translate(size / 2 + this.offsetX, size / 2 + this.offsetY);
                    ctx.rotate((this.rotation * Math.PI) / 180);
                    const baseScale = Math.max(size / this.imgEl.width, size / this.imgEl.height);
                    const scale = baseScale * this.zoom;
                    ctx.drawImage(
                        this.imgEl,
                        (-this.imgEl.width * scale) / 2,
                        (-this.imgEl.height * scale) / 2,
                        this.imgEl.width * scale,
                        this.imgEl.height * scale
                    );
                    ctx.restore();
                },
                async saveCrop() {
                    if (!this.imgEl || this.saving) return;
                    this.saving = true;
                    const out = document.createElement('canvas');
                    const size = 512;
                    const ratio = size / 240;
                    out.width = size;
                    out.height = size;
                    const ctx = out.getContext('2d');
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, size, size);
                    ctx.save();
                    ctx.translate(size / 2 + this.offsetX * ratio, size / 2 + this.offsetY * ratio);
                    ctx.rotate((this.rotation * Math.PI) / 180);
                    const baseScale = Math.max(size / this.imgEl.width, size / this.imgEl.height);
                    const scale = baseScale * this.zoom;
                    ctx.drawImage(
                        this.imgEl,
                        (-this.imgEl.width * scale) / 2,
                        (-this.imgEl.height * scale) / 2,
                        this.imgEl.width * scale,
                        this.imgEl.height * scale
                    );
                    ctx.restore();
                    const dataUrl = out.toDataURL('image/jpeg', 0.9);
                    await $wire.saveCroppedAvatar(dataUrl);
                    this.saving = false;
                    this.cropOpen = false;
                }
            }"
        >
            <label class="relative block cursor-pointer group shrink-0" title="Klik foto untuk ubah & crop (Maks. 2MB)">
                <img src="{{ $user->avatar_image_url }}" alt="Avatar Pegawai"
                    class="w-16 h-16 rounded-full object-cover aspect-square border-2 border-white/40 shadow-lg shadow-black/20 group-hover:brightness-90 group-active:scale-95 transition">
                <input type="file" @change="openCropper($event)" class="hidden" accept="image/*">
            </label>

            <template x-teleport="body">
                <div x-show="cropOpen" x-cloak @keydown.escape.window="cropOpen = false"
                    class="fixed inset-0 z-[100] bg-slate-900/70 backdrop-blur-xs flex items-end sm:items-center justify-center p-4"
                    @click.self="cropOpen = false">
                    <div class="w-full max-w-sm rounded-3xl bg-white p-5 shadow-2xl space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-800">Edit &amp; Crop Foto Profil</h3>
                                <p class="text-[11px] text-slate-500">Geser foto atau atur zoom agar pas di tengah</p>
                            </div>
                            <button type="button" @click="cropOpen = false"
                                class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center cursor-pointer">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        </div>

                        <div class="flex justify-center">
                            <div class="relative w-[240px] h-[240px] rounded-full overflow-hidden border-4 border-sky-500/30 shadow-inner bg-slate-900 touch-none cursor-grab active:cursor-grabbing"
                                @mousedown="startDrag($event)" @mousemove.window="onDrag($event)" @mouseup.window="endDrag()"
                                @touchstart.prevent="startDrag($event)" @touchmove.prevent="onDrag($event)" @touchend="endDrag()">
                                <canvas x-ref="cropCanvas" class="w-[240px] h-[240px] block"></canvas>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs font-bold text-slate-600 shrink-0"><i class="fa-solid fa-magnifying-glass-plus mr-1 text-sky-600"></i>Zoom</span>
                                <input type="range" min="1" max="3" step="0.05" x-model.number="zoom" @input="drawPreview()"
                                    class="w-full accent-sky-600 cursor-pointer">
                                <button type="button" @click="rotation = (rotation + 90) % 360; drawPreview()"
                                    class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold shrink-0 cursor-pointer"
                                    title="Putar 90°">
                                    <i class="fa-solid fa-rotate-right"></i>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            <button type="button" @click="cropOpen = false"
                                class="flex-1 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer">
                                Batal
                            </button>
                            <button type="button" @click="saveCrop()" :disabled="saving"
                                class="flex-1 py-2.5 rounded-2xl bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white text-xs font-bold shadow-md shadow-sky-600/25 cursor-pointer">
                                <span x-show="!saving">Simpan Foto</span>
                                <span x-show="saving"><i class="fa-solid fa-spinner fa-spin mr-1"></i>Menyimpan...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <div class="flex-1 min-w-0">
                <div
                    class="inline-block px-2.5 py-0.5 rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-[10px] font-extrabold text-sky-200 uppercase tracking-wider mb-1">
                    {{ $user->jabatan->nama ?? 'Pegawai' }}
                </div>
                @php
                    $namaLengkap = $user->name;
                    if ($user->gelar_depan && stripos($namaLengkap, $user->gelar_depan) === false) {
                        $namaLengkap = $user->gelar_depan . ' ' . $namaLengkap;
                    }
                    if ($user->gelar_belakang && stripos($namaLengkap, $user->gelar_belakang) === false) {
                        $namaLengkap .= ', ' . $user->gelar_belakang;
                    }
                @endphp
                <h2 class="text-base font-extrabold truncate tracking-tight text-white drop-shadow-xs">
                    {{ trim($namaLengkap) }}
                </h2>
                <div class="flex items-center gap-1.5 text-xs text-sky-100 font-mono mt-1 font-medium" x-data="{ copiedNip: false }">
                    <span class="text-amber-300 font-bold">#</span>
                    <span class="truncate">NIP: {{ $user->nip ?? '-' }}</span>
                    @if ($user->nip)
                        <button type="button"
                            @click="navigator.clipboard?.writeText('{{ $user->nip }}'); copiedNip = true; setTimeout(() => copiedNip = false, 1500)"
                            class="px-1.5 py-0.5 rounded-md bg-white/15 hover:bg-white/25 text-[10px] text-white transition cursor-pointer shrink-0"
                            title="Salin NIP">
                            <i class="fa-regular" :class="copiedNip ? 'fa-circle-check text-emerald-300' : 'fa-copy'"></i>
                        </button>
                    @endif
                </div>
                <div class="flex items-center gap-1.5 text-[11px] text-sky-100/90 font-mono mt-0.5" x-data="{ copiedEmail: false }">
                    <i class="fa-solid fa-envelope text-sky-300 text-[10px]"></i>
                    <span class="truncate">{{ $user->email }}</span>
                    <button type="button"
                        @click="navigator.clipboard?.writeText('{{ $user->email }}'); copiedEmail = true; setTimeout(() => copiedEmail = false, 1500)"
                        class="px-1.5 py-0.5 rounded-md bg-white/15 hover:bg-white/25 text-[10px] text-white transition cursor-pointer shrink-0"
                        title="Salin Email">
                        <i class="fa-regular" :class="copiedEmail ? 'fa-circle-check text-emerald-300' : 'fa-copy'"></i>
                    </button>
                </div>
                @php
                    $golHero = $user->riwayatGolongan->firstWhere('is_golongan_terakhir', true)
                        ?? $user->riwayatGolongan->first();
                @endphp
                <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                    @if ($golHero)
                        <span class="px-2 py-0.5 rounded-full bg-amber-400/20 border border-amber-300/30 text-[10px] font-bold text-amber-200">
                            {{ $golHero->golongan?->value ?? $golHero->golongan }}{{ $golHero->nama_pangkat ? ' • ' . $golHero->nama_pangkat : '' }}
                        </span>
                    @endif
                    <span class="px-2 py-0.5 rounded-full bg-white/15 text-[10px] font-bold uppercase text-emerald-200">
                        {{ $user->status_kerja ?? 'WFO' }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full bg-white/10 text-[10px] font-medium text-sky-100">
                        {{ $user->jadwalKerja->nama ?? 'Jadwal Reguler' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Ringkasan Unit Kerja & Atasan -->
        <div class="mt-4 pt-3 border-t border-white/15 grid grid-cols-2 gap-2 text-[11px] relative z-10">
            <div class="bg-white/10 rounded-xl px-2.5 py-2 border border-white/10">
                <div class="text-sky-200/80 text-[10px]">Bidang Dinas</div>
                <div class="font-bold text-white truncate">{{ $user->bidang->nama ?? 'Kepala Dinas' }}</div>
            </div>
            <div class="bg-white/10 rounded-xl px-2.5 py-2 border border-white/10">
                <div class="text-sky-200/80 text-[10px]">Atasan</div>
                <div class="font-bold text-white truncate">{{ $user->atasan->name ?? 'Pimpinan' }}</div>
            </div>
        </div>

        <!-- Upload Progress & Status Alert -->
        <div wire:loading wire:target="new_avatar" class="mt-3 relative z-10">
            <div
                class="bg-black/40 backdrop-blur-md rounded-2xl p-2.5 border border-white/15 flex items-center space-x-2.5 text-xs text-amber-200">
                <i class="fa-solid fa-spinner fa-spin text-sm text-amber-300"></i>
                <div class="flex-1">
                    <div class="flex justify-between text-[11px] font-bold mb-1">
                        <span>Mengunggah foto...</span>
                        <span>Maks. 2MB</span>
                    </div>
                    <div class="w-full bg-white/20 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-amber-400 h-1.5 rounded-full animate-pulse w-full"></div>
                    </div>
                </div>
            </div>
        </div>
        @error('new_avatar')
            <div
                class="mt-2 text-[11px] font-bold text-rose-300 bg-rose-950/40 px-3 py-1.5 rounded-xl border border-rose-500/30">
                <i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $message }}
            </div>
        @enderror
    </div>

    <!-- Navigation Tabs (Termasuk Tab Statistik & Data Pegawai) -->
    <div class="flex space-x-2 overflow-x-auto pb-2 mb-5 no-scrollbar">
        @if (!$isEditing)
            <button wire:click="setTab('utama')"
                class="px-4 py-2 rounded-2xl text-xs font-bold whitespace-nowrap transition-all shadow-xs {{ $activeTab === 'utama' ? 'bg-sky-600 text-white shadow-sky-600/30' : 'bg-white text-slate-600 border border-slate-200/80' }}">
                <i class="fa-solid fa-user text-xs mr-1.5"></i>Data Utama
            </button>
            <button wire:click="setTab('administrasi')"
                class="px-4 py-2 rounded-2xl text-xs font-bold whitespace-nowrap transition-all shadow-xs {{ $activeTab === 'administrasi' ? 'bg-sky-600 text-white shadow-sky-600/30' : 'bg-white text-slate-600 border border-slate-200/80' }}">
                <i class="fa-solid fa-id-card text-xs mr-1.5"></i>Administrasi
            </button>
            <button wire:click="setTab('golongan')"
                class="px-4 py-2 rounded-2xl text-xs font-bold whitespace-nowrap transition-all shadow-xs {{ $activeTab === 'golongan' ? 'bg-sky-600 text-white shadow-sky-600/30' : 'bg-white text-slate-600 border border-slate-200/80' }}">
                <i class="fa-solid fa-shield-halved text-xs mr-1.5"></i>Pangkat / Golongan
            </button>
            <button wire:click="setTab('jabatan')"
                class="px-4 py-2 rounded-2xl text-xs font-bold whitespace-nowrap transition-all shadow-xs {{ $activeTab === 'jabatan' ? 'bg-sky-600 text-white shadow-sky-600/30' : 'bg-white text-slate-600 border border-slate-200/80' }}">
                <i class="fa-solid fa-briefcase text-xs mr-1.5"></i>Jabatan
            </button>
            <button wire:click="setTab('pendidikan')"
                class="px-4 py-2 rounded-2xl text-xs font-bold whitespace-nowrap transition-all shadow-xs {{ $activeTab === 'pendidikan' ? 'bg-sky-600 text-white shadow-sky-600/30' : 'bg-white text-slate-600 border border-slate-200/80' }}">
                <i class="fa-solid fa-graduation-cap text-xs mr-1.5"></i>Pendidikan
            </button>
            <button wire:click="setTab('keluarga')"
                class="px-4 py-2 rounded-2xl text-xs font-bold whitespace-nowrap transition-all shadow-xs {{ $activeTab === 'keluarga' ? 'bg-sky-600 text-white shadow-sky-600/30' : 'bg-white text-slate-600 border border-slate-200/80' }}">
                <i class="fa-solid fa-people-roof text-xs mr-1.5"></i>Keluarga
            </button>
        @else
            <div class="px-4 py-2 rounded-2xl text-xs font-bold bg-sky-600 text-white shadow-xs">
                <i class="fa-solid fa-pen-to-square mr-1.5"></i>{{ match ($activeTab) {
                    'administrasi' => 'Mode Edit Administrasi',
                    'golongan' => 'Mode Edit Pangkat / Golongan',
                    'jabatan' => 'Mode Edit Jabatan',
                    'pendidikan' => 'Mode Edit Pendidikan',
                    'keluarga' => 'Mode Edit Keluarga',
                    default => 'Mode Edit Data Utama',
                } }}
            </div>
        @endif
    </div>

    @if (!$isEditing && !in_array($activeTab, ['golongan', 'jabatan', 'pendidikan']))
        <div class="flex justify-end mb-3">
            <button type="button" wire:click="toggleEdit(true)"
                class="px-3 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-600 text-xs font-bold flex items-center gap-1.5 border border-sky-200/70 transition active:scale-95 cursor-pointer">
                <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                <span>{{ match ($activeTab) {
                    'administrasi' => 'Ubah Data Administrasi',
                    'golongan' => 'Ubah Pangkat / Golongan',
                    'jabatan' => 'Ubah Jabatan',
                    'pendidikan' => 'Ubah Pendidikan',
                    'keluarga' => 'Ubah Data Keluarga',
                    default => 'Ubah Data Utama',
                } }}</span>
            </button>
        </div>
    @endif

    <!-- FORM EDIT / VIEW -->
        @if ($isEditing)
            <form wire:submit="save" class="space-y-4" x-data="{ dirty: false, confirmClose: false }" @input="dirty = true" @change="dirty = true">
                <div class="bg-white p-5 rounded-3xl shadow-xs border border-slate-100 space-y-4">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xs font-extrabold text-sky-700 uppercase tracking-wider">
                            @if ($activeTab === 'pendidikan') Edit Pendidikan Terakhir
                            @elseif ($activeTab === 'administrasi') Edit Administrasi &amp; Nomor
                    @elseif ($activeTab === 'keluarga') Edit Data Keluarga
                            @elseif ($activeTab === 'golongan') Data Pangkat &amp; Golongan
                            @elseif ($activeTab === 'jabatan') Data Jabatan &amp; Unit Kerja
                            @elseif ($activeTab === 'administrasi') Edit Administrasi &amp; Nomor
                            @else Edit Biodata &amp; Kontak Utama
                            @endif
                        </h3>
                        <button type="button" @click="if (dirty) { confirmClose = true } else { $wire.toggleEdit() }" class="text-xs font-bold text-slate-400 hover:text-slate-600 cursor-pointer">Tutup</button>
                    </div>

                    <x-mobile.rhoe-modal model="confirmClose" title="Perubahan Belum Disimpan" subtitle="Simpan perubahan sebelum menutup?">
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Anda telah mengubah data pada form ini. Jika ditutup tanpa menyimpan, perubahan akan hilang.
                        </p>
                        <x-slot:footer>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="confirmClose = false; dirty = false; $wire.toggleEdit()"
                                    class="flex-1 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer">
                                    Tutup Saja
                                </button>
                                <button type="button" @click="confirmClose = false; dirty = false; $wire.save()"
                                    class="flex-1 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold cursor-pointer">
                                    Simpan Data
                                </button>
                            </div>
                        </x-slot:footer>
                    </x-mobile.rhoe-modal>

            @if ($activeTab === 'golongan')
                @php
                    $golTerakhir = $user->riwayatGolongan->firstWhere('is_golongan_terakhir', true)
                        ?? $user->riwayatGolongan->first();
                    $tmtCpns = $user->tmt_cpns ?? $user->riwayatGolongan->sortBy('tmt_golongan')->first()?->tmt_golongan;
                    $masaKerja = $golTerakhir?->masa_kerja_tahun !== null
                        ? "{$golTerakhir->masa_kerja_tahun} Tahun " . ($golTerakhir->masa_kerja_bulan ?? 0) . ' Bulan'
                        : ($tmtCpns
                            ? \Carbon\Carbon::parse($tmtCpns)->diff(now())->format('%y Tahun %m Bulan')
                            : '-');
                @endphp
                <div class="space-y-3" x-data="{ golModalOpen: false }" x-on:open-gol-modal.window="golModalOpen = true">
                    {{-- Ringkasan Golongan Terakhir & Masa Kerja --}}
                    <div class="bg-white rounded-2xl p-3.5 shadow-xs border border-slate-100 space-y-2 text-xs">
                        <div class="flex items-center justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500 flex items-center gap-1.5"><i class="fa-solid fa-shield-halved text-sky-600"></i> Golongan</span>
                            <span class="font-bold text-slate-900">{{ $golTerakhir?->golongan?->value ?? ($golTerakhir?->golongan ?? '-') }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500 flex items-center gap-1.5"><i class="fa-solid fa-award text-sky-600"></i> Pangkat</span>
                            <span class="font-bold text-slate-900">{{ $golTerakhir?->nama_pangkat ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500 flex items-center gap-1.5"><i class="fa-regular fa-calendar text-sky-600"></i> TMT Golongan</span>
                            <span class="font-bold text-slate-900">{{ $golTerakhir?->tmt_golongan ? \Carbon\Carbon::parse($golTerakhir->tmt_golongan)->translatedFormat('j F Y') : '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1">
                            <span class="text-slate-500 flex items-center gap-1.5"><i class="fa-regular fa-clock text-sky-600"></i> Masa Kerja</span>
                            <span class="font-bold text-slate-900">{{ $masaKerja }}</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] font-extrabold text-sky-700 uppercase tracking-wider">Riwayat Pangkat &amp; Golongan</span>
                        <button type="button" wire:click="newGolongan" @click="golModalOpen = true"
                            class="px-3 py-1.5 rounded-xl text-[11px] font-bold flex items-center gap-1.5 cursor-pointer transition bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>Tambah Baru</span>
                        </button>
                    </div>

                    <div class="space-y-2">
                        @forelse ($user->riwayatGolongan as $rg)
                            <div class="flex items-center justify-between gap-2 p-3 rounded-2xl border border-slate-200/80 bg-slate-50/70">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-extrabold text-slate-800">{{ $rg->golongan?->value ?? (string) $rg->golongan }}</span>
                                        @if ($rg->nama_pangkat)
                                            <span class="text-xs font-medium text-slate-600 truncate">— {{ $rg->nama_pangkat }}</span>
                                        @endif
                                        @if ($rg->is_golongan_terakhir)
                                            <span class="px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[9px] font-bold uppercase">Terakhir</span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-slate-500 mt-0.5">
                                        SK: {{ $rg->nomor_sk ?: '-' }} • TMT: {{ $rg->tmt_golongan ? \Carbon\Carbon::parse($rg->tmt_golongan)->format('d/m/Y') : '-' }}
                                    </div>
                                </div>
                                <button type="button" wire:click="editGolongan({{ $rg->id }})" @click="golModalOpen = true"
                                    class="shrink-0 px-2.5 py-1.5 rounded-xl bg-white border border-slate-200 text-sky-600 text-[11px] font-bold hover:bg-sky-50 cursor-pointer">
                                    <i class="fa-solid fa-pen text-[10px]"></i>
                                </button>
                            </div>
                        @empty
                            <div class="text-center py-4 text-xs text-slate-400">Belum ada riwayat pangkat &amp; golongan.</div>
                        @endforelse
                    </div>

                    <x-mobile.rhoe-modal model="golModalOpen" :title="$editingGolonganId ? 'Edit Pangkat & Golongan' : 'Tambah Pangkat & Golongan'">
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Golongan Ruang</label>
                                    <select wire:model.live="gol_golongan" class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                                        <option value="">- Pilih Golongan -</option>
                                        @foreach ($golonganOptions as $gol)
                                            <option value="{{ $gol->value }}">{{ $gol->getLabel() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pangkat</label>
                                    <input type="text" wire:model="gol_nama_pangkat" readonly placeholder="Otomatis dari golongan"
                                        class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed focus:outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor SK Pangkat</label>
                                <input type="text" wire:model="gol_nomor_sk" placeholder="Nomor SK..." class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal SK</label>
                                    <x-mobile.date-picker wire:model="gol_tanggal_sk" clearable />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">TMT Golongan</label>
                                    <x-mobile.date-picker wire:model="gol_tmt_golongan" clearable />
                                </div>
                            </div>
                            <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                                <input type="checkbox" wire:model="gol_is_terakhir" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                                <span>Jadikan Pangkat Terakhir</span>
                            </label>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Upload File SK (PDF, Maks. 2 MB)</label>
                                <label class="flex flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50/70 px-3 py-3 text-center cursor-pointer hover:border-sky-500 transition-colors">
                                    <i class="fa-solid fa-file-pdf text-lg text-rose-500"></i>
                                    <span class="text-[11px] font-semibold text-slate-700">
                                        {{ $gol_file_sk ? $gol_file_sk->getClientOriginalName() : 'Ketuk untuk pilih file SK (PDF)' }}
                                    </span>
                                    <input type="file" wire:model="gol_file_sk" accept="application/pdf,.pdf" class="hidden">
                                </label>
                                <div wire:loading wire:target="gol_file_sk" class="text-[11px] text-sky-600 font-medium mt-1">
                                    <i class="fa-solid fa-spinner fa-spin mr-1"></i> Mengunggah SK...
                                </div>
                                @error('gol_file_sk') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <x-slot:footer>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="golModalOpen = false"
                                    class="flex-1 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer">
                                    Batal
                                </button>
                                <button type="button" @click="golModalOpen = false; $wire.save()"
                                    class="flex-1 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold cursor-pointer">
                                    Simpan
                                </button>
                            </div>
                        </x-slot:footer>
                    </x-mobile.rhoe-modal>
                </div>
                    @elseif ($activeTab === 'jabatan')
                        <div class="grid grid-cols-1 gap-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Kepegawaian</label>
                                    <select wire:model="status_kepegawaian" class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                                        <option value="">- Pilih Status -</option>
                                        @foreach ($statusKepegawaianOptions as $st)
                                            <option value="{{ $st->value }}">{{ $st->getLabel() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Jabatan Saat Ini</label>
                                    <select wire:model="jabatan_id" class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                                        <option value="">- Pilih Jabatan -</option>
                                        @foreach ($jabatanOptions as $jab)
                                            <option value="{{ $jab->id }}">{{ $jab->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Tingkat Eselon Jabatan</label>
                                    <select wire:model="eselon" class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                                        <option value="">- Non Eselon -</option>
                                        @foreach ($eselonOptions as $es)
                                            <option value="{{ $es->value }}">{{ $es->getLabel() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Bidang Dinas</label>
                                    <select wire:model="bidang_id" class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                                        <option value="">- Kepala Dinas / Tanpa Bidang -</option>
                                        @foreach ($bidangOptions as $bidang)
                                            <option value="{{ $bidang->id }}">{{ $bidang->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Atasan Langsung</label>
                                <select wire:model="atasan_id" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                                    <option value="">- Pimpinan Tertinggi -</option>
                                    @foreach ($atasanOptions as $atasan)
                                        <option value="{{ $atasan->id }}">{{ $atasan->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-100 space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-extrabold text-sky-700 uppercase tracking-wider">Riwayat Jabatan</span>
                                <button type="button" wire:click="newJabatan"
                                    class="px-3 py-1.5 rounded-xl text-[11px] font-bold flex items-center gap-1.5 cursor-pointer transition border {{ $editingJabatanId === null ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border-emerald-200' }}">
                                    <i class="fa-solid fa-plus text-[10px]"></i>
                                    <span>Tambah Baru</span>
                                </button>
                            </div>
                            @if ($user->riwayatJabatan->isNotEmpty())
                                <div class="flex gap-1.5 overflow-x-auto pb-1">
                                    @foreach ($user->riwayatJabatan as $rj)
                                        <button type="button" wire:click="editJabatan({{ $rj->id }})"
                                            class="px-2.5 py-1.5 rounded-xl text-[11px] font-bold whitespace-nowrap cursor-pointer border flex items-center gap-1 {{ $editingJabatanId === $rj->id ? 'bg-sky-600 text-white border-sky-600' : 'bg-slate-50 text-slate-600 border-slate-200' }}">
                                            <i class="fa-solid fa-pen text-[9px]"></i>
                                            <span>{{ \Illuminate\Support\Str::limit($rj->nama_jabatan, 24) }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                            <div class="px-3 py-2 rounded-xl text-[11px] font-semibold {{ $editingJabatanId === null ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-sky-50 text-sky-700 border border-sky-200' }}">
                                {{ $editingJabatanId === null ? 'Mode Tambah Data Baru — isi form di bawah lalu tekan Simpan Perubahan.' : 'Mode Edit Data Terpilih — atau tekan + Tambah Baru untuk menambah riwayat.' }}
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Jabatan</label>
                                <input type="text" wire:model="jab_nama_jabatan" placeholder="Contoh: Kepala Bidang..." class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Jabatan</label>
                                    <select wire:model="jab_jenis_jabatan" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                                        <option value="Struktural">Struktural</option>
                                        <option value="Fungsional">Fungsional</option>
                                        <option value="Pelaksana">Pelaksana</option>
                                        <option value="PPPK">PPPK</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Unit Kerja</label>
                                    <input type="text" wire:model="jab_unit_kerja" placeholder="Dinas / Bagian" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor SK Jabatan</label>
                                <input type="text" wire:model="jab_nomor_sk" placeholder="Nomor SK..." class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal SK</label>
                                    <x-mobile.date-picker wire:model="jab_tanggal_sk" clearable />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">TMT Jabatan</label>
                                    <x-mobile.date-picker wire:model="jab_tmt_jabatan" clearable />
                                </div>
                            </div>
                            <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                                <input type="checkbox" wire:model="jab_is_sekarang" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                                <span>Jadikan Jabatan Sekarang</span>
                            </label>
                        </div>
                    @elseif ($activeTab === 'pendidikan')
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-[11px] font-extrabold text-sky-700 uppercase tracking-wider">Riwayat Pendidikan Formal</span>
                            <button type="button" wire:click="newPendidikan"
                                class="px-3 py-1.5 rounded-xl text-[11px] font-bold flex items-center gap-1.5 cursor-pointer transition border {{ $editingPendidikanId === null ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border-emerald-200' }}">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                <span>Tambah Baru</span>
                            </button>
                        </div>
                        @if ($user->riwayatPendidikan->isNotEmpty())
                            <div class="flex gap-1.5 overflow-x-auto pb-1">
                                @foreach ($user->riwayatPendidikan as $rp)
                                    <button type="button" wire:click="editPendidikan({{ $rp->id }})"
                                        class="px-2.5 py-1.5 rounded-xl text-[11px] font-bold whitespace-nowrap cursor-pointer border flex items-center gap-1 {{ $editingPendidikanId === $rp->id ? 'bg-sky-600 text-white border-sky-600' : 'bg-slate-50 text-slate-600 border-slate-200' }}">
                                        <i class="fa-solid fa-pen text-[9px]"></i>
                                        <span>{{ $rp->jenjang?->value ?? (string) $rp->jenjang }} - {{ \Illuminate\Support\Str::limit($rp->jurusan ?: $rp->nama_institusi, 18) }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                        <div class="px-3 py-2 rounded-xl text-[11px] font-semibold {{ $editingPendidikanId === null ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-sky-50 text-sky-700 border border-sky-200' }}">
                            {{ $editingPendidikanId === null ? 'Mode Tambah Data Baru — isi form di bawah lalu tekan Simpan Perubahan.' : 'Mode Edit Data Terpilih — atau tekan + Tambah Baru untuk menambah riwayat.' }}
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jenjang</label>
                                <select wire:model="pend_jenjang" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                                    <option value="">- Pilih Jenjang -</option>
                                    @foreach ($jenjangOptions as $jen)
                                        <option value="{{ $jen->value }}">{{ $jen->getLabel() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Lulus</label>
                                <input type="number" wire:model="pend_tahun_lulus" placeholder="2020" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Sekolah / Perguruan Tinggi</label>
                            <input type="text" wire:model="pend_nama_institusi" placeholder="Contoh: Universitas Andalas" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jurusan / Prodi</label>
                                <input type="text" wire:model="pend_jurusan" placeholder="Hukum / Manajemen" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Ijazah</label>
                                <input type="text" wire:model="pend_nomor_ijazah" placeholder="Nomor Ijazah..." class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            </div>
                        </div>
                        <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                            <input type="checkbox" wire:model="pend_is_terakhir" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                            <span>Jadikan Pendidikan Terakhir</span>
                        </label>
                    @elseif ($activeTab === 'administrasi')
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">NPWP</label>
                                <input type="text" wire:model="npwp" x-mask="99.999.999.9-999.999" placeholder="99.999.999.9-999.999"
                                    class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal NPWP</label>
                                <x-mobile.date-picker wire:model="tanggal_npwp" clearable />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">No. BPJS</label>
                                <input type="text" wire:model="no_bpjs" inputmode="numeric" @input="$el.value = $el.value.replace(/[^0-9]/g, '')" placeholder="Nomor BPJS..."
                                    class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Taspen</label>
                                <input type="text" wire:model="no_taspen" inputmode="numeric" @input="$el.value = $el.value.replace(/[^0-9]/g, '')" placeholder="Nomor Taspen..."
                                    class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">NIK</label>
                                <input type="text" wire:model="nik" inputmode="numeric" maxlength="16" @input="$el.value = $el.value.replace(/[^0-9]/g, '')" placeholder="16 digit NIK..."
                                    class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">No. KK</label>
                                <input type="text" wire:model="no_kk" inputmode="numeric" maxlength="16" @input="$el.value = $el.value.replace(/[^0-9]/g, '')" placeholder="16 digit No. KK..."
                                    class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono">
                            </div>
                        </div>
                    @elseif ($activeTab === 'keluarga')
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">NIK</label>
                                <input type="text" wire:model="nik" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">No. KK</label>
                                <input type="text" wire:model="no_kk" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Keluarga</label>
                            <textarea wire:model="alamat" rows="2" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none"></textarea>
                        </div>
                    @else

                    @if ($errors->any())
                        <div
                            class="p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold space-y-1">
                            <div class="flex items-center text-rose-800">
                                <i class="fa-solid fa-circle-exclamation mr-1.5 text-sm"></i>
                                <span>Mohon periksa isian berikut:</span>
                            </div>
                            <ul class="list-disc list-inside text-[11px] font-medium text-rose-600 pl-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                            <input type="text" wire:model="name"
                                class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            @error('name')
                                <span class="text-[11px] text-rose-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Gelar Depan</label>
                                <input type="text" wire:model="gelar_depan" placeholder="Dr., Ir."
                                    class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Gelar Belakang</label>
                                <input type="text" wire:model="gelar_belakang" placeholder="S.Sos, S.Kom"
                                    class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">NIP</label>
                            <input type="text" wire:model="nip" x-mask="99999999 999999 9 999" inputmode="numeric" placeholder="19840426 200902 2 001"
                                class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Kelamin</label>
                                <select wire:model="jenis_kelamin" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                                    <option value="">- Pilih -</option>
                                    <option value="L">Laki-laki (L)</option>
                                    <option value="P">Perempuan (P)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Agama</label>
                                <select wire:model="agama" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                                    <option value="">- Pilih -</option>
                                    <option value="Islam">Islam</option>
                                    <option value="Kristen">Kristen</option>
                                    <option value="Katolik">Katolik</option>
                                    <option value="Hindu">Hindu</option>
                                    <option value="Buddha">Buddha</option>
                                    <option value="Konghucu">Konghucu</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tempat Lahir</label>
                                <input type="text" wire:model="tempat_lahir" placeholder="Contoh: Padang" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Lahir</label>
                                <x-mobile.date-picker wire:model="tanggal_lahir" clearable />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center">
                                <i class="fa-brands fa-whatsapp text-emerald-600 mr-1 text-sm"></i>
                                <span>No. WhatsApp</span>
                            </label>
                            <input type="tel" wire:model="no_hp" inputmode="numeric" @input="$el.value = $el.value.replace(/[^0-9]/g, '')" placeholder="Contoh: 081234567890"
                                class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none font-mono">
                            <p class="text-[10px] text-slate-400 mt-1">Format: 08xxxxxxxxxx</p>
                            @error('no_hp')
                                <span class="text-[11px] font-bold text-rose-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No. Telepon</label>
                            <input type="tel" wire:model="no_telp" inputmode="numeric" @input="$el.value = $el.value.replace(/[^0-9]/g, '')" placeholder="Nomor telepon..."
                                class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono">
                        </div>
                    </div>

                    <div x-data="{ copied: false }">
                        <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                            <span class="flex items-center">
                                <i class="fa-solid fa-envelope mr-1 text-sky-600"></i>
                                Email Login
                            </span>
                            <span class="text-[10px] text-amber-600 font-bold">*Wajib</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="email" wire:model="email" placeholder="email@dinas.go.id"
                                class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono">
                            <button type="button"
                                @click="navigator.clipboard?.writeText($wire.email || ''); copied = true; setTimeout(() => copied = false, 1500)"
                                class="shrink-0 px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-600 text-xs font-bold transition cursor-pointer">
                                <i class="fa-regular" :class="copied ? 'fa-circle-check text-emerald-600' : 'fa-copy'"></i>
                            </button>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1" x-text="copied ? 'Email disalin!' : 'Akun login di database'"></p>
                        @error('email')
                            <span class="text-[11px] font-bold text-rose-500 block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Domisili</label>
                        <textarea wire:model="alamat" rows="2"
                            class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 space-y-3">
                        <span class="text-[11px] font-extrabold text-sky-700 uppercase tracking-wider block">Administrasi &amp; Jaminan</span>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">NPWP</label>
                            <input type="text" wire:model="npwp" x-mask="99.999.999.9-999.999" placeholder="99.999.999.9-999.999"
                                class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal NPWP</label>
                            <x-mobile.date-picker wire:model="tanggal_npwp" clearable />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No. BPJS</label>
                            <input type="text" wire:model="no_bpjs" inputmode="numeric" @input="$el.value = $el.value.replace(/[^0-9]/g, '')" placeholder="Nomor BPJS..."
                                class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Taspen</label>
                            <input type="text" wire:model="no_taspen" inputmode="numeric" @input="$el.value = $el.value.replace(/[^0-9]/g, '')" placeholder="Nomor Taspen..."
                                class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono">
                        </div>
                    </div>

                    @if ($fullEditMode)
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">NIK</label>
                                <input type="text" wire:model="nik" inputmode="numeric" maxlength="16" @input="$el.value = $el.value.replace(/[^0-9]/g, '')" placeholder="16 digit NIK..."
                                    class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">No. KK</label>
                                <input type="text" wire:model="no_kk" inputmode="numeric" maxlength="16" @input="$el.value = $el.value.replace(/[^0-9]/g, '')" placeholder="16 digit No. KK..."
                                    class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jenjang Pendidikan</label>
                                <input type="text" wire:model="jenjang_pendidikan" placeholder="S1 / S2"
                                    class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jurusan / Pendidikan</label>
                                <input type="text" wire:model="pendidikan" placeholder="Manajemen / Hukum"
                                    class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            </div>
                        </div>
                    @endif

                    <!-- Keamanan & Password Akun (Hanya Diisi Ketika Mau Diganti Saja) -->
                    <div class="border-t border-slate-100 pt-3.5 space-y-3">
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                                <span class="flex items-center text-slate-800">
                                    <i class="fa-solid fa-lock text-sky-600 mr-1.5"></i> Password Baru
                                </span>
                                <span class="text-[10px] text-slate-400 font-normal">Abaikan jika tidak merubah</span>
                            </label>
                            <input type="password" wire:model="password" readonly
                                onfocus="this.removeAttribute('readonly');" autocomplete="new-password"
                                placeholder="Kosongkan jika tidak ingin merubah..."
                                class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            <p class="text-[10px] text-slate-400 mt-1">Kosongkan jika password pegawai tidak ingin
                                dirubah.
                            </p>
                            @error('password')
                                <span class="text-[11px] font-bold text-rose-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Konfirmasi Password Baru</label>
                            <input type="password" wire:model="password_confirmation" readonly
                                onfocus="this.removeAttribute('readonly');" autocomplete="new-password"
                                placeholder="Ketik ulang password baru jika ingin merubah..."
                                class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            @error('password_confirmation')
                                <span class="text-[11px] font-bold text-rose-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    @endif

                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full py-3 bg-sky-600 hover:bg-sky-700 disabled:opacity-50 text-white rounded-2xl font-bold text-xs shadow-md shadow-sky-600/30 active:scale-95 transition-all flex items-center justify-center space-x-2">
                        <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
                        <span wire:loading wire:target="save" class="flex items-center space-x-2">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                            <span>Menyimpan...</span>
                        </span>
                    </button>
                </div>
            </form>
        @else
            <!-- TAB 0: STATISTIK KEHADIRAN (8-CARD & NAVIGASI BULAN) -->
            @if ($activeTab === 'statistik')
                <div class="space-y-4">
                    <div
                        class="flex justify-between items-center bg-white p-3.5 rounded-3xl border border-slate-100 shadow-xs">
                        <div>
                            <h3 class="text-xs font-extrabold text-slate-800 tracking-tight uppercase">Rekap Bulan</h3>
                            <p class="text-[11px] text-slate-500">Statistik kehadiran bulanan Anda</p>
                        </div>
                        <!-- Month & Year Navigator -->
                        <div
                            class="flex items-center space-x-1.5 bg-slate-50 px-2.5 py-1.5 rounded-2xl border border-slate-200/80 shadow-xs">
                            <button wire:click="prevMonth"
                                class="w-6 h-6 rounded-lg bg-white hover:bg-sky-50 text-slate-500 hover:text-sky-600 flex items-center justify-center transition-colors active:scale-90"
                                title="Bulan Sebelumnya">
                                <i class="fa-solid fa-chevron-left text-[10px]"></i>
                            </button>
                            <span class="text-xs font-extrabold text-sky-700 px-1 select-none font-sans">
                                {{ $bulanNama }}
                            </span>
                            <button wire:click="nextMonth"
                                class="w-6 h-6 rounded-lg bg-white hover:bg-sky-50 text-slate-500 hover:text-sky-600 flex items-center justify-center transition-colors active:scale-90"
                                title="Bulan Berikutnya">
                                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                            </button>
                        </div>
                    </div>

                    <!-- 8 Stat Cards -->
                    <div class="grid grid-cols-4 gap-2">
                        <!-- 1. Hadir -->
                        <a href="{{ route('mobile.riwayat', ['status' => 'hadir', 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                            wire:navigate
                            class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center hover:bg-slate-50 active:scale-95 transition-all">
                            <div
                                class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm mb-1.5 shadow-xs">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <span class="text-lg font-black text-slate-800">{{ $statistik['hadir'] }}</span>
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Hadir</span>
                        </a>

                        <!-- 2. Terlambat -->
                        <a href="{{ route('mobile.riwayat', ['status' => 'terlambat', 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                            wire:navigate
                            class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center hover:bg-slate-50 active:scale-95 transition-all">
                            <div
                                class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm mb-1.5 shadow-xs">
                                <i class="fa-solid fa-clock"></i>
                            </div>
                            <span class="text-lg font-black text-slate-800">{{ $statistik['terlambat'] }}</span>
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Terlambat</span>
                        </a>

                        <!-- 3. Dinas Luar -->
                        <a href="{{ route('mobile.riwayat', ['status' => 'dinas_luar', 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                            wire:navigate
                            class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center hover:bg-slate-50 active:scale-95 transition-all">
                            <div
                                class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-sm mb-1.5 shadow-xs">
                                <i class="fa-solid fa-briefcase"></i>
                            </div>
                            <span class="text-lg font-black text-slate-800">{{ $statistik['dinas_luar'] }}</span>
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Dinas Luar</span>
                        </a>

                        <!-- 4. Pulang Cepat -->
                        <a href="{{ route('mobile.riwayat', ['status' => 'pulang_cepat', 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                            wire:navigate
                            class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center hover:bg-slate-50 active:scale-95 transition-all">
                            <div
                                class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm mb-1.5 shadow-xs">
                                <i class="fa-solid fa-person-walking-dashed-line-arrow-right"></i>
                            </div>
                            <span class="text-lg font-black text-slate-800">{{ $statistik['pulang_cepat'] }}</span>
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Plg Cepat</span>
                        </a>

                        <!-- 5. Izin -->
                        <a href="{{ route('mobile.riwayat', ['status' => 'izin', 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                            wire:navigate
                            class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center hover:bg-slate-50 active:scale-95 transition-all">
                            <div
                                class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm mb-1.5 shadow-xs">
                                <i class="fa-solid fa-calendar-check"></i>
                            </div>
                            <span class="text-lg font-black text-slate-800">{{ $statistik['izin'] }}</span>
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Izin</span>
                        </a>

                        <!-- 6. Sakit -->
                        <a href="{{ route('mobile.riwayat', ['status' => 'sakit', 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                            wire:navigate
                            class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center hover:bg-slate-50 active:scale-95 transition-all">
                            <div
                                class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm mb-1.5 shadow-xs">
                                <i class="fa-solid fa-notes-medical"></i>
                            </div>
                            <span class="text-lg font-black text-slate-800">{{ $statistik['sakit'] }}</span>
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Sakit</span>
                        </a>

                        <!-- 7. Tanpa Keterangan / Alpa -->
                        <a href="{{ route('mobile.riwayat', ['status' => 'tidak_hadir', 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                            wire:navigate
                            class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center hover:bg-slate-50 active:scale-95 transition-all">
                            <div
                                class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-sm mb-1.5 shadow-xs">
                                <i class="fa-solid fa-user-xmark"></i>
                            </div>
                            <span class="text-lg font-black text-rose-600">{{ $statistik['tanpa_keterangan'] }}</span>
                            <span class="text-[9px] text-rose-500 font-bold uppercase tracking-wide">Alpa</span>
                        </a>

                        <!-- 8. LHK Dibuat -->
                        @if (\App\Models\PengaturanAplikasi::isModuleEnabled('lhk'))
                            <a href="{{ route('mobile.lhk.index') }}" wire:navigate
                                class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center hover:bg-slate-50 active:scale-95 transition-all">
                                <div
                                    class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-sm mb-1.5 shadow-xs">
                                    <i class="fa-solid fa-file-signature"></i>
                                </div>
                                <span class="text-lg font-black text-teal-700">{{ $statistik['lhk_dibuat'] }}</span>
                                <span class="text-[9px] text-teal-600 font-bold uppercase tracking-wide">LHK</span>
                            </a>
                        @endif
                    </div>

                    <!-- Cetak PDF Rekap Pintas -->
                    <div
                        class="bg-gradient-to-r from-sky-50 to-teal-50/60 p-4 rounded-3xl border border-sky-100 flex items-center justify-between shadow-xs">
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-10 h-10 rounded-2xl bg-sky-600 text-white flex items-center justify-center text-base shadow-xs">
                                <i class="fa-solid fa-file-pdf"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-800">Cetak Rekap Presensi</h4>
                                <p class="text-[10px] text-slate-500">Unduh PDF riwayat bulan {{ $bulanNama }}</p>
                            </div>
                        </div>
                        <a href="{{ route('mobile.riwayat.cetak-pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                            target="_blank"
                            class="px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-xs active:scale-95 transition-all flex items-center space-x-1.5">
                            <i class="fa-solid fa-download text-[11px]"></i>
                            <span>Unduh PDF</span>
                        </a>
                    </div>
                </div>
            @endif

            <!-- TAB 1: DATA UTAMA -->
            @if ($activeTab === 'utama')
                <div class="bg-white rounded-3xl p-5 shadow-xs border border-slate-100 space-y-3.5">
                    <h3 class="text-xs font-extrabold text-sky-700 uppercase tracking-wider mb-2">Identitas & Kontak
                        Utama</h3>
                    <div class="flex justify-between items-center py-1.5 border-b border-slate-50 text-xs">
                        <span class="text-slate-400 font-medium">NIP</span>
                        <button type="button" x-data="{ copied: false }"
                            @if ($user->nip) @click="navigator.clipboard.writeText('{{ $user->nip }}').then(() => { copied = true; setTimeout(() => copied = false, 1500) })" @endif
                            class="inline-flex items-center gap-1.5 font-bold font-mono text-slate-800 hover:text-amber-600 cursor-pointer">
                            <span>{{ $user->nip ?? '-' }}</span>
                            @if ($user->nip)<i
                                    class="fa-regular text-[10px]"
                                    :class="copied ? 'fa-circle-check text-emerald-500' : 'fa-copy text-slate-400'"></i>
                            @endif
                        </button>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                        <span class="text-slate-400 font-medium">Jenis Kelamin</span>
                        <span
                            class="font-bold text-slate-800">{{ $user->jenis_kelamin?->value ?? ($user->jenis_kelamin ?? '-') }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                        <span class="text-slate-400 font-medium">Tempat, Tgl Lahir</span>
                        <span class="font-bold text-slate-800">{{ $user->tempat_lahir ?? '-' }},
                            {{ $user->tanggal_lahir ? $user->tanggal_lahir->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                        <span class="text-slate-400 font-medium">Agama</span>
                        <span class="font-bold text-slate-800">{{ $user->agama ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-slate-50 text-xs">
                        <span class="text-slate-400 font-medium">No. HP / WhatsApp</span>
                        <button type="button" x-data="{ copied: false }"
                            @if ($user->no_hp) @click="navigator.clipboard.writeText('{{ $user->no_hp }}').then(() => { copied = true; setTimeout(() => copied = false, 1500) })" @endif
                            class="inline-flex items-center gap-1.5 font-bold text-sky-600 hover:text-amber-600 cursor-pointer">
                            <span>{{ $user->no_hp ?? '-' }}</span>
                            @if ($user->no_hp)<i
                                    class="fa-regular text-[10px]"
                                    :class="copied ? 'fa-circle-check text-emerald-500' : 'fa-copy text-slate-400'"></i>
                            @endif
                        </button>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-slate-50 text-xs">
                        <span class="text-slate-400 font-medium">Email User</span>
                        <button type="button" x-data="{ copied: false }"
                            @click="navigator.clipboard.writeText('{{ $user->email }}').then(() => { copied = true; setTimeout(() => copied = false, 1500) })"
                            class="inline-flex items-center gap-1.5 font-bold text-slate-800 hover:text-amber-600 cursor-pointer max-w-[210px]">
                            <span class="truncate">{{ $user->email }}</span>
                            <i class="fa-regular text-[10px] shrink-0"
                                :class="copied ? 'fa-circle-check text-emerald-500' : 'fa-copy text-slate-400'"></i>
                        </button>
                    </div>
                    <div class="py-1.5 text-xs">
                        <span class="text-slate-400 font-medium block mb-1">Alamat Domisili</span>
                        <span class="font-medium text-slate-700 leading-relaxed">{{ $user->alamat ?? '-' }}</span>
                    </div>

                    <div class="pt-3 border-t border-slate-100 space-y-2">
                        <h4 class="text-[11px] font-extrabold text-sky-700 uppercase tracking-wider">Unit Kerja &amp; Atasan</h4>
                        <div class="flex justify-between py-1 text-xs">
                            <span class="text-slate-400 font-medium">Status Kepegawaian</span>
                            <span class="font-bold text-slate-800 text-right">{{ $user->status_kepegawaian?->getLabel() ?? ($user->status_kepegawaian ?? '-') }}</span>
                        </div>
                        <div class="flex justify-between py-1 text-xs">
                            <span class="text-slate-400 font-medium">Jabatan Saat Ini</span>
                            <span class="font-bold text-slate-800 text-right">{{ $user->jabatan->nama ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-1 text-xs">
                            <span class="text-slate-400 font-medium">Bidang Dinas</span>
                            <span class="font-bold text-slate-800 text-right">{{ $user->bidang->nama ?? 'Kepala Dinas' }}</span>
                        </div>
                        <div class="flex justify-between py-1 text-xs">
                            <span class="text-slate-400 font-medium">Atasan Langsung</span>
                            <span class="font-bold text-slate-800 text-right">{{ $user->atasan->name ?? 'Pimpinan Tertinggi' }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- TAB 2: PANGKAT / GOLONGAN -->
            @if ($activeTab === 'administrasi')
                <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-100 space-y-2.5">
                    <h3 class="text-xs font-extrabold text-sky-700 uppercase tracking-wider mb-1">Administrasi &amp; Nomor</h3>
                    <div class="flex justify-between py-1 text-xs border-b border-slate-50">
                        <span class="text-slate-400 font-medium">NPWP</span>
                        <span class="font-bold text-slate-800 font-mono">{{ $user->npwp ?: '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-xs border-b border-slate-50">
                        <span class="text-slate-400 font-medium">Tanggal NPWP</span>
                        <span class="font-bold text-slate-800">{{ $user->tanggal_npwp ? \Carbon\Carbon::parse($user->tanggal_npwp)->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-xs border-b border-slate-50">
                        <span class="text-slate-400 font-medium">No. BPJS</span>
                        <span class="font-bold text-slate-800 font-mono">{{ $user->no_bpjs ?: '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-xs border-b border-slate-50">
                        <span class="text-slate-400 font-medium">Nomor Taspen</span>
                        <span class="font-bold text-slate-800 font-mono">{{ $user->no_taspen ?: '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-xs border-b border-slate-50">
                        <span class="text-slate-400 font-medium">NIK</span>
                        <span class="font-bold text-slate-800 font-mono">{{ $user->nik ?: '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1 text-xs">
                        <span class="text-slate-400 font-medium">No. KK</span>
                        <span class="font-bold text-slate-800 font-mono">{{ $user->no_kk ?: '-' }}</span>
                    </div>
                </div>
            @endif

            @if ($activeTab === 'golongan')
                @php
                    $golTerakhir = $user->riwayatGolongan->firstWhere('is_golongan_terakhir', true)
                        ?? $user->riwayatGolongan->first();
                    $tmtCpns = $user->tmt_cpns ?? $user->riwayatGolongan->sortBy('tmt_golongan')->first()?->tmt_golongan;
                    $masaKerja = $golTerakhir?->masa_kerja_tahun !== null
                        ? "{$golTerakhir->masa_kerja_tahun} Tahun " . ($golTerakhir->masa_kerja_bulan ?? 0) . ' Bulan'
                        : ($tmtCpns
                            ? \Carbon\Carbon::parse($tmtCpns)->diff(now())->format('%y Tahun %m Bulan')
                            : '-');
                @endphp
                <div class="space-y-3" x-data="{ golModalOpen: false }" x-on:open-gol-modal.window="golModalOpen = true">
                    {{-- Ringkasan Golongan Terakhir & Masa Kerja --}}
                    <div class="bg-white rounded-2xl p-3.5 shadow-xs border border-slate-100 space-y-2 text-xs">
                        <div class="flex items-center justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500 flex items-center gap-1.5"><i class="fa-solid fa-shield-halved text-sky-600"></i> Golongan</span>
                            <span class="font-bold text-slate-900">{{ $golTerakhir?->golongan?->value ?? ($golTerakhir?->golongan ?? '-') }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500 flex items-center gap-1.5"><i class="fa-solid fa-award text-sky-600"></i> Pangkat</span>
                            <span class="font-bold text-slate-900">{{ $golTerakhir?->nama_pangkat ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1 border-b border-slate-50">
                            <span class="text-slate-500 flex items-center gap-1.5"><i class="fa-regular fa-calendar text-sky-600"></i> TMT Golongan</span>
                            <span class="font-bold text-slate-900">{{ $golTerakhir?->tmt_golongan ? \Carbon\Carbon::parse($golTerakhir->tmt_golongan)->translatedFormat('j F Y') : '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1">
                            <span class="text-slate-500 flex items-center gap-1.5"><i class="fa-regular fa-clock text-sky-600"></i> Masa Kerja</span>
                            <span class="font-bold text-slate-900">{{ $masaKerja }}</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between px-1 pt-1">
                        <h3
                            class="text-xs font-extrabold text-sky-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-shield-halved"></i> Riwayat Pangkat & Golongan Ruang
                        </h3>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-semibold text-slate-400 bg-slate-100 px-2.5 py-0.5 rounded-full">
                                {{ $user->riwayatGolongan->count() }} Pangkat
                            </span>
                            <button type="button" wire:click="newGolongan"
                                class="px-2.5 py-1 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold flex items-center gap-1 shadow-xs cursor-pointer">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                <span>Tambah</span>
                            </button>
                        </div>
                    </div>

                    @forelse($user->riwayatGolongan as $item)
                        <div
                            class="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 space-y-2 hover:border-sky-200 transition-all">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <span
                                            class="px-2 py-0.5 rounded-md text-[10px] font-extrabold tracking-wide uppercase bg-sky-50 text-sky-700 border border-sky-200">
                                            {{ $item->golongan?->value ?? (string) $item->golongan }}
                                        </span>
                                        @if ($item->is_golongan_terakhir)
                                            <span
                                                class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                Pangkat Terakhir
                                            </span>
                                        @endif
                                    </div>
                                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight">
                                        {{ $item->golongan?->value ?? (string) $item->golongan }} -
                                        {{ $item->nama_pangkat }}
                                    </h4>
                                    <p class="text-[11px] font-mono text-slate-500 mt-0.5">
                                        {{ $item->nomor_sk ?: '-' }}
                                    </p>
                                </div>
                                @if ($item->tmt_golongan)
                                    <span
                                        class="text-[11px] font-bold text-slate-600 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-100 shrink-0">
                                        {{ \Carbon\Carbon::parse($item->tmt_golongan)->translatedFormat('d F Y') }}
                                    </span>
                                @endif
                            </div>

                            <div class="pt-2 border-t border-slate-50 flex items-center justify-between text-[11px]">
                                <span class="text-slate-400">
                                    TMT: <strong
                                        class="text-slate-700">{{ $item->tmt_golongan ? \Carbon\Carbon::parse($item->tmt_golongan)->format('d/m/Y') : '-' }}</strong>
                                </span>

                                <div class="flex items-center gap-1.5">
                                    @if ($item->file_sk_golongan)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->file_sk_golongan) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 font-bold text-sky-600 hover:text-sky-700 bg-sky-50 hover:bg-sky-100 px-2.5 py-1 rounded-lg transition-colors">
                                            <i class="fa-solid fa-file-pdf text-red-500"></i> Unduh SK
                                        </a>
                                    @else
                                        <span class="text-slate-300 italic text-[10px]">SK belum diunggah</span>
                                    @endif
                                    <button type="button" wire:click="editGolongan({{ $item->id }})"
                                        class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-sky-50 text-sky-600 flex items-center justify-center transition-colors cursor-pointer"
                                        title="Edit Pangkat">
                                        <i class="fa-solid fa-pen text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-3xl p-5 shadow-xs border border-slate-100 text-center py-6">
                            <p class="text-xs text-slate-400 italic">Belum ada data riwayat pangkat/golongan.</p>
                        </div>
                    @endforelse

                    <x-mobile.rhoe-modal model="golModalOpen" :title="$editingGolonganId ? 'Edit Pangkat & Golongan' : 'Tambah Pangkat & Golongan'">
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Golongan Ruang</label>
                                    <select wire:model.live="gol_golongan" class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                                        <option value="">- Pilih Golongan -</option>
                                        @foreach ($golonganOptions as $gol)
                                            <option value="{{ $gol->value }}">{{ $gol->getLabel() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pangkat</label>
                                    <input type="text" wire:model="gol_nama_pangkat" readonly placeholder="Otomatis dari golongan"
                                        class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed focus:outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nomor SK Pangkat</label>
                                <input type="text" wire:model="gol_nomor_sk" placeholder="Nomor SK..." class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal SK</label>
                                    <x-mobile.date-picker wire:model="gol_tanggal_sk" clearable />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">TMT Golongan</label>
                                    <x-mobile.date-picker wire:model="gol_tmt_golongan" clearable />
                                </div>
                            </div>
                            <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                                <input type="checkbox" wire:model="gol_is_terakhir" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                                <span>Jadikan Pangkat Terakhir</span>
                            </label>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Upload File SK (PDF, Maks. 2 MB)</label>
                                <label class="flex flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-slate-200 bg-slate-50/70 px-3 py-3 text-center cursor-pointer hover:border-sky-500 transition-colors">
                                    <i class="fa-solid fa-file-pdf text-lg text-rose-500"></i>
                                    <span class="text-[11px] font-semibold text-slate-700">
                                        {{ $gol_file_sk ? $gol_file_sk->getClientOriginalName() : 'Ketuk untuk pilih file SK (PDF)' }}
                                    </span>
                                    <input type="file" wire:model="gol_file_sk" accept="application/pdf,.pdf" class="hidden">
                                </label>
                                <div wire:loading wire:target="gol_file_sk" class="text-[11px] text-sky-600 font-medium mt-1">
                                    <i class="fa-solid fa-spinner fa-spin mr-1"></i> Mengunggah SK...
                                </div>
                                @error('gol_file_sk') <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <x-slot:footer>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="golModalOpen = false"
                                    class="flex-1 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer">
                                    Batal
                                </button>
                                <button type="button" @click="golModalOpen = false; $wire.save()"
                                    class="flex-1 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold cursor-pointer">
                                    Simpan
                                </button>
                            </div>
                        </x-slot:footer>
                    </x-mobile.rhoe-modal>
                </div>
            @endif

            <!-- TAB 3: JABATAN -->
            @if ($activeTab === 'jabatan')
                <div class="space-y-3">
                    <!-- Info Eselon -->
                    <div
                        class="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 flex items-center justify-between">
                        <div>
                            <span class="text-slate-400 text-xs font-medium block">Tingkat Eselon Jabatan</span>
                            <span
                                class="font-extrabold text-slate-900 text-sm">{{ $user->eselon?->value ?? ($user->eselon ?? 'Non Eselon') }}</span>
                        </div>
                        <span
                            class="px-2.5 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                            {{ $user->eselon?->value ?? 'Non Eselon' }}
                        </span>
                    </div>

                    <!-- Riwayat Jabatan -->
                    <div x-data="{ jabModalOpen: false }" x-on:open-jabatan-modal.window="jabModalOpen = true" class="space-y-3">
                    <div class="flex items-center justify-between px-1 pt-2">
                        <h3
                            class="text-xs font-extrabold text-sky-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-briefcase"></i> Riwayat Jabatan
                        </h3>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-semibold text-slate-400 bg-slate-100 px-2.5 py-0.5 rounded-full">
                                {{ $user->riwayatJabatan->count() }} Jabatan
                            </span>
                            <button type="button" wire:click="newJabatan"
                                class="px-2.5 py-1 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold flex items-center gap-1 shadow-xs cursor-pointer">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                <span>Tambah</span>
                            </button>
                        </div>
                    </div>

                    @forelse($user->riwayatJabatan as $item)
                        <div
                            class="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 space-y-2 hover:border-sky-200 transition-all">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <span
                                            class="px-2 py-0.5 rounded-md text-[10px] font-extrabold tracking-wide uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ $item->jenis_jabatan }}
                                        </span>
                                        @if ($item->is_jabatan_sekarang)
                                            <span
                                                class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-primary-50 text-primary-600 border border-primary-200">
                                                Sekarang
                                            </span>
                                        @endif
                                    </div>
                                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight">
                                        {{ $item->nama_jabatan }}
                                    </h4>
                                    @if ($item->unit_kerja)
                                        <p class="text-[11px] font-medium text-slate-500 mt-0.5">
                                            {{ $item->unit_kerja }}
                                        </p>
                                    @endif
                                </div>
                                @if ($item->tmt_jabatan)
                                    <span
                                        class="text-[11px] font-bold text-slate-600 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-100 shrink-0">
                                        {{ \Carbon\Carbon::parse($item->tmt_jabatan)->translatedFormat('d F Y') }}
                                    </span>
                                @endif
                            </div>

                            <div class="pt-2 border-t border-slate-50 flex items-center justify-between text-[11px]">
                                <span class="text-slate-400">
                                    SK: <strong class="text-slate-700">{{ $item->nomor_sk ?: '-' }}</strong>
                                </span>

                                <div class="flex items-center gap-1.5">
                                    @if ($item->file_sk_jabatan)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->file_sk_jabatan) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 font-bold text-sky-600 hover:text-sky-700 bg-sky-50 hover:bg-sky-100 px-2.5 py-1 rounded-lg transition-colors">
                                            <i class="fa-solid fa-file-pdf text-red-500"></i> Unduh SK
                                        </a>
                                    @else
                                        <span class="text-slate-300 italic text-[10px]">SK belum diunggah</span>
                                    @endif
                                    <button type="button" wire:click="editJabatan({{ $item->id }})"
                                        class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-sky-50 text-sky-600 flex items-center justify-center transition-colors cursor-pointer"
                                        title="Edit Jabatan">
                                        <i class="fa-solid fa-pen text-[10px]"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-3xl p-5 shadow-xs border border-slate-100 text-center py-6">
                            <p class="text-xs text-slate-400 italic">Belum ada data riwayat jabatan.</p>
                        </div>
                    @endforelse

                    <x-mobile.rhoe-modal model="jabModalOpen" :title="$editingJabatanId ? 'Edit Riwayat Jabatan' : 'Tambah Riwayat Jabatan'">
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Jabatan</label>
                                <select wire:model="jab_jenis" class="w-full text-xs font-medium px-3 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none bg-white">
                                    <option value="">- Pilih Jenis -</option>
                                    <option value="Struktural">Struktural</option>
                                    <option value="Fungsional">Fungsional</option>
                                    <option value="Pelaksana">Pelaksana</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Jabatan</label>
                                <input type="text" wire:model="jab_nama" placeholder="Contoh: Analis Kebijakan Ahli Muda" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Unit Kerja</label>
                                <input type="text" wire:model="jab_unit_kerja" placeholder="Unit kerja..." class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor SK</label>
                                    <input type="text" wire:model="jab_nomor_sk" placeholder="Nomor SK..." class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">TMT Jabatan</label>
                                    <x-mobile.date-picker wire:model="jab_tmt_jabatan" clearable />
                                </div>
                            </div>
                            <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                                <input type="checkbox" wire:model="jab_is_sekarang" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                                <span>Jabatan Saat Ini</span>
                            </label>
                        </div>
                        <x-slot:footer>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="jabModalOpen = false"
                                    class="flex-1 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer">
                                    Batal
                                </button>
                                <button type="button" @click="jabModalOpen = false; $wire.save()"
                                    class="flex-1 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold cursor-pointer">
                                    Simpan
                                </button>
                            </div>
                        </x-slot:footer>
                    </x-mobile.rhoe-modal>
                    </div>
                </div>
            @endif

            <!-- TAB 4: PENDIDIKAN -->
            @if ($activeTab === 'pendidikan')
                <div class="space-y-3" x-data="{ pendModalOpen: false }" x-on:open-pendidikan-modal.window="pendModalOpen = true">
                    <div class="flex items-center justify-between px-1">
                        <h3
                            class="text-xs font-extrabold text-sky-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-graduation-cap"></i> Riwayat Pendidikan Formal
                        </h3>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] font-semibold text-slate-400 bg-slate-100 px-2.5 py-0.5 rounded-full">
                                {{ $user->riwayatPendidikan->count() }} Jenjang
                            </span>
                            <button type="button" wire:click="newPendidikan"
                                class="px-2.5 py-1 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold flex items-center gap-1 shadow-xs cursor-pointer">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                <span>Tambah</span>
                            </button>
                        </div>
                    </div>

                    @forelse($user->riwayatPendidikan as $item)
                        <div
                            class="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 space-y-2 hover:border-sky-200 transition-all">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <span
                                            class="px-2 py-0.5 rounded-md text-[10px] font-extrabold tracking-wide uppercase {{ match ($item->jenjang?->value ?? (string) $item->jenjang) {
                                                'S3', 'S2' => 'bg-amber-50 text-amber-700 border border-amber-200',
                                                'S1', 'D4' => 'bg-emerald-50 text-emerald-700 border border-emerald-200',
                                                'D3', 'D2', 'D1' => 'bg-sky-50 text-sky-700 border border-sky-200',
                                                'SLTA' => 'bg-indigo-50 text-indigo-700 border border-indigo-200',
                                                default => 'bg-slate-100 text-slate-700 border border-slate-200',
                                            } }}">
                                            {{ $item->jenjang?->value ?? (string) $item->jenjang }}
                                        </span>
                                        @if ($item->is_pendidikan_terakhir)
                                            <span
                                                class="px-2 py-0.5 rounded-md text-[9px] font-bold bg-primary-50 text-primary-600 border border-primary-200">
                                                Terakhir
                                            </span>
                                        @endif
                                    </div>
                                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight">
                                        {{ $item->jurusan ?: $item->nama_institusi }}
                                    </h4>
                                    <p class="text-[11px] font-medium text-slate-500 mt-0.5">
                                        {{ $item->nama_institusi }}
                                    </p>
                                </div>
                                @if ($item->tahun_lulus)
                                    <span
                                        class="text-xs font-bold text-slate-600 bg-slate-50 px-2 py-1 rounded-lg border border-slate-100 shrink-0">
                                        {{ $item->tahun_lulus }}
                                    </span>
                                @endif
                            </div>

                            <div class="pt-2 border-t border-slate-50 flex items-center justify-between text-[11px]">
                                <span class="text-slate-400">
                                    No. Ijazah: <strong
                                        class="text-slate-700">{{ $item->nomor_ijazah ?: '-' }}</strong>
                                </span>

                                @if ($item->file_ijazah)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->file_ijazah) }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 font-bold text-sky-600 hover:text-sky-700 bg-sky-50 hover:bg-sky-100 px-2.5 py-1 rounded-lg transition-colors">
                                        <i class="fa-solid fa-file-pdf text-red-500"></i> Unduh Ijazah
                                    </a>
                                @else
                                    <span class="text-slate-300 italic text-[10px]">Ijazah belum diunggah</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-3xl p-5 shadow-xs border border-slate-100 space-y-3.5">
                            <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                                <span class="text-slate-400 font-medium">Tingkat Pendidikan</span>
                                <span
                                    class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-100">{{ $user->jenjang_pendidikan ?? '-' }}</span>
                            </div>
                            <div class="py-1.5 border-b border-slate-50 text-xs">
                                <span class="text-slate-400 font-medium block mb-1">Jurusan / Prodi</span>
                                <span class="font-bold text-slate-800">{{ $user->pendidikan ?? '-' }}</span>
                            </div>
                        </div>
                    @endforelse
                </div>
            @endif

            <!-- TAB 5: KELUARGA -->
            @if ($activeTab === 'keluarga')
                <div class="bg-white rounded-3xl p-5 shadow-xs border border-slate-100 space-y-3.5">
                    <h3 class="text-xs font-extrabold text-sky-700 uppercase tracking-wider mb-2">Data Kependudukan
                        Keluarga</h3>
                    <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                        <span class="text-slate-400 font-medium">Nomor KK</span>
                        <span class="font-bold font-mono text-slate-800">{{ $user->no_kk ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                        <span class="text-slate-400 font-medium">NIK</span>
                        <span class="font-bold font-mono text-slate-800">{{ $user->nik ?? '-' }}</span>
                    </div>
                    <div class="py-1.5 text-xs">
                        <span class="text-slate-400 font-medium block mb-1">Alamat Keluarga</span>
                        <span class="font-medium text-slate-700 leading-relaxed">{{ $user->alamat ?? '-' }}</span>
                    </div>
                </div>
            @endif
        @endif

        {{-- <p class="text-center text-[10px] text-slate-400 mt-6">SAKTI Mobile v1.0 &bull; Kabupaten Sijunjung</p> --}}

        @if ($showImpersonateModal)
            <div
                class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-4">
                <div class="android-modal-anim w-full max-w-sm rounded-3xl bg-white p-5 shadow-2xl space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-extrabold text-slate-800">Pindah Akun Pegawai</h3>
                        <button type="button" wire:click="$set('showImpersonateModal', false)"
                            class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center cursor-pointer">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>
                    <input type="text" wire:model.live.debounce.250ms="impersonateSearch"
                        placeholder="Cari nama atau NIP..."
                        class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs focus:border-amber-400 focus:outline-none">
                    <div class="max-h-64 overflow-y-auto divide-y divide-slate-100">
                        @forelse ($impersonateCandidates as $cand)
                            <button type="button" wire:click="impersonateUser({{ $cand->id }})"
                                class="w-full py-2.5 px-2 text-left hover:bg-amber-50 rounded-xl transition flex items-center justify-between cursor-pointer">
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-slate-800 truncate">{{ $cand->name }}</div>
                                    <div class="text-[10px] font-mono text-slate-400">{{ $cand->nip ?? '-' }}</div>
                                </div>
                                <i class="fa-solid fa-arrow-right text-[10px] text-amber-500"></i>
                            </button>
                        @empty
                            <p class="py-6 text-center text-xs text-slate-400">Pegawai tidak ditemukan.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

        <!-- Modern Confirm Logout Modal -->
        <div x-show="confirmLogout" x-cloak x-transition.opacity.duration.150ms
            class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-5">
            <div @click.outside="confirmLogout = false" x-transition.scale.95.duration.150ms
                class="w-full max-w-xs rounded-3xl bg-white p-6 shadow-2xl text-center space-y-4">
                <div
                    class="mx-auto w-14 h-14 rounded-full bg-rose-50 text-rose-500 ring-8 ring-rose-500/10 flex items-center justify-center text-xl">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Keluar dari Akun?</h3>
                    <p class="text-xs text-slate-500 mt-1">Sesi Anda akan diakhiri dan harus masuk kembali untuk
                        mengakses presensi.</p>
                </div>
                <div class="grid grid-cols-2 gap-2.5 pt-1">
                    <button type="button" @click="confirmLogout = false"
                        class="py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer">
                        Batal
                    </button>
                    <button type="button" wire:click="logout"
                        class="py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-extrabold shadow-md shadow-rose-600/25 cursor-pointer">
                        Ya, Keluar
                    </button>
                </div>
            </div>
        </div>
</div>
