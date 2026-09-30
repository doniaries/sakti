<div class="p-5 pb-20">
    <!-- Header with Month Picker -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold text-gray-800">Riwayat</h1>
        <div class="flex items-center space-x-3 bg-white px-3 py-1.5 rounded-full shadow-sm border border-gray-100">
            <button wire:click="prevMonth" class="text-gray-400 hover:text-[#1B6CA8]"><i class="fa-solid fa-chevron-left text-sm"></i></button>
            <span class="text-sm font-semibold text-gray-700 w-24 text-center">{{ $bulanNama }}</span>
            <button wire:click="nextMonth" class="text-gray-400 hover:text-[#1B6CA8]"><i class="fa-solid fa-chevron-right text-sm"></i></button>
        </div>
    </div>

    @if($riwayat->isEmpty())
        <div class="flex flex-col items-center justify-center h-64 text-gray-400">
            <i class="fa-solid fa-calendar-xmark text-6xl mb-4 opacity-50"></i>
            <p>Tidak ada data absensi bulan ini.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($riwayat as $item)
                @php
                    $status = $item->status;
                    $color = match($status) {
                        'hadir' => 'text-[#2D9B4F] bg-[#2D9B4F]/10',
                        'terlambat' => 'text-[#F59E0B] bg-[#F59E0B]/10',
                        'izin' => 'text-[#0EA5E9] bg-[#0EA5E9]/10',
                        'sakit' => 'text-[#8B5CF6] bg-[#8B5CF6]/10',
                        default => 'text-red-500 bg-red-100',
                    };
                    $iconColor = match($status) {
                        'hadir' => 'text-[#2D9B4F]',
                        'terlambat' => 'text-[#F59E0B]',
                        'izin' => 'text-[#0EA5E9]',
                        'sakit' => 'text-[#8B5CF6]',
                        default => 'text-red-500',
                    };
                @endphp
                
                <div class="bg-white p-4 rounded-2xl shadow-sm flex items-center">
                    <div class="w-12 h-12 rounded-full {{ $color }} flex justify-center items-center mr-4">
                        <i class="fa-solid fa-calendar-day {{ $iconColor }} text-lg"></i>
                    </div>
                    
                    <div class="flex-1">
                        <h4 class="font-bold text-gray-800 text-sm mb-1">{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('l, d M Y') }}</h4>
                        <div class="flex space-x-3 text-[11px] text-gray-500">
                            <span><i class="fa-solid fa-circle text-[#F59E0B] text-[8px] mr-1"></i> Masuk: {{ $item->jam_masuk ?? '-' }}</span>
                            <span><i class="fa-solid fa-circle text-[#2D9B4F] text-[8px] mr-1"></i> Pulang: {{ $item->jam_pulang ?? '-' }}</span>
                        </div>
                    </div>
                    
                    <div class="px-2.5 py-1 rounded-full {{ $color }} text-[10px] font-bold uppercase">
                        {{ $status }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
