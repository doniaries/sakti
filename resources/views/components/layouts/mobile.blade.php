<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>{{ $title ?? 'SAKTI Mobile' }}</title>
    
    <!-- PWA Setup -->
    @php
        $pwaIconVer = @filemtime(public_path('icons/pwa-192x192.png')) ?: time();
        $faviconVer = @filemtime(public_path('favicon.png')) ?: time();
    @endphp
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/pwa-192x192.png') }}?v={{ $pwaIconVer }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v={{ $faviconVer }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ $faviconVer }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}?v={{ $pwaIconVer }}">
    <meta name="theme-color" content="#1B6CA8">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="SAKTI Web">
    <link rel="apple-touch-icon" sizes="192x192" href="{{ asset('icons/pwa-192x192.png') }}?v={{ $pwaIconVer }}">
    <link rel="apple-touch-icon" sizes="512x512" href="{{ asset('icons/pwa-512x512.png') }}?v={{ $pwaIconVer }}">
    
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

        // Native Android/PWA Status Bar Notification + Web Push (VAPID) Bridge
        (function () {
            if (!('Notification' in window)) return;

            const vapidPublicKey = @js(config('webpush.vapid.public_key'));
            const subscribeWebPush = async () => {
                if (!vapidPublicKey || Notification.permission !== 'granted' || !('serviceWorker' in navigator) || !('PushManager' in window)) return;
                try {
                    const reg = await navigator.serviceWorker.ready;
                    let sub = await reg.pushManager.getSubscription();
                    if (!sub) {
                        const padding = '='.repeat((4 - vapidPublicKey.length % 4) % 4);
                        const base64 = (vapidPublicKey + padding).replace(/-/g, '+').replace(/_/g, '/');
                        const rawData = window.atob(base64);
                        const outputArray = new Uint8Array(rawData.length);
                        for (let i = 0; i < rawData.length; ++i) outputArray[i] = rawData.charCodeAt(i);
                        sub = await reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: outputArray });
                    }
                    @auth
                    await fetch(@js(route('mobile.push-subscribe')), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': @js(csrf_token()),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(sub.toJSON())
                    });
                    @endauth
                } catch (e) {}
            };

            const requestNotifPermission = () => {
                if (Notification.permission === 'default') {
                    Notification.requestPermission().then((perm) => {
                        if (perm === 'granted') subscribeWebPush();
                    }).catch(() => {});
                } else if (Notification.permission === 'granted') {
                    subscribeWebPush();
                }
            };
            if (Notification.permission === 'granted') {
                subscribeWebPush();
            }
            window.addEventListener('click', requestNotifPermission, { once: true, passive: true });
            window.addEventListener('touchstart', requestNotifPermission, { once: true, passive: true });

            window.addEventListener('notify', (event) => {
                const raw = event.detail;
                const detail = Array.isArray(raw) ? (raw[0] || {}) : (raw || {});
                const title = detail.title || 'SAKTI Mobile';
                const body = detail.message || '';
                if (!body) return;

                if (window.SaktiNative && typeof window.SaktiNative.postMessage === 'function') {
                    try {
                        window.SaktiNative.postMessage(JSON.stringify({ action: 'notify', title, body }));
                    } catch (e) {}
                }

                if (Notification.permission !== 'granted') return;

                const options = {
                    body: body,
                    icon: '/icons/icon-192x192.png?v=2',
                    badge: '/icons/icon-192x192.png?v=2',
                    vibrate: [200, 100, 200],
                    silent: false,
                    tag: 'sakti-notif-' + Date.now(),
                    renotify: true
                };

                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.getRegistration()
                        .then((reg) => {
                            if (reg && reg.showNotification) {
                                return reg.showNotification(title, options);
                            }
                            new Notification(title, options);
                        })
                        .catch(() => {
                            try { new Notification(title, options); } catch (e) {}
                        });
                } else {
                    try { new Notification(title, options); } catch (e) {}
                }
            });
        })();
    </script>
    
    <style>
        @media (max-width: 639px) {
            html {
                font-size: 15px;
            }
        }
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
            width: 100%;
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
            transition: background-color 0.2s ease, color 0.2s ease, max-width 0.2s ease;
        }
        @media (min-width: 640px) {
            .mobile-container,
            .bottom-nav {
                max-width: 640px;
            }
        }
        @media (min-width: 768px) {
            .mobile-container,
            .bottom-nav {
                max-width: 768px;
            }
        }
        @media (min-width: 1024px) {
            .mobile-container,
            .bottom-nav {
                max-width: 960px;
            }
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
        @media (min-width: 640px) {
            .bottom-nav { max-width: 640px; }
        }
        @media (min-width: 768px) {
            .bottom-nav { max-width: 768px; }
        }
        @media (min-width: 1024px) {
            .bottom-nav { max-width: 960px; }
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
            background: linear-gradient(135deg, #0284C7 0%, #1D4ED8 100%);
            color: #FFFFFF;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px -3px rgba(2, 132, 199, 0.45), 0 0 0 5px #F8FAFC;
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
        @keyframes rhoePageEnter {
            from {
                opacity: 0;
                transform: translateY(10px) scale(0.99);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .content-area > *,
        .rhoe-enter {
            animation: rhoePageEnter 260ms cubic-bezier(0.16, 1, 0.3, 1) both;
        }
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
        @keyframes androidPopIn {
            0% { opacity: 0; transform: translateY(28px) scale(0.94); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        .android-modal-anim {
            animation: androidPopIn 0.28s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
    </style>
    @livewireStyles
</head>
<body>
    @auth
        @if (auth()->user()?->hasAnyRole(['super_admin', 'superadmin']) && \App\Models\PengaturanAplikasi::getPengaturan()->mode_simulasi)
            <div class="bg-amber-400 text-slate-950 px-3 py-1.5 text-[11px] font-bold flex items-center justify-center gap-1.5 text-center shadow-xs">
                <i class="fa-solid fa-triangle-exclamation text-xs shrink-0"></i>
                <span>MODE SIMULASI AKTIF — Data simulasi tidak masuk laporan &amp; notif hanya ke Superadmin</span>
            </div>
        @endif
    @endauth
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
                failSafeTimer: null,
                startNav(e) {
                    const url = e?.detail?.url?.pathname || '';
                    this.target = url.includes('/absensi') ? 'absensi'
                        : (url.includes('/riwayat') ? 'riwayat'
                        : (url.includes('/izin') ? 'izin'
                        : (url.includes('/profil') ? 'profil'
                        : (url.includes('/surat-tugas/buat') ? 'surat_tugas_form'
                        : (url.includes('/surat-tugas') ? 'surat_tugas' : 'dashboard')))));
                    clearTimeout(this.timer);
                    clearTimeout(this.failSafeTimer);
                    this.timer = setTimeout(() => { this.navigating = true; }, 120);
                    this.failSafeTimer = setTimeout(() => { this.endNav(); }, 6000);
                },
                endNav() {
                    clearTimeout(this.timer);
                    clearTimeout(this.failSafeTimer);
                    this.navigating = false;
                }
            }"
            x-init="
                document.addEventListener('livewire:navigate', (e) => startNav(e));
                document.addEventListener('livewire:navigated', () => endNav());
                document.addEventListener('livewire:navigate-failed', () => endNav());
            "
            x-show="navigating"
            x-cloak
            x-transition.opacity.duration.150ms
            class="fixed inset-0 z-40 w-full max-w-[440px] sm:max-w-[640px] md:max-w-[768px] lg:max-w-[960px] mx-auto bg-slate-50 dark:bg-[#09090b] pb-24 space-y-4 pointer-events-none">
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

            <!-- 5. Skeleton Surat Tugas List -->
            <template x-if="target === 'surat_tugas'">
                <div class="space-y-4 animate-pulse">
                    <div class="bg-white dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800 px-4 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-slate-200 dark:bg-zinc-800"></div>
                            <div class="space-y-1.5">
                                <div class="h-3.5 w-24 rounded-full bg-slate-200 dark:bg-zinc-800"></div>
                                <div class="h-2.5 w-36 rounded-full bg-slate-100 dark:bg-zinc-800"></div>
                            </div>
                        </div>
                        <div class="h-8 w-28 rounded-xl bg-slate-200 dark:bg-zinc-800"></div>
                    </div>
                    <div class="px-4 space-y-3">
                        <div class="h-32 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800"></div>
                        <div class="h-32 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800"></div>
                        <div class="h-32 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800"></div>
                    </div>
                </div>
            </template>

            <!-- 6. Skeleton Form Buat Surat Tugas -->
            <template x-if="target === 'surat_tugas_form'">
                <div class="space-y-4 animate-pulse">
                    <div class="bg-white dark:bg-zinc-950 border-b border-zinc-200 dark:border-zinc-800 px-4 py-3 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-slate-200 dark:bg-zinc-800"></div>
                        <div class="space-y-1.5">
                            <div class="h-3.5 w-28 rounded-full bg-slate-200 dark:bg-zinc-800"></div>
                            <div class="h-2.5 w-40 rounded-full bg-slate-100 dark:bg-zinc-800"></div>
                        </div>
                    </div>
                    <div class="px-4 space-y-4">
                        <div class="h-56 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800"></div>
                        <div class="h-28 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800"></div>
                        <div class="h-44 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200/80 dark:border-zinc-800"></div>
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
                <a href="{{ route('mobile.dashboard') }}" class="nav-item {{ request()->routeIs('mobile.dashboard') ? 'active' : '' }}" wire:navigate.hover>
                    <i class="fa-solid fa-house"></i>
                    <span>Beranda</span>
                </a>

                <!-- 2. Riwayat & Izin -->
                <a href="{{ route('mobile.riwayat') }}" class="nav-item {{ request()->routeIs('mobile.riwayat') || request()->routeIs('mobile.izin.*') ? 'active' : '' }}" wire:navigate.hover>
                    <i class="fa-solid fa-calendar-days"></i>
                    <span>Riwayat</span>
                </a>

                <!-- 3. TOMBOL NAVIGASI PRESENSI TENGAH -->
                <div class="center-absen-btn-wrapper">
                    <a href="{{ route('mobile.absensi') }}" 
                       wire:navigate.hover
                       class="center-absen-btn group ring-4 ring-sky-200 transition-all duration-300 active:scale-95 cursor-pointer {{ request()->routeIs('mobile.absensi') ? 'ring-sky-400 shadow-sky-500/40' : '' }}"
                       title="Halaman Presensi Kehadiran">
                        <i class="fa-solid fa-fingerprint"></i>
                        <span>PRESENSI</span>
                    </a>
                </div>

                <!-- 4. Persetujuan (Atasan/Admin) atau LHK (Pegawai) -->
                @if (auth()->user()?->hasAnyRole(['super_admin', 'admin', 'pimpinan', 'kepala_dinas', 'kabid', 'atasan']))
                    <a href="{{ route('mobile.persetujuan') }}" class="nav-item {{ request()->routeIs('mobile.persetujuan') ? 'active' : '' }}" wire:navigate.hover>
                        <i class="fa-solid fa-clipboard-check"></i>
                        <span>Persetujuan</span>
                    </a>
                @else
                    <a href="{{ route('mobile.lhk.index') }}" class="nav-item {{ request()->routeIs('mobile.lhk.*') ? 'active' : '' }}" wire:navigate.hover>
                        <i class="fa-solid fa-clipboard-list"></i>
                        <span>LHK</span>
                    </a>
                @endif

                <!-- 5. Profil (Halaman sama dengan data pegawai Filament & bisa diedit) -->
                <a href="{{ route('mobile.profil') }}" class="nav-item {{ request()->routeIs('mobile.profil') ? 'active' : '' }}" wire:navigate.hover>
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
            show(raw) {
                const detail = Array.isArray(raw) ? (raw[0] || {}) : (raw || {});
                this.type = detail.type || 'info';
                this.title = detail.title || (this.type === 'error' ? 'Peringatan!' : (this.type === 'success' ? 'Berhasil!' : 'Informasi'));
                this.message = detail.message || '';
                this.buttonText = detail.buttonText || 'Mengerti';
                this.showUser = Boolean(detail.show_user || false);
                this.open = true;
            },
            formattedMessage() {
                const esc = String(this.message || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
                return esc
                    .replace(/\(Tepat Waktu\)/g, '<strong class=\'font-extrabold text-emerald-700\'>(Tepat Waktu)</strong>')
                    .replace(/\(Terlambat[^)]*\)/g, '<strong class=\'font-extrabold text-rose-600\'>$&</strong>')
                    .replace(/\(Catatan:\s*Pulang Cepat\)/g, '<strong class=\'font-extrabold text-amber-600\'>(Pulang Cepat)</strong>')
                    .replace(/\(Mode Simulasi\)/g, '<strong class=\'font-bold text-sky-700\'>(Mode Simulasi)</strong>');
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
                        {{ auth()->user()->isPegawaiWajibAbsen() ? (auth()->user()->jabatan?->nama ?? 'Pegawai') : 'Super Administrator' }}
                    </span>
                </div>
                @endauth

                <p class="text-sm text-slate-600 mt-4 px-1 leading-7 font-medium tracking-wide" x-html="formattedMessage()"></p>
                @auth
                <p x-show="showUser || type === 'success'" class="text-sm font-black text-slate-950 mt-3">{{ auth()->user()->name }}</p>
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

    <!-- PWA Install Banner (muncul di atas agar tidak menghalangi navbar) -->
    <div id="pwa-install-banner"
         class="hidden fixed top-4 left-1/2 -translate-x-1/2 z-[90] w-[calc(100%-2rem)] max-w-md bg-white/95 dark:bg-zinc-900/95 backdrop-blur-md border border-sky-200 dark:border-zinc-700 rounded-2xl shadow-2xl p-3.5 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <img src="{{ asset('icons/pwa-192x192.png') }}?v={{ $pwaIconVer }}" alt="SAKTI Web" class="w-10 h-10 rounded-xl shrink-0 shadow-sm">
            <div class="min-w-0">
                <p class="text-xs font-extrabold text-slate-800 dark:text-white truncate">Pasang SAKTI Web</p>
                <p id="pwa-install-desc" class="text-[11px] text-slate-500 dark:text-zinc-400 truncate">Akses cepat langsung dari layar utama</p>
            </div>
        </div>
        <div class="flex items-center gap-1.5 shrink-0">
            <button type="button" id="pwa-install-btn"
                    class="px-3 py-1.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-sm active:scale-95 transition">
                Pasang
            </button>
            <button type="button" id="pwa-install-close" title="Tutup"
                    class="w-7 h-7 rounded-xl bg-slate-100 dark:bg-zinc-800 text-slate-500 dark:text-zinc-400 flex items-center justify-center text-xs hover:bg-slate-200">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>

    <script>
        (function () {
            const isStandalone = !!window.SaktiNative
                || window.matchMedia('(display-mode: standalone)').matches
                || window.matchMedia('(display-mode: minimal-ui)').matches
                || window.matchMedia('(display-mode: fullscreen)').matches
                || window.matchMedia('(display-mode: window-controls-overlay)').matches
                || window.navigator.standalone === true;
            if (isStandalone) return;

            const banner = document.getElementById('pwa-install-banner');
            const installBtn = document.getElementById('pwa-install-btn');
            const closeBtn = document.getElementById('pwa-install-close');
            const descEl = document.getElementById('pwa-install-desc');
            if (!banner || !installBtn || !closeBtn) return;

            const showBanner = async () => {
                if (localStorage.getItem('sakti_pwa_installed') === '1') return;
                if (sessionStorage.getItem('sakti_pwa_dismissed') === '1') return;
                if (typeof navigator.getInstalledRelatedApps === 'function') {
                    try {
                        const relatedApps = await navigator.getInstalledRelatedApps();
                        if (relatedApps && relatedApps.length > 0) {
                            localStorage.setItem('sakti_pwa_installed', '1');
                            return;
                        }
                    } catch (err) {}
                }
                banner.classList.remove('hidden');
            };

            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                window.__saktiInstallPrompt = e;
                showBanner();
            });

            if (window.__saktiInstallPrompt) {
                showBanner();
            }

            installBtn.addEventListener('click', async () => {
                const promptEvent = window.__saktiInstallPrompt;
                if (promptEvent) {
                    promptEvent.prompt();
                    const { outcome } = await promptEvent.userChoice;
                    window.__saktiInstallPrompt = null;
                    if (outcome === 'accepted') {
                        localStorage.setItem('sakti_pwa_installed', '1');
                        banner.classList.add('hidden');
                    }
                } else if (descEl) {
                    descEl.textContent = 'Buka menu browser (⋮ / Bagikan) lalu pilih "Instal aplikasi" atau "Tambah ke layar utama".';
                    descEl.classList.remove('truncate');
                }
            });

            closeBtn.addEventListener('click', () => {
                banner.classList.add('hidden');
                sessionStorage.setItem('sakti_pwa_dismissed', '1');
                localStorage.setItem('sakti_pwa_installed', '1');
            });

            window.addEventListener('appinstalled', () => {
                window.__saktiInstallPrompt = null;
                localStorage.setItem('sakti_pwa_installed', '1');
                banner.classList.add('hidden');
            });
        })();
    </script>

    @auth
    <!-- Modal Paksa Aktifkan Izin Notifikasi & GPS -->
    <div id="sakti-perm-modal" class="hidden fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-xs">
        <div class="w-full max-w-sm rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 p-6 shadow-2xl text-center">
            <div class="mx-auto w-14 h-14 rounded-2xl bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400 flex items-center justify-center text-2xl mb-4">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Aktifkan Izin Notifikasi &amp; GPS</h3>
            <p id="sakti-perm-desc" class="text-xs text-slate-500 dark:text-zinc-400 mt-1.5 leading-relaxed">
                SAKTI Mobile memerlukan izin <strong>Notifikasi</strong> dan <strong>Lokasi (GPS)</strong> agar presensi dan pemberitahuan surat tugas berjalan normal.
            </p>
            <div class="mt-3.5 grid grid-cols-2 gap-2 text-[11px] font-bold">
                <div class="px-2.5 py-2 rounded-xl bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 flex items-center justify-between">
                    <span class="text-slate-600 dark:text-zinc-300"><i class="fa-solid fa-bell mr-1"></i>Notif</span>
                    <span id="sakti-perm-notif-status" class="px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-700">Belum</span>
                </div>
                <div class="px-2.5 py-2 rounded-xl bg-slate-50 dark:bg-zinc-800/80 border border-slate-200 dark:border-zinc-700 flex items-center justify-between">
                    <span class="text-slate-600 dark:text-zinc-300"><i class="fa-solid fa-location-dot mr-1"></i>GPS</span>
                    <span id="sakti-perm-gps-status" class="px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-700">Belum</span>
                </div>
            </div>
            <button type="button" id="sakti-perm-btn"
                class="mt-5 w-full py-3 px-4 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-lg shadow-sky-600/25 active:scale-95 transition cursor-pointer">
                Aktifkan Sekarang
            </button>
        </div>
    </div>
    <script>
        (function () {
            const modal = document.getElementById('sakti-perm-modal');
            const btn = document.getElementById('sakti-perm-btn');
            const desc = document.getElementById('sakti-perm-desc');
            if (!modal || !btn) return;

            const checkPermissions = async () => {
                if (window.SaktiNative) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    return;
                }
                const isAbsensiPage = window.location.pathname.includes('/mobile/absensi');
                const notifOk = !('Notification' in window) || Notification.permission === 'granted';
                let gpsOk = !isAbsensiPage || !('geolocation' in navigator);
                if (!gpsOk && navigator.permissions && navigator.permissions.query) {
                    try {
                        const geo = await navigator.permissions.query({ name: 'geolocation' });
                        gpsOk = geo.state === 'granted';
                    } catch (e) {
                        gpsOk = true;
                    }
                }
                const notifBadge = document.getElementById('sakti-perm-notif-status');
                const gpsBadge = document.getElementById('sakti-perm-gps-status');
                const gpsRow = gpsBadge ? gpsBadge.closest('div') : null;
                if (gpsRow) {
                    gpsRow.classList.toggle('hidden', !isAbsensiPage);
                }
                if (notifBadge) {
                    notifBadge.textContent = notifOk ? 'Aktif' : 'Belum';
                    notifBadge.className = notifOk
                        ? 'px-1.5 py-0.5 rounded-md bg-emerald-100 text-emerald-700'
                        : 'px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-700';
                }
                if (gpsBadge) {
                    gpsBadge.textContent = gpsOk ? 'Aktif' : 'Belum';
                    gpsBadge.className = gpsOk
                        ? 'px-1.5 py-0.5 rounded-md bg-emerald-100 text-emerald-700'
                        : 'px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-700';
                }
                if (!notifOk || !gpsOk) {
                    modal.classList.remove('hidden');
                } else {
                    modal.classList.add('hidden');
                }
            };

            btn.addEventListener('click', async () => {
                if ('Notification' in window && Notification.permission !== 'granted') {
                    try { await Notification.requestPermission(); } catch (e) {}
                }
                if ('geolocation' in navigator) {
                    navigator.geolocation.getCurrentPosition(
                        () => checkPermissions(),
                        () => {
                            if (desc) {
                                desc.innerHTML = 'Izin diblokir oleh browser. Buka <strong>Pengaturan Situs (ikon gembok di address bar)</strong> lalu aktifkan <strong>Notifikasi</strong> dan <strong>Lokasi</strong>, kemudian muat ulang halaman.';
                            }
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                }
                await checkPermissions();
            });

            window.addEventListener('load', checkPermissions);
            document.addEventListener('livewire:navigated', checkPermissions);
        })();
    </script>
    @endauth

    <!-- Global Tailwind/Flutter-style Delete Confirmation Modal -->
    <div x-data="{
            open: false,
            title: 'Konfirmasi Hapus',
            message: 'Yakin ingin menghapus data ini?',
            wireId: null,
            method: null,
            params: []
        }"
        @sakti-confirm.window="
            title = $event.detail?.title || 'Konfirmasi Hapus';
            message = $event.detail?.message || 'Yakin ingin menghapus data ini?';
            wireId = $event.detail?.wireId || null;
            method = $event.detail?.method || null;
            params = $event.detail?.params || [];
            open = true;
        "
        x-show="open"
        x-cloak
        class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs"
        style="display: none;">
        <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.outside="open = false"
            class="w-full max-w-xs bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-2xl border border-slate-100 dark:border-slate-800 text-center">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-500/15 text-rose-500 flex items-center justify-center mx-auto mb-3 text-lg">
                <i class="fa-solid fa-trash-can"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800 dark:text-slate-100" x-text="title"></h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed" x-text="message"></p>
            <div class="grid grid-cols-2 gap-2.5 mt-5">
                <button type="button" @click="open = false"
                    class="py-2.5 px-4 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition cursor-pointer">
                    Batal
                </button>
                <button type="button" @click="open = false; if (wireId && method && window.Livewire) { window.Livewire.find(wireId)?.call(method, ...params); }"
                    class="py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-sm shadow-rose-500/30 transition cursor-pointer">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>

    @livewireScripts

    <!-- Pull-to-Refresh (hanya di paling atas, butuh tarikan dalam >140px, cooldown 10 detik) -->
    <div id="sakti-ptr-indicator"
        class="fixed top-3 left-1/2 -translate-x-1/2 z-[9998] hidden items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-900/85 text-white text-[11px] font-semibold shadow-lg backdrop-blur-xs pointer-events-none transition-all duration-150">
        <i class="fa-solid fa-arrow-down text-sky-400" id="sakti-ptr-icon"></i>
        <span id="sakti-ptr-text">Tarik untuk memuat ulang</span>
    </div>
    <script>
        (function () {
            let startY = 0;
            let pulling = false;
            let deltaY = 0;
            let lastRefreshAt = 0;
            const THRESHOLD = 140;
            const COOLDOWN_MS = 10000;
            const indicator = document.getElementById('sakti-ptr-indicator');
            const icon = document.getElementById('sakti-ptr-icon');
            const text = document.getElementById('sakti-ptr-text');

            document.addEventListener('touchstart', (e) => {
                if (window.scrollY <= 0 && e.touches.length === 1) {
                    startY = e.touches[0].clientY;
                    pulling = true;
                    deltaY = 0;
                } else {
                    pulling = false;
                }
            }, { passive: true });

            document.addEventListener('touchmove', (e) => {
                if (!pulling || window.scrollY > 0) {
                    pulling = false;
                    indicator.classList.add('hidden');
                    indicator.classList.remove('flex');
                    return;
                }
                deltaY = e.touches[0].clientY - startY;
                if (deltaY > 45) {
                    indicator.classList.remove('hidden');
                    indicator.classList.add('flex');
                    if (deltaY >= THRESHOLD) {
                        text.textContent = 'Lepaskan untuk memuat ulang';
                        icon.className = 'fa-solid fa-rotate-right text-emerald-400';
                    } else {
                        text.textContent = 'Tarik lebih dalam untuk refresh';
                        icon.className = 'fa-solid fa-arrow-down text-sky-400';
                    }
                } else {
                    indicator.classList.add('hidden');
                    indicator.classList.remove('flex');
                }
            }, { passive: true });

            document.addEventListener('touchend', () => {
                if (!pulling) return;
                pulling = false;
                indicator.classList.add('hidden');
                indicator.classList.remove('flex');

                if (deltaY >= THRESHOLD) {
                    const now = Date.now();
                    if (now - lastRefreshAt < COOLDOWN_MS) {
                        text.textContent = 'Tunggu beberapa detik sebelum refresh lagi';
                        icon.className = 'fa-solid fa-clock text-amber-400';
                        indicator.classList.remove('hidden');
                        indicator.classList.add('flex');
                        setTimeout(() => {
                            indicator.classList.add('hidden');
                            indicator.classList.remove('flex');
                        }, 2000);
                        return;
                    }
                    lastRefreshAt = now;
                    window.location.reload();
                }
                deltaY = 0;
            }, { passive: true });
        })();
    </script>
</body>
</html>

