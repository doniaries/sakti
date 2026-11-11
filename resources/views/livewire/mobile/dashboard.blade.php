<div class="p-5 pb-20">
    @php
        $pengaturan = \App\Models\PengaturanAplikasi::getPengaturan();
    @endphp
    <!-- Top Header: Logo, Nama Aplikasi & Logout -->
    <div class="flex justify-between items-center mb-5">
        <div class="flex items-center space-x-2.5">
            @if (!empty($pengaturan->logo) && file_exists(public_path('storage/' . $pengaturan->logo)))
                <img src="{{ asset('storage/' . $pengaturan->logo) }}" alt="Logo Aplikasi"
                    class="w-10 h-10 rounded-xl object-contain bg-white shadow-md shadow-sky-500/15 p-1 border border-slate-200/80">
            @else
                <div
                    class="w-10 h-10 rounded-xl bg-gradient-to-tr from-sky-600 via-sky-500 to-teal-400 text-white flex items-center justify-center shadow-md shadow-sky-500/25 border border-white/20">
                    <i class="fa-solid fa-leaf text-lg"></i>
                </div>
            @endif
            <div>
                <h1 class="text-lg font-black text-slate-800 tracking-tight leading-tight uppercase">
                    {{ $pengaturan->nama_aplikasi }}</h1>
                <p class="text-[10px] font-bold text-sky-600 tracking-wider uppercase">
                    {{ $pengaturan->sub_nama ?? 'Dinas Lingkungan Hidup' }}</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            @if (!empty($user->whatsapp_url))
                <a href="{{ $user->whatsapp_url }}" target="_blank" rel="noopener noreferrer"
                    class="px-2.5 py-1.5 rounded-xl bg-emerald-500/15 text-emerald-700 hover:bg-emerald-500 hover:text-white border border-emerald-300 font-bold text-[11px] flex items-center transition shadow-xs active:scale-95"
                    title="Buka WhatsApp">
                    <i class="fa-brands fa-whatsapp text-sm mr-1 text-emerald-600"></i> WA
                </a>
            @endif
            @if (auth()->user() &&
                    auth()->user()->hasAnyRole(['super_admin', 'admin']))
                <a href="{{ url('/admin') }}"
                    class="px-2.5 py-1.5 rounded-xl bg-amber-500/15 text-amber-700 hover:bg-amber-500 hover:text-white border border-amber-300 font-bold text-[11px] flex items-center transition shadow-xs"
                    title="Buka Panel Admin">
                    <i class="fa-solid fa-shield-halved mr-1"></i> Admin
                </a>
            @endif
            <a href="{{ route('portal') }}"
                class="px-2.5 py-1.5 rounded-xl bg-sky-500/15 text-sky-700 hover:bg-sky-500 hover:text-white border border-sky-300 font-bold text-[11px] flex items-center transition shadow-xs"
                title="Kembali ke Beranda Portal">
                <i class="fa-solid fa-house mr-1"></i> Portal
            </a>
            <button wire:click="logout"
                class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-500 hover:text-rose-600 transition flex items-center justify-center border border-slate-200/60"
                title="Keluar">
                <i class="fa-solid fa-right-from-bracket text-sm"></i>
            </button>
        </div>
    </div>

    <!-- Greeting Card -->
    <div
        class="w-full p-5 rounded-3xl bg-gradient-to-br from-[#0F172A] via-[#0369A1] to-[#0284C7] text-white shadow-xl shadow-sky-950/20 mb-6 relative overflow-hidden border border-white/15">
        <!-- Background Glow Accent & Decorative Watermark Icon -->
        <div class="absolute -right-6 -bottom-6 w-36 h-36 bg-sky-400/20 rounded-full blur-2xl pointer-events-none">
        </div>
        <div class="absolute -right-3 -top-3 text-white/[0.07] text-8xl pointer-events-none">
            <i class="fa-solid fa-fingerprint"></i>
        </div>

        @php
            $hour = now()->hour;
            $greeting =
                $hour < 12
                    ? 'Selamat Pagi'
                    : ($hour < 15
                        ? 'Selamat Siang'
                        : ($hour < 18
                            ? 'Selamat Sore'
                            : 'Selamat Malam'));
            $greetingIcon =
                $hour < 12
                    ? 'fa-sun text-amber-400'
                    : ($hour < 15
                        ? 'fa-cloud-sun text-amber-300'
                        : ($hour < 18
                            ? 'fa-cloud-sun text-orange-400'
                            : 'fa-moon text-indigo-200'));
        @endphp

        <div class="flex justify-between items-start relative z-10">
            <div class="flex-1 pr-3">
                <div class="flex items-center space-x-1.5 text-xs font-bold text-sky-200 tracking-wide mb-1">
                    <i class="fa-solid {{ $greetingIcon }}"></i>
                    <span>{{ $greeting }},</span>
                </div>
                <h2 class="text-xl font-extrabold tracking-tight text-white drop-shadow-xs">{{ $user->name }}</h2>
                <p class="text-xs font-semibold text-sky-200/90 mt-0.5 truncate flex items-center">
                    <i class="fa-solid fa-id-badge text-[10px] mr-1.5 text-sky-300"></i>
                    <span>{{ $user->jabatan->nama ?? 'Pegawai' }}</span>
                </p>

                <!-- Tanggal & Jam Aktif Server -->
                <div class="flex items-center flex-wrap gap-2 text-xs text-white/90 mt-2.5" x-data="{
                    hours: '--',
                    minutes: '--',
                    colon: true,
                    updateClock() {
                        const d = new Date();
                        this.hours = String(d.getHours()).padStart(2, '0');
                        this.minutes = String(d.getMinutes()).padStart(2, '0');
                        this.colon = !this.colon;
                    }
                }"
                    x-init="updateClock();
                    setInterval(() => updateClock(), 1000);">
                    <span class="flex items-center font-medium">
                        <i class="fa-regular fa-calendar text-sky-300 mr-1.5"></i>
                        {{ now()->translatedFormat('l, d F Y') }}
                    </span>
                    <span class="text-white/40">•</span>
                    <!-- Jam Aktif Server Real-Time (HH:MM WIB) -->
                    <span
                        class="flex items-center bg-black/30 backdrop-blur-md px-2.5 py-1 rounded-full font-mono text-[11px] font-bold text-amber-300 border border-white/15 shadow-inner">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 inline-block animate-pulse"></span>
                        <span x-text="hours">--</span><span class="inline-block transition-opacity duration-200"
                            :class="colon ? 'opacity-100' : 'opacity-20'">:</span><span
                            x-text="minutes">--</span>&nbsp;WIB
                    </span>
                </div>
            </div>

            <!-- Foto Avatar Pegawai di Dashboard (Bisa Klik / Ketuk untuk Ganti Foto) -->
            <div class="relative shrink-0">
                <label class="relative block cursor-pointer group active:scale-95 transition-transform" title="Klik untuk ganti foto profil">
                    <input type="file" wire:model="new_avatar" accept="image/jpeg,image/png,image/webp" class="hidden">
                    <img src="{{ $user->avatar_image_url }}" alt="{{ $user->name }}"
                        class="w-14 h-14 rounded-2xl object-cover border-2 border-white/50 shadow-lg shadow-sky-950/40 aspect-square">
                    <!-- Overlay Tombol Ganti Foto / Kamera -->
                    <div class="absolute inset-0 bg-slate-900/60 rounded-2xl flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity backdrop-blur-[1px]">
                        <i class="fa-solid fa-camera text-white text-xs"></i>
                        <span class="text-[8px] font-bold text-white uppercase tracking-tighter mt-0.5">Ubah</span>
                    </div>
                    <!-- Loading Spinner saat Upload -->
                    <div wire:loading wire:target="new_avatar" class="absolute inset-0 bg-slate-950/75 rounded-2xl flex items-center justify-center">
                        <i class="fa-solid fa-circle-notch fa-spin text-white text-sm"></i>
                    </div>
                    <!-- Live Pulse / Radar Blinking Status Badge -->
                    <span class="absolute -bottom-1 -right-1 flex h-4 w-4" title="Online / Aktif">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span
                            class="relative inline-flex rounded-full h-4 w-4 bg-emerald-400 border-2 border-slate-900 shadow-xs"></span>
                    </span>
                </label>
            </div>
        </div>

        <!-- Kartu Aksi Presensi Interaktif: Absen Masuk & Absen Keluar -->
        <div class="grid grid-cols-2 gap-2.5 mt-5 relative z-10" x-data="dashboardAttendance()">
            <!-- Tombol 1: Masuk -->
            <button type="button"
                @click="absen('masuk')"
                :disabled="loadingMasuk || loadingKeluar"
                class="flex items-center p-3 rounded-2xl border text-left enabled:active:scale-[0.96] transition-transform duration-150 ease-out disabled:scale-100 cursor-pointer relative overflow-hidden group {{ $hariIni['sudah_masuk'] ? 'bg-white/20 border-white/30 backdrop-blur-md shadow-inner' : ($hariIni['lewat_batas_masuk'] ? 'bg-rose-950/30 border-rose-400/30 backdrop-blur-xs' : 'bg-white/10 hover:bg-white/20 border-white/25 backdrop-blur-xs shadow-sm ring-1 ring-white/20') }}">
                
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm shadow-xs shrink-0 {{ $hariIni['sudah_masuk'] ? 'bg-emerald-400 text-slate-900 shadow-emerald-500/30' : ($hariIni['lewat_batas_masuk'] ? 'bg-rose-500/20 text-rose-300 border border-rose-400/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-400/30') }}">
                    <template x-if="loadingMasuk">
                        <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                    </template>
                    <template x-if="!loadingMasuk">
                        <i class="fa-solid {{ $hariIni['sudah_masuk'] ? 'fa-check' : ($hariIni['lewat_batas_masuk'] ? 'fa-lock' : 'fa-right-to-bracket') }}"></i>
                    </template>
                </div>
                
                <div class="ml-2.5 flex flex-col min-w-0">
                    <span class="text-[10px] text-white/70 uppercase font-bold tracking-wider truncate">Absen Masuk</span>
                    <span class="text-sm font-extrabold font-mono text-white truncate">
                        <template x-if="loadingMasuk">
                            <span class="text-xs font-sans text-sky-200">Mencatat...</span>
                        </template>
                        <template x-if="!loadingMasuk">
                            <span>{{ !empty($hariIni['jam_masuk']) ? substr($hariIni['jam_masuk'], 0, 5) . ' WIB' : ($hariIni['lewat_batas_masuk'] ? 'Lewat Batas' : 'Tap Masuk') }}</span>
                        </template>
                    </span>
                    @if ($hariIni['sudah_masuk'])
                        <span class="text-[9px] font-bold text-emerald-300 flex items-center mt-0.5">
                            <i class="fa-solid fa-circle-check text-[8px] mr-1"></i>Tercatat
                        </span>
                    @elseif ($hariIni['lewat_batas_masuk'])
                        <span class="text-[9px] font-bold text-rose-300 flex items-center mt-0.5">
                            <i class="fa-solid fa-circle-xmark text-[8px] mr-1"></i>Alpa (09:00)
                        </span>
                    @else
                        <span class="text-[9px] font-bold text-emerald-200/80 flex items-center mt-0.5" x-show="!loadingMasuk">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse mr-1 inline-block"></span>Siap Tap
                        </span>
                    @endif
                </div>
            </button>

            <!-- Tombol 2: Keluar (di sebelah tombol masuk) -->
            <button type="button"
                @click="absen('pulang')"
                :disabled="loadingMasuk || loadingKeluar"
                class="flex items-center p-3 rounded-2xl border text-left enabled:active:scale-[0.96] transition-transform duration-150 ease-out disabled:scale-100 cursor-pointer relative overflow-hidden group {{ $hariIni['sudah_pulang'] ? 'bg-white/20 border-white/30 backdrop-blur-md shadow-inner' : ($hariIni['sudah_masuk'] ? ($hariIni['belum_jam_pulang'] ? 'bg-black/25 border-white/15 backdrop-blur-xs' : 'bg-gradient-to-br from-amber-500/25 to-sky-500/25 hover:from-amber-500/35 hover:to-sky-500/35 border-amber-300/40 backdrop-blur-md shadow-md ring-1 ring-amber-400/40') : 'bg-black/20 border-white/10 opacity-75 backdrop-blur-xs') }}">
                
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm shadow-xs shrink-0 {{ $hariIni['sudah_pulang'] ? 'bg-sky-400 text-slate-900 shadow-sky-500/30' : ($hariIni['sudah_masuk'] ? ($hariIni['belum_jam_pulang'] ? 'bg-white/10 text-white/50 border border-white/15' : 'bg-amber-400 text-slate-950 shadow-amber-400/30') : 'bg-white/10 text-white/40') }}">
                    <template x-if="loadingKeluar">
                        <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                    </template>
                    <template x-if="!loadingKeluar">
                        <i class="fa-solid {{ $hariIni['sudah_pulang'] ? 'fa-check-double' : ($hariIni['sudah_masuk'] ? ($hariIni['belum_jam_pulang'] ? 'fa-lock' : 'fa-right-from-bracket') : 'fa-lock') }}"></i>
                    </template>
                </div>
                
                <div class="ml-2.5 flex flex-col min-w-0">
                    <span class="text-[10px] text-white/70 uppercase font-bold tracking-wider truncate">Absen Keluar</span>
                    <span class="text-sm font-extrabold font-mono text-white truncate">
                        <template x-if="loadingKeluar">
                            <span class="text-xs font-sans text-sky-200">Mencatat...</span>
                        </template>
                        <template x-if="!loadingKeluar">
                            <span>{{ !empty($hariIni['jam_pulang']) ? substr($hariIni['jam_pulang'], 0, 5) . ' WIB' : ($hariIni['sudah_masuk'] ? ($hariIni['belum_jam_pulang'] ? 'Buka ' . $hariIni['jam_pulang_resmi'] : 'Tap Keluar') : '--:--') }}</span>
                        </template>
                    </span>
                    @if ($hariIni['sudah_pulang'])
                        <span class="text-[9px] font-bold text-sky-300 flex items-center mt-0.5">
                            <i class="fa-solid fa-circle-check text-[8px] mr-1"></i>Selesai
                        </span>
                    @elseif ($hariIni['sudah_masuk'])
                        @if ($hariIni['belum_jam_pulang'])
                            <span class="text-[9px] font-medium text-amber-200/70 flex items-center mt-0.5">
                                <i class="fa-solid fa-clock text-[8px] mr-1"></i>Belum Jam Pulang
                            </span>
                        @else
                            <span class="text-[9px] font-bold text-amber-200 flex items-center mt-0.5" x-show="!loadingKeluar">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse mr-1 inline-block"></span>Siap Pulang
                            </span>
                        @endif
                    @else
                        <span class="text-[9px] font-medium text-white/40 mt-0.5">
                            Menunggu Masuk
                        </span>
                    @endif
                </div>
            </button>
        </div>
    </div>

    <!-- Menu Layanan Kepegawaian (Izin, Sakit, Cuti, WFH, Hari Libur, Reputasi) -->
    <div class="mb-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-base font-extrabold text-slate-800 tracking-tight">Layanan Kepegawaian</h3>
            <a href="{{ route('mobile.izin.index') }}" wire:navigate
                class="text-xs font-bold text-sky-600 hover:text-sky-700 flex items-center">
                <span>Riwayat Izin</span>
                <i class="fa-solid fa-chevron-right text-[10px] ml-1"></i>
            </a>
        </div>
        <div class="grid grid-cols-4 gap-2.5">
            <!-- Izin -->
            <a href="{{ route('mobile.izin.create', ['kategori' => 'izin']) }}" wire:navigate
                class="bg-white p-3 rounded-2xl border border-slate-100 shadow-xs flex flex-col items-center hover:bg-sky-50/40 active:scale-95 transition-all text-center">
                <div
                    class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-base mb-1.5 shadow-xs">
                    <i class="fa-solid fa-envelope-open-text"></i>
                </div>
                <span class="text-xs font-bold text-slate-800">Izin</span>
                <span class="text-[9px] text-slate-400 font-medium">Pribadi</span>
            </a>

            <!-- Sakit -->
            <a href="{{ route('mobile.izin.create', ['kategori' => 'sakit']) }}" wire:navigate
                class="bg-white p-3 rounded-2xl border border-slate-100 shadow-xs flex flex-col items-center hover:bg-rose-50/40 active:scale-95 transition-all text-center">
                <div
                    class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-base mb-1.5 shadow-xs">
                    <i class="fa-solid fa-notes-medical"></i>
                </div>
                <span class="text-xs font-bold text-slate-800">Sakit</span>
                <span class="text-[9px] text-slate-400 font-medium">Dokter</span>
            </a>

            <!-- Cuti -->
            <a href="{{ route('mobile.izin.create', ['kategori' => 'cuti']) }}" wire:navigate
                class="bg-white p-3 rounded-2xl border border-slate-100 shadow-xs flex flex-col items-center hover:bg-amber-50/40 active:scale-95 transition-all text-center">
                <div
                    class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base mb-1.5 shadow-xs">
                    <i class="fa-solid fa-umbrella-beach"></i>
                </div>
                <span class="text-xs font-bold text-slate-800">Cuti</span>
                <span class="text-[9px] text-slate-400 font-medium">Tahunan</span>
            </a>

            <!-- WFH -->
            <a href="{{ route('mobile.izin.create', ['kategori' => 'wfh']) }}" wire:navigate
                class="bg-white p-3 rounded-2xl border border-slate-100 shadow-xs flex flex-col items-center hover:bg-teal-50/40 active:scale-95 transition-all text-center">
                <div
                    class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-base mb-1.5 shadow-xs">
                    <i class="fa-solid fa-house-laptop"></i>
                </div>
                <span class="text-xs font-bold text-slate-800">WFH</span>
                <span class="text-[9px] text-slate-400 font-medium">Rumah</span>
            </a>

            <!-- Kalender Libur -->
            <a href="{{ route('mobile.hari-libur') }}" wire:navigate
                class="bg-white p-3 rounded-2xl border border-slate-100 shadow-xs flex flex-col items-center hover:bg-slate-50 active:scale-95 transition-all text-center">
                <div
                    class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base mb-1.5 shadow-xs">
                    <i class="fa-regular fa-calendar-days"></i>
                </div>
                <span class="text-xs font-bold text-slate-800">Hari Libur</span>
                <span class="text-[9px] text-slate-400 font-medium">Kalender</span>
            </a>

            <!-- Reputasi Rekan Kerja -->
            <a href="{{ route('mobile.reputasi') }}" wire:navigate
                class="bg-white p-3 rounded-2xl border border-slate-100 shadow-xs flex flex-col items-center hover:bg-slate-50 active:scale-95 transition-all text-center">
                <div
                    class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-base mb-1.5 shadow-xs">
                    <i class="fa-solid fa-star"></i>
                </div>
                <span class="text-xs font-bold text-slate-800">Reputasi</span>
                <span class="text-[9px] text-slate-400 font-medium">Rekan Kerja</span>
            </a>

            <!-- LHK Baru -->
            <a href="{{ route('mobile.lhk.create') }}" wire:navigate
                class="bg-white p-3 rounded-2xl border border-slate-100 shadow-xs flex flex-col items-center hover:bg-emerald-50/40 active:scale-95 transition-all text-center">
                <div
                    class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base mb-1.5 shadow-xs">
                    <i class="fa-solid fa-file-circle-plus"></i>
                </div>
                <span class="text-xs font-bold text-slate-800">Buat LHK</span>
                <span class="text-[9px] text-slate-400 font-medium">Kinerja</span>
            </a>

            <!-- Statistik & Rekap Profil -->
            <a href="{{ route('mobile.profil') }}" wire:navigate
                class="bg-white p-3 rounded-2xl border border-slate-100 shadow-xs flex flex-col items-center hover:bg-sky-50/40 active:scale-95 transition-all text-center">
                <div
                    class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-base mb-1.5 shadow-xs">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <span class="text-xs font-bold text-slate-800">Statistik</span>
                <span class="text-[9px] text-slate-400 font-medium">Kehadiran</span>
            </a>
        </div>
    </div>

    <!-- Grafik Sederhana 7 Hari -->
    <h3 class="text-base font-extrabold text-slate-800 tracking-tight mb-3">Kehadiran 7 Hari Terakhir</h3>
    <div class="bg-white p-4 rounded-3xl shadow-xs border border-slate-100 h-40 flex items-end justify-between gap-1">
        @foreach ($riwayat7Hari as $item)
            @php
                $h = 10;
                $color = 'bg-red-400';
                if ($item['status'] == 'hadir' || $item['status'] == 'terlambat') {
                    $color = 'bg-[#2D9B4F]';
                    if ($item['jam_masuk']) {
                        $jam = (int) explode(':', $item['jam_masuk'])[0];
                        $h = max(20, (18 - $jam) * 10);
                    } else {
                        $h = 80;
                    }
                } elseif ($item['status'] == 'izin') {
                    $color = 'bg-[#0EA5E9]';
                    $h = 60;
                } elseif ($item['status'] == 'sakit') {
                    $color = 'bg-[#8B5CF6]';
                    $h = 60;
                }
            @endphp
            <div class="flex flex-col items-center w-full">
                <div class="w-full max-w-[20px] rounded-md {{ $color }}"
                    style="height: {{ $h }}%;"></div>
                <span class="text-[10px] text-gray-500 mt-2 font-medium">{{ $item['hari'] }}</span>
            </div>
        @endforeach
    </div>

