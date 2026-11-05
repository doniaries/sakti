@extends('errors.layout')

@section('code', '403')
@section('badge_label', 'HAK AKSES DIBATASI')
@section('title', 'Akses Halaman Dibatasi')
@section('message')
    @php
        $isFilament = request()->is('admin*');
    @endphp
    @if(!empty($exception?->getMessage()) && !in_array($exception->getMessage(), ['This action is unauthorized.', 'Forbidden']))
        {{ $exception->getMessage() }}
    @elseif($isFilament)
        Mohon maaf, akun Anda tidak memiliki hak akses untuk membuka Panel Administrator Sistem. Panel ini khusus diperuntukkan bagi pejabat pengelola dan administrator berwenang. Silakan hubungi Kasubag Umum & Kepegawaian jika Anda memerlukan akses.
    @else
        Mohon maaf, akun Anda tidak memiliki hak akses untuk membuka halaman atau fitur ini. Silakan kembali ke menu utama untuk melanjutkan aktivitas kedinasan Anda.
    @endif
@endsection

@section('illustration')
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 200" width="220" height="190" fill="none">
        <defs>
            <linearGradient id="shieldBody403" x1="50" y1="20" x2="190" y2="175" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#f59e0b" stop-opacity="0.28"/>
                <stop offset="100%" stop-color="#0284c7" stop-opacity="0.16"/>
            </linearGradient>
            <linearGradient id="shieldStroke403" x1="60" y1="20" x2="180" y2="175" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#fbbf24"/>
                <stop offset="100%" stop-color="#38bdf8"/>
            </linearGradient>
            <linearGradient id="lockGrad403" x1="95" y1="75" x2="145" y2="135" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#fde68a"/>
                <stop offset="50%" stop-color="#f59e0b"/>
                <stop offset="100%" stop-color="#d97706"/>
            </linearGradient>
        </defs>

        <!-- Outer Radar Rings -->
        <circle cx="120" cy="102" r="82" stroke="rgba(251, 191, 36, 0.16)" stroke-width="1.5" stroke-dasharray="6 6"/>
        <circle cx="120" cy="102" r="66" fill="rgba(245, 158, 11, 0.05)"/>

        <!-- Floating Security Nodes -->
        <circle cx="46" cy="56" r="5" fill="#38bdf8" opacity="0.8"/>
        <circle cx="194" cy="64" r="4" fill="#fbbf24" opacity="0.85"/>
        <circle cx="52" cy="148" r="4" fill="#fbbf24" opacity="0.6"/>
        <circle cx="188" cy="142" r="6" fill="#38bdf8" opacity="0.6"/>

        <!-- Shield Vector Base -->
        <path d="M120 22L178 46V96C178 136.5 153.2 168.8 120 182C86.8 168.8 62 136.5 62 96V46L120 22Z"
              fill="url(#shieldBody403)"
              stroke="url(#shieldStroke403)"
              stroke-width="3"
              stroke-linejoin="round"/>

        <!-- Inner Shield Highlight -->
        <path d="M120 34L166 53V95C166 128.8 146.1 155.8 120 167.5C93.9 155.8 74 128.8 74 95V53L120 34Z"
              stroke="rgba(255, 255, 255, 0.14)"
              stroke-width="1.5"/>

        <!-- Shackle of Padlock -->
        <path d="M104 88V74C104 65.163 111.163 58 120 58C128.837 58 136 65.163 136 74V88"
              stroke="#fde68a"
              stroke-width="6"
              stroke-linecap="round"/>

        <!-- Padlock Body -->
        <rect x="93" y="86" width="54" height="44" rx="11" fill="url(#lockGrad403)" stroke="#fef3c7" stroke-width="2"/>

        <!-- Keyhole -->
        <circle cx="120" cy="104" r="5" fill="#0f172a"/>
        <path d="M120 108V118" stroke="#0f172a" stroke-width="4" stroke-linecap="round"/>

        <!-- Warning Badge Corner -->
        <g transform="translate(150, 118)">
            <circle cx="18" cy="18" r="16" fill="#ef4444" stroke="#0f172a" stroke-width="3"/>
            <path d="M12 12L24 24M24 12L12 24" stroke="#ffffff" stroke-width="3" stroke-linecap="round"/>
        </g>
    </svg>
@endsection

