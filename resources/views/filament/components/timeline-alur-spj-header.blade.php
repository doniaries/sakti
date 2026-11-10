@props([
    'counts' => [
        'pemohon' => 0,
        'pptk' => 0,
        'ppk' => 0,
        'pa' => 0,
        'bendahara' => 0,
        'cair' => 0,
        'total' => 0,
    ],
])

<div class="flex flex-col gap-2 py-1 max-w-full">
    {{-- Header Baris Atas: Judul & Keterangan --}}
    <div class="flex flex-wrap items-center gap-2">
        <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-primary-500/10 text-primary-600 dark:bg-primary-400/10 dark:text-primary-400">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" />
            </svg>
        </span>
        <h3 class="text-sm font-bold tracking-tight text-gray-900 dark:text-white">
            Alur & Posisi Berkas SPJ
        </h3>
        <span class="hidden sm:inline-block text-xs font-medium text-gray-500 dark:text-gray-400">
            &bull; Pipeline 6 Tahapan Verifikasi
        </span>
        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-700 dark:bg-white/10 dark:text-gray-300">
            Total {{ $counts['total'] ?? 0 }} Berkas
        </span>
    </div>

    {{-- Stepper Pipeline Horizontal --}}
    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs text-gray-600 dark:text-gray-300 scrollbar-thin">
        {{-- Tahap 1: Pemohon --}}
        <div class="flex items-center gap-1.5 shrink-0 rounded-lg border {{ ($counts['pemohon'] ?? 0) > 0 ? 'border-amber-300 bg-amber-50/80 text-amber-900 dark:border-amber-700/60 dark:bg-amber-950/40 dark:text-amber-200 shadow-xs' : 'border-gray-200 bg-white/60 dark:border-white/10 dark:bg-white/5' }} px-2.5 py-1 transition">
            <span class="flex h-4 w-4 items-center justify-center rounded-full {{ ($counts['pemohon'] ?? 0) > 0 ? 'bg-amber-500 text-white font-bold' : 'bg-gray-200 text-gray-600 dark:bg-white/10 dark:text-gray-300 font-semibold' }} text-[10px]">1</span>
            <span class="font-semibold">Pemohon</span>
            <span class="rounded-full px-1.5 py-0.2 text-[10px] font-bold {{ ($counts['pemohon'] ?? 0) > 0 ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' }}">
                {{ $counts['pemohon'] ?? 0 }}
            </span>
        </div>

        <svg class="h-3.5 w-3.5 shrink-0 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>

        {{-- Tahap 2: PPTK --}}
        <div class="flex items-center gap-1.5 shrink-0 rounded-lg border {{ ($counts['pptk'] ?? 0) > 0 ? 'border-blue-300 bg-blue-50/80 text-blue-900 dark:border-blue-700/60 dark:bg-blue-950/40 dark:text-blue-200 shadow-xs' : 'border-gray-200 bg-white/60 dark:border-white/10 dark:bg-white/5' }} px-2.5 py-1 transition">
            <span class="flex h-4 w-4 items-center justify-center rounded-full {{ ($counts['pptk'] ?? 0) > 0 ? 'bg-blue-500 text-white font-bold' : 'bg-gray-200 text-gray-600 dark:bg-white/10 dark:text-gray-300 font-semibold' }} text-[10px]">2</span>
            <span class="font-semibold">PPTK</span>
            <span class="rounded-full px-1.5 py-0.2 text-[10px] font-bold {{ ($counts['pptk'] ?? 0) > 0 ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' }}">
                {{ $counts['pptk'] ?? 0 }}
            </span>
        </div>

        <svg class="h-3.5 w-3.5 shrink-0 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>

        {{-- Tahap 3: PPK --}}
        <div class="flex items-center gap-1.5 shrink-0 rounded-lg border {{ ($counts['ppk'] ?? 0) > 0 ? 'border-indigo-300 bg-indigo-50/80 text-indigo-900 dark:border-indigo-700/60 dark:bg-indigo-950/40 dark:text-indigo-200 shadow-xs' : 'border-gray-200 bg-white/60 dark:border-white/10 dark:bg-white/5' }} px-2.5 py-1 transition">
            <span class="flex h-4 w-4 items-center justify-center rounded-full {{ ($counts['ppk'] ?? 0) > 0 ? 'bg-indigo-500 text-white font-bold' : 'bg-gray-200 text-gray-600 dark:bg-white/10 dark:text-gray-300 font-semibold' }} text-[10px]">3</span>
            <span class="font-semibold">PPK (Keu)</span>
            <span class="rounded-full px-1.5 py-0.2 text-[10px] font-bold {{ ($counts['ppk'] ?? 0) > 0 ? 'bg-indigo-500 text-white' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' }}">
                {{ $counts['ppk'] ?? 0 }}
            </span>
        </div>

        <svg class="h-3.5 w-3.5 shrink-0 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>

        {{-- Tahap 4: PA / Kadis --}}
        <div class="flex items-center gap-1.5 shrink-0 rounded-lg border {{ ($counts['pa'] ?? 0) > 0 ? 'border-purple-300 bg-purple-50/80 text-purple-900 dark:border-purple-700/60 dark:bg-purple-950/40 dark:text-purple-200 shadow-xs' : 'border-gray-200 bg-white/60 dark:border-white/10 dark:bg-white/5' }} px-2.5 py-1 transition">
            <span class="flex h-4 w-4 items-center justify-center rounded-full {{ ($counts['pa'] ?? 0) > 0 ? 'bg-purple-500 text-white font-bold' : 'bg-gray-200 text-gray-600 dark:bg-white/10 dark:text-gray-300 font-semibold' }} text-[10px]">4</span>
            <span class="font-semibold">PA (Kadis)</span>
            <span class="rounded-full px-1.5 py-0.2 text-[10px] font-bold {{ ($counts['pa'] ?? 0) > 0 ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' }}">
                {{ $counts['pa'] ?? 0 }}
            </span>
        </div>

        <svg class="h-3.5 w-3.5 shrink-0 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>

        {{-- Tahap 5: Bendahara --}}
        <div class="flex items-center gap-1.5 shrink-0 rounded-lg border {{ ($counts['bendahara'] ?? 0) > 0 ? 'border-emerald-300 bg-emerald-50/80 text-emerald-900 dark:border-emerald-700/60 dark:bg-emerald-950/40 dark:text-emerald-200 shadow-xs' : 'border-gray-200 bg-white/60 dark:border-white/10 dark:bg-white/5' }} px-2.5 py-1 transition">
            <span class="flex h-4 w-4 items-center justify-center rounded-full {{ ($counts['bendahara'] ?? 0) > 0 ? 'bg-emerald-500 text-white font-bold' : 'bg-gray-200 text-gray-600 dark:bg-white/10 dark:text-gray-300 font-semibold' }} text-[10px]">5</span>
            <span class="font-semibold">Bendahara</span>
            <span class="rounded-full px-1.5 py-0.2 text-[10px] font-bold {{ ($counts['bendahara'] ?? 0) > 0 ? 'bg-emerald-500 text-white' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' }}">
                {{ $counts['bendahara'] ?? 0 }}
            </span>
        </div>

        <svg class="h-3.5 w-3.5 shrink-0 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>

        {{-- Tahap 6: Cair --}}
        <div class="flex items-center gap-1.5 shrink-0 rounded-lg border {{ ($counts['cair'] ?? 0) > 0 ? 'border-teal-300 bg-teal-50/80 text-teal-900 dark:border-teal-700/60 dark:bg-teal-950/40 dark:text-teal-200 shadow-xs' : 'border-gray-200 bg-white/60 dark:border-white/10 dark:bg-white/5' }} px-2.5 py-1 transition">
            <span class="flex h-4 w-4 items-center justify-center rounded-full {{ ($counts['cair'] ?? 0) > 0 ? 'bg-teal-500 text-white font-bold' : 'bg-gray-200 text-gray-600 dark:bg-white/10 dark:text-gray-300 font-semibold' }} text-[10px]">✓</span>
            <span class="font-semibold">Selesai/Cair</span>
            <span class="rounded-full px-1.5 py-0.2 text-[10px] font-bold {{ ($counts['cair'] ?? 0) > 0 ? 'bg-teal-500 text-white' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' }}">
                {{ $counts['cair'] ?? 0 }}
            </span>
        </div>
    </div>
</div>
