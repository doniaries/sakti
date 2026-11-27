<div class="p-4 pb-28 flex flex-col min-h-[calc(100vh-100px)] space-y-3.5 text-zinc-900 dark:text-zinc-100" x-data="absensiApp()">
    <!-- Hero Biru Gradient Melengkung + Kartu Mengambang -->
    <section class="-mx-4 -mt-4"
        x-data="{ 
            time: '--:--:--',
            clockTimer: null,
            initClock() {
                const pad = (n) => String(n).padStart(2, '0');
                const update = () => {
                    const d = new Date();
                    this.time = `${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
                };
                update();
                clearInterval(this.clockTimer);
                this.clockTimer = setInterval(update, 1000);
                document.addEventListener('livewire:navigating', () => clearInterval(this.clockTimer), { once: true });
            }
        }" 
        x-init="initClock()">

        <div class="relative overflow-hidden rounded-b-[28px] bg-gradient-to-br from-[#0F172A] via-[#0369A1] to-[#0284C7] px-5 pt-5 pb-12 text-white shadow-lg shadow-sky-950/20">
            <div class="flex items-center justify-between">
                <h1 class="text-lg md:text-2xl font-extrabold tracking-tight">Presensi Kehadiran</h1>
                <div class="flex items-center gap-2 pl-3 border-l-2 border-amber-400">
                    <span class="text-3xl md:text-4xl font-black leading-none tracking-tighter tabular-nums">{{ now()->format('d') }}</span>
                    <div class="leading-tight">
                        <div class="text-xs md:text-sm font-extrabold text-amber-300">{{ now()->translatedFormat('l') }}</div>
                        <div class="text-[10px] md:text-xs font-semibold uppercase tracking-wider text-sky-100">{{ now()->translatedFormat('M Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative z-10 -mt-8 mx-4 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 p-4 shadow-xl shadow-slate-900/10 ring-1 ring-slate-900/5 space-y-3.5">
            <!-- Status Hari Libur / Selesai -->
            @if ($hariLiburHariIni)
                <div class="p-2.5 rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 text-amber-800 dark:text-amber-300 text-xs md:text-sm font-bold text-center">
                    <i class="fa-solid fa-calendar-xmark mr-1"></i> Hari Libur: {{ $hariLiburHariIni->nama }}
                </div>
            @elseif ($sudahMasuk && $sudahPulang)
                <div class="p-2.5 rounded-2xl bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-xs md:text-sm font-bold text-center">
                    <i class="fa-solid fa-circle-check mr-1"></i> Presensi Datang &amp; Pulang Hari Ini Selesai
                </div>
            @endif

        <!-- Row 1: Live Clock (Tengah & Besar), Jadwal Kerja & Jarak -->
        <div class="flex flex-col items-center text-center gap-2 py-1">
            <div class="flex items-baseline justify-center gap-1.5">
                @if ($modeSimulasi && !empty($simulasiJamAktif))
                    <span class="text-4xl md:text-5xl font-black font-mono tracking-tight text-amber-600 dark:text-amber-400 leading-none">{{ $simulasiJamAktif }}:00</span>
                @else
                    <span class="text-4xl md:text-5xl font-black font-mono tracking-tight text-zinc-900 dark:text-white leading-none" x-text="time">--:--:--</span>
                @endif
                <span class="text-xs md:text-sm font-extrabold text-zinc-400 font-mono">WIB</span>
            </div>
            <div class="flex flex-wrap items-center justify-center gap-2">
                <p class="text-[11px] md:text-sm text-zinc-500 dark:text-zinc-400 font-medium">
                    <i class="fa-regular fa-clock text-sky-500 mr-1"></i>Jadwal {{ $jamMasukResmi }} - {{ $jamPulangResmi }} WIB
                </p>
                <button type="button" @click="detectPosition(true)" title="Ketuk untuk refresh GPS"
                    class="shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] md:text-xs font-bold border cursor-pointer active:scale-95 transition"
                    :class="(isWfh || modeSimulasi)
                        ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/15 dark:border-emerald-500/30 dark:text-emerald-300'
                        : (isWithinRadius === false
                            ? 'bg-rose-50 border-rose-200 text-rose-700 dark:bg-rose-500/15 dark:border-rose-500/30 dark:text-rose-300'
                            : 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/15 dark:border-emerald-500/30 dark:text-emerald-300')">
                    <i class="fa-solid fa-location-dot text-[10px]"></i>
                    <span x-text="modeSimulasi ? ('Bebas Lokasi • ' + currentDistanceText) : (isWfh ? 'Bebas WFH' : (currentDistanceText + ' / {{ $maxRadius }}m'))">Cek GPS...</span>
                </button>
            </div>
        </div>

        <!-- Row 3: Ringkasan Status Absen Datang & Pulang -->
        <div class="grid grid-cols-2 gap-2.5">
            <div class="rounded-2xl border px-3 py-3 text-center {{ $sudahMasuk ? 'bg-emerald-50/90 dark:bg-emerald-500/15 border-emerald-200 dark:border-emerald-500/30' : 'bg-amber-50/80 dark:bg-amber-500/15 border-amber-200 dark:border-amber-500/30' }}">
                <span class="text-[10px] md:text-xs uppercase tracking-wider font-extrabold inline-flex items-center gap-1 {{ $sudahMasuk ? 'text-emerald-700 dark:text-emerald-300' : 'text-amber-700 dark:text-amber-300' }}">
                    <i class="fa-solid {{ $sudahMasuk ? 'fa-circle-check text-emerald-600' : 'fa-clock text-amber-500' }}"></i>
                    <span>Datang</span>
                </span>
                <span class="mt-1 text-lg md:text-xl font-black font-mono block leading-tight {{ $sudahMasuk ? 'text-emerald-950 dark:text-emerald-200' : 'text-amber-800 dark:text-amber-200' }}">
                    {{ $sudahMasuk ? substr($absenHarian->jam_masuk, 0, 5) . ' WIB' : '--:--' }}
                </span>
            </div>
            <div class="rounded-2xl border px-3 py-3 text-center {{ $sudahPulang ? 'bg-sky-50/90 dark:bg-sky-500/15 border-sky-200 dark:border-sky-500/30' : 'bg-amber-50/80 dark:bg-amber-500/15 border-amber-200 dark:border-amber-500/30' }}">
                <span class="text-[10px] md:text-xs uppercase tracking-wider font-extrabold inline-flex items-center gap-1 {{ $sudahPulang ? 'text-sky-700 dark:text-sky-300' : 'text-amber-700 dark:text-amber-300' }}">
                    <i class="fa-solid {{ $sudahPulang ? 'fa-check-double text-sky-600' : 'fa-clock text-amber-500' }}"></i>
                    <span>Pulang</span>
                </span>
                <span class="mt-1 text-lg md:text-xl font-black font-mono block leading-tight {{ $sudahPulang ? 'text-sky-950 dark:text-sky-200' : 'text-amber-800 dark:text-amber-200' }}">
                    {{ $sudahPulang ? substr($absenHarian->jam_pulang, 0, 5) . ' WIB' : '--:--' }}
                </span>
            </div>
        </div>

        <!-- Warning GPS jika belum diizinkan -->
        <template x-if="gpsWarning">
            <div class="p-2.5 rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 text-[11px] text-amber-800 dark:text-amber-300 flex items-center justify-between">
                <div class="flex items-center space-x-1.5">
                    <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                    <span>Izin lokasi browser nonaktif.</span>
                </div>
                <button type="button" @click="detectPosition()" class="underline font-bold text-amber-700 dark:text-amber-200 cursor-pointer">Aktifkan</button>
            </div>
        </template>

        <!-- Baris 4: Admin Tools (Hanya Tampil Jika Role SuperAdmin / Admin Dinas) -->
        @if ($isAdmin)
            <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-[10px]">
                <span class="font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider flex items-center">
                    <i class="fa-solid fa-screwdriver-wrench mr-1.5 text-xs"></i> Mode Admin
                </span>
                <div class="flex items-center space-x-1.5">
                    <a href="{{ route('mobile.pengaturan-lokasi') }}" wire:navigate
                        class="px-2.5 py-1 bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold rounded-lg shadow-md shadow-slate-900/5 transition-transform duration-150 ease-out active:scale-[0.96] flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-map-location-dot text-[10px]"></i>
                        <span>Atur Peta & Titik</span>
                    </a>
                    <button wire:click="simulasiResetMasuk"
                        class="px-2.5 py-1 bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 font-bold rounded-lg transition-transform duration-150 ease-out active:scale-[0.96] cursor-pointer"
                        title="Kosongkan presensi hari ini untuk uji ulang">
                        Reset Hari Ini
                    </button>
                </div>
            </div>
        @endif
        </div>
    </section>

    <!-- PETA & TITIK KOORDINAT GPS (LANGSUNG DI ATAS TOMBOL ABSEN) -->
    <div class="bg-white rounded-2xl p-3 border border-slate-200/90 shadow-md shadow-slate-900/5 mb-4">
        <div class="flex items-center justify-between gap-2 mb-2">
            <div class="flex items-center space-x-2 min-w-0">
                <div class="w-7 h-7 rounded-lg bg-emerald-500/15 text-emerald-500 flex items-center justify-center text-xs shrink-0">
                    <i class="fa-solid fa-map-location-dot"></i>
                </div>
                <div class="min-w-0">
                    <span class="text-xs md:text-sm font-extrabold text-slate-800 block leading-tight">Peta & Lokasi (GPS)</span>
                    <span class="text-[10px] md:text-xs font-mono text-slate-500 block truncate" x-text="userCoordsText ? userCoordsText : 'Mencari koordinat...'"></span>
                </div>
            </div>
            <div class="flex items-center gap-1.5 shrink-0">
                <span class="px-2 py-1 border rounded-lg text-[10px] font-bold flex items-center gap-1.5"
                    :class="gpsStatus === 'ok'
                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-400/30'
                        : (gpsStatus === 'blocked'
                            ? 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-500/15 dark:text-rose-300 dark:border-rose-400/30'
                            : 'bg-zinc-50 text-zinc-600 border-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:border-zinc-700')">
                    <span class="w-1.5 h-1.5 rounded-full"
                        :class="gpsStatus === 'ok' ? 'bg-emerald-500' : (gpsStatus === 'blocked' ? 'bg-rose-500' : 'bg-zinc-400')"></span>
                    <span x-text="gpsStatus === 'ok' ? 'GPS OK' : (gpsStatus === 'blocked' ? 'GPS Nonaktif' : 'Mencari...')">GPS</span>
                </span>
                <button type="button" @click="detectPosition(true)"
                    class="px-3 py-1.5 rounded-xl bg-sky-500/15 hover:bg-sky-500/25 text-sky-600 dark:text-cyan-300 border border-sky-400/30 text-xs font-bold flex items-center gap-1.5 transition active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-crosshairs text-xs"></i>
                    <span>Perbarui GPS</span>
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
        <template x-if="gpsStatus === 'ok' && !isWfh && isWithinRadius === false">
            <div class="mb-2 px-2.5 py-2 rounded-xl bg-rose-500/10 border border-rose-400/35 text-rose-700 dark:text-rose-300 text-[10px] font-bold flex items-center justify-between gap-2">
                <span class="flex items-center gap-1.5">
                    <i class="fa-solid fa-location-crosshairs text-rose-500 shrink-0"></i>
                    <span>Di luar radius kantor (<span x-text="currentDistanceText"></span>) &bull; Mendekatlah ke kantor</span>
                </span>
                <span class="font-mono opacity-80 shrink-0" x-text="gpsAccuracy ? '±' + gpsAccuracy + 'm' : ''"></span>
            </div>
        </template>
        <template x-if="gpsStatus === 'ok' && (isWfh || isWithinRadius !== false)">
            <div class="mb-2 px-2.5 py-1.5 rounded-xl bg-emerald-500/10 border border-emerald-400/30 text-emerald-700 dark:text-emerald-300 text-[10px] font-semibold flex items-center justify-between">
                <span class="flex items-center gap-1.5">
                    <i class="fa-solid fa-circle-check text-emerald-500"></i>
                    <span x-text="isWfh ? 'Mode WFH Aktif &bull; Siap Presensi' : 'Dalam Radius Kantor &bull; Siap Presensi'"></span>
                </span>
                <span class="font-mono opacity-85" x-text="gpsAccuracy ? 'Akurasi ±' + gpsAccuracy + ' m' : 'Siap'"></span>
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

    <!-- Info Jabatan -->
    @if (!empty($user->jabatan?->nama))
        <div class="bg-white rounded-2xl p-3 border border-slate-200/90 shadow-md shadow-slate-900/5 flex items-center text-xs mb-3">
            <div class="flex items-center space-x-2.5 min-w-0">
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 border border-sky-100 flex items-center justify-center text-xs shrink-0">
                    <i class="fa-solid fa-id-badge"></i>
                </div>
                <div class="min-w-0">
                    <span class="text-[9px] md:text-xs uppercase tracking-wider text-slate-400 block font-bold leading-tight">Jabatan Pegawai</span>
                    <span class="font-extrabold text-slate-800 truncate block text-[11px] md:text-sm mt-0.5">{{ $user->jabatan->nama ?? '-' }}</span>
                </div>
            </div>
        </div>
    @endif

    <!-- TOMBOL ABSEN MELAYANG (SAMAR & NONAKTIF DI LUAR RADIUS, JELAS & AKTIF DI DALAM RADIUS) -->
    @if ((!$hariLiburHariIni || $modeSimulasi) && !$sudahMasuk && !$lewatBatasMasuk && !$belumBukaMasuk)
        <div class="fixed bottom-[90px] inset-x-0 z-30 flex justify-center pointer-events-none px-4">
            <div class="pointer-events-auto max-w-[440px] w-full flex items-center gap-2">
                <button type="button" @click="detectPosition(true)"
                    class="shrink-0 py-3 px-4 rounded-full bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-extrabold text-xs shadow-xl shadow-sky-600/30 border border-white/25 flex items-center justify-center gap-1.5 transition cursor-pointer"
                    title="Perbarui Lokasi GPS Sekarang">
                    <i class="fa-solid fa-crosshairs text-sm"></i>
                    <span>Perbarui GPS</span>
                </button>
                <button type="button" @click="doPunch('masuk')"
                    :disabled="loadingMasuk || (!isWfh && !modeSimulasi && isWithinRadius !== true)"
                    :class="(!isWfh && !modeSimulasi && isWithinRadius !== true)
                        ? 'opacity-40 grayscale cursor-not-allowed'
                        : 'opacity-100 cursor-pointer active:scale-95 hover:from-emerald-500 hover:to-teal-400'"
                    class="flex-1 py-3 px-4 rounded-full bg-gradient-to-r from-emerald-600 to-teal-500 text-white font-extrabold text-xs shadow-xl shadow-emerald-600/35 border border-white/25 flex items-center justify-center gap-2 transition-all duration-300">
                    <i x-show="!loadingMasuk" class="fa-solid fa-fingerprint text-sm"></i>
                    <i x-show="loadingMasuk" x-cloak class="fa-solid fa-circle-notch fa-spin text-sm"></i>
                    <span x-text="loadingMasuk ? 'Memproses Absen Datang...' : ((!isWfh && !modeSimulasi && isWithinRadius !== true) ? 'Di Luar Radius' : 'Tap Absen Datang')"></span>
                </button>
            </div>
        </div>
    @elseif (!$hariLiburHariIni && !$modeSimulasi && !$sudahMasuk && ($lewatBatasMasuk || $belumBukaMasuk))
        <div class="fixed bottom-[90px] inset-x-0 z-30 flex justify-center pointer-events-none px-4">
            <button type="button" disabled
                class="pointer-events-auto max-w-[360px] w-full py-3 px-5 rounded-full bg-slate-500 text-white font-extrabold text-xs shadow-lg border border-white/25 flex items-center justify-center gap-2 opacity-50 grayscale cursor-not-allowed">
                <i class="fa-solid fa-clock text-sm"></i>
                <span>{{ $belumBukaMasuk ? 'Absen Datang (Belum Buka)' : 'Absen Datang (Di Luar Jam)' }}</span>
            </button>
        </div>
    @elseif ($sudahMasuk && !$sudahPulang && !$belumJamPulang && !$lewatBatasPulang)
        <div class="fixed bottom-[90px] inset-x-0 z-30 flex justify-center pointer-events-none px-4">
            <div class="pointer-events-auto max-w-[440px] w-full flex items-center gap-2">
                <button type="button" @click="detectPosition(true)"
                    class="shrink-0 py-3 px-4 rounded-full bg-sky-600 hover:bg-sky-700 active:scale-95 text-white font-extrabold text-xs shadow-xl shadow-sky-600/30 border border-white/25 flex items-center justify-center gap-1.5 transition cursor-pointer"
                    title="Perbarui Lokasi GPS Sekarang">
                    <i class="fa-solid fa-crosshairs text-sm"></i>
                    <span>Perbarui GPS</span>
                </button>
                <button type="button" @click="doPunch('pulang')"
                    :disabled="loadingPulang || (!isWfh && !modeSimulasi && isWithinRadius !== true)"
                    :class="(!isWfh && !modeSimulasi && isWithinRadius !== true)
                        ? 'opacity-40 grayscale cursor-not-allowed'
                        : 'opacity-100 cursor-pointer active:scale-95 hover:from-amber-400 hover:to-orange-400'"
                    class="flex-1 py-3 px-4 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 text-white font-extrabold text-xs md:text-sm shadow-xl shadow-amber-500/35 border border-white/25 flex items-center justify-center gap-2 transition-all duration-300">
                    <i x-show="!loadingPulang" class="fa-solid fa-fingerprint text-sm"></i>
                    <i x-show="loadingPulang" x-cloak class="fa-solid fa-circle-notch fa-spin text-sm"></i>
                    <span x-text="loadingPulang ? 'Memproses Absen Pulang...' : ((!isWfh && !modeSimulasi && isWithinRadius !== true) ? 'Di Luar Radius' : 'Tap Absen Pulang')"></span>
                </button>
            </div>
        </div>
    @elseif ($sudahMasuk && !$sudahPulang && ($belumJamPulang || $lewatBatasPulang))
        <div class="fixed bottom-[90px] inset-x-0 z-30 flex justify-center pointer-events-none px-4">
            <button type="button" disabled
                class="pointer-events-auto max-w-[360px] w-full py-3 px-5 rounded-full bg-slate-500 text-white font-extrabold text-xs md:text-sm shadow-lg border border-white/25 flex items-center justify-center gap-2 opacity-60 grayscale cursor-not-allowed">
                <i class="fa-solid fa-clock text-sm"></i>
                <span>{{ $belumJamPulang ? "Absen Pulang (Mulai {$jamPulangResmi} WIB)" : 'Absen Pulang (Waktu Berakhir)' }}</span>
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
                    lastLat: null,
                    lastLng: null,
                    lastCoordsAt: 0,
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
                        document.addEventListener('livewire:navigating', () => {
                            if (this.map) {
                                this.map.remove();
                                this.map = null;
                            }
                        }, { once: true });
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

                        let bestPos = null;
                        let watchId = null;
                        let settled = false;

                        const applyPos = (pos, isFinal) => {
                            this.gpsWarning = false;
                            this.gpsStatus = 'ok';
                            this.gpsAccuracy = pos.coords.accuracy ? Math.round(pos.coords.accuracy) : null;
                            const uLat = pos.coords.latitude;
                            const uLng = pos.coords.longitude;
                            this.lastLat = uLat;
                            this.lastLng = uLng;
                            this.lastCoordsAt = Date.now();
                            this.userCoordsText = `${uLat.toFixed(5)}, ${uLng.toFixed(5)}`;
                            const d = this.calcDistance(this.officeLat, this.officeLng, uLat, uLng);
                            this.currentDistance = Math.round(d);
                            this.currentDistanceText = `${this.currentDistance} meter`;
                            this.isWithinRadius = this.modeSimulasi || this.isWfh || (this.currentDistance <= this.maxRadius);
                            this.updateUserMarker(uLat, uLng);

                            if (isFinal && manual) {
                                const outOfRadius = !this.modeSimulasi && !this.isWfh && this.isWithinRadius === false;
                                window.dispatchEvent(new CustomEvent('notify', {
                                    detail: outOfRadius
                                        ? {
                                            type: 'warning',
                                            title: 'Di Luar Radius Kantor',
                                            message: `Jarak Anda ${this.currentDistanceText} (maksimal radius ${this.maxRadius} meter). Silakan mendekat ke area kantor sesuai aturan.`
                                        }
                                        : {
                                            type: 'success',
                                            title: 'GPS OK',
                                            message: `Akurasi GPS ±${this.gpsAccuracy ?? '-'}m, jarak ${this.currentDistanceText}.`
                                        }
                                }));
                            }
                        };

                        const finishWatch = () => {
                            if (settled) return;
                            settled = true;
                            if (watchId !== null) {
                                navigator.geolocation.clearWatch(watchId);
                                watchId = null;
                            }
                            if (bestPos) {
                                applyPos(bestPos, true);
                            }
                        };

                        const timer = setTimeout(finishWatch, 6000);

                        watchId = navigator.geolocation.watchPosition(
                            (pos) => {
                                if (!bestPos || (pos.coords.accuracy && pos.coords.accuracy < (bestPos.coords.accuracy || 9999))) {
                                    bestPos = pos;
                                    applyPos(bestPos, false);
                                }
                                if (pos.coords.accuracy && pos.coords.accuracy <= 15) {
                                    clearTimeout(timer);
                                    finishWatch();
                                }
                            },
                            (err) => {
                                clearTimeout(timer);
                                if (watchId !== null) navigator.geolocation.clearWatch(watchId);
                                if (bestPos) {
                                    applyPos(bestPos, true);
                                    return;
                                }
                                this.gpsStatus = 'error';
                                this.gpsWarning = true;
                                if (manual) {
                                    window.dispatchEvent(new CustomEvent('notify', {
                                        detail: { type: 'warning', title: 'GPS Tidak Aktif', message: 'Pastikan GPS/Lokasi perangkat aktif dan izinkan akses lokasi.' }
                                    }));
                                }
                            },
                            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
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
                                timeout: 15000,
                                maximumAge: 0
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

                        const hasFreshCoords = this.lastLat !== null && this.lastLng !== null && (Date.now() - this.lastCoordsAt) < 120000;

                        if (this.modeSimulasi || hasFreshCoords || !navigator.geolocation) {
                            executeCall(this.lastLat ?? this.officeLat, this.lastLng ?? this.officeLng);
                            return;
                        }

                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                this.lastLat = pos.coords.latitude;
                                this.lastLng = pos.coords.longitude;
                                this.lastCoordsAt = Date.now();
                                executeCall(pos.coords.latitude, pos.coords.longitude);
                            },
                            (err) => {
                                console.warn('[ABSEN] GPS error fallback:', err);
                                executeCall(this.lastLat ?? this.officeLat, this.lastLng ?? this.officeLng);
                            },
                            {
                                enableHighAccuracy: false,
                                timeout: 4000,
                                maximumAge: 30000
                            }
                        );
                    }
                };
            };
        </script>
    @endscript
</div>
