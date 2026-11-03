@extends('errors.layout')

@section('code', '404')
@section('badge_label', 'HALAMAN TIDAK DITEMUKAN')
@section('title', 'Halaman atau Data Tidak Ditemukan')
@section('message')
    Tautan yang Anda akses mungkin telah dipindahkan, dihapus, atau alamat URL yang dimasukkan kurang tepat. Silakan kembali ke Beranda untuk melanjutkan aktivitas.
@endsection

@section('illustration')
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 200" width="220" height="190" fill="none">
        <defs>
            <linearGradient id="docGrad404" x1="55" y1="30" x2="165" y2="165" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#38bdf8" stop-opacity="0.28"/>
                <stop offset="100%" stop-color="#6366f1" stop-opacity="0.16"/>
            </linearGradient>
            <linearGradient id="lensGrad404" x1="115" y1="85" x2="185" y2="155" gradientUnits="userSpaceOnUse">
                <stop offset="0%" stop-color="#fbbf24"/>
                <stop offset="100%" stop-color="#f59e0b"/>
            </linearGradient>
        </defs>

        <circle cx="120" cy="100" r="78" stroke="rgba(56, 189, 248, 0.18)" stroke-width="1.5" stroke-dasharray="6 6"/>

        <!-- Floating Document Card -->
        <rect x="64" y="32" width="94" height="122" rx="14" fill="url(#docGrad404)" stroke="#38bdf8" stroke-width="2.5"/>
        <line x1="82" y1="60" x2="128" y2="60" stroke="#7dd3fc" stroke-width="4" stroke-linecap="round"/>
        <line x1="82" y1="78" x2="140" y2="78" stroke="rgba(255,255,255,0.35)" stroke-width="4" stroke-linecap="round"/>
        <line x1="82" y1="96" x2="118" y2="96" stroke="rgba(255,255,255,0.25)" stroke-width="4" stroke-linecap="round"/>

        <!-- Magnifying Glass Vector -->
        <circle cx="144" cy="116" r="30" fill="rgba(15, 23, 42, 0.88)" stroke="url(#lensGrad404)" stroke-width="6"/>
        <line x1="166" y1="138" x2="190" y2="162" stroke="#f59e0b" stroke-width="8" stroke-linecap="round"/>

        <!-- Question Mark inside Lens -->
        <path d="M138 109C138 104.5 141.5 102 145 102C148.5 102 151.5 104.5 151.5 108C151.5 111 149 112.5 146.5 114.5C145 115.5 144.5 117 144.5 119"
              stroke="#fbbf24" stroke-width="3.5" stroke-linecap="round"/>
        <circle cx="144.5" cy="126" r="2.3" fill="#fbbf24"/>
    </svg>
@endsection
