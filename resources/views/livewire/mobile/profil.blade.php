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

        <div class="flex items-center space-x-4 relative z-10">
            <div class="relative">
                <img src="{{ $user->avatar_image_url }}" alt="Avatar Pegawai"
                    class="w-16 h-16 rounded-full object-cover aspect-square border-2 border-white/40 shadow-lg shadow-black/20">

                <label
                    class="absolute -bottom-1 -right-1 w-7 h-7 bg-amber-400 hover:bg-amber-300 text-slate-900 rounded-full flex items-center justify-center text-xs cursor-pointer shadow-md font-bold hover:scale-110 active:scale-95 transition-transform ring-2 ring-slate-900"
                    title="Ganti Foto Profil Langsung (Maks. 2MB)">
                    <i class="fa-solid fa-camera"></i>
                    <input type="file" wire:model="new_avatar" class="hidden" accept="image/*">
                </label>
            </div>

            <div class="flex-1 min-w-0">
                <div
                    class="inline-block px-2.5 py-0.5 rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-[10px] font-extrabold text-sky-200 uppercase tracking-wider mb-1">
                    {{ $user->jabatan->nama ?? 'Pegawai' }}
                </div>
                @php
                    $namaLengkap = $user->name;
                    if ($user->gelar_depan && !str_starts_with($namaLengkap, $user->gelar_depan)) {
                        $namaLengkap = $user->gelar_depan . ' ' . $namaLengkap;
                    }
                    if ($user->gelar_belakang && !str_contains($namaLengkap, $user->gelar_belakang)) {
                        $namaLengkap .= ', ' . $user->gelar_belakang;
                    }
                @endphp
                <h2 class="text-base font-extrabold truncate tracking-tight text-white drop-shadow-xs">
                    {{ trim($namaLengkap) }}
                </h2>
                <div class="flex items-center text-xs text-sky-100 font-mono mt-1 font-medium">
                    <span class="text-amber-300 font-bold mr-1.5">#</span>
                    <span>NIP: {{ $user->nip ?? '-' }}</span>
                </div>
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
                <i class="fa-solid fa-pen-to-square mr-1.5"></i>Mode Edit Profil
            </div>
        @endif
    </div>

    @if (!$isEditing)
        <div class="flex justify-end mb-3">
            <button type="button" wire:click="toggleEdit(true)"
                class="px-3 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-600 text-xs font-bold flex items-center gap-1.5 border border-sky-200/70 transition active:scale-95 cursor-pointer">
                <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                <span>Ubah Data</span>
            </button>
        </div>
    @endif

    <!-- FORM EDIT / VIEW -->
        @if ($isEditing)
            <form wire:submit="save" class="space-y-4">
                <div class="bg-white p-5 rounded-3xl shadow-xs border border-slate-100 space-y-4">
                    <h3 class="text-xs font-extrabold text-sky-700 uppercase tracking-wider mb-2">Edit Biodata & Kontak
                    </h3>

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

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center">
                                <i class="fa-brands fa-whatsapp text-emerald-600 mr-1 text-sm"></i>
                                <span>No. WhatsApp</span>
                            </label>
                            <input type="tel" wire:model="no_hp" placeholder="Contoh: 081234567890"
                                class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none font-mono">
                            <p class="text-[10px] text-slate-400 mt-1">Format: 08xxxxxxxxxx</p>
                            @error('no_hp')
                                <span class="text-[11px] font-bold text-rose-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">No. Telepon</label>
                            <input type="text" wire:model="no_telp"
                                class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label
                                class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                                <span class="flex items-center"><i class="fa-solid fa-envelope mr-1 text-sky-600"></i>
                                    Email
                                    Login</span>
                                <span class="text-[10px] text-amber-600 font-bold">*Wajib</span>
                            </label>
                            <input type="email" wire:model="email" placeholder="email@dinas.go.id"
                                class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none font-mono">
                            <p class="text-[10px] text-slate-400 mt-1">Akun login di database</p>
                            @error('email')
                                <span class="text-[11px] font-bold text-rose-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Domisili</label>
                        <textarea wire:model="alamat" rows="2"
                            class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none"></textarea>
                    </div>

                    @if ($fullEditMode)
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">NIK</label>
                                <input type="text" wire:model="nik"
                                    class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">No. KK</label>
                                <input type="text" wire:model="no_kk"
                                    class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">NPWP</label>
                                <input type="text" wire:model="npwp"
                                    class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">No. BPJS</label>
                                <input type="text" wire:model="no_bpjs"
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
                        <h4 class="text-[11px] font-extrabold text-sky-700 uppercase tracking-wider">Nomor Administrasi
                            &
                            Jaminan</h4>
                        <div class="flex justify-between py-1 text-xs">
                            <span class="text-slate-400 font-medium">NPWP</span>
                            <span class="font-bold font-mono text-slate-800">{{ $user->npwp ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-1 text-xs">
                            <span class="text-slate-400 font-medium">BPJS Kesehatan</span>
                            <span class="font-bold font-mono text-slate-800">{{ $user->no_bpjs ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between py-1 text-xs">
                            <span class="text-slate-400 font-medium">Nomor Taspen</span>
                            <span class="font-bold font-mono text-slate-800">{{ $user->no_taspen ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- TAB 2: PANGKAT / GOLONGAN -->
            @if ($activeTab === 'golongan')
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

                    <div class="flex items-center justify-between px-1 pt-1">
                        <h3
                            class="text-xs font-extrabold text-sky-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-shield-halved"></i> Riwayat Pangkat & Golongan Ruang
                        </h3>
                        <span class="text-[11px] font-semibold text-slate-400 bg-slate-100 px-2.5 py-0.5 rounded-full">
                            {{ $user->riwayatGolongan->count() }} Pangkat
                        </span>
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

                                @if ($item->file_sk_golongan)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->file_sk_golongan) }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 font-bold text-sky-600 hover:text-sky-700 bg-sky-50 hover:bg-sky-100 px-2.5 py-1 rounded-lg transition-colors">
                                        <i class="fa-solid fa-file-pdf text-red-500"></i> Unduh SK Pangkat
                                    </a>
                                @else
                                    <span class="text-slate-300 italic text-[10px]">SK belum diunggah</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-3xl p-5 shadow-xs border border-slate-100 text-center py-6">
                            <p class="text-xs text-slate-400 italic">Belum ada data riwayat pangkat/golongan.</p>
                        </div>
                    @endforelse
                </div>
            @endif

            <!-- TAB 3: JABATAN -->
            @if ($activeTab === 'jabatan')
                <div class="space-y-3">
                    <!-- Posisi Sekarang -->
                    <div class="bg-white rounded-3xl p-5 shadow-xs border border-slate-100 space-y-3.5">
                        <h3 class="text-xs font-extrabold text-sky-700 uppercase tracking-wider mb-2">Jabatan & Shift
                            Kerja
                            Saat Ini</h3>
                        <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                            <span class="text-slate-400 font-medium">Jabatan</span>
                            <span
                                class="font-bold text-slate-800 text-right">{{ $user->jabatan->nama ?? 'Belum Ditentukan' }}</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                            <span class="text-slate-400 font-medium">Bidang Dinas</span>
                            <span
                                class="font-bold text-slate-800 text-right">{{ $user->bidang->nama ?? 'Kepala Dinas' }}</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                            <span class="text-slate-400 font-medium">Atasan Langsung</span>
                            <span
                                class="font-bold text-slate-800 text-right">{{ $user->atasan->name ?? 'Pimpinan Tertinggi' }}</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                            <span class="text-slate-400 font-medium">Tipe Kerja</span>
                            <span
                                class="px-2.5 py-0.5 rounded-full font-bold uppercase {{ strtolower($user->status_kerja ?? 'wfo') === 'wfh' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }}">
                                {{ $user->status_kerja ?? 'WFO' }}
                            </span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                            <span class="text-slate-400 font-medium">Shift Kerja</span>
                            <span
                                class="font-bold text-slate-800">{{ $user->jadwalKerja->nama ?? 'Jadwal Default Sistem' }}</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                            <span class="text-slate-400 font-medium">ID Mesin (PIN)</span>
                            <span
                                class="font-bold font-mono text-slate-800">{{ $user->nomor_absensi ?? 'Belum Terhubung' }}</span>
                        </div>
                    </div>

                    <!-- Riwayat Jabatan -->
                    <div class="flex items-center justify-between px-1 pt-2">
                        <h3
                            class="text-xs font-extrabold text-sky-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-briefcase"></i> Riwayat Jabatan
                        </h3>
                        <span class="text-[11px] font-semibold text-slate-400 bg-slate-100 px-2.5 py-0.5 rounded-full">
                            {{ $user->riwayatJabatan->count() }} Jabatan
                        </span>
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

                                @if ($item->file_sk_jabatan)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->file_sk_jabatan) }}"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 font-bold text-sky-600 hover:text-sky-700 bg-sky-50 hover:bg-sky-100 px-2.5 py-1 rounded-lg transition-colors">
                                        <i class="fa-solid fa-file-pdf text-red-500"></i> Unduh SK Jabatan
                                    </a>
                                @else
                                    <span class="text-slate-300 italic text-[10px]">SK belum diunggah</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-3xl p-5 shadow-xs border border-slate-100 text-center py-6">
                            <p class="text-xs text-slate-400 italic">Belum ada data riwayat jabatan.</p>
                        </div>
                    @endforelse
                </div>
            @endif

            <!-- TAB 4: PENDIDIKAN -->
            @if ($activeTab === 'pendidikan')
                <div class="space-y-3">
                    <div class="flex items-center justify-between px-1">
                        <h3
                            class="text-xs font-extrabold text-sky-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-graduation-cap"></i> Riwayat Pendidikan Formal
                        </h3>
                        <span class="text-[11px] font-semibold text-slate-400 bg-slate-100 px-2.5 py-0.5 rounded-full">
                            {{ $user->riwayatPendidikan->count() }} Jenjang
                        </span>
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

        <!-- TOMBOL PENGATURAN MODUL SISTEM (KHUSUS SUPER ADMIN) -->
        @if (auth()->user()?->hasRole('super_admin'))
            <div class="mt-5">
                <a href="{{ route('mobile.pengaturan-modul') }}" wire:navigate
                    class="w-full bg-white hover:bg-sky-50/70 rounded-2xl p-4 shadow-xs border border-sky-200/80 flex items-center justify-between gap-3 transition active:scale-[0.99]">
                    <div class="flex items-center gap-3 min-w-0">
                        <div
                            class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-sm shrink-0">
                            <i class="fa-solid fa-sliders"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-extrabold text-slate-800">Pengaturan Modul Sistem</p>
                            <p class="text-[10px] text-slate-500 truncate">Aktifkan / nonaktifkan menu Web, Mobile &
                                Android</p>
                        </div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-xs text-sky-600 shrink-0"></i>
                </a>
            </div>
        @endif

        <!-- Tombol Keluar dari Akun (Logout) -->
        <div class="mt-8 pt-4 border-t border-slate-200/80">
            <button type="button" @click="confirmLogout = true"
                class="w-full py-3 px-4 rounded-2xl bg-white hover:bg-rose-50 text-rose-600 font-extrabold text-xs border border-rose-200 shadow-xs flex items-center justify-center space-x-2 transition active:scale-98 cursor-pointer">
                <i class="fa-solid fa-power-off text-rose-500 text-sm"></i>
                <span>Keluar dari Akun (Logout)</span>
            </button>
            <p class="text-center text-[10px] text-slate-400 mt-2.5">SAKTI Mobile v1.0 &bull; Kabupaten Sijunjung</p>
        </div>

        @if ($showImpersonateModal)
            <div
                class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-4">
                <div class="w-full max-w-sm rounded-3xl bg-white p-5 shadow-2xl space-y-3">
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
