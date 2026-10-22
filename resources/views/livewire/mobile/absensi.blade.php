<div class="p-5 pb-24 flex flex-col min-h-[calc(100vh-100px)]" x-data="absensiApp()">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4 pt-1">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Presensi Kehadiran</h1>
            <p class="text-xs text-slate-500 font-medium">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        <div class="flex items-center space-x-1.5">
            <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider {{ $isWfh ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-sky-100 text-sky-800 border border-sky-300' }}">
                {{ $isWfh ? 'Pegawai WFH' : 'Pegawai WFO' }}
            </span>
            <div class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200/60 rounded-full text-[10px] font-bold flex items-center shadow-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse mr-1.5"></span>
                GPS ON
            </div>
        </div>
    </div>

    <!-- Live Digital Clock -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white rounded-2xl p-4 text-center shadow-md mb-4" x-data="{ time: '' }" x-init="setInterval(() => { const d = new Date(); time = d.toLocaleTimeString('id-ID', { hour12: false }); }, 1000); time = (new Date()).toLocaleTimeString('id-ID', { hour12: false });">
        <span class="text-xs font-semibold uppercase tracking-widest text-sky-400">Waktu Server Saat Ini</span>
        <div class="text-3xl font-extrabold font-mono tracking-wider mt-0.5" x-text="time">--:--:--</div>
        <div class="text-[11px] text-slate-400 mt-1">Waktu Indonesia Barat (WIB)</div>
    </div>

    <!-- Lokasi Kantor & Geofencing Card -->
    <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-200/80 mb-4">
        <div class="flex items-center justify-between mb-2">
            <div class="flex items-center space-x-2">
                <div class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-map-location-dot"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-slate-800 leading-tight">Geofencing Kantor</h3>
                    <p class="text-[10px] text-slate-400">{{ $alamatKantor }}</p>
                </div>
            </div>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $isWfh ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-600' }}">
                Radius: {{ $isWfh ? 'Bebas (WFH)' : $maxRadius . ' m' }}
            </span>
        </div>

        <!-- Real-Time Distance & Location Status -->
        <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
            <div class="flex items-center space-x-1.5">
                <span class="w-2 h-2 rounded-full" :class="isWithinRadius || isWfh ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                <span class="text-[11px] font-medium text-slate-600">Jarak ke kantor:</span>
                <span class="font-bold font-mono text-[11px]" :class="isWithinRadius || isWfh ? 'text-emerald-700' : 'text-rose-600'" x-text="currentDistanceText">Mendeteksi GPS...</span>
            </div>
            <div>
                <template x-if="isWfh">
                    <span class="text-[10px] font-bold text-sky-600 bg-sky-50 px-2 py-0.5 rounded-full">Bebas Radius</span>
                </template>
                <template x-if="!isWfh && isWithinRadius === true">
                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Dalam Radius</span>
                </template>
                <template x-if="!isWfh && isWithinRadius === false">
                    <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full">Luar Radius</span>
                </template>
            </div>
        </div>

        <!-- Admin Quick Setter: Set Office Location to My Current GPS -->
        @if($isAdmin)
            <div class="mt-3 pt-2.5 border-t border-dashed border-amber-200 bg-amber-50/50 p-2.5 rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-amber-900 block flex items-center">
                            <i class="fa-solid fa-wand-magic-sparkles text-amber-600 mr-1.5"></i>
                            Admin: Atur Lokasi Kantor Cepat
                        </span>
                        <span class="text-[10px] text-amber-700">Set koordinat kantor ke posisi GPS Anda saat ini</span>
                    </div>
                    <button @click="setOfficeLocation()" 
                            :disabled="settingOffice"
                            class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-[10px] rounded-lg shadow-xs transition active:scale-95 flex items-center space-x-1 shrink-0">
                        <i class="fa-solid fa-location-crosshairs" :class="{ 'fa-spin': settingOffice }"></i>
                        <span x-text="settingOffice ? 'Menyimpan...' : 'Set Ke Posisi Saya'">Set Ke Posisi Saya</span>
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Peringatan jika belum absen masuk pada jam pulang -->
    @php
        $nowTime = now()->format('H:i:s');
        $lewatJamMasuk = !$sudahMasuk && $nowTime >= '15:00:00';
    @endphp
    @if($lewatJamMasuk)
        <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl flex items-start space-x-3 shadow-xs">
            <i class="fa-solid fa-triangle-exclamation text-rose-500 text-xl mt-0.5"></i>
            <div class="text-xs leading-relaxed">
                <span class="font-bold block text-sm mb-0.5 text-rose-900">Perhatian: Absen Masuk Terlewat!</span>
                Anda tidak mengambil absen masuk pagi ini. Sistem mengunci pengambilan absen pulang otomatis dan status hari ini dinyatakan <strong>Tidak Hadir (Alpa)</strong>.
            </div>
        </div>
    @endif

    <!-- Info Card Masuk & Pulang -->
    <div class="w-full p-4 bg-white rounded-2xl shadow-xs border border-slate-100 mb-6 flex justify-between relative overflow-hidden">
        <div class="flex-1 flex flex-col items-center py-1">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg mb-2">
                <i class="fa-solid fa-right-to-bracket"></i>
            </div>
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Jam Masuk</span>
            <span class="text-base font-bold {{ $absenHarian && $absenHarian->jam_masuk ? 'text-amber-600' : 'text-slate-300' }}">
                {{ $absenHarian->jam_masuk ?? '--:--' }}
            </span>
        </div>
        <div class="w-px bg-slate-100 my-2"></div>
        <div class="flex-1 flex flex-col items-center py-1">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg mb-2">
                <i class="fa-solid fa-right-from-bracket"></i>
            </div>
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Jam Pulang</span>
            <span class="text-base font-bold {{ $absenHarian && $absenHarian->jam_pulang ? 'text-emerald-600' : 'text-slate-300' }}">
                {{ $absenHarian->jam_pulang ?? '--:--' }}
            </span>
        </div>
    </div>

    <!-- Pulse Punch Button -->
    <div class="flex-1 flex flex-col items-center justify-center my-2">
        @if($selesai)
            <div class="w-52 h-52 rounded-full bg-slate-100 border-4 border-slate-200 flex flex-col items-center justify-center shadow-inner text-center p-4">
                <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl mb-2">
                    <i class="fa-solid fa-check-double"></i>
                </div>
                <span class="text-slate-700 font-bold text-base">Presensi Lengkap</span>
                <span class="text-[11px] text-slate-400 mt-1">Masuk & Pulang tercatat</span>
            </div>
        @elseif($lewatJamMasuk)
            <div class="w-52 h-52 rounded-full bg-rose-50 border-4 border-rose-200 flex flex-col items-center justify-center shadow-xs text-center p-4">
                <i class="fa-solid fa-ban text-rose-500 text-5xl mb-2"></i>
                <span class="text-rose-700 font-bold text-sm">Tidak Dapat Absen</span>
                <span class="text-[11px] text-rose-500 mt-1">Absen masuk pagi terlewat</span>
            </div>
        @else
            @php
                $btnGradient = $sudahMasuk ? 'from-emerald-500 to-teal-600 shadow-emerald-500/30' : 'from-sky-500 to-blue-600 shadow-sky-500/30';
                $btnLabel = $sudahMasuk ? 'Tap Absen Pulang' : 'Tap Absen Masuk';
                $btnSub = $sudahMasuk ? 'Selesai tugas dinas' : 'Mulai tugas kedinasan';
            @endphp
            
            <button @click="doAbsen()" 
                    :disabled="loading"
                    class="w-52 h-52 rounded-full bg-gradient-to-tr {{ $btnGradient }} text-white flex flex-col items-center justify-center shadow-2xl transition-all duration-300 active:scale-95 relative overflow-hidden group border-4 border-white">
                
                <!-- Loading state -->
                <div x-show="loading" class="absolute inset-0 flex items-center justify-center bg-black/30 backdrop-blur-xs">
                    <i class="fa-solid fa-circle-notch fa-spin text-5xl text-white"></i>
                </div>
                
                <div class="w-16 h-16 rounded-full bg-white/20 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <i class="fa-solid fa-fingerprint text-4xl" x-show="!loading"></i>
                </div>
                <span class="font-extrabold text-base tracking-wide" x-show="!loading">{{ $btnLabel }}</span>
                <span class="text-[10px] text-white/80 font-medium mt-0.5" x-show="!loading">{{ $btnSub }}</span>
            </button>
        @endif
    </div>

    <!-- GPS Info & Radius Footer Note -->
    <div class="flex flex-col items-center mt-auto pt-4 text-slate-400 text-center">
        <div class="flex items-center text-xs font-semibold text-slate-500">
            <i class="fa-solid fa-location-crosshairs text-sky-500 text-sm mr-2"></i>
            <span>Geofencing Aktif: Titik ({{ round($officeLat, 4) }}, {{ round($officeLng, 4) }})</span>
        </div>
        <p class="text-[10px] text-slate-400 mt-0.5">Pastikan GPS ponsel aktif dengan akurasi tinggi.</p>
    </div>

    <script>
        function absensiApp() {
            return {
                loading: false,
                settingOffice: false,
                officeLat: {{ $officeLat }},
                officeLng: {{ $officeLng }},
                maxRadius: {{ $maxRadius }},
                isWfh: {{ $isWfh ? 'true' : 'false' }},
                currentDistance: null,
                currentDistanceText: 'Mendeteksi...',
                isWithinRadius: null,

                init() {
                    this.detectPosition();
                },

                detectPosition() {
                    if (!navigator.geolocation) return;
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            const d = this.calcDistance(this.officeLat, this.officeLng, pos.coords.latitude, pos.coords.longitude);
                            this.currentDistance = Math.round(d);
                            this.currentDistanceText = `${this.currentDistance} meter`;
                            this.isWithinRadius = (this.currentDistance <= this.maxRadius);
                        },
                        (err) => {
                            this.currentDistanceText = 'GPS belum aktif';
                        },
                        { enableHighAccuracy: true, timeout: 8000 }
                    );
                },

                calcDistance(lat1, lon1, lat2, lon2) {
                    const R = 6371000;
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLon = (lon2 - lon1) * Math.PI / 180;
                    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                              Math.sin(dLon/2) * Math.sin(dLon/2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                    return R * c;
                },

                doAbsen() {
                    if (this.loading) return;
                    this.loading = true;
                    
                    if (!navigator.geolocation) {
                        alert('Browser tidak mendukung Geolocation.');
                        this.loading = false;
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            @this.punch(position.coords.latitude, position.coords.longitude).then(() => {
                                this.loading = false;
                                this.detectPosition();
                            });
                        },
                        (error) => {
                            let msg = 'Gagal mendapatkan lokasi GPS.';
                            if (error.code == 1) msg = 'Akses lokasi ditolak oleh browser/ponsel. Mohon izinkan akses GPS.';
                            alert(msg);
                            this.loading = false;
                        },
                        { enableHighAccuracy: true, timeout: 10000 }
                    );
                },

                setOfficeLocation() {
                    if (this.settingOffice) return;
                    this.settingOffice = true;

                    if (!navigator.geolocation) {
                        alert('Browser tidak mendukung Geolocation.');
                        this.settingOffice = false;
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            @this.setLokasiKantorKeSaya(position.coords.latitude, position.coords.longitude).then(() => {
                                this.settingOffice = false;
                                this.officeLat = position.coords.latitude;
                                this.officeLng = position.coords.longitude;
                                this.detectPosition();
                            });
                        },
                        (error) => {
                            alert('Gagal mendeteksi posisi GPS Anda.');
                            this.settingOffice = false;
                        },
                        { enableHighAccuracy: true, timeout: 10000 }
                    );
                }
            }
        }
    </script>
</div>
