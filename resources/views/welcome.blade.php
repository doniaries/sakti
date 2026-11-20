<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="google-site-verification" content="1l9WVhQ-bVhmRSL4KJIOxPk9MJ1NRdE99cOTtxgHEK4" />
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/') }}">
    <title>Portal Parpora Sijunjung – SAKTI Parpora | Dinas Pariwisata, Pemuda dan Olahraga</title>
    <meta name="description" content="Portal Parpora Sijunjung (SAKTI Parpora) – Portal Layanan Digital Dinas Pariwisata, Pemuda dan Olahraga (Disparpora) Kabupaten Sijunjung. Akses presensi pegawai, Laporan Harian Kinerja (LHK), SPJ, dan layanan terpadu Parpora Sijunjung.">
    <meta name="keywords" content="Portal Parpora, Portal Parpora Sijunjung, SAKTI Parpora, SAKTI Parpora Sijunjung, Parpora Sijunjung, Disparpora Sijunjung, Sistem Administrasi Kinerja Terintegrasi, Absensi Disparpora Sijunjung">

    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:locale" content="id_ID">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:site_name" content="Portal Parpora Sijunjung (SAKTI Parpora)">
    <meta property="og:title" content="Portal Parpora Sijunjung – SAKTI Parpora | Disparpora Kabupaten Sijunjung">
    <meta property="og:description" content="Satu Portal untuk Semua Layanan Digital Dinas Pariwisata, Pemuda dan Olahraga (Parpora) Kabupaten Sijunjung, termasuk SAKTI Parpora, Presensi Mobile, dan LHK.">
    <meta property="og:image" content="{{ asset('icons/icon-512x512.png') }}">

    <!-- Structured Data (JSON-LD) -->
    @php
        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => 'Portal Parpora Sijunjung – SAKTI Parpora',
            'alternateName' => [
                'Portal Parpora',
                'Portal Parpora Sijunjung',
                'SAKTI Parpora',
                'SAKTI Parpora Sijunjung',
                'Parpora Sijunjung',
                'SAKTI Disparpora Sijunjung',
            ],
            'url' => url('/'),
            'applicationCategory' => 'GovernmentApplication',
            'operatingSystem' => 'Web, Android, iOS',
            'description' => 'Portal Layanan Digital Dinas Pariwisata, Pemuda dan Olahraga (Parpora) Kabupaten Sijunjung – SAKTI Parpora (Sistem Administrasi Kinerja Terintegrasi).',
            'publisher' => [
                '@type' => 'GovernmentOrganization',
                'name' => $pengaturan->nama_dinas ?? 'Dinas Pariwisata, Pemuda dan Olahraga Kabupaten Sijunjung',
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    <!-- PWA Setup -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
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

        [x-cloak] {
            display: none !important;
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
    <div class="w-full max-w-6xl mx-auto px-4 sm:px-6 py-6 md:py-12 pb-32 md:pb-12 relative z-10 flex-1 flex flex-col justify-center"
        x-data="{
            activeCategory: 'all',
            infoModal: false,
            loginModal: false,
            searchOpen: false,
            searchQuery: '',
            matches(cat, title, desc) {
                const catMatch = this.activeCategory === 'all' || this.activeCategory === cat;
                if (!this.searchQuery) return catMatch;
                const q = this.searchQuery.toLowerCase();
                return catMatch && (title.toLowerCase().includes(q) || desc.toLowerCase().includes(q));
            }
        }">

        <!-- ======================================================== -->
        <!-- TOP APP BAR KHUSUS MOBILE (ALA ASN DIGITAL BKN)          -->
        <!-- ======================================================== -->
        <div
            class="md:hidden sticky top-0 z-30 -mx-4 -mt-6 mb-5 px-4 py-3 bg-slate-950/85 backdrop-blur-xl border-b border-white/10 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('images/kabupaten-sijunjung.png') }}" alt="Logo Sijunjung"
                    class="w-8 h-8 object-contain drop-shadow-md">
                <div>
                    <div class="text-xs font-black tracking-tight text-white flex items-center gap-1.5">
                        <span>{{ $pengaturan->nama_aplikasi ?? 'SAKTI' }}</span>
                        <span
                            class="px-1.5 py-0.5 rounded-full bg-sky-500/20 text-sky-300 text-[9px] font-extrabold uppercase">Digital</span>
                    </div>
                    <div class="text-[10px] text-slate-400 truncate max-w-[200px]">
                        {{ $pengaturan->nama_dinas ?? 'Dinas Pariwisata Pemuda dan Olahraga' }}
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="searchOpen = !searchOpen"
                    class="w-8 h-8 rounded-full bg-white/5 border border-white/10 text-slate-300 flex items-center justify-center hover:text-white transition-colors cursor-pointer">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </button>
                <button type="button" @click="infoModal = true"
                    class="w-8 h-8 rounded-full bg-sky-500/15 border border-sky-400/30 text-sky-300 flex items-center justify-center hover:text-white transition-colors cursor-pointer">
                    <i class="fa-solid fa-circle-question text-xs"></i>
                </button>
            </div>
        </div>

        <!-- Search Drawer Mobile -->
        <div x-show="searchOpen" x-cloak x-transition class="md:hidden mb-5 -mt-1">
            <div class="relative">
                <i
                    class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" x-model="searchQuery" placeholder="Cari layanan, SPJ, absensi, atau fitur..."
                    class="w-full bg-white/10 border border-white/20 rounded-2xl py-2.5 pl-9 pr-8 text-xs text-white placeholder-slate-400 focus:outline-hidden focus:ring-2 focus:ring-sky-400 backdrop-blur-md">
                <button type="button" x-show="searchQuery" @click="searchQuery = ''"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- HEADER & CENTRAL HUB (INSPIRASI BKN ASN DIGITAL)         -->
        <!-- ======================================================== -->
        <header class="text-center mb-8 md:mb-10">
            <!-- Logos & Instansi -->
            <div class="inline-flex items-center justify-center gap-3 sm:gap-5 mb-4">
                <!-- Logo Kabupaten Sijunjung (Kiri) -->
                <img src="{{ asset('images/kabupaten-sijunjung.png') }}" alt="Logo Kabupaten Sijunjung"
                    class="w-12 h-12 sm:w-16 sm:h-16 object-contain drop-shadow-[0_4px_14px_rgba(0,0,0,.6)] logo-hover">

                <!-- Central Circular Emblem Hub -->
                <div class="relative group">
                    <div
                        class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-tr from-sky-500 via-teal-400 to-emerald-400 p-0.5 shadow-[0_0_25px_rgba(56,189,248,0.4)] flex items-center justify-center transition-transform duration-500 group-hover:scale-105">
                        <div
                            class="w-full h-full rounded-full bg-slate-950 flex flex-col items-center justify-center overflow-hidden relative">
                            <div class="absolute inset-0 bg-radial from-sky-500/20 to-transparent pointer-events-none">
                            </div>
                            <span
                                class="text-xs sm:text-sm font-black tracking-widest text-transparent bg-clip-text bg-gradient-to-r from-sky-200 via-white to-teal-200">PORTAL</span>
                            <span
                                class="text-[8px] sm:text-[9px] font-extrabold uppercase tracking-wider text-amber-300">PARPORA</span>
                        </div>
                    </div>
                    <!-- Outer Orbit Pulse Ring -->
                    <div
                        class="absolute -inset-1 rounded-full border border-sky-400/30 animate-ping pointer-events-none opacity-40">
                    </div>
                </div>

                <!-- Logo Geopark Silokek (Kanan) -->
                <img src="{{ asset('images/geopark-silokek.png') }}" alt="Logo Geopark Silokek"
                    class="w-12 h-12 sm:w-16 sm:h-16 object-contain drop-shadow-[0_4px_14px_rgba(0,0,0,.6)] logo-hover">
            </div>

            <!-- Nama Instansi & Badge Wilayah -->
            <div class="mb-2">
                <span
                    class="inline-block px-3.5 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-400/30 text-emerald-300 text-[10px] sm:text-[11px] font-extrabold uppercase tracking-[2px] mb-1">
                    Pemerintah Kabupaten Sijunjung
                </span>
                <h2 class="text-xs sm:text-sm md:text-base font-bold text-slate-200 tracking-tight">
                    {{ $pengaturan->nama_dinas ?? 'Dinas Pariwisata Pemuda dan Olahraga' }}
                </h2>
            </div>

            <!-- Judul Utama Sistem -->
            <h1
                class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black uppercase tracking-tight leading-none mb-2
                       text-transparent bg-clip-text bg-gradient-to-r from-white via-sky-100 to-sky-300 drop-shadow-sm">
                PORTAL PARPORA
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 max-w-2xl mx-auto font-medium leading-relaxed mb-4">
                Portal Layanan Digital Disparpora Sijunjung
            </p>

            <!-- Slogan Mandat ala BKN ASN Digital (Capsule Pill) -->
            <div
                class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/[0.04] border border-white/10 backdrop-blur-md text-[11px] sm:text-xs text-sky-200 font-medium shadow-sm mb-4">
                <i class="fa-solid fa-quote-left text-sky-400 text-[10px]"></i>
                <span>Satu Portal untuk Semua Layanan Disparpora</span>
                <i class="fa-solid fa-quote-right text-sky-400 text-[10px]"></i>
            </div>

            <!-- Digital Clock, Tahun Anggaran & Bantuan -->
            <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-2.5">
                <div class="inline-flex items-center gap-2 bg-white/[0.04] border border-white/[0.08] backdrop-blur-md px-3.5 py-1.5 rounded-full text-xs text-slate-300"
                    x-data="{
                        time: '--:--',
                        date: '{{ now()->translatedFormat('l, d F Y') }}',
                        update() {
                            const d = new Date();
                            this.time = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
                        }
                    }" x-init="update();
                    setInterval(() => update(), 1000);">
                    <i class="fa-regular fa-calendar text-sky-400 text-[11px]"></i>
                    <span x-text="date">{{ now()->translatedFormat('l, d F Y') }}</span>
                    <span class="w-1 h-1 rounded-full bg-slate-600"></span>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span x-text="time + ' WIB'" class="font-mono font-bold text-amber-300 tracking-wider">--:--
                        WIB</span>
                </div>

                <!-- Badge Tahun Anggaran Aktif -->
                <div
                    class="inline-flex items-center gap-1.5 bg-amber-500/15 border border-amber-400/30 text-amber-300 backdrop-blur-md px-3 py-1.5 rounded-full text-xs font-bold">
                    <i class="fa-solid fa-calendar-check text-[11px]"></i>
                    <span>Tahun Anggaran {{ \App\Models\PengaturanAplikasi::getTahunAnggaranAktif() }}</span>
                </div>

                {{-- <button type="button" @click="infoModal = true"
                    class="inline-flex items-center gap-1.5 bg-sky-500/15 hover:bg-sky-500/25 text-sky-200 border border-sky-400/30 backdrop-blur-md px-3 py-1.5 rounded-full text-xs font-semibold transition-all active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-circle-info text-sky-400"></i>
                    <span>Panduan Alur</span>
                </button> --}}
            </div>
        </header>

        <!-- User Logged-in Banner -->
        @auth
            <div
                class="mb-6 p-4 rounded-[28px] bg-white/[0.05] backdrop-blur-md border border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 max-w-3xl mx-auto w-full">
                <div class="flex items-center gap-3 text-center sm:text-left">
                    <img src="{{ auth()->user()->avatar_image_url }}" alt="{{ auth()->user()->name }}"
                        class="w-11 h-11 rounded-2xl object-cover border-2 border-sky-400/60 shadow-md">
                    <div>
                        <div class="text-[10px] text-sky-300/80 font-bold uppercase tracking-wider">Sedang Login:</div>
                        <div class="text-sm font-extrabold text-white">{{ auth()->user()->name }}</div>
                        <div class="text-[11px] text-slate-400 font-mono">
                            <span
                                class="text-amber-300 font-bold uppercase">{{ auth()->user()->roles->pluck('name')->implode(', ') ?: 'Pegawai' }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if (auth()->user()->hasAnyRole(['super_admin', 'admin']))
                        <a href="{{ url('/admin/portal-links') }}"
                            class="px-3 py-2 bg-sky-500/20 hover:bg-sky-500/30 text-sky-200 border border-sky-400/30 rounded-2xl text-xs font-bold transition-all flex items-center gap-1.5">
                            <i class="fa-solid fa-sliders"></i>
                            <span>Kelola Link</span>
                        </a>
                    @endif
                    <form action="{{ route('portal.logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="px-3.5 py-2 bg-rose-500/15 hover:bg-rose-500 text-rose-300 hover:text-white border border-rose-400/30 rounded-2xl text-xs font-bold transition-all flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        @endauth

        <!-- ======================================================== -->
        <!-- FILTER KATEGORI (ORGANIC CAPSULE PILLS)                   -->
        <!-- ======================================================== -->
        @php
            $portalLinksCollection = isset($portalLinks) && $portalLinks->count() > 0 ? $portalLinks : collect();
            $categories = $portalLinksCollection->pluck('kategori')->unique()->filter()->values();
        @endphp

        @if ($categories->count() > 1)
            <div class="flex flex-wrap items-center justify-center gap-2 mb-8">
                <button type="button" @click="activeCategory = 'all'"
                    :class="activeCategory === 'all' ?
                        'bg-sky-500 text-slate-950 font-black shadow-lg shadow-sky-500/30 border-sky-400' :
                        'bg-white/[0.04] text-slate-400 hover:text-white border-white/10'"
                    class="px-4 py-2 rounded-full text-xs font-bold border backdrop-blur-md transition-all duration-200 cursor-pointer">
                    Semua Layanan ({{ $portalLinksCollection->count() }})
                </button>
                @foreach ($categories as $cat)
                    @php
                        $catName = match ($cat) {
                            'keuangan' => 'Keuangan & SPJ',
                            'kepegawaian' => 'Kepegawaian & Presensi',
                            'kinerja', 'layanan_utama' => 'Kinerja & Mobile',
                            'administrasi' => 'Panel Admin',
                            'panduan', 'dokumen_lampiran' => 'Dokumen & Regulasi',
                            'eksternal' => 'Layanan Eksternal',
                            default => ucfirst(str_replace('_', ' ', $cat)),
                        };
                    @endphp
                    <button type="button" @click="activeCategory = '{{ $cat }}'"
                        :class="activeCategory === '{{ $cat }}' ?
                            'bg-sky-500 text-slate-950 font-black shadow-lg shadow-sky-500/30 border-sky-400' :
                            'bg-white/[0.04] text-slate-400 hover:text-white border-white/10'"
                        class="px-4 py-2 rounded-full text-xs font-bold border backdrop-blur-md transition-all duration-200 cursor-pointer">
                        {{ $catName }}
                    </button>
                @endforeach
            </div>
        @else
            <!-- Divider Elegance -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-3">
                    <span class="w-10 h-px bg-gradient-to-r from-transparent to-sky-400/40"></span>
                    <span class="text-[10px] uppercase font-extrabold tracking-[2.5px] text-sky-300/80">
                        Pilihan Pintu Akses Layanan & Modul
                    </span>
                    <span class="w-10 h-px bg-gradient-to-l from-transparent to-sky-400/40"></span>
                </div>
            </div>
        @endif

        <!-- ======================================================== -->
        <!-- GRID KARTU NON-PETAK (ORGANIC SQUIRCLE & SATELLITE CARDS)-->
        <!-- ======================================================== -->
        <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-5 lg:gap-6 w-full">
            @forelse($portalLinksCollection as $link)
                @php
                    $tokens = $link->style_tokens;
                    $shapeClass = match ($link->bentuk) {
                        'capsule' => 'rounded-2xl sm:rounded-[44px]',
                        'circle' => 'rounded-2xl sm:rounded-[36px]',
                        default => 'rounded-2xl sm:rounded-[32px] lg:rounded-[36px]',
                    };
                @endphp
                <div x-show="matches('{{ $link->kategori }}', '{{ addslashes($link->judul) }}', '{{ addslashes($link->deskripsi ?? '') }}')"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    onclick="if (!event.target.closest('a, button, input, label')) { if ('{{ $link->target ?? '_self' }}' === '_blank') { window.open('{{ $link->link_url }}', '_blank'); } else { window.location.href = '{{ $link->link_url }}'; } }"
                    class="group relative cursor-pointer {{ $shapeClass }} bg-gradient-to-b {{ $tokens['card_bg'] }} border {{ $tokens['border'] }} p-3.5 sm:p-6 lg:p-7 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1.5 sm:hover:-translate-y-2 hover:shadow-2xl {{ $tokens['shadow_hover'] }} overflow-hidden">

                    <!-- Ambient Glow Blur di Sudut -->
                    <div
                        class="absolute -right-6 -top-6 sm:-right-8 sm:-top-8 w-20 h-20 sm:w-32 sm:h-32 {{ $tokens['glow_ambient'] }} rounded-full blur-2xl transition-all pointer-events-none">
                    </div>

                    <div>
                        <!-- Header Row: Elevated Squircle Icon & Badges -->
                        <div class="flex items-start justify-between gap-1.5 mb-2.5 sm:mb-4">
                            <div
                                class="w-10 h-10 sm:w-14 sm:h-14 shrink-0 rounded-xl sm:rounded-[22px] {{ $tokens['icon_box'] }} flex items-center justify-center text-base sm:text-2xl shadow-lg group-hover:scale-110 transition-transform duration-300">
                                <i class="{{ $link->fa_icon }}"></i>
                            </div>

                            <div class="flex items-center gap-1 sm:gap-1.5 flex-wrap justify-end">
                                <!-- Lampiran Download Button (Jika Ada Berkas) -->
                                @if ($link->lampiran_path)
                                    <a href="{{ $link->lampiran_url }}" target="_blank" download
                                        title="Unduh Berkas Lampiran"
                                        class="px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 text-white text-[8px] sm:text-[10px] font-bold tracking-wider uppercase transition-colors inline-flex items-center gap-1 shadow-sm">
                                        <i class="fa-solid fa-paperclip text-[8px] sm:text-[9px]"></i>
                                        <span class="hidden sm:inline">Berkas</span>
                                    </a>
                                @endif

                                @if ($link->badge)
                                    <span
                                        class="px-2 py-0.5 sm:px-3 sm:py-1 rounded-full {{ $tokens['badge'] }} text-[8px] sm:text-[10px] font-extrabold tracking-wider uppercase truncate max-w-[78px] sm:max-w-none">
                                        {{ $link->badge }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Judul & Deskripsi Fitur -->
                        <h3
                            class="text-xs sm:text-xl font-black text-white leading-snug tracking-tight mb-1 sm:mb-2 line-clamp-2 {{ $tokens['text_accent'] }} transition-colors">
                            {{ $link->judul }}
                        </h3>
                        <p class="text-[10px] sm:text-[13px] text-slate-300/90 leading-snug sm:leading-relaxed line-clamp-2 sm:line-clamp-none mb-3 sm:mb-6 font-normal">
                            {{ $link->deskripsi }}
                        </p>
                    </div>

                    <!-- Footer Action Button -->
                    <div class="pt-1 sm:pt-2">
                        <a href="{{ $link->link_url }}" target="{{ $link->target ?? '_self' }}"
                            class="w-full py-2 px-3 sm:py-3.5 sm:px-5 rounded-xl sm:rounded-2xl {{ $tokens['btn'] }} font-black text-[11px] sm:text-sm transition-all flex items-center justify-center gap-1.5 sm:gap-2 active:scale-95 shadow-md">
                            <span>Buka</span>
                            <span class="hidden sm:inline">Layanan</span>
                            <i
                                class="fa-solid fa-arrow-right text-[10px] sm:text-xs transition-transform group-hover:translate-x-1.5"></i>
                        </a>

                        @auth
                            @if (auth()->user()->hasAnyRole(['super_admin', 'admin']))
                                <div class="mt-2 text-center">
                                    <a href="{{ url('/admin/portal-links/' . $link->id . '/edit') }}"
                                        class="inline-flex items-center gap-1 text-[10px] text-slate-400 hover:text-sky-300 transition-colors">
                                        <i class="fa-solid fa-pen-to-square text-[9px]"></i>
                                        <span>Kelola Kartu Ini</span>
                                    </a>
                                </div>
                            @endif
                        @endauth
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12 text-slate-400">
                    <i class="fa-solid fa-layer-group text-3xl mb-3 text-slate-600 block"></i>
                    <p class="text-sm">Belum ada kartu portal yang aktif. Silakan tambahkan melalui Panel Admin.</p>
                </div>
            @endforelse
        </div>

        <!-- Info Bar Keamanan & Shortcut Admin -->
        <div
            class="mt-10 max-w-4xl mx-auto w-full bg-white/[0.03] backdrop-blur-md rounded-2xl p-4 border border-white/[0.08] flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-3">
            <div class="flex items-center gap-2 text-slate-300">
                <i class="fa-solid fa-shield-heart text-sky-400"></i>
                <span>Keamanan Terproteksi: Geofencing GPS, Enkripsi Log & Verifikasi QR SPJ</span>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" @click="infoModal = true"
                    class="hover:text-white transition cursor-pointer flex items-center gap-1.5 text-sky-300 font-semibold">
                    <i class="fa-solid fa-circle-question"></i>
                    <span>Panduan Alur Sistem</span>
                </button>
                @auth
                    @if (auth()->user()->hasAnyRole(['super_admin', 'admin']))
                        <span class="text-white/20">|</span>
                        <a href="{{ url('/admin/portal-links') }}"
                            class="hover:text-amber-300 transition flex items-center gap-1 text-amber-400 font-semibold">
                            <i class="fa-solid fa-gear"></i>
                            <span>Kelola Portal</span>
                        </a>
                    @endif
                @endauth
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- FLOATING BOTTOM NAVBAR MOBILE (INSPIRASI BKN ASN DIGITAL) -->
        <!-- ======================================================== -->
        <nav
            class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-slate-950/95 backdrop-blur-2xl border-t border-sky-500/20 shadow-[0_-10px_35px_rgba(0,0,0,0.6)] px-6 py-1.5 pb-safe">
            <div class="max-w-md mx-auto flex items-end justify-between relative">
                <!-- Tab 1: Beranda -->
                <button type="button"
                    @click="window.scrollTo({top: 0, behavior: 'smooth'}); activeCategory = 'all'; searchQuery = ''"
                    class="flex flex-col items-center justify-center flex-1 py-1 group cursor-pointer">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center transition-colors"
                        :class="activeCategory === 'all' && !searchQuery ? 'bg-sky-500/15 text-sky-400' :
                            'text-slate-400 group-hover:text-white'">
                        <i class="fa-solid fa-house text-lg"></i>
                    </div>
                    <span class="text-[10px] font-bold tracking-tight -mt-0.5"
                        :class="activeCategory === 'all' && !searchQuery ? 'text-sky-400' :
                            'text-slate-400 group-hover:text-white'">
                        Beranda
                    </span>
                </button>

                <!-- Tab 2: Elevated Center Button (Fingerprint / SAKTI Hub) -->
                <div class="flex-1 flex flex-col items-center justify-center -mt-6 relative">
                    <a href="{{ route('mobile.absensi') }}"
                        class="w-16 h-16 rounded-full bg-gradient-to-tr from-sky-400 via-teal-300 to-emerald-400 p-0.5 shadow-[0_10px_25px_rgba(56,189,248,0.5)] flex items-center justify-center transition-transform active:scale-90 group relative">
                        <div
                            class="w-full h-full rounded-full bg-slate-950 flex flex-col items-center justify-center border-2 border-white/20">
                            <i
                                class="fa-solid fa-fingerprint text-2xl text-transparent bg-clip-text bg-gradient-to-tr from-sky-400 to-teal-300 group-hover:scale-110 transition-transform"></i>
                        </div>
                        <!-- Outer Ping Ring -->
                        <div
                            class="absolute -inset-1 rounded-full border border-sky-400/40 animate-ping pointer-events-none opacity-30">
                        </div>
                    </a>
                    <span class="text-[10px] font-extrabold text-sky-300 tracking-wider mt-1 uppercase">SAKTI</span>
                </div>

                <!-- Tab 3: Masuk / Akun -->
                <button type="button" @click="loginModal = true"
                    class="flex flex-col items-center justify-center flex-1 py-1 group cursor-pointer">
                    <div
                        class="w-8 h-8 rounded-full flex items-center justify-center transition-colors text-slate-400 group-hover:text-white">
                        @auth
                            <i class="fa-solid fa-circle-user text-lg text-emerald-400"></i>
                        @else
                            <i class="fa-solid fa-arrow-right-to-bracket text-lg"></i>
                        @endauth
                    </div>
                    <span class="text-[10px] font-bold tracking-tight -mt-0.5 text-slate-400 group-hover:text-white">
                        @auth Akun
                        @else
                        Masuk @endauth
                    </span>
                </button>
            </div>
        </nav>

        <!-- ===== MODAL PILIHAN AKSES MASUK (PORTAL ACCESS MODAL) ===== -->
        <div x-show="loginModal" x-cloak
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-6 overflow-y-auto"
            role="dialog" aria-modal="true">

            <!-- Backdrop Blur -->
            <div x-show="loginModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="loginModal = false"
                class="fixed inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity"></div>

            <!-- Modal Box (Bottom Sheet on mobile, squircle card on desktop) -->
            <div x-show="loginModal" x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-full sm:translate-y-4 sm:scale-95"
                class="relative w-full max-w-lg bg-[#0C1530] border-t sm:border border-white/15 rounded-t-[36px] sm:rounded-3xl p-6 sm:p-7 shadow-2xl text-left overflow-hidden z-10 max-h-[90vh] overflow-y-auto">

                <!-- Drag Handle for Mobile -->
                <div class="sm:hidden w-12 h-1.5 rounded-full bg-white/20 mx-auto mb-4"></div>

                <!-- Header Modal -->
                <div class="flex items-center justify-between pb-4 border-b border-white/10 mb-5">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-sky-400 to-teal-400 text-slate-950 flex items-center justify-center text-lg font-black shadow-md">
                            <i class="fa-solid fa-door-open"></i>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-black text-white">Pintu Masuk & Layanan</h3>
                            <p class="text-xs text-slate-400">Pilih akses sesuai peran Anda di instansi</p>
                        </div>
                    </div>
                    <button type="button" @click="loginModal = false"
                        class="w-8 h-8 rounded-full bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white flex items-center justify-center transition-all cursor-pointer">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                @auth
                    <!-- Jika User Sedang Login -->
                    <div class="p-4 rounded-2xl bg-white/[0.04] border border-white/10 mb-4">
                        <div class="flex items-center gap-3 mb-3">
                            <img src="{{ auth()->user()->avatar_image_url }}" alt="{{ auth()->user()->name }}"
                                class="w-12 h-12 rounded-2xl object-cover border-2 border-sky-400/60 shadow-md">
                            <div>
                                <div class="text-[10px] text-sky-300 font-bold uppercase tracking-wider">Akun Terhubung:
                                </div>
                                <div class="text-sm font-extrabold text-white">{{ auth()->user()->name }}</div>
                                <div class="text-xs text-slate-400 font-mono">
                                    {{ auth()->user()->roles->pluck('name')->implode(', ') ?: 'Pegawai' }}
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2 pt-2 border-t border-white/10">
                            <a href="{{ route('mobile.dashboard') }}"
                                class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-sky-500 to-teal-400 hover:from-sky-400 text-slate-950 font-black text-xs flex items-center justify-center gap-2">
                                <i class="fa-solid fa-compass"></i>
                                <span>Buka Dashboard Presensi</span>
                            </a>
                            @if (auth()->user()->hasAnyRole(['super_admin', 'admin', 'pptk', 'ppk', 'pa_kpa', 'bendahara_pengeluaran', 'pengurus_barang']))
                                <a href="{{ url('/admin') }}"
                                    class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-amber-500 to-orange-400 hover:from-amber-400 text-slate-950 font-black text-xs flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-gauge-high"></i>
                                    <span>Buka Panel Administrator & SPJ</span>
                                </a>
                            @endif
                            <form action="{{ route('portal.logout') }}" method="POST" class="mt-1">
                                @csrf
                                <button type="submit"
                                    class="w-full py-2 px-4 rounded-xl bg-rose-500/15 hover:bg-rose-500 text-rose-300 hover:text-white border border-rose-400/30 text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    <span>Keluar dari Akun</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- Pilihan Pintu Masuk Pengunjung -->
                    <div class="space-y-3">
                        <!-- Pintu 1: Pegawai Mobile -->
                        <a href="{{ route('mobile.login') }}"
                            class="group p-4 rounded-2xl bg-gradient-to-b from-sky-950/40 via-slate-900/80 to-slate-950/90 border border-sky-500/30 hover:border-sky-400/80 flex items-center justify-between transition-all hover:scale-[1.01]">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-sky-500 to-teal-400 text-slate-950 flex items-center justify-center text-lg font-black shadow-md group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-mobile-screen-button"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-black text-white group-hover:text-sky-300 transition-colors">
                                        Presensi & Kinerja Pegawai (Mobile)
                                    </div>
                                    <p class="text-[11px] text-slate-400">Absensi GPS mandiri, pengisian LHK harian, izin &
                                        rekap</p>
                                </div>
                            </div>
                            <i
                                class="fa-solid fa-arrow-right text-xs text-sky-400 group-hover:translate-x-1 transition-transform"></i>
                        </a>

                        <!-- Pintu 2: Admin & SPJ -->
                        <a href="{{ url('/admin/login') }}"
                            class="group p-4 rounded-2xl bg-gradient-to-b from-amber-950/40 via-slate-900/80 to-slate-950/90 border border-amber-500/30 hover:border-amber-400/80 flex items-center justify-between transition-all hover:scale-[1.01]">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-400 text-slate-950 flex items-center justify-center text-lg font-black shadow-md group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <div>
                                    <div
                                        class="text-xs font-black text-white group-hover:text-amber-300 transition-colors">
                                        Panel Admin & Keuangan SPJ
                                    </div>
                                    <p class="text-[11px] text-slate-400">Pejabat & verifikator: Surat Pesanan, verifikasi
                                        berjenjang SPJ, NPD</p>
                                </div>
                            </div>
                            <i
                                class="fa-solid fa-arrow-right text-xs text-amber-400 group-hover:translate-x-1 transition-transform"></i>
                        </a>

                        <!-- Pintu 3: Lacak Dokumen -->
                        <a href="{{ route('dokumen.tracking') }}"
                            class="group p-4 rounded-2xl bg-gradient-to-b from-teal-950/40 via-slate-900/80 to-slate-950/90 border border-teal-500/30 hover:border-teal-400/80 flex items-center justify-between transition-all hover:scale-[1.01]">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-teal-500 to-emerald-400 text-slate-950 flex items-center justify-center text-lg font-black shadow-md group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-qrcode"></i>
                                </div>
                                <div>
                                    <div class="text-xs font-black text-white group-hover:text-teal-300 transition-colors">
                                        Pelacakan Dokumen SPJ (QR Code)
                                    </div>
                                    <p class="text-[11px] text-slate-400">Verifikasi keabsahan lembar transaksi resmi
                                        secara terbuka</p>
                                </div>
                            </div>
                            <i
                                class="fa-solid fa-arrow-right text-xs text-teal-400 group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </div>

    <!-- ===== MODAL INFORMASI & PANDUAN ALUR SISTEM ===== -->
    <div x-show="infoModal" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto" role="dialog"
        aria-modal="true">

        <!-- Backdrop Blur -->
        <div x-show="infoModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="infoModal = false"
            class="fixed inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity"></div>

        <!-- Modal Box -->
        <div x-show="infoModal" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="relative w-full max-w-2xl bg-[#0C1530] border border-white/15 rounded-3xl p-6 sm:p-8 shadow-2xl text-left overflow-hidden z-10 my-8">

            <!-- Header Modal -->
            <div class="flex items-center justify-between pb-4 border-b border-white/10 mb-6">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-black text-white">Panduan & Alur Sistem SAKTI</h3>
                        <p class="text-xs text-slate-400">
                            {{ $pengaturan->sub_nama ?? 'Sistem Administrasi Kinerja Terintegrasi' }}</p>
                    </div>
                </div>
                <button type="button" @click="infoModal = false"
                    class="w-9 h-9 rounded-xl bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white flex items-center justify-center transition-all cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <div class="space-y-6 max-h-[70vh] overflow-y-auto pr-1">
                <!-- Bagian 1: Alur SPJ Keuangan & Pengadaan -->
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        <h4 class="text-xs font-black uppercase tracking-wider text-amber-300">
                            Alur Terpadu Pengadaan Barang & SPJ Keuangan
                        </h4>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="bg-white/[0.03] border border-white/5 rounded-2xl p-3.5">
                            <div class="flex items-center gap-2 text-amber-400 font-bold text-xs mb-1">
                                <span
                                    class="w-5 h-5 rounded-full bg-amber-500/20 flex items-center justify-center text-[10px]">1</span>
                                Surat Pesanan (Tahap Awal)
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Staf bidang menyusun rincian rencana belanja sesuai RKA. PPTK memeriksa dan
                                menandatangani Nota Pesanan resmi sebelum barang dibeli ke rekanan.
                            </p>
                        </div>

                        <div class="bg-white/[0.03] border border-white/5 rounded-2xl p-3.5">
                            <div class="flex items-center gap-2 text-sky-400 font-bold text-xs mb-1">
                                <span
                                    class="w-5 h-5 rounded-full bg-sky-500/20 flex items-center justify-center text-[10px]">2</span>
                                Realisasi Belanja & Bukti
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Setelah barang diterima, pemohon mengunggah faktur/nota asli rekanan, bukti foto fisik
                                barang, atau bundel berkas e-Katalog eproc.
                            </p>
                        </div>

                        <div class="bg-white/[0.03] border border-white/5 rounded-2xl p-3.5">
                            <div class="flex items-center gap-2 text-emerald-400 font-bold text-xs mb-1">
                                <span
                                    class="w-5 h-5 rounded-full bg-emerald-500/20 flex items-center justify-center text-[10px]">3</span>
                                Verifikasi 4 Tahap Berjenjang
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Pemeriksaan kelayakan belanja berjenjang secara paperless oleh PPTK (Kabid), PPK
                                (Kasubag), PA (Kepala Dinas), dan Bendahara Pengeluaran.
                            </p>
                        </div>

                        <div class="bg-white/[0.03] border border-white/5 rounded-2xl p-3.5">
                            <div class="flex items-center gap-2 text-violet-400 font-bold text-xs mb-1">
                                <span
                                    class="w-5 h-5 rounded-full bg-violet-500/20 flex items-center justify-center text-[10px]">4</span>
                                Dokumen SPJ Sah & Kas
                            </div>
                            <p class="text-[11px] text-slate-400 leading-relaxed">
                                Sistem menerbitkan otomatis Nota Pesanan, Kwitansi Pembayaran, dan NPD sah dengan QR
                                code, potongan pajak PPN/PPh, serta mutasi persediaan barang.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Bagian 2: Presensi & Kinerja Pegawai -->
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-2 h-2 rounded-full bg-sky-400"></span>
                        <h4 class="text-xs font-black uppercase tracking-wider text-sky-300">
                            Presensi & Laporan Kinerja Pegawai (Mobile PWA)
                        </h4>
                    </div>
                    <div
                        class="bg-white/[0.03] border border-white/5 rounded-2xl p-4 text-xs text-slate-300 space-y-2 leading-relaxed">
                        <p class="flex items-start gap-2">
                            <i class="fa-solid fa-check text-sky-400 mt-0.5 text-xs"></i>
                            <span><b>Absensi GPS & Geofencing:</b> Pegawai WFO absen masuk & pulang di radius koordinat
                                kantor, sedangkan pegawai WFH atau Dinas Luar mendapatkan fleksibilitas lokasi
                                resmi.</span>
                        </p>
                        <p class="flex items-start gap-2">
                            <i class="fa-solid fa-check text-sky-400 mt-0.5 text-xs"></i>
                            <span><b>Laporan Harian Kinerja (LHK):</b> Diisi harian setelah absensi lengkap, memuat
                                aktivitas harian, target output, dan foto bukti pelaksanaan tugas dinas.</span>
                        </p>
                        <p class="flex items-start gap-2">
                            <i class="fa-solid fa-check text-sky-400 mt-0.5 text-xs"></i>
                            <span><b>Cetak & Monitoring:</b> Rekap kehadiran bulanan dapat dicetak langsung ke PDF atau
                                printer bluetooth oleh pegawai bersangkutan.</span>
                        </p>
                    </div>
                </div>

                <!-- Bagian 3: Bantuan & Keamanan -->
                <div
                    class="p-3.5 rounded-2xl bg-sky-500/10 border border-sky-400/20 flex items-center justify-between text-xs text-sky-300">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Enkripsi Log & Anti Mock Location terintegrasi penuh.</span>
                    </div>
                    <button type="button" @click="infoModal = false"
                        class="px-3.5 py-1.5 rounded-xl bg-sky-500 text-slate-950 font-bold hover:bg-sky-400 transition-all text-xs cursor-pointer">
                        Tutup Panduan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer
        class="w-full border-t border-white/[0.06] py-4 text-center text-[11px] text-slate-500 relative z-10 bg-slate-950/40 backdrop-blur-sm">
        <p class="font-medium">
            &copy; {{ date('Y') }} {{ $pengaturan->nama_dinas ?? 'Dinas Pariwisata Pemuda dan Olahraga' }} &bull;
            Kabupaten
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
