<div class="flex flex-col items-center justify-center text-center">
    @if ($logoUrl)
        <div class="mb-3 flex items-center justify-center">
            <img 
                src="{{ $logoUrl }}" 
                alt="{{ $namaAplikasi }}" 
                class="h-16 w-auto max-w-[180px] object-contain drop-shadow-lg transition-transform duration-300 hover:scale-105"
                onerror="this.style.display='none'"
            />
        </div>
    @endif

    <div class="text-2xl sm:text-3xl font-black tracking-wider text-white uppercase drop-shadow-sm">
        {{ $namaAplikasi }}
    </div>

    @if (!empty($kepanjangan))
        <div class="mt-1 text-xs sm:text-sm font-medium text-amber-400 dark:text-amber-400 max-w-md tracking-wide leading-snug">
            {{ $kepanjangan }}
        </div>
    @endif

    @if (!empty($namaDinas))
        <div class="mt-1 text-[11px] font-semibold text-gray-400 dark:text-gray-400 uppercase tracking-widest">
            {{ $namaDinas }}
        </div>
    @endif

    <div class="mt-4 pt-3 border-t border-white/10 w-full text-base sm:text-lg font-semibold text-gray-200 dark:text-gray-200">
        {{ $defaultHeading }}
    </div>
</div>

