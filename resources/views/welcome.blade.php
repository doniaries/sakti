<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ $pengaturan->nama_aplikasi ?? 'Absensi & LHK' }} - Portal Sistem Kepegawaian</title>
    
    <!-- PWA Setup -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0F172A">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        * {
            -webkit-tap-highlight-color: transparent;
        }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-[#0B1120] via-[#0F172A] to-[#1E293B] text-slate-100 min-h-screen flex flex-col justify-between selection:bg-sky-500 selection:text-white relative overflow-x-hidden">

    <!-- Ambient Decorative Glows -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[350px] bg-sky-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-10 -left-20 w-[400px] h-[400px] bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/3 -right-20 w-[400px] h-[400px] bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <!-- MAIN CONTAINER -->
    <div class="w-full max-w-5xl mx-auto px-5 py-8 md:py-12 relative z-10 flex-1 flex flex-col justify-center">

        <!-- Top Header & Branding -->
        <header class="text-center mb-8 md:mb-12">
            <div class="inline-flex items-center justify-center space-x-3 mb-4">
                @if(!empty($pengaturan->logo) && file_exists(public_path('storage/' . $pengaturan->logo)))
                    <img src="{{ asset('storage/' . $pengaturan->logo) }}" 
                         alt="Logo Instansi" 
                         class="w-14 h-14 md:w-16 md:h-16 rounded-2xl object-contain bg-white/95 p-1.5 shadow-xl shadow-sky-950/50 border border-white/20">
                @else
                    <div class="w-14 h-14 md:w-16 md:h-16 rounded-2xl bg-gradient-to-tr from-sky-500 via-sky-600 to-teal-400 text-white flex items-center justify-center shadow-xl shadow-sky-500/25 border border-white/25">
                        <i class="fa-solid fa-leaf text-2xl md:text-3xl"></i>
                    </div>
                @endif
                <div class="text-left">
                    <span class="inline-block px-2.5 py-0.5 rounded-full bg-sky-500/20 text-sky-300 border border-sky-400/30 text-[10px] md:text-xs font-extrabold uppercase tracking-widest">
                        Pemerintah Daerah
                    </span>
                    <h2 class="text-sm md:text-base font-extrabold text-white tracking-tight leading-snug">
                        {{ $pengaturan->nama_instansi ?? 'Dinas Lingkungan Hidup' }}
                    </h2>
                </div>
            </div>

            <h1 class="text-2xl md:text-4xl lg:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-white via-sky-100 to-sky-300 tracking-tight leading-tight uppercase mb-3">
                {{ $pengaturan->nama_aplikasi ?? 'ABSENSI & LHK' }}
            </h1>
            <p class="text-xs md:text-sm text-slate-300 max-w-2xl mx-auto font-medium leading-relaxed">
                {{ $pengaturan->sub_nama ?? 'Sistem Terintegrasi Presensi Digital & Laporan Harian Kinerja Pegawai' }}
            </p>

            <!-- Digital Clock Real-Time -->
            <div class="mt-4 inline-flex items-center space-x-2 bg-white/5 border border-white/10 backdrop-blur-md px-4 py-1.5 rounded-full text-xs text-sky-200"
                 x-data="{ 
                     time: '--:-- WIB',
                     date: '{{ now()->translatedFormat('l, d F Y') }}',
                     update() {
                         const d = new Date();
                         const h = String(d.getHours()).padStart(2, '0');
                         const m = String(d.getMinutes()).padStart(2, '0');
                         this.time = `${h}:${m} WIB`;
                     }
                 }"
                 x-init="update(); setInterval(() => update(), 1000);">
                <i class="fa-regular fa-calendar text-sky-400"></i>
                <span x-text="date">{{ now()->translatedFormat('l, d F Y') }}</span>
                <span class="text-white/30">•</span>
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span x-text="time" class="font-mono font-bold text-amber-300">--:-- WIB</span>
            </div>
        </header>

        <!-- User Logged-in Status Banner (Jika Sedang Aktif Login) -->
        @auth
            <div class="mb-8 p-4 rounded-3xl bg-white/10 backdrop-blur-md border border-white/20 flex flex-col sm:flex-row items-center justify-between gap-4 max-w-3xl mx-auto w-full shadow-lg shadow-black/20">
                <div class="flex items-center space-x-3.5 text-center sm:text-left">
                    <img src="{{ auth()->user()->avatar_image_url }}" 
                         alt="{{ auth()->user()->name }}" 
                         class="w-12 h-12 rounded-2xl object-cover border-2 border-sky-400 aspect-square shadow-md">
                    <div>
                        <div class="text-[11px] text-sky-300 font-bold uppercase tracking-wider">Anda sedang login sebagai:</div>
                        <div class="text-sm md:text-base font-extrabold text-white">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-slate-300 font-mono">
                            Peran: <span class="text-amber-300 font-bold uppercase">{{ auth()->user()->roles->pluck('name')->implode(', ') ?: 'Pegawai' }}</span>
                        </div>
                    </div>
                </div>
                <form action="{{ route('portal.logout') }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-rose-500/20 hover:bg-rose-500 text-rose-200 hover:text-white border border-rose-400/40 rounded-xl text-xs font-bold transition flex items-center justify-center space-x-2">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Ganti Akun / Keluar</span>
                    </button>
                </form>
            </div>
        @endauth

        <!-- Section Title: Pilih Jalur Masuk -->
        <div class="text-center mb-6">
            <span class="text-xs uppercase font-extrabold tracking-widest text-sky-400 bg-sky-950/60 border border-sky-500/30 px-3.5 py-1 rounded-full">
                Silakan Pilih Pintu Akses Anda
            </span>
        </div>

        <!-- 2 DUAL CARDS: PEGAWAI VS ADMIN -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-8 max-w-4xl mx-auto w-full">

            <!-- CARD 1: PEGAWAI (PRESENSI & LHK MOBILE) -->
            <div class="group relative rounded-3xl bg-gradient-to-b from-sky-900/40 via-slate-900/80 to-slate-900/90 border border-sky-500/30 hover:border-sky-400 transition-all duration-300 p-6 md:p-8 flex flex-col justify-between shadow-xl shadow-sky-950/40 hover:shadow-sky-500/20 hover:-translate-y-1">
                <!-- Card Glow -->
                <div class="absolute -right-4 -top-4 w-28 h-28 bg-sky-500/20 rounded-full blur-2xl group-hover:bg-sky-400/30 transition-all pointer-events-none"></div>

                <div>
                    <!-- Badge & Icon -->
                    <div class="flex items-center justify-between mb-5">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-sky-600 to-teal-400 text-white flex items-center justify-center shadow-lg shadow-sky-600/30 text-2xl group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-mobile-screen-button"></i>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-sky-500/20 border border-sky-400/40 text-sky-300 text-[11px] font-extrabold tracking-wider uppercase">
                            Pegawai / ASN
                        </span>
                    </div>

                    <h3 class="text-xl md:text-2xl font-black text-white tracking-tight mb-2 group-hover:text-sky-300 transition-colors">
                        Presensi & LHK Mobile
                    </h3>
                    <p class="text-xs md:text-sm text-slate-300 leading-relaxed mb-6 font-medium">
                        Aplikasi khusus seluruh pegawai ASN & Non-ASN untuk absensi masuk/pulang harian berbasis GPS/WFO/WFH, pengisian LHK, dan riwayat presensi.
                    </p>

                    <!-- Feature Bullet Points -->
                    <ul class="space-y-2.5 mb-8 text-xs text-slate-300">
                        <li class="flex items-center space-x-2.5">
                            <i class="fa-solid fa-circle-check text-sky-400 text-sm"></i>
                            <span>Presensi Masuk & Pulang (WFO, WFH, Dinas Luar)</span>
                        </li>
                        <li class="flex items-center space-x-2.5">
                            <i class="fa-solid fa-circle-check text-sky-400 text-sm"></i>
                            <span>Pengisian & Bukti Foto Laporan Harian Kinerja (LHK)</span>
                        </li>
                        <li class="flex items-center space-x-2.5">
                            <i class="fa-solid fa-circle-check text-sky-400 text-sm"></i>
                            <span>Cetak Rekap Riwayat PDF & Bluetooth Printer</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Button -->
                <div>
                    @auth
                        <a href="{{ route('mobile.dashboard') }}" 
                           class="w-full py-3.5 px-5 rounded-2xl bg-gradient-to-r from-sky-500 to-teal-500 hover:from-sky-400 hover:to-teal-400 text-white font-extrabold text-sm shadow-lg shadow-sky-500/30 transition-all flex items-center justify-center space-x-2 group-hover:shadow-sky-400/40 active:scale-95">
                            <span>Buka Dashboard Presensi</span>
                            <i class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"></i>
                        </a>
                    @else
                        <a href="{{ route('mobile.login') }}" 
                           class="w-full py-3.5 px-5 rounded-2xl bg-gradient-to-r from-sky-500 to-teal-500 hover:from-sky-400 hover:to-teal-400 text-white font-extrabold text-sm shadow-lg shadow-sky-500/30 transition-all flex items-center justify-center space-x-2 group-hover:shadow-sky-400/40 active:scale-95">
                            <i class="fa-solid fa-right-to-bracket mr-1"></i>
                            <span>Masuk Presensi Pegawai</span>
                            <i class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"></i>
                        </a>
                    @endauth
                </div>
            </div>

            <!-- CARD 2: ADMINISTRATOR (PANEL FILAMENT ADMIN) -->
            <div class="group relative rounded-3xl bg-gradient-to-b from-slate-800/50 via-slate-900/80 to-slate-900/90 border border-amber-500/30 hover:border-amber-400 transition-all duration-300 p-6 md:p-8 flex flex-col justify-between shadow-xl shadow-black/40 hover:shadow-amber-500/20 hover:-translate-y-1">
                <!-- Card Glow -->
                <div class="absolute -right-4 -top-4 w-28 h-28 bg-amber-500/15 rounded-full blur-2xl group-hover:bg-amber-400/25 transition-all pointer-events-none"></div>

                <div>
                    <!-- Badge & Icon -->
                    <div class="flex items-center justify-between mb-5">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-500 text-slate-950 flex items-center justify-center shadow-lg shadow-amber-500/30 text-2xl group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-amber-500/20 border border-amber-400/40 text-amber-300 text-[11px] font-extrabold tracking-wider uppercase">
                            Admin & Verifikator
                        </span>
                    </div>

                    <h3 class="text-xl md:text-2xl font-black text-white tracking-tight mb-2 group-hover:text-amber-300 transition-colors">
                        Panel Administrator
                    </h3>
                    <p class="text-xs md:text-sm text-slate-300 leading-relaxed mb-6 font-medium">
                        Portal manajemen operasional dinas untuk pimpinan, verifikator LHK, dan admin kepegawaian dalam memantau kehadiran serta konfigurasi sistem.
                    </p>

                    <!-- Feature Bullet Points -->
                    <ul class="space-y-2.5 mb-8 text-xs text-slate-300">
                        <li class="flex items-center space-x-2.5">
                            <i class="fa-solid fa-circle-check text-amber-400 text-sm"></i>
                            <span>Monitoring Kehadiran Real-Time & Rekap Absensi Dinas</span>
                        </li>
                        <li class="flex items-center space-x-2.5">
                            <i class="fa-solid fa-circle-check text-amber-400 text-sm"></i>
                            <span>Verifikasi, Validasi & Evaluasi Laporan Harian Kinerja (LHK)</span>
                        </li>
                        <li class="flex items-center space-x-2.5">
                            <i class="fa-solid fa-circle-check text-amber-400 text-sm"></i>
                            <span>Integrasi Mesin ZKTeco, Shift Jam Kerja & Master Pegawai</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Button -->
                <div>
                    @auth
                        @if(auth()->user()->hasAnyRole(['super_admin', 'admin']))
                            <a href="{{ url('/admin') }}" 
                               class="w-full py-3.5 px-5 rounded-2xl bg-gradient-to-r from-amber-500 via-amber-400 to-orange-400 hover:from-amber-400 hover:to-orange-300 text-slate-950 font-black text-sm shadow-lg shadow-amber-500/30 transition-all flex items-center justify-center space-x-2 group-hover:shadow-amber-400/40 active:scale-95">
                                <i class="fa-solid fa-gauge-high mr-1"></i>
                                <span>Buka Panel Admin</span>
                                <i class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"></i>
                            </a>
                        @else
                            <div class="p-3 rounded-2xl bg-slate-800/80 border border-slate-700 text-center">
                                <span class="text-xs text-slate-400 font-medium block">
                                    <i class="fa-solid fa-lock text-amber-400 mr-1"></i> Akun Anda berstatus <b>Pegawai</b>
                                </span>
                                <span class="text-[11px] text-slate-500">Akses panel admin dibatasi khusus Administrator</span>
                            </div>
                        @endif
                    @else
                        <a href="{{ url('/admin/login') }}" 
                           class="w-full py-3.5 px-5 rounded-2xl bg-gradient-to-r from-amber-500 via-amber-400 to-orange-400 hover:from-amber-400 hover:to-orange-300 text-slate-950 font-black text-sm shadow-lg shadow-amber-500/30 transition-all flex items-center justify-center space-x-2 group-hover:shadow-amber-400/40 active:scale-95">
                            <i class="fa-solid fa-key mr-1"></i>
                            <span>Masuk Panel Admin</span>
                            <i class="fa-solid fa-arrow-right transition-transform group-hover:translate-x-1"></i>
                        </a>
                    @endauth
                </div>
            </div>

        </div>

        <!-- Info Card Bawah: Bantuan & Ketentuan -->
        <div class="mt-10 max-w-4xl mx-auto w-full bg-slate-900/60 backdrop-blur-md rounded-2xl p-4 border border-white/10 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-3">
            <div class="flex items-center space-x-2.5">
                <i class="fa-solid fa-shield-heart text-sky-400 text-base"></i>
                <span>Keamanan Presensi Terproteksi: Geofencing Radius, Deteksi Fake GPS, & Enkripsi Log</span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="hover:text-white transition cursor-pointer"><i class="fa-solid fa-circle-question mr-1"></i>Panduan Presensi</span>
                <span class="text-white/20">|</span>
                <span class="hover:text-white transition cursor-pointer"><i class="fa-solid fa-headset mr-1"></i>Bantuan IT</span>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer class="w-full border-t border-white/10 py-5 text-center text-xs text-slate-400 relative z-10 bg-slate-950/60 backdrop-blur-sm">
        <p class="font-medium">
            &copy; {{ date('Y') }} {{ $pengaturan->nama_instansi ?? 'Dinas Lingkungan Hidup' }} • Sistem Presensi & Kinerja Terpadu
        </p>
        <p class="text-[11px] text-slate-400 mt-1">
            Versi Platform 4.0 • Didukung oleh Filament Admin & Livewire Mobile PWA
        </p>
    </footer>

</body>
</html>
