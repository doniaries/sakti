<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ $pengaturan->nama_aplikasi ?? 'Sakti' }} - {{ $pengaturan->nama_dinas ?? 'Dinas Pariwisata Pemuda dan Olahraga' }}</title>

    <!-- PWA Setup -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#070B1A">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">
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

        /* ===== Starry Sky (Bintang Diam / Tidak Bergerak) ===== */
        .sky-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .static-star {
            position: absolute;
            background: #ffffff;
            border-radius: 50%;
            pointer-events: none;
            animation: starTwinkle var(--twinkle-dur, 3s) ease-in-out infinite alternate;
        }

        @keyframes starTwinkle {
            0% {
                opacity: var(--min-op, 0.15);
                transform: scale(0.8);
            }

            100% {
                opacity: var(--max-op, 0.85);
                transform: scale(1.25);
                box-shadow: 0 0 5px rgba(255, 255, 255, 0.85);
            }
        }

        /* Bintang Berkilau Silang (Static Cross Glint) */
        .glint-star {
            position: absolute;
            width: 3px;
            height: 3px;
            background: #ffffff;
            border-radius: 50%;
            box-shadow: 0 0 6px rgba(186, 230, 253, 0.9);
            animation: glintPulse 4s ease-in-out infinite alternate;
        }

        .glint-star::before,
        .glint-star::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(186, 230, 253, 0.75);
            border-radius: 50%;
        }

        .glint-star::before {
            width: 12px;
            height: 1.5px;
        }

        .glint-star::after {
            width: 1.5px;
            height: 12px;
        }

        @keyframes glintPulse {
            0% {
                transform: scale(0.6) rotate(0deg);
                opacity: 0.2;
            }

            50% {
                transform: scale(1.1) rotate(45deg);
                opacity: 0.95;
            }

            100% {
                transform: scale(0.6) rotate(90deg);
                opacity: 0.2;
            }
        }

        /* ===== Komet Melayang / Bergerak ===== */
        .comet {
            position: absolute;
            width: 4px;
            height: 4px;
            background: #ffffff;
            border-radius: 50%;
            box-shadow: 0 0 10px 2px rgba(147, 197, 253, 0.95), 0 0 22px 6px rgba(56, 189, 248, 0.7);
            pointer-events: none;
            opacity: 0;
            z-index: 1;
        }

        .comet::before {
            content: '';
            position: absolute;
            top: 50%;
            right: 0;
            transform: translateY(-50%);
            width: var(--tail-len, 150px);
            height: 2px;
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.95) 0%, rgba(147, 197, 253, 0.75) 25%, rgba(56, 189, 248, 0.35) 60%, transparent 100%);
            box-shadow: 0 0 10px rgba(147, 197, 253, 0.6);
            border-radius: 50%;
        }

        @keyframes cometFlight {
            0% {
                transform: translate(0, 0) rotate(var(--angle, -35deg));
                opacity: 0;
            }

            8% {
                opacity: 1;
            }

            70% {
                opacity: 0.95;
            }

            100% {
                transform: translate(var(--travel-x, -700px), var(--travel-y, 500px)) rotate(var(--angle, -35deg));
                opacity: 0;
            }
        }

        .logo-hover {
            transition: transform .3s ease;
        }

        .logo-hover:hover {
            transform: scale(1.08) rotate(-2deg);
        }
    </style>
</head>

