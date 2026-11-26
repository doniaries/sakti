<div x-data="{
    showPassword: false,
    helpSheet: false,
    isDark: document.documentElement.classList.contains('dark'),
    toggleTheme() {
        if (window.toggleMobileTheme) {
            window.toggleMobileTheme();
            this.isDark = document.documentElement.classList.contains('dark');
        }
    }
}"
    class="min-h-screen flex flex-col justify-between bg-[#fafafa] dark:bg-[#09090b] text-zinc-900 dark:text-zinc-100 relative overflow-hidden transition-colors duration-300">

    <!-- Rhoe Dot Grid + Subtle Top Aurora -->
    <div class="pointer-events-none fixed inset-0 opacity-60 dark:opacity-100"
        style="background-image: radial-gradient(rgba(148, 163, 184, 0.22) 1px, transparent 1px); background-size: 22px 22px; mask-image: radial-gradient(ellipse 85% 55% at 50% 10%, #000 50%, transparent 100%);">
    </div>
    <div
        class="pointer-events-none absolute -top-20 left-1/2 -translate-x-1/2 w-80 h-44 bg-gradient-to-r from-sky-500/25 via-indigo-500/20 to-emerald-500/25 blur-3xl rounded-full">
    </div>

    <!-- Top Action Bar (Back + Theme Icon Morph + Help Bottom Sheet Trigger) -->
    <div class="relative z-20 pt-4 px-5 flex items-center justify-between">
        <a href="{{ route('portal') }}"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/80 dark:bg-zinc-900/80 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-200 text-xs font-semibold backdrop-blur-md active:scale-95 transition-transform shadow-sm">
            <i class="fa-solid fa-arrow-left text-[10px] text-sky-500"></i>
            <span>Portal</span>
        </a>

        <div class="flex items-center gap-2">
            <!-- Icon Morph: Theme Toggle -->
            <button type="button" @click="toggleTheme()" title="Ganti Tema Terang / Gelap"
                class="w-8 h-8 rounded-full bg-white/80 dark:bg-zinc-900/80 border border-zinc-200 dark:border-zinc-800 flex items-center justify-center text-xs active:scale-90 transition-all cursor-pointer shadow-sm">
                <i x-show="!isDark" class="fa-solid fa-moon text-zinc-700 transition-transform duration-300"></i>
                <i x-show="isDark" x-cloak class="fa-solid fa-sun text-amber-400 transition-transform duration-300"></i>
            </button>

            <!-- Bottom Sheet Trigger: Bantuan -->
            <button type="button" @click="helpSheet = true" title="Bantuan Login"
                class="w-8 h-8 rounded-full bg-white/80 dark:bg-zinc-900/80 border border-zinc-200 dark:border-zinc-800 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xs active:scale-90 transition-all cursor-pointer shadow-sm">
                <i class="fa-solid fa-circle-question"></i>
            </button>
        </div>
    </div>

    <!-- Center Content -->
    <div class="relative z-10 px-5 py-6 flex-1 flex flex-col justify-center w-full max-w-md mx-auto">
        @php
            $pengaturan = \App\Models\PengaturanAplikasi::getPengaturan();
            $logoUrl = asset('favicon.png');
            if (!empty($pengaturan->logo) && file_exists(public_path('storage/' . $pengaturan->logo))) {
                $logoUrl = asset('storage/' . $pengaturan->logo);
            } elseif (file_exists(public_path('images/logo-kabupaten.png'))) {
                $logoUrl = asset('images/logo-kabupaten.png');
            }
        @endphp

        <!-- Avatar Info + Badge (Rhoe UI Pattern) -->
        <div class="flex flex-col items-center text-center mb-6">
            <div class="relative mb-3">
                <div
                    class="w-16 h-16 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-lg flex items-center justify-center">
                    <img src="{{ $logoUrl }}" alt="Logo Instansi" class="w-full h-full object-cover">
                </div>
                <span
                    class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-sky-500 text-white border-2 border-[#fafafa] dark:border-[#09090b] flex items-center justify-center text-[10px] shadow">
                    <i class="fa-solid fa-fingerprint"></i>
                </span>
            </div>

            <!-- Feedback Badge -->
            <span
                class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-sky-500/10 border border-sky-500/25 text-sky-600 dark:text-sky-400 text-[10px] font-bold uppercase tracking-wider mb-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>SAKTI Mobile</span>
            </span>

            <h1 class="text-2xl font-black tracking-tight text-zinc-900 dark:text-white">
                Masuk ke Akun
            </h1>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1 max-w-xs">
                Gunakan email kedinasan atau akun Google yang terdaftar di Disparpora Sijunjung.
            </p>
        </div>

        <!-- Feedback Alert Banner -->
        @if ($hariLiburHariIni ?? null)
            <div class="mb-4 p-3.5 rounded-2xl border bg-sky-50 dark:bg-sky-500/10 border-sky-200 dark:border-sky-500/30 text-center">
                <span class="inline-block px-2.5 py-0.5 rounded-full bg-sky-500/15 text-sky-700 dark:text-sky-300 text-[10px] font-extrabold uppercase tracking-wider mb-1">
                    Hari Ini &bull; {{ \Carbon\Carbon::parse($hariLiburHariIni->tanggal)->translatedFormat('d F Y') }}
                </span>
                <div class="text-sm font-extrabold text-zinc-900 dark:text-white">{{ $hariLiburHariIni->nama }}</div>
            </div>
        @elseif ($hariLiburTerdekat ?? null)
            @php
                $sisaHari = (int) today()->diffInDays(\Carbon\Carbon::parse($hariLiburTerdekat->tanggal));
            @endphp
            <div class="mb-4 p-3.5 rounded-2xl border bg-sky-50/70 dark:bg-sky-500/10 border-sky-200/80 dark:border-sky-500/30 text-center">
                <span class="inline-block px-2.5 py-0.5 rounded-full bg-sky-500/15 text-sky-700 dark:text-sky-300 text-[10px] font-extrabold uppercase tracking-wider mb-1">
                    Menuju Hari Besar ({{ $sisaHari }} Hari Lagi)
                </span>
                <div class="text-sm font-extrabold text-zinc-900 dark:text-white">{{ $hariLiburTerdekat->nama }}</div>
                <div class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-0.5">{{ \Carbon\Carbon::parse($hariLiburTerdekat->tanggal)->translatedFormat('l, d F Y') }}</div>
            </div>
        @endif

        @if (session('notify'))
            @php
                $notify = session('notify');
                $isWarning = ($notify['type'] ?? 'error') === 'warning';
            @endphp
            <div
                class="mb-4 p-3.5 rounded-2xl border text-xs {{ $isWarning ? 'bg-amber-50 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/30 text-amber-800 dark:text-amber-300' : 'bg-rose-50 dark:bg-rose-500/10 border-rose-200 dark:border-rose-500/30 text-rose-800 dark:text-rose-300' }}">
                <div class="font-bold flex items-center gap-1.5 mb-0.5">
                    <i class="fa-solid {{ $isWarning ? 'fa-triangle-exclamation' : 'fa-circle-exclamation' }}"></i>
                    <span>{{ $notify['title'] ?? 'Peringatan Login' }}</span>
                </div>
                <p class="text-[11px] opacity-90">{{ $notify['message'] ?? '' }}</p>
            </div>
        @endif

        <!-- Login Card Surface -->
        <div
            class="rounded-3xl bg-white/90 dark:bg-zinc-900/80 border border-zinc-200/90 dark:border-zinc-800 p-5 shadow-xl shadow-zinc-900/5 dark:shadow-black/30 backdrop-blur-md">
            <form wire:submit="login" class="space-y-4">
                <!-- Email -->
                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1.5">Email
                        Kedinasan</label>
                    <div class="relative">
                        <i
                            class="fa-regular fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-400 text-xs"></i>
                        <input type="email" wire:model="email" autocomplete="email" placeholder="nama@gmail.com"
                            class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-zinc-50 dark:bg-zinc-950/70 border border-zinc-200 dark:border-zinc-800 text-xs font-medium text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-500 transition-all @error('email') border-rose-500 ring-2 ring-rose-500/20 @enderror">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-[11px] font-semibold text-rose-500 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- Password with Icon Morph -->
                <div>
                    <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1.5">Kata Sandi</label>
                    <div class="relative">
                        <i
                            class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-400 text-xs"></i>
                        <input :type="showPassword ? 'text' : 'password'" wire:model="password"
                            autocomplete="current-password" placeholder="Masukkan kata sandi..."
                            class="w-full pl-9 pr-10 py-2.5 rounded-xl bg-zinc-50 dark:bg-zinc-950/70 border border-zinc-200 dark:border-zinc-800 text-xs font-medium text-zinc-900 dark:text-zinc-100 placeholder-zinc-400 focus:outline-none focus:ring-2 focus:ring-sky-500/50 focus:border-sky-500 transition-all @error('password') border-rose-500 ring-2 ring-rose-500/20 @enderror">
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors cursor-pointer">
                            <i x-show="!showPassword" class="fa-regular fa-eye text-xs"></i>
                            <i x-show="showPassword" x-cloak class="fa-regular fa-eye-slash text-xs text-sky-500"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-[11px] font-semibold text-rose-500 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                            <span>{{ $message }}</span>
                        </p>
                    @enderror
                </div>

                <!-- Remember + Help Sheet Link -->
                <div class="flex items-center justify-between text-xs pt-0.5">
                    <label
                        class="inline-flex items-center gap-2 text-zinc-600 dark:text-zinc-400 cursor-pointer select-none">
                        <input type="checkbox" wire:model="remember"
                            class="rounded border-zinc-300 dark:border-zinc-700 text-sky-500 focus:ring-sky-500">
                        <span class="font-medium">Ingat saya</span>
                    </label>
                    <button type="button" @click="helpSheet = true"
                        class="text-sky-600 dark:text-sky-400 font-semibold hover:underline cursor-pointer">
                        Kendala login?
                    </button>
                </div>

                <!-- Primary Action Button with Loading Indicator -->
                <button type="submit" wire:loading.attr="disabled"
                    class="w-full py-3 px-4 rounded-xl bg-zinc-900 hover:bg-zinc-800 dark:bg-sky-500 dark:hover:bg-sky-400 text-white dark:text-zinc-950 font-bold text-xs shadow-lg shadow-zinc-900/15 dark:shadow-sky-500/20 flex items-center justify-center gap-2 active:scale-[0.98] transition-all cursor-pointer disabled:opacity-60">
                    <span wire:loading.remove wire:target="login" class="inline-flex items-center gap-2">
                        <span>Masuk Sekarang</span>
                        <i class="fa-solid fa-arrow-right text-[11px]"></i>
                    </span>
                    <span wire:loading wire:target="login" class="inline-flex items-center gap-2">
                        <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                        <span>Memverifikasi Akun...</span>
                    </span>
                </button>
            </form>

            <!-- Divider -->
            <div class="relative my-4">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-zinc-200 dark:border-zinc-800"></div>
                </div>
                <div class="relative flex justify-center text-[10px] uppercase tracking-wider">
                    <span class="bg-white dark:bg-zinc-900 px-2.5 text-zinc-400 font-semibold">atau masuk cepat</span>
                </div>
            </div>

            <!-- Google SSO Button -->
            <a href="{{ route('auth.google') }}"
                class="w-full py-2.5 px-4 rounded-xl bg-zinc-50 hover:bg-zinc-100 dark:bg-zinc-950 dark:hover:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-200 font-bold text-xs flex items-center justify-center gap-2.5 active:scale-[0.98] transition-all">
                <svg class="w-4 h-4" viewBox="0 0 24 24">
                    <path fill="#4285F4"
                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                    <path fill="#34A853"
                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                    <path fill="#FBBC05"
                        d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                    <path fill="#EA4335"
                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                </svg>
                <span>Masuk dengan Google</span>
            </a>
        </div>
    </div>

    <!-- Footer -->
    <div class="relative z-10 pb-5 px-5 text-center text-[10px] text-zinc-400">
        &copy; {{ date('Y') }} {{ $pengaturan->nama_dinas ?? 'Disparpora Kabupaten Sijunjung' }} &bull;
        <a href="{{ route('panduan') }}" class="text-sky-500 font-semibold hover:underline">Panduan</a>
    </div>

    <!-- Rhoe UI Bottom Sheet: Bantuan Login -->
    <div x-show="helpSheet" x-cloak class="fixed inset-0 z-50 flex items-end justify-center">
        <!-- Backdrop -->
        <div x-show="helpSheet" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" @click="helpSheet = false"
            class="fixed inset-0 bg-black/50 backdrop-blur-xs"></div>

        <!-- Sheet Panel -->
        <div x-show="helpSheet" x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
            class="relative w-full max-w-[440px] rounded-t-3xl bg-white dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-800 p-5 pb-7 shadow-2xl z-10">
            <div class="w-10 h-1 rounded-full bg-zinc-300 dark:bg-zinc-700 mx-auto mb-4"></div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-extrabold text-zinc-900 dark:text-white">Bantuan &amp; Kendala Login</h3>
                <button type="button" @click="helpSheet = false"
                    class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <ul class="space-y-2.5 text-xs text-zinc-600 dark:text-zinc-400 mb-5">
                <li class="flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
                    <span>Pastikan email yang digunakan sama dengan email yang terdaftar di data pegawai SAKTI.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
                    <span>Gunakan tombol <strong>Masuk dengan Google</strong> jika akun Gmail Anda sudah
                        terdaftar.</span>
                </li>
                <li class="flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5"></i>
                    <span>Jika lupa kata sandi, hubungi <strong>Admin Kepegawaian Dinas</strong> untuk reset
                        akun.</span>
                </li>
            </ul>
            <div class="flex items-center gap-2">
                <a href="{{ route('panduan') }}"
                    class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold text-center transition-colors">
                    Buka Panduan Lengkap
                </a>
                <button type="button" @click="helpSheet = false"
                    class="px-4 py-2.5 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 text-xs font-bold cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
