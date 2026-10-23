<div class="p-5 pb-24 flex flex-col min-h-[calc(100vh-100px)]" x-data="typeof absensiApp === 'function' ? absensiApp() : {}" @trigger-absen.window="typeof triggerDoAbsen === 'function' ? triggerDoAbsen() : null">
    <!-- Header -->
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
                GPS ON
            </div>
        </div>
    </div>

    <!-- Live Digital Clock -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white rounded-2xl p-4 text-center shadow-md mb-4"
        x-data="{ time: '' }" x-init="setInterval(() => { const d = new Date();
            time = d.toLocaleTimeString('id-ID', { hour12: false }); }, 1000);
        time = (new Date()).toLocaleTimeString('id-ID', { hour12: false });">
        <span class="text-xs font-semibold uppercase tracking-widest text-sky-400">Waktu Saat Ini</span>
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
            <span
                class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $isWfh ? 'bg-amber-50 text-amber-600' : 'bg-slate-100 text-slate-600' }}">
                Radius: {{ $isWfh ? 'Bebas (WFH)' : $maxRadius . ' m' }}
            </span>
        </div>

        <!-- Real-Time Distance & Location Status -->
        <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
            <div class="flex items-center space-x-1.5">
                <span class="w-2 h-2 rounded-full"
                    :class="isWithinRadius || isWfh ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                <span class="text-[11px] font-medium text-slate-600">Jarak ke kantor:</span>
                <span class="font-bold font-mono text-[11px]"
                    :class="isWithinRadius || isWfh ? 'text-emerald-700' : 'text-rose-600'"
                    x-text="currentDistanceText">Mendeteksi GPS...</span>
            </div>
            <div>
                <template x-if="isWfh">
                    <span class="text-[10px] font-bold text-sky-600 bg-sky-50 px-2 py-0.5 rounded-full">Bebas
                        Radius</span>
                </template>
                <template x-if="!isWfh && isWithinRadius === true">
                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Dalam
                        Radius</span>
                </template>
                <template x-if="!isWfh && isWithinRadius === false">
                    <span class="text-[10px] font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full">Luar
                        Radius</span>
                </template>
            </div>
        </div>

        <!-- Admin Quick Setter: Set Office Location to My Current GPS -->
        @if ($isAdmin)
            <div class="mt-3 pt-2.5 border-t border-dashed border-amber-200 bg-amber-50/50 p-2.5 rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-amber-900 block flex items-center">
                            <i class="fa-solid fa-wand-magic-sparkles text-amber-600 mr-1.5"></i>
                            Admin: Atur Lokasi Kantor Cepat
                        </span>
                        <span class="text-[10px] text-amber-700">Set koordinat kantor ke posisi GPS Anda saat ini</span>
                    </div>
                    <button @click="setOfficeLocation()" :disabled="settingOffice"
                        class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-[10px] rounded-lg shadow-xs transition active:scale-95 flex items-center space-x-1 shrink-0">
                        <i class="fa-solid fa-location-crosshairs" :class="{ 'fa-spin': settingOffice }"></i>
                        <span x-text="settingOffice ? 'Menyimpan...' : 'Set Ke Posisi Saya'">Set Ke Posisi Saya</span>
                    </button>
                </div>
            </div>
        @endif
    </div>

    <!-- Info Card Masuk & Pulang -->
    <div class="w-full p-4 bg-white rounded-2xl shadow-xs border border-slate-100 mb-4 flex justify-between relative overflow-hidden">
        <div class="flex-1 flex flex-col items-center py-1">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg mb-2">
                <i class="fa-solid fa-right-to-bracket"></i>
            </div>
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-0.5">Jam Masuk</span>
            <span class="text-base font-bold {{ $absenHarian && $absenHarian->jam_masuk ? 'text-amber-600' : 'text-slate-300' }}">
                {{ $absenHarian->jam_masuk ?? '--:--' }}
            </span>
            <span class="text-[9px] text-slate-400 mt-0.5">Buka: {{ $bukaMasuk }} - {{ $tutupMasuk }}</span>
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
            <span class="text-[9px] text-slate-400 mt-0.5">Buka: {{ $jamPulangResmi }} - {{ $tutupPulang }}</span>
        </div>
    </div>

    <!-- Status & Panduan Tombol Navbar -->
    <div class="flex-1 flex flex-col items-center justify-center py-6 px-4 text-center my-auto">
        @if ($selesai)
            <div class="w-20 h-20 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl mb-3 shadow-inner">
                <i class="fa-solid fa-check-double"></i>
            </div>
            <h3 class="text-base font-extrabold text-slate-800">Presensi Lengkap Hari Ini</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-xs leading-relaxed">
                Absen masuk ({{ $absenHarian->jam_masuk }}) dan absen pulang ({{ $absenHarian->jam_pulang }}) telah tercatat di sistem.
            </p>
            <span class="mt-3 px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200/80 rounded-full text-[11px] font-bold">
                Tugas Kedinasan Selesai
            </span>

        @elseif($lewatJamMasuk)
            <div class="w-20 h-20 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-3xl mb-3 shadow-inner">
                <i class="fa-solid fa-ban"></i>
            </div>
            <h3 class="text-base font-extrabold text-rose-800">Batas Absen Masuk Terlewat</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-xs leading-relaxed">
                Batas akhir absen masuk (pukul {{ $tutupMasuk }} WIB) telah berakhir. Status Anda hari ini tercatat <strong>Tidak Hadir (Alpa)</strong> dan tombol absensi dinonaktifkan.
            </p>
            <span class="mt-3 px-3 py-1 bg-rose-50 text-rose-700 border border-rose-200 rounded-full text-[11px] font-bold">
                Tombol Absen Terkunci
            </span>

        @elseif($belumBukaMasuk)
            <div class="w-20 h-20 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center text-3xl mb-3 shadow-inner">
                <i class="fa-solid fa-hourglass-start"></i>
            </div>
            <h3 class="text-base font-extrabold text-slate-800">Absen Masuk Belum Dibuka</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-xs leading-relaxed">
                Presensi masuk pagi dibuka mulai pukul <strong>{{ $bukaMasuk }} WIB</strong> s.d <strong>{{ $tutupMasuk }} WIB</strong>.
            </p>
            <span class="mt-3 px-3 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-[11px] font-bold">
                Menunggu Pukul {{ $bukaMasuk }} WIB
            </span>

        @elseif($belumJamPulang)
            <div class="w-20 h-20 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center text-3xl mb-3 shadow-inner">
                <i class="fa-solid fa-business-time"></i>
            </div>
            <h3 class="text-base font-extrabold text-slate-800">Sedang Bertugas Dinas</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-xs leading-relaxed">
                Absen masuk pagi berhasil dicatat ({{ $absenHarian->jam_masuk }}). Presensi pulang dibuka mulai pukul <strong>{{ $jamPulangResmi }} WIB</strong>.
            </p>
            <span class="mt-3 px-3 py-1 bg-sky-50 text-sky-700 border border-sky-200 rounded-full text-[11px] font-bold">
                Buka Pulang: {{ $jamPulangResmi }} WIB
            </span>

        @elseif($lewatJamPulang)
            <div class="w-20 h-20 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-3xl mb-3 shadow-inner">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <h3 class="text-base font-extrabold text-rose-800">Batas Absen Pulang Berakhir</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-xs leading-relaxed">
                Batas akhir absen pulang malam hari (pukul {{ $tutupPulang }} WIB) telah berakhir.
            </p>

        @elseif($siapAbsenPulang)
            <div wire:click="punch" 
                 class="w-full flex flex-col items-center cursor-pointer active:scale-95 transition-transform">
                <div class="w-20 h-20 rounded-full bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-3xl mb-3 animate-pulse shadow-md">
                    <i class="fa-solid fa-fingerprint text-4xl"></i>
                </div>
                <h3 class="text-base font-extrabold text-emerald-700">Siap Mengambil Absen Pulang</h3>
                <p class="text-xs text-slate-600 mt-1.5 max-w-xs leading-relaxed">
                    Waktu dinas hari ini telah selesai. Silakan tekan tombol bulat <span class="font-bold text-sky-700">ABSEN</span> di bawah atau tap kartu ini untuk mencatat kepulangan Anda.
                </p>
                <div class="mt-4 flex items-center space-x-1.5 text-sky-600 text-xs font-bold animate-bounce">
                    <span>Tap untuk Ambil Absen Pulang</span>
                    <i class="fa-solid fa-arrow-down"></i>
                </div>
            </div>

        @elseif($siapAbsenMasuk)
            <div wire:click="punch" 
                 class="w-full flex flex-col items-center cursor-pointer active:scale-95 transition-transform">
                <div class="w-20 h-20 rounded-full bg-sky-500/10 text-sky-600 flex items-center justify-center text-3xl mb-3 animate-pulse shadow-md">
                    <i class="fa-solid fa-fingerprint text-4xl"></i>
                </div>
                <h3 class="text-base font-extrabold text-sky-800">Siap Mengambil Absen Masuk</h3>
                <p class="text-xs text-slate-600 mt-1.5 max-w-xs leading-relaxed">
                    Silakan tekan tombol bulat <span class="font-bold text-sky-700">ABSEN</span> di bilah bawah atau tap kartu ini untuk mencatat kehadiran pagi Anda.
                </p>
                <div class="mt-4 flex items-center space-x-1.5 text-sky-600 text-xs font-bold animate-bounce">
                    <span>Tap untuk Ambil Absen Masuk</span>
                    <i class="fa-solid fa-arrow-down"></i>
                </div>
            </div>
        @endif
    </div>

    <!-- GPS Info & Radius Footer Note -->
    <div class="flex flex-col items-center mt-auto pt-2 pb-1 text-slate-400 text-center">
        <div class="flex items-center text-[11px] font-semibold text-slate-500">
            <i class="fa-solid fa-location-crosshairs text-sky-500 text-xs mr-1.5"></i>
            <span>Geofencing: Titik ({{ round($officeLat, 4) }}, {{ round($officeLng, 4) }})</span>
        </div>
        <p class="text-[10px] text-slate-400 mt-0.5">Akurasi GPS tinggi diwajibkan untuk absensi.</p>
    </div>

