<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ $title ?? 'Absensi LHK Mobile' }}</title>
    
    <!-- PWA Setup -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1B6CA8">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    
    <!-- Fonts & CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        * {
            -webkit-tap-highlight-color: transparent;
        }
        body { 
            background-color: #0F172A; 
            font-family: 'Plus Jakarta Sans', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .mobile-container {
            max-width: 440px;
            margin: 0 auto;
            background: #F8FAFC;
            min-height: 100vh;
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            display: flex;
            flex-direction: column;
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
            background: rgba(255, 255, 255, 0.98);
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
        .nav-item {
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center;
            color: #94A3B8; 
            font-size: 10px; 
            font-weight: 600;
            text-decoration: none;
            transition: all 0.25s ease;
            position: relative;
            height: 100%;
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
        .nav-item.active i { 
            transform: translateY(-2px);
            color: #0284C7; 
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
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 20px -3px rgba(2, 132, 199, 0.45), 0 0 0 5px #F8FAFC;
            transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
            text-decoration: none;
        }
        .center-absen-btn:active {
            transform: scale(0.92) translateY(2px);
            box-shadow: 0 4px 10px -2px rgba(2, 132, 199, 0.4), 0 0 0 4px #F8FAFC;
        }
        .center-absen-btn i {
            font-size: 24px;
            margin-bottom: 0px;
        }
        .center-absen-btn span {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.3px;
            margin-top: 2px;
        }
    </style>
    @livewireStyles
</head>
<body>
    <div class="mobile-container">
        <!-- Content -->
        <div class="content-area">
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

                <!-- 3. TOMBOL ABSEN TENGAH (Selalu Muncul & Sticky) -->
                <div class="center-absen-btn-wrapper">
                    <a href="{{ route('mobile.absensi') }}" class="center-absen-btn group {{ request()->routeIs('mobile.absensi') ? 'ring-4 ring-sky-300' : '' }}" wire:navigate>
                        <i class="fa-solid fa-fingerprint"></i>
                        <span>ABSEN</span>
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
    <script>
        // PWA Service Worker Registration
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').then(function(registration) {
                    console.log('ServiceWorker registration successful with scope: ', registration.scope);
                }, function(err) {
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
        }
    </script>
</body>
</html>

