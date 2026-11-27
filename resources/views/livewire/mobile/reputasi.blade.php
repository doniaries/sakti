<div class="p-5 pb-28">
    <!-- Header Back Navigation -->
    <div class="flex items-center space-x-3 mb-5">
        <a href="{{ route('mobile.dashboard') }}" wire:navigate 
           class="w-10 h-10 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-center text-slate-600 hover:text-slate-800 active:scale-95 transition-all">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Reputasi Pegawai</h1>
            <p class="text-xs text-slate-500 font-medium">Penilaian & Apresiasi Rekan Kerja</p>
        </div>
    </div>

    <!-- Rating Summary Card -->
    <div class="bg-gradient-to-r from-amber-500 via-amber-600 to-orange-500 text-white rounded-3xl p-5 mb-5 shadow-lg shadow-amber-500/20 relative overflow-hidden">
        <div class="absolute -right-4 -bottom-4 text-white/10 text-8xl pointer-events-none">
            <i class="fa-solid fa-star"></i>
        </div>
        <div class="relative z-10 flex items-center justify-between">
            <div>
                <span class="text-xs text-amber-100 font-bold uppercase tracking-wider block mb-1">Skor Reputasi Saya</span>
                <div class="flex items-baseline space-x-2">
                    <span class="text-3xl font-black font-mono leading-none">{{ $avgBintang }}</span>
                    <span class="text-xs text-amber-200 font-bold">/ 5.0</span>
                </div>
                <div class="flex text-amber-300 text-xs mt-2 space-x-1">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fa-solid fa-star {{ $i <= round($avgBintang) ? 'text-amber-300' : 'text-white/30' }}"></i>
                    @endfor
                </div>
            </div>
            <div class="text-right">
                <span class="text-2xl font-black font-mono leading-none block">{{ $reputasiDiterima->count() }}</span>
                <span class="text-xs text-amber-100 font-medium">Ulasan Diterima</span>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center space-x-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Tabs -->
    <div class="flex space-x-2 mb-5">
        <button wire:click="setTab('berikan')" 
                class="px-3.5 py-2 rounded-2xl text-xs font-bold transition-all shadow-xs {{ $activeTab === 'berikan' ? 'bg-sky-600 text-white shadow-sky-600/30' : 'bg-white text-slate-600 border border-slate-200' }}">
            <i class="fa-solid fa-pen-nib mr-1 text-xs"></i> Beri Nilai
        </button>
        <button wire:click="setTab('diterima')" 
                class="px-3.5 py-2 rounded-2xl text-xs font-bold transition-all shadow-xs {{ $activeTab === 'diterima' ? 'bg-sky-600 text-white shadow-sky-600/30' : 'bg-white text-slate-600 border border-slate-200' }}">
            <i class="fa-solid fa-inbox mr-1 text-xs"></i> Diterima ({{ $reputasiDiterima->count() }})
        </button>
        <button wire:click="setTab('diberikan')" 
                class="px-3.5 py-2 rounded-2xl text-xs font-bold transition-all shadow-xs {{ $activeTab === 'diberikan' ? 'bg-sky-600 text-white shadow-sky-600/30' : 'bg-white text-slate-600 border border-slate-200' }}">
            <i class="fa-solid fa-paper-plane mr-1 text-xs"></i> Diberikan ({{ $reputasiDiberikan->count() }})
        </button>
    </div>

    <!-- TAB 1: FORM BERIKAN NILAI -->
    @if($activeTab === 'berikan')
        <form wire:submit="simpanPenilaian" class="bg-white rounded-3xl p-5 border border-slate-100 shadow-xs space-y-4">
            <h3 class="text-xs font-extrabold text-sky-700 uppercase tracking-wider">Nilai Rekan Kerja</h3>

            <!-- Pilih Rekan Kerja -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Pilih Rekan Kerja <span class="text-rose-500">*</span></label>
                <div class="relative">
                    <select wire:model="user_id" 
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all appearance-none">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($rekanKerjas as $rk)
                            <option value="{{ $rk->id }}">{{ $rk->name }} ({{ $rk->jabatan->nama ?? 'Pegawai' }})</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
                @error('user_id') <span class="text-[13px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Pilih Bintang (1 - 5) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Nilai Bintang <span class="text-rose-500">*</span></label>
                <div class="flex items-center space-x-2.5 p-3 rounded-2xl bg-amber-50/60 border border-amber-200/60 justify-center">
                    @for($s = 1; $s <= 5; $s++)
                        <button type="button" wire:click="setBintang({{ $s }})" 
                                class="text-2xl transition-transform hover:scale-125 active:scale-95 {{ $s <= $nilai_reputasi ? 'text-amber-400' : 'text-slate-300' }}">
                            <i class="fa-solid fa-star"></i>
                        </button>
                    @endfor
                    <span class="ml-2 font-mono font-bold text-sm text-amber-800">{{ $nilai_reputasi }} / 5</span>
                </div>
            </div>

            <!-- Catatan / Ulasan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Apresiasi / Catatan Performa <span class="text-rose-500">*</span></label>
                <textarea wire:model="keterangan" rows="3"
                          placeholder="Berikan masukan positif, profesionalisme, atau kerja sama tim..."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all leading-relaxed"></textarea>
                @error('keterangan') <span class="text-[13px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Tombol Submit -->
            <button type="submit" 
                    class="w-full py-3 bg-gradient-to-r from-sky-600 to-sky-500 text-white font-extrabold text-xs rounded-2xl shadow-md shadow-sky-600/30 active:scale-95 transition-all flex items-center justify-center space-x-2">
                <i class="fa-solid fa-paper-plane mr-1"></i>
                <span>Kirim Penilaian</span>
            </button>
        </form>
    @endif

    <!-- TAB 2: DITERIMA -->
    @if($activeTab === 'diterima')
        <div class="space-y-3">
            @forelse($reputasiDiterima as $rep)
                <div class="bg-white rounded-3xl p-4 border border-slate-100 shadow-xs">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-sm font-bold">
                                <i class="fa-solid fa-user-check"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-800">{{ $rep->penilai->name ?? 'Rekan Kerja' }}</h4>
                                <span class="text-xs text-slate-400 font-medium">{{ $rep->penilai->jabatan->nama ?? 'Pegawai' }}</span>
                            </div>
                        </div>
                        <div class="flex text-amber-400 text-xs space-x-0.5">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fa-solid fa-star {{ $i <= $rep->nilai_reputasi ? 'text-amber-400' : 'text-slate-200' }}"></i>
                            @endfor
                        </div>
                    </div>
                    <div class="bg-slate-50 rounded-2xl p-3 text-xs text-slate-700 italic border border-slate-100/80 leading-relaxed">
                        "{{ $rep->keterangan }}"
                    </div>
                    <div class="text-xs text-slate-400 mt-2 text-right">
                        {{ \Carbon\Carbon::parse($rep->tanggal)->translatedFormat('d F Y') }}
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl p-8 border border-slate-100 text-center shadow-xs">
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl mx-auto mb-2">
                        <i class="fa-regular fa-star"></i>
                    </div>
                    <h4 class="text-xs font-bold text-slate-700">Belum ada penilaian diterima</h4>
                    <p class="text-[13px] text-slate-400 mt-1">Penilaian dan apresiasi dari rekan kerja Anda akan tampil di sini.</p>
                </div>
            @endforelse
        </div>
    @endif

    <!-- TAB 3: DIBERIKAN -->
    @if($activeTab === 'diberikan')
        <div class="space-y-3">
            @forelse($reputasiDiberikan as $rep)
                <div class="bg-white rounded-3xl p-4 border border-slate-100 shadow-xs">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm font-bold">
                                <i class="fa-solid fa-award"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-slate-800">{{ $rep->user->name ?? 'Rekan Kerja' }}</h4>
                                <span class="text-xs text-slate-400 font-medium">{{ $rep->user->jabatan->nama ?? 'Pegawai' }}</span>
                            </div>
                        </div>
                        <div class="flex text-amber-400 text-xs space-x-0.5">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fa-solid fa-star {{ $i <= $rep->nilai_reputasi ? 'text-amber-400' : 'text-slate-200' }}"></i>
                            @endfor
                        </div>
                    </div>
                    <div class="bg-slate-50 rounded-2xl p-3 text-xs text-slate-700 border border-slate-100/80 leading-relaxed">
                        "{{ $rep->keterangan }}"
                    </div>
                    <div class="text-xs text-slate-400 mt-2 text-right">
                        {{ \Carbon\Carbon::parse($rep->tanggal)->translatedFormat('d F Y') }}
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-3xl p-8 border border-slate-100 text-center shadow-xs">
                    <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-500 flex items-center justify-center text-xl mx-auto mb-2">
                        <i class="fa-solid fa-pen-nib"></i>
                    </div>
                    <h4 class="text-xs font-bold text-slate-700">Belum ada penilaian diberikan</h4>
                    <p class="text-[13px] text-slate-400 mt-1">Anda belum memberikan penilaian kepada rekan kerja.</p>
                </div>
            @endforelse
        </div>
    @endif
</div>