@script
<script>
    // Global Attendance Punch Trigger (Bisa dipanggil dari Navbar maupun Card)
    window.triggerDoAbsen = function() {
        console.log('[ABSEN] triggerDoAbsen invoked');

        @if ($selesai)
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { 
                    type: 'info', 
                    title: 'Presensi Lengkap', 
                    message: 'Anda sudah menyelesaikan absensi masuk dan pulang untuk hari ini.' 
                } 
            }));
            return;
        @elseif($lewatJamMasuk)
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { 
                    type: 'error', 
                    title: 'Batas Absen Masuk Berakhir!', 
                    message: 'Batas akhir absen masuk pukul {{ $tutupMasuk }} WIB telah lewat. Status Anda tercatat Alpa (Tidak Hadir) hari ini dan tidak dapat mengambil absen pulang.' 
                } 
            }));
            return;
        @elseif($belumBukaMasuk)
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { 
                    type: 'warning', 
                    title: 'Diluar Jam Absen (Belum Dibuka)', 
                    message: 'Saat ini belum memasuki jam presensi dinas. Absen masuk pagi dibuka mulai pukul {{ $bukaMasuk }} WIB s.d {{ $tutupMasuk }} WIB.' 
                } 
            }));
            return;
        @elseif($belumJamPulang)
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { 
                    type: 'warning', 
                    title: 'Belum Waktu Pulang!', 
                    message: 'Saat ini masih dalam jam kerja dinas. Presensi pulang hari ini baru dibuka mulai pukul {{ $jamPulangResmi }} WIB.' 
                } 
            }));
            return;
        @elseif($lewatJamPulang)
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { 
                    type: 'error', 
                    title: 'Batas Absen Pulang Berakhir!', 
                    message: 'Batas akhir pengambilan absen pulang malam hari (pukul {{ $tutupPulang }} WIB) telah berakhir.' 
                } 
            }));
            return;
        @endif

        // Ubah tampilan tombol navbar menjadi loading PROSES
        const btnText = document.getElementById('btn-absen-text');
        const btnIcon = document.getElementById('btn-absen-icon');
        const btnNav = document.getElementById('btn-absen-navbar');
        if (btnText) btnText.innerText = 'PROSES';
        if (btnIcon) btnIcon.className = 'fa-solid fa-circle-notch fa-spin';
        if (btnNav) btnNav.disabled = true;

        const resetBtn = () => {
            if (btnText) btnText.innerText = 'ABSEN';
            if (btnIcon) btnIcon.className = 'fa-solid fa-fingerprint';
            if (btnNav) btnNav.disabled = false;
        };

        const doSubmit = (lat, lng) => {
            $wire.punch(lat, lng)
                .then(() => {
                    resetBtn();
                })
                .catch((err) => {
                    console.error('[ABSEN] Punch error:', err);
                    resetBtn();
                    window.dispatchEvent(new CustomEvent('notify', { 
                        detail: { 
                            type: 'error', 
                            title: 'Gagal Menghubungi Server', 
                            message: 'Terjadi gangguan saat mencatat presensi.' 
                        } 
                    }));
                });
        };

        if (!navigator.geolocation) {
            if (window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost') {
                console.warn('[ABSEN] Geolocation tidak tersedia di browser, memakai koordinat kantor untuk pengujian lokal.');
                doSubmit({{ $officeLat }}, {{ $officeLng }});
                return;
            }
            resetBtn();
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { 
                    type: 'error', 
                    title: 'GPS Tidak Didukung', 
                    message: 'Browser/perangkat Anda tidak mendukung fitur Geolocation GPS.' 
                } 
            }));
            return;
        }

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                console.log('[ABSEN] GPS coordinates acquired:', lat, lng);
                doSubmit(lat, lng);
            },
            (error) => {
                console.warn('[ABSEN] GPS error:', error);
                // Fallback khusus pengujian lokal laptop jika browser menolak/tidak ada hardware GPS
                if (window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost') {
                    console.info('[ABSEN] GPS laptop tidak terdeteksi, memakai titik kantor untuk kemudahan testing lokal.');
                    doSubmit({{ $officeLat }}, {{ $officeLng }});
                    return;
                }

                resetBtn();
                let msg = 'Gagal membaca koordinat GPS perangkat.';
                if (error.code === 1) {
                    msg = 'Akses lokasi ditolak oleh browser/ponsel. Mohon izinkan akses GPS di pengaturan browser Anda.';
                } else if (error.code === 3) {
                    msg = 'Waktu deteksi GPS habis. Pastikan GPS aktif dan sinyal memadai.';
                }
                window.dispatchEvent(new CustomEvent('notify', { 
                    detail: { 
                        type: 'error', 
                        title: 'Akses Lokasi GPS Diperlukan', 
                        message: msg 
                    } 
                }));
            },
            {
                enableHighAccuracy: true,
                timeout: 8000
            }
        );
    };

    // Pasang listener CustomEvent
    window.addEventListener('trigger-absen', function() {
        if (typeof window.triggerDoAbsen === 'function') {
            window.triggerDoAbsen();
        }
    });

    window.absensiApp = function() {
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

            doAbsen() {
                if (typeof window.triggerDoAbsen === 'function') {
                    window.triggerDoAbsen();
                }
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
                        if (window.location.hostname === '127.0.0.1' || window.location.hostname === 'localhost') {
                            this.currentDistance = 0;
                            this.currentDistanceText = '0 meter';
                            this.isWithinRadius = true;
                        } else {
                            this.currentDistanceText = 'GPS belum aktif';
                        }
                    }, {
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
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                return R * c;
            },

            setOfficeLocation() {
                if (this.settingOffice) return;
                this.settingOffice = true;

                if (!navigator.geolocation) {
                    window.dispatchEvent(new CustomEvent('notify', { 
                        detail: { 
                            type: 'error', 
                            title: 'GPS Tidak Didukung', 
                            message: 'Browser tidak mendukung sensor GPS.' 
                        } 
                    }));
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
                        window.dispatchEvent(new CustomEvent('notify', { 
                            detail: { 
                                type: 'error', 
                                title: 'Deteksi GPS Gagal', 
                                message: 'Gagal mendeteksi posisi GPS perangkat Anda.' 
                            } 
                        }));
                        this.settingOffice = false;
                    }, {
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
