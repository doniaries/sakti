<div class="p-4 pb-24 flex flex-col min-h-[calc(100vh-100px)]" x-data="absensiApp()">
    <!-- Top Bar: Header & Status Pegawai -->
    <div class="flex items-center justify-between mb-3 pt-1">
        <div>
            <h1 class="text-lg font-extrabold text-slate-800 tracking-tight">Presensi Kehadiran</h1>
            <p class="text-[11px] text-slate-500 font-medium">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        <div class="flex items-center space-x-1.5">
            <span
                class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider {{ $isWfh ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-sky-100 text-sky-800 border border-sky-300' }}">
                {{ $isWfh ? 'WFH' : 'WFO' }}
            </span>
            <div
                class="px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200/60 rounded-full text-[10px] font-bold flex items-center shadow-xs">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse mr-1"></span>
                <span>GPS</span>
            </div>
        </div>
    </div>

    <!-- CARD UTAMA: WAKTU SERVER, JADWAL & LOKASI KANTOR (WARNA SAMA DENGAN DASHBOARD) -->
    <div class="bg-gradient-to-br from-[#0F172A] via-[#0369A1] to-[#0284C7] text-white rounded-3xl p-4.5 shadow-xl shadow-sky-950/20 mb-4 relative overflow-hidden border border-white/15"
        x-data="{ time: '' }" x-init="setInterval(() => {
            const d = new Date();
            time = d.toLocaleTimeString('id-ID', { hour12: false });
        }, 1000);
        time = (new Date()).toLocaleTimeString('id-ID', { hour12: false });">
        
        <!-- Background Glow Accent & Decorative Watermark Icon (Identik Dashboard) -->
        <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-sky-400/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -right-3 -top-3 text-white/[0.07] text-8xl pointer-events-none">
            <i class="fa-solid fa-fingerprint"></i>
        </div>
        
        <!-- Baris 1: Live Digital Clock -->
        <div class="flex items-center justify-between pb-3 border-b border-white/15 relative z-10">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-xl bg-white/15 backdrop-blur-md text-sky-200 flex items-center justify-center text-sm border border-white/20">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div>
                    <span class="text-[9px] uppercase tracking-wider text-sky-200/80 block font-semibold">Waktu Server</span>
                    <div class="text-2xl font-extrabold font-mono tracking-wider text-white leading-none mt-0.5" x-text="time">--:--:--</div>
                </div>
            </div>
            <div class="text-right">
                <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-black/25 backdrop-blur-md text-amber-300 border border-white/15">WIB</span>
            </div>
        </div>

        <!-- Baris 2: Jadwal & Toleransi (Tile Icon Ringkas) -->
        <div class="grid grid-cols-2 gap-2 py-2.5 border-b border-white/15 text-xs relative z-10">
            <!-- Jadwal Kerja -->
            <div class="bg-black/25 backdrop-blur-xs rounded-xl p-2 border border-white/15 flex items-center space-x-2">
                <div class="w-7 h-7 rounded-lg bg-sky-400/20 text-sky-300 flex items-center justify-center text-xs shrink-0">
                    <i class="fa-regular fa-calendar-check"></i>
                </div>
                <div class="min-w-0">
                    <span class="text-[9px] text-sky-200/70 block font-medium truncate">Jadwal Kerja</span>
                    <span class="font-bold font-mono text-[11px] text-white block truncate">{{ $jamMasukResmi }} - {{ $jamPulangResmi }}</span>
                </div>
            </div>

            <!-- Toleransi Terlambat -->
            <div class="bg-black/25 backdrop-blur-xs rounded-xl p-2 border border-white/15 flex items-center space-x-2">
                <div class="w-7 h-7 rounded-lg bg-amber-400/20 text-amber-300 flex items-center justify-center text-xs shrink-0">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div class="min-w-0">
                    <span class="text-[9px] text-sky-200/70 block font-medium truncate">Toleransi</span>
                    <span class="font-bold text-[11px] text-amber-300 block truncate">{{ $toleransi }} Mnt (s.d {{ $batasToleransi }})</span>
                </div>
            </div>
        </div>

        <!-- Baris 3: Info Kantor & Jarak User Real-Time -->
        <div class="pt-2.5 text-xs space-y-1.5 relative z-10">
            <!-- Kantor -->
            <div class="flex items-center space-x-2 text-white/90 text-[11px]">
                <i class="fa-solid fa-building text-sky-300 text-xs shrink-0"></i>
                <span class="truncate font-medium text-white">{{ $alamatKantor }}</span>
            </div>

            <!-- Jarak User Real-Time -->
            <div class="flex items-center justify-between bg-black/25 backdrop-blur-xs rounded-xl px-2.5 py-1.5 border border-white/15">
                <div class="flex items-center space-x-2 min-w-0">
                    <i class="fa-solid fa-location-dot text-emerald-400 text-xs shrink-0"></i>
                    <div class="text-[10px] text-sky-100 truncate">
                        <span>Jarak: </span>
                        <span class="font-bold font-mono text-white" x-text="currentDistanceText">Mendeteksi...</span>
                    </div>
                </div>

                <div>
                    <template x-if="isWfh">
                        <span class="text-[9px] font-bold text-amber-300 bg-amber-500/20 border border-amber-400/30 px-2 py-0.5 rounded-full">Bebas WFH</span>
                    </template>
                    <template x-if="!isWfh && isWithinRadius === true">
                        <span class="text-[9px] font-bold text-emerald-300 bg-emerald-500/20 border border-emerald-400/30 px-2 py-0.5 rounded-full">Dalam Radius</span>
                    </template>
                    <template x-if="!isWfh && isWithinRadius === false">
                        <span class="text-[9px] font-bold text-rose-300 bg-rose-500/20 border border-rose-400/30 px-2 py-0.5 rounded-full">Luar Radius</span>
                    </template>
                    <template x-if="!isWfh && isWithinRadius === null">
                        <span class="text-[9px] font-bold text-sky-200 bg-sky-400/20 border border-sky-300/30 px-2 py-0.5 rounded-full animate-pulse">Cek GPS...</span>
                    </template>
                </div>
            </div>

            <!-- Warning GPS jika belum diizinkan -->
            <template x-if="gpsWarning">
                <div class="p-2 rounded-xl bg-amber-500/20 border border-amber-400/30 text-[10px] text-amber-200 flex items-center justify-between">
                    <div class="flex items-center space-x-1.5">
                        <i class="fa-solid fa-triangle-exclamation text-amber-300"></i>
                        <span>Izin lokasi browser nonaktif.</span>
                    </div>
                    <button type="button" @click="detectPosition()" class="underline font-bold text-amber-200 hover:text-white">Aktifkan</button>
                </div>
            </template>
        </div>

        <!-- Baris 4: Admin Tools (Jika Role SuperAdmin/Admin) -->
        @if ($isAdmin)
            <div class="mt-2.5 pt-2 border-t border-white/15 flex items-center justify-between relative z-10">
                <span class="text-[9px] font-bold text-amber-300 uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-screwdriver-wrench mr-1"></i> Admin Tool
                </span>
                <div class="flex items-center space-x-1.5">
                    <button @click="setOfficeLocation()" :disabled="settingOffice"
                        class="px-2 py-0.5 bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold text-[9px] rounded-md shadow-xs transition active:scale-95 cursor-pointer">
                        <span x-text="settingOffice ? '...' : 'Set Ke Titik Saya'">Set Ke Titik Saya</span>
                    </button>
                    <button wire:click="simulasiResetMasuk"
                        class="px-2 py-0.5 bg-white/20 hover:bg-white/30 text-white font-bold text-[9px] rounded-md transition active:scale-95 cursor-pointer"
                        title="Kosongkan presensi hari ini untuk uji ulang">
                        Reset Hari Ini
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- KARTU UTAMA PRESENSI (2 KOLOM SEJAJAR: ABSEN DATANG & ABSEN PULANG) -->
    <div class="grid grid-cols-2 gap-3 mb-4">
        <!-- 1. KARTU ABSEN DATANG -->
        <div class="bg-white rounded-2xl p-3.5 shadow-xs border border-slate-200/90 flex flex-col justify-between min-h-[190px] transition-all {{ $sudahMasuk ? 'ring-2 ring-emerald-500/20 bg-emerald-50/20' : '' }}">
            <div>
                <!-- Header Icon & Status Badge -->
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center text-sm {{ $sudahMasuk ? 'bg-emerald-500 text-white shadow-xs' : 'bg-emerald-50 text-emerald-600' }}">
                        <i class="fa-solid {{ $sudahMasuk ? 'fa-check' : 'fa-arrow-right-to-bracket' }}"></i>
                    </div>
                    @if ($sudahMasuk)
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                            Tercatat
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                            Pagi
                        </span>
                    @endif
                </div>

                <!-- Judul Kartu -->
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-600 block">Absen Datang</span>

                <!-- Jam Masuk -->
                <div class="mt-1">
                    <div class="text-xl font-black font-mono tracking-tight {{ $sudahMasuk ? 'text-emerald-700' : 'text-slate-300' }}">
                        {{ $sudahMasuk ? substr($absenHarian->jam_masuk, 0, 5) : '--:--' }}
                        <span class="text-[10px] font-bold font-sans {{ $sudahMasuk ? 'text-emerald-600' : 'text-slate-300' }}">WIB</span>
                    </div>
                    <span class="text-[10px] font-medium block mt-0.5 truncate text-slate-400">
                        {{ $sudahMasuk ? ($absenHarian->status?->getLabel() ?? 'Hadir Tepat Waktu') : 'Buka: ' . $bukaMasuk }}
                    </span>
                </div>
            </div>

            <!-- Tombol Aksi Absen Datang -->
            <div class="mt-3">
                @if ($sudahMasuk)
                    <button type="button" disabled
                        class="w-full py-2 px-2.5 rounded-xl bg-emerald-100/80 text-emerald-800 font-bold text-xs flex items-center justify-center space-x-1 border border-emerald-200 cursor-default">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-xs"></i>
                        <span>Sudah Datang</span>
                    </button>
                @elseif ($lewatBatasMasuk)
                    <button type="button"
                        @click="window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'danger', title: 'Waktu Absen Telah Berakhir', message: 'Batas akhir absen masuk telah lewat (Pukul {{ $tutupMasuk }} WIB). Anda tercatat Alpa hari ini.' } }))"
                        class="w-full py-2 px-2.5 rounded-xl bg-rose-100 text-rose-800 font-extrabold text-xs flex items-center justify-center space-x-1 border border-rose-300 cursor-not-allowed">
                        <i class="fa-solid fa-lock text-rose-600 text-xs"></i>
                        <span>Waktu Lewat</span>
                    </button>
                @elseif ($belumBukaMasuk)
                    <button type="button"
                        @click="window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'warning', title: 'Belum Dibuka', message: 'Presensi masuk dibuka mulai pukul {{ $bukaMasuk }} WIB.' } }))"
                        class="w-full py-2 px-2.5 rounded-xl bg-slate-100 text-slate-500 font-bold text-xs flex items-center justify-center space-x-1 border border-slate-200 cursor-not-allowed">
                        <i class="fa-solid fa-clock text-slate-400 text-xs"></i>
                        <span>Belum Buka</span>
                    </button>
                @else
                    <button type="button" @click="doPunch('masuk')" :disabled="loadingMasuk"
                        class="w-full py-2 px-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-sm active:scale-95 transition-all flex items-center justify-center space-x-1.5 cursor-pointer">
                        <template x-if="loadingMasuk">
                            <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                        </template>
                        <template x-if="!loadingMasuk">
                            <i class="fa-solid fa-fingerprint text-sm"></i>
                        </template>
                        <span x-text="loadingMasuk ? 'Memproses...' : 'Absen Datang'">Absen Datang</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- 2. KARTU ABSEN PULANG -->
        <div class="bg-white rounded-2xl p-3.5 shadow-xs border border-slate-200/90 flex flex-col justify-between min-h-[190px] transition-all {{ $sudahPulang ? 'ring-2 ring-sky-500/20 bg-sky-50/20' : '' }}">
            <div>
                <!-- Header Icon & Status Badge -->
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 rounded-xl flex items-center justify-center text-sm {{ $sudahPulang ? 'bg-sky-500 text-white shadow-xs' : ($sudahMasuk ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-400') }}">
                        <i class="fa-solid {{ $sudahPulang ? 'fa-check-double' : 'fa-arrow-right-from-bracket' }}"></i>
                    </div>
                    @if ($sudahPulang)
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase bg-sky-100 text-sky-800 border border-sky-300">
                            Selesai
                        </span>
                    @elseif (!$sudahMasuk)
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 text-slate-400 border border-slate-200">
                            Sore
                        </span>
                    @elseif ($belumJamPulang)
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                            Buka {{ $jamPulangResmi }}
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase bg-amber-100 text-amber-800 border border-amber-300 flex items-center">
                            <span class="w-1 h-1 rounded-full bg-amber-500 animate-pulse mr-1"></span>Siap
                        </span>
                    @endif
                </div>

                <!-- Judul Kartu -->
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-600 block">Absen Pulang</span>

                <!-- Jam Pulang -->
                <div class="mt-1">
                    <div class="text-xl font-black font-mono tracking-tight {{ $sudahPulang ? 'text-sky-700' : 'text-slate-300' }}">
                        {{ $sudahPulang ? substr($absenHarian->jam_pulang, 0, 5) : '--:--' }}
                        <span class="text-[10px] font-bold font-sans {{ $sudahPulang ? 'text-sky-600' : 'text-slate-300' }}">WIB</span>
                    </div>
                    <span class="text-[10px] font-medium block mt-0.5 truncate text-slate-400">
                        {{ $sudahPulang ? 'Tugas Selesai' : 'Pulang: ' . $jamPulangResmi . ' WIB' }}
                    </span>
                </div>
            </div>

            <!-- Tombol Aksi Absen Pulang -->
            <div class="mt-3">
                @if ($sudahPulang)
                    <button type="button" disabled
                        class="w-full py-2 px-2.5 rounded-xl bg-sky-100/80 text-sky-800 font-bold text-xs flex items-center justify-center space-x-1 border border-sky-200 cursor-default">
                        <i class="fa-solid fa-circle-check text-sky-600 text-xs"></i>
                        <span>Sudah Pulang</span>
                    </button>
                @elseif (!$sudahMasuk)
                    <button type="button"
                        @click="window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'warning', title: 'Belum Absen Datang', message: 'Harap lakukan Absen Datang terlebih dahulu sebelum Absen Pulang.' } }))"
                        class="w-full py-2 px-2.5 rounded-xl bg-slate-100 text-slate-400 font-bold text-xs flex items-center justify-center space-x-1.5 border border-slate-200 cursor-not-allowed">
                        <i class="fa-solid fa-lock text-slate-400 text-xs"></i>
                        <span>Belum Datang</span>
                    </button>
                @elseif ($belumJamPulang)
                    <button type="button"
                        @click="window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'warning', title: 'Belum Jam Pulang', message: 'Tombol absen pulang baru aktif ketika jam pulang sudah masuk (Pukul {{ $jamPulangResmi }} WIB).' } }))"
                        class="w-full py-2 px-2.5 rounded-xl bg-slate-100 text-slate-500 font-bold text-xs flex items-center justify-center space-x-1 border border-slate-200 cursor-not-allowed">
                        <i class="fa-solid fa-clock text-slate-400 text-xs"></i>
                        <span>Belum Jam Pulang</span>
                    </button>
                @elseif ($lewatBatasPulang)
                    <button type="button"
                        @click="window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'danger', title: 'Waktu Pulang Lewat', message: 'Batas akhir absen pulang telah lewat (Pukul {{ $tutupPulang }} WIB).' } }))"
                        class="w-full py-2 px-2.5 rounded-xl bg-rose-100 text-rose-800 font-extrabold text-xs flex items-center justify-center space-x-1 border border-rose-300 cursor-not-allowed">
                        <i class="fa-solid fa-lock text-rose-600 text-xs"></i>
                        <span>Waktu Lewat</span>
                    </button>
                @else
                    <button type="button" @click="doPunch('pulang')" :disabled="loadingPulang"
                        class="w-full py-2 px-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-extrabold text-xs shadow-sm active:scale-95 transition-all flex items-center justify-center space-x-1.5 cursor-pointer">
                        <template x-if="loadingPulang">
                            <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                        </template>
                        <template x-if="!loadingPulang">
                            <i class="fa-solid fa-fingerprint text-sm"></i>
                        </template>
                        <span x-text="loadingPulang ? 'Memproses...' : 'Absen Pulang'">Absen Pulang</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Info Jabatan & PIN Mesin -->
    @if (!empty($user->jabatan?->nama) || !empty($user->nomor_absensi))
        <div class="bg-white rounded-xl p-2.5 border border-slate-200/80 flex items-center justify-between text-[11px] text-slate-600 mb-2">
            <span class="truncate pr-2 flex items-center">
                <i class="fa-solid fa-id-badge text-sky-500 mr-1.5 text-xs"></i>
                <span class="font-semibold text-slate-700">{{ $user->jabatan->nama ?? '-' }}</span>
            </span>
            <span class="truncate pl-2 text-right shrink-0 text-slate-500">
                PIN: <strong class="text-slate-800 font-mono">{{ $user->nomor_absensi ?? '-' }}</strong>
            </span>
        </div>
    @endif

    <div class="flex flex-col items-center mt-auto pt-2 pb-1 text-slate-400 text-center">
        <p class="text-[10px] text-slate-400">Akurasi GPS tinggi diwajibkan untuk presensi kedinasan.</p>
    </div>

    @script
        <script>
            window.absensiApp = function() {
                return {
                    loadingMasuk: false,
                    loadingPulang: false,
                    settingOffice: false,
                    officeLat: {{ $officeLat }},
                    officeLng: {{ $officeLng }},
                    maxRadius: {{ $maxRadius }},
                    isWfh: {{ $isWfh ? 'true' : 'false' }},
                    currentDistance: null,
                    currentDistanceText: 'Mendeteksi...',
                    userCoordsText: '',
                    isWithinRadius: null,
                    gpsWarning: false,

                    init() {
                        this.detectPosition();
                    },

                    calcDistance(lat1, lon1, lat2, lon2) {
                        const R = 6371000;
                        const dLat = (lat2 - lat1) * Math.PI / 180;
                        const dLon = (lon2 - lon1) * Math.PI / 180;
                        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                            Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                            Math.sin(dLon / 2) * Math.sin(dLon / 2);
                        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                        return R * c;
                    },

                    detectPosition() {
                        if (!navigator.geolocation) {
                            this.currentDistance = 0;
                            this.currentDistanceText = '0 meter (Simulasi)';
                            this.userCoordsText = `${this.officeLat.toFixed(5)}, ${this.officeLng.toFixed(5)}`;
                            this.isWithinRadius = true;
                            return;
                        }

                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                this.gpsWarning = false;
                                const uLat = pos.coords.latitude;
                                const uLng = pos.coords.longitude;
                                this.userCoordsText = `${uLat.toFixed(5)}, ${uLng.toFixed(5)}`;
                                const d = this.calcDistance(this.officeLat, this.officeLng, uLat, uLng);
                                this.currentDistance = Math.round(d);
                                this.currentDistanceText = `${this.currentDistance} meter`;
                                this.isWithinRadius = this.isWfh || (this.currentDistance <= this.maxRadius);
                            },
                            (err) => {
                                console.warn('[ABSEN] GPS warning:', err);
                                if (err.code === 1) {
                                    this.gpsWarning = true;
                                    this.currentDistanceText = 'Izin Lokasi Nonaktif';
                                    this.isWithinRadius = this.isWfh ? true : false;
                                } else {
                                    this.currentDistance = 0;
                                    this.currentDistanceText = '0 meter (Mode GPS)';
                                    this.userCoordsText = `${this.officeLat.toFixed(5)}, ${this.officeLng.toFixed(5)}`;
                                    this.isWithinRadius = true;
                                }
                            },
                            {
                                enableHighAccuracy: true,
                                timeout: 8000,
                                maximumAge: 10000
                            }
                        );
                    },

                    setOfficeLocation() {
                        if (this.settingOffice) return;
                        this.settingOffice = true;

                        if (!navigator.geolocation) {
                            this.settingOffice = false;
                            return;
                        }

                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                const uLat = position.coords.latitude;
                                const uLng = position.coords.longitude;
                                $wire.setLokasiKantorKeSaya(uLat, uLng).then(() => {
                                    this.settingOffice = false;
                                    this.officeLat = uLat;
                                    this.officeLng = uLng;
                                    this.userCoordsText = `${uLat.toFixed(5)}, ${uLng.toFixed(5)}`;
                                    this.currentDistance = 0;
                                    this.currentDistanceText = '0 meter';
                                    this.isWithinRadius = true;
                                    this.gpsWarning = false;
                                });
                            },
                            (error) => {
                                this.settingOffice = false;
                                this.gpsWarning = true;
                            },
                            {
                                enableHighAccuracy: true,
                                timeout: 8000
                            }
                        );
                    },

                    doPunch(jenis) {
                        if (jenis === 'masuk') this.loadingMasuk = true;
                        if (jenis === 'pulang') this.loadingPulang = true;

                        const resetBtn = () => {
                            this.loadingMasuk = false;
                            this.loadingPulang = false;
                        };

                        const executeCall = (lat, lng) => {
                            const call = (jenis === 'masuk') ? $wire.punchMasuk(lat, lng) : $wire.punchPulang(lat, lng);
                            call.then(() => {
                                resetBtn();
                            }).catch((err) => {
                                console.error('[ABSEN] Punch error:', err);
                                resetBtn();
                            });
                        };

                        if (!navigator.geolocation) {
                            executeCall(this.officeLat, this.officeLng);
                            return;
                        }

                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                executeCall(pos.coords.latitude, pos.coords.longitude);
                            },
                            (err) => {
                                console.warn('[ABSEN] GPS error fallback:', err);
                                executeCall(this.officeLat, this.officeLng);
                            },
                            {
                                enableHighAccuracy: true,
                                timeout: 7000,
                                maximumAge: 10000
                            }
                        );
                    }
                };
            };
        </script>
    @endscript
</div>
