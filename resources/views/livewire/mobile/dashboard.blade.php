<div class="p-5 pb-20">
    @php
        $pengaturan = \App\Models\PengaturanAplikasi::getPengaturan();
    @endphp
    <!-- Top Header: Logo, Nama Aplikasi & Logout -->
    <div class="flex justify-between items-center mb-5">
        <div class="flex items-center space-x-2.5">
            @if(!empty($pengaturan->logo) && file_exists(public_path('storage/' . $pengaturan->logo)))
                <img src="{{ asset('storage/' . $pengaturan->logo) }}" 
                     alt="Logo Aplikasi" 
                     class="w-10 h-10 rounded-xl object-contain bg-white shadow-md shadow-sky-500/15 p-1 border border-slate-200/80">
            @else
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-600 via-sky-500 to-teal-400 text-white flex items-center justify-center shadow-md shadow-sky-500/25 border border-white/20">
                    <i class="fa-solid fa-leaf text-lg"></i>
                </div>
            @endif
            <div>
                <h1 class="text-lg font-black text-slate-800 tracking-tight leading-tight uppercase">{{ $pengaturan->nama_aplikasi }}</h1>
                <p class="text-[10px] font-bold text-sky-600 tracking-wider uppercase">{{ $pengaturan->sub_nama ?? 'Dinas Lingkungan Hidup' }}</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            @if(!empty($user->whatsapp_url))
                <a href="{{ $user->whatsapp_url }}" target="_blank" rel="noopener noreferrer" 
                   class="px-2.5 py-1.5 rounded-xl bg-emerald-500/15 text-emerald-700 hover:bg-emerald-500 hover:text-white border border-emerald-300 font-bold text-[11px] flex items-center transition shadow-xs active:scale-95" 
                   title="Buka WhatsApp">
                    <i class="fa-brands fa-whatsapp text-sm mr-1 text-emerald-600"></i> WA
                </a>
            @endif
            @if(auth()->user() && auth()->user()->hasAnyRole(['super_admin', 'admin']))
                <a href="{{ url('/admin') }}" class="px-2.5 py-1.5 rounded-xl bg-amber-500/15 text-amber-700 hover:bg-amber-500 hover:text-white border border-amber-300 font-bold text-[11px] flex items-center transition shadow-xs" title="Buka Panel Admin">
                    <i class="fa-solid fa-shield-halved mr-1"></i> Admin
                </a>
            @endif
            <button wire:click="logout" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-500 hover:text-rose-600 transition flex items-center justify-center border border-slate-200/60" title="Keluar">
                <i class="fa-solid fa-right-from-bracket text-sm"></i>
            </button>
        </div>
    </div>

    <!-- Greeting Card -->
    <div class="w-full p-5 rounded-3xl bg-gradient-to-br from-[#0F172A] via-[#0369A1] to-[#0284C7] text-white shadow-xl shadow-sky-950/20 mb-6 relative overflow-hidden border border-white/15">
        <!-- Background Glow Accent & Decorative Watermark Icon -->
        <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-sky-400/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -right-3 -top-3 text-white/[0.07] text-8xl pointer-events-none">
            <i class="fa-solid fa-fingerprint"></i>
        </div>

        @php
            $hour = now()->hour;
            $greeting = $hour < 12 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
            $greetingIcon = $hour < 12 ? 'fa-sun text-amber-400' : ($hour < 15 ? 'fa-cloud-sun text-amber-300' : ($hour < 18 ? 'fa-cloud-sun text-orange-400' : 'fa-moon text-indigo-200'));
        @endphp

        <div class="flex justify-between items-start relative z-10">
            <div class="flex-1 pr-3">
                <div class="flex items-center space-x-1.5 text-xs font-bold text-sky-200 tracking-wide mb-1">
                    <i class="fa-solid {{ $greetingIcon }}"></i>
                    <span>{{ $greeting }},</span>
                </div>
                <h2 class="text-xl font-extrabold tracking-tight text-white drop-shadow-xs">{{ $user->name }}</h2>
                
                <!-- Tanggal & Jam Aktif Server -->
                <div class="flex items-center flex-wrap gap-2 text-xs text-white/90 mt-2.5" 
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
                    <span class="flex items-center bg-black/30 backdrop-blur-md px-2.5 py-1 rounded-full font-mono text-[11px] font-bold text-amber-300 border border-white/15 shadow-inner">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 inline-block animate-pulse"></span>
                        <span x-text="hours">--</span><span class="inline-block transition-opacity duration-200" :class="colon ? 'opacity-100' : 'opacity-20'">:</span><span x-text="minutes">--</span>&nbsp;WIB
                    </span>
                </div>
            </div>

            <!-- Foto Avatar Pegawai di Dashboard -->
            <a href="{{ route('mobile.profil') }}" wire:navigate class="relative block shrink-0 active:scale-95 transition-transform" title="Buka Profil">
                <img src="{{ $user->avatar_image_url }}" 
                     alt="{{ $user->name }}" 
                     class="w-14 h-14 rounded-2xl object-cover border-2 border-white/50 shadow-lg shadow-sky-950/40 aspect-square">
                <!-- Live Pulse / Radar Blinking Status Badge -->
                <span class="absolute -bottom-1 -right-1 flex h-4 w-4" title="Online / Aktif">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-400 border-2 border-slate-900 shadow-xs"></span>
                </span>
            </a>
        </div>
        
        <div class="grid grid-cols-2 gap-2.5 mt-5 relative z-10">
            <!-- Masuk -->
            <div class="flex items-center p-3 rounded-2xl border border-white/20 {{ $hariIni['sudah_masuk'] ? 'bg-white/20 backdrop-blur-md shadow-inner' : 'bg-black/25 backdrop-blur-xs' }}">
                <div class="w-9 h-9 rounded-xl {{ $hariIni['sudah_masuk'] ? 'bg-emerald-400 text-slate-900 shadow-md shadow-emerald-500/30' : 'bg-white/10 text-white/40' }} flex items-center justify-center text-sm shadow-xs transition-colors">
                    <i class="fa-solid fa-right-to-bracket"></i>
                </div>
                <div class="ml-2.5 flex flex-col">
                    <span class="text-[10px] text-white/70 uppercase font-bold tracking-wider">Absen Masuk</span>
                    <span class="text-sm font-extrabold font-mono {{ $hariIni['sudah_masuk'] ? 'text-white' : 'text-white/40' }}">{{ $hariIni['jam_masuk'] ?? '--:--' }}</span>
                </div>
            </div>
            <!-- Keluar -->
            <div class="flex items-center p-3 rounded-2xl border border-white/20 {{ $hariIni['sudah_pulang'] ? 'bg-white/20 backdrop-blur-md shadow-inner' : 'bg-black/25 backdrop-blur-xs' }}">
                <div class="w-9 h-9 rounded-xl {{ $hariIni['sudah_pulang'] ? 'bg-sky-400 text-slate-900 shadow-md shadow-sky-500/30' : 'bg-white/10 text-white/40' }} flex items-center justify-center text-sm shadow-xs transition-colors">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </div>
                <div class="ml-2.5 flex flex-col">
                    <span class="text-[10px] text-white/70 uppercase font-bold tracking-wider">Absen Keluar</span>
                    <span class="text-sm font-extrabold font-mono {{ $hariIni['sudah_pulang'] ? 'text-white' : 'text-white/40' }}">{{ $hariIni['jam_pulang'] ?? '--:--' }}</span>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($user->whatsapp_url))
        <!-- Card / Tombol WhatsApp Pegawai -->
        <div class="mb-6 bg-gradient-to-r from-emerald-500/10 via-emerald-500/5 to-teal-500/10 border border-emerald-300/70 rounded-2xl p-3.5 flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-lg shadow-sm">
                    <i class="fa-brands fa-whatsapp"></i>
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-800">WhatsApp Terhubung</div>
                    <div class="text-[11px] text-emerald-700 font-mono font-medium">{{ $user->no_hp }}</div>
                </div>
            </div>
            <a href="{{ $user->whatsapp_url }}" target="_blank" rel="noopener noreferrer" 
               class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs active:scale-95 transition-all flex items-center space-x-1.5">
                <span>Chat WA</span>
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
            </a>
        </div>
    @endif

    <!-- Statistik Kehadiran dengan Filter Bulan & Tahun Interaktif -->
    <div class="flex justify-between items-center mb-3">
        <h3 class="text-base font-extrabold text-slate-800 tracking-tight">Statistik Kehadiran</h3>
        <!-- Interactive Month & Year Navigator -->
        <div class="flex items-center space-x-1.5 bg-white px-2 py-1 rounded-2xl shadow-xs border border-slate-200/80">
            <button wire:click="prevMonth" class="w-6 h-6 rounded-lg bg-slate-50 hover:bg-sky-50 text-slate-500 hover:text-sky-600 flex items-center justify-center transition-colors active:scale-90" title="Bulan Sebelumnya">
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </button>
            <span class="text-xs font-extrabold text-sky-700 px-1 select-none">
                {{ $bulanNama }}
            </span>
            <button wire:click="nextMonth" class="w-6 h-6 rounded-lg bg-slate-50 hover:bg-sky-50 text-slate-500 hover:text-sky-600 flex items-center justify-center transition-colors active:scale-90" title="Bulan Berikutnya">
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-4 gap-2 mb-6">
        <!-- 1. Hadir -->
        <a href="{{ route('mobile.riwayat', ['status' => 'hadir', 'bulan' => $bulan, 'tahun' => $tahun]) }}" wire:navigate 
           class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center hover:bg-slate-50 active:scale-95 transition-all">
            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm mb-1.5 shadow-xs">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <span class="text-lg font-black text-slate-800">{{ $statistik['hadir'] }}</span>
            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Hadir</span>
        </a>

        <!-- 2. Terlambat -->
        <a href="{{ route('mobile.riwayat', ['status' => 'terlambat', 'bulan' => $bulan, 'tahun' => $tahun]) }}" wire:navigate 
           class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center hover:bg-slate-50 active:scale-95 transition-all">
            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm mb-1.5 shadow-xs">
                <i class="fa-solid fa-clock"></i>
            </div>
            <span class="text-lg font-black text-slate-800">{{ $statistik['terlambat'] }}</span>
            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Terlambat</span>
        </a>

        <!-- 3. Dinas Luar -->
        <a href="{{ route('mobile.riwayat', ['status' => 'dinas_luar', 'bulan' => $bulan, 'tahun' => $tahun]) }}" wire:navigate 
           class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center hover:bg-slate-50 active:scale-95 transition-all">
            <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-sm mb-1.5 shadow-xs">
                <i class="fa-solid fa-briefcase"></i>
            </div>
            <span class="text-lg font-black text-slate-800">{{ $statistik['dinas_luar'] }}</span>
            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Dinas Luar</span>
        </a>

        <!-- 4. Pulang Cepat -->
        <a href="{{ route('mobile.riwayat', ['status' => 'pulang_cepat', 'bulan' => $bulan, 'tahun' => $tahun]) }}" wire:navigate 
           class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center hover:bg-slate-50 active:scale-95 transition-all">
            <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm mb-1.5 shadow-xs">
                <i class="fa-solid fa-person-walking-dashed-line-arrow-right"></i>
            </div>
            <span class="text-lg font-black text-slate-800">{{ $statistik['pulang_cepat'] }}</span>
            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Plg Cepat</span>
        </a>

        <!-- 5. Izin -->
        <a href="{{ route('mobile.riwayat', ['status' => 'izin', 'bulan' => $bulan, 'tahun' => $tahun]) }}" wire:navigate 
           class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center hover:bg-slate-50 active:scale-95 transition-all">
            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm mb-1.5 shadow-xs">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <span class="text-lg font-black text-slate-800">{{ $statistik['izin'] }}</span>
            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Izin</span>
        </a>

        <!-- 6. Sakit -->
        <a href="{{ route('mobile.riwayat', ['status' => 'sakit', 'bulan' => $bulan, 'tahun' => $tahun]) }}" wire:navigate 
           class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center hover:bg-slate-50 active:scale-95 transition-all">
            <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm mb-1.5 shadow-xs">
                <i class="fa-solid fa-notes-medical"></i>
            </div>
            <span class="text-lg font-black text-slate-800">{{ $statistik['sakit'] }}</span>
            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wide">Sakit</span>
        </a>

        <!-- 7. Tanpa Keterangan / Alpa -->
        <a href="{{ route('mobile.riwayat', ['status' => 'tidak_hadir', 'bulan' => $bulan, 'tahun' => $tahun]) }}" wire:navigate 
           class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center hover:bg-slate-50 active:scale-95 transition-all">
            <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-sm mb-1.5 shadow-xs">
                <i class="fa-solid fa-user-xmark"></i>
            </div>
            <span class="text-lg font-black text-rose-600">{{ $statistik['tanpa_keterangan'] }}</span>
            <span class="text-[9px] text-rose-500 font-bold uppercase tracking-wide">Alpa</span>
        </a>

        <!-- 8. LHK Dibuat -->
        <a href="{{ route('mobile.lhk.index') }}" wire:navigate 
           class="bg-white p-3 rounded-2xl shadow-xs border border-slate-100 text-center flex flex-col items-center hover:bg-slate-50 active:scale-95 transition-all">
            <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-sm mb-1.5 shadow-xs">
                <i class="fa-solid fa-file-signature"></i>
            </div>
            <span class="text-lg font-black text-teal-700">{{ $statistik['lhk_dibuat'] }}</span>
            <span class="text-[9px] text-teal-600 font-bold uppercase tracking-wide">LHK</span>
        </a>
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
