<div class="p-5 pb-28">
    <!-- Header Back Navigation -->
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center space-x-3">
            <a href="{{ route('mobile.dashboard') }}" wire:navigate 
               class="w-10 h-10 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-center text-slate-600 hover:text-slate-800 active:scale-95 transition-all">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Hari Libur</h1>
                <p class="text-xs text-slate-500 font-medium">Libur Nasional & Cuti Bersama</p>
            </div>
        </div>

        <!-- Year Switcher -->
        <div class="flex items-center space-x-1.5 bg-white px-2 py-1 rounded-2xl shadow-xs border border-slate-200/80">
            <button wire:click="prevYear" class="w-6 h-6 rounded-lg bg-slate-50 hover:bg-sky-50 text-slate-500 hover:text-sky-600 flex items-center justify-center transition-colors active:scale-90">
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </button>
            <span class="text-xs font-black text-sky-700 px-1 select-none font-mono">
                {{ $tahun }}
            </span>
            <button wire:click="nextYear" class="w-6 h-6 rounded-lg bg-slate-50 hover:bg-sky-50 text-slate-500 hover:text-sky-600 flex items-center justify-center transition-colors active:scale-90">
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </button>
        </div>
    </div>

    <!-- Filter Jenis -->
    <div class="flex space-x-2 mb-5">
        <button wire:click="setFilterJenis('semua')" 
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all shadow-xs {{ $filterJenis === 'semua' ? 'bg-slate-800 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
            Semua
        </button>
        <button wire:click="setFilterJenis('libur_nasional')" 
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all shadow-xs {{ $filterJenis === 'libur_nasional' ? 'bg-rose-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
            <i class="fa-solid fa-calendar-xmark mr-1 text-[10px]"></i> Libur Nasional
        </button>
        <button wire:click="setFilterJenis('cuti_bersama')" 
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all shadow-xs {{ $filterJenis === 'cuti_bersama' ? 'bg-amber-500 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
            <i class="fa-solid fa-umbrella-beach mr-1 text-[10px]"></i> Cuti Bersama
        </button>
    </div>

    <!-- List Hari Libur -->
    <div class="space-y-3">
        @forelse($hariLiburs as $hl)
            @php
                $isPassed = $hl->tanggal->isPast() && !$hl->tanggal->isToday();
                $isToday = $hl->tanggal->isToday();
                $isCuti = $hl->jenis === 'cuti_bersama';
            @endphp
            <div class="bg-white rounded-3xl p-4 border border-slate-100 shadow-xs flex items-center justify-between {{ $isPassed ? 'opacity-60' : '' }}">
                <div class="flex items-center space-x-3.5">
                    <div class="w-12 h-12 rounded-2xl flex flex-col items-center justify-center font-bold text-center border shadow-xs {{ $isCuti ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                        <span class="text-base font-extrabold leading-none">{{ $hl->tanggal->format('d') }}</span>
                        <span class="text-[9px] uppercase tracking-wider font-extrabold mt-0.5">{{ $hl->tanggal->translatedFormat('M') }}</span>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-slate-800 leading-snug">{{ $hl->nama }}</h3>
                        <div class="flex items-center space-x-2 mt-1">
                            <span class="text-[10px] text-slate-400 font-medium">{{ $hl->tanggal->translatedFormat('l') }}</span>
                            <span class="text-slate-300">•</span>
                            <span class="text-[9px] px-2 py-0.5 rounded-full font-bold uppercase {{ $isCuti ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $isCuti ? 'Cuti Bersama' : 'Libur Nasional' }}
                            </span>
                        </div>
                    </div>
                </div>

                @if($isToday)
                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-xl text-[10px] font-black uppercase tracking-wider animate-pulse">
                        Hari Ini
                    </span>
                @elseif(!$isPassed)
                    <div class="text-right">
                        <span class="text-[11px] font-bold text-sky-600 block">
                            {{ now()->diffInDays($hl->tanggal, false) }} hr lagi
                        </span>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-3xl p-8 border border-slate-100 text-center shadow-xs">
                <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center text-xl mx-auto mb-2">
                    <i class="fa-regular fa-calendar-xmark"></i>
                </div>
                <h4 class="text-xs font-bold text-slate-700">Tidak ada hari libur</h4>
                <p class="text-[11px] text-slate-400 mt-1">Belum ada jadwal hari libur yang tercatat pada filter ini untuk tahun {{ $tahun }}.</p>
            </div>
        @endforelse
    </div>
</div>