<body class="bg-[#070B1A] text-slate-100 min-h-screen flex flex-col justify-between relative overflow-x-hidden">

    <!-- Background Gradient Langit Malam -->
    <div class="fixed inset-0 bg-gradient-to-b from-[#070B1A] via-[#0C1530] to-[#0A1225] z-0"></div>

    <!-- Star Layers (Bintang Statis) -->
    <div class="sky-canvas" id="skyStars"></div>

    <!-- Comet Layer (Hanya Komet yang Bergerak) -->
    <div class="sky-canvas" id="cometLayer"></div>

    <!-- Ambient Glows -->
    <div
        class="fixed top-0 left-1/2 -translate-x-1/2 w-[550px] h-[300px] bg-sky-500/10 rounded-full blur-[100px] pointer-events-none z-0">
    </div>
    <div
        class="fixed bottom-10 -left-16 w-[350px] h-[350px] bg-emerald-500/[0.06] rounded-full blur-[90px] pointer-events-none z-0">
    </div>
    <div
        class="fixed top-1/3 -right-16 w-[350px] h-[350px] bg-violet-500/[0.06] rounded-full blur-[90px] pointer-events-none z-0">
    </div>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="w-full max-w-4xl mx-auto px-5 py-8 md:py-12 relative z-10 flex-1 flex flex-col justify-center">

        <!-- Header: Logo Kabupaten (kiri) — Badge — Logo Geopark (kanan) -->
        <header class="text-center mb-8 md:mb-10">
            <div class="inline-flex items-center justify-center gap-4 md:gap-5 mb-5">
                <!-- Logo Kabupaten Sijunjung (Kiri) -->
                <img src="{{ asset('images/kabupaten-sijunjung.png') }}" alt="Logo Kabupaten Sijunjung"
                    class="w-14 h-14 md:w-[68px] md:h-[68px] object-contain drop-shadow-[0_4px_12px_rgba(0,0,0,.5)] logo-hover">

                <!-- Badge Instansi -->
                <div class="text-center">
                    <span
                        class="inline-block px-3 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-400/25 text-emerald-300 text-[9px] md:text-[10px] font-extrabold uppercase tracking-[2px] mb-0.5">
                        Pemerintah Kabupaten
                    </span>
                    <h2 class="text-xs md:text-sm font-bold text-white/90 tracking-tight leading-snug">
                        {{ $pengaturan->nama_dinas ?? 'Dinas Pariwisata Pemuda dan Olahraga' }}
                    </h2>
                </div>

                <!-- Logo Geopark Silokek (Kanan) -->
                <img src="{{ asset('images/geopark-silokek.png') }}" alt="Logo Geopark Silokek"
                    class="w-14 h-14 md:w-[68px] md:h-[68px] object-contain drop-shadow-[0_4px_12px_rgba(0,0,0,.5)] logo-hover">
            </div>

            <!-- Judul Aplikasi dari tabel pengaturan -->
            <h1
                class="text-3xl md:text-5xl lg:text-[3.5rem] font-black uppercase tracking-tight leading-none mb-3
                        text-transparent bg-clip-text bg-gradient-to-r from-white via-sky-200 to-sky-400">
                {{ $pengaturan->nama_aplikasi ?? 'SAKTI' }}
            </h1>
            <p class="text-xs md:text-sm text-slate-400 max-w-xl mx-auto font-medium leading-relaxed">
                {{ $pengaturan->sub_nama ?? 'Sistem Administrasi, Kinerja, dan Tata Informasi' }}
            </p>

            <!-- Digital Clock -->
            <div class="mt-4 inline-flex items-center gap-2.5 bg-white/[0.04] border border-white/[0.07] backdrop-blur-md px-4 py-1.5 rounded-full text-xs text-slate-300"
                x-data="{
                    time: '--:--',
                    date: '{{ now()->translatedFormat('l, d F Y') }}',
                    update() {
                        const d = new Date();
                        this.time = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
                    }
                }" x-init="update();
                setInterval(() => update(), 1000);">
                <i class="fa-regular fa-calendar text-sky-400/80 text-[11px]"></i>
                <span x-text="date">{{ now()->translatedFormat('l, d F Y') }}</span>
                <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span x-text="time + ' WIB'" class="font-mono font-bold text-amber-300 tracking-wider">--:-- WIB</span>
            </div>
        </header>

        <!-- User Logged-in Banner -->
        @auth
            <div
                class="mb-7 p-4 rounded-2xl bg-white/[0.05] backdrop-blur-md border border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 max-w-3xl mx-auto w-full">
                <div class="flex items-center gap-3 text-center sm:text-left">
                    <img src="{{ auth()->user()->avatar_image_url }}" alt="{{ auth()->user()->name }}"
                        class="w-11 h-11 rounded-xl object-cover border-2 border-sky-400/60 shadow-md">
                    <div>
                        <div class="text-[10px] text-sky-300/80 font-bold uppercase tracking-wider">Sedang login:</div>
                        <div class="text-sm font-extrabold text-white">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-slate-400 font-mono">
                            <span
                                class="text-amber-300 font-bold uppercase">{{ auth()->user()->roles->pluck('name')->implode(', ') ?: 'Pegawai' }}</span>
                        </div>
                    </div>
                </div>
                <form action="{{ route('portal.logout') }}" method="POST" class="w-full sm:w-auto">
                    @csrf
                    <button type="submit"
                        class="w-full sm:w-auto px-3.5 py-2 bg-rose-500/15 hover:bg-rose-500 text-rose-300 hover:text-white border border-rose-400/30 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        @endauth

        <!-- Divider -->
        <div class="text-center mb-6">
            <div class="inline-flex items-center gap-3">
                <span class="w-8 h-px bg-gradient-to-r from-transparent to-slate-600"></span>
                <span class="text-[10px] uppercase font-extrabold tracking-[2.5px] text-sky-400/80">
                    Silakan Pilih Pintu Akses Anda
                </span>
                <span class="w-8 h-px bg-gradient-to-l from-transparent to-slate-600"></span>
            </div>
        </div>

        <!-- ===== 2 ACCESS CARDS (Admin di KIRI, Pegawai di KANAN) ===== -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 max-w-3xl mx-auto w-full">

            <!-- ============================================= -->
            <!-- CARD 1 (KIRI): ADMINISTRATOR & KEUANGAN SPJ   -->
            <!-- ============================================= -->
            <div
                class="group relative rounded-[22px] bg-gradient-to-b from-amber-950/30 to-slate-900/60 border border-amber-500/20 hover:border-amber-400/50 transition-all duration-300 p-6 flex flex-col justify-between overflow-hidden hover:-translate-y-1 hover:shadow-xl hover:shadow-amber-500/10">
                <div
                    class="absolute -right-6 -top-6 w-24 h-24 bg-amber-500/12 rounded-full blur-2xl group-hover:bg-amber-400/20 transition-all pointer-events-none">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-500 text-slate-950 flex items-center justify-center shadow-lg shadow-amber-500/25 text-xl group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <span
                            class="px-2.5 py-0.5 rounded-full bg-amber-500/15 border border-amber-400/30 text-amber-300 text-[10px] font-extrabold tracking-wider uppercase">
                            Admin & Verifikator
                        </span>
                    </div>

                    <h3
                        class="text-lg md:text-xl font-black text-white tracking-tight mb-1.5 group-hover:text-amber-300 transition-colors">
                        Panel Administrator
                    </h3>
                    <p class="text-[11px] md:text-xs text-slate-400 leading-relaxed mb-5 font-medium">
                        Portal manajemen terpadu untuk pimpinan, admin kepegawaian, verifikator LHK, serta pengelolaan
                        keuangan SPJ & persediaan dinas.
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
                            <span>Pengadaan Barang & SPJ Keuangan Terpadu</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-amber-400 text-xs"></i>
                            <span>Master Pegawai, Mesin ZKTeco & Shift Kerja</span>
                        </li>
                    </ul>
                </div>

                <div>
                    @auth
                        @if (auth()->user()->hasAnyRole(['super_admin', 'admin']))
                            <a href="{{ url('/admin') }}"
                                class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-orange-400 hover:from-amber-400 hover:to-orange-300 text-slate-950 font-black text-sm shadow-lg shadow-amber-500/25 transition-all flex items-center justify-center gap-2 active:scale-95">
                                <i class="fa-solid fa-gauge-high"></i>
                                <span>Buka Panel Admin</span>
                                <i
                                    class="fa-solid fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
                            </a>
                        @else
                            <div class="p-3 rounded-xl bg-slate-800/60 border border-slate-700/50 text-center">
                                <span class="text-[11px] text-slate-400 font-medium block">
                                    <i class="fa-solid fa-lock text-amber-400/70 mr-1"></i> Akun Anda berstatus
                                    <b>Pegawai</b>
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

            <!-- =========================================== -->
            <!-- CARD 2 (KANAN): PEGAWAI / PRESENSI & LHK   -->
            <!-- =========================================== -->
            <div
                class="group relative rounded-[22px] bg-gradient-to-b from-sky-950/50 to-slate-900/60 border border-sky-500/20 hover:border-sky-400/50 transition-all duration-300 p-6 flex flex-col justify-between overflow-hidden hover:-translate-y-1 hover:shadow-xl hover:shadow-sky-500/10">
                <div
                    class="absolute -right-6 -top-6 w-24 h-24 bg-sky-500/15 rounded-full blur-2xl group-hover:bg-sky-400/25 transition-all pointer-events-none">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div
                            class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-600 to-teal-400 text-white flex items-center justify-center shadow-lg shadow-sky-600/25 text-xl group-hover:scale-110 transition-transform">
                            <i class="fa-solid fa-mobile-screen-button"></i>
                        </div>
                        <span
                            class="px-2.5 py-0.5 rounded-full bg-sky-500/15 border border-sky-400/30 text-sky-300 text-[10px] font-extrabold tracking-wider uppercase">
                            Pegawai / ASN
                        </span>
                    </div>

                    <h3
                        class="text-lg md:text-xl font-black text-white tracking-tight mb-1.5 group-hover:text-sky-300 transition-colors">
                        Presensi & LHK Mobile
                    </h3>
                    <p class="text-[11px] md:text-xs text-slate-400 leading-relaxed mb-5 font-medium">
                        Aplikasi khusus seluruh pegawai ASN & Non-ASN untuk absensi harian berbasis GPS/WFO/WFH,
                        pengisian LHK, dan riwayat presensi.
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
                            <span>Pengajuan Izin & Dinas Luar Online</span>
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

        </div>

        <!-- Info Bar -->
        <div
            class="mt-8 max-w-3xl mx-auto w-full bg-white/[0.03] backdrop-blur-md rounded-xl p-3.5 border border-white/[0.06] flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 gap-2.5">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-shield-heart text-sky-400/70"></i>
                <span>Keamanan Terproteksi: Geofencing, Deteksi Fake GPS & Enkripsi Log</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="hover:text-slate-300 transition cursor-pointer"><i
                        class="fa-solid fa-circle-question mr-0.5"></i>Panduan</span>
                <span class="text-white/15">|</span>
                <span class="hover:text-slate-300 transition cursor-pointer"><i
                        class="fa-solid fa-headset mr-0.5"></i>Bantuan IT</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer
        class="w-full border-t border-white/[0.06] py-4 text-center text-[11px] text-slate-500 relative z-10 bg-slate-950/40 backdrop-blur-sm">
        <p class="font-medium">
            &copy; {{ date('Y') }} {{ $pengaturan->nama_dinas ?? 'Dinas Pariwisata Pemuda dan Olahraga' }} &bull; Kabupaten
            Sijunjung
        </p>
        <p class="text-[10px] text-slate-600 mt-0.5">
            {{ $pengaturan->versi_aplikasi ?? 'v1.0' }} &bull; Filament Admin & Livewire Mobile PWA
        </p>
    </footer>

    <!-- ===== Stars & Comets JavaScript ===== -->
    <script>
        // 1. Bintang Statis (Diam di Tempat, Berkelap-kelip Lembut)
        (function() {
            const container = document.getElementById('skyStars');
            const totalStars = 110;

            for (let i = 0; i < totalStars; i++) {
                const s = document.createElement('div');
                s.className = 'static-star';

                const size = (Math.random() * 2 + 0.8).toFixed(1);
                const left = (Math.random() * 100).toFixed(2);
                const top = (Math.random() * 100).toFixed(2);
                const dur = (Math.random() * 3 + 2).toFixed(1);
                const delay = (Math.random() * 4).toFixed(1);
                const minOp = (Math.random() * 0.2 + 0.1).toFixed(2);
                const maxOp = (Math.random() * 0.45 + 0.55).toFixed(2);

                s.style.width = size + 'px';
                s.style.height = size + 'px';
                s.style.left = left + '%';
                s.style.top = top + '%';
                s.style.setProperty('--twinkle-dur', dur + 's');
                s.style.setProperty('--min-op', minOp);
                s.style.setProperty('--max-op', maxOp);
                s.style.animationDelay = delay + 's';

                container.appendChild(s);
            }

            // Tambahkan beberapa bintang berkilau silang (glint) yang tetap diam
            for (let j = 0; j < 8; j++) {
                const g = document.createElement('div');
                g.className = 'glint-star';
                g.style.left = (Math.random() * 94 + 3).toFixed(2) + '%';
                g.style.top = (Math.random() * 85 + 5).toFixed(2) + '%';
                g.style.animationDelay = (Math.random() * 5).toFixed(1) + 's';
                g.style.animationDuration = (Math.random() * 3 + 3.5).toFixed(1) + 's';
                container.appendChild(g);
            }
        })();

        // 2. Komet Melayang / Bergerak Melintasi Langit
        (function() {
            const container = document.getElementById('cometLayer');

            function spawnComet() {
                const comet = document.createElement('div');
                comet.className = 'comet';

                // Posisi awal muncul di atas / kanan atas
                const startX = Math.random() * 65 + 30; // 30% - 95% width
                const startY = Math.random() * 30 + 2; // 2% - 32% height

                // Variasi lintasan komet
                const angle = -(Math.random() * 15 + 30); // -30deg s/d -45deg
                const tailLen = Math.floor(Math.random() * 70 + 130); // 130px - 200px
                const speed = (Math.random() * 0.8 + 1.2).toFixed(2); // 1.2s - 2.0s
                const travelX = -(Math.random() * 250 + 550); // -550px s/d -800px
                const travelY = Math.random() * 200 + 400; // 400px s/d 600px

                comet.style.left = startX + '%';
                comet.style.top = startY + '%';
                comet.style.setProperty('--angle', angle + 'deg');
                comet.style.setProperty('--tail-len', tailLen + 'px');
                comet.style.setProperty('--travel-x', travelX + 'px');
                comet.style.setProperty('--travel-y', travelY + 'px');
                comet.style.animation = `cometFlight ${speed}s cubic-bezier(0.25, 0.1, 0.25, 1) forwards`;

                container.appendChild(comet);

                setTimeout(() => {
                    comet.remove();
                }, speed * 1000 + 100);
            }

            // Komet pertama muncul cepat
            setTimeout(spawnComet, 1200);

            // Interval komet berkala secara acak
            function scheduleNextComet() {
                const nextDelay = Math.random() * 3500 + 2500; // setiap 2.5 - 6 detik
                setTimeout(() => {
                    spawnComet();
                    scheduleNextComet();
                }, nextDelay);
            }
            scheduleNextComet();
        })();
    </script>
</body>

</html>
