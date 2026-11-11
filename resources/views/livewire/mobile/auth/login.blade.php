<div
    class="h-full flex flex-col justify-between bg-gradient-to-br from-[#0A192F] via-[#0F355C] to-[#1E6B9E] relative overflow-hidden">
    <!-- Ambient Background Lighting & Biometric Watermark -->
    <div class="absolute -top-16 -right-16 w-56 h-56 bg-sky-400/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/4 -left-16 w-48 h-48 bg-teal-400/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -top-6 -right-6 text-white/[0.04] text-[180px] pointer-events-none select-none">
        <i class="fa-solid fa-fingerprint"></i>
    </div>

    <!-- Top Navigation Bar -->
    <div class="pt-4 px-5 flex items-center justify-between z-20 shrink-0">
        <a href="{{ route('portal') }}"
            class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-black/25 hover:bg-black/40 text-white border border-white/20 backdrop-blur-md text-xs font-bold transition-transform duration-150 ease-out active:scale-[0.96] shadow-sm">
            <i class="fa-solid fa-arrow-left text-[11px] text-sky-300"></i>
            <span>Portal Beranda</span>
        </a>
        <div
            class="px-2.5 py-1 rounded-full bg-white/10 text-sky-200 border border-white/15 text-[10px] font-extrabold uppercase tracking-wider backdrop-blur-md">
            Portal Pegawai
        </div>
    </div>

    <!-- Brand Identity Hero (Harmonious & Professional) -->
    <div class="pt-2 pb-5 px-6 flex flex-col items-center justify-center text-center z-10 shrink-0">
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

        <!-- Logo Instansi dengan Biometric Badge Sesuai Permintaan -->
        <div class="relative mb-2.5">
            <div
                class="w-16 h-16 rounded-2xl bg-white/15 backdrop-blur-md p-2.5 border border-white/25 shadow-xl shadow-sky-950/30 flex items-center justify-center">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo Pemerintah Kabupaten Sijunjung"
                        class="w-full h-full object-contain filter drop-shadow-sm">
                @else
                    <i class="fa-solid fa-building-columns text-white text-2xl"></i>
                @endif
            </div>
            <!-- Ikon Sidik Jari (Dipertahankan Menempel Sempurna pada Logo) -->
            <div class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-sky-500 border-2 border-[#0F355C] text-white flex items-center justify-center text-[10px] shadow-sm"
                title="Presensi Biometrik ASN">
                <i class="fa-solid fa-fingerprint"></i>
            </div>
        </div>

        <!-- Typography Brand Lockup -->
        <h1 class="text-white text-2xl font-black tracking-tight leading-none uppercase drop-shadow-sm">
            {{ $pengaturan->nama_aplikasi ?? 'SAKTI' }}
        </h1>
        
        <!-- Sub Judul Pembeda Presensi Kehadiran -->
        <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-sky-400/20 border border-sky-300/30 text-sky-200 text-[10px] font-extrabold uppercase tracking-wider mt-2 mb-1 backdrop-blur-md">
            <i class="fa-solid fa-fingerprint text-[9px] text-sky-300"></i>
            <span>Presensi Kehadiran & LHK</span>
        </div>

        <p class="text-sky-100/90 text-xs font-semibold tracking-wide">
            {{ $pengaturan->sub_nama ?? 'Sistem Administrasi Kinerja Terintegrasi' }}
        </p>
        <span class="inline-block mt-0.5 text-[10px] font-bold uppercase tracking-widest text-sky-300/80">
            Kabupaten Sijunjung
        </span>
    </div>

    <!-- Form Card: White Elevated Surface with Subtle Structure -->
    <div class="bg-white rounded-t-[32px] p-6 pt-5 pb-6 shadow-2xl flex-1 flex flex-col justify-between z-10">
        <div>
            <!-- Card Header -->
            <div class="mb-4">
                <h2 class="text-lg font-black text-slate-800 tracking-tight">Masuk ke Akun</h2>
                <p class="text-slate-500 text-xs mt-0.5 font-medium">Gunakan email kedinasan atau akun Google terdaftar
                </p>
            </div>

            <!-- Form -->
            <form wire:submit="login" class="space-y-3.5">
                <!-- Email Field -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Kedinasan / Akun</label>
                    <div class="relative group">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-sky-600 transition-colors">
                            <i class="fa-regular fa-envelope text-sm"></i>
                        </div>
                        <input type="email" wire:model="email"
                            class="w-full pl-10 pr-3.5 py-2.5 bg-slate-50/80 hover:bg-slate-50 focus:bg-white border border-slate-200/90 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition duration-150 @error('email') border-rose-400 ring-2 ring-rose-500/20 @enderror"
                            placeholder="nama@sijunjung.go.id">
                    </div>
                    @error('email')
                        <span class="text-rose-500 text-[10px] mt-1 block font-semibold">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password Field -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi</label>
                    <div class="relative group" x-data="{ show: false }">
                        <div
                            class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-sky-600 transition-colors">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </div>
                        <input :type="show ? 'text' : 'password'" wire:model="password"
                            class="w-full pl-10 pr-10 py-2.5 bg-slate-50/80 hover:bg-slate-50 focus:bg-white border border-slate-200/90 rounded-xl text-xs font-semibold text-slate-800 placeholder-slate-400 focus:outline-none focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 transition duration-150 @error('password') border-rose-400 ring-2 ring-rose-500/20 @enderror"
                            placeholder="••••••••••••">
                        <button type="button"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center cursor-pointer text-slate-400 hover:text-slate-600 active:scale-95 transition-transform"
                            @click="show = !show" tabindex="-1">
                            <i class="fa-solid text-xs" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="text-rose-500 text-[10px] mt-1 block font-semibold">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-0.5">
                    <label class="flex items-center cursor-pointer group">
                        <input type="checkbox" wire:model="remember" id="remember"
                            class="w-4 h-4 text-sky-600 bg-slate-100 border-slate-300 rounded focus:ring-sky-500 cursor-pointer">
                        <span
                            class="ml-2 text-xs text-slate-600 font-medium group-hover:text-slate-800 transition-colors">Ingat
                            Saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Primary Submit Button -->
                <button type="submit"
                    class="w-full bg-gradient-to-r from-sky-600 via-sky-600 to-sky-700 hover:from-sky-500 hover:to-sky-600 text-white font-extrabold text-xs py-3 rounded-xl shadow-lg shadow-sky-600/25 enabled:active:scale-[0.96] transition-transform duration-150 ease-out disabled:scale-100 flex items-center justify-center space-x-2 cursor-pointer"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove class="flex items-center space-x-1.5">
                        <span>Masuk ke Akun</span>
                        <i class="fa-solid fa-arrow-right text-[11px] text-sky-200"></i>
                    </span>
                    <span wire:loading class="flex items-center space-x-1.5">
                        <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                        <span>Memproses Autentikasi...</span>
                    </span>
                </button>
            </form>

            <!-- Divider Atau -->
            <div class="relative flex items-center justify-center my-3.5">
                <div class="w-full border-t border-slate-200"></div>
                <span class="absolute bg-white px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    atau masuk cepat
                </span>
            </div>

            <!-- Tombol Google Login (Socialite) -->
            <a href="{{ route('auth.google', ['from' => 'mobile']) }}"
                class="w-full py-2.5 px-4 rounded-xl border border-slate-200/90 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 font-bold text-xs shadow-xs enabled:active:scale-[0.96] transition-transform duration-150 ease-out flex items-center justify-center space-x-2.5 cursor-pointer">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
                        fill="#4285F4" />
                    <path
                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
                        fill="#34A853" />
                    <path
                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"
                        fill="#FBBC05" />
                    <path
                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"
                        fill="#EA4335" />
                </svg>
                <span>Masuk dengan Akun Google</span>
            </a>
        </div>

        <!-- Footer Link & Identity Grounding -->
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
            {{-- <a href="{{ route('portal') }}" class="font-bold text-sky-600 hover:text-sky-700 flex items-center space-x-1">
                <i class="fa-solid fa-house text-[10px]"></i>
                <span>Beranda Portal</span>
            </a> --}}
            <span class="font-medium text-slate-400 text-[10px]">
                Sistem Sakti &bull; v1.0
            </span>
        </div>
    </div>
</div>
