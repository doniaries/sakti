@extends('errors.layout')

@section('code', '500')
@section('badge_label', 'GANGGUAN TEKNIS SISTEM')
@section('title', 'Terjadi Kendala Pemrosesan')
@section('message')
    Sistem sedang mengalami kendala teknis saat memproses permintaan Anda. Silakan kembali ke Beranda dan coba beberapa saat lagi.
@endsection

@section('illustration')
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 200" width="220" height="190" fill="none">
        <circle cx="120" cy="100" r="78" stroke="rgba(239, 68, 68, 0.2)" stroke-width="1.5" stroke-dasharray="6 6"/>
        <rect x="64" y="48" width="112" height="42" rx="10" fill="rgba(30, 41, 59, 0.9)" stroke="#38bdf8" stroke-width="2.5"/>
        <circle cx="84" cy="69" r="5" fill="#22c55e"/>
        <circle cx="100" cy="69" r="5" fill="#fbbf24"/>
        <line x1="122" y1="69" x2="158" y2="69" stroke="rgba(255,255,255,0.3)" stroke-width="4" stroke-linecap="round"/>

        <rect x="64" y="102" width="112" height="42" rx="10" fill="rgba(30, 41, 59, 0.9)" stroke="#f59e0b" stroke-width="2.5"/>
        <circle cx="84" cy="123" r="5" fill="#ef4444"/>
        <circle cx="100" cy="123" r="5" fill="#fbbf24"/>
        <line x1="122" y1="123" x2="158" y2="123" stroke="rgba(255,255,255,0.3)" stroke-width="4" stroke-linecap="round"/>

        <g transform="translate(148, 114)">
            <polygon points="20,2 38,34 2,34" fill="#ef4444" stroke="#0f172a" stroke-width="2.5" stroke-linejoin="round"/>
            <line x1="20" y1="13" x2="20" y2="23" stroke="#ffffff" stroke-width="3" stroke-linecap="round"/>
            <circle cx="20" cy="28" r="1.8" fill="#ffffff"/>
        </g>
    </svg>
@endsection
