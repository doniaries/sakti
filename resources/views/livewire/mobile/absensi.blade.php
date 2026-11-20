<div class="p-4 pb-28 flex flex-col min-h-[calc(100vh-100px)]" x-data="absensiApp()">
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
            <button type="button" @click="detectPosition(true)"
                :class="gpsStatus === 'ok'
                    ? 'bg-emerald-50 text-emerald-700 border-emerald-200/60 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-400/30'
                    : (gpsStatus === 'blocked'
                        ? 'bg-rose-50 text-rose-700 border-rose-300 dark:bg-rose-500/20 dark:text-rose-300 dark:border-rose-400/40 animate-pulse'
                        : 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-500/15 dark:text-sky-300 dark:border-sky-400/30')"
                class="px-2 py-0.5 border rounded-full text-[10px] font-bold flex items-center shadow-xs cursor-pointer transition">
                <span class="w-1.5 h-1.5 rounded-full mr-1"
                    :class="gpsStatus === 'ok' ? 'bg-emerald-500' : (gpsStatus === 'blocked' ? 'bg-rose-500' : 'bg-sky-500 animate-ping')"></span>
                <span x-text="gpsStatus === 'ok' ? 'GPS OK' : (gpsStatus === 'blocked' ? 'GPS Nonaktif' : 'Cek GPS...')">GPS</span>
            </button>
        </div>
    </div>

    <!-- CARD UTAMA: WAKTU SERVER, JADWAL & LOKASI KANTOR (SESUAI PEDOMAN BETTER-UI) -->
    <div class="bg-gradient-to-br from-[#0F172A] via-[#0369A1] to-[#0284C7] text-white rounded-3xl p-4 shadow-xl shadow-sky-950/20 mb-4 relative overflow-hidden border border-white/15"
        x-data="{ 
            time: '--:--:--',
            initClock() {
                const pad = (n) => String(n).padStart(2, '0');
                const update = () => {
                    const d = new Date();
                    this.time = `${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
                };
                update();
                setInterval(update, 1000);
            }
        }" 
        x-init="initClock()">
        
        <!-- Background Glow Accent & Decorative Watermark Icon (Identik Dashboard) -->
        <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-sky-400/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -right-3 -top-3 text-white/[0.07] text-8xl pointer-events-none">
            <i class="fa-solid fa-fingerprint"></i>
        </div>
        
        <!-- Baris 1: Jam Live + Mode Kerja & Tanggal (Ultra Compact) -->
        <div class="flex items-center justify-between gap-2 relative z-10">
            <div class="flex items-baseline space-x-1.5">
                @if ($modeSimulasi && !empty($simulasiJamAktif))
                    <span class="text-2xl font-black font-mono tracking-tight text-amber-300 leading-none">{{ $simulasiJamAktif }}:00</span>
                @else
                    <span class="text-2xl font-black font-mono tracking-tight text-white leading-none" x-text="time">--:--:--</span>
                @endif
                <span class="text-[10px] font-bold text-amber-300 font-mono">WIB</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-black/30 border border-white/15 {{ $isWfh ? 'text-amber-300' : 'text-emerald-300' }}">
                    {{ $isWfh ? 'WFH' : 'WFO' }}
                </span>
                <span class="text-[11px] font-semibold text-sky-100">{{ now()->translatedFormat('D, d M') }}</span>
            </div>
        </div>

        <!-- Baris 2: Jadwal Kerja & Perbandingan Jarak User vs Radius Kantor -->
        <div class="mt-2 pt-2 border-t border-white/15 flex items-center justify-between gap-2 text-[11px] relative z-10">
            <span class="text-sky-100 font-semibold truncate">
                <i class="fa-regular fa-calendar-check text-sky-300 mr-1"></i>{{ $jamMasukResmi }} - {{ $jamPulangResmi }} WIB
            </span>
            <button type="button" @click="detectPosition(true)" title="Ketuk untuk refresh GPS"
                class="shrink-0 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border cursor-pointer active:scale-95 transition"
                :class="isWfh
                    ? 'bg-amber-500/20 border-amber-400/30 text-amber-200'
                    : (isWithinRadius === false
                        ? 'bg-rose-500/25 border-rose-400/40 text-rose-100'
                        : 'bg-emerald-500/25 border-emerald-400/40 text-emerald-100')">
                <i class="fa-solid fa-location-dot text-[9px]"></i>
                <span x-text="isWfh ? 'Bebas WFH' : ('Jarak ' + currentDistanceText + ' / Radius {{ $maxRadius }}m')">Cek GPS...</span>
            </button>
        </div>

        <!-- Baris 3: Ringkasan Status Absen Datang & Pulang (Compact 2 Kolom) -->
        <div class="mt-2 grid grid-cols-2 gap-2 relative z-10">
            <div class="rounded-xl bg-black/20 border border-white/15 px-2.5 py-1.5 flex items-center justify-between">
                <div>
                    <span class="text-[9px] uppercase tracking-wider text-sky-200/80 font-bold block">Datang</span>
                    <span class="text-xs font-black font-mono {{ $sudahMasuk ? 'text-emerald-300' : 'text-white/50' }}">
                        {{ $sudahMasuk ? substr($absenHarian->jam_masuk, 0, 5) . ' WIB' : '--:--' }}
                    </span>
                </div>
                <i class="fa-solid {{ $sudahMasuk ? 'fa-circle-check text-emerald-400' : 'fa-clock text-white/40' }} text-sm"></i>
            </div>
            <div class="rounded-xl bg-black/20 border border-white/15 px-2.5 py-1.5 flex items-center justify-between">
                <div>
                    <span class="text-[9px] uppercase tracking-wider text-sky-200/80 font-bold block">Pulang</span>
                    <span class="text-xs font-black font-mono {{ $sudahPulang ? 'text-sky-300' : 'text-white/50' }}">
                        {{ $sudahPulang ? substr($absenHarian->jam_pulang, 0, 5) . ' WIB' : '--:--' }}
                    </span>
                </div>
                <i class="fa-solid {{ $sudahPulang ? 'fa-check-double text-sky-300' : 'fa-clock text-white/40' }} text-sm"></i>
            </div>
        </div>

        <!-- Warning GPS jika belum diizinkan -->
        <template x-if="gpsWarning">
            <div class="mt-2 p-2 rounded-xl bg-amber-500/20 border border-amber-400/30 text-[10px] text-amber-200 flex items-center justify-between relative z-10">
                <div class="flex items-center space-x-1.5">
                    <i class="fa-solid fa-triangle-exclamation text-amber-300"></i>
                    <span>Izin lokasi browser nonaktif.</span>
                </div>
                <button type="button" @click="detectPosition()" class="underline font-bold text-amber-200 hover:text-white cursor-pointer">Aktifkan</button>
            </div>
        </template>

        <!-- Baris 4: Admin Tools (Hanya Tampil Jika Role SuperAdmin / Admin Dinas) -->
        @if ($isAdmin)
            <div class="mt-2.5 pt-2 border-t border-white/15 flex items-center justify-between relative z-10 text-[10px]">
                <span class="font-bold text-amber-300 uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-screwdriver-wrench mr-1.5 text-xs text-amber-400"></i> Mode Admin
                </span>
                <div class="flex items-center space-x-1.5">
                    <a href="{{ route('mobile.pengaturan-lokasi') }}" wire:navigate
                        class="px-2.5 py-1 bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold rounded-lg shadow-xs transition-transform duration-150 ease-out active:scale-[0.96] flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-map-location-dot text-[10px]"></i>
                        <span>Atur Peta & Titik</span>
                    </a>
                    <button wire:click="simulasiResetMasuk"
                        class="px-2.5 py-1 bg-white/20 hover:bg-white/30 text-white font-bold rounded-lg transition-transform duration-150 ease-out active:scale-[0.96] cursor-pointer"
                        title="Kosongkan presensi hari ini untuk uji ulang">
                        Reset Hari Ini
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- PETA & TITIK KOORDINAT GPS (LANGSUNG DI ATAS TOMBOL ABSEN) -->
    <div class="bg-white rounded-2xl p-3 border border-slate-200/90 shadow-xs mb-4">
        <div class="flex items-center justify-between gap-2 mb-2">
            <div class="flex items-center space-x-2 min-w-0">
                <div class="w-7 h-7 rounded-lg bg-emerald-500/15 text-emerald-500 flex items-center justify-center text-xs shrink-0">
                    <i class="fa-solid fa-map-location-dot"></i>
                </div>
                <div class="min-w-0">
                    <span class="text-xs font-extrabold text-slate-800 block leading-tight">Peta & Lokasi (GPS)</span>
                    <span class="text-[10px] font-mono text-slate-500 block truncate" x-text="userCoordsText ? userCoordsText : 'Mencari koordinat...'"></span>
                </div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <span class="px-2 py-1 rounded-lg text-[10px] font-extrabold border"
                    :class="isWithinRadius === false ? 'bg-rose-500/15 text-rose-500 border-rose-400/30' : 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-300 border-emerald-400/30'"
                    x-text="'Jarak: ' + currentDistanceText"></span>
                <button type="button" @click="detectPosition(true)"
                    class="px-2.5 py-1 rounded-lg bg-sky-500/15 hover:bg-sky-500/25 text-sky-600 dark:text-cyan-300 border border-sky-400/30 text-[10px] font-bold flex items-center gap-1 transition active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-crosshairs text-[10px]"></i>
                    <span>Perbarui</span>
                </button>
            </div>
        </div>

        <!-- Banner Status GPS Browser -->
        <template x-if="gpsStatus === 'blocked'">
            <div class="mb-2 p-2.5 rounded-xl bg-rose-500/15 border border-rose-400/40 text-rose-700 dark:text-rose-200 text-[11px] flex items-center justify-between gap-2">
                <div class="flex items-start gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-rose-500 mt-0.5 shrink-0"></i>
                    <span><strong>GPS Browser belum aktif!</strong> Izinkan akses Lokasi pada browser (ikon gembok/setelan di address bar), lalu tekan <strong>Perbarui</strong>.</span>
                </div>
            </div>
        </template>
        <template x-if="gpsStatus === 'ok'">
            <div class="mb-2 px-2.5 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-400/30 text-emerald-700 dark:text-emerald-300 text-[10px] font-semibold flex items-center justify-between">
                <span class="flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    <span>GPS Browser Aktif &amp; OK</span>
                </span>
                <span class="font-mono opacity-85" x-text="gpsAccuracy ? 'Akurasi ±' + gpsAccuracy + ' m' : 'Siap Presensi'"></span>
            </div>
        </template>

        <div wire:ignore id="absensi-map" class="w-full h-44 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700/60 bg-slate-900 z-10"></div>

        <div class="flex items-center justify-between mt-2 text-[10px]">
            <span class="text-slate-500 flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>
                Batas Radius Kantor: <strong class="text-slate-700">{{ $maxRadius }} m</strong>
            </span>
            <span class="text-slate-400 font-medium">Pin Biru = Posisi Anda</span>
        </div>
    </div>

    <!-- Status Hari Libur / Selesai / Di Luar Radius (Ringkas di Bawah Peta) -->
    @if ($hariLiburHariIni)
        <div class="mb-3 p-3 rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 text-amber-800 dark:text-amber-300 text-xs font-bold text-center">
            <i class="fa-solid fa-calendar-xmark mr-1"></i> Hari Libur: {{ $hariLiburHariIni->nama }}
        </div>
    @elseif ($sudahMasuk && $sudahPulang)
        <div class="mb-3 p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs font-bold text-center">
            <i class="fa-solid fa-circle-check mr-1"></i> Presensi Datang &amp; Pulang Hari Ini Selesai
        </div>
    @else
        <div x-show="!isWfh && isWithinRadius === false" x-cloak
            class="mb-3 p-3 rounded-2xl bg-rose-50 dark:bg-rose-500/15 border border-rose-200 dark:border-rose-500/30 text-rose-700 dark:text-rose-300 text-xs font-bold text-center">
            <i class="fa-solid fa-location-crosshairs mr-1"></i> Anda berada di luar radius kantor (<span x-text="currentDistanceText"></span>). Mendekatlah ke area kantor untuk mengaktifkan tombol absen.
        </div>
    @endif

    <!-- Info Jabatan & PIN Mesin (Polesan Elegan Sesuai Better-UI) -->
    @if (!empty($user->jabatan?->nama) || !empty($user->nomor_absensi))
        <div class="bg-white rounded-2xl p-3 border border-slate-200/90 shadow-xs flex items-center justify-between text-xs mb-3">
            <div class="flex items-center space-x-2.5 min-w-0 pr-2">
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 border border-sky-100 flex items-center justify-center text-xs shrink-0">
                    <i class="fa-solid fa-id-badge"></i>
                </div>
                <div class="min-w-0">
                    <span class="text-[9px] uppercase tracking-wider text-slate-400 block font-bold leading-tight">Jabatan Pegawai</span>
                    <span class="font-extrabold text-slate-800 truncate block text-[11px] mt-0.5">{{ $user->jabatan->nama ?? '-' }}</span>
                </div>
            </div>
            <div class="shrink-0 text-right">
                <span class="text-[9px] uppercase tracking-wider text-slate-400 block font-bold leading-tight">PIN Mesin</span>
                <span class="px-2 py-0.5 rounded-lg bg-slate-100 border border-slate-200 text-slate-800 font-black font-mono text-[11px] inline-block mt-0.5">
                    {{ $user->nomor_absensi ?? '-' }}
                </span>
            </div>
        </div>
    @endif

    <!-- TOMBOL ABSEN MELAYANG (FLOATING 1-TAP PILL SAAT SIAP ABSEN & DALAM RADIUS) -->
    @if (!$hariLiburHariIni && !$sudahMasuk && !$lewatBatasMasuk && !$belumBukaMasuk)
        <div x-show="isWfh || isWithinRadius === true" x-cloak x-transition
            class="fixed bottom-[90px] inset-x-0 z-30 flex justify-center pointer-events-none px-4">
            <button type="button" @click="doPunch('masuk')" :disabled="loadingMasuk"
                class="pointer-events-auto max-w-[360px] w-full py-3 px-5 rounded-full bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-extrabold text-xs shadow-xl shadow-emerald-600/35 border border-white/25 flex items-center justify-center gap-2 active:scale-95 transition-all cursor-pointer disabled:opacity-60">
                <i x-show="!loadingMasuk" class="fa-solid fa-fingerprint text-sm"></i>
                <i x-show="loadingMasuk" x-cloak class="fa-solid fa-circle-notch fa-spin text-sm"></i>
                <span x-text="loadingMasuk ? 'Memproses Absen Datang...' : 'Tap Absen Datang Sekarang'"></span>
            </button>
        </div>
    @elseif ($sudahMasuk && !$sudahPulang && !$belumJamPulang && !$lewatBatasPulang)
        <div x-show="isWfh || isWithinRadius === true" x-cloak x-transition
            class="fixed bottom-[90px] inset-x-0 z-30 flex justify-center pointer-events-none px-4">
            <button type="button" @click="doPunch('pulang')" :disabled="loadingPulang"
                class="pointer-events-auto max-w-[360px] w-full py-3 px-5 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-white font-extrabold text-xs shadow-xl shadow-amber-500/35 border border-white/25 flex items-center justify-center gap-2 active:scale-95 transition-all cursor-pointer disabled:opacity-60">
                <i x-show="!loadingPulang" class="fa-solid fa-fingerprint text-sm"></i>
                <i x-show="loadingPulang" x-cloak class="fa-solid fa-circle-notch fa-spin text-sm"></i>
                <span x-text="loadingPulang ? 'Memproses Absen Pulang...' : 'Tap Absen Pulang Sekarang'"></span>
            </button>
        </div>
    @endif

    <div class="flex flex-col items-center mt-auto pt-1 pb-16 text-slate-400 text-center">
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
                    modeSimulasi: {{ $modeSimulasi ? 'true' : 'false' }},
                    currentDistance: null,
                    currentDistanceText: 'Mendeteksi...',
                    userCoordsText: '',
                    isWithinRadius: null,
                    gpsWarning: false,
                    gpsStatus: 'checking',
                    gpsAccuracy: null,
                    map: null,
                    officeCircle: null,
                    userMarker: null,

                    init() {
                        this.$nextTick(() => {
                            this.initMap();
                            this.detectPosition();
                        });
                    },

                    initMap() {
                        const el = document.getElementById('absensi-map');
                        if (!el || typeof L === 'undefined' || this.map) return;

                        this.map = L.map(el, { zoomControl: true, attributionControl: false })
                            .setView([this.officeLat, this.officeLng], 17);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19
                        }).addTo(this.map);

                        L.marker([this.officeLat, this.officeLng]).addTo(this.map)
                            .bindPopup('Titik Lokasi Kantor');

                        this.officeCircle = L.circle([this.officeLat, this.officeLng], {
                            color: '#10b981',
                            fillColor: '#10b981',
                            fillOpacity: 0.18,
                            weight: 2,
                            radius: this.maxRadius
                        }).addTo(this.map);
                    },

                    updateUserMarker(lat, lng) {
                        if (!this.map || typeof L === 'undefined') return;
                        if (this.userMarker) {
                            this.userMarker.setLatLng([lat, lng]);
                        } else {
                            const userIcon = L.divIcon({
                                className: 'custom-user-marker',
                                html: '<div class="w-8 h-8 rounded-full bg-sky-500 border-2 border-white shadow-lg flex items-center justify-center text-white"><i class="fa-solid fa-person text-sm"></i></div>',
                                iconSize: [32, 32],
                                iconAnchor: [16, 16]
                            });
                            this.userMarker = L.marker([lat, lng], { icon: userIcon })
                                .addTo(this.map)
                                .bindPopup('Posisi Anda Sekarang');
                        }
                        const group = L.featureGroup([this.officeCircle, this.userMarker]);
                        this.map.fitBounds(group.getBounds(), { padding: [24, 24], maxZoom: 18 });
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

                    detectPosition(manual = false) {
                        this.gpsStatus = 'checking';

                        if (!navigator.geolocation) {
                            this.gpsWarning = true;
                            this.gpsStatus = 'blocked';
                            this.currentDistanceText = 'GPS Tidak Didukung';
                            if (this.modeSimulasi) {
                                this.currentDistance = 0;
                                this.currentDistanceText = '0 meter (Simulasi)';
                                this.userCoordsText = `${this.officeLat.toFixed(5)}, ${this.officeLng.toFixed(5)}`;
                                this.isWithinRadius = true;
                                this.updateUserMarker(this.officeLat, this.officeLng);
                            }
                            if (manual) {
                                window.dispatchEvent(new CustomEvent('notify', {
                                    detail: { type: 'warning', title: 'GPS Tidak Didukung', message: 'Browser Anda tidak mendukung fitur geolokasi.' }
                                }));
                            }
                            return;
                        }

                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                this.gpsWarning = false;
                                this.gpsStatus = 'ok';
                                this.gpsAccuracy = pos.coords.accuracy ? Math.round(pos.coords.accuracy) : null;
                                const uLat = pos.coords.latitude;
                                const uLng = pos.coords.longitude;
                                this.userCoordsText = `${uLat.toFixed(5)}, ${uLng.toFixed(5)}`;
                                const d = this.calcDistance(this.officeLat, this.officeLng, uLat, uLng);
                                this.currentDistance = Math.round(d);
                                this.currentDistanceText = this.modeSimulasi
                                    ? `${this.currentDistance} meter (Simulasi)`
                                    : `${this.currentDistance} meter`;
                                this.isWithinRadius = this.isWfh || (this.currentDistance <= this.maxRadius);
                                this.updateUserMarker(uLat, uLng);

                                if (manual) {
                                    window.dispatchEvent(new CustomEvent('notify', {
                                        detail: { type: 'success', title: 'GPS OK', message: `Lokasi GPS aktif (${this.currentDistanceText}).` }
                                    }));
                                }
                            },
                            (err) => {
                                console.warn('[ABSEN] GPS warning:', err);
                                this.gpsWarning = true;
                                this.gpsStatus = 'blocked';
                                this.gpsAccuracy = null;

                                if (this.modeSimulasi) {
                                    this.currentDistance = 0;
                                    this.currentDistanceText = '0 meter (Simulasi)';
                                    this.userCoordsText = `${this.officeLat.toFixed(5)}, ${this.officeLng.toFixed(5)}`;
                                    this.isWithinRadius = true;
                                    this.updateUserMarker(this.officeLat, this.officeLng);
                                } else {
                                    this.currentDistanceText = err.code === 1 ? 'Izin GPS Nonaktif' : 'Sinyal GPS Mati';
                                    this.isWithinRadius = this.isWfh ? true : false;
                                }

                                if (manual || err.code === 1) {
                                    window.dispatchEvent(new CustomEvent('notify', {
                                        detail: {
                                            type: 'warning',
                                            title: 'GPS Browser Belum Aktif',
                                            message: 'Mohon izinkan akses Lokasi pada browser Anda agar titik presensi dapat terbaca.'
                                        }
                                    }));
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
