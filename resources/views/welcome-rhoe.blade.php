<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url('/') }}">
    <title>Portal Digital Parpora – Disparpora Sijunjung</title>
    <meta name="description" content="Portal Digital Parpora Sijunjung – Portal Layanan Digital Dinas Pariwisata, Pemuda dan Olahraga Kabupaten Sijunjung.">

    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="manifest" href="{{ asset('manifest-portal.json') }}?v=1">
    <meta name="theme-color" content="#09090b">
    <meta name="apple-mobile-web-app-title" content="Portal Digital Parpora">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">

    @if (app()->environment('local') && file_exists(public_path('hot')))
        @vite(['resources/js/app.js'])
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        [x-cloak] { display: none !important; }

        /* Animated Dot Grid Background (Rhoe UI Style) */
        .rhoe-grid {
            background-image: radial-gradient(rgba(148, 163, 184, 0.18) 1px, transparent 1px);
            background-size: 24px 24px;
            mask-image: radial-gradient(ellipse 80% 60% at 50% 20%, #000 55%, transparent 100%);
        }
        html.rhoe-light .rhoe-grid {
            background-image: radial-gradient(rgba(15, 23, 42, 0.12) 1px, transparent 1px);
        }

        /* Spotlight Card Effect */
        .spotlight-card {
            position: relative;
            overflow: hidden;
        }
        .spotlight-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(420px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), rgba(56, 189, 248, 0.16), transparent 60%);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        .spotlight-card:hover::before {
            opacity: 1;
        }

        /* Animated Shimmer Badge */
        @keyframes shimmerSlide {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .shimmer-border {
            background: linear-gradient(110deg, rgba(255,255,255,0.06) 30%, rgba(56,189,248,0.28) 50%, rgba(255,255,255,0.06) 70%);
            background-size: 200% 100%;
            animation: shimmerSlide 4.5s linear infinite;
        }

        /* Light Mode Overrides + Visual Siang (Matahari, Awan, Pegunungan) */
        html.rhoe-light body {
            background: linear-gradient(180deg, #7dd3fc 0%, #bae6fd 32%, #e0f2fe 65%, #fef3c7 100%) !important;
            color: #09090b !important;
        }
        #rhoeDayScenery {
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.5s ease;
        }
        html.rhoe-light #rhoeDayScenery {
            opacity: 1;
        }
        @keyframes rhoeCloudFloat {
            0%, 100% { transform: translateX(0px); }
            50% { transform: translateX(26px); }
        }
        @keyframes rhoeCloudReverse {
            0%, 100% { transform: translateX(0px); }
            50% { transform: translateX(-22px); }
        }
        .rhoe-cloud-1 { animation: rhoeCloudFloat 18s ease-in-out infinite; }
        .rhoe-cloud-2 { animation: rhoeCloudReverse 24s ease-in-out infinite; }
        .rhoe-cloud-3 { animation: rhoeCloudFloat 21s ease-in-out infinite; }
        html.rhoe-light .rhoe-surface {
            background-color: rgba(255, 255, 255, 0.88) !important;
            border-color: rgba(228, 228, 231, 0.9) !important;
            color: #09090b !important;
        }
        html.rhoe-light .rhoe-muted {
            color: #52525b !important;
        }
        html.rhoe-light .rhoe-heading {
            background-image: linear-gradient(to bottom, #09090b, #3f3f46) !important;
        }
        html.rhoe-light .rhoe-hero-badge {
            background: rgba(255, 255, 255, 0.88) !important;
            border-color: rgba(2, 132, 199, 0.35) !important;
            color: #0369a1 !important;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
        }
        html.rhoe-light .rhoe-card-badge {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
            border: 1px solid #cbd5e1 !important;
        }
    </style>
    <script>
        (function () {
            const saved = localStorage.getItem('rhoe_theme_override');
            const hour = new Date().getHours();
            const isLight = saved ? saved === 'light' : (hour >= 6 && hour < 18);
            document.documentElement.classList.toggle('rhoe-light', isLight);
        })();
        window.toggleRhoeTheme = function () {
            const isLight = document.documentElement.classList.toggle('rhoe-light');
            localStorage.setItem('rhoe_theme_override', isLight ? 'light' : 'dark');
            window.dispatchEvent(new CustomEvent('rhoe-theme-changed', { detail: { light: isLight } }));
        };
    </script>
</head>

@php
    $portalLinksCollection = collect($portalLinks ?? []);
    $categories = $portalLinksCollection->pluck('kategori')->filter()->unique()->values();
@endphp

<body class="bg-[#09090b] text-zinc-100 min-h-screen flex flex-col justify-between relative overflow-x-hidden transition-colors duration-300"
    x-data="{
        activeCategory: 'all',
        searchQuery: '',
        isLight: document.documentElement.classList.contains('rhoe-light'),
        matches(cat, title, desc) {
            const catMatch = this.activeCategory === 'all' || this.activeCategory === cat;
            if (!this.searchQuery) return catMatch;
            const q = this.searchQuery.toLowerCase();
            return catMatch && (title.toLowerCase().includes(q) || desc.toLowerCase().includes(q));
        }
    }"
    @rhoe-theme-changed.window="isLight = $event.detail.light">

    <!-- Background Dot Grid + Subtle Top Aurora -->
    <div class="fixed inset-0 rhoe-grid pointer-events-none z-0"></div>
    <div class="fixed -top-24 left-1/2 -translate-x-1/2 w-[680px] h-[280px] bg-gradient-to-r from-sky-500/20 via-indigo-500/20 to-emerald-500/20 blur-[110px] rounded-full pointer-events-none z-0"></div>

    <!-- Visual Siang: Matahari, Awan & Pegunungan (Mode Terang) -->
    <div id="rhoeDayScenery" class="fixed inset-0 z-0 overflow-hidden" aria-hidden="true">
        <div class="absolute -top-10 right-6 sm:right-20 w-44 h-44 sm:w-64 sm:h-64 rounded-full bg-amber-300/35 blur-3xl"></div>
        <div class="absolute top-16 right-8 sm:top-20 sm:right-24 w-14 h-14 sm:w-20 sm:h-20 rounded-full bg-gradient-to-br from-yellow-200 via-amber-300 to-orange-300 shadow-[0_0_55px_rgba(251,191,36,0.75)]"></div>

        <svg class="rhoe-cloud-1 absolute top-20 left-[6%] w-32 sm:w-48 text-white/85 drop-shadow-sm" viewBox="0 0 200 70" fill="currentColor">
            <path d="M35 58h125a22 22 0 0 0 4-43.6A32 32 0 0 0 104 8a28 28 0 0 0-50 13.5A20 20 0 0 0 35 58Z" />
        </svg>
        <svg class="rhoe-cloud-2 absolute top-32 right-[14%] w-24 sm:w-40 text-white/75" viewBox="0 0 200 70" fill="currentColor">
            <path d="M35 58h125a22 22 0 0 0 4-43.6A32 32 0 0 0 104 8a28 28 0 0 0-50 13.5A20 20 0 0 0 35 58Z" />
        </svg>
        <svg class="rhoe-cloud-3 absolute top-44 left-[22%] w-24 sm:w-36 text-white/60 hidden sm:block" viewBox="0 0 200 70" fill="currentColor">
            <path d="M35 58h125a22 22 0 0 0 4-43.6A32 32 0 0 0 104 8a28 28 0 0 0-50 13.5A20 20 0 0 0 35 58Z" />
        </svg>

        <svg class="absolute bottom-0 inset-x-0 w-full h-44 sm:h-64 md:h-72" viewBox="0 0 1440 320" preserveAspectRatio="none">
            <path fill="#7dd3fc" fill-opacity="0.45"
                d="M0,224L80,192C160,160,320,96,480,106.7C640,117,800,203,960,208C1120,213,1280,139,1360,101.3L1440,64L1440,320L0,320Z" />
            <path fill="#34d399" fill-opacity="0.45"
                d="M0,256L96,213.3C192,171,384,85,576,106.7C768,128,960,256,1152,261.3C1344,267,1392,181,1440,149.3L1440,320L0,320Z" />
            <path fill="#059669" fill-opacity="0.35"
                d="M0,288L120,261.3C240,235,480,181,720,197.3C960,213,1200,299,1320,288L1440,277.3L1440,320L0,320Z" />
        </svg>
    </div>

    <!-- Top Floating Navbar (Rhoe Minimalist Bar) -->
    <header class="sticky top-0 z-30 border-b border-white/10 rhoe-surface bg-zinc-950/75 backdrop-blur-xl">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-14 sm:h-16 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('images/kabupaten-sijunjung.png') }}" alt="Sijunjung" class="w-7 h-7 sm:w-8 sm:h-8 object-contain">
                <div class="leading-tight">
                    <div class="text-xs sm:text-sm font-extrabold tracking-tight">PORTAL PARPORA</div>
                    <div class="text-[10px] rhoe-muted text-zinc-400 hidden sm:block">{{ $pengaturan->nama_dinas ?? 'Dinas Pariwisata, Pemuda dan Olahraga' }}</div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('panduan') }}"
                    class="px-3 py-1.5 rounded-full text-[11px] sm:text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white transition-colors inline-flex items-center gap-1.5 shadow-sm">
                    <i class="fa-solid fa-book-open text-[10px]"></i>
                    <span>Panduan</span>
                </a>
                <a href="{{ url('/admin') }}"
                    class="px-3 py-1.5 rounded-full text-[11px] sm:text-xs font-semibold rhoe-surface bg-white/10 hover:bg-white/15 border border-white/10 transition-colors inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-right-to-bracket text-[10px]"></i>
                    <span>Masuk</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="relative z-10 w-full max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-14 flex-1">
        <!-- Hero Section -->
        <section class="text-center max-w-3xl mx-auto mb-8 sm:mb-12">
            <!-- Logos -->
            <div class="flex items-center justify-center gap-4 mb-5">
                <img src="{{ asset('images/kabupaten-sijunjung.png') }}" alt="Kabupaten Sijunjung" class="w-11 h-11 sm:w-14 sm:h-14 object-contain drop-shadow">
                <img src="{{ asset('images/geopark-silokek.png') }}" alt="Geopark Silokek" class="w-11 h-11 sm:w-14 sm:h-14 object-contain drop-shadow">
            </div>

            <!-- Shimmer Pill Badge -->
            <div class="rhoe-hero-badge inline-flex items-center gap-2 px-3.5 py-1 rounded-full shimmer-border border border-sky-400/30 text-[11px] font-bold text-sky-400 mb-4">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Pemerintah Kabupaten Sijunjung &bull; Tahun Anggaran {{ \App\Models\PengaturanAplikasi::getTahunAnggaranAktif() }}</span>
            </div>

            <h1 class="rhoe-heading text-4xl sm:text-6xl font-black tracking-tight leading-[1.08] bg-gradient-to-b from-white via-zinc-100 to-zinc-400 bg-clip-text text-transparent mb-3">
                PORTAL DIGITAL PARPORA
            </h1>
            <p class="rhoe-muted text-sm sm:text-base text-zinc-400 max-w-xl mx-auto leading-relaxed mb-6">
                Portal Layanan Digital Disparpora Sijunjung — Satu Portal untuk Semua Layanan Kepegawaian, Kinerja, SPJ, Aset, dan Informasi Publik.
            </p>

            <!-- Search Input (Rhoe Command Style) -->
            <div class="max-w-md mx-auto relative mb-5">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-zinc-400 text-xs"></i>
                <input type="text" x-model="searchQuery" placeholder="Cari layanan SAKTI, Presensi, Geopark..."
                    class="rhoe-surface w-full rounded-2xl bg-zinc-900/80 border border-white/10 pl-10 pr-4 py-2.5 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/60 transition-all">
            </div>

            <!-- Category Filter Pills -->
            @if ($categories->count() > 1)
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <button type="button" @click="activeCategory = 'all'"
                        :class="activeCategory === 'all' ? 'bg-sky-500 text-zinc-950 font-bold border-sky-400' : 'rhoe-surface bg-zinc-900/70 text-zinc-400 border-white/10 hover:border-sky-400/40'"
                        class="px-3.5 py-1.5 rounded-full text-xs border transition-all cursor-pointer">
                        Semua ({{ $portalLinksCollection->count() }})
                    </button>
                    @foreach ($categories as $cat)
                        @php
                            $catName = match ($cat) {
                                'kepegawaian' => 'Kepegawaian & Presensi',
                                'kinerja', 'layanan_utama' => 'Kinerja & Mobile',
                                'administrasi' => 'Panel Admin',
                                'panduan', 'dokumen_lampiran' => 'Dokumen & Regulasi',
                                'eksternal' => 'Layanan Eksternal',
                                default => ucfirst(str_replace('_', ' ', $cat)),
                            };
                        @endphp
                        <button type="button" @click="activeCategory = '{{ $cat }}'"
                            :class="activeCategory === '{{ $cat }}' ? 'bg-sky-500 text-zinc-950 font-bold border-sky-400' : 'rhoe-surface bg-zinc-900/70 text-zinc-400 border-white/10 hover:border-sky-400/40'"
                            class="px-3.5 py-1.5 rounded-full text-xs border transition-all cursor-pointer">
                            {{ $catName }}
                        </button>
                    @endforeach
                    <a href="{{ route('panduan') }}"
                        class="px-3.5 py-1.5 rounded-full text-xs font-extrabold border transition-all inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-500 text-white border-emerald-500 shadow-sm shadow-emerald-600/25">
                        <i class="fa-solid fa-book-open text-[11px]"></i>
                        <span>Panduan Pengguna</span>
                    </a>
                </div>
            @endif
        </section>

        <!-- Bento Spotlight Card Grid (2 Kolom di Mobile, 3 Kolom di Desktop) -->
        <section class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-5">
            @foreach ($portalLinksCollection as $link)
                @php $tokens = $link->style_tokens; @endphp
                <a href="{{ $link->link_url }}" target="{{ $link->target ?? '_self' }}"
                    x-show="matches('{{ $link->kategori }}', '{{ addslashes($link->judul) }}', '{{ addslashes($link->deskripsi ?? '') }}')"
                    x-transition
                    onmousemove="const r = this.getBoundingClientRect(); this.style.setProperty('--mouse-x', (event.clientX - r.left) + 'px'); this.style.setProperty('--mouse-y', (event.clientY - r.top) + 'px');"
                    class="spotlight-card rhoe-surface group rounded-2xl sm:rounded-3xl bg-zinc-900/70 border border-white/10 hover:border-sky-400/50 p-4 sm:p-6 flex flex-col justify-between transition-all duration-300 hover:-translate-y-1 shadow-sm hover:shadow-xl">
                    <div>
                        <div class="flex items-start justify-between gap-2 mb-3 sm:mb-4">
                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl {{ $tokens['icon_box'] }} flex items-center justify-center text-base sm:text-xl shadow-md group-hover:scale-105 transition-transform">
                                <i class="{{ $link->fa_icon }}"></i>
                            </div>
                            @if ($link->badge)
                                <span class="rhoe-card-badge px-2.5 py-0.5 rounded-full text-[9px] sm:text-[10px] font-bold uppercase tracking-wider {{ $tokens['badge'] }} truncate max-w-[95px] sm:max-w-none">
                                    {{ $link->badge }}
                                </span>
                            @endif
                        </div>
                        <h3 class="text-xs sm:text-base font-extrabold tracking-tight leading-snug mb-1.5 line-clamp-2 group-hover:text-sky-400 transition-colors">
                            {{ $link->judul }}
                        </h3>
                        <p class="rhoe-muted text-[11px] sm:text-xs text-zinc-400 leading-relaxed line-clamp-2 sm:line-clamp-3 mb-4">
                            {{ $link->deskripsi }}
                        </p>
                    </div>

                    <div class="pt-2 border-t border-white/5 flex items-center justify-between text-[11px] sm:text-xs font-bold text-sky-400">
                        <span>Buka Layanan</span>
                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px] transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5"></i>
                    </div>
                </a>
            @endforeach
        </section>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-white/10 rhoe-surface bg-zinc-950/70 py-5 px-4 text-center text-xs">
        <p class="font-bold">&copy; {{ date('Y') }} {{ $pengaturan->nama_dinas ?? 'Dinas Pariwisata, Pemuda dan Olahraga Kabupaten Sijunjung' }}</p>
        @if (! empty($pengaturan->alamat_kantor))
            <p class="rhoe-muted text-[11px] text-zinc-400 mt-0.5">{{ $pengaturan->alamat_kantor }}</p>
        @endif
        <p class="rhoe-muted text-[10px] text-zinc-500 mt-1">
            {{ $pengaturan->versi_aplikasi ?? 'v1.0.0' }}
            @if (! empty($pengaturan->pembuat_aplikasi)) &bull; {{ $pengaturan->pembuat_aplikasi }} @endif
        </p>
    </footer>

    <!-- Floating Scroll Up/Down + Theme Toggle -->
    <div x-data="{
            scrolled: false,
            checkScroll() { this.scrolled = window.scrollY > 180; },
            scrollAction() {
                window.scrollTo({ top: this.scrolled ? 0 : document.documentElement.scrollHeight, behavior: 'smooth' });
            }
        }"
        x-init="checkScroll()"
        @scroll.window="checkScroll()"
        class="fixed bottom-5 right-4 z-50 flex items-center gap-2 opacity-45 hover:opacity-100 focus-within:opacity-100 transition-opacity duration-300">
        <button type="button" @click="scrollAction()"
            :title="scrolled ? 'Kembali ke Atas' : 'Gulir ke Bawah'"
            class="w-9 h-9 md:w-10 md:h-10 rounded-full rhoe-surface bg-zinc-900/70 border border-white/15 shadow-md backdrop-blur-md flex items-center justify-center cursor-pointer active:scale-90 transition-transform">
            <i x-show="!scrolled" class="fa-solid fa-arrow-down text-sky-400 text-xs md:text-sm animate-bounce"></i>
            <i x-show="scrolled" x-cloak class="fa-solid fa-arrow-up text-sky-400 text-xs md:text-sm animate-bounce"></i>
        </button>

        <button type="button" onclick="window.toggleRhoeTheme()"
            class="w-9 h-9 md:w-10 md:h-10 rounded-full rhoe-surface bg-zinc-900/70 border border-white/15 shadow-md backdrop-blur-md flex items-center justify-center cursor-pointer active:scale-90 transition-transform"
            title="Ganti Mode Terang / Gelap">
            <i x-show="!isLight" class="fa-solid fa-sun text-amber-400 text-sm"></i>
            <i x-show="isLight" x-cloak class="fa-solid fa-moon text-zinc-700 text-sm"></i>
        </button>
    </div>
</body>
</html>
