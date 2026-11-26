<div class="p-4 pb-28 text-slate-800 dark:text-slate-100">
    @php
        $pengaturan = \App\Models\PengaturanAplikasi::getPengaturan();
        $logoUrl = null;
        if (!empty($pengaturan->logo) && file_exists(public_path('storage/' . $pengaturan->logo))) {
            $logoUrl = asset('storage/' . $pengaturan->logo);
        } elseif (file_exists(public_path('images/logo-kabupaten.png'))) {
            $logoUrl = asset('images/logo-kabupaten.png');
        } elseif (file_exists(public_path('images/logo-instansi.png'))) {
            $logoUrl = asset('images/logo-instansi.png');
        }
    @endphp

    <!-- Top Header -->
    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center space-x-2.5">
            <div class="relative shrink-0">
                <div
                    class="w-10 h-10 rounded-xl bg-white dark:bg-white/10 p-1 border border-slate-200 dark:border-white/15 flex items-center justify-center shadow-xs backdrop-blur-sm">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="Logo Instansi" class="w-full h-full object-contain">
                    @else
                        <i class="fa-solid fa-building-columns text-sky-600 dark:text-cyan-400 text-base"></i>
                    @endif
                </div>
                <div
                    class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-emerald-500 dark:bg-emerald-400 border border-white dark:border-[#0B132B] text-white dark:text-slate-950 flex items-center justify-center text-[8px]">
                    <i class="fa-solid fa-fingerprint"></i>
                </div>
            </div>
            <div>
                <div class="flex items-center space-x-1.5">
                    <h1
                        class="text-base font-black text-slate-900 dark:text-white tracking-tight leading-tight uppercase">
                        {{ $pengaturan->nama_aplikasi ?? 'SAKTI' }}
                    </h1>
                    {{-- <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded-md bg-sky-100 text-sky-700 border border-sky-200 dark:bg-cyan-500/15 dark:text-cyan-300 dark:border-cyan-400/30 tracking-wider">
                        E-Presensi
                    </span> --}}
                </div>
                <p class="text-[10px] font-semibold text-sky-600 dark:text-cyan-400 tracking-wider uppercase">
                    Presensi & Kinerja Pegawai
                </p>
            </div>
        </div>
        <div class="flex items-center space-x-1.5">
            <!-- Notifikasi Terintegrasi Filament & Mobile -->
            <div class="relative" x-data="{ openNotif: false }" @keydown.escape.window="openNotif = false" wire:poll.visible.30s>
                <button type="button" @click="openNotif = !openNotif"
                    class="relative w-9 h-9 rounded-xl bg-white dark:bg-white/10 text-slate-600 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-white/20 border border-slate-200 dark:border-white/15 shadow-xs flex items-center justify-center transition cursor-pointer"
                    title="Notifikasi">
                    <i class="fa-regular fa-bell text-sm"></i>
                    @if (($unreadNotifCount ?? 0) > 0)
                        <span
                            class="absolute -top-1 -right-1 min-w-[17px] h-[17px] px-1 rounded-full bg-rose-500 text-white text-[9px] font-black flex items-center justify-center border-2 border-slate-50 dark:border-[#0B132B] shadow-xs">
                            {{ $unreadNotifCount > 9 ? '9+' : $unreadNotifCount }}
                        </span>
                    @endif
                </button>

                <!-- Backdrop untuk menutup notifikasi dengan mudah -->
                <div x-show="openNotif" x-transition.opacity.duration.150ms x-cloak @click="openNotif = false"
                    class="fixed inset-0 z-[65] bg-slate-900/35 backdrop-blur-[1px]"></div>

                <!-- Popover Daftar Notifikasi (Fixed centered di viewport mobile agar tidak terpotong) -->
                <div x-show="openNotif" x-transition.opacity.duration.150ms x-cloak @click.outside="openNotif = false"
                    class="fixed inset-x-4 top-16 mx-auto max-w-[408px] z-[70] rounded-2xl bg-white dark:bg-[#132338] border border-slate-200 dark:border-slate-700/80 shadow-2xl overflow-hidden">
                    <div
                        class="flex items-center justify-between px-3.5 py-2.5 border-b border-slate-100 dark:border-slate-700/60 bg-slate-50/80 dark:bg-[#0F1C2E]">
                        <div class="flex items-center gap-1.5">
                            <i class="fa-solid fa-bell text-sky-600 dark:text-cyan-400 text-xs"></i>
                            <span class="text-xs font-extrabold text-slate-800 dark:text-white">Notifikasi</span>
                            @if (($unreadNotifCount ?? 0) > 0)
                                <span
                                    class="px-1.5 py-0.5 rounded-full bg-rose-500/15 text-rose-600 dark:text-rose-300 text-[9px] font-bold">
                                    {{ $unreadNotifCount }} baru
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if (($unreadNotifCount ?? 0) > 0)
                                <button type="button" wire:click="markAllNotificationsAsRead"
                                    class="text-[10px] font-bold text-sky-600 dark:text-cyan-400 hover:underline cursor-pointer">
                                    Tandai dibaca
                                </button>
                            @endif
                            <button type="button" @click="openNotif = false"
                                class="w-6 h-6 rounded-lg bg-slate-200/70 dark:bg-white/10 text-slate-500 dark:text-slate-300 hover:bg-slate-300/80 flex items-center justify-center transition cursor-pointer"
                                title="Tutup Notifikasi">
                                <i class="fa-solid fa-xmark text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <div class="max-h-72 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/50">
                        @forelse ($recentNotifications ?? [] as $notif)
                            @php
                                $data = $notif->data ?? [];
                                $title = $data['title'] ?? 'Notifikasi Sistem';
                                $body = $data['body'] ?? '';
                                $rawUrl = $data['actions'][0]['url'] ?? null;
                                $titleLower = strtolower($title);
                                $isKeuangan =
                                    str_contains($titleLower, 'spj') ||
                                    str_contains((string) $rawUrl, 'pengajuan-spjs');

                                if ($isKeuangan) {
                                    $targetUrl = $rawUrl;
                                } elseif (
                                    str_contains($titleLower, 'baru') ||
                                    str_contains($titleLower, 'persetujuan') ||
                                    str_contains($titleLower, 'menunggu') ||
                                    str_contains($titleLower, 'tinjau')
                                ) {
                                    $targetUrl = route('mobile.persetujuan');
                                } elseif (
                                    str_contains($titleLower, 'izin') ||
                                    str_contains($titleLower, 'cuti') ||
                                    str_contains($titleLower, 'sakit')
                                ) {
                                    $targetUrl = route('mobile.izin.index');
                                } elseif (str_contains($titleLower, 'lhk')) {
                                    $targetUrl = route('mobile.lhk.index');
                                } elseif (str_contains($titleLower, 'wfh')) {
                                    $targetUrl = route('mobile.wfh.index');
                                } else {
                                    $targetUrl =
                                        $rawUrl && !str_contains((string) $rawUrl, '/admin')
                                            ? $rawUrl
                                            : route('mobile.persetujuan');
                                }
                                $isUnread = is_null($notif->read_at);
                            @endphp
                            <div wire:key="notif-{{ $notif->id }}"
                                class="p-3 transition hover:bg-slate-50 dark:hover:bg-[#192d47] {{ $isUnread ? 'bg-sky-50/50 dark:bg-cyan-500/10' : '' }}">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ $targetUrl }}"
                                            @if (!$isKeuangan) wire:navigate @endif
                                            wire:click="markNotificationAsRead('{{ $notif->id }}')"
                                            @click="openNotif = false" class="block group">
                                            <div class="flex items-center gap-1.5">
                                                @if ($isUnread)
                                                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 shrink-0"></span>
                                                @endif
                                                <p
                                                    class="text-xs font-bold text-slate-800 dark:text-white truncate group-hover:text-sky-600 transition-colors">
                                                    {{ $title }}</p>
                                            </div>
                                            @if ($body)
                                                <p
                                                    class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5 leading-snug">
                                                    {{ $body }}</p>
                                            @endif
                                        </a>
                                        <div class="flex items-center justify-between mt-1.5">
                                            <span
                                                class="text-[9px] text-slate-400">{{ $notif->created_at?->diffForHumans() }}</span>
                                            <div class="flex items-center gap-2">
                                                @if ($isUnread)
                                                    <button type="button"
                                                        wire:click="markNotificationAsRead('{{ $notif->id }}')"
                                                        class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 hover:underline cursor-pointer">
                                                        Tandai dibaca
                                                    </button>
                                                @endif
                                                @if ($targetUrl)
                                                    <a href="{{ $targetUrl }}"
                                                        @if (!$isKeuangan) wire:navigate @endif
                                                        wire:click="markNotificationAsRead('{{ $notif->id }}')"
                                                        @click="openNotif = false"
                                                        class="text-[10px] font-bold text-sky-600 dark:text-cyan-400 hover:underline">
                                                        Buka &rarr;
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 px-4 text-center">
                                <i
                                    class="fa-regular fa-bell-slash text-slate-300 dark:text-slate-600 text-2xl mb-2"></i>
                                <p class="text-xs font-medium text-slate-400">Belum ada notifikasi baru.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Toggle Dark / Light Mode -->
            <button type="button" x-data="{ dark: document.documentElement.classList.contains('dark') }" @mobile-theme-changed.window="dark = $event.detail.dark"
                @click="window.toggleMobileTheme()"
                class="w-9 h-9 rounded-xl bg-white dark:bg-white/10 text-amber-500 dark:text-amber-300 hover:bg-slate-100 dark:hover:bg-white/20 border border-slate-200 dark:border-white/15 shadow-xs flex items-center justify-center transition cursor-pointer"
                :title="dark ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap'">
                <i class="fa-solid text-xs" :class="dark ? 'fa-sun' : 'fa-moon text-slate-600'"></i>
            </button>
            @if (!empty($user->whatsapp_url))
                <a href="{{ $user->whatsapp_url }}" target="_blank" rel="noopener noreferrer"
                    class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-300 hover:bg-emerald-500 hover:text-white dark:hover:text-slate-950 border border-emerald-200 dark:border-emerald-400/30 flex items-center justify-center transition"
                    title="WhatsApp">
                    <i class="fa-brands fa-whatsapp text-sm"></i>
                </a>
            @endif
            @if (auth()->user() &&
                    auth()->user()->hasAnyRole(['super_admin', 'admin']))
                <a href="{{ url('/admin') }}"
                    class="w-9 h-9 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-300 hover:bg-amber-500 hover:text-white dark:hover:text-slate-950 border border-amber-200 dark:border-amber-400/30 flex items-center justify-center transition"
                    title="Panel Admin">
                    <i class="fa-solid fa-shield-halved text-xs"></i>
                </a>
            @endif
            <a href="{{ route('portal') }}"
                class="w-9 h-9 rounded-xl bg-white dark:bg-white/10 text-slate-600 dark:text-slate-200 hover:bg-sky-500 dark:hover:bg-cyan-500 hover:text-white dark:hover:text-slate-950 border border-slate-200 dark:border-white/15 shadow-xs flex items-center justify-center transition"
                title="Beranda Portal">
                <i class="fa-solid fa-house text-xs"></i>
            </a>
            <button wire:click="logout"
                class="w-9 h-9 rounded-xl bg-white dark:bg-white/10 hover:bg-rose-50 dark:hover:bg-rose-500/20 text-slate-500 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-300 border border-slate-200 dark:border-white/15 shadow-xs transition flex items-center justify-center"
                title="Keluar">
                <i class="fa-solid fa-right-from-bracket text-xs"></i>
            </button>
        </div>
    </div>

    <!-- Identitas Pegawai -->
    <div class="flex items-center space-x-3.5 mb-4 px-1">
        <div class="relative shrink-0 w-14 h-14">
            <label class="relative block w-14 h-14 cursor-pointer group active:scale-95 transition-transform"
                title="Klik untuk ganti foto profil">
                <input type="file" wire:model="new_avatar" accept="image/jpeg,image/png,image/webp" class="hidden">
                <img src="{{ $user->avatar_image_url }}" alt="{{ $user->name }}"
                    class="w-14 h-14 rounded-2xl object-cover border-2 border-sky-400/50 dark:border-cyan-400/40 shadow-md aspect-square">
                <div
                    class="absolute inset-0 bg-slate-900/60 rounded-2xl flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity backdrop-blur-[1px]">
                    <i class="fa-solid fa-camera text-white text-xs"></i>
                </div>
                <div wire:loading wire:target="new_avatar"
                    class="absolute inset-0 bg-slate-950/75 rounded-2xl flex items-center justify-center">
                    <i class="fa-solid fa-circle-notch fa-spin text-white text-sm"></i>
                </div>
                <span class="absolute -bottom-1 -right-1 flex h-3.5 w-3.5" title="Aktif">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span
                        class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-400 border-2 border-slate-50 dark:border-[#0B132B]"></span>
                </span>
            </label>
        </div>
        <div class="flex-1 min-w-0">
            <h2 class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight truncate"
                title="{{ $user->name }}">
                {{ $user->name }}
            </h2>
            <p class="text-[11px] font-mono text-slate-500 dark:text-slate-400 mt-0.5 truncate">
                NIP. {{ $user->nip ?? '-' }}
            </p>
            <p class="text-[11px] font-semibold text-sky-600 dark:text-cyan-400 mt-0.5 truncate">
                {{ $user->jabatan->nama ?? 'Pegawai' }}
            </p>
        </div>
    </div>

    <!-- Kartu Jam Server & Status Lokasi -->
    <div
        class="w-full p-4 rounded-2xl bg-white dark:bg-[#132338]/90 backdrop-blur-md border border-slate-200/80 dark:border-slate-700/60 shadow-sm dark:shadow-lg mb-3 text-center relative overflow-hidden">
        <p class="text-xs font-semibold text-slate-500 dark:text-slate-300">
            {{ now()->translatedFormat('l, d F Y') }}
        </p>

        <!-- Jam Aktif Server Real-Time (HH:MM WIB) -->
        <div class="my-1.5 flex items-center justify-center font-mono text-3xl font-black tracking-wider text-emerald-600 dark:text-emerald-400"
            x-data="{
                hours: '--',
                minutes: '--',
                colon: true,
                updateClock() {
                    const d = new Date();
                    this.hours = String(d.getHours()).padStart(2, '0');
                    this.minutes = String(d.getMinutes()).padStart(2, '0');
                    this.colon = !this.colon;
                }
            }" x-init="updateClock();
            setInterval(() => updateClock(), 1000);">
            <span x-text="hours">--</span><span class="inline-block transition-opacity duration-200 mx-0.5"
                :class="colon ? 'opacity-100' : 'opacity-25'">:</span><span x-text="minutes">--</span>
            <span class="text-lg font-extrabold ml-1.5 text-emerald-500 dark:text-emerald-300">WIB</span>
        </div>

        @if (!$hariLiburHariIni)
            <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 flex items-center justify-center">
                <i class="fa-solid fa-location-dot text-emerald-500 dark:text-emerald-400 mr-1.5"></i>
                Mode Kerja:
                <span class="text-emerald-600 dark:text-emerald-400 font-bold ml-1">
                    {{ ($user->status_kerja ?? 'wfo') === 'wfh' ? 'WFH (Fleksibel)' : 'WFO (Radius Kantor)' }}
                </span>
            </p>
        @else
            <style>
                @keyframes liburZoomInOut {

                    0%,
                    100% {
                        transform: scale(1);
                    }

                    50% {
                        transform: scale(1.06);
                    }
                }

                .animate-libur-zoom {
                    animation: liburZoomInOut 2.2s ease-in-out infinite;
                }
            </style>
            <div
                class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-amber-50 border border-amber-300 text-amber-800 shadow-xs animate-libur-zoom">
                <i class="fa-solid fa-calendar-xmark text-amber-600 text-lg"></i>
                <span class="text-sm sm:text-base font-black uppercase tracking-wide">
                    HARI LIBUR: {{ $hariLiburHariIni->nama }}
                </span>
            </div>
        @endif
    </div>

    <!-- 2 Tombol Aksi Utama: Absen Masuk & Absen Pulang -->
    @if (!$hariLiburHariIni)
        <div class="grid grid-cols-2 gap-2.5 mb-5" x-data="dashboardAttendance()"
            data-sudah-masuk="{{ $hariIni['sudah_masuk'] ? '1' : '0' }}">
            <!-- Tombol Masuk -->
            <button type="button" @click="absen('masuk')" :disabled="loadingMasuk || loadingKeluar"
                class="flex items-center justify-center gap-2 py-3 px-3 rounded-xl font-extrabold text-xs uppercase tracking-wide transition active:scale-95 cursor-pointer border {{ $hariIni['sudah_masuk'] ? 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-500/20 dark:border-emerald-400/40 dark:text-emerald-300' : ($hariIni['lewat_batas_masuk'] ? 'bg-rose-50 border-rose-200 text-rose-700 dark:bg-rose-500/15 dark:border-rose-400/30 dark:text-rose-300' : 'bg-emerald-500 hover:bg-emerald-400 border-emerald-400 text-white dark:text-slate-950 shadow-md shadow-emerald-950/20') }}">
                <template x-if="loadingMasuk">
                    <i class="fa-solid fa-circle-notch fa-spin text-sm"></i>
                </template>
                <template x-if="!loadingMasuk">
                    <i
                        class="fa-solid {{ $hariIni['sudah_masuk'] ? 'fa-right-to-bracket' : ($hariIni['lewat_batas_masuk'] ? 'fa-lock' : 'fa-right-to-bracket') }} text-sm"></i>
                </template>
                <div class="text-left leading-tight">
                    <div>
                        {{ $hariIni['sudah_masuk'] ? 'Masuk ' . substr($hariIni['jam_masuk'], 0, 5) : ($hariIni['lewat_batas_masuk'] ? 'Lewat Batas' : 'Absen Masuk') }}
                    </div>
                    @if ($hariIni['lewat_batas_masuk'] && !$hariIni['sudah_masuk'])
                        <div class="text-[9px] font-semibold opacity-80 normal-case">Alpa
                            ({{ $hariIni['tutup_masuk'] }})</div>
                    @endif
                </div>
            </button>

            <!-- Tombol Pulang -->
            <button type="button" @click="absen('pulang')" :disabled="loadingMasuk || loadingKeluar"
                class="flex items-center justify-center gap-2 py-3 px-3 rounded-xl font-extrabold text-xs uppercase tracking-wide transition active:scale-95 cursor-pointer border {{ $hariIni['sudah_pulang'] ? 'bg-sky-50 border-sky-200 text-sky-700 dark:bg-cyan-500/20 dark:border-cyan-400/40 dark:text-cyan-300' : ($hariIni['sudah_masuk'] && !$hariIni['belum_jam_pulang'] ? 'bg-amber-400 hover:bg-amber-300 border-amber-300 text-slate-950 shadow-md' : 'bg-slate-100 border-slate-200 text-slate-500 dark:bg-[#1a1625] dark:border-rose-500/25 dark:text-rose-300/80') }}">
                <template x-if="loadingKeluar">
                    <i class="fa-solid fa-circle-notch fa-spin text-sm"></i>
                </template>
                <template x-if="!loadingKeluar">
                    <i
                        class="fa-solid {{ $hariIni['sudah_pulang'] ? 'fa-right-from-bracket' : ($hariIni['sudah_masuk'] && !$hariIni['belum_jam_pulang'] ? 'fa-right-from-bracket' : 'fa-lock') }} text-sm"></i>
                </template>
                <div class="text-left leading-tight">
                    <div>
                        {{ $hariIni['sudah_pulang'] ? 'Pulang ' . substr($hariIni['jam_pulang'], 0, 5) : ($hariIni['sudah_masuk'] ? ($hariIni['belum_jam_pulang'] ? 'Buka ' . $hariIni['jam_pulang_resmi'] : 'Absen Pulang') : 'Absen Pulang') }}
                    </div>
                    @if (!$hariIni['sudah_masuk'])
                        <div class="text-[9px] font-semibold opacity-70 normal-case">Menunggu Masuk</div>
                    @endif
                </div>
            </button>
        </div>
    @endif

    <!-- Rekap Jam Kerja ASN (7j 30m / hari • 37j 30m / minggu) -->
    @if (!$hariLiburHariIni)
        <div
            class="w-full p-3.5 rounded-2xl bg-white dark:bg-[#132338]/90 backdrop-blur-md border border-slate-200/80 dark:border-slate-700/60 shadow-2xs mb-5">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <div
                        class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 dark:bg-cyan-500/15 dark:text-cyan-400 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-business-time"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-extrabold text-slate-800 dark:text-white leading-tight">Jam Kerja
                            Efektif ASN</h4>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 leading-tight">Standar 7j 30m/hari
                            &bull; 37j 30m/minggu (non-istirahat)</p>
                    </div>
                </div>
                <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:border-emerald-500/30">
                    {{ $jamKerjaAsn['persen_bulanan'] }}%
                </span>
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs">
                <div
                    class="p-2.5 rounded-xl bg-slate-50 dark:bg-[#0F1C2E] border border-slate-100 dark:border-slate-700/60">
                    <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 block">Tercatat Hari
                        Ini</span>
                    <div class="font-mono font-extrabold text-sm text-slate-800 dark:text-white mt-0.5">
                        @if ($hariIni['sudah_masuk'] && !$hariIni['sudah_pulang'])
                            <span class="text-amber-600 dark:text-amber-300 text-xs font-sans">Bertugas</span>
                        @else
                            {{ $jamKerjaAsn['harian_format'] }}
                        @endif
                        <span class="text-[10px] font-normal text-slate-400">/
                            {{ $jamKerjaAsn['target_harian_format'] }}</span>
                    </div>
                    <div class="mt-2 w-full h-1.5 rounded-full bg-slate-200/80 dark:bg-slate-800 overflow-hidden">
                        <div class="h-full rounded-full bg-emerald-500 transition-all duration-500"
                            style="width: {{ min(100, (int) ($jamKerjaAsn['persen_harian'] ?? 0)) }}%"></div>
                    </div>
                </div>
                <div
                    class="p-2.5 rounded-xl bg-slate-50 dark:bg-[#0F1C2E] border border-slate-100 dark:border-slate-700/60">
                    <span class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 block">Akumulasi Bulan
                        Ini</span>
                    <div class="font-mono font-extrabold text-sm text-sky-700 dark:text-cyan-300 mt-0.5">
                        {{ $jamKerjaAsn['bulanan_format'] }}
                        <span class="text-[10px] font-normal text-slate-400">/
                            {{ $jamKerjaAsn['target_bulanan_format'] }}</span>
                    </div>
                    <div class="mt-2 w-full h-1.5 rounded-full bg-slate-200/80 dark:bg-slate-800 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-emerald-500 transition-all duration-500"
                            style="width: {{ min(100, (int) ($jamKerjaAsn['persen_bulanan'] ?? 0)) }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Menu Layanan Kepegawaian -->
    <div class="mb-5">
        <div class="flex items-center justify-between mb-2.5 px-0.5">
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white tracking-tight">Layanan Kepegawaian</h3>
            @if (\App\Models\PengaturanAplikasi::isModuleEnabled('izin'))
            <a href="{{ route('mobile.izin.index') }}" wire:navigate
                class="text-xs font-bold text-sky-600 dark:text-cyan-400 hover:opacity-80 flex items-center">
                <span>Riwayat Izin</span>
                <i class="fa-solid fa-chevron-right text-[10px] ml-1"></i>
            </a>
            @endif
        </div>
        <div class="grid grid-cols-4 gap-2">
            @if (\App\Models\PengaturanAplikasi::isModuleEnabled('izin'))
            <!-- Izin -->
            <a href="{{ route('mobile.izin.create', ['kategori' => 'izin']) }}" wire:navigate
                class="bg-white dark:bg-[#132338]/90 py-3 px-1.5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-2xs flex flex-col items-center hover:bg-slate-50 dark:hover:bg-[#192d47] active:scale-95 transition-all text-center">
                <div
                    class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 dark:bg-cyan-500/15 dark:text-cyan-400 flex items-center justify-center text-sm mb-1.5">
                    <i class="fa-solid fa-envelope-open-text"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200 leading-tight">Izin</span>
            </a>

            <!-- Sakit -->
            <a href="{{ route('mobile.izin.create', ['kategori' => 'sakit']) }}" wire:navigate
                class="bg-white dark:bg-[#132338]/90 py-3 px-1.5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-2xs flex flex-col items-center hover:bg-slate-50 dark:hover:bg-[#192d47] active:scale-95 transition-all text-center">
                <div
                    class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400 flex items-center justify-center text-sm mb-1.5">
                    <i class="fa-solid fa-notes-medical"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200 leading-tight">Sakit</span>
            </a>

            <!-- Cuti -->
            <a href="{{ route('mobile.izin.create', ['kategori' => 'cuti']) }}" wire:navigate
                class="bg-white dark:bg-[#132338]/90 py-3 px-1.5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-2xs flex flex-col items-center hover:bg-slate-50 dark:hover:bg-[#192d47] active:scale-95 transition-all text-center">
                <div
                    class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400 flex items-center justify-center text-sm mb-1.5">
                    <i class="fa-solid fa-umbrella-beach"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200 leading-tight">Cuti</span>
            </a>
            @endif

            <!-- WFH -->
            @if (\App\Models\PengaturanAplikasi::isModuleEnabled('wfh'))
            <a href="{{ route('mobile.wfh.index') }}" wire:navigate
                class="bg-white dark:bg-[#132338]/90 py-3 px-1.5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-2xs flex flex-col items-center hover:bg-slate-50 dark:hover:bg-[#192d47] active:scale-95 transition-all text-center">
                <div
                    class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 dark:bg-teal-500/15 dark:text-teal-400 flex items-center justify-center text-sm mb-1.5">
                    <i class="fa-solid fa-house-laptop"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200 leading-tight">WFH</span>
            </a>
            @endif

            <!-- Kalender Libur -->
            <a href="{{ route('mobile.hari-libur') }}" wire:navigate
                class="bg-white dark:bg-[#132338]/90 py-3 px-1.5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-2xs flex flex-col items-center hover:bg-slate-50 dark:hover:bg-[#192d47] active:scale-95 transition-all text-center">
                <div
                    class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-400 flex items-center justify-center text-sm mb-1.5">
                    <i class="fa-regular fa-calendar-days"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200 leading-tight">Hari Libur</span>
            </a>

            <!-- Reputasi Rekan Kerja (masuk modul LHK) -->
            @if (\App\Models\PengaturanAplikasi::isModuleEnabled('lhk'))
            <a href="{{ route('mobile.reputasi') }}" wire:navigate
                class="bg-white dark:bg-[#132338]/90 py-3 px-1.5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-2xs flex flex-col items-center hover:bg-slate-50 dark:hover:bg-[#192d47] active:scale-95 transition-all text-center">
                <div
                    class="w-9 h-9 rounded-xl bg-amber-50 text-amber-500 dark:bg-amber-500/15 dark:text-amber-400 flex items-center justify-center text-sm mb-1.5">
                    <i class="fa-solid fa-star"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200 leading-tight">Reputasi</span>
            </a>

            <!-- LHK Baru -->
            <a href="{{ route('mobile.lhk.create') }}" wire:navigate
                class="bg-white dark:bg-[#132338]/90 py-3 px-1.5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-2xs flex flex-col items-center hover:bg-slate-50 dark:hover:bg-[#192d47] active:scale-95 transition-all text-center">
                <div
                    class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400 flex items-center justify-center text-sm mb-1.5">
                    <i class="fa-solid fa-file-circle-plus"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200 leading-tight">Buat LHK</span>
            </a>
            @endif

            <!-- Statistik & Rekap Profil -->
            <a href="{{ route('mobile.profil') }}" wire:navigate
                class="bg-white dark:bg-[#132338]/90 py-3 px-1.5 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-2xs flex flex-col items-center hover:bg-slate-50 dark:hover:bg-[#192d47] active:scale-95 transition-all text-center">
                <div
                    class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 dark:bg-cyan-500/15 dark:text-cyan-400 flex items-center justify-center text-sm mb-1.5">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <span class="text-[11px] font-bold text-slate-700 dark:text-slate-200 leading-tight">Statistik</span>
            </a>
        </div>
    </div>

    <!-- Grafik Sederhana 10 Hari Kerja -->
    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white tracking-tight mb-2.5 px-0.5">Kehadiran 10 Hari
        Kerja Terakhir</h3>
    <div
        class="bg-white dark:bg-[#132338]/90 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-700/60 shadow-2xs h-36 flex items-end justify-between gap-1.5">
        @foreach ($riwayat7Hari as $item)
            @php
                $h = 10;
                $color = 'bg-rose-500';
                if ($item['status'] == 'hadir' || $item['status'] == 'terlambat') {
                    $color = 'bg-emerald-400';
                    if ($item['jam_masuk']) {
                        $jam = (int) explode(':', $item['jam_masuk'])[0];
                        $h = max(20, (18 - $jam) * 10);
                    } else {
                        $h = 80;
                    }
                } elseif ($item['status'] == 'izin') {
                    $color = 'bg-cyan-400';
                    $h = 60;
                } elseif ($item['status'] == 'sakit') {
                    $color = 'bg-violet-400';
                    $h = 60;
                }
            @endphp
            <div class="flex flex-col items-center w-full">
                <div class="w-full max-w-[20px] rounded-md {{ $color }}"
                    style="height: {{ $h }}%;"></div>
                <span class="text-[10px] text-slate-400 mt-2 font-medium">{{ $item['hari'] }}</span>
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

                        if (jenis === 'pulang' && this.$root.dataset.sudahMasuk !== '1') {
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
                            }, {
                                enableHighAccuracy: true,
                                timeout: 15000,
                                maximumAge: 0
                            }
                        );
                    }
                };
            };
        </script>
    @endscript
</div>
