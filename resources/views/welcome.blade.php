<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ $pengaturan->nama_aplikasi ?? 'Absensi & LHK' }} - {{ $pengaturan->nama_dinas ?? 'Dinas Lingkungan Hidup' }}</title>

    <!-- PWA Setup -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#070B1A">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        * { -webkit-tap-highlight-color: transparent; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* ===== Floating Stars ===== */
        .stars-layer { position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 0; overflow: hidden; }

        .bg-star {
            position: absolute;
            background: #fff;
            border-radius: 50%;
            animation: bgPulse 4s ease-in-out infinite alternate;
        }
        @keyframes bgPulse {
            0% { opacity: 0.08; }
            100% { opacity: 0.55; }
        }

        .float-star {
            position: absolute;
            background: #fff;
            border-radius: 50%;
            opacity: 0;
            animation: floatUp linear infinite, shimmer 2s ease-in-out infinite alternate;
        }
        @keyframes floatUp {
            0%   { transform: translateY(100vh) translateX(0); opacity: 0; }
            8%   { opacity: 1; }
            88%  { opacity: 0.8; }
            100% { transform: translateY(-30px) translateX(var(--dx)); opacity: 0; }
        }
        @keyframes shimmer {
            0%   { box-shadow: 0 0 2px rgba(255,255,255,0.3); }
            100% { box-shadow: 0 0 6px rgba(255,255,255,0.9), 0 0 18px rgba(120,180,255,0.35); }
        }

        /* Shooting star */
        .shoot {
            position: absolute;
            width: 3px; height: 3px;
            background: #fff;
            border-radius: 50%;
            opacity: 0;
            pointer-events: none;
        }
        .shoot::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 55px; height: 1px;
            background: linear-gradient(90deg, rgba(255,255,255,.6), transparent);
            transform-origin: right;
        }
        @keyframes shooting {
            0%   { opacity: 0; transform: translate(0,0) rotate(-45deg); }
            6%   { opacity: 1; }
            65%  { opacity: 1; }
            100% { opacity: 0; transform: translate(-350px,350px) rotate(-45deg); }
        }

        /* Logo hover */
        .logo-hover { transition: transform .3s ease; }
        .logo-hover:hover { transform: scale(1.08) rotate(-2deg); }
    </style>
