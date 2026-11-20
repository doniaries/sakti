<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ $title ?? 'Absensi LHK Mobile' }}</title>
    
    <!-- PWA Setup -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=2">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#1B6CA8">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="SAKTI Mobile">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    
    <!-- Fonts & CSS -->
    @if (app()->environment('local') && file_exists(public_path('hot')))
        @vite(['resources/js/app.js'])
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' };
        (function () {
            const apply = () => {
                const saved = localStorage.getItem('sakti_mobile_theme') || 'light';
                document.documentElement.classList.toggle('dark', saved === 'dark');
            };
            apply();
            document.addEventListener('livewire:navigated', apply);
        })();
        window.toggleMobileTheme = function () {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('sakti_mobile_theme', isDark ? 'dark' : 'light');
            window.dispatchEvent(new CustomEvent('mobile-theme-changed', { detail: { dark: isDark } }));
        };
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Leaflet Map CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/sw.js').catch(function () {});
            });
        }
    </script>
    
    <style>
        * {
            -webkit-tap-highlight-color: transparent;
        }
        body { 
            background-color: #E2E8F0; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            transition: background-color 0.2s ease;
        }
        html.dark body {
            background-color: #060C1A;
        }
        .mobile-container {
            max-width: 440px;
            margin: 0 auto;
            background-color: #F8FAFC;
            background-image:
                radial-gradient(circle at 90% 6%, rgba(14, 165, 233, 0.14) 0%, transparent 42%),
                radial-gradient(circle at 10% 32%, rgba(16, 185, 129, 0.10) 0%, transparent 45%),
                
                
                linear-gradient(160deg, #F8FAFC 0%, #EFF6FF 48%, #ECFDF5 100%);
            background-size: 100% 100%, 100% 100%, 28px 28px, 28px 28px, 100% 100%;
            color: #0F172A;
            min-height: 100vh;
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
            display: flex;
            flex-direction: column;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .mobile-container::before {
            content: '';
            position: sticky;
            top: 0;
            display: block;
            height: 3px;
            width: 100%;
            z-index: 60;
            background: linear-gradient(90deg, #0284C7 0%, #22D3EE 50%, #10B981 100%);
        }
        html.dark .mobile-container {
            background-color: #0B132B;
            background-image:
                radial-gradient(circle at 88% 7%, rgba(34, 211, 238, 0.16) 0%, transparent 40%),
                radial-gradient(circle at 10% 34%, rgba(16, 185, 129, 0.12) 0%, transparent 44%),
                
                
                linear-gradient(160deg, #0B132B 0%, #0F1E3C 52%, #081A2C 100%);
            background-size: 100% 100%, 100% 100%, 28px 28px, 28px 28px, 100% 100%;
            color: #F1F5F9;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.65);
        }
        html.dark .mobile-container::before {
            background: linear-gradient(90deg, #22D3EE 0%, #38BDF8 50%, #34D399 100%);
            box-shadow: 0 0 12px rgba(34, 211, 238, 0.5);
        }
        .content-area { 
            flex: 1; 
            padding-bottom: 110px; 
            overflow-y: auto; 
            -webkit-overflow-scrolling: touch;
        }
        
        /* Sticky Bottom Navigation Bar (Rata Bawah Tanpa Celah) */
        .bottom-nav-wrapper {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
            display: flex;
            justify-content: center;
            pointer-events: none;
            padding: 0;
            margin: 0;
        }
        .bottom-nav {
            width: 100%;
            max-width: 440px;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-top: 1px solid rgba(226, 232, 240, 0.9);
            border-left: 1px solid rgba(226, 232, 240, 0.5);
            border-right: 1px solid rgba(226, 232, 240, 0.5);
            border-bottom: none;
            border-radius: 24px 24px 0 0;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
            align-items: center;
            height: 68px;
            padding: 0 8px env(safe-area-inset-bottom, 0px) 8px;
            box-shadow: 0 -10px 25px -5px rgba(15, 23, 42, 0.08), 0 -1px 3px 0 rgba(0, 0, 0, 0.03);
            pointer-events: auto;
        }
        html.dark .bottom-nav {
            background: rgba(16, 30, 48, 0.96);
            border-top-color: rgba(51, 65, 85, 0.7);
            border-left-color: rgba(51, 65, 85, 0.4);
            border-right-color: rgba(51, 65, 85, 0.4);
            box-shadow: 0 -10px 25px -5px rgba(0, 0, 0, 0.35);
        }
        .nav-item {
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center;
            color: #64748B; 
            font-size: 10px; 
            font-weight: 600;
            text-decoration: none;
            transition: all 0.25s ease;
            position: relative;
            height: 100%;
        }
        html.dark .nav-item {
            color: #94A3B8;
        }
        .nav-item i { 
            font-size: 19px; 
            margin-bottom: 3px;
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .nav-item.active { 
            color: #0284C7; 
            font-weight: 700;
        }
        html.dark .nav-item.active {
            color: #38BDF8;
        }
        .nav-item.active i { 
            transform: translateY(-2px);
            color: #0284C7; 
        }
        html.dark .nav-item.active i {
            color: #38BDF8;
        }
        .nav-item.active::after {
            content: '';
            position: absolute;
            bottom: 8px;
            width: 5px;
            height: 5px;
            background-color: #0284C7;
            border-radius: 50%;
        }
        html.dark .nav-item.active::after {
            background-color: #38BDF8;
        }

        /* Center Elevated Punch Button */
        .center-absen-btn-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }
        .center-absen-btn {
            position: absolute;
            top: -24px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            color: #FFFFFF;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px -3px rgba(245, 158, 11, 0.45), 0 0 0 5px #F8FAFC;
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            text-decoration: none;
        }
        html.dark .center-absen-btn {
            background: linear-gradient(135deg, #22D3EE 0%, #0284C7 100%);
            color: #061326;
            box-shadow: 0 10px 25px -3px rgba(34, 211, 238, 0.45), 0 0 0 5px #0B132B;
        }
        .center-absen-btn:active {
            transform: scale(0.92) translateY(2px);
        }
        .center-absen-btn i {
            font-size: 23px;
            margin-bottom: 0px;
        }
        .center-absen-btn span {
            font-size: 8.5px;
            font-weight: 800;
            letter-spacing: 0.3px;
            margin-top: 1px;
        }

        /* Unified Dark Navy Theme untuk semua halaman Mobile saat html.dark aktif */
        html.dark .content-area .bg-white {
            background-color: rgba(19, 35, 56, 0.92) !important;
            backdrop-filter: blur(12px);
        }
        html.dark .content-area .bg-slate-50,
        html.dark .content-area .bg-slate-50\/80,
        html.dark .content-area .bg-gray-50,
        html.dark .content-area .bg-sky-50,
        html.dark .content-area .bg-sky-50\/80,
        html.dark .content-area .bg-blue-50,
        html.dark .content-area [class*="from-sky-50"],
        html.dark .content-area [class*="from-blue-50"],
        html.dark .content-area [class*="from-slate-50"] {
            background: rgba(15, 28, 46, 0.88) !important;
        }
        html.dark .content-area .bg-slate-100,
        html.dark .content-area .bg-gray-100,
        html.dark .content-area .bg-slate-200,
        html.dark .content-area .bg-slate-200\/80,
        html.dark .content-area .bg-slate-200\/70,
        html.dark .content-area .bg-gray-200 {
            background-color: rgba(30, 41, 59, 0.88) !important;
        }
        html.dark .content-area .border-slate-100,
        html.dark .content-area .border-slate-200,
        html.dark .content-area .border-slate-300,
        html.dark .content-area .border-gray-100,
        html.dark .content-area .border-gray-200,
        html.dark .content-area .border-sky-100,
        html.dark .content-area .border-sky-200 {
            border-color: rgba(51, 65, 85, 0.65) !important;
        }
        html.dark .content-area .text-sky-700,
        html.dark .content-area .text-sky-600,
        html.dark .content-area .text-blue-700,
        html.dark .content-area .text-blue-600 {
            color: #38BDF8 !important;
        }
        html.dark .content-area .text-slate-900,
        html.dark .content-area .text-slate-800,
        html.dark .content-area .text-gray-900,
        html.dark .content-area .text-gray-800 {
            color: #F8FAFC !important;
        }
        html.dark .content-area .text-slate-700,
        html.dark .content-area .text-gray-700 {
            color: #E2E8F0 !important;
        }
        html.dark .content-area .text-slate-600,
        html.dark .content-area .text-gray-600 {
            color: #CBD5E1 !important;
        }
        html.dark .content-area .text-slate-500,
        html.dark .content-area .text-gray-500 {
            color: #94A3B8 !important;
        }
        html.dark .content-area input:not([type="checkbox"]):not([type="radio"]):not([type="file"]),
        html.dark .content-area select,
        html.dark .content-area textarea {
            background-color: #0F1E33 !important;
            border-color: rgba(71, 85, 105, 0.7) !important;
            color: #F1F5F9 !important;
        }
        html.dark .content-area input::placeholder,
        html.dark .content-area textarea::placeholder {
            color: #64748B !important;
        }
    </style>
    @livewireStyles
</head>
<body>
    <div class="mobile-container">
        @php
            $pengaturanMobile = \App\Models\PengaturanAplikasi::getPengaturan();
            $isSimulasiAktif = (bool) ($pengaturanMobile->mode_simulasi || session('mode_simulasi_absensi', false));
            $isRhoeMobile = request()->query('tema', $pengaturanMobile->portal_theme ?? 'default') === 'rhoe';
        @endphp
        @if ($isSimulasiAktif)
            <!-- Sticky Warning Mode Simulasi Aktif untuk Seluruh Pegawai -->
            <div class="sticky top-0 z-50 bg-gradient-to-r from-amber-500 to-orange-500 text-white px-3 py-1.5 shadow-md flex items-center justify-between text-[11px] font-bold border-b border-amber-600/40">
                <div class="flex items-center space-x-1.5 min-w-0">
                    <span class="w-2 h-2 rounded-full bg-white animate-ping shrink-0"></span>
                    <span class="truncate">⚠️ MODE SIMULASI AKTIF: Presensi bebas lokasi untuk pengujian</span>
                </div>
                @if (auth()->check() && auth()->user()->hasAnyRole(['super_admin', 'admin']))
                    <a href="{{ route('mobile.pengaturan-lokasi') }}" wire:navigate class="px-2 py-0.5 rounded bg-black/20 hover:bg-black/30 text-[10px] uppercase font-extrabold shrink-0 ml-2">
                        Atur
                    </a>
                @endif
            </div>
        @endif


        <!-- Global Navigation Skeleton Overlay (Muncul otomatis saat pindah menu wire:navigate > 120ms) -->
        <div x-data="{
                navigating: false,
                target: 'dashboard',
                timer: null,
                startNav(e) {
                    const url = e?.detail?.url?.pathname || '';
                    this.target = url.includes('/absensi') ? 'absensi'
                        : (url.includes('/riwayat') ? 'riwayat'
                        : (url.includes('/izin') ? 'izin'
                        : (url.includes('/profil') ? 'profil' : 'dashboard')));
                    clearTimeout(this.timer);
                    this.timer = setTimeout(() => { this.navigating = true; }, 120);
                },
                endNav() {
                    clearTimeout(this.timer);
                    this.navigating = false;
                }
            }"
            x-init="
                document.addEventListener('livewire:navigate', (e) => startNav(e));
                document.addEventListener('livewire:navigated', () => endNav());
            "
            x-show="navigating"
            x-cloak
            x-transition.opacity.duration.150ms
            class="fixed inset-0 z-40 max-w-[440px] mx-auto bg-slate-50 dark:bg-[#09090b] pb-24 space-y-4 pointer-events-none">
            <!-- 1. Skeleton Beranda -->
            <template x-if="target === 'dashboard'">
                <div class="space-y-4 animate-pulse">
                    <div class="rounded-b-[28px] bg-slate-200 dark:bg-zinc-800 px-5 pt-5 pb-12 space-y-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-300 dark:bg-zinc-700 shrink-0"></div>
                                <div class="space-y-1.5">
                                    <div class="h-2.5 w-20 rounded-full bg-slate-300 dark:bg-zinc-700"></div>
                                    <div class="h-3.5 w-36 rounded-full bg-slate-300 dark:bg-zinc-700"></div>
                                </div>
                            </div>
                            <div class="w-9 h-9 rounded-full bg-slate-300 dark:bg-zinc-700"></div>
                        </div>
                        <div class="flex items-end justify-between">
                            <div class="h-7 w-32 rounded-full bg-slate-300 dark:bg-zinc-700"></div>
                            <div class="h-8 w-24 rounded-xl bg-slate-300 dark:bg-zinc-700"></div>
                        </div>
                    </div>
                    <div class="-mt-8 mx-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-3.5">
                        <div class="grid grid-cols-4 gap-3">
                            @for ($i = 0; $i < 8; $i++)
                                <div class="flex flex-col items-center gap-1.5">
                                    <div class="w-11 h-11 rounded-2xl bg-slate-100 dark:bg-zinc-800"></div>
                                    <div class="h-2 w-10 rounded-full bg-slate-200 dark:bg-zinc-800"></div>
                                </div>
                            @endfor
                        </div>
                    </div>
                    <div class="px-4 grid grid-cols-2 gap-3">
                        <div class="h-24 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-3.5"></div>
                        <div class="h-24 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-3.5"></div>
                    </div>
                </div>
            </template>

            <!-- 2. Skeleton Presensi -->
            <template x-if="target === 'absensi'">
                <div class="space-y-4 animate-pulse">
                    <div class="rounded-b-[28px] bg-slate-200 dark:bg-zinc-800 px-5 pt-5 pb-12 flex items-center justify-between">
                        <div class="h-5 w-40 rounded-full bg-slate-300 dark:bg-zinc-700"></div>
                        <div class="h-9 w-20 rounded-xl bg-slate-300 dark:bg-zinc-700"></div>
                    </div>
                    <div class="-mt-8 mx-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-4 space-y-3">
                        <div class="h-7 w-32 rounded-full bg-slate-200 dark:bg-zinc-800"></div>
                        <div class="grid grid-cols-2 gap-2.5">
                            <div class="h-14 rounded-2xl bg-slate-100 dark:bg-zinc-800"></div>
                            <div class="h-14 rounded-2xl bg-slate-100 dark:bg-zinc-800"></div>
                        </div>
                    </div>
                    <div class="mx-4 h-56 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800"></div>
                </div>
            </template>

            <!-- 3. Skeleton Riwayat -->
            <template x-if="target === 'riwayat'">
                <div class="space-y-4 animate-pulse">
                    <div class="rounded-b-[28px] bg-slate-200 dark:bg-zinc-800 px-5 pt-5 pb-12 flex items-center justify-between">
                        <div class="h-5 w-36 rounded-full bg-slate-300 dark:bg-zinc-700"></div>
                        <div class="h-8 w-24 rounded-xl bg-slate-300 dark:bg-zinc-700"></div>
                    </div>
                    <div class="-mt-8 mx-4 h-12 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800"></div>
                    <div class="mx-4 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800 p-4 grid grid-cols-3 gap-2">
                        @for ($i = 0; $i < 6; $i++)
                            <div class="h-12 rounded-xl bg-slate-100 dark:bg-zinc-800"></div>
                        @endfor
                    </div>
                    <div class="mx-4 space-y-2.5">
                        <div class="h-20 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800"></div>
                        <div class="h-20 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800"></div>
                    </div>
                </div>
            </template>

            <!-- 4. Skeleton Izin & Profil -->
            <template x-if="target === 'izin' || target === 'profil'">
                <div class="space-y-4 animate-pulse">
                    <div class="rounded-b-[28px] bg-slate-200 dark:bg-zinc-800 px-5 pt-5 pb-10 flex items-center justify-between">
                        <div class="h-5 w-40 rounded-full bg-slate-300 dark:bg-zinc-700"></div>
                        <div class="h-8 w-24 rounded-xl bg-slate-300 dark:bg-zinc-700"></div>
                    </div>
                    <div class="mx-4 h-12 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800"></div>
                    <div class="mx-4 space-y-3">
                        <div class="h-24 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800"></div>
                        <div class="h-24 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800"></div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Content -->
        <div class="content-area {{ !auth()->check() ? '!pb-0 h-screen overflow-hidden' : '' }}">
            {{ $slot }}
        </div>

        <!-- Sticky Floating Bottom Navigation -->
        @auth
        <div class="bottom-nav-wrapper">
            <nav class="bottom-nav">
                <!-- 1. Beranda (Halaman Awal) -->
                <a href="{{ route('mobile.dashboard') }}" class="nav-item {{ request()->routeIs('mobile.dashboard') ? 'active' : '' }}" wire:navigate>
                    <i class="fa-solid fa-house"></i>
                    <span>Beranda</span>
                </a>

                <!-- 2. Riwayat & LHK -->
                <a href="{{ route('mobile.riwayat') }}" class="nav-item {{ request()->routeIs('mobile.riwayat') || request()->routeIs('mobile.lhk.*') ? 'active' : '' }}" wire:navigate>
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>Riwayat</span>
                </a>

                <!-- 3. TOMBOL NAVIGASI PRESENSI TENGAH -->
                <div class="center-absen-btn-wrapper">
                    <a href="{{ route('mobile.absensi') }}" 
                       wire:navigate
                       class="center-absen-btn group ring-4 ring-amber-200 transition-all duration-300 active:scale-95 cursor-pointer {{ request()->routeIs('mobile.absensi') ? 'ring-amber-400 shadow-amber-500/30' : '' }}"
                       title="Halaman Presensi Kehadiran">
                        <i class="fa-solid fa-fingerprint"></i>
                        <span>PRESENSI</span>
                    </a>
                </div>

                <!-- 4. Izin & Cuti -->
                <a href="{{ route('mobile.izin.index') }}" class="nav-item {{ request()->routeIs('mobile.izin.*') ? 'active' : '' }}" wire:navigate>
                    <i class="fa-solid fa-envelope-open-text"></i>
                    <span>Izin</span>
                </a>

                <!-- 5. Profil (Halaman sama dengan data pegawai Filament & bisa diedit) -->
                <a href="{{ route('mobile.profil') }}" class="nav-item {{ request()->routeIs('mobile.profil') ? 'active' : '' }}" wire:navigate>
                    <i class="fa-solid fa-circle-user"></i>
                    <span>Profil</span>
                </a>
            </nav>
        </div>
        @endauth
    </div>

    <!-- DRY Custom Tailwind Popup Modal (Menggantikan SweetAlert Default) -->
    <div x-data="{ 
            open: false, 
            type: 'error', 
            title: 'Peringatan!', 
            message: '', 
            buttonText: 'Mengerti',
            showUser: false,
            show(detail) {
                this.type = detail.type || 'info';
                this.title = detail.title || (this.type === 'error' ? 'Peringatan!' : (this.type === 'success' ? 'Berhasil!' : 'Informasi'));
                this.message = detail.message || '';
                this.buttonText = detail.buttonText || 'Mengerti';
                this.showUser = Boolean(detail.show_user || false);
                this.open = true;
            },
            close() {
                this.open = false;
            }
         }" 
         x-on:notify.window="show(Array.isArray($event.detail) ? $event.detail[0] : $event.detail)"
         @if(session()->has('notify'))
         x-init="show({{ json_encode(session('notify')) }})"
         @endif
         x-cloak>
        
        <!-- Backdrop Blur -->
        <div x-show="open" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100]"
             @click="close()"></div>

        <!-- Popup Box -->
        <div x-show="open" 
             x-transition:enter="transition cubic-bezier(0.34, 1.56, 0.64, 1) duration-300"
             x-transition:enter-start="opacity-0 scale-75 translate-y-6"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-75 translate-y-6"
             class="fixed inset-0 z-[101] flex items-center justify-center p-5 pointer-events-none">
            
            <div class="w-full max-w-sm bg-white rounded-3xl p-6 shadow-2xl border border-slate-100 text-center pointer-events-auto transform transition-all relative overflow-hidden">
                <!-- Ambient Top Glow -->
                <div class="absolute -top-12 -right-12 w-32 h-32 rounded-full blur-2xl pointer-events-none"
                     :class="{
                        'bg-rose-400/20': type === 'error',
                        'bg-emerald-400/20': type === 'success',
                        'bg-amber-400/20': type === 'warning',
                        'bg-sky-400/20': type === 'info'
                     }"></div>

                <!-- Animated Icon Ring -->
                <div class="relative mx-auto w-16 h-16 mb-4 flex items-center justify-center">
                    <template x-if="type === 'success'">
                        <span class="absolute inset-0 rounded-full bg-emerald-400/35 animate-ping"></span>
                    </template>
                    <div class="relative w-16 h-16 rounded-full flex items-center justify-center text-2xl shadow-lg transition-transform duration-300"
                         :class="{
                            'bg-rose-50 text-rose-500 ring-8 ring-rose-500/10 shadow-rose-500/20': type === 'error',
                            'bg-gradient-to-br from-emerald-500 to-teal-600 text-white ring-8 ring-emerald-500/20 shadow-emerald-500/30 animate-bounce': type === 'success',
                            'bg-amber-50 text-amber-500 ring-8 ring-amber-500/10 shadow-amber-500/20': type === 'warning',
                            'bg-sky-50 text-sky-500 ring-8 ring-sky-500/10 shadow-sky-500/20': type === 'info'
                         }">
                        <template x-if="type === 'error'">
                            <i class="fa-solid fa-xmark"></i>
                        </template>
                        <template x-if="type === 'success'">
                            <i class="fa-solid fa-check"></i>
                        </template>
                        <template x-if="type === 'warning'">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </template>
                        <template x-if="type === 'info'">
                            <i class="fa-solid fa-info"></i>
                        </template>
                    </div>
                </div>

                <!-- Title & Message -->
                <h3 class="text-lg font-extrabold text-slate-800 tracking-tight" x-text="title">Peringatan!</h3>

                @auth
                <div x-show="showUser || type === 'success'" class="mt-3 flex flex-col items-center gap-1.5">
                    <img src="{{ auth()->user()->avatar_image_url }}"
                         alt="{{ auth()->user()->name }}"
                         class="w-16 h-16 rounded-full object-cover ring-4 ring-emerald-200 shadow-md">
                    <span class="px-3 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-bold">
                        {{ auth()->user()->jabatan?->nama ?? 'Pegawai' }}
                    </span>
                </div>
                @endauth

                <p class="text-sm text-slate-500 mt-3 leading-relaxed font-medium" x-text="message"></p>
                @auth
                <p x-show="showUser || type === 'success'" class="text-sm font-black text-slate-950 mt-1.5">{{ auth()->user()->name }}</p>
                @endauth

                <!-- Action Button -->
                <div class="mt-6">
                    <button @click="close()" 
                            class="w-full py-3 px-5 rounded-2xl font-bold text-sm text-white shadow-lg active:scale-95 transition-all"
                            :class="{
                                'bg-rose-600 hover:bg-rose-700 shadow-rose-600/30': type === 'error',
                                'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/30': type === 'success',
                                'bg-amber-500 hover:bg-amber-600 shadow-amber-500/30': type === 'warning',
                                'bg-sky-600 hover:bg-sky-700 shadow-sky-600/30': type === 'info'
                            }">
                        <span x-text="buttonText">Mengerti</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>

