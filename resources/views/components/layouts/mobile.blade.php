<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ $title ?? 'Absensi LHK Mobile' }}</title>
    
    <!-- PWA Setup -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0B132B">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    
    <!-- Fonts & CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' };
        (function () {
            const apply = () => {
                const saved = localStorage.getItem('sakti_mobile_theme') || 'dark';
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
    
    <!-- Leaflet Map CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // Cache Buster: Unregister stale service workers and purge cache storage
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for (let r of registrations) {
                    r.unregister();
                }
            });
        }
        if ('caches' in window) {
            caches.keys().then(function(names) {
                for (let name of names) {
                    caches.delete(name);
                }
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
                linear-gradient(to right, rgba(2, 132, 199, 0.045) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(16, 185, 129, 0.04) 1px, transparent 1px),
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
                linear-gradient(to right, rgba(56, 189, 248, 0.055) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(16, 185, 129, 0.045) 1px, transparent 1px),
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
            background: linear-gradient(135deg, #0284C7 0%, #0369A1 100%);
            color: #FFFFFF;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px -3px rgba(2, 132, 199, 0.4), 0 0 0 5px #F8FAFC;
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
        html.dark .content-area .bg-gray-50 {
            background-color: rgba(15, 28, 46, 0.85) !important;
        }
        html.dark .content-area .bg-slate-100,
        html.dark .content-area .bg-gray-100 {
            background-color: rgba(30, 41, 59, 0.85) !important;
        }
        html.dark .content-area .border-slate-100,
        html.dark .content-area .border-slate-200,
        html.dark .content-area .border-gray-100,
        html.dark .content-area .border-gray-200 {
            border-color: rgba(51, 65, 85, 0.65) !important;
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
            $isSimulasiAktif = (bool) (\App\Models\PengaturanAplikasi::getPengaturan()->mode_simulasi || session('mode_simulasi_absensi', false));
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

        <!-- Content -->
        <div class="content-area {{ !auth()->check() ? '!pb-0 h-screen overflow-hidden' : '' }}">
            {{ $slot }}
        </div>

        <!-- Sticky Floating Bottom Navigation -->
        @auth
        <div class="bottom-nav-wrapper">
            <nav class="bottom-nav">
                <!-- 1. Dashboard -->
                <a href="{{ route('mobile.dashboard') }}" class="nav-item {{ request()->routeIs('mobile.dashboard') ? 'active' : '' }}" wire:navigate>
                    <i class="fa-solid fa-house"></i>
                    <span>Beranda</span>
                </a>

                <!-- 2. Riwayat -->
                <a href="{{ route('mobile.riwayat') }}" class="nav-item {{ request()->routeIs('mobile.riwayat') ? 'active' : '' }}" wire:navigate>
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>Riwayat</span>
                </a>

                <!-- 3. TOMBOL NAVIGASI PRESENSI TENGAH (Navigasi ke Halaman Presensi) -->
                <div class="center-absen-btn-wrapper">
                    <a href="{{ route('mobile.absensi') }}" 
                       wire:navigate
                       class="center-absen-btn group ring-4 ring-sky-300 transition-all duration-300 active:scale-95 cursor-pointer {{ request()->routeIs('mobile.absensi') ? 'ring-emerald-400 shadow-emerald-500/30' : '' }}"
                       title="Halaman Presensi Kehadiran">
                        <i class="fa-solid fa-fingerprint"></i>
                        <span>PRESENSI</span>
                    </a>
                </div>

                <!-- 4. LHK -->
                <a href="{{ route('mobile.lhk.index') }}" class="nav-item {{ request()->routeIs('mobile.lhk.*') ? 'active' : '' }}" wire:navigate>
                    <i class="fa-solid fa-file-pen"></i>
                    <span>LHK</span>
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
            show(detail) {
                this.type = detail.type || 'info';
                this.title = detail.title || (this.type === 'error' ? 'Peringatan!' : (this.type === 'success' ? 'Berhasil!' : 'Informasi'));
                this.message = detail.message || '';
                this.buttonText = detail.buttonText || 'Mengerti';
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
                <div class="mx-auto w-16 h-16 rounded-3xl flex items-center justify-center text-2xl mb-4 shadow-lg transition-transform duration-300"
                     :class="{
                        'bg-rose-50 text-rose-500 ring-8 ring-rose-500/10 shadow-rose-500/20': type === 'error',
                        'bg-emerald-50 text-emerald-500 ring-8 ring-emerald-500/10 shadow-emerald-500/20': type === 'success',
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

                <!-- Title & Message -->
                <h3 class="text-lg font-extrabold text-slate-800 tracking-tight" x-text="title">Peringatan!</h3>
                <p class="text-sm text-slate-500 mt-2 leading-relaxed font-medium" x-text="message"></p>

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

