<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('code', '403') — @yield('title', 'Akses Dibatasi') | SAKTI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            background: radial-gradient(circle at 15% 20%, #1e293b 0%, #0f172a 55%, #020617 100%);
            color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            padding: 24px;
        }
        .bg-vector-pattern {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
            opacity: 0.55;
        }
        .glow-orb-1 {
            position: fixed;
            top: -120px;
            right: -100px;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.22) 0%, rgba(245, 158, 11, 0) 70%);
            filter: blur(40px);
            pointer-events: none;
            z-index: 0;
        }
        .glow-orb-2 {
            position: fixed;
            bottom: -140px;
            left: -100px;
            width: 520px;
            height: 520px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.20) 0%, rgba(56, 189, 248, 0) 70%);
            filter: blur(45px);
            pointer-events: none;
            z-index: 0;
        }
        .error-card {
            position: relative;
            z-index: 10;
            max-width: 640px;
            width: 100%;
            background: rgba(15, 23, 42, 0.78);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 28px;
            padding: 42px 36px;
            text-align: center;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.65), inset 0 1px 0 0 rgba(255, 255, 255, 0.12);
        }
        .illustration-wrap {
            width: 220px;
            height: 190px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: floatSlow 4s ease-in-out infinite;
        }
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        .badge-code {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.35);
            margin-bottom: 14px;
        }
        .error-title {
            font-size: 1.85rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.02em;
            margin-bottom: 10px;
            line-height: 1.25;
        }
        .error-desc {
            font-size: 0.95rem;
            color: #94a3b8;
            line-height: 1.65;
            max-width: 500px;
            margin: 0 auto 24px;
        }
        .user-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(30, 41, 59, 0.85);
            border: 1px solid rgba(148, 163, 184, 0.2);
            padding: 8px 16px;
            border-radius: 14px;
            font-size: 0.82rem;
            color: #cbd5e1;
            margin-bottom: 26px;
        }
        .user-pill strong {
            color: #38bdf8;
        }
        .btn-group {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 12px 22px;
            border-radius: 14px;
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #0f172a;
            box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.45);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px -5px rgba(245, 158, 11, 0.6);
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        }
        .btn-secondary {
            background: rgba(56, 189, 248, 0.14);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.35);
        }
        .btn-secondary:hover {
            transform: translateY(-2px);
            background: rgba(56, 189, 248, 0.24);
        }
        .btn-ghost {
            background: rgba(255, 255, 255, 0.06);
            color: #cbd5e1;
            border: 1px solid rgba(255, 255, 255, 0.12);
        }
        .btn-ghost:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
        }
    </style>
</head>
<body>
    <!-- Background Vector Geometric Mesh -->
    <svg class="bg-vector-pattern" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice">
        <defs>
            <pattern id="grid" width="60" height="60" patternUnits="userSpaceOnUse">
                <path d="M 60 0 L 0 0 0 60" fill="none" stroke="rgba(148, 163, 184, 0.07)" stroke-width="1"/>
            </pattern>
            <linearGradient id="waveGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#f59e0b" stop-opacity="0.12"/>
                <stop offset="100%" stop-color="#38bdf8" stop-opacity="0.04"/>
            </linearGradient>
            <linearGradient id="waveGrad2" x1="100%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" stop-color="#38bdf8" stop-opacity="0.12"/>
                <stop offset="100%" stop-color="#6366f1" stop-opacity="0.03"/>
            </linearGradient>
        </defs>
        <rect width="1440" height="900" fill="url(#grid)"/>
        <path d="M0,640 C320,520 480,760 820,640 C1120,530 1280,680 1440,590 L1440,900 L0,900 Z" fill="url(#waveGrad1)"/>
        <path d="M0,720 C280,640 620,820 980,700 C1210,620 1340,740 1440,690 L1440,900 L0,900 Z" fill="url(#waveGrad2)"/>
        <circle cx="180" cy="160" r="90" fill="none" stroke="rgba(245, 158, 11, 0.1)" stroke-width="2" stroke-dasharray="8 8"/>
        <circle cx="1260" cy="220" r="130" fill="none" stroke="rgba(56, 189, 248, 0.1)" stroke-width="2" stroke-dasharray="10 10"/>
    </svg>

    <div class="glow-orb-1"></div>
    <div class="glow-orb-2"></div>

    <main class="error-card">
        <div class="illustration-wrap">
            @yield('illustration')
        </div>

        <div class="badge-code">
            <span>ERROR @yield('code', '403')</span>
            <span>•</span>
            <span>@yield('badge_label', 'PROTEKSI HAK AKSES SHIELD')</span>
        </div>

        <h1 class="error-title">@yield('title', 'Halaman Tidak Dapat Diakses')</h1>

        <p class="error-desc">
            @yield('message', 'Maaf, akun Anda tidak memiliki izin untuk membuka halaman ini.')
        </p>

        @auth
            <div class="user-pill">
                <span>Login aktif: <strong>{{ auth()->user()->name }}</strong></span>
                @if(auth()->user()->bidang)
                    <span>•</span>
                    <span>Bidang: <strong>{{ auth()->user()->bidang->nama }}</strong></span>
                @endif
            </div>
        @endauth

        <div class="btn-group">
            <a href="{{ url('/admin') }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.3">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Kembali ke Beranda</span>
            </a>

            <a href="{{ url('/admin/pengajuan-spjs') }}" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Menu Keuangan & SPJ</span>
            </a>

            <button type="button" onclick="window.history.length > 1 ? window.history.back() : window.location.href='{{ url('/admin') }}'" class="btn btn-ghost">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Halaman Sebelumnya</span>
            </button>
        </div>
    </main>
</body>
</html>