@script
<script>
    window.dashboardAttendance = function() {
        return {
            loadingMasuk: false,
            loadingKeluar: false,

            absen(jenis) {
                if (jenis === 'masuk' && {{ $hariIni['sudah_masuk'] ? 'true' : 'false' }}) {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            type: 'info',
                            title: 'Sudah Absen Masuk',
                            message: 'Presensi masuk Anda telah tercatat pada pukul {{ $hariIni['jam_masuk'] ?? '' }} WIB.'
                        }
                    }));
                    return;
                }

                if (jenis === 'masuk' && {{ $hariIni['lewat_batas_masuk'] ? 'true' : 'false' }}) {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            type: 'danger',
                            title: 'Waktu Absen Masuk Telah Berakhir',
                            message: 'Batas akhir absen masuk telah lewat (Pukul {{ $hariIni['tutup_masuk'] }} WIB). Anda tercatat Alpa hari ini.'
                        }
                    }));
                    return;
                }

                if (jenis === 'pulang' && !{{ $hariIni['sudah_masuk'] ? 'true' : 'false' }}) {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            type: 'warning',
                            title: 'Belum Absen Masuk',
                            message: 'Harap lakukan Absen Masuk terlebih dahulu sebelum mengambil Absen Keluar.'
                        }
                    }));
                    return;
                }

                if (jenis === 'pulang' && {{ $hariIni['belum_jam_pulang'] ? 'true' : 'false' }}) {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            type: 'warning',
                            title: 'Belum Jam Pulang',
                            message: 'Tombol absen pulang baru aktif ketika jam pulang sudah masuk (Pukul {{ $hariIni['jam_pulang_resmi'] }} WIB).'
                        }
                    }));
                    return;
                }

                if (jenis === 'pulang' && {{ $hariIni['sudah_pulang'] ? 'true' : 'false' }}) {
                    window.dispatchEvent(new CustomEvent('notify', {
                        detail: {
                            type: 'info',
                            title: 'Sudah Absen Keluar',
                            message: 'Presensi keluar Anda hari ini telah tercatat pada pukul {{ $hariIni['jam_pulang'] ?? '' }} WIB.'
                        }
                    }));
                    return;
                }

                if (jenis === 'masuk') this.loadingMasuk = true;
                if (jenis === 'pulang') this.loadingKeluar = true;

                const resetLoading = () => {
                    this.loadingMasuk = false;
                    this.loadingKeluar = false;
                };

                const doSubmit = (lat, lng) => {
                    $wire.ambilAbsen(jenis, lat, lng)
                        .then(() => {
                            resetLoading();
                        })
                        .catch((err) => {
                            console.error('[DASHBOARD] Absen error:', err);
                            resetLoading();
                        });
                };

                if (!navigator.geolocation) {
                    doSubmit(null, null);
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        doSubmit(pos.coords.latitude, pos.coords.longitude);
                    },
                    (err) => {
                        console.warn('[DASHBOARD] Geolocation error/fallback:', err);
                        doSubmit(null, null);
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
