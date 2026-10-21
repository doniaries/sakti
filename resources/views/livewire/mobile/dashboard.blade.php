<div class="p-5 pb-20">
    <div class="flex justify-between items-center mb-5">
        <h1 class="text-xl font-bold text-gray-800">Dashboard</h1>
        <button wire:click="logout" class="text-gray-500 hover:text-red-500 transition">
            <i class="fa-solid fa-right-from-bracket text-lg"></i>
        </button>
    </div>

    <!-- Greeting Card -->
    <div class="w-full p-5 rounded-3xl bg-gradient-to-br from-[#0284C7] via-[#0369A1] to-[#075985] text-white shadow-xl shadow-sky-950/15 mb-6 relative overflow-hidden border border-white/10">
        <!-- Background Glow Accent -->
        <div class="absolute -right-8 -top-8 w-36 h-36 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

        @php
            $hour = now()->hour;
            $greeting = $hour < 12 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
        @endphp

        <div class="flex justify-between items-start">
            <div>
                <span class="text-xs font-semibold text-sky-200 tracking-wider uppercase">Panel Pegawai</span>
                <h2 class="text-xl font-extrabold tracking-tight mt-0.5">{{ $greeting }}, {{ explode(' ', $user->name)[0] }}!</h2>
                
                <!-- Tanggal & Jam Aktif Server -->
                <div class="flex items-center flex-wrap gap-2 text-xs text-white/85 mt-2" 
                     x-data="{ 
                         hours: '--', 
                         minutes: '--', 
                         colon: true,
                         updateClock() {
                             const d = new Date();
                             this.hours = String(d.getHours()).padStart(2, '0');
                             this.minutes = String(d.getMinutes()).padStart(2, '0');
                             this.colon = !this.colon;
                         }
                     }" 
                     x-init="updateClock(); setInterval(() => updateClock(), 1000);">
                    <span class="flex items-center font-medium">
                        <i class="fa-regular fa-calendar text-sky-300 mr-1.5"></i>
                        {{ now()->translatedFormat('l, d F Y') }}
                    </span>
                    <span class="text-white/40">•</span>
                    <!-- Jam Aktif Server Real-Time (HH:MM WIB) -->
                    <span class="flex items-center bg-black/25 backdrop-blur-md px-2.5 py-1 rounded-full font-mono text-[11px] font-bold text-amber-300 border border-white/10 shadow-xs">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 inline-block"></span>
                        <span x-text="hours">--</span><span class="inline-block transition-opacity duration-200" :class="colon ? 'opacity-100' : 'opacity-20'">:</span><span x-text="minutes">--</span>&nbsp;WIB
                    </span>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-2 gap-2.5 mt-5">
            <!-- Masuk -->
            <div class="flex items-center p-3 rounded-2xl border border-white/20 {{ $hariIni['sudah_masuk'] ? 'bg-white/20 backdrop-blur-sm' : 'bg-black/15' }}">
                <div class="w-8 h-8 rounded-xl {{ $hariIni['sudah_masuk'] ? 'bg-emerald-400 text-slate-900' : 'bg-white/15 text-white/50' }} flex items-center justify-center text-sm shadow-xs">
                    <i class="fa-solid fa-right-to-bracket"></i>
                </div>
                <div class="ml-2.5 flex flex-col">
                    <span class="text-[10px] text-white/70 uppercase font-bold tracking-wider">Absen Masuk</span>
                    <span class="text-sm font-extrabold font-mono {{ $hariIni['sudah_masuk'] ? 'text-white' : 'text-white/40' }}">{{ $hariIni['jam_masuk'] ?? '--:--' }}</span>
                </div>
            </div>
            <!-- Keluar -->
            <div class="flex items-center p-3 rounded-2xl border border-white/20 {{ $hariIni['sudah_pulang'] ? 'bg-white/20 backdrop-blur-sm' : 'bg-black/15' }}">
                <div class="w-8 h-8 rounded-xl {{ $hariIni['sudah_pulang'] ? 'bg-sky-400 text-slate-900' : 'bg-white/15 text-white/50' }} flex items-center justify-center text-sm shadow-xs">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </div>
                <div class="ml-2.5 flex flex-col">
                    <span class="text-[10px] text-white/70 uppercase font-bold tracking-wider">Absen Keluar</span>
                    <span class="text-sm font-extrabold font-mono {{ $hariIni['sudah_pulang'] ? 'text-white' : 'text-white/40' }}">{{ $hariIni['jam_pulang'] ?? '--:--' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Bulan Ini -->
    <div class="flex justify-between items-center mb-3">
        <h3 class="text-base font-extrabold text-slate-800 tracking-tight">Statistik Kehadiran Bulan Ini</h3>
        <span class="text-[11px] font-bold text-sky-600 bg-sky-50 px-2.5 py-0.5 rounded-full border border-sky-100">{{ now()->translatedFormat('F Y') }}</span>
    </div>

    <div class="grid grid-cols-4 gap-2 mb-6">
        <!-- 1. Hadir -->
        <div class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center">
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm mb-1.5">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <span class="text-lg font-black text-slate-800">{{ $statistik['hadir'] }}</span>
            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Hadir</span>
        </div>

        <!-- 2. Terlambat -->
        <div class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center">
            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm mb-1.5">
                <i class="fa-solid fa-clock"></i>
            </div>
            <span class="text-lg font-black text-slate-800">{{ $statistik['terlambat'] }}</span>
            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Terlambat</span>
        </div>

        <!-- 3. Dinas Luar -->
        <div class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center">
            <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-sm mb-1.5">
                <i class="fa-solid fa-briefcase"></i>
            </div>
            <span class="text-lg font-black text-slate-800">{{ $statistik['dinas_luar'] }}</span>
            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Dinas Luar</span>
        </div>

        <!-- 4. Pulang Cepat -->
        <div class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center">
            <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm mb-1.5">
                <i class="fa-solid fa-person-walking-dashed-line-arrow-right"></i>
            </div>
            <span class="text-lg font-black text-slate-800">{{ $statistik['pulang_cepat'] }}</span>
            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Plg Cepat</span>
        </div>

        <!-- 5. Izin -->
        <div class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center">
            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm mb-1.5">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <span class="text-lg font-black text-slate-800">{{ $statistik['izin'] }}</span>
            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Izin</span>
        </div>

        <!-- 6. Sakit -->
        <div class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center">
            <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm mb-1.5">
                <i class="fa-solid fa-notes-medical"></i>
            </div>
            <span class="text-lg font-black text-slate-800">{{ $statistik['sakit'] }}</span>
            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Sakit</span>
        </div>

        <!-- 7. Tanpa Keterangan / Alpa -->
        <div class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center">
            <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-sm mb-1.5">
                <i class="fa-solid fa-user-xmark"></i>
            </div>
            <span class="text-lg font-black text-rose-600">{{ $statistik['tanpa_keterangan'] }}</span>
            <span class="text-[9px] text-rose-500 font-bold uppercase tracking-wide">Alpa</span>
        </div>

        <!-- 8. LHK Dibuat -->
        <div class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center">
            <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-sm mb-1.5">
                <i class="fa-solid fa-file-signature"></i>
            </div>
            <span class="text-lg font-black text-teal-700">{{ $statistik['lhk_dibuat'] }}</span>
            <span class="text-[9px] text-teal-600 font-bold uppercase tracking-wide">LHK</span>
        </div>
    </div>

    <!-- Quick Actions Khusus LHK -->
    <h3 class="text-base font-extrabold text-slate-800 tracking-tight mb-3">Laporan Harian Kinerja (LHK)</h3>
    <div class="grid grid-cols-2 gap-3 mb-6">
        <!-- 1. Jumlah LHK yang Telah Dibuat -->
        <a href="{{ route('mobile.lhk.index') }}" wire:navigate class="bg-gradient-to-br from-teal-50 to-emerald-50/60 border border-teal-200/60 rounded-2xl p-4 flex items-center justify-between transition-all active:scale-95 shadow-xs">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center text-lg shadow-xs">
                    <i class="fa-solid fa-file-signature"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-teal-700 uppercase tracking-wider">LHK Dibuat</span>
                    <span class="text-xs text-slate-500 font-medium">Bulan Ini</span>
                </div>
            </div>
            <span class="text-xl font-black text-teal-800 font-mono">{{ $statistik['lhk_dibuat'] }}</span>
        </a>

        <!-- 2. Tombol Buat LHK Baru -->
        <a href="{{ route('mobile.lhk.create') }}" wire:navigate class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white rounded-2xl p-4 flex items-center justify-between transition-all active:scale-95 shadow-md shadow-emerald-700/20">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-lg text-white">
                    <i class="fa-solid fa-circle-plus"></i>
                </div>
                <div class="flex flex-col text-left">
                    <span class="text-xs font-black tracking-wide">Buat LHK</span>
                    <span class="text-[10px] text-white/80 font-medium">Isi Pekerjaan</span>
                </div>
            </div>
            <i class="fa-solid fa-arrow-right text-xs text-white/70"></i>
        </a>
    </div>

    <!-- Grafik Sederhana 7 Hari -->
    <h3 class="text-lg font-bold text-gray-800 mb-3">7 Hari Terakhir</h3>
    <div class="bg-white p-4 rounded-2xl shadow-sm h-40 flex items-end justify-between gap-1">
        @foreach($riwayat7Hari as $item)
            @php
                $h = 10;
                $color = 'bg-red-400';
                if($item['status'] == 'hadir' || $item['status'] == 'terlambat') {
                    $color = 'bg-[#2D9B4F]';
                    if($item['jam_masuk']) {
                        $jam = (int) explode(':', $item['jam_masuk'])[0];
                        $h = max(20, (18 - $jam) * 10);
                    } else {
                        $h = 80;
                    }
                } elseif($item['status'] == 'izin') {
                    $color = 'bg-[#0EA5E9]'; $h = 60;
                } elseif($item['status'] == 'sakit') {
                    $color = 'bg-[#8B5CF6]'; $h = 60;
                }
            @endphp
            <div class="flex flex-col items-center w-full">
                <div class="w-full max-w-[20px] rounded-md {{ $color }}" style="height: {{ $h }}%;"></div>
                <span class="text-[10px] text-gray-500 mt-2 font-medium">{{ $item['hari'] }}</span>
            </div>
        @endforeach
    </div>
</div>
