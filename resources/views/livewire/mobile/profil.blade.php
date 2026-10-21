<div class="p-5 pb-28">
    <!-- Top Bar -->
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Profil Pegawai</h1>
            <p class="text-xs text-slate-500 font-medium">Informasi & Biodata Kepegawaian</p>
        </div>
        <button wire:click="toggleEdit" 
                class="px-3.5 py-1.5 rounded-xl font-bold text-xs flex items-center transition-all shadow-xs {{ $isEditing ? 'bg-slate-200 text-slate-700' : 'bg-sky-500 text-white shadow-sky-500/25 active:scale-95' }}">
            <i class="fa-solid {{ $isEditing ? 'fa-xmark' : 'fa-pen-to-square' }} mr-1.5"></i>
            {{ $isEditing ? 'Batal' : 'Edit Profil' }}
        </button>
    </div>

    <!-- Header Avatar & Nama -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white rounded-3xl p-5 shadow-xl mb-6 relative overflow-hidden border border-white/10">
        <div class="flex items-center space-x-4">
            <div class="relative">
                <img src="{{ $user->avatar_url ? asset('storage/'.$user->avatar_url) : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=0284c7&color=fff' }}" 
                     alt="Avatar" 
                     class="w-16 h-16 rounded-2xl object-cover border-2 border-white/30 shadow-md">
                @if($isEditing)
                    <label class="absolute -bottom-1 -right-1 w-6 h-6 bg-sky-500 text-white rounded-full flex items-center justify-center text-[10px] cursor-pointer shadow-md">
                        <i class="fa-solid fa-camera"></i>
                        <input type="file" wire:model="new_avatar" class="hidden" accept="image/*">
                    </label>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <span class="text-[10px] font-bold text-sky-400 uppercase tracking-widest">{{ $user->jabatan->nama ?? 'Pegawai' }}</span>
                <h2 class="text-base font-extrabold truncate tracking-tight text-white mt-0.5">
                    {{ trim(($user->gelar_depan ? $user->gelar_depan.' ' : '').$user->name.($user->gelar_belakang ? ', '.$user->gelar_belakang : '')) }}
                </h2>
                <div class="flex items-center text-xs text-white/70 mt-1 font-mono">
                    <i class="fa-solid fa-hashtag text-[10px] text-sky-400 mr-1.5"></i>
                    <span>NIP: {{ $user->nip ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- MyASN Style Navigation Tabs -->
    <div class="flex space-x-2 overflow-x-auto pb-2 mb-5 no-scrollbar">
        <button wire:click="setTab('utama')" 
                class="px-4 py-2 rounded-2xl text-xs font-bold whitespace-nowrap transition-all shadow-xs {{ $activeTab === 'utama' ? 'bg-sky-600 text-white shadow-sky-600/30' : 'bg-white text-slate-600 border border-slate-200/80' }}">
            <i class="fa-solid fa-user text-xs mr-1.5"></i>Data Utama
        </button>
        <button wire:click="setTab('golongan')" 
                class="px-4 py-2 rounded-2xl text-xs font-bold whitespace-nowrap transition-all shadow-xs {{ $activeTab === 'golongan' ? 'bg-sky-600 text-white shadow-sky-600/30' : 'bg-white text-slate-600 border border-slate-200/80' }}">
            <i class="fa-solid fa-shield-halved text-xs mr-1.5"></i>Golongan
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
    </div>

    <!-- FORM EDIT / VIEW -->
    @if($isEditing)
        <form wire:submit.prevent="save" class="space-y-4">
            <div class="bg-white p-5 rounded-3xl shadow-xs border border-slate-100 space-y-4">
                <h3 class="text-xs font-extrabold text-sky-700 uppercase tracking-wider mb-2">Edit Biodata & Kontak</h3>
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap</label>
                    <input type="text" wire:model="name" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    @error('name') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Gelar Depan</label>
                        <input type="text" wire:model="gelar_depan" placeholder="Dr., Ir." class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Gelar Belakang</label>
                        <input type="text" wire:model="gelar_belakang" placeholder="S.IP, M.CIO" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. HP / WA</label>
                        <input type="text" wire:model="no_hp" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. Telepon</label>
                        <input type="text" wire:model="no_telp" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Pribadi</label>
                    <input type="email" wire:model="email_pribadi" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Domisili</label>
                    <textarea wire:model="alamat" rows="2" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none"></textarea>
                </div>

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

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">NPWP</label>
                        <input type="text" wire:model="npwp" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. BPJS</label>
                        <input type="text" wire:model="no_bpjs" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Password Baru (Opsional)</label>
                    <input type="password" wire:model="password" placeholder="Kosongkan jika tidak ganti" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none mb-2">
                    <input type="password" wire:model="password_confirmation" placeholder="Ulangi password baru" class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>

                <button type="submit" class="w-full py-3 bg-sky-600 text-white rounded-2xl font-bold text-xs shadow-md shadow-sky-600/30 active:scale-95 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    @else
        <!-- TAB 1: DATA UTAMA -->
        @if($activeTab === 'utama')
            <div class="bg-white rounded-3xl p-5 shadow-xs border border-slate-100 space-y-3.5">
                <h3 class="text-xs font-extrabold text-sky-700 uppercase tracking-wider mb-2">Identitas & Kontak Utama</h3>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">NIP</span>
                    <span class="font-bold font-mono text-slate-800">{{ $user->nip ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">NIK</span>
                    <span class="font-bold font-mono text-slate-800">{{ $user->nik ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">Jenis Kelamin</span>
                    <span class="font-bold text-slate-800">{{ $user->jenis_kelamin?->value ?? $user->jenis_kelamin ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">Tempat, Tgl Lahir</span>
                    <span class="font-bold text-slate-800">{{ $user->tempat_lahir ?? '-' }}, {{ $user->tanggal_lahir ? $user->tanggal_lahir->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">Agama</span>
                    <span class="font-bold text-slate-800">{{ $user->agama ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">No. HP / WhatsApp</span>
                    <span class="font-bold text-sky-600">{{ $user->no_hp ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">Email Akun Dinas</span>
                    <span class="font-bold text-slate-800 truncate max-w-[200px]">{{ $user->email }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">Email Pribadi</span>
                    <span class="font-bold text-slate-800 truncate max-w-[200px]">{{ $user->email_pribadi ?? '-' }}</span>
                </div>
                <div class="py-1.5 text-xs">
                    <span class="text-slate-400 font-medium block mb-1">Alamat Domisili</span>
                    <span class="font-medium text-slate-700 leading-relaxed">{{ $user->alamat ?? '-' }}</span>
                </div>
            </div>
        @endif

        <!-- TAB 2: GOLONGAN -->
        @if($activeTab === 'golongan')
            <div class="bg-white rounded-3xl p-5 shadow-xs border border-slate-100 space-y-3.5">
                <h3 class="text-xs font-extrabold text-sky-700 uppercase tracking-wider mb-2">Kepangkatan & Golongan</h3>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">Golongan Ruang</span>
                    <span class="px-2.5 py-0.5 rounded-full bg-sky-50 text-sky-700 font-bold border border-sky-100">{{ $user->pangkat_golongan?->value ?? $user->pangkat_golongan ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">Pangkat Pembina</span>
                    <span class="font-bold text-slate-800">
                        @php
                            $g = $user->pangkat_golongan?->value ?? $user->pangkat_golongan;
                            $pangkat = match($g) {
                                'IV/e' => 'Pembina Utama', 'IV/d' => 'Pembina Utama Madya', 'IV/c' => 'Pembina Utama Muda', 'IV/b' => 'Pembina Tingkat I', 'IV/a' => 'Pembina',
                                'III/d' => 'Penata Tingkat I', 'III/c' => 'Penata', 'III/b' => 'Penata Muda Tingkat I', 'III/a' => 'Penata Muda',
                                'II/d' => 'Pengatur Tingkat I', 'II/c' => 'Pengatur', 'II/b' => 'Pengatur Muda Tingkat I', 'II/a' => 'Pengatur Muda',
                                default => '-'
                            };
                        @endphp
                        {{ $pangkat }}
                    </span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">Eselon</span>
                    <span class="font-bold text-slate-800">{{ $user->eselon?->value ?? $user->eselon ?? 'Non Eselon' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">NPWP</span>
                    <span class="font-bold font-mono text-slate-800">{{ $user->npwp ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">BPJS Kesehatan</span>
                    <span class="font-bold font-mono text-slate-800">{{ $user->no_bpjs ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">Nomor Taspen</span>
                    <span class="font-bold font-mono text-slate-800">{{ $user->no_taspen ?? '-' }}</span>
                </div>
            </div>
        @endif

        <!-- TAB 3: JABATAN -->
        @if($activeTab === 'jabatan')
            <div class="bg-white rounded-3xl p-5 shadow-xs border border-slate-100 space-y-3.5">
                <h3 class="text-xs font-extrabold text-sky-700 uppercase tracking-wider mb-2">Posisi & Shift Kerja</h3>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">Jabatan</span>
                    <span class="font-bold text-slate-800 text-right">{{ $user->jabatan->nama ?? 'Belum Ditentukan' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">Bidang Dinas</span>
                    <span class="font-bold text-slate-800 text-right">{{ $user->bidang->nama ?? 'Pimpinan / Tanpa Bidang' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">Atasan Langsung</span>
                    <span class="font-bold text-slate-800 text-right">{{ $user->atasan->name ?? 'Pimpinan Tertinggi' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">Tipe Kerja</span>
                    <span class="px-2.5 py-0.5 rounded-full font-bold uppercase {{ strtolower($user->status_kerja ?? 'wfo') === 'wfh' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }}">
                        {{ $user->status_kerja ?? 'WFO' }}
                    </span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">Shift Kerja</span>
                    <span class="font-bold text-slate-800">{{ $user->jadwalKerja->nama ?? 'Jadwal Default Sistem' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">ID Mesin (PIN)</span>
                    <span class="font-bold font-mono text-slate-800">{{ $user->nomor_absensi ?? 'Belum Terhubung' }}</span>
                </div>
            </div>
        @endif

        <!-- TAB 4: PENDIDIKAN -->
        @if($activeTab === 'pendidikan')
            <div class="bg-white rounded-3xl p-5 shadow-xs border border-slate-100 space-y-3.5">
                <h3 class="text-xs font-extrabold text-sky-700 uppercase tracking-wider mb-2">Riwayat Pendidikan</h3>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">Tingkat Pendidikan</span>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-100">{{ $user->jenjang_pendidikan ?? '-' }}</span>
                </div>
                <div class="py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium block mb-1">Jurusan / Prodi</span>
                    <span class="font-bold text-slate-800">{{ $user->pendidikan ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">Gelar Depan</span>
                    <span class="font-bold text-slate-800">{{ $user->gelar_depan ?? '-' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-50 text-xs">
                    <span class="text-slate-400 font-medium">Gelar Belakang</span>
                    <span class="font-bold text-slate-800">{{ $user->gelar_belakang ?? '-' }}</span>
                </div>
            </div>
        @endif

        <!-- TAB 5: KELUARGA -->
        @if($activeTab === 'keluarga')
            <div class="bg-white rounded-3xl p-5 shadow-xs border border-slate-100 space-y-3.5">
                <h3 class="text-xs font-extrabold text-sky-700 uppercase tracking-wider mb-2">Data Kependudukan Keluarga</h3>
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
</div>
