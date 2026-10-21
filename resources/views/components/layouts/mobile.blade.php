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
        
        /* Sticky Floating Bottom Bar */
        .bottom-nav-wrapper {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
            display: flex;
            justify-content: center;
            pointer-events: none;
            padding: 0 16px 16px 16px;
        }
        .bottom-nav {
            width: 100%;
            max-width: 440px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 28px;
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
            align-items: center;
            height: 70px;
            padding: 0 10px;
            box-shadow: 0 20px 30px -10px rgba(15, 23, 42, 0.15), 0 0 1px 1px rgba(0, 0, 0, 0.04);
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
            margin-bottom: 4px;
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .nav-item.active { 
            color: #0284C7; 
        }
        .nav-item.active i { 
            transform: translateY(-2px);
            color: #0284C7; 
        }
        .nav-item.active::after {
            content: '';
            position: absolute;
            bottom: 6px;
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

                <!-- 5. Profil / Admin Link -->
                <a href="{{ url('/admin') }}" class="nav-item" target="_blank">
                    <i class="fa-solid fa-circle-user"></i>
                    <span>Profil</span>
                </a>
            </nav>
        </div>
        @endauth
    </div>

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('notify', (event) => {
                const data = Array.isArray(event) ? event[0] : event;
                Swal.fire({
                    icon: data.type || 'info',
                    title: data.title || (data.type === 'error' ? 'Peringatan!' : 'Informasi'),
                    text: data.message,
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#0284C7',
                    customClass: {
                        popup: 'rounded-2xl',
                        confirmButton: 'rounded-xl px-5 py-2.5 font-bold'
                    }
                });
            });
        });

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

