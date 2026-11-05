<div class="flex flex-col items-center justify-center text-center -mt-2">
    {{-- 1. LOGO RESMI --}}
    @if ($logoUrl)
        <div class="mb-2 flex items-center justify-center">
            <img 
                src="{{ $logoUrl }}" 
                alt="{{ $namaDinas ?? $namaAplikasi }}" 
                class="h-16 w-auto max-w-[220px] object-contain drop-shadow-md transition-transform duration-300 hover:scale-105"
            />
        </div>
    @endif

    {{-- 2. NAMA INSTANSI (Setelah Logo) --}}
    @if (!empty($namaDinas))
        <div class="text-xs font-semibold text-gray-300 dark:text-gray-300 uppercase tracking-wider leading-snug max-w-sm">
            {{ $namaDinas }}
        </div>
    @endif

    {{-- 3. NAMA APLIKASI (Setelah Nama Instansi) --}}
    <div class="mt-1 text-2xl sm:text-3xl font-black tracking-wider text-white uppercase drop-shadow-sm leading-tight">
        {{ $namaAplikasi }}
    </div>

    {{-- 4. KEPANJANGAN APLIKASI --}}
    @if (!empty($kepanjangan))
        <div class="mt-0.5 text-xs font-medium text-amber-400 dark:text-amber-400 max-w-md tracking-normal leading-snug">
            {{ $kepanjangan }}
        </div>
    @endif

    {{-- 5. GARIS PEMISAH & HEADING FORM MASUK --}}
    <div class="mt-3 pt-2.5 border-t border-white/10 w-full text-base font-semibold text-gray-200 dark:text-gray-200">
        {{ $defaultHeading }}
    </div>
</div>
