@php
    $aksiUtama = !$hariIni['sudah_masuk'] ? 'masuk' : (!$hariIni['sudah_pulang'] ? 'pulang' : 'selesai');
@endphp

<div wire:poll.10s x-data="{
    avatarPreview: false,
    notifSheet: false,
    suratTugasSheet: false,
    konfirmasiSuccessModal: false,
    logoutConfirmModal: false,
    remindConfirmModal: false,
    jam: '--:--',
    detik: '--',
    punching: false,
    init() {
        const tick = () => {
            const d = new Date();
            this.jam = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
            this.detik = String(d.getSeconds()).padStart(2, '0');
        };
        tick();
        setInterval(tick, 1000);
    },
    doPunch(jenis) {
        if (this.punching || jenis === 'selesai') return;
        this.punching = true;
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (pos) => $wire.ambilAbsen(jenis, pos.coords.latitude, pos.coords.longitude).finally(() => this.punching = false),
                () => $wire.ambilAbsen(jenis, null, null).finally(() => this.punching = false), { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        } else {
            $wire.ambilAbsen(jenis, null, null).finally(() => this.punching = false);
        }
    }
}" @open-notif-sheet.window="notifSheet = true"
    @konfirmasi-hadir-sukses.window="notifSheet = false; konfirmasiSuccessModal = true"
    class="pb-28 px-4 pt-4 space-y-4 text-zinc-900 dark:text-zinc-100">

    <!-- Holiday Notice (Hanya tampil saat hari libur) -->
    @if ($hariLiburHariIni)
        <div
            class="rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 px-3.5 py-2.5 flex items-center gap-2.5 text-xs text-amber-800 dark:text-amber-300">
            <i class="fa-solid fa-calendar-day text-amber-500"></i>
            <span class="font-bold truncate">Hari Libur: {{ $hariLiburHariIni->nama }}</span>
        </div>
    @endif

    <!-- 1. HERO HIJAU EMERALD + KARTU AKSI MENGAMBANG (Referensi) -->
    @php
        $hour = (int) now()->format('H');
        $sapaan = match (true) {
            $hour >= 4 && $hour < 11 => 'Selamat pagi',
            $hour >= 11 && $hour < 15 => 'Selamat siang',
            $hour >= 15 && $hour < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };
    @endphp
    <section class="-mx-4 -mt-4">
        <div
            class="relative overflow-hidden rounded-b-[28px] bg-gradient-to-br from-[#0F172A] via-[#0369A1] to-[#0284C7] px-5 pt-5 pb-12 text-white shadow-lg shadow-sky-950/20">
            <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-white/10 blur-2xl pointer-events-none">
            </div>

            <!-- Row 1: Avatar + Sapaan + Bell -->
            <div class="flex items-center justify-between gap-3 relative z-10">
                <div class="flex items-center gap-3 min-w-0">
                    <button type="button" @click="avatarPreview = true" class="relative shrink-0 cursor-pointer group"
                        title="Lihat atau ganti foto profil">
                        <img src="{{ $user->avatar_image_url }}" alt="{{ $user->name }}"
                            class="w-10 h-10 rounded-full object-cover ring-2 ring-white/50 group-active:scale-95 transition">
                        <span class="absolute -bottom-0.5 -right-0.5 w-4 h-4 rounded-full bg-sky-500 border border-white text-white flex items-center justify-center shadow-xs">
                            <i class="fa-solid fa-camera text-[8px]"></i>
                        </span>
                    </button>
                    <div class="min-w-0">
                        <div class="text-[13px] text-sky-100 font-medium">{{ $sapaan }},</div>
                        <h1 class="text-sm font-extrabold tracking-tight leading-snug">{{ $user->name }}</h1>
                        @php
                            $jabatanNama = $user->jabatan?->nama;
                            $bidangNama = $user->bidang?->nama;
                            $jabatanBidang =
                                strcasecmp((string) $jabatanNama, 'Sekretaris') === 0 &&
                                strcasecmp((string) $bidangNama, 'Sekretariat') === 0
                                    ? 'Sekretaris'
                                    : collect([$jabatanNama, $bidangNama])
                                        ->filter()
                                        ->implode(' | ');
                        @endphp
                        @if ($jabatanBidang)
                            <div class="text-xs text-sky-100/90 font-medium leading-tight mt-0.5">{{ $jabatanBidang }}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-1.5 shrink-0">
                    <div x-data="{
                        isNative: !!window.SaktiNative,
                        perms: window.__saktiPerms || { location: true, notification: true, battery: true }
                    }" @sakti-device-permissions.window="perms = $event.detail || perms"
                        class="flex items-center gap-1.5 shrink-0">
                        <button type="button"
                            @click="(isNative && !perms.notification) ? window.SaktiNative?.postMessage('open_app_settings') : (notifSheet = true)"
                            class="relative w-9 h-9 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center transition cursor-pointer shrink-0"
                            :title="(isNative && !perms.notification) ?
                            'Izin Notifikasi belum aktif — ketuk untuk mengaktifkan' : 'Notifikasi'">
                            <i class="fa-solid text-sm"
                                :class="(isNative && !perms.notification) ? 'fa-bell-slash text-rose-300' :
                                'fa-bell {{ $unreadNotifCount > 0 ? 'text-amber-300 notif-bell-ring' : '' }}'"></i>
                            @if ($unreadNotifCount > 0)
                                <span x-show="!isNative || perms.notification"
                                    class="absolute top-0.5 right-0.5 flex h-2.5 w-2.5 notif-dot-move">
                                    <span
                                        class="notif-dot-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                    <span
                                        class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500 ring-2 ring-sky-800"></span>
                                </span>
                            @endif
                            <span x-show="isNative && !perms.notification" x-cloak
                                class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-rose-500 ring-2 ring-sky-800"></span>
                        </button>
                        <button type="button"
                            @click="logoutConfirmModal = true"
                            class="w-9 h-9 rounded-full bg-white/15 hover:bg-rose-500/80 text-white flex items-center justify-center transition cursor-pointer shrink-0"
                            title="Keluar"
                            aria-label="Keluar">
                            <i class="fa-solid fa-right-from-bracket text-sm"></i>
                        </button>
                        <template x-teleport="body">
                            <div x-show="logoutConfirmModal" x-cloak
                                @keydown.escape.window="logoutConfirmModal = false"
                                x-transition.opacity.duration.150ms
                                class="fixed inset-0 z-[120] bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-5"
                                @click.self="logoutConfirmModal = false">
                                <div x-show="logoutConfirmModal"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    class="w-full max-w-xs rounded-[28px] bg-white dark:bg-zinc-900 p-6 shadow-2xl border border-slate-200/70 dark:border-zinc-800 space-y-4">
                                    <div class="w-12 h-12 mx-auto rounded-2xl bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl">
                                        <i class="fa-solid fa-right-from-bracket"></i>
                                    </div>
                                    <div class="text-center space-y-1.5">
                                        <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Keluar dari Aplikasi?</h3>
                                        <p class="text-xs text-slate-500 dark:text-zinc-400 leading-relaxed">
                                            Sesi Anda akan diakhiri dan Anda perlu masuk kembali untuk mengakses SAKTI.
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-end gap-2 pt-2">
                                        <button type="button" @click="logoutConfirmModal = false"
                                            class="px-4 py-2.5 rounded-full text-xs font-bold text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition cursor-pointer">
                                            Batal
                                        </button>
                                        <form method="POST" action="{{ route('portal.logout') }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                class="px-5 py-2.5 rounded-full bg-rose-600 hover:bg-rose-700 text-white text-xs font-extrabold shadow-md shadow-rose-600/25 active:scale-95 transition cursor-pointer">
                                                Ya, Keluar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    @if ($unreadNotifCount > 0)
                        <style>
                            @keyframes notifBellRing {

                                0%,
                                100% {
                                    transform: rotate(0deg);
                                }

                                15% {
                                    transform: rotate(16deg);
                                }

                                30% {
                                    transform: rotate(-14deg);
                                }

                                45% {
                                    transform: rotate(10deg);
                                }

                                60% {
                                    transform: rotate(-8deg);
                                }

                                75% {
                                    transform: rotate(4deg);
                                }
                            }

                            @keyframes notifDotMove {

                                0%,
                                100% {
                                    transform: translateY(0) scale(1);
                                }

                                50% {
                                    transform: translateY(-2px) scale(1.18);
                                }
                            }

                            @keyframes notifDotPing {
                                0% {
                                    transform: scale(1);
                                    opacity: 0.8;
                                }

                                75%,
                                100% {
                                    transform: scale(2.2);
                                    opacity: 0;
                                }
                            }

                            .notif-bell-ring {
                                transform-origin: top center;
                                animation: notifBellRing 1.6s ease-in-out infinite;
                            }

                            .notif-dot-move {
                                animation: notifDotMove 1.1s ease-in-out infinite;
                            }

                            .notif-dot-ping {
                                animation: notifDotPing 1.4s cubic-bezier(0, 0, 0.2, 1) infinite;
                            }
                        </style>
                    @endif
                </div>
            </div>

            <!-- Row 1.5: Baris Ikon Aksi Cepat di bawah tombol Notifikasi & Keluar -->
            <div class="mt-2 flex items-center justify-end gap-2 relative z-10">
                <a href="/admin" target="_blank" rel="noopener"
                    class="w-8 h-8 rounded-full bg-amber-400 hover:bg-amber-300 text-slate-950 inline-flex items-center justify-center shadow-sm transition cursor-pointer shrink-0"
                    title="Panel Admin SAKTI (Proses SPJ)"
                    aria-label="Panel Admin SAKTI (Proses SPJ)">
                    <i class="fa-solid fa-shield-halved text-xs"></i>
                </a>
                @if ($user->canRemindPegawai())
                    <button type="button" @click="remindConfirmModal = true"
                        class="w-8 h-8 rounded-full bg-rose-500 hover:bg-rose-400 text-white inline-flex items-center justify-center shadow-sm transition cursor-pointer shrink-0"
                        title="Ingatkan Pegawai Lengkapi Data Kepegawaian"
                        aria-label="Ingatkan Pegawai Lengkapi Data Kepegawaian">
                        <i class="fa-solid fa-bullhorn text-xs"></i>
                    </button>
                    <template x-teleport="body">
                        <div x-show="remindConfirmModal" x-cloak
                            @keydown.escape.window="remindConfirmModal = false"
                            x-transition.opacity.duration.150ms
                            class="fixed inset-0 z-[120] bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-5"
                            @click.self="remindConfirmModal = false">
                            <div x-show="remindConfirmModal"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                class="w-full max-w-xs rounded-[28px] bg-white dark:bg-zinc-900 p-6 shadow-2xl border border-slate-200/70 dark:border-zinc-800 space-y-4">
                                <div class="w-12 h-12 mx-auto rounded-2xl bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center text-xl">
                                    <i class="fa-solid fa-bullhorn"></i>
                                </div>
                                <div class="text-center space-y-1.5">
                                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Ingatkan Pegawai?</h3>
                                    <p class="text-xs text-slate-500 dark:text-zinc-400 leading-relaxed">
                                        Kirim notifikasi pengingat ke semua pegawai aktif yang data kepegawaiannya belum lengkap?
                                    </p>
                                </div>
                                <div class="flex items-center justify-end gap-2 pt-2">
                                    <button type="button" @click="remindConfirmModal = false"
                                        class="px-4 py-2.5 rounded-full text-xs font-bold text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 transition cursor-pointer">
                                        Batal
                                    </button>
                                    <button type="button" @click="remindConfirmModal = false; $wire.ingatkanPegawai()"
                                        class="px-5 py-2.5 rounded-full bg-rose-600 hover:bg-rose-700 text-white text-xs font-extrabold shadow-md shadow-rose-600/25 active:scale-95 transition cursor-pointer">
                                        Ya, Kirim
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                @endif
                @if ($user->hasAnyRole(['super_admin', 'superadmin']))
                    <button type="button" wire:click="toggleSimulasi"
                        class="w-8 h-8 rounded-full {{ $modeSimulasi ? 'bg-emerald-400 text-slate-950' : 'bg-white/15 hover:bg-white/25 text-white' }} inline-flex items-center justify-center shadow-sm transition cursor-pointer shrink-0"
                        title="{{ $modeSimulasi ? 'Mode Simulasi ON (klik untuk nonaktifkan)' : 'Aktifkan Mode Simulasi (Khusus Superadmin)' }}"
                        aria-label="Toggle Mode Simulasi">
                        <i class="fa-solid fa-flask text-xs"></i>
                    </button>
                @endif
            </div>

            <!-- Row 2: Jam Live & Status Masuk/Pulang -->
            <div class="mt-5 flex items-center justify-between gap-3 relative z-10">
                <div class="min-w-0 md:absolute md:left-1/2 md:-translate-x-1/2 md:text-center">
                    <div class="text-[13px] text-sky-100 font-medium">{{ now()->translatedFormat('l, d M Y') }}</div>
                    <div class="flex items-baseline gap-1 font-mono mt-0.5 md:justify-center">
                        <span x-text="jam" class="text-3xl font-black tracking-tight">--:--</span>
                        <span x-text="':' + detik" class="text-sm font-bold text-sky-200"></span>
                        <span class="text-xs font-bold text-sky-200 ml-0.5">WIB</span>
                    </div>
                </div>
                <div class="flex items-center gap-3 pl-3.5 border-l border-white/25 text-right shrink-0">
                    <div>
                        <div class="text-[13px] font-semibold text-sky-100">Masuk</div>
                        <div class="text-base sm:text-lg font-black font-mono leading-tight">
                            {{ $hariIni['jam_masuk'] ? \Carbon\Carbon::parse($hariIni['jam_masuk'])->format('H:i') : '--:--' }}
                        </div>
                    </div>
                    <div class="w-px h-8 bg-white/20"></div>
                    <div>
                        <div class="text-[13px] font-semibold text-sky-100">Pulang</div>
                        <div class="text-base sm:text-lg font-black font-mono leading-tight">
                            {{ $hariIni['jam_pulang'] ? \Carbon\Carbon::parse($hariIni['jam_pulang'])->format('H:i') : '--:--' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overlapping White Quick Action Card (Semua Layanan) -->
        @php
            $layanan = [
                [
                    'route' => 'mobile.absensi',
                    'icon' => 'fa-fingerprint',
                    'label' => 'Presensi',
                    'color' => 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400',
                ],
                [
                    'route' => 'mobile.lhk.create',
                    'module' => 'lhk',
                    'icon' => 'fa-pen-to-square',
                    'label' => 'Isi LHK',
                    'color' => 'bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400',
                ],
                [
                    'route' => 'mobile.izin.index',
                    'module' => 'izin',
                    'icon' => 'fa-envelope-open-text',
                    'label' => 'Izin & Cuti',
                    'color' => 'bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400',
                ],
                [
                    'route' => 'mobile.riwayat',
                    'icon' => 'fa-clock-rotate-left',
                    'label' => 'Riwayat',
                    'color' => 'bg-violet-50 dark:bg-violet-500/15 text-violet-600 dark:text-violet-400',
                ],
                [
                    'route' => 'mobile.hari-libur',
                    'icon' => 'fa-calendar-day',
                    'label' => 'Hari Libur',
                    'color' => 'bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400',
                ],
                [
                    'route' => 'mobile.lhk.bank-aktivitas',
                    'module' => 'lhk',
                    'icon' => 'fa-box-archive',
                    'label' => 'Bank Aktivitas',
                    'color' => 'bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400',
                ],
                [
                    'route' => 'mobile.reputasi',
                    'module' => 'reputasi',
                    'icon' => 'fa-medal',
                    'label' => 'Reputasi',
                    'color' => 'bg-yellow-50 dark:bg-yellow-500/15 text-yellow-600 dark:text-yellow-400',
                ],
                [
                    'route' => 'mobile.agenda',
                    'module' => 'agenda',
                    'icon' => 'fa-calendar-check',
                    'label' => 'Agenda',
                    'color' => 'bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400',
                ],
                [
                    'route' => 'mobile.persetujuan',
                    'roles' => [
                        'super_admin',
                        'admin',
                        'admin_kepegawaian',
                        'admin_keuangan',
                        'kabid',
                        'pptk',
                        'ppk',
                        'pa',
                    ],
                    'icon' => 'fa-file-signature',
                    'label' => 'Persetujuan',
                    'color' => 'bg-teal-50 dark:bg-teal-500/15 text-teal-600 dark:text-teal-400',
                ],
            ];
        @endphp
        <div
            class="relative z-10 -mt-8 mx-4 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 p-3.5 shadow-xl shadow-slate-900/10 ring-1 ring-slate-900/5">
            <div class="grid grid-cols-4 gap-x-2 gap-y-3">
                @foreach ($layanan as $item)
                    @if (Route::has($item['route']) &&
                            (empty($item['module']) || \App\Models\PengaturanAplikasi::isModuleEnabled($item['module'])) &&
                            (empty($item['roles']) || $user->hasAnyRole($item['roles'])))
                        <a href="{{ route($item['route']) }}" wire:navigate
                            class="flex flex-col items-center gap-1.5 active:scale-95 transition-transform">
                            <div
                                class="w-12 h-12 md:w-16 md:h-16 rounded-2xl {{ $item['color'] }} border border-slate-200/80 dark:border-zinc-700/70 flex items-center justify-center text-xl md:text-2xl">
                                <i class="fa-solid {{ $item['icon'] }}"></i>
                            </div>
                            <span
                                class="text-[13px] md:text-sm font-bold text-zinc-700 dark:text-zinc-300 text-center leading-tight">{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
                @if (Route::has('mobile.surat-tugas.index'))
                    <a href="{{ route('mobile.surat-tugas.index') }}" wire:navigate
                        class="flex flex-col items-center gap-1.5 active:scale-95 transition-transform">
                        <div
                            class="w-12 h-12 md:w-16 md:h-16 rounded-2xl bg-teal-50 dark:bg-teal-500/15 text-teal-600 dark:text-teal-400 border border-slate-200/80 dark:border-zinc-700/70 flex items-center justify-center text-xl md:text-2xl">
                            <i class="fa-solid fa-file-signature"></i>
                        </div>
                        <span
                            class="text-[13px] md:text-sm font-bold text-zinc-700 dark:text-zinc-300 text-center leading-tight">Surat
                            Tugas</span>
                    </a>
                @endif
            </div>
        </div>
    </section>

    <!-- 2. COMPACT BENTO METRICS (Jam Kerja Efektif ASN) -->
    <section class="grid grid-cols-2 gap-3">
        <div
            class="rounded-2xl bg-white dark:bg-zinc-900/90 border border-zinc-200/90 dark:border-zinc-800 p-3.5 shadow-md shadow-slate-900/5">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Hari
                    Ini</span>
                <span
                    class="text-xs font-extrabold text-sky-600 dark:text-sky-400">{{ $jamKerjaAsn['persen_harian'] }}%</span>
            </div>
            <div class="text-base font-black font-mono text-zinc-900 dark:text-white leading-tight">
                {{ $jamKerjaAsn['harian_format'] }}
            </div>
            <div class="text-xs text-zinc-400 mt-0.5">Target {{ $jamKerjaAsn['target_harian_format'] }}</div>
            <div class="w-full h-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800 mt-2.5 overflow-hidden">
                <div class="h-full rounded-full bg-sky-500 transition-all duration-500"
                    style="width: {{ $jamKerjaAsn['persen_harian'] }}%"></div>
            </div>
        </div>

        <div
            class="rounded-2xl bg-white dark:bg-zinc-900/90 border border-zinc-200/90 dark:border-zinc-800 p-3.5 shadow-md shadow-slate-900/5">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Bulan
                    Ini</span>
                <span
                    class="text-xs font-extrabold text-emerald-600 dark:text-emerald-400">{{ $jamKerjaAsn['persen_bulanan'] }}%</span>
            </div>
            <div class="text-base font-black font-mono text-zinc-900 dark:text-white leading-tight">
                {{ $jamKerjaAsn['bulanan_format'] }}
            </div>
            <div class="text-xs text-zinc-400 mt-0.5">Target {{ $jamKerjaAsn['target_bulanan_format'] }}</div>
            <div class="w-full h-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800 mt-2.5 overflow-hidden">
                <div class="h-full rounded-full bg-emerald-500 transition-all duration-500"
                    style="width: {{ $jamKerjaAsn['persen_bulanan'] }}%"></div>
            </div>
        </div>
    </section>

    <!-- 3. STATUS PRESENSI HARI INI -->
    @if ($hariLiburHariIni && !$hariIni['sudah_masuk'])
        <div
            class="rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200/80 dark:border-rose-800/60 p-4 flex items-center gap-3 shadow-sm">
            <div
                class="w-11 h-11 rounded-xl bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-calendar-xmark"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-extrabold text-rose-900 dark:text-rose-200">Hari Libur:
                    {{ $hariLiburHariIni->nama ?? 'Libur' }}</div>
                <div class="text-[13px] text-rose-700/90 dark:text-rose-300/80 font-medium truncate">Tidak ada
                    kewajiban ambil absen hari ini</div>
            </div>
        </div>
    @elseif (!$hariIni['sudah_masuk'])
        @if ($hariIni['belum_buka_masuk'] && !$modeSimulasi)
            <div aria-disabled="true"
                class="rounded-2xl bg-slate-200/80 dark:bg-zinc-800/90 border border-slate-300/80 dark:border-zinc-700 text-slate-500 dark:text-zinc-400 p-4 flex items-center gap-3 opacity-75 cursor-not-allowed select-none">
                <div
                    class="w-11 h-11 rounded-xl bg-slate-300/60 dark:bg-zinc-700/70 flex items-center justify-center text-lg shrink-0 text-slate-500 dark:text-zinc-400">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-extrabold">Absen Masuk Belum Dibuka</div>
                    <div class="text-[13px] font-medium truncate">
                        Dibuka pukul {{ $hariIni['buka_masuk'] }} WIB
                    </div>
                </div>
                <i class="fa-solid fa-lock text-xs opacity-60"></i>
            </div>
        @else
            <a href="{{ route('mobile.absensi') }}" wire:navigate
                class="rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 text-white p-4 flex items-center gap-3 shadow-md shadow-amber-900/15 active:scale-[0.98] transition-transform">
                <div
                    class="w-11 h-11 rounded-xl bg-white/15 border border-white/25 flex items-center justify-center text-lg shrink-0 text-white">
                    <i class="fa-solid fa-fingerprint"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-extrabold">Anda Belum Ambil Absen Masuk</div>
                    <div class="text-[13px] text-amber-50 font-medium truncate">
                        @if ($hariIni['lewat_batas_masuk'])
                            Batas absen ({{ $hariIni['tutup_masuk'] }} WIB) terlewat &bull; Ketuk untuk presensi
                        @else
                            Ketuk untuk ambil absen sebelum {{ $hariIni['tutup_masuk'] }} WIB
                        @endif
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-xs text-white/80"></i>
            </a>
        @endif
    @elseif (!$hariIni['sudah_pulang'])
        <a href="{{ route('mobile.absensi') }}" wire:navigate
            class="rounded-2xl bg-gradient-to-r from-[#0F172A] via-[#0369A1] to-[#0284C7] text-white p-4 flex items-center gap-3 shadow-md shadow-sky-900/15 active:scale-[0.98] transition-transform">
            <div
                class="w-11 h-11 rounded-xl bg-white/15 border border-white/20 flex items-center justify-center text-lg shrink-0 text-white">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-extrabold">Belum Ambil Absen Pulang</div>
                <div class="text-[13px] text-sky-100 font-medium truncate">
                    Masuk {{ substr((string) $hariIni['jam_masuk'], 0, 5) }} WIB &bull; Jadwal pulang
                    {{ $hariIni['jam_pulang_resmi'] }} WIB
                </div>
            </div>
            <i class="fa-solid fa-chevron-right text-xs text-sky-200"></i>
        </a>
    @else
        <div
            class="rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/60 p-4 flex items-center gap-3 shadow-sm">
            <div
                class="w-11 h-11 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-extrabold text-emerald-900 dark:text-emerald-200">Presensi Hari Ini Lengkap
                </div>
                <div class="text-[13px] text-emerald-700/90 dark:text-emerald-300/80 font-medium truncate">
                    Masuk {{ substr((string) $hariIni['jam_masuk'], 0, 5) }} WIB &bull; Pulang
                    {{ substr((string) $hariIni['jam_pulang'], 0, 5) }} WIB
                </div>
            </div>
        </div>
    @endif

    <!-- 5. RIWAYAT ABSEN 7 HARI -->
    <section
        class="rounded-2xl bg-white dark:bg-zinc-900/90 border border-zinc-200/90 dark:border-zinc-800 p-4 shadow-md shadow-slate-900/5">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-xs font-extrabold text-zinc-900 dark:text-white">Riwayat Absen</h2>
            <a href="{{ route('mobile.riwayat') }}" wire:navigate
                class="text-[13px] font-bold text-sky-600 dark:text-sky-400 hover:underline">
                Lihat Semua &rarr;
            </a>
        </div>
        <x-mobile.scroll-list maxHeight="max-h-[260px]">
            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach ($riwayat7Hari as $item)
                    @php
                        $rawStatus = $item['status'] ?? null;
                        $st =
                            $rawStatus instanceof \BackedEnum
                                ? (string) $rawStatus->value
                                : (string) ($rawStatus ?? '');
                        $stLabel =
                            $rawStatus instanceof \Filament\Support\Contracts\HasLabel
                                ? (string) $rawStatus->getLabel()
                                : ($st !== ''
                                    ? ucfirst(str_replace('_', ' ', $st))
                                    : 'Belum ada');
                        $badgeClass = match ($st) {
                            'hadir' => 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300',
                            'terlambat' => 'bg-amber-500/15 text-amber-700 dark:text-amber-300',
                            'izin', 'cuti', 'dinas_luar' => 'bg-sky-500/15 text-sky-700 dark:text-sky-300',
                            'alpha', 'tidak_hadir' => 'bg-rose-500/15 text-rose-700 dark:text-rose-300',
                            default => 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500',
                        };
                    @endphp
                    <li class="py-2.5 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-zinc-900 dark:text-white">{{ $item['hari'] }}</div>
                            <div class="text-xs text-zinc-400">{{ $item['tanggal'] }}</div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-[13px] font-mono text-zinc-600 dark:text-zinc-300">
                                {{ $item['jam_masuk'] ? substr($item['jam_masuk'], 0, 5) : '--:--' }}
                                <span class="text-zinc-300 dark:text-zinc-600">–</span>
                                {{ $item['jam_pulang'] ? substr($item['jam_pulang'], 0, 5) : '--:--' }}
                            </span>
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-bold {{ $badgeClass }}">{{ $stLabel }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-mobile.scroll-list>
    </section>

    <!-- Card: Surat Tugas Tim Kreatif -->
    @if (filled(auth()->user()?->jabatan_tim_kreatif))
        @php
            $uid = auth()->id();
            $suratTugasSaya = \App\Models\SuratTugas::query()
                ->with(['laporans' => fn($q) => $q->where('user_id', $uid)])
                ->where('status', 'terbit')
                ->where(function ($q) use ($uid): void {
                    $q->whereHas('pegawais', fn($p) => $p->where('users.id', $uid))
                        ->orWhereJsonContains('penanggung_jawab_ids', $uid)
                        ->orWhereJsonContains('penanggung_jawab_ids', (string) $uid)
                        ->orWhereJsonContains('koordinator_acara_ids', $uid)
                        ->orWhereJsonContains('koordinator_acara_ids', (string) $uid)
                        ->orWhere('penanggung_jawab_id', $uid);
                })
                ->latest('tanggal_mulai')
                ->take(10)
                ->get();
        @endphp
        <div id="card-surat-tugas"
            class="rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 p-4 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-zinc-900 dark:text-white">Surat Tugas Tim Kreatif</h3>
                    <p class="text-[13px] text-zinc-500">Konfirmasi kehadiran & kirim laporan kegiatan</p>
                </div>
                <span
                    class="px-2.5 py-1 rounded-full bg-teal-50 dark:bg-teal-500/15 text-teal-700 dark:text-teal-300 text-[13px] font-bold">
                    {{ $suratTugasSaya->count() }}
                </span>
            </div>

            <div class="space-y-2.5">
                @forelse ($suratTugasSaya as $st)
                    @php
                        $pivotSt = $st->pegawais()->where('users.id', $uid)->first()?->pivot;
                        $statusHadir = $pivotSt?->status_kehadiran ?? 'belum_konfirmasi';
                    @endphp
                    <div class="p-3 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-950/60 space-y-2"
                        x-data="{ openLapor: false, ringkasan: 'Peliputan, dokumentasi foto/video, dan publikasi kegiatan telah dilaksanakan dengan baik dan lancar.' }">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="text-xs font-bold text-zinc-900 dark:text-white">{{ $st->nama_kegiatan }}
                                </div>
                                <div class="text-xs text-zinc-500 mt-0.5">
                                    {{ $st->tanggal_mulai?->translatedFormat('d M Y') }}
                                    @if (filled($st->jam_acara))
                                        • {{ \Illuminate\Support\Carbon::parse($st->jam_acara)->format('H.i') }} WIB
                                    @endif
                                    @if (filled($st->lokasi))
                                        • {{ $st->lokasi }}
                                    @endif
                                </div>
                            </div>
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-semibold shrink-0 {{ $statusHadir === 'hadir' ? 'bg-emerald-100 text-emerald-700' : 'bg-zinc-200 text-zinc-600' }}">
                                {{ $statusHadir === 'hadir' ? 'Hadir' : 'Belum Konfirmasi' }}
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            @if ($statusHadir !== 'hadir')
                                <button type="button"
                                    wire:click="konfirmasiSuratTugas({{ $st->id }}, 'hadir', '')"
                                    class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-[13px] font-semibold">
                                    Konfirmasi Hadir
                                </button>
                            @endif
                            @if ($statusHadir === 'hadir')
                                <button type="button" @click="openLapor = !openLapor"
                                    class="px-3 py-1.5 rounded-lg bg-sky-600 text-white text-[13px] font-semibold">
                                    {{ $st->laporans->isNotEmpty() ? 'Tambah Laporan' : 'Buat Laporan' }}
                                </button>
                            @endif
                            <a href="{{ route('surat-tugas.cetak-pdf', ['suratTugas' => $st->id]) }}" target="_blank"
                                rel="noopener"
                                class="px-2.5 py-1.5 rounded-lg bg-zinc-200 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 text-[13px] font-semibold">
                                Lihat
                            </a>
                            <a href="{{ route('surat-tugas.cetak-pdf', ['suratTugas' => $st->id]) }}" target="_blank"
                                rel="noopener"
                                class="px-2.5 py-1.5 rounded-lg bg-zinc-200 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 text-[13px] font-semibold">
                                Unduh / Print
                            </a>
                            <a href="https://wa.me/?text={{ urlencode("Surat Tugas: {$st->nama_kegiatan}\n" . route('surat-tugas.cetak-pdf', ['suratTugas' => $st->id])) }}"
                                target="_blank" rel="noopener"
                                class="px-2.5 py-1.5 rounded-lg bg-green-600 text-white text-[13px] font-semibold">
                                Share WA
                            </a>
                            @if ($st->laporans->isNotEmpty())
                                <a href="{{ route('surat-tugas.laporan-pdf', ['suratTugas' => $st->id]) }}"
                                    target="_blank" rel="noopener"
                                    class="px-2.5 py-1.5 rounded-lg bg-indigo-600 text-white text-[13px] font-semibold">
                                    Laporan PDF
                                </a>
                                <a href="https://wa.me/?text={{ urlencode("Laporan Kegiatan: {$st->nama_kegiatan}\n" . route('surat-tugas.laporan-pdf', ['suratTugas' => $st->id])) }}"
                                    target="_blank" rel="noopener"
                                    class="px-2.5 py-1.5 rounded-lg bg-green-700 text-white text-[13px] font-semibold">
                                    Share Laporan WA
                                </a>
                            @endif
                        </div>

                        <div x-show="openLapor" x-cloak
                            class="pt-2 space-y-2 border-t border-zinc-200 dark:border-zinc-800">
                            <textarea x-model="ringkasan" rows="3" placeholder="Ringkasan hasil kegiatan..."
                                class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-xs"></textarea>
                            <button type="button"
                                @click="if (ringkasan.trim() !== '') { $wire.simpanLaporanSuratTugas({{ $st->id }}, ringkasan); openLapor = false; }"
                                class="w-full py-2 rounded-xl bg-teal-600 text-white text-xs font-semibold">
                                Kirim Laporan
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-zinc-400">
                        Belum ada surat tugas aktif.
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    <!-- Rhoe UI Modal: Konfirmasi Kehadiran Berhasil -->
    <x-mobile.rhoe-modal model="konfirmasiSuccessModal" title="Konfirmasi Berhasil"
        subtitle="Status kehadiran Anda telah tercatat">
        <div class="py-4 text-center space-y-3">
            <div
                class="w-14 h-14 mx-auto rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <i class="fa-solid fa-circle-check text-3xl"></i>
            </div>
            <div class="text-sm font-bold text-zinc-900 dark:text-white">
                Hadir Dikonfirmasi!
            </div>
            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                Terima kasih, status kehadiran Anda pada Surat Tugas telah diperbarui dan diberitahukan ke pimpinan.
            </p>
            <button type="button" @click="konfirmasiSuccessModal = false"
                class="w-full py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700">
                Tutup
            </button>
        </div>
    </x-mobile.rhoe-modal>

    <!-- Rhoe UI Modal: Notifikasi -->
    <x-mobile.rhoe-modal model="avatarPreview" title="Foto Profil" maxWidth="max-w-xs">
        <div class="space-y-4 text-center">
            <div class="relative w-52 h-52 mx-auto">
                <img src="{{ $user->avatar_image_url }}" alt="{{ $user->name }}"
                    class="w-52 h-52 rounded-2xl object-cover border border-slate-200 dark:border-slate-700 shadow-md">
                <div wire:loading.flex wire:target="new_avatar"
                    class="absolute inset-0 rounded-2xl bg-slate-900/60 backdrop-blur-xs flex-col items-center justify-center gap-2 text-white">
                    <i class="fa-solid fa-spinner fa-spin text-xl"></i>
                    <span class="text-xs font-bold">Mengunggah foto...</span>
                </div>
            </div>
            @error('new_avatar')
                <p class="text-xs font-semibold text-rose-500">{{ $message }}</p>
            @enderror
            <p class="text-[11px] text-slate-400">Format JPG, PNG, atau WEBP (maks. 2MB)</p>
        </div>

        <x-slot:footer>
            <div class="grid grid-cols-2 gap-2.5 w-full">
                <button type="button" @click="avatarPreview = false"
                    class="h-10 rounded-xl bg-slate-200/80 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold cursor-pointer active:scale-95 transition">
                    Tutup
                </button>
                <label
                    class="h-10 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold flex items-center justify-center gap-1.5 cursor-pointer active:scale-95 transition shadow-sm">
                    <input type="file" wire:model="new_avatar" accept="image/jpeg,image/png,image/webp" class="sr-only">
                    <i class="fa-solid fa-camera text-[13px]"></i>
                    <span>Ganti Foto</span>
                </label>
            </div>
        </x-slot:footer>
    </x-mobile.rhoe-modal>

    <x-mobile.rhoe-modal model="notifSheet" title="Notifikasi ({{ $unreadNotifCount }})"
        subtitle="Pemberitahuan terbaru terkait presensi, izin, dan kinerja">
        <div class="space-y-2">
            @forelse ($recentNotifications as $notif)
                <div wire:key="notif-{{ $notif->id }}" x-data="{ startX: 0, dx: 0, swiping: false, canSwipe: {{ is_null($notif->read_at) ? 'false' : 'true' }} }"
                    @touchstart="if (canSwipe) { startX = $event.touches[0].clientX; swiping = true; }"
                    @touchmove="if (swiping && canSwipe) { dx = $event.touches[0].clientX - startX; }"
                    @touchend="if (swiping && canSwipe && Math.abs(dx) > 90) { $wire.deleteNotification('{{ $notif->id }}'); } dx = 0; swiping = false;"
                    :style="dx ? 'transform: translateX(' + dx + 'px); opacity: ' + Math.max(0.25, 1 - Math.abs(dx) / 160) : ''"
                    class="transition-all duration-150">
                    @php
                        $notifUrl = $notif->data['url'] ?? ($notif->data['actions'][0]['url'] ?? null);
                        $notifTitleLower = strtolower($notif->data['title'] ?? '');
                        $stIdFallback =
                            $notif->data['surat_tugas_id'] ?? ($notif->data['viewData']['surat_tugas_id'] ?? null);
                        if (!$notifUrl) {
                            if ($stIdFallback || str_contains($notifTitleLower, 'surat tugas')) {
                                $notifUrl = route(
                                    'mobile.surat-tugas.index',
                                    $stIdFallback ? ['surat_tugas_id' => $stIdFallback] : [],
                                );
                            } elseif (str_contains($notifTitleLower, 'lhk')) {
                                $notifUrl = route('mobile.lhk.index');
                            } elseif (
                                str_contains($notifTitleLower, 'izin') ||
                                str_contains($notifTitleLower, 'cuti') ||
                                str_contains($notifTitleLower, 'persetujuan')
                            ) {
                                $notifUrl = route('mobile.persetujuan');
                            } elseif (
                                str_contains($notifTitleLower, 'absen') ||
                                str_contains($notifTitleLower, 'presensi')
                            ) {
                                $notifUrl = route('mobile.riwayat');
                            }
                        }
                        $isFilamentUrl = $notifUrl && str_contains($notifUrl, '/admin');
                    @endphp
                    @if ($notifUrl)
                        <a href="{{ $notifUrl }}"
                            @if ($isFilamentUrl) target="_blank" rel="noopener" @else wire:navigate @endif
                            wire:click="markNotificationAsRead('{{ $notif->id }}')" @click="notifSheet = false"
                            class="block p-3 rounded-2xl border cursor-pointer transition-colors {{ is_null($notif->read_at) ? 'bg-sky-50/80 dark:bg-sky-500/10 border-sky-200 dark:border-sky-500/30' : 'bg-zinc-50 dark:bg-zinc-950/60 border-zinc-200/70 dark:border-zinc-800' }}">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-xs font-bold text-zinc-900 dark:text-white">
                                    {{ $notif->data['title'] ?? 'Pemberitahuan' }}</div>
                                <i
                                    class="fa-solid {{ $isFilamentUrl ? 'fa-arrow-up-right-from-square' : 'fa-chevron-right' }} text-xs text-zinc-400"></i>
                            </div>
                            <p class="text-[13px] text-zinc-600 dark:text-zinc-400 mt-0.5">
                                {{ $notif->data['body'] ?? ($notif->data['message'] ?? '') }}</p>
                            <div class="text-[11px] text-zinc-400 mt-1">{{ $notif->created_at->diffForHumans() }}
                            </div>
                        </a>
                    @else
                        <div wire:click="markNotificationAsRead('{{ $notif->id }}')"
                            class="p-3 rounded-2xl border cursor-pointer transition-colors {{ is_null($notif->read_at) ? 'bg-sky-50/80 dark:bg-sky-500/10 border-sky-200 dark:border-sky-500/30' : 'bg-zinc-50 dark:bg-zinc-950/60 border-zinc-200/70 dark:border-zinc-800' }}">
                            <div class="text-xs font-bold text-zinc-900 dark:text-white">
                                {{ $notif->data['title'] ?? 'Pemberitahuan' }}</div>
                            <p class="text-[13px] text-zinc-600 dark:text-zinc-400 mt-0.5">
                                {{ $notif->data['body'] ?? ($notif->data['message'] ?? '') }}</p>
                            <div class="text-[11px] text-zinc-400 mt-1">{{ $notif->created_at->diffForHumans() }}
                            </div>
                            @php
                                $stId =
                                    $notif->data['surat_tugas_id'] ??
                                    ($notif->data['viewData']['surat_tugas_id'] ?? null);
                                $sudahHadir = $stId
                                    ? \Illuminate\Support\Facades\DB::table('surat_tugas_user')
                                        ->where('surat_tugas_id', $stId)
                                        ->where('user_id', auth()->id())
                                        ->where('status_kehadiran', 'hadir')
                                        ->exists()
                                    : false;
                            @endphp
                            @if ($stId)
                                <div class="mt-2 flex flex-wrap items-center gap-2" @click.stop>
                                    @if (!$sudahHadir)
                                        <button type="button"
                                            @click.stop="$wire.konfirmasiSuratTugas({{ $stId }}, 'hadir', '{{ $notif->id }}')"
                                            class="px-3 py-1 rounded-lg bg-emerald-600 text-white text-[13px] font-semibold hover:bg-emerald-700 cursor-pointer">
                                            Konfirmasi Hadir
                                        </button>
                                    @else
                                        <span
                                            class="px-2.5 py-1 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-[13px] font-semibold">
                                            ✓ Sudah Konfirmasi Hadir
                                        </span>
                                    @endif
                                    <a href="{{ route('surat-tugas.cetak-pdf', ['suratTugas' => $stId]) }}"
                                        target="_blank" rel="noopener"
                                        class="px-2.5 py-1 rounded-lg bg-zinc-200 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 text-[13px] font-semibold">
                                        Lihat Surat
                                    </a>
                                    <a href="{{ route('surat-tugas.cetak-pdf', ['suratTugas' => $stId]) }}"
                                        target="_blank" rel="noopener"
                                        class="px-2.5 py-1 rounded-lg bg-zinc-200 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 text-[13px] font-semibold">
                                        Unduh / Print
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="py-8 text-center text-xs text-zinc-400">
                    <i class="fa-regular fa-bell-slash text-2xl mb-2 block opacity-50"></i>
                    Belum ada notifikasi.
                </div>
            @endforelse
        </div>

        <x-slot:footer>
            @if ($unreadNotifCount > 0)
                <button type="button" wire:click="markAllNotificationsAsRead"
                    class="px-3 py-2 rounded-xl bg-sky-500 hover:bg-sky-400 text-white text-xs font-bold transition-colors cursor-pointer">
                    Tandai Dibaca
                </button>
            @endif
            @if ($recentNotifications->isNotEmpty())
                <button type="button" wire:click="clearAllNotifications"
                    class="px-3 py-2 rounded-xl bg-rose-500/15 hover:bg-rose-500/25 text-rose-600 dark:text-rose-400 text-xs font-bold transition-colors cursor-pointer">
                    Bersihkan
                </button>
            @endif
            <button type="button" @click="notifSheet = false"
                class="px-3.5 py-2 rounded-xl bg-zinc-200/80 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 text-xs font-bold cursor-pointer">
                Tutup
            </button>
        </x-slot:footer>
    </x-mobile.rhoe-modal>
</div>
