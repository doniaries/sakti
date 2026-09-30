<div class="p-5 pb-24 relative min-h-screen">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold text-gray-800">Laporan LHK</h1>
    </div>

    @if($lhks->isEmpty())
        <div class="flex flex-col items-center justify-center h-[50vh] text-gray-400">
            <i class="fa-solid fa-file-signature text-6xl mb-4 opacity-50"></i>
            <p>Belum ada LHK yang dibuat.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($lhks as $lhk)
                @php
                    $status = $lhk->status_kehadiran;
                    $color = match($status) {
                        'Hadir' => 'text-[#2D9B4F] bg-[#2D9B4F]/10',
                        'Sakit' => 'text-[#8B5CF6] bg-[#8B5CF6]/10',
                        'Izin' => 'text-[#0EA5E9] bg-[#0EA5E9]/10',
                        'Dinas Luar' => 'text-[#F59E0B] bg-[#F59E0B]/10',
                        default => 'text-gray-500 bg-gray-100',
                    };
                    $iconColor = match($status) {
                        'Hadir' => 'text-[#2D9B4F]',
                        'Sakit' => 'text-[#8B5CF6]',
                        'Izin' => 'text-[#0EA5E9]',
                        'Dinas Luar' => 'text-[#F59E0B]',
                        default => 'text-gray-500',
                    };
                @endphp
                
                <div class="bg-white p-4 rounded-2xl shadow-sm flex items-center">
                    <div class="w-12 h-12 rounded-full {{ $color }} flex justify-center items-center mr-4">
                        <i class="fa-solid fa-file-lines {{ $iconColor }} text-lg"></i>
                    </div>
                    
                    <div class="flex-1">
                        <h4 class="font-bold text-gray-800 text-sm mb-1">{{ \Carbon\Carbon::parse($lhk->tanggal)->translatedFormat('l, d M Y') }}</h4>
                        <div class="flex items-center text-[11px] text-gray-500">
                            <i class="fa-solid fa-briefcase mr-1.5"></i>
                            <span>{{ $lhk->pekerjaans->count() }} Kegiatan</span>
                        </div>
                    </div>
                    
                    <div class="px-2.5 py-1 rounded-full {{ $color }} text-[10px] font-bold uppercase">
                        {{ $status }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- FAB Buat LHK -->
    <a href="{{ route('mobile.lhk.create') }}" wire:navigate class="fixed bottom-[90px] right-5 bg-[#1B6CA8] text-white px-5 py-3 rounded-full shadow-lg shadow-[#1B6CA8]/40 flex items-center space-x-2 active:scale-95 transition-transform z-40">
        <i class="fa-solid fa-plus"></i>
        <span class="font-semibold text-sm">Buat LHK</span>
    </a>
</div>
