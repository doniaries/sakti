<div x-data="{
    showPassword: false,
    loginSheet: {{ $errors->any() || session('notify') ? 'true' : 'false' }},
    helpSheet: false,
    jam: '--:--',
    detik: '--',
    tanggal: '',
    init() {
        const updateClock = () => {
            const now = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            this.jam = `${pad(now.getHours())}:${pad(now.getMinutes())}`;
            this.detik = pad(now.getSeconds());
            this.tanggal = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        };
        updateClock();
        setInterval(updateClock, 1000);
    }
}" class="min-h-screen flex flex-col bg-slate-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100">

    <!-- Top Blue Hero -->
    <div class="relative bg-gradient-to-br from-[#0A2540] via-[#0E4A7B] to-[#0284C7] text-white px-5 pt-4 pb-8 rounded-b-[32px] shadow-xl overflow-hidden">
        <div class="flex items-center justify-between mb-4">
            <a href="{{ route('portal') }}"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/15 border border-white/20 text-white text-xs font-semibold backdrop-blur-md">
                <i class="fa-solid fa-arrow-left text-[10px]"></i>
                <span>Portal</span>
            </a>
            <button type="button" @click="helpSheet = true" title="Bantuan Login"
                class="w-8 h-8 rounded-full bg-white/15 border border-white/20 text-white flex items-center justify-center text-xs cursor-pointer">
                <i class="fa-solid fa-circle-question"></i>
            </button>
        </div>

        <div class="flex flex-col items-center text-center">
            <!-- Dual Emblem Pill -->
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 border border-white/20 backdrop-blur-md mb-3">
                <img src="{{ asset('images/logo-kabupaten.png') }}" alt="Kab. Sijunjung" class="w-5 h-5 object-contain">
                <span class="w-px h-3.5 bg-white/30"></span>
                <img src="{{ asset('images/geopark-silokek.png') }}" alt="Geopark Silokek" class="w-5 h-5 object-contain rounded-full">
            </div>

            <!-- SAKTI Circle Logo -->
            <div class="w-16 h-16 rounded-full bg-[#0A192F] border-2 border-white/30 shadow-xl overflow-hidden flex items-center justify-center mb-2.5">
                <img src="{{ asset('icons/icon-192x192.png') }}?v=2" alt="SAKTI" class="w-full h-full object-cover">
            </div>

            <span class="inline-block px-3 py-0.5 rounded-full bg-white/15 border border-white/25 text-sky-100 text-[10px] font-extrabold uppercase tracking-wider mb-1">
                SAKTI MOBILE
            </span>
            <p class="text-[11px] text-sky-100/90 font-semibold mb-2">Sistem Administrasi Kinerja Terintegrasi</p>

            <h1 class="text-xl font-black tracking-tight">Hai, Selamat Datang!</h1>
            <p class="text-xs text-sky-100/90 mt-0.5">{{ $pengaturan->nama_dinas ?? $pengaturan->nama_instansi ?? 'Disparpora Kabupaten Sijunjung' }}</p>

            <!-- Live Clock Box -->
            <div class="mt-4 px-5 py-2.5 rounded-2xl bg-white/10 border border-white/20 backdrop-blur-md">
                <div class="flex items-baseline justify-center gap-1 font-black tracking-tight">
                    <span class="text-3xl" x-text="jam"></span>
                    <span class="text-sm text-sky-200" x-text="':' + detik"></span>
                    <span class="text-[11px] font-bold text-sky-300 ml-1">WIB</span>
                </div>
                <div class="text-[11px] text-sky-100/90 font-medium mt-0.5" x-text="tanggal"></div>
            </div>
        </div>
    </div>

    <!-- Bottom Content: Hari Besar Card + Login Button -->
    <div class="flex-1 px-5 py-5 flex flex-col justify-between max-w-md mx-auto w-full">
        <div class="rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-4 shadow-sm text-center">
            @php
                $holidayItem = ($hariLiburHariIni ?? null) ?: ($hariLiburTerdekat ?? null);
                $holidayImg = (! empty($holidayItem?->gambar) && file_exists(public_path('storage/' . $holidayItem->gambar)))
                    ? asset('storage/' . $holidayItem->gambar)
                    : asset('images/geopark-silokek.png');
            @endphp
            <div class="rounded-2xl bg-sky-50/80 dark:bg-sky-500/10 border border-sky-100 dark:border-sky-500/20 py-5 px-4 mb-4">
                <img src="{{ $holidayImg }}" alt="Ilustrasi Peringatan" class="mx-auto w-20 h-20 object-contain rounded-2xl mb-2.5 drop-shadow-sm">
                <div class="text-xs font-semibold text-slate-600 dark:text-zinc-300" x-text="tanggal"></div>
            </div>

            @if ($hariLiburHariIni ?? null)
                <span class="inline-block px-3 py-1 rounded-full bg-sky-50 dark:bg-sky-500/15 border border-sky-200 dark:border-sky-500/30 text-sky-700 dark:text-sky-300 text-[10px] font-extrabold uppercase tracking-wider">
                    {{ \Carbon\Carbon::parse($hariLiburHariIni->tanggal)->translatedFormat('l, d F Y') }}
                </span>
                <h2 class="text-base font-black text-slate-900 dark:text-white mt-2">{{ $hariLiburHariIni->nama }}</h2>
            @elseif ($hariLiburTerdekat ?? null)
                <span class="inline-block px-3 py-1 rounded-full bg-sky-50 dark:bg-sky-500/15 border border-sky-200 dark:border-sky-500/30 text-sky-700 dark:text-sky-300 text-[10px] font-extrabold uppercase tracking-wider">
                    {{ \Carbon\Carbon::parse($hariLiburTerdekat->tanggal)->translatedFormat('l, d F Y') }}
                </span>
                <h2 class="text-base font-black text-slate-900 dark:text-white mt-2">{{ $hariLiburTerdekat->nama }}</h2>
            @else
                <p class="text-xs font-semibold text-slate-500 dark:text-zinc-400">Tidak ada hari peringatan / libur terdekat.</p>
            @endif
        </div>

        <button type="button" @click="loginSheet = true"
            class="mt-5 w-full py-3.5 px-5 rounded-2xl bg-[#0284C7] hover:bg-[#0369A1] text-white font-bold text-sm shadow-lg shadow-sky-600/25 active:scale-[0.98] transition cursor-pointer">
            Login
        </button>
    </div>

    <!-- Bottom Sheet Login Modal -->
    <div x-show="loginSheet" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-slate-950/60 backdrop-blur-xs">
        <div @click.outside="loginSheet = false"
            x-show="loginSheet"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-full sm:translate-y-4 sm:scale-95 opacity-0"
            x-transition:enter-end="translate-y-0 sm:scale-100 opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0 sm:scale-100 opacity-100"
            x-transition:leave-end="translate-y-full sm:translate-y-4 sm:scale-95 opacity-0"
            class="w-full max-w-md bg-white dark:bg-zinc-900 rounded-t-[28px] sm:rounded-3xl p-6 shadow-2xl">
            <div class="w-10 h-1 bg-slate-200 dark:bg-zinc-700 rounded-full mx-auto mb-4 sm:hidden"></div>

            <div class="flex items-center gap-3 mb-4">
                <img src="{{ asset('images/logo-kabupaten.png') }}" alt="Logo" class="w-9 h-9 rounded-xl object-contain bg-slate-100 dark:bg-zinc-800 p-1">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">Masuk ke Akun Pegawai</h3>
                    <p class="text-xs text-slate-500 dark:text-zinc-400">Gunakan email kedinasan atau akun Google</p>
                </div>
            </div>

            @if (session('notify'))
                @php
                    $notify = session('notify');
                    $isWarning = ($notify['type'] ?? 'error') === 'warning';
                @endphp
                <div class="mb-3 p-3 rounded-xl border text-xs {{ $isWarning ? 'bg-amber-50 border-amber-200 text-amber-800' : 'bg-rose-50 border-rose-200 text-rose-800' }}">
                    <div class="font-bold">{{ $notify['title'] ?? 'Peringatan Login' }}</div>
                    <p class="text-[11px]">{{ $notify['message'] ?? '' }}</p>
                </div>
            @endif

            <form wire:submit="login" class="space-y-3.5">
                <div>
                    <div class="relative">
                        <i class="fa-regular fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input type="email" wire:model="email" placeholder="Email / Username" required
                            class="w-full h-11 pl-10 pr-3.5 rounded-xl bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-sm text-slate-900 dark:text-white focus:bg-white dark:focus:bg-zinc-900 focus:border-sky-500 outline-none">
                    </div>
                    @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                        <input :type="showPassword ? 'text' : 'password'" wire:model="password" placeholder="Kata Sandi" required
                            class="w-full h-11 pl-10 pr-10 rounded-xl bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-sm text-slate-900 dark:text-white focus:bg-white dark:focus:bg-zinc-900 focus:border-sky-500 outline-none">
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 cursor-pointer">
                            <i class="fa-regular" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    @error('password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-zinc-300 cursor-pointer">
                    <input type="checkbox" wire:model="remember" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    <span>Ingat email &amp; kata sandi</span>
                </label>

                <button type="submit" wire:loading.attr="disabled"
                    class="w-full py-3 rounded-xl bg-[#0284C7] hover:bg-[#0369A1] text-white font-bold text-sm shadow-md cursor-pointer">
                    <span wire:loading.remove wire:target="login">Masuk Sekarang</span>
                    <span wire:loading wire:target="login">Memproses...</span>
                </button>
            </form>

            @if (Route::has('auth.google'))
                <div class="my-3 flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase">
                    <span class="flex-1 h-px bg-slate-200 dark:bg-zinc-800"></span>
                    <span>ATAU</span>
                    <span class="flex-1 h-px bg-slate-200 dark:bg-zinc-800"></span>
                </div>
                <a href="{{ route('auth.google') }}"
                    class="w-full py-2.5 px-4 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-sky-600 dark:text-sky-400 text-xs font-bold flex items-center justify-center gap-2">
                    <i class="fa-brands fa-google text-rose-500"></i>
                    <span>Masuk dengan Akun Google</span>
                </a>
            @endif
        </div>
    </div>
</div>
