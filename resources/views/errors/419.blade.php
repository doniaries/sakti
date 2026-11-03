@extends('errors.layout')

@section('code', '419')
@section('badge_label', 'SESI KEAMANAN BERAKHIR')
@section('title', 'Sesi Halaman Kedaluwarsa')
@section('message')
    Halaman telah dibiarkan tidak aktif terlalu lama sehingga token keamanan berakhir. Silakan muat ulang halaman atau kembali ke Beranda untuk melanjutkan.
@endsection

@section('illustration')
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 240 200" width="220" height="190" fill="none">
        <circle cx="120" cy="100" r="78" stroke="rgba(251, 191, 36, 0.18)" stroke-width="1.5" stroke-dasharray="6 6"/>
        <circle cx="120" cy="100" r="58" fill="rgba(245, 158, 11, 0.14)" stroke="#fbbf24" stroke-width="3.5"/>
        <circle cx="120" cy="100" r="46" stroke="rgba(255,255,255,0.16)" stroke-width="1.5" stroke-dasharray="4 6"/>
        <path d="M120 72V102L140 114" stroke="#fde68a" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="120" cy="100" r="5" fill="#38bdf8"/>
        <path d="M166 56L180 42M74 56L60 42" stroke="#38bdf8" stroke-width="4" stroke-linecap="round"/>
    </svg>
@endsection