</head>
<body class="bg-[#070B1A] text-slate-100 min-h-screen flex flex-col justify-between relative overflow-x-hidden">

    <!-- ===== Background Layers ===== -->
    <div class="fixed inset-0 bg-gradient-to-b from-[#070B1A] via-[#0C1530] to-[#0A1225] z-0"></div>

    <!-- Static stars -->
    <div class="stars-layer" id="bgStars"></div>
    <!-- Floating stars -->
    <div class="stars-layer" id="floatStars"></div>
    <!-- Shooting stars -->
    <div class="stars-layer" id="shootStars"></div>

    <!-- Ambient glows -->
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[550px] h-[300px] bg-sky-500/10 rounded-full blur-[100px] pointer-events-none z-0"></div>
    <div class="fixed bottom-10 -left-16 w-[350px] h-[350px] bg-emerald-500/8 rounded-full blur-[90px] pointer-events-none z-0"></div>
    <div class="fixed top-1/3 -right-16 w-[350px] h-[350px] bg-violet-500/8 rounded-full blur-[90px] pointer-events-none z-0"></div>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="w-full max-w-4xl mx-auto px-5 py-8 md:py-12 relative z-10 flex-1 flex flex-col justify-center">

        <!-- Header: Logo Kab (kiri) — Badge Instansi — Logo Geopark (kanan) -->
        <header class="text-center mb-8 md:mb-10">
            <div class="inline-flex items-center justify-center gap-4 md:gap-5 mb-5">
                <!-- Logo Kabupaten (kiri) -->
                <img src="{{ asset('images/kabupaten-sijunjung.png') }}"
                     alt="Logo Kabupaten Sijunjung"
                     class="w-14 h-14 md:w-[68px] md:h-[68px] object-contain drop-shadow-[0_4px_12px_rgba(0,0,0,.5)] logo-hover">

                <!-- Badge Tengah -->
                <div class="text-center">
                    <span class="inline-block px-3 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-400/25 text-emerald-300 text-[9px] md:text-[10px] font-extrabold uppercase tracking-[2px] mb-0.5">
                        Pemerintah Daerah
                    </span>
                    <h2 class="text-xs md:text-sm font-bold text-white/90 tracking-tight leading-snug">
                        {{ $pengaturan->nama_dinas ?? 'Dinas Lingkungan Hidup' }}
                    </h2>
                </div>

                <!-- Logo Geopark (kanan) -->
                <img src="{{ asset('images/geopark-silokek.png') }}"
                     alt="Logo Geopark Silokek"
                     class="w-14 h-14 md:w-[68px] md:h-[68px] object-contain drop-shadow-[0_4px_12px_rgba(0,0,0,.5)] logo-hover">
            </div>

            <!-- Judul Aplikasi dari Pengaturan -->
            <h1 class="text-3xl md:text-5xl lg:text-[3.5rem] font-black uppercase tracking-tight leading-none mb-3
                        text-transparent bg-clip-text bg-gradient-to-r from-white via-sky-200 to-sky-400">
                {{ $pengaturan->nama_aplikasi ?? 'ABSENSI LHK' }}
            </h1>
            <p class="text-xs md:text-sm text-slate-400 max-w-xl mx-auto font-medium leading-relaxed">
                {{ $pengaturan->sub_nama ?? $pengaturan->nama_dinas ?? 'Dinas Lingkungan Hidup' }}
            </p>

            <!-- Digital Clock -->
            <div class="mt-4 inline-flex items-center gap-2.5 bg-white/[0.04] border border-white/[0.07] backdrop-blur-md px-4 py-1.5 rounded-full text-xs text-slate-300"
                 x-data="{
                     time: '--:--',
                     date: '{{ now()->translatedFormat('l, d F Y') }}',
                     update() {
                         const d = new Date();
                         this.time = String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
                     }
                 }"
                 x-init="update(); setInterval(() => update(), 1000);">
                <i class="fa-regular fa-calendar text-sky-400/80 text-[11px]"></i>
                <span x-text="date">{{ now()->translatedFormat('l, d F Y') }}</span>
                <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span x-text="time + ' WIB'" class="font-mono font-bold text-amber-300 tracking-wider">--:-- WIB</span>
            </div>
        </header>

        <!-- User Logged-in Banner -->
        @auth
            <div class="mb-7 p-4 rounded-2xl bg-white/[0.05] backdrop-blur-md border border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 max-w-3xl mx-auto w-full">
                <div class="flex items-center gap-3 text-center sm:text-left">
                    <img src="{{ auth()->user()->avatar_image_url }}"
                         alt="{{ auth()->user()->name }}"
                         class="w-11 h-11 rounded-xl object-cover border-2 border-sky-400/60 shadow-md">
                    <div>
                        <div class="text-[10px] text-sky-300/80 font-bold uppercase tracking-wider">Sedang login:</div>
                        <div class="text-sm font-extrabold text-white">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-slate-400 font-mono">
                            <span class="text-amber-300 font-bold uppercase">{{ auth()->user()->roles->pluck('name')->implode(', ') ?: 'Pegawai' }}</span>
                        </div>
                    </div>
                </div>
                <form action="{{ route('portal.logout') }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto px-3.5 py-2 bg-rose-500/15 hover:bg-rose-500 text-rose-300 hover:text-white border border-rose-400/30 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        @endauth

        <!-- Divider Label -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center gap-3">
                <span class="w-8 h-px bg-gradient-to-r from-transparent to-slate-600"></span>
                <span class="text-[10px] uppercase font-extrabold tracking-[2.5px] text-sky-400/80">
                    Silakan Pilih Pintu Akses Anda
                </span>
                <span class="w-8 h-px bg-gradient-to-l from-transparent to-slate-600"></span>
            </div>
        </div>

        <!-- ===== 2 ACCESS CARDS ===== -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 max-w-3xl mx-auto w-full">

            <!-- CARD: PEGAWAI -->
            <div class="group relative rounded-[22px] bg-gradient-to-b from-sky-950/50 to-slate-900/60 border border-sky-500/20 hover:border-sky-400/50 transition-all duration-300 p-6 flex flex-col justify-between overflow-hidden hover:-translate-y-1 hover:shadow-xl hover:shadow-sky-500/10">
                <!-- Glow -->
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-sky-500/15 rounded-full blur-2xl group-hover:bg-sky-400/25 transition-all pointer-events-none"></div>

                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-600 to-teal-400 text-white flex items-center justify-center shadow-lg shadow-sky-600/25 text-xl group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-mobile-screen-button"></i>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-sky-500/15 border border-sky-400/30 text-sky-300 text-[10px] font-extrabold tracking-wider uppercase">
                            Pegawai / ASN
                        </span>
                    </div>

                    <h3 class="text-lg md:text-xl font-black text-white tracking-tight mb-1.5 group-hover:text-sky-300 transition-colors">
                        Presensi & LHK Mobile
                    </h3>
                    <p class="text-[11px] md:text-xs text-slate-400 leading-relaxed mb-5 font-medium">
                        Aplikasi presensi harian berbasis GPS/WFO/WFH, pengisian LHK, dan riwayat kehadiran pegawai.
                    </p>

                    <ul class="space-y-2 mb-6 text-[11px] text-slate-300">
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-sky-400 text-xs"></i>
                            <span>Presensi Masuk & Pulang (WFO, WFH, Dinas Luar)</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-sky-400 text-xs"></i>
                            <span>Pengisian & Bukti Foto Laporan Harian Kinerja</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-sky-400 text-xs"></i>
                            <span>Cetak Rekap Riwayat PDF & Bluetooth Printer</span>
                        </li>
                    </ul>
                </div>

                <div>
                    @auth
                        <a href="{{ route('mobile.dashboard') }}"
                           class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-sky-500 to-teal-500 hover:from-sky-400 hover:to-teal-400 text-white font-extrabold text-sm shadow-lg shadow-sky-500/25 transition-all flex items-center justify-center gap-2 active:scale-95">
                            <span>Buka Dashboard Presensi</span>
                            <i class="fa-solid fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
                        </a>
                    @else
                        <a href="{{ route('mobile.login') }}"
                           class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-sky-500 to-teal-500 hover:from-sky-400 hover:to-teal-400 text-white font-extrabold text-sm shadow-lg shadow-sky-500/25 transition-all flex items-center justify-center gap-2 active:scale-95">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            <span>Masuk Presensi Pegawai</span>
                            <i class="fa-solid fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
                        </a>
                    @endauth
                </div>
            </div>

            <!-- CARD: ADMINISTRATOR -->
            <div class="group relative rounded-[22px] bg-gradient-to-b from-amber-950/30 to-slate-900/60 border border-amber-500/20 hover:border-amber-400/50 transition-all duration-300 p-6 flex flex-col justify-between overflow-hidden hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-500/10">
                <!-- Glow -->
                <div class="absolute -right-6 -top-6 w-24 h-24 bg-amber-500/12 rounded-full blur-2xl group-hover:bg-amber-400/20 transition-all pointer-events-none"></div>

                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-500 text-slate-950 flex items-center justify-center shadow-lg shadow-amber-500/25 text-xl group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-amber-500/15 border border-amber-400/30 text-amber-300 text-[10px] font-extrabold tracking-wider uppercase">
                            Admin & Verifikator
                        </span>
                    </div>

                    <h3 class="text-lg md:text-xl font-black text-white tracking-tight mb-1.5 group-hover:text-amber-300 transition-colors">
                        Panel Administrator
                    </h3>
                    <p class="text-[11px] md:text-xs text-slate-400 leading-relaxed mb-5 font-medium">
                        Portal manajemen operasional dinas untuk pimpinan, verifikator LHK, dan admin kepegawaian.
                    </p>

                    <ul class="space-y-2 mb-6 text-[11px] text-slate-300">
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-amber-400 text-xs"></i>
                            <span>Monitoring Kehadiran Real-Time & Rekap Absensi</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-amber-400 text-xs"></i>
                            <span>Verifikasi & Evaluasi Laporan Harian Kinerja</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-amber-400 text-xs"></i>
                            <span>Integrasi Mesin ZKTeco, Shift & Master Pegawai</span>
                        </li>
                    </ul>
                </div>

                <div>
                    @auth
                        @if(auth()->user()->hasAnyRole(['super_admin', 'admin']))
                            <a href="{{ url('/admin') }}"
                               class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-orange-400 hover:from-amber-400 hover:to-orange-300 text-slate-950 font-black text-sm shadow-lg shadow-amber-500/25 transition-all flex items-center justify-center gap-2 active:scale-95">
                                <i class="fa-solid fa-gauge-high"></i>
                                <span>Buka Panel Admin</span>
                                <i class="fa-solid fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
                            </a>
                        @else
                            <div class="p-3 rounded-xl bg-slate-800/60 border border-slate-700/50 text-center">
                                <span class="text-[11px] text-slate-400 font-medium block">
                                    <i class="fa-solid fa-lock text-amber-400/70 mr-1"></i> Akun Anda berstatus <b>Pegawai</b>
                                </span>
                                <span class="text-[10px] text-slate-500">Akses panel admin khusus Administrator</span>
                            </div>
                        @endif
                    @else
                        <a href="{{ url('/admin/login') }}"
                           class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-orange-400 hover:from-amber-400 hover:to-orange-300 text-slate-950 font-black text-sm shadow-lg shadow-amber-500/25 transition-all flex items-center justify-center gap-2 active:scale-95">
                            <i class="fa-solid fa-key"></i>
                            <span>Masuk Panel Admin</span>
                            <i class="fa-solid fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
                        </a>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Info Bar Bawah -->
        <div class="mt-8 max-w-3xl mx-auto w-full bg-white/[0.03] backdrop-blur-md rounded-xl p-3.5 border border-white/[0.06] flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 gap-2.5">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-shield-heart text-sky-400/70"></i>
                <span>Keamanan Terproteksi: Geofencing, Deteksi Fake GPS & Enkripsi Log</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="hover:text-slate-300 transition cursor-pointer"><i class="fa-solid fa-circle-question mr-0.5"></i>Panduan</span>
                <span class="text-white/15">|</span>
                <span class="hover:text-slate-300 transition cursor-pointer"><i class="fa-solid fa-headset mr-0.5"></i>Bantuan IT</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="w-full border-t border-white/[0.06] py-4 text-center text-[11px] text-slate-500 relative z-10 bg-slate-950/40 backdrop-blur-sm">
        <p class="font-medium">
            &copy; {{ date('Y') }} {{ $pengaturan->nama_dinas ?? 'Dinas Lingkungan Hidup' }} &bull; Kabupaten Sijunjung
        </p>
        <p class="text-[10px] text-slate-600 mt-0.5">
            {{ $pengaturan->versi_aplikasi ?? 'v1.0' }} &bull; Filament Admin & Livewire Mobile PWA
        </p>
    </footer>

    <!-- ===== Stars JavaScript ===== -->
    <script>
        // Static background stars
        (function() {
            const c = document.getElementById('bgStars');
            for (let i = 0; i < 100; i++) {
                const s = document.createElement('div');
                s.className = 'bg-star';
                const sz = Math.random() * 2 + 0.5;
                s.style.cssText = `width:${sz}px;height:${sz}px;left:${Math.random()*100}%;top:${Math.random()*100}%;animation-duration:${Math.random()*4+2}s;animation-delay:${Math.random()*5}s;`;
                c.appendChild(s);
            }
        })();

        // Floating stars
        (function() {
            const c = document.getElementById('floatStars');
            function spawn() {
                const s = document.createElement('div');
                s.className = 'float-star';
                const sz = Math.random() * 3 + 1;
                const dur = Math.random() * 12 + 10;
                const dx = (Math.random() - 0.5) * 80;
                s.style.cssText = `width:${sz}px;height:${sz}px;left:${Math.random()*100}%;--dx:${dx}px;animation-duration:${dur}s,2s;`;
                c.appendChild(s);
                setTimeout(() => s.remove(), dur * 1000);
            }
            for (let i = 0; i < 20; i++) setTimeout(spawn, Math.random() * 4000);
            setInterval(spawn, 900);
        })();

        // Shooting stars
        (function() {
            const c = document.getElementById('shootStars');
            function shoot() {
                const s = document.createElement('div');
                s.className = 'shoot';
                s.style.cssText = `top:${Math.random()*45}%;left:${Math.random()*50+45}%;animation:shooting ${Math.random()*1.2+0.8}s linear forwards;`;
                c.appendChild(s);
                setTimeout(() => s.remove(), 2500);
            }
            function loop() {
                shoot();
                setTimeout(loop, Math.random() * 5000 + 4000);
            }
            setTimeout(loop, 3000);
        })();
    </script>
</body>
</html>
