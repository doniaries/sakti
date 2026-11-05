<div class="flex flex-col items-center justify-center text-center -mt-6 sm:-mt-8">
    {{-- 1. LOGO RESMI --}}
    @if ($logoUrl)
        <div class="mb-1 flex items-center justify-center">
            <img 
                src="{{ $logoUrl }}" 
                alt="{{ $namaDinas ?? $namaAplikasi }}" 
                class="h-24 sm:h-28 w-auto max-w-[300px] object-contain drop-shadow-lg transition-transform duration-300 hover:scale-105"
            />
        </div>
    @endif

    {{-- 2. NAMA INSTANSI (Setelah Logo) --}}
    @if (!empty($namaDinas))
        <div class="text-xs sm:text-sm font-bold text-gray-300 dark:text-gray-300 uppercase tracking-widest leading-tight max-w-sm mt-0.5">
            {{ $namaDinas }}
        </div>
    @endif

    {{-- 3. NAMA APLIKASI (Setelah Nama Instansi) --}}
    <div class="mt-1 text-2xl sm:text-3xl font-black tracking-wider text-white uppercase drop-shadow-sm leading-tight">
        {{ $namaAplikasi }}
    </div>

    {{-- 4. KEPANJANGAN APLIKASI --}}
    @if (!empty($kepanjangan))
        <div class="mt-0.5 text-xs sm:text-sm font-medium text-amber-400 dark:text-amber-400 max-w-md tracking-normal leading-snug">
            {{ $kepanjangan }}
        </div>
    @endif

    {{-- 5. GARIS PEMISAH & HEADING FORM MASUK --}}
    <div class="mt-3 pt-2.5 border-t border-white/10 w-full text-base sm:text-lg font-semibold text-gray-200 dark:text-gray-200">
        {{ $defaultHeading }}
    </div>
</div>
