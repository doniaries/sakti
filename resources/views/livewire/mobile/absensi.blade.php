<div class="p-5 pb-24 flex flex-col min-h-[calc(100vh-100px)]" x-data="absensiApp()">
    <!-- Top Bar: Header & Status GPS -->
    <div class="flex items-center justify-between mb-4 pt-1">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Presensi Kehadiran</h1>
            <p class="text-xs text-slate-500 font-medium">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        <div class="flex items-center space-x-1.5">
            <span
                class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider {{ $isWfh ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-sky-100 text-sky-800 border border-sky-300' }}">
                {{ $isWfh ? 'Pegawai WFH' : 'Pegawai WFO' }}
            </span>
            <div
                class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200/60 rounded-full text-[10px] font-bold flex items-center shadow-xs">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse mr-1.5"></span>
                <span x-text="isWithinRadius || isWfh ? 'GPS AKTIF' : 'GPS SIAP'">GPS AKTIF</span>
            </div>
        </div>
    </div>

    <!-- Card Utama Waktu Server, Lokasi & Jadwal Kedinasan -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white rounded-3xl p-4.5 shadow-md border border-slate-700/60 mb-5 relative overflow-hidden"
        x-data="{ time: '' }" x-init="setInterval(() => {
            const d = new Date();
            time = d.toLocaleTimeString('id-ID', { hour12: false });
        }, 1000);
        time = (new Date()).toLocaleTimeString('id-ID', { hour12: false });">
        
        <!-- Header: Waktu Server + Mode Kerja Badge -->
        <div class="flex items-center justify-between mb-1.5">
            <div class="flex items-center space-x-1.5 text-slate-400 text-[10px] uppercase font-bold tracking-wider">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Waktu Server Saat Ini</span>
            </div>
            <span class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider {{ $isWfh ? 'bg-amber-500/20 text-amber-300 border border-amber-500/40' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' }}">
                {{ $isWfh ? 'WFH (Bebas Radius)' : 'WFO (Kantor)' }}
            </span>
        </div>

        <!-- Big Digital Clock -->
        <div class="text-3xl sm:text-4xl font-extrabold font-mono tracking-wider text-white" x-text="time">--:--:--</div>

        <!-- Jadwal Kerja & Toleransi Kedinasan -->
        <div class="grid grid-cols-2 gap-2 mt-3 pt-3 border-t border-slate-700/70 text-[11px]">
            <div class="bg-slate-800/80 rounded-xl p-2.5 border border-slate-700/60">
                <span class="text-[10px] text-slate-400 block font-medium">Jadwal Kerja</span>
                <span class="font-bold text-white font-mono text-xs">{{ $jamMasukResmi }} - {{ $jamPulangResmi }} WIB</span>
                <span class="text-[9px] text-sky-400 block mt-0.5">{{ $jadwal->nama ?? 'Jadwal Reguler' }} {{ $isJumat ? '(Hari Jumat)' : '' }}</span>
            </div>
            <div class="bg-slate-800/80 rounded-xl p-2.5 border border-slate-700/60">
                <span class="text-[10px] text-slate-400 block font-medium">Toleransi Terlambat</span>
                <span class="font-bold text-white font-mono text-xs">{{ $toleransi }} Menit</span>
                <span class="text-[9px] text-amber-400 block mt-0.5">Maks. s.d {{ $batasToleransi }} WIB</span>
            </div>
        </div>

        <!-- Lokasi Kantor & Jarak User -->
        <div class="mt-3 pt-2.5 border-t border-slate-700/70 text-[11px] space-y-2">
            <!-- Lokasi Kantor -->
            <div class="flex items-start space-x-2 text-slate-300">
                <i class="fa-solid fa-building text-sky-400 text-xs mt-0.5 shrink-0"></i>
                <div class="min-w-0 flex-1">
                    <span class="font-semibold text-white truncate block text-[11px]">{{ $alamatKantor }}</span>
                    <span class="text-[9px] text-slate-400 font-mono">Titik Kantor: ({{ round($officeLat, 5) }}, {{ round($officeLng, 5) }}) &bull; Radius: {{ $maxRadius }}m</span>
                </div>
            </div>

            <!-- Jarak User Real-Time -->
            <div class="flex items-center justify-between bg-slate-800/90 rounded-xl px-3 py-2 border border-slate-700/80">
                <div class="flex items-center space-x-2 min-w-0">
                    <i class="fa-solid fa-location-crosshairs text-emerald-400 text-xs shrink-0"></i>
                    <div class="text-[10px] text-slate-300 truncate">
                        <span>Jarak ke Kantor: </span>
                        <span class="font-bold font-mono text-white text-xs" x-text="currentDistanceText">Mendeteksi...</span>
                        <template x-if="userCoordsText">
                            <span class="text-[9px] text-slate-400 block truncate font-mono" x-text="'Posisi Anda: (' + userCoordsText + ')'"></span>
                        </template>
                    </div>
                </div>

                <div>
                    <template x-if="isWfh">
                        <span class="text-[9px] font-bold text-amber-300 bg-amber-500/20 border border-amber-500/30 px-2 py-0.5 rounded-full">Bebas WFH</span>
                    </template>
                    <template x-if="!isWfh && isWithinRadius === true">
                        <span class="text-[9px] font-bold text-emerald-300 bg-emerald-500/20 border border-emerald-500/30 px-2 py-0.5 rounded-full">Dalam Radius</span>
                    </template>
                    <template x-if="!isWfh && isWithinRadius === false">
                        <span class="text-[9px] font-bold text-rose-300 bg-rose-500/20 border border-rose-500/30 px-2 py-0.5 rounded-full">Luar Radius</span>
                    </template>
                    <template x-if="!isWfh && isWithinRadius === null">
                        <span class="text-[9px] font-bold text-sky-300 bg-sky-500/20 border border-sky-500/30 px-2 py-0.5 rounded-full animate-pulse">Cek GPS...</span>
                    </template>
                </div>
            </div>

            <!-- Peringatan jika GPS belum diizinkan -->
            <template x-if="gpsWarning">
                <div class="p-2 rounded-xl bg-amber-500/20 border border-amber-500/40 text-[10px] text-amber-200 flex items-center justify-between">
                    <div class="flex items-center space-x-1.5">
                        <i class="fa-solid fa-triangle-exclamation text-amber-400 text-xs"></i>
                        <span>Izin lokasi browser belum aktif.</span>
                    </div>
                    <button type="button" @click="detectPosition()" class="underline font-bold text-amber-300 hover:text-white cursor-pointer">Aktifkan</button>
                </div>
            </template>
        </div>

        <!-- Admin Tools (Set Titik Saya / Reset Hari Ini) -->
        @if ($isAdmin)
            <div class="mt-3 pt-2.5 border-t border-slate-700/60 flex items-center justify-between">
                <span class="text-[9px] font-bold text-amber-400 uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-screwdriver-wrench mr-1"></i> Admin Tool
                </span>
                <div class="flex items-center space-x-1.5">
                    <button @click="setOfficeLocation()" :disabled="settingOffice"
                        class="px-2.5 py-1 bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-[9px] rounded-lg shadow-sm transition active:scale-95 cursor-pointer">
                        <span x-text="settingOffice ? 'Menyimpan...' : 'Set Ke Titik Saya'">Set Ke Titik Saya</span>
                    </button>
                    <button wire:click="simulasiResetMasuk"
                        class="px-2 py-1 bg-slate-700 hover:bg-slate-600 text-slate-200 font-bold text-[9px] rounded-lg transition active:scale-95 cursor-pointer"
                        title="Kosongkan presensi hari ini untuk uji ulang">
                        Reset Hari Ini
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- KARTU UTAMA PRESENSI (2 KOLOM TERPADU: ABSEN DATANG & ABSEN PULANG) -->
    <div class="grid grid-cols-2 gap-3 mb-6">
        <!-- 1. KARTU ABSEN DATANG -->
        <div
            class="bg-white rounded-3xl p-4 shadow-sm border border-slate-200/90 flex flex-col justify-between h-56 transition-all {{ $sudahMasuk ? 'ring-2 ring-emerald-500/20 bg-emerald-50/20' : '' }}">
            <div>
                <!-- Top Header -->
                <div class="flex items-center justify-between mb-2">
                    <div
                        class="w-9 h-9 rounded-xl flex items-center justify-center text-sm {{ $sudahMasuk ? 'bg-emerald-500 text-white shadow-xs' : 'bg-slate-100 text-slate-600' }}">
                        <i class="fa-solid {{ $sudahMasuk ? 'fa-check' : 'fa-right-to-bracket' }}"></i>
                    </div>
                    @if ($sudahMasuk)
                        <span
                            class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                            Tercatat
                        </span>
                    @else
                        <span
                            class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                            Pagi
                        </span>
                    @endif
                </div>

                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 block">Absen Datang</span>

                <!-- Large Display Time -->
                <div class="mt-1">
                    <span
                        class="text-xl font-black font-mono tracking-tight block {{ $sudahMasuk ? 'text-emerald-700' : 'text-slate-300' }}">
                        {{ $sudahMasuk ? substr($absenHarian->jam_masuk, 0, 5) : '--:--' }}
                        <span
                            class="text-[11px] font-bold font-sans {{ $sudahMasuk ? 'text-emerald-600' : 'text-slate-300' }}">WIB</span>
                    </span>
                    <span
                        class="text-[10px] font-medium block mt-0.5 truncate {{ $sudahMasuk ? $absenHarian->status?->getLabel() ?? 'Hadir Tepat Waktu' : 'Buka: ' . $bukaMasuk . ' - ' . $tutupMasuk }}</span>
                </div>
            </div>

            <!-- Action Button -->
            <div class="mt-3">
                @if ($sudahMasuk)
                    <button type="button" disabled
                        class="w-full py-2.5 px-3 rounded-xl bg-emerald-100/80 text-emerald-800 font-bold text-xs flex items-center justify-center space-x-1.5 border border-emerald-200 cursor-default">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-xs"></i>
                        <span>Sudah Datang</span>
                    </button>
                @else
                    <button type="button" @click="doPunch('masuk')" :disabled="loadingMasuk"
                        class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md shadow-emerald-600/30 active:scale-95 transition-all flex items-center justify-center space-x-1.5 cursor-pointer">
                        <template x-if="loadingMasuk">
                            <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                        </template>
                        <template x-if="!loadingMasuk">
                            <i class="fa-solid fa-fingerprint text-sm"></i>
                        </template>
                        <span x-text="loadingMasuk ? 'Mencatat...' : 'Absen Datang'">Absen Datang</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- 2. KARTU ABSEN PULANG -->
        <div
            class="bg-white rounded-3xl p-4 shadow-sm border border-slate-200/90 flex flex-col justify-between h-56 transition-all {{ $sudahPulang ? 'ring-2 ring-sky-500/20 bg-sky-50/20' : '' }}">
            <div>
                <!-- Top Header -->
                <div class="flex items-center justify-between mb-2">
                    <div
                        class="w-9 h-9 rounded-xl flex items-center justify-center text-sm {{ $sudahPulang ? 'bg-sky-500 text-white shadow-xs' : ($sudahMasuk ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-400') }}">
                        <i class="fa-solid {{ $sudahPulang ? 'fa-check-double' : 'fa-right-from-bracket' }}"></i>
                    </div>
                    @if ($sudahPulang)
                        <span
                            class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase bg-sky-100 text-sky-800 border border-sky-300">
                            Selesai
                        </span>
                    @elseif ($sudahMasuk)
                        <span
                            class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase bg-amber-100 text-amber-800 border border-amber-300 flex items-center">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse mr-1"></span>Siap
                        </span>
                    @else
                        <span
                            class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 text-slate-400 border border-slate-200">
                            Sore
                        </span>
                    @endif
                </div>

                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 block">Absen Pulang</span>

                <!-- Large Display Time -->
                <div class="mt-1">
                    <span
                        class="text-xl font-black font-mono tracking-tight block {{ $sudahPulang ? 'text-sky-700' : 'text-slate-300' }}">
                        {{ $sudahPulang ? substr($absenHarian->jam_pulang, 0, 5) : '--:--' }}
                        <span
                            class="text-[11px] font-bold font-sans {{ $sudahPulang ? 'text-sky-600' : 'text-slate-300' }}">WIB</span>
                    </span>
                    <span
                        class="text-[10px] font-medium block mt-0.5 truncate {{ $sudahPulang ? 'text-sky-600 font-bold' : 'text-slate-400' }}">
                        {{ $sudahPulang ? 'Tugas Dinas Selesai' : 'Pulang: ' . $jamPulangResmi . ' WIB' }}
                    </span>
                </div>
            </div>

            <!-- Action Button -->
            <div class="mt-3">
                @if ($sudahPulang)
                    <button type="button" disabled
                        class="w-full py-2.5 px-3 rounded-xl bg-sky-100/80 text-sky-800 font-bold text-xs flex items-center justify-center space-x-1.5 border border-sky-200 cursor-default">
                        <i class="fa-solid fa-circle-check text-sky-600 text-xs"></i>
                        <span>Sudah Pulang</span>
                    </button>
                @elseif (!$sudahMasuk)
                    <button type="button"
                        @click="window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'warning', title: 'Belum Absen Datang', message: 'Harap lakukan Absen Datang terlebih dahulu sebelum Absen Pulang.' } }))"
                        class="w-full py-2.5 px-3 rounded-xl bg-slate-100 text-slate-400 font-bold text-xs flex items-center justify-center space-x-1.5 border border-slate-200 cursor-not-allowed">
                        <i class="fa-solid fa-lock text-slate-400 text-xs"></i>
                        <span>Menunggu Datang</span>
                    </button>
                @else
                    <button type="button" @click="doPunch('pulang')" :disabled="loadingPulang"
                        class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-extrabold text-xs shadow-md shadow-orange-500/30 active:scale-95 transition-all flex items-center justify-center space-x-1.5 cursor-pointer">
                        <template x-if="loadingPulang">
                            <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                        </template>
                        <template x-if="!loadingPulang">
                            <i class="fa-solid fa-fingerprint text-sm"></i>
                        </template>
                        <span x-text="loadingPulang ? 'Mencatat...' : 'Absen Pulang'">Absen Pulang</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if (!empty($user->jabatan?->nama) || !empty($user->nomor_absensi))
        <div class="bg-white rounded-2xl p-3 border border-slate-200/80 flex items-center justify-between text-[11px] text-slate-500 mb-4">
            <span class="truncate pr-2">
                <strong class="text-slate-700">Jabatan:</strong> {{ $user->jabatan->nama ?? '-' }}
            </span>
            <span class="truncate pl-2 text-right shrink-0">
                <strong class="text-slate-700">PIN Mesin:</strong> {{ $user->nomor_absensi ?? '-' }}
            </span>
        </div>
    @endif

    <!-- GPS Info & Radius Footer Note -->
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
                                    this.currentDistanceText = 'Izin GPS Belum Aktif';
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
