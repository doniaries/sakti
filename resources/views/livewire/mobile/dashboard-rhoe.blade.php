@php
    $aksiUtama = ! $hariIni['sudah_masuk']
        ? 'masuk'
        : (! $hariIni['sudah_pulang'] ? 'pulang' : 'selesai');
@endphp

<div x-data="{
        notifSheet: false,
        suratTugasSheet: false,
        konfirmasiSuccessModal: false,
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
                    () => $wire.ambilAbsen(jenis, null, null).finally(() => this.punching = false),
                    { enableHighAccuracy: true, timeout: 8000 }
                );
            } else {
                $wire.ambilAbsen(jenis, null, null).finally(() => this.punching = false);
            }
        }
    }"
    @open-notif-sheet.window="notifSheet = true"
    @konfirmasi-hadir-sukses.window="notifSheet = false; konfirmasiSuccessModal = true"
    class="pb-28 px-4 pt-4 space-y-4 text-zinc-900 dark:text-zinc-100">

    <!-- Holiday Notice (Hanya tampil saat hari libur) -->
    @if ($hariLiburHariIni)
        <div class="rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 px-3.5 py-2.5 flex items-center gap-2.5 text-xs text-amber-800 dark:text-amber-300">
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
        <div class="relative overflow-hidden rounded-b-[28px] bg-gradient-to-br from-[#0F172A] via-[#0369A1] to-[#0284C7] px-5 pt-5 pb-12 text-white shadow-lg shadow-sky-950/20">
            <div class="absolute -right-10 -top-10 w-40 h-40 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>

            <!-- Row 1: Avatar + Sapaan + Bell -->
            <div class="flex items-center justify-between gap-3 relative z-10">
                <div class="flex items-center gap-3 min-w-0">
                    <img src="{{ $user->avatar_image_url }}" alt="{{ $user->name }}"
                        class="w-10 h-10 rounded-full object-cover ring-2 ring-white/50 shrink-0">
                    <div class="min-w-0">
                        <div class="text-[11px] text-sky-100 font-medium">{{ $sapaan }},</div>
                        <h1 class="text-sm font-extrabold tracking-tight truncate">{{ $user->name }}</h1>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    @if ($user->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian', 'admin_keuangan', 'pptk', 'ppk', 'pa']))
                        <a href="/admin" target="_blank" rel="noopener"
                            class="h-9 px-3 rounded-full bg-amber-400 hover:bg-amber-300 text-slate-950 text-[11px] font-extrabold flex items-center gap-1.5 shadow-sm transition cursor-pointer"
                            title="Buka Panel Admin SAKTI di tab baru">
                            <i class="fa-solid fa-shield-halved text-xs"></i>
                            <span>Panel Admin</span>
                        </a>
                    @endif
                    <button type="button" @click="notifSheet = true"
                        class="relative w-9 h-9 rounded-full bg-white/15 hover:bg-white/25 flex items-center justify-center transition cursor-pointer shrink-0">
                        <i class="fa-solid fa-bell text-sm {{ $unreadNotifCount > 0 ? 'text-amber-300 notif-bell-ring' : '' }}"></i>
                        @if ($unreadNotifCount > 0)
                            <span class="absolute top-0.5 right-0.5 flex h-2.5 w-2.5 notif-dot-move">
                                <span class="notif-dot-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-rose-500 ring-2 ring-sky-800"></span>
                            </span>
                        @endif
                    </button>
                    @if ($unreadNotifCount > 0)
                        <style>
                            @keyframes notifBellRing {
                                0%, 100% { transform: rotate(0deg); }
                                15% { transform: rotate(16deg); }
                                30% { transform: rotate(-14deg); }
                                45% { transform: rotate(10deg); }
                                60% { transform: rotate(-8deg); }
                                75% { transform: rotate(4deg); }
                            }
                            @keyframes notifDotMove {
                                0%, 100% { transform: translateY(0) scale(1); }
                                50% { transform: translateY(-2px) scale(1.18); }
                            }
                            @keyframes notifDotPing {
                                0% { transform: scale(1); opacity: 0.8; }
                                75%, 100% { transform: scale(2.2); opacity: 0; }
                            }
                            .notif-bell-ring { transform-origin: top center; animation: notifBellRing 1.6s ease-in-out infinite; }
                            .notif-dot-move { animation: notifDotMove 1.1s ease-in-out infinite; }
                            .notif-dot-ping { animation: notifDotPing 1.4s cubic-bezier(0, 0, 0.2, 1) infinite; }
                        </style>
                    @endif
                </div>
            </div>

            <!-- Row 2: Jam Live & Status Masuk/Pulang -->
            <div class="mt-5 flex items-center justify-between gap-3 relative z-10">
                <div class="min-w-0">
                    <div class="text-[11px] text-sky-100 font-medium">{{ now()->translatedFormat('l, d M Y') }}</div>
                    <div class="flex items-baseline gap-1 font-mono mt-0.5">
                        <span x-text="jam" class="text-3xl font-black tracking-tight">--:--</span>
                        <span x-text="':' + detik" class="text-sm font-bold text-sky-200"></span>
                        <span class="text-[10px] font-bold text-sky-200 ml-0.5">WIB</span>
                    </div>
                </div>
                <div class="flex items-center gap-3 pl-3.5 border-l border-white/25 text-right shrink-0">
                    <div>
                        <div class="text-[11px] font-semibold text-sky-100">Masuk</div>
                        <div class="text-base sm:text-lg font-black font-mono leading-tight">{{ $hariIni['jam_masuk'] ? \Carbon\Carbon::parse($hariIni['jam_masuk'])->format('H:i') : '--:--' }}</div>
                    </div>
                    <div class="w-px h-8 bg-white/20"></div>
                    <div>
                        <div class="text-[11px] font-semibold text-sky-100">Pulang</div>
                        <div class="text-base sm:text-lg font-black font-mono leading-tight">{{ $hariIni['jam_pulang'] ? \Carbon\Carbon::parse($hariIni['jam_pulang'])->format('H:i') : '--:--' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overlapping White Quick Action Card (Semua Layanan) -->
        @php
            $layanan = [
                ['route' => 'mobile.absensi', 'icon' => 'fa-fingerprint', 'label' => 'Presensi', 'color' => 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400'],
                ['route' => 'mobile.lhk.create', 'module' => 'lhk', 'icon' => 'fa-pen-to-square', 'label' => 'Isi LHK', 'color' => 'bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400'],
                ['route' => 'mobile.izin.index', 'module' => 'izin', 'icon' => 'fa-envelope-open-text', 'label' => 'Izin & Cuti', 'color' => 'bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400'],
                ['route' => 'mobile.riwayat', 'icon' => 'fa-clock-rotate-left', 'label' => 'Riwayat', 'color' => 'bg-violet-50 dark:bg-violet-500/15 text-violet-600 dark:text-violet-400'],
                ['route' => 'mobile.hari-libur', 'icon' => 'fa-calendar-day', 'label' => 'Hari Libur', 'color' => 'bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400'],
                ['route' => 'mobile.lhk.bank-aktivitas', 'module' => 'lhk', 'icon' => 'fa-box-archive', 'label' => 'Bank Aktivitas', 'color' => 'bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400'],
                ['route' => 'mobile.reputasi', 'module' => 'reputasi', 'icon' => 'fa-medal', 'label' => 'Reputasi', 'color' => 'bg-yellow-50 dark:bg-yellow-500/15 text-yellow-600 dark:text-yellow-400'],
                ['route' => 'mobile.agenda', 'module' => 'agenda', 'icon' => 'fa-calendar-check', 'label' => 'Agenda', 'color' => 'bg-sky-50 dark:bg-sky-500/15 text-sky-600 dark:text-sky-400'],
                ['route' => 'mobile.persetujuan', 'roles' => ['super_admin', 'admin', 'admin_kepegawaian', 'admin_keuangan', 'kabid', 'pptk', 'ppk', 'pa'], 'icon' => 'fa-file-signature', 'label' => 'Persetujuan', 'color' => 'bg-teal-50 dark:bg-teal-500/15 text-teal-600 dark:text-teal-400'],
            ];
        @endphp
        <div class="relative z-10 -mt-8 mx-4 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 p-3.5 shadow-xl shadow-slate-900/10 ring-1 ring-slate-900/5">
            <div class="grid grid-cols-4 gap-x-2 gap-y-3">
                @foreach ($layanan as $item)
                    @if (Route::has($item['route']) && (empty($item['module']) || \App\Models\PengaturanAplikasi::isModuleEnabled($item['module'])) && (empty($item['roles']) || $user->hasAnyRole($item['roles'])))
                        <a href="{{ route($item['route']) }}" wire:navigate class="flex flex-col items-center gap-1.5 active:scale-95 transition-transform">
                            <div class="w-11 h-11 rounded-2xl {{ $item['color'] }} border border-slate-200/80 dark:border-zinc-700/70 flex items-center justify-center text-base">
                                <i class="fa-solid {{ $item['icon'] }}"></i>
                            </div>
                            <span class="text-[10px] font-semibold text-zinc-700 dark:text-zinc-300 text-center leading-tight">{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
                @if (filled(auth()->user()?->jabatan_tim_kreatif))
                    <button type="button" @click="suratTugasSheet = true"
                        class="flex flex-col items-center gap-1.5 group cursor-pointer">
                        <div class="w-12 h-12 rounded-2xl bg-teal-50 dark:bg-teal-500/15 text-teal-600 dark:text-teal-400 flex items-center justify-center shadow-sm group-hover:scale-105 transition-transform">
                            <i class="fa-solid fa-file-signature text-lg"></i>
                        </div>
                        <span class="text-[11px] font-medium text-zinc-700 dark:text-zinc-300 text-center leading-tight">Surat Tugas</span>
                    </button>
                @endif
            </div>
        </div>
    </section>

    <!-- 2. COMPACT BENTO METRICS (Jam Kerja Efektif ASN) -->
    <section class="grid grid-cols-2 gap-3">
        <div class="rounded-2xl bg-white dark:bg-zinc-900/90 border border-zinc-200/90 dark:border-zinc-800 p-3.5 shadow-md shadow-slate-900/5">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Hari Ini</span>
                <span class="text-[10px] font-extrabold text-sky-600 dark:text-sky-400">{{ $jamKerjaAsn['persen_harian'] }}%</span>
            </div>
            <div class="text-base font-black font-mono text-zinc-900 dark:text-white leading-tight">
                {{ $jamKerjaAsn['harian_format'] }}
            </div>
            <div class="text-[10px] text-zinc-400 mt-0.5">Target {{ $jamKerjaAsn['target_harian_format'] }}</div>
            <div class="w-full h-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800 mt-2.5 overflow-hidden">
                <div class="h-full rounded-full bg-sky-500 transition-all duration-500" style="width: {{ $jamKerjaAsn['persen_harian'] }}%"></div>
            </div>
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900/90 border border-zinc-200/90 dark:border-zinc-800 p-3.5 shadow-md shadow-slate-900/5">
            <div class="flex items-center justify-between mb-1.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Bulan Ini</span>
                <span class="text-[10px] font-extrabold text-emerald-600 dark:text-emerald-400">{{ $jamKerjaAsn['persen_bulanan'] }}%</span>
            </div>
            <div class="text-base font-black font-mono text-zinc-900 dark:text-white leading-tight">
                {{ $jamKerjaAsn['bulanan_format'] }}
            </div>
            <div class="text-[10px] text-zinc-400 mt-0.5">Target {{ $jamKerjaAsn['target_bulanan_format'] }}</div>
            <div class="w-full h-1.5 rounded-full bg-zinc-100 dark:bg-zinc-800 mt-2.5 overflow-hidden">
                <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ $jamKerjaAsn['persen_bulanan'] }}%"></div>
            </div>
        </div>
    </section>

    <!-- 3. STATUS PRESENSI HARI INI -->
    @if ($hariLiburHariIni && !$hariIni['sudah_masuk'])
        <div class="rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200/80 dark:border-rose-800/60 p-4 flex items-center gap-3 shadow-sm">
            <div class="w-11 h-11 rounded-xl bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-calendar-xmark"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-extrabold text-rose-900 dark:text-rose-200">Hari Libur: {{ $hariLiburHariIni->nama ?? 'Libur' }}</div>
                <div class="text-[11px] text-rose-700/90 dark:text-rose-300/80 font-medium truncate">Tidak ada kewajiban ambil absen hari ini</div>
            </div>
        </div>
    @elseif (!$hariIni['sudah_masuk'])
        <a href="{{ route('mobile.absensi') }}" wire:navigate
            class="rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500 text-white p-4 flex items-center gap-3 shadow-md shadow-amber-900/15 active:scale-[0.98] transition-transform">
            <div class="w-11 h-11 rounded-xl bg-white/15 border border-white/25 flex items-center justify-center text-lg shrink-0 text-white">
                <i class="fa-solid fa-fingerprint"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-extrabold">Anda Belum Ambil Absen Masuk</div>
                <div class="text-[11px] text-amber-50 font-medium truncate">
                    @if ($hariIni['belum_buka_masuk'])
                        Absen masuk dibuka pukul {{ $hariIni['buka_masuk'] }} WIB
                    @elseif ($hariIni['lewat_batas_masuk'])
                        Batas absen ({{ $hariIni['tutup_masuk'] }} WIB) terlewat &bull; Ketuk untuk presensi
                    @else
                        Ketuk untuk ambil absen sebelum {{ $hariIni['tutup_masuk'] }} WIB
                    @endif
                </div>
            </div>
            <i class="fa-solid fa-chevron-right text-xs text-white/80"></i>
        </a>
    @elseif (!$hariIni['sudah_pulang'])
        <a href="{{ route('mobile.absensi') }}" wire:navigate
            class="rounded-2xl bg-gradient-to-r from-[#0F172A] via-[#0369A1] to-[#0284C7] text-white p-4 flex items-center gap-3 shadow-md shadow-sky-900/15 active:scale-[0.98] transition-transform">
            <div class="w-11 h-11 rounded-xl bg-white/15 border border-white/20 flex items-center justify-center text-lg shrink-0 text-white">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-extrabold">Belum Ambil Absen Pulang</div>
                <div class="text-[11px] text-sky-100 font-medium truncate">
                    Masuk {{ substr((string) $hariIni['jam_masuk'], 0, 5) }} WIB &bull; Jadwal pulang {{ $hariIni['jam_pulang_resmi'] }} WIB
                </div>
            </div>
            <i class="fa-solid fa-chevron-right text-xs text-sky-200"></i>
        </a>
    @else
        <div class="rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/80 dark:border-emerald-800/60 p-4 flex items-center gap-3 shadow-sm">
            <div class="w-11 h-11 rounded-xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-extrabold text-emerald-900 dark:text-emerald-200">Presensi Hari Ini Lengkap</div>
                <div class="text-[11px] text-emerald-700/90 dark:text-emerald-300/80 font-medium truncate">
                    Masuk {{ substr((string) $hariIni['jam_masuk'], 0, 5) }} WIB &bull; Pulang {{ substr((string) $hariIni['jam_pulang'], 0, 5) }} WIB
                </div>
            </div>
        </div>
    @endif

    <!-- 5. RIWAYAT ABSEN 7 HARI -->
    <section class="rounded-2xl bg-white dark:bg-zinc-900/90 border border-zinc-200/90 dark:border-zinc-800 p-4 shadow-md shadow-slate-900/5">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-xs font-extrabold text-zinc-900 dark:text-white">Riwayat Absen</h2>
            <a href="{{ route('mobile.riwayat') }}" wire:navigate class="text-[11px] font-bold text-sky-600 dark:text-sky-400 hover:underline">
                Lihat Semua &rarr;
            </a>
        </div>
        <x-mobile.scroll-list maxHeight="max-h-[260px]">
            <ul class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @foreach ($riwayat7Hari as $item)
                    @php
                        $rawStatus = $item['status'] ?? null;
                        $st = $rawStatus instanceof \BackedEnum ? (string) $rawStatus->value : (string) ($rawStatus ?? '');
                        $stLabel = $rawStatus instanceof \Filament\Support\Contracts\HasLabel
                            ? (string) $rawStatus->getLabel()
                            : ($st !== '' ? ucfirst(str_replace('_', ' ', $st)) : 'Belum ada');
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
                            <div class="text-[10px] text-zinc-400">{{ $item['tanggal'] }}</div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-[11px] font-mono text-zinc-600 dark:text-zinc-300">
                                {{ $item['jam_masuk'] ? substr($item['jam_masuk'], 0, 5) : '--:--' }}
                                <span class="text-zinc-300 dark:text-zinc-600">–</span>
                                {{ $item['jam_pulang'] ? substr($item['jam_pulang'], 0, 5) : '--:--' }}
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $badgeClass }}">{{ $stLabel }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </x-mobile.scroll-list>
    </section>

    <!-- Rhoe UI Modal: Surat Tugas Tim Kreatif -->
    @if (filled(auth()->user()?->jabatan_tim_kreatif))
        @php
            $uid = auth()->id();
            $suratTugasSaya = \App\Models\SuratTugas::query()
                ->with(['laporans' => fn ($q) => $q->where('user_id', $uid)])
                ->where('status', 'terbit')
                ->where(function ($q) use ($uid): void {
                    $q->whereHas('pegawais', fn ($p) => $p->where('users.id', $uid))
                        ->orWhereJsonContains('penanggung_jawab_ids', $uid)
                        ->orWhereJsonContains('koordinator_acara_ids', $uid)
                        ->orWhere('penanggung_jawab_id', $uid);
                })
                ->latest('tanggal_mulai')
                ->take(10)
                ->get();
        @endphp
        <x-mobile.rhoe-modal model="suratTugasSheet" title="Surat Tugas Tim Kreatif" subtitle="Konfirmasi kehadiran & kirim laporan kegiatan">
            <div class="space-y-3">
                @forelse ($suratTugasSaya as $st)
                    @php
                        $pivotSt = $st->pegawais()->where('users.id', auth()->id())->first()?->pivot;
                        $statusHadir = $pivotSt?->status_kehadiran ?? 'belum_konfirmasi';
                    @endphp
                    <div class="p-3 rounded-2xl border border-zinc-200/80 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-950/60 space-y-2"
                         x-data="{ openLapor: false, ringkasan: '', link: '' }">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <div class="text-xs font-bold text-zinc-900 dark:text-white">{{ $st->nama_kegiatan }}</div>
                                <div class="text-[10px] text-zinc-500 mt-0.5">
                                    {{ $st->tanggal_mulai?->translatedFormat('d M Y') }}
                                    @if (filled($st->jam_acara)) • {{ \Illuminate\Support\Carbon::parse($st->jam_acara)->format('H.i') }} WIB @endif
                                    @if (filled($st->lokasi)) • {{ $st->lokasi }} @endif
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $statusHadir === 'hadir' ? 'bg-emerald-100 text-emerald-700' : 'bg-zinc-200 text-zinc-600' }}">
                                {{ $statusHadir === 'hadir' ? 'Siap Hadir' : 'Belum Konfirmasi' }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 pt-1">
                            @if ($statusHadir !== 'hadir')
                                <button type="button"
                                    wire:click="konfirmasiSuratTugas({{ $st->id }}, 'hadir', '')"
                                    class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-[11px] font-semibold">
                                    Siap Hadir
                                </button>
                            @endif
                            <button type="button"
                                @click="openLapor = !openLapor"
                                class="px-3 py-1.5 rounded-lg bg-sky-600 text-white text-[11px] font-semibold">
                                {{ $st->laporans->isNotEmpty() ? 'Tambah Laporan' : 'Buat Laporan' }}
                            </button>
                            <a href="{{ route('surat-tugas.cetak-pdf', ['suratTugas' => $st->id]) }}" target="_blank" rel="noopener"
                               class="px-2.5 py-1.5 rounded-lg bg-zinc-200 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 text-[11px] font-semibold">
                                Lihat
                            </a>
                            <a href="{{ route('surat-tugas.cetak-pdf', ['suratTugas' => $st->id]) }}" target="_blank" rel="noopener"
                               class="px-2.5 py-1.5 rounded-lg bg-zinc-200 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 text-[11px] font-semibold">
                                Unduh / Print
                            </a>
                            <a href="https://wa.me/?text={{ urlencode("Surat Tugas: {$st->nama_kegiatan}\n" . route('surat-tugas.cetak-pdf', ['suratTugas' => $st->id])) }}" target="_blank" rel="noopener"
                               class="px-2.5 py-1.5 rounded-lg bg-green-600 text-white text-[11px] font-semibold">
                                Share WA
                            </a>
                            @if ($st->laporans->isNotEmpty())
                                <a href="{{ route('surat-tugas.laporan-pdf', ['suratTugas' => $st->id]) }}" target="_blank" rel="noopener"
                                   class="px-2.5 py-1.5 rounded-lg bg-indigo-600 text-white text-[11px] font-semibold">
                                    Laporan PDF
                                </a>
                                <a href="https://wa.me/?text={{ urlencode("Laporan Kegiatan: {$st->nama_kegiatan}\n" . route('surat-tugas.laporan-pdf', ['suratTugas' => $st->id])) }}" target="_blank" rel="noopener"
                                   class="px-2.5 py-1.5 rounded-lg bg-green-700 text-white text-[11px] font-semibold">
                                    Share Laporan WA
                                </a>
                            @endif
                        </div>

                        <div x-show="openLapor" x-cloak class="pt-2 space-y-2 border-t border-zinc-200 dark:border-zinc-800">
                            <textarea x-model="ringkasan" rows="2" placeholder="Ringkasan hasil kegiatan..."
                                class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-xs"></textarea>
                            <input x-model="link" type="url" placeholder="Link dokumentasi (Google Drive / YouTube, opsional)"
                                class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-3 py-2 text-xs">
                            <button type="button"
                                @click="if (ringkasan.trim() !== '') { $wire.simpanLaporanSuratTugas({{ $st->id }}, ringkasan, link); ringkasan = ''; link = ''; openLapor = false; }"
                                class="w-full py-2 rounded-xl bg-teal-600 text-white text-xs font-semibold">
                                Kirim Laporan
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="py-8 text-center text-xs text-zinc-400">
                        Belum ada surat tugas aktif.
                    </div>
                @endforelse
            </div>
        </x-mobile.rhoe-modal>
    @endif

    <!-- Rhoe UI Modal: Konfirmasi Kehadiran Berhasil -->
    <x-mobile.rhoe-modal model="konfirmasiSuccessModal" title="Konfirmasi Berhasil" subtitle="Status kehadiran Anda telah tercatat">
        <div class="py-4 text-center space-y-3">
            <div class="w-14 h-14 mx-auto rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                <i class="fa-solid fa-circle-check text-3xl"></i>
            </div>
            <div class="text-sm font-bold text-zinc-900 dark:text-white">
                Siap Hadir Dikonfirmasi!
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
    <x-mobile.rhoe-modal model="notifSheet" title="Notifikasi ({{ $unreadNotifCount }})" subtitle="Pemberitahuan terbaru terkait presensi, izin, dan kinerja">
        <div class="space-y-2">
            @forelse ($recentNotifications as $notif)
                @php
                    $notifUrl = $notif->data['url'] ?? ($notif->data['actions'][0]['url'] ?? null);
                    $isFilamentUrl = $notifUrl && str_contains($notifUrl, '/admin');
                @endphp
                @if ($notifUrl)
                    <a href="{{ $notifUrl }}"
                       @if ($isFilamentUrl) target="_blank" rel="noopener" @else wire:navigate @endif
                       wire:click="markNotificationAsRead('{{ $notif->id }}')"
                       @click="notifSheet = false"
                       class="block p-3 rounded-2xl border cursor-pointer transition-colors {{ is_null($notif->read_at) ? 'bg-sky-50/80 dark:bg-sky-500/10 border-sky-200 dark:border-sky-500/30' : 'bg-zinc-50 dark:bg-zinc-950/60 border-zinc-200/70 dark:border-zinc-800' }}">
                        <div class="flex items-center justify-between gap-2">
                            <div class="text-xs font-bold text-zinc-900 dark:text-white">{{ $notif->data['title'] ?? 'Pemberitahuan' }}</div>
                            <i class="fa-solid {{ $isFilamentUrl ? 'fa-arrow-up-right-from-square' : 'fa-chevron-right' }} text-[10px] text-zinc-400"></i>
                        </div>
                        <p class="text-[11px] text-zinc-600 dark:text-zinc-400 mt-0.5">{{ $notif->data['body'] ?? ($notif->data['message'] ?? '') }}</p>
                        <div class="text-[9px] text-zinc-400 mt-1">{{ $notif->created_at->diffForHumans() }}</div>
                    </a>
                @else
                    <div wire:click="markNotificationAsRead('{{ $notif->id }}')"
                        class="p-3 rounded-2xl border cursor-pointer transition-colors {{ is_null($notif->read_at) ? 'bg-sky-50/80 dark:bg-sky-500/10 border-sky-200 dark:border-sky-500/30' : 'bg-zinc-50 dark:bg-zinc-950/60 border-zinc-200/70 dark:border-zinc-800' }}">
                        <div class="text-xs font-bold text-zinc-900 dark:text-white">{{ $notif->data['title'] ?? 'Pemberitahuan' }}</div>
                        <p class="text-[11px] text-zinc-600 dark:text-zinc-400 mt-0.5">{{ $notif->data['body'] ?? ($notif->data['message'] ?? '') }}</p>
                        <div class="text-[9px] text-zinc-400 mt-1">{{ $notif->created_at->diffForHumans() }}</div>
                        @php
                            $stId = $notif->data['surat_tugas_id'] ?? ($notif->data['viewData']['surat_tugas_id'] ?? null);
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
                                @if (! $sudahHadir)
                                    <button type="button"
                                        @click.stop="$wire.konfirmasiSuratTugas({{ $stId }}, 'hadir', '{{ $notif->id }}')"
                                        class="px-3 py-1 rounded-lg bg-emerald-600 text-white text-[11px] font-semibold hover:bg-emerald-700 cursor-pointer">
                                        Siap Hadir
                                    </button>
                                @else
                                    <span class="px-2.5 py-1 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 text-[11px] font-semibold">
                                        ✓ Sudah Konfirmasi Hadir
                                    </span>
                                @endif
                                <a href="{{ route('surat-tugas.cetak-pdf', ['suratTugas' => $stId]) }}" target="_blank" rel="noopener"
                                   class="px-2.5 py-1 rounded-lg bg-zinc-200 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 text-[11px] font-semibold">
                                    Lihat Surat
                                </a>
                                <a href="{{ route('surat-tugas.cetak-pdf', ['suratTugas' => $stId]) }}" target="_blank" rel="noopener"
                                   class="px-2.5 py-1 rounded-lg bg-zinc-200 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 text-[11px] font-semibold">
                                    Unduh / Print
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
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
