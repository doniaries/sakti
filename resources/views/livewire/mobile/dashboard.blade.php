<div class="p-5 pb-20">
    <div class="flex justify-between items-center mb-5">
        <h1 class="text-xl font-bold text-gray-800">Dashboard</h1>
        <button wire:click="logout" class="text-gray-500 hover:text-red-500 transition">
            <i class="fa-solid fa-right-from-bracket text-lg"></i>
        </button>
    </div>

    <!-- Greeting Card -->
    <div class="w-full p-5 rounded-2xl bg-gradient-to-br from-[#1B6CA8] to-[#00B4D8] text-white shadow-lg mb-6">
        @php
            $hour = now()->hour;
            $greeting = $hour < 12 ? 'Selamat Pagi' : ($hour < 17 ? 'Selamat Siang' : 'Selamat Sore');
        @endphp
        <h2 class="text-xl font-bold">{{ $greeting }}, {{ explode(' ', $user->name)[0] }}!</h2>
        <p class="text-white/80 text-sm mt-1">{{ now()->translatedFormat('l, d F Y') }}</p>
        
        <div class="flex space-x-3 mt-4">
            <!-- Masuk -->
            <div class="flex items-center px-3 py-2 rounded-full border border-white/30 {{ $hariIni['sudah_masuk'] ? 'bg-white/25' : 'bg-white/10' }}">
                <i class="fa-solid {{ $hariIni['sudah_masuk'] ? 'fa-circle-check' : 'fa-circle-xmark' }} text-white text-sm"></i>
                <div class="ml-2 flex flex-col">
                    <span class="text-[10px] text-white/70 uppercase tracking-wider">Masuk</span>
                    <span class="text-sm font-semibold">{{ $hariIni['jam_masuk'] ?? '-' }}</span>
                </div>
            </div>
            <!-- Pulang -->
            <div class="flex items-center px-3 py-2 rounded-full border border-white/30 {{ $hariIni['sudah_pulang'] ? 'bg-white/25' : 'bg-white/10' }}">
                <i class="fa-solid {{ $hariIni['sudah_pulang'] ? 'fa-circle-check' : 'fa-circle-xmark' }} text-white text-sm"></i>
                <div class="ml-2 flex flex-col">
                    <span class="text-[10px] text-white/70 uppercase tracking-wider">Pulang</span>
                    <span class="text-sm font-semibold">{{ $hariIni['jam_pulang'] ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik -->
    <h3 class="text-lg font-bold text-gray-800 mb-3">Statistik Bulan Ini</h3>
    <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-white p-3 rounded-2xl shadow-sm text-center flex flex-col items-center">
            <i class="fa-solid fa-circle-check text-[#2D9B4F] text-2xl mb-1"></i>
            <span class="text-xl font-bold text-[#2D9B4F]">{{ $statistik['hadir'] }}</span>
            <span class="text-[10px] text-gray-500 uppercase">Hadir</span>
        </div>
        <div class="bg-white p-3 rounded-2xl shadow-sm text-center flex flex-col items-center">
            <i class="fa-solid fa-clock text-[#F59E0B] text-2xl mb-1"></i>
            <span class="text-xl font-bold text-[#F59E0B]">{{ $statistik['terlambat'] }}</span>
            <span class="text-[10px] text-gray-500 uppercase">Terlambat</span>
        </div>
        <div class="bg-white p-3 rounded-2xl shadow-sm text-center flex flex-col items-center">
            <i class="fa-solid fa-calendar-check text-[#0EA5E9] text-2xl mb-1"></i>
            <span class="text-xl font-bold text-[#0EA5E9]">{{ $statistik['izin'] }}</span>
            <span class="text-[10px] text-gray-500 uppercase">Izin</span>
        </div>
        <div class="bg-white p-3 rounded-2xl shadow-sm text-center flex flex-col items-center">
            <i class="fa-solid fa-notes-medical text-[#8B5CF6] text-2xl mb-1"></i>
            <span class="text-xl font-bold text-[#8B5CF6]">{{ $statistik['sakit'] }}</span>
            <span class="text-[10px] text-gray-500 uppercase">Sakit</span>
        </div>
        <div class="bg-white p-3 rounded-2xl shadow-sm text-center flex flex-col items-center col-span-2">
            <i class="fa-solid fa-file-signature text-[#1B6CA8] text-2xl mb-1"></i>
            <span class="text-xl font-bold text-[#1B6CA8]">{{ $statistik['lhk_dibuat'] }}</span>
            <span class="text-[10px] text-gray-500 uppercase">LHK Dibuat</span>
        </div>
    </div>

    <!-- Quick Actions -->
    <h3 class="text-lg font-bold text-gray-800 mb-3">Aksi Cepat</h3>
    <div class="flex space-x-3 mb-6">
        <a href="{{ route('mobile.absensi') }}" wire:navigate class="flex-1 bg-[#1B6CA8]/10 border border-[#1B6CA8]/30 rounded-xl p-4 flex items-center transition active:bg-[#1B6CA8]/20">
            <i class="fa-solid fa-fingerprint text-[#1B6CA8] text-xl"></i>
            <span class="ml-3 font-semibold text-[#1B6CA8] text-sm">Absen Sekarang</span>
        </a>
        <a href="{{ route('mobile.lhk.create') }}" wire:navigate class="flex-1 bg-[#2D9B4F]/10 border border-[#2D9B4F]/30 rounded-xl p-4 flex items-center transition active:bg-[#2D9B4F]/20">
            <i class="fa-solid fa-circle-plus text-[#2D9B4F] text-xl"></i>
            <span class="ml-3 font-semibold text-[#2D9B4F] text-sm">Buat LHK</span>
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
