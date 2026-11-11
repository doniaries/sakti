<div class="p-5 pb-24 flex flex-col min-h-[calc(100vh-100px)]" x-data="absensiApp()" @trigger-absen.window="triggerPunch()">
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
                GPS AKTIF
            </div>
        </div>
    </div>

    <!-- Live Digital Clock Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white rounded-2xl p-4 text-center shadow-md mb-4"
        x-data="{ time: '' }" x-init="setInterval(() => { const d = new Date();
            time = d.toLocaleTimeString('id-ID', { hour12: false }); }, 1000);
        time = (new Date()).toLocaleTimeString('id-ID', { hour12: false });">
        <span class="text-[11px] font-semibold uppercase tracking-widest text-sky-400">Waktu Server Saat Ini</span>
        <div class="text-3xl font-extrabold font-mono tracking-wider mt-0.5" x-text="time">--:--:--</div>
        <div class="text-[11px] text-slate-400 mt-1 flex items-center justify-center space-x-1.5">
            <i class="fa-solid fa-map-pin text-sky-400 text-xs"></i>
            <span class="truncate max-w-[260px]">{{ $alamatKantor }}</span>
        </div>
    </div>

    <!-- Geofencing & Jarak Kantor -->
    <div class="bg-white rounded-2xl p-3.5 shadow-xs border border-slate-200/80 mb-5">
        <div class="flex items-center justify-between text-xs">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-sm shrink-0">
                    <i class="fa-solid fa-location-crosshairs"></i>
                </div>
                <div>
                    <div class="flex items-center space-x-1.5">
                        <span class="w-2 h-2 rounded-full"
                            :class="isWithinRadius || isWfh ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                        <span class="text-[11px] font-bold text-slate-700">Jarak ke Kantor:</span>
                        <span class="font-mono font-bold text-[11px]"
                            :class="isWithinRadius || isWfh ? 'text-emerald-700' : 'text-rose-600'"
                            x-text="currentDistanceText">Mendeteksi GPS...</span>
                    </div>
                    <span class="text-[10px] text-slate-400">
                        {{ $isWfh ? 'Bebas Radius (WFH)' : 'Radius Maksimal: ' . $maxRadius . ' meter' }}
                    </span>
                </div>
            </div>
            <div>
                <template x-if="isWfh">
                    <span class="text-[10px] font-bold text-sky-700 bg-sky-50 border border-sky-200 px-2.5 py-1 rounded-full">Bebas Radius</span>
                </template>
                <template x-if="!isWfh && isWithinRadius === true">
                    <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full">Dalam Radius</span>
                </template>
                <template x-if="!isWfh && isWithinRadius === false">
                    <span class="text-[10px] font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2.5 py-1 rounded-full">Luar Radius</span>
                </template>
            </div>
        </div>

        @if ($isAdmin)
            <div class="mt-2.5 pt-2 border-t border-dashed border-amber-200 flex items-center justify-between">
                <span class="text-[10px] font-bold text-amber-900">
                    <i class="fa-solid fa-screwdriver-wrench text-amber-600 mr-1"></i> Admin Tools:
                </span>
                <div class="flex items-center space-x-1.5">
                    <button @click="setOfficeLocation()" :disabled="settingOffice"
                        class="px-2 py-1 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-[9px] rounded-lg shadow-xs transition active:scale-95">
                        <span x-text="settingOffice ? 'Menyimpan...' : 'Set Titik Saya'">Set Titik Saya</span>
                    </button>
                    <button wire:click="simulasiResetMasuk"
                        class="px-2 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-[9px] rounded-lg transition active:scale-95"
                        title="Kosongkan data hari ini untuk pengujian ulang">
                        Reset Hari Ini
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- KARTU UTAMA PRESENSI (2 KOLOM TERPADU: MASUK & KELUAR) -->
    <div class="grid grid-cols-2 gap-3 mb-6">
        <!-- 1. KARTU ABSEN MASUK -->
        <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-200/90 flex flex-col justify-between h-56 transition-all {{ $sudahMasuk ? 'ring-2 ring-emerald-500/20 bg-emerald-50/20' : '' }}">
            <div>
                <!-- Top Header -->
                <div class="flex items-center justify-between mb-2">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm {{ $sudahMasuk ? 'bg-emerald-500 text-white shadow-xs' : 'bg-slate-100 text-slate-600' }}">
                        <i class="fa-solid {{ $sudahMasuk ? 'fa-check' : 'fa-right-to-bracket' }}"></i>
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

                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 block">Absen Masuk</span>
                
                <!-- Large Display Time -->
                <div class="mt-1">
                    <span class="text-xl font-black font-mono tracking-tight block {{ $sudahMasuk ? 'text-emerald-700' : 'text-slate-300' }}">
                        {{ $sudahMasuk ? substr($absenHarian->jam_masuk, 0, 5) : '--:--' }}
                        <span class="text-[11px] font-bold font-sans {{ $sudahMasuk ? 'text-emerald-600' : 'text-slate-300' }}">WIB</span>
                    </span>
                    <span class="text-[10px] font-medium block mt-0.5 truncate {{ $sudahMasuk ? 'text-emerald-600 font-bold' : 'text-slate-400' }}">
                        {{ $sudahMasuk ? ($absenHarian->status?->getLabel() ?? 'Hadir Tepat Waktu') : 'Buka: ' . $bukaMasuk . ' - ' . $tutupMasuk }}
                    </span>
                </div>
            </div>

            <!-- Action Button -->
            <div class="mt-3">
                @if ($sudahMasuk)
                    <button type="button" disabled
                        class="w-full py-2.5 px-3 rounded-xl bg-emerald-100/80 text-emerald-800 font-bold text-xs flex items-center justify-center space-x-1.5 border border-emerald-200 cursor-default">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-xs"></i>
                        <span>Sudah Masuk</span>
                    </button>
                @else
                    <button type="button"
                        @click="doPunch('masuk')"
                        :disabled="loadingMasuk"
                        class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md shadow-emerald-600/30 active:scale-95 transition-all flex items-center justify-center space-x-1.5 cursor-pointer">
                        <template x-if="loadingMasuk">
                            <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                        </template>
                        <template x-if="!loadingMasuk">
                            <i class="fa-solid fa-fingerprint text-sm"></i>
                        </template>
                        <span x-text="loadingMasuk ? 'Mencatat...' : 'Catat Masuk'">Catat Masuk</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- 2. KARTU ABSEN KELUAR -->
        <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-200/90 flex flex-col justify-between h-56 transition-all {{ $sudahPulang ? 'ring-2 ring-sky-500/20 bg-sky-50/20' : '' }}">
            <div>
                <!-- Top Header -->
                <div class="flex items-center justify-between mb-2">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm {{ $sudahPulang ? 'bg-sky-500 text-white shadow-xs' : ($sudahMasuk ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-400') }}">
                        <i class="fa-solid {{ $sudahPulang ? 'fa-check-double' : 'fa-right-from-bracket' }}"></i>
                    </div>
                    @if ($sudahPulang)
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase bg-sky-100 text-sky-800 border border-sky-300">
                            Selesai
                        </span>
                    @elseif ($sudahMasuk)
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold uppercase bg-amber-100 text-amber-800 border border-amber-300 flex items-center">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse mr-1"></span>Siap
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-100 text-slate-400 border border-slate-200">
                            Sore
                        </span>
                    @endif
                </div>

                <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-500 block">Absen Keluar</span>
                
                <!-- Large Display Time -->
                <div class="mt-1">
                    <span class="text-xl font-black font-mono tracking-tight block {{ $sudahPulang ? 'text-sky-700' : 'text-slate-300' }}">
                        {{ $sudahPulang ? substr($absenHarian->jam_pulang, 0, 5) : '--:--' }}
                        <span class="text-[11px] font-bold font-sans {{ $sudahPulang ? 'text-sky-600' : 'text-slate-300' }}">WIB</span>
                    </span>
                    <span class="text-[10px] font-medium block mt-0.5 truncate {{ $sudahPulang ? 'text-sky-600 font-bold' : 'text-slate-400' }}">
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
                        <span>Sudah Keluar</span>
                    </button>
                @elseif (!$sudahMasuk)
                    <button type="button"
                        @click="window.dispatchEvent(new CustomEvent('notify', { detail: { type: 'warning', title: 'Belum Absen Masuk', message: 'Harap lakukan Absen Masuk terlebih dahulu sebelum Absen Keluar.' } }))"
                        class="w-full py-2.5 px-3 rounded-xl bg-slate-100 text-slate-400 font-bold text-xs flex items-center justify-center space-x-1.5 border border-slate-200 cursor-not-allowed">
                        <i class="fa-solid fa-lock text-slate-400 text-xs"></i>
                        <span>Menunggu Masuk</span>
                    </button>
                @else
                    <button type="button"
                        @click="doPunch('pulang')"
                        :disabled="loadingPulang"
                        class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-extrabold text-xs shadow-md shadow-orange-500/30 active:scale-95 transition-all flex items-center justify-center space-x-1.5 cursor-pointer">
                        <template x-if="loadingPulang">
                            <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                        </template>
                        <template x-if="!loadingPulang">
                            <i class="fa-solid fa-fingerprint text-sm"></i>
                        </template>
                        <span x-text="loadingPulang ? 'Mencatat...' : 'Catat Keluar'">Catat Keluar</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- GPS Info & Radius Footer Note -->
    <div class="flex flex-col items-center mt-auto pt-4 pb-1 text-slate-400 text-center">
        <div class="flex items-center text-[11px] font-semibold text-slate-500">
            <i class="fa-solid fa-location-crosshairs text-sky-500 text-xs mr-1.5"></i>
            <span>Geofencing: Titik ({{ round($officeLat, 4) }}, {{ round($officeLng, 4) }})</span>
        </div>
        <p class="text-[10px] text-slate-400 mt-0.5">Akurasi GPS tinggi diwajibkan untuk presensi kedinasan.</p>
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
            isWithinRadius: null,

            init() {
                this.detectPosition();

                // Handler tombol navbar center
                window.triggerDoAbsen = () => {
                    this.triggerPunch();
                };
            },

            triggerPunch() {
                if (!{{ $sudahMasuk ? 'true' : 'false' }}) {
                    this.doPunch('masuk');
                } else if (!{{ $sudahPulang ? 'true' : 'false' }}) {
                    this.doPunch('pulang');
                } else {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            type: 'info',
                            title: 'Presensi Lengkap',
                            message: 'Anda sudah menyelesaikan absensi masuk dan keluar hari ini.'
                        }
                    }));
                }
            },

            doPunch(jenis) {
                if (jenis === 'masuk') this.loadingMasuk = true;
                if (jenis === 'pulang') this.loadingPulang = true;

                const resetBtn = () => {
                    this.loadingMasuk = false;
                    this.loadingPulang = false;
                    const btnText = document.getElementById('btn-absen-text');
                    const btnIcon = document.getElementById('btn-absen-icon');
                    if (btnText) btnText.innerText = 'ABSEN';
                    if (btnIcon) btnIcon.className = 'fa-solid fa-fingerprint';
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
            },

            detectPosition() {
                if (!navigator.geolocation) {
                    this.currentDistance = 0;
                    this.currentDistanceText = '0 meter';
                    this.isWithinRadius = true;
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const d = this.calcDistance(this.officeLat, this.officeLng, pos.coords.latitude, pos.coords.longitude);
                        this.currentDistance = Math.round(d);
                        this.currentDistanceText = `${this.currentDistance} meter`;
                        this.isWithinRadius = (this.currentDistance <= this.maxRadius);
                    },
                    (err) => {
                        this.currentDistance = 0;
                        this.currentDistanceText = '0 meter (Mode GPS)';
                        this.isWithinRadius = true;
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 6000
                    }
                );
            },

            calcDistance(lat1, lon1, lat2, lon2) {
                const R = 6371000;
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLon = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                    Math.sin(dLon / 2) * Math.sin(dLon / 2);
                const c = 2 * atan2(Math.sqrt(a), sqrt(1 - a));
                return R * c;
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
                        $wire.setLokasiKantorKeSaya(position.coords.latitude, position.coords.longitude).then(() => {
                            this.settingOffice = false;
                            this.officeLat = position.coords.latitude;
                            this.officeLng = position.coords.longitude;
                            this.detectPosition();
                        });
                    },
                    (error) => {
                        this.settingOffice = false;
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 8000
                    }
                );
            }
        };
    };
</script>
@endscript
</div>
