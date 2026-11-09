<style>
    /* Pangkas ruang kosong/lapang di bagian atas card login Filament */
    .fi-simple-main {
        padding-top: 1.25rem !important;
        padding-bottom: 2rem !important;
    }
    .fi-simple-header {
        margin-bottom: 0.25rem !important;
    }
</style>

<div class="flex flex-col items-center justify-center text-center -mt-2">
    {{-- 1. LOGO RESMI --}}
    @if (!empty($logoUrl))
        <div class="mb-1.5 flex items-center justify-center">
            <img 
                src="{{ $logoUrl }}" 
                alt="Logo Resmi" 
                class="h-20 sm:h-24 w-auto max-w-[280px] object-contain drop-shadow-lg transition-transform duration-300 hover:scale-105"
                onerror="this.onerror=null; this.src='{{ asset('images/logo-instansi.png') }}';"
            />
        </div>
    @endif

    {{-- 2. NAMA INSTANSI (Setelah Logo) --}}
    @if (!empty($namaDinas))
        <div class="text-xs sm:text-sm font-bold text-slate-600 dark:text-slate-300 uppercase tracking-widest leading-tight max-w-sm mt-0.5">
            {{ $namaDinas }}
        </div>
    @endif

    {{-- 3. NAMA APLIKASI (Setelah Nama Instansi) --}}
    <div class="mt-1 text-2xl sm:text-3xl font-black tracking-wider text-slate-900 dark:text-white uppercase drop-shadow-sm leading-tight">
        {{ $namaAplikasi }}
    </div>

    {{-- 4. KEPANJANGAN APLIKASI --}}
    @if (!empty($kepanjangan))
        <div class="mt-0.5 text-xs sm:text-sm font-semibold text-amber-600 dark:text-amber-400 max-w-md tracking-normal leading-snug">
            {{ $kepanjangan }}
        </div>
    @endif

    {{-- 5. GARIS PEMISAH & HEADING FORM MASUK --}}
    <div class="mt-3 pt-2.5 border-t border-slate-200 dark:border-white/10 w-full text-base sm:text-lg font-semibold text-slate-700 dark:text-slate-200">
        {{ $defaultHeading }}
    </div>
</div>
