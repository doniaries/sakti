<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title ?? 'Absensi LHK Mobile' }}</title>
    
    <!-- Tailwind CSS (menggunakan CDN untuk kemudahan/sementara, namun disarankan via Vite di production) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #F0F4F8; font-family: 'Inter', sans-serif; }
        .mobile-container {
            max-width: 480px;
            margin: 0 auto;
            background: #F0F4F8;
            min-height: 100vh;
            position: relative;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }
        .content-area { flex: 1; padding-bottom: 80px; overflow-y: auto; }
        .bottom-nav {
            position: absolute;
            bottom: 0; width: 100%;
            background: white;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            padding-bottom: env(safe-area-inset-bottom, 10px);
            z-index: 50;
        }
        .nav-item {
            display: flex; flex-direction: column; align-items: center;
            color: #9ca3af; font-size: 11px; text-decoration: none;
            transition: color 0.2s;
        }
        .nav-item i { font-size: 20px; margin-bottom: 4px; }
        .nav-item.active { color: #1B6CA8; }
        .nav-item.active i { color: #1B6CA8; }
    </style>
    @livewireStyles
</head>
<body>
    <div class="mobile-container">
        <!-- Content -->
        <div class="content-area">
            {{ $slot }}
        </div>

        <!-- Bottom Navigation -->
        @auth
        <nav class="bottom-nav">
            <a href="{{ route('mobile.dashboard') }}" class="nav-item {{ request()->routeIs('mobile.dashboard') ? 'active' : '' }}" wire:navigate>
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('mobile.absensi') }}" class="nav-item {{ request()->routeIs('mobile.absensi') ? 'active' : '' }}" wire:navigate>
                <i class="fa-solid fa-fingerprint"></i>
                <span>Absensi</span>
            </a>
            <a href="{{ route('mobile.riwayat') }}" class="nav-item {{ request()->routeIs('mobile.riwayat') ? 'active' : '' }}" wire:navigate>
                <i class="fa-solid fa-clock-rotate-left"></i>
                <span>Riwayat</span>
            </a>
            <a href="{{ route('mobile.lhk.index') }}" class="nav-item {{ request()->routeIs('mobile.lhk.*') ? 'active' : '' }}" wire:navigate>
                <i class="fa-solid fa-file-signature"></i>
                <span>LHK</span>
            </a>
        </nav>
        @endauth
    </div>

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('notify', (event) => {
                Swal.fire({
                    icon: event[0].type,
                    title: event[0].message,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true,
                });
            });
        });
    </script>
</body>
</html>
