<div class="h-full flex flex-col justify-between bg-gradient-to-b from-[#0F172A] via-[#0369A1] to-[#0284C7] relative overflow-hidden">
    <!-- Top Bar: Kembali ke Beranda -->
    <div class="pt-3 px-4 flex items-center justify-between z-20 shrink-0">
        <a href="{{ route('portal') }}" 
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-black/25 hover:bg-black/40 text-white border border-white/20 backdrop-blur-md text-[11px] font-bold transition-transform duration-150 ease-out active:scale-[0.96] shadow-xs">
            <i class="fa-solid fa-arrow-left text-[10px]"></i>
            <span>Kembali ke Beranda</span>
        </a>
        <div class="px-2 py-0.5 rounded-full bg-white/10 text-sky-200 border border-white/15 text-[10px] font-bold">
            Portal ASN
        </div>
    </div>

    <!-- Header: Logo Instansi, Ikon Sidik Jari & Judul -->
    <div class="py-2.5 px-4 flex flex-col items-center justify-center text-center shrink-0">
        @php
            $pengaturan = \App\Models\PengaturanAplikasi::getPengaturan();
            $logoUrl = null;
            if (!empty($pengaturan->logo) && file_exists(public_path('storage/' . $pengaturan->logo))) {
                $logoUrl = asset('storage/' . $pengaturan->logo);
            } elseif (file_exists(public_path('images/logo-kabupaten.png'))) {
                $logoUrl = asset('images/logo-kabupaten.png');
            } elseif (file_exists(public_path('images/logo-instansi.png'))) {
                $logoUrl = asset('images/logo-instansi.png');
            }
        @endphp

        <!-- Logo Instansi / Kabupaten di Atas -->
        @if ($logoUrl)
            <img src="{{ $logoUrl }}" alt="Logo Instansi" class="h-10 w-auto object-contain mb-2 drop-shadow-md">
        @endif

        <!-- Ikon Sidik Jari (Dipertahankan Sesuai Permintaan) -->
        <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur-md flex items-center justify-center mb-1.5 border border-white/20 shadow-inner">
            <i class="fa-solid fa-fingerprint text-white text-2xl"></i>
        </div>

        <h1 class="text-white text-lg font-black tracking-tight leading-tight uppercase">{{ $pengaturan->nama_aplikasi ?? 'Absensi LHK' }}</h1>
        <p class="text-sky-100/90 text-[11px] mt-0.5 font-medium">{{ $pengaturan->sub_nama ?? 'Sistem Administrasi Kinerja Terintegrasi' }}</p>
    </div>

    <!-- Form Card: Fits 100% On Screen -->
    <div class="bg-white rounded-t-[28px] p-5 pt-4 pb-4 shadow-2xl flex flex-col justify-between shrink-0">
        <div>
            <div class="mb-2.5">
                <h2 class="text-base font-black text-slate-800 tracking-tight">Masuk</h2>
                <p class="text-slate-500 text-xs">Silakan login dengan akun Anda</p>
            </div>

            <form wire:submit="login" class="space-y-2.5">
                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-regular fa-envelope text-xs"></i>
                        </div>
                        <input type="email" wire:model="email" 
                            class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:bg-white transition @error('email') ring-2 ring-red-500 @enderror"
                            placeholder="nama@sijunjung.go.id">
                    </div>
                    @error('email') <span class="text-rose-500 text-[10px] mt-0.5 block font-semibold">{{ $message }}</span> @enderror
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Password</label>
                    <div class="relative" x-data="{ show: false }">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </div>
                        <input :type="show ? 'text' : 'password'" wire:model="password" 
                            class="w-full pl-9 pr-9 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:bg-white transition @error('password') ring-2 ring-red-500 @enderror"
                            placeholder="Masukkan password">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer text-slate-400 hover:text-slate-600" @click="show = !show">
                            <i class="fa-solid text-xs" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </div>
                    </div>
                    @error('password') <span class="text-rose-500 text-[10px] mt-0.5 block font-semibold">{{ $message }}</span> @enderror
                </div>
                
                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-0.5">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="remember" id="remember" class="w-3.5 h-3.5 text-sky-600 bg-slate-100 border-slate-300 rounded focus:ring-sky-500">
                        <span class="ml-2 text-xs text-slate-600 font-medium">Ingat Saya</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                    class="w-full bg-[#1B6CA8] hover:bg-[#155685] text-white font-extrabold text-xs py-2.5 rounded-xl shadow-md shadow-sky-900/15 enabled:active:scale-[0.96] transition-transform duration-150 ease-out disabled:scale-100 flex items-center justify-center cursor-pointer"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove>Masuk</span>
                    <span wire:loading>
                        <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                    </span>
                </button>
            </form>

            <!-- Divider Atau -->
            <div class="relative flex items-center justify-center my-2.5">
                <div class="w-full border-t border-slate-200"></div>
                <span class="absolute bg-white px-2.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    atau
                </span>
            </div>

            <!-- Tombol Google Login (Socialite) -->
            <a href="{{ route('auth.google', ['from' => 'mobile']) }}"
               class="w-full py-2.5 px-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-xs enabled:active:scale-[0.96] transition-transform duration-150 ease-out flex items-center justify-center space-x-2.5 cursor-pointer">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" fill="#EA4335"/>
                </svg>
                <span>Masuk dengan Google</span>
            </a>
        </div>

        <!-- Footer Link Kembali -->
        <div class="mt-2.5 pt-2 border-t border-slate-100 text-center">
            <a href="{{ route('portal') }}" class="inline-flex items-center gap-1.5 text-[11px] font-bold text-slate-500 hover:text-sky-700 transition-colors">
                <i class="fa-solid fa-house text-slate-400 text-[10px]"></i>
                <span>Salah masuk pintu? Kembali ke Beranda Portal</span>
            </a>
        </div>
    </div>
</div>