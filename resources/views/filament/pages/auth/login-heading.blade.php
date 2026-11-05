<div class="flex flex-col items-center justify-center text-center -mt-2">
    {{-- 1. LOGO RESMI --}}
    @if ($logoUrl)
        <div class="mb-1.5 flex items-center justify-center">
            <img 
                src="{{ $logoUrl }}" 
                alt="{{ $namaDinas ?? $namaAplikasi }}" 
                class="h-11 sm:h-12 w-auto max-w-[150px] object-contain drop-shadow transition-transform duration-300 hover:scale-105"
            />
        </div>
    @endif

    {{-- 2. NAMA INSTANSI (Setelah Logo) --}}
    @if (!empty($namaDinas))
        <div class="text-[11px] sm:text-xs font-semibold text-gray-300 dark:text-gray-300 uppercase tracking-wider leading-tight max-w-xs">
            {{ $namaDinas }}
        </div>
    @endif

    {{-- 3. NAMA APLIKASI (Setelah Nama Instansi) --}}
    <div class="mt-0.5 text-xl sm:text-2xl font-black tracking-wider text-white uppercase drop-shadow-sm leading-tight">
        {{ $namaAplikasi }}
    </div>

    {{-- 4. KEPANJANGAN APLIKASI --}}
    @if (!empty($kepanjangan))
        <div class="mt-0.5 text-[11px] sm:text-xs font-medium text-amber-400 dark:text-amber-400 max-w-xs tracking-normal leading-tight">
            {{ $kepanjangan }}
        </div>
    @endif

    {{-- 5. GARIS PEMISAH & HEADING FORM MASUK --}}
    <div class="mt-2.5 pt-2 border-t border-white/10 w-full text-sm sm:text-base font-semibold text-gray-200 dark:text-gray-200">
        {{ $defaultHeading }}
    </div>
</div>
