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

        <!-- Year Switcher & Sync Button (Sync Khusus Superadmin & Admin) -->
        <div class="flex items-center space-x-2">
            @if($isAdmin)
                <button wire:click="sinkronkanApi" wire:loading.attr="disabled"
                        class="w-9 h-9 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-center text-sky-600 hover:text-sky-700 active:scale-95 transition-all cursor-pointer"
                        title="Tarik & Sinkronkan Data Libur dari API Resmi (Khusus Admin)">
                    <i class="fa-solid fa-arrows-rotate text-xs" wire:loading.class="animate-spin" wire:target="sinkronkanApi"></i>
                </button>
            @endif
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
    </div>

    <!-- Filter Jenis dengan Count Badge -->
    <div class="flex space-x-2 mb-5 overflow-x-auto pb-1 no-scrollbar">
        <!-- Semua -->
        <button wire:click="setFilterJenis('semua')" 
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-xs flex items-center whitespace-nowrap {{ $filterJenis === 'semua' ? 'bg-slate-800 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            <span>Semua</span>
            <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $filterJenis === 'semua' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">
                {{ $countSemua }}
            </span>
        </button>

        <!-- Libur Nasional -->
        <button wire:click="setFilterJenis('libur_nasional')" 
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-xs flex items-center whitespace-nowrap {{ ($filterJenis === 'libur_nasional' || $filterJenis === 'nasional') ? 'bg-rose-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            <i class="fa-solid fa-calendar-xmark mr-1.5 text-[10px] {{ ($filterJenis === 'libur_nasional' || $filterJenis === 'nasional') ? 'text-white' : 'text-rose-500' }}"></i>
            <span>Libur Nasional</span>
            <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-[10px] font-black {{ ($filterJenis === 'libur_nasional' || $filterJenis === 'nasional') ? 'bg-white/20 text-white' : 'bg-rose-50 text-rose-700' }}">
                {{ $countNasional }}
            </span>
        </button>

        <!-- Cuti Bersama -->
        <button wire:click="setFilterJenis('cuti_bersama')" 
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-xs flex items-center whitespace-nowrap {{ $filterJenis === 'cuti_bersama' ? 'bg-amber-500 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            <i class="fa-solid fa-umbrella-beach mr-1.5 text-[10px] {{ $filterJenis === 'cuti_bersama' ? 'text-white' : 'text-amber-500' }}"></i>
            <span>Cuti Bersama</span>
            <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-[10px] font-black {{ $filterJenis === 'cuti_bersama' ? 'bg-white/20 text-white' : 'bg-amber-50 text-amber-700' }}">
                {{ $countCutiBersama }}
            </span>
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
                @endif
            </div>
        @empty
            <div class="bg-white rounded-3xl p-8 border border-slate-100 text-center shadow-xs">
                <div class="w-14 h-14 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center text-xl mx-auto mb-2">
                    <i class="fa-regular fa-calendar-xmark"></i>
                </div>
                <h4 class="text-xs font-bold text-slate-700">Tidak ada hari libur</h4>
                <p class="text-[11px] text-slate-400 mt-1">Belum ada jadwal hari libur yang tercatat pada filter ini untuk tahun {{ $tahun }}.</p>
                @if($isAdmin)
                    <button wire:click="sinkronkanApi" wire:loading.attr="disabled"
                            class="mt-3 px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold inline-flex items-center space-x-1.5 shadow-sm">
                        <i class="fa-solid fa-cloud-arrow-down" wire:loading.class="animate-bounce" wire:target="sinkronkanApi"></i>
                        <span>Tarik Data Otomatis dari API</span>
                    </button>
                @endif
            </div>
        @endforelse
    </div>
</div>
