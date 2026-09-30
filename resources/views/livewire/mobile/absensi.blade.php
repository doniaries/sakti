<div class="p-5 pb-20 flex flex-col h-full" x-data="absensiApp()">
    <div class="flex justify-center items-center mb-6 pt-2">
        <h1 class="text-lg font-bold text-gray-800">Absensi Kehadiran</h1>
    </div>

    <!-- Info Card -->
    <div class="w-full p-5 bg-white rounded-2xl shadow-sm mb-8 flex justify-between relative overflow-hidden">
        <div class="flex-1 flex flex-col items-center">
            <i class="fa-solid fa-right-to-bracket text-[#F59E0B] text-2xl mb-2"></i>
            <span class="text-xs text-gray-400 mb-1">Jam Masuk</span>
            <span class="text-lg font-bold {{ $absenHarian && $absenHarian->jam_masuk ? 'text-[#F59E0B]' : 'text-gray-300' }}">
                {{ $absenHarian->jam_masuk ?? '-' }}
            </span>
        </div>
        <div class="w-px bg-gray-100 my-2"></div>
        <div class="flex-1 flex flex-col items-center">
            <i class="fa-solid fa-right-from-bracket text-[#2D9B4F] text-2xl mb-2"></i>
            <span class="text-xs text-gray-400 mb-1">Jam Pulang</span>
            <span class="text-lg font-bold {{ $absenHarian && $absenHarian->jam_pulang ? 'text-[#2D9B4F]' : 'text-gray-300' }}">
                {{ $absenHarian->jam_pulang ?? '-' }}
            </span>
        </div>
    </div>

    <!-- Pulse Button -->
    <div class="flex-1 flex flex-col items-center justify-center">
        @if($selesai)
            <div class="w-48 h-48 rounded-full bg-gray-200 flex flex-col items-center justify-center shadow-inner">
                <i class="fa-solid fa-fingerprint text-gray-400 text-6xl mb-2"></i>
                <span class="text-gray-500 font-semibold">Selesai</span>
            </div>
        @else
            @php
                $btnColor = $sudahMasuk ? 'bg-[#2D9B4F] shadow-[#2D9B4F]/40' : 'bg-[#1B6CA8] shadow-[#1B6CA8]/40';
                $btnLabel = $sudahMasuk ? 'Tap Absen Pulang' : 'Tap Absen Masuk';
            @endphp
            
            <button @click="doAbsen()" 
                    :disabled="loading"
                    class="w-48 h-48 rounded-full {{ $btnColor }} text-white flex flex-col items-center justify-center shadow-[0_0_30px_5px] transition-transform active:scale-95 relative overflow-hidden group">
                
                <!-- Loading state -->
                <div x-show="loading" class="absolute inset-0 flex items-center justify-center bg-black/20">
                    <i class="fa-solid fa-circle-notch fa-spin text-4xl"></i>
                </div>
                
                <i class="fa-solid fa-fingerprint text-6xl mb-2" x-show="!loading"></i>
                <span class="font-semibold" x-show="!loading">{{ $btnLabel }}</span>
            </button>
        @endif
    </div>

    <!-- GPS Info -->
    <div class="flex justify-center items-center mt-auto pt-8 text-gray-400">
        <i class="fa-solid fa-location-dot text-sm mr-2"></i>
        <span class="text-xs">Validasi GPS Aktif</span>
    </div>

    <script>
        function absensiApp() {
            return {
                loading: false,
                doAbsen() {
                    if (this.loading) return;
                    this.loading = true;
                    
                    if (!navigator.geolocation) {
                        Swal.fire('Error', 'Browser tidak mendukung Geolocation.', 'error');
                        this.loading = false;
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            @this.punch(position.coords.latitude, position.coords.longitude).then(() => {
                                this.loading = false;
                            });
                        },
                        (error) => {
                            let msg = 'Gagal mendapatkan lokasi.';
                            if(error.code == 1) msg = 'Akses lokasi ditolak.';
                            Swal.fire('Error GPS', msg, 'error');
                            this.loading = false;
                        },
                        { enableHighAccuracy: true, timeout: 10000 }
                    );
                }
            }
        }
    </script>
</div>
