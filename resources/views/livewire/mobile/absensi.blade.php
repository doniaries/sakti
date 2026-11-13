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
        
        <!-- Baris 1: Live Digital Clock & Identity Header -->
        <div class="flex items-center justify-between pb-3 border-b border-white/15 relative z-10">
            <div class="flex items-center space-x-2.5">
                <div class="w-8 h-8 rounded-xl bg-white/15 backdrop-blur-md text-sky-200 flex items-center justify-center text-sm border border-white/20 shrink-0">
                    <i class="fa-regular fa-clock"></i>
                </div>
                <div>
                    <span class="text-[9px] uppercase tracking-wider text-sky-200/80 block font-bold leading-tight">Waktu Server Resmi</span>
                    <div class="flex items-baseline space-x-1.5 mt-0.5">
                        <span class="text-2xl font-black font-mono tracking-tight text-white leading-none" x-text="time">--:--:--</span>
                        <span class="text-[10px] font-bold text-amber-300 font-mono">WIB</span>
                    </div>
                </div>
            </div>
            <div class="text-right flex flex-col items-end">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-black/30 backdrop-blur-md border border-white/15 {{ $isWfh ? 'text-amber-300' : 'text-emerald-300' }} flex items-center shadow-xs mb-1">
                    <span class="w-1.5 h-1.5 rounded-full {{ $isWfh ? 'bg-amber-400' : 'bg-emerald-400' }} animate-pulse mr-1.5 inline-block"></span>
                    <span>{{ $isWfh ? 'WFH' : 'WFO' }}</span>
                </span>
                <div class="flex items-center text-xs font-bold text-white drop-shadow-xs">
                    <i class="fa-regular fa-calendar-days text-sky-300 text-[11px] mr-1.5"></i>
                    <span>{{ now()->translatedFormat('l, d F Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Baris 2: Jadwal & Toleransi (Tile Terpadu Concentric Radius) -->
        <div class="grid grid-cols-2 gap-2 my-2.5 text-xs relative z-10">
            <!-- Jadwal Kerja -->
            <div class="bg-black/20 backdrop-blur-xs rounded-xl p-2.5 border border-white/15 flex items-center space-x-2">
                <div class="w-7 h-7 rounded-lg bg-sky-400/20 text-sky-300 flex items-center justify-center text-xs shrink-0 border border-sky-400/20">
                    <i class="fa-regular fa-calendar-check"></i>
                </div>
                <div class="min-w-0">
                    <span class="text-[9px] text-sky-200/80 uppercase font-bold tracking-wider block truncate">Jadwal Kerja (ASN 7j 30m)</span>
                    <span class="font-extrabold font-mono text-xs text-white block truncate">{{ $jamMasukResmi }} - {{ $jamPulangResmi }} <span class="text-[9px] font-sans font-medium text-sky-200">WIB</span></span>
                    <span class="text-[9px] text-sky-200/80 block truncate">
                        {{ $selesai ? "Tercatat: {$durasiKerjaHariIni} / {$targetHarianAsn}" : '37j 30m/minggu (non-istirahat)' }}
                    </span>
                </div>
            </div>

            <!-- Toleransi Terlambat -->
            <div class="bg-black/20 backdrop-blur-xs rounded-xl p-2.5 border border-white/15 flex items-center space-x-2">
                <div class="w-7 h-7 rounded-lg bg-amber-400/20 text-amber-300 flex items-center justify-center text-xs shrink-0 border border-amber-400/20">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div class="min-w-0">
                    <span class="text-[9px] text-amber-200/90 uppercase font-bold tracking-wider block truncate">Batas Hadir</span>
                    <span class="font-extrabold text-xs text-amber-300 block truncate">s.d {{ $batasToleransi }} <span class="text-[9px] font-normal text-amber-200/80">(+{{ $toleransi }}m)</span></span>
                </div>
            </div>
        </div>

        <!-- Baris 3: Lokasi Kantor & Jarak User (Satu Baris Kompak & Elegan) -->
        <div class="bg-black/25 backdrop-blur-xs rounded-xl p-2.5 border border-white/15 flex items-center justify-between relative z-10">
            <div class="flex items-center space-x-2.5 min-w-0 pr-2">
                <div class="w-7 h-7 rounded-lg bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 flex items-center justify-center text-xs shrink-0">
                    <i class="fa-solid fa-location-dot"></i>
                </div>
                <div class="min-w-0">
                    <span class="text-xs font-extrabold text-white truncate block">
                        {{ Str::before($alamatKantor, ',') }}
                    </span>
                    <span class="text-[10px] text-sky-200/75 block truncate">
                        Radius {{ $maxRadius }} meter &bull; {{ Str::after($alamatKantor, ',') ? Str::limit(trim(Str::after($alamatKantor, ',')), 32) : 'Muaro Sijunjung' }}
                    </span>
                </div>
            </div>

            <!-- Status Jarak Real-Time Pill -->
            <div class="shrink-0 text-right">
                <template x-if="isWfh">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/20 border border-amber-400/30 text-amber-300">
                        <i class="fa-solid fa-house-laptop mr-1 text-[9px]"></i> Bebas WFH
                    </span>
                </template>
                <template x-if="!isWfh && isWithinRadius === true">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/25 border border-emerald-400/40 text-emerald-300 shadow-xs">
                        <i class="fa-solid fa-circle-check mr-1 text-[9px]"></i> <span x-text="currentDistanceText">0 meter</span>
                    </span>
                </template>
                <template x-if="!isWfh && isWithinRadius === false">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-500/25 border border-rose-400/40 text-rose-300 shadow-xs">
                        <i class="fa-solid fa-circle-xmark mr-1 text-[9px]"></i> <span x-text="currentDistanceText">Luar Radius</span>
                    </span>
                </template>
                <template x-if="!isWfh && isWithinRadius === null">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-400/20 border border-sky-300/30 text-sky-200 animate-pulse">
                        <i class="fa-solid fa-satellite-dish mr-1 text-[9px]"></i> Cek GPS...
                    </span>
                </template>
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
                    <button type="button" @click="doPunch('masuk')"
                        :disabled="loadingMasuk || (!isWfh && !modeSimulasi && isWithinRadius === false)"
                        :class="(!isWfh && !modeSimulasi && isWithinRadius === false) ? 'opacity-50 cursor-not-allowed filter grayscale' : 'cursor-pointer'"
                        class="w-full py-2.5 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md shadow-emerald-600/20 enabled:active:scale-[0.96] transition-transform duration-150 ease-out disabled:scale-100 flex items-center justify-center space-x-1.5">
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
                    <button type="button" @click="doPunch('pulang')"
                        :disabled="loadingPulang || (!isWfh && !modeSimulasi && isWithinRadius === false)"
                        :class="(!isWfh && !modeSimulasi && isWithinRadius === false) ? 'opacity-50 cursor-not-allowed filter grayscale' : 'cursor-pointer'"
                        class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-extrabold text-xs shadow-md shadow-amber-500/20 enabled:active:scale-[0.96] transition-transform duration-150 ease-out disabled:scale-100 flex items-center justify-center space-x-1.5">
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

    <!-- Peta & Titik Koordinat GPS -->
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
                    x-text="currentDistanceText"></span>
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

    <div class="flex flex-col items-center mt-auto pt-1 pb-1 text-slate-400 text-center">
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
                            this.userMarker = L.circleMarker([lat, lng], {
                                radius: 8,
                                color: '#ffffff',
                                weight: 2,
                                fillColor: '#0ea5e9',
                                fillOpacity: 1
                            }).addTo(this.map).bindPopup('Posisi Anda Sekarang');
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
                                const uLat = this.modeSimulasi ? this.officeLat : pos.coords.latitude;
                                const uLng = this.modeSimulasi ? this.officeLng : pos.coords.longitude;
                                this.userCoordsText = `${uLat.toFixed(5)}, ${uLng.toFixed(5)}`;
                                const d = this.calcDistance(this.officeLat, this.officeLng, uLat, uLng);
                                this.currentDistance = Math.round(d);
                                this.currentDistanceText = this.modeSimulasi ? '0 meter (Simulasi)' : `${this.currentDistance} meter`;
                                this.isWithinRadius = this.modeSimulasi || this.isWfh || (this.currentDistance <= this.maxRadius);
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
