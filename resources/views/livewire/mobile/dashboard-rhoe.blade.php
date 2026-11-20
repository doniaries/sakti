@php
    $hour = (int) now()->format('H');
    $sapaan = match (true) {
        $hour >= 4 && $hour < 11 => 'Selamat Pagi',
        $hour >= 11 && $hour < 15 => 'Selamat Siang',
        $hour >= 15 && $hour < 18 => 'Selamat Sore',
        default => 'Selamat Malam',
    };

    $aksiUtama = ! $hariIni['sudah_masuk']
        ? 'masuk'
        : (! $hariIni['sudah_pulang'] ? 'pulang' : 'selesai');
@endphp

<div x-data="{
        notifSheet: false,
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
    class="pb-28 px-4 pt-4 space-y-4 text-zinc-900 dark:text-zinc-100">

    <!-- Holiday Notice (Hanya tampil saat hari libur) -->
    @if ($hariLiburHariIni)
        <div class="rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 px-3.5 py-2.5 flex items-center gap-2.5 text-xs text-amber-800 dark:text-amber-300">
            <i class="fa-solid fa-calendar-day text-amber-500"></i>
            <span class="font-bold truncate">Hari Libur: {{ $hariLiburHariIni->nama }}</span>
        </div>
    @endif

    <!-- 1. UNIFIED HERO COMMAND CARD (Profil + Jam + Status + 1 Tombol Aksi Cerdas) -->
    <section class="rounded-3xl bg-white dark:bg-zinc-900/90 border border-zinc-200/90 dark:border-zinc-800 p-4 shadow-sm space-y-4">
        <!-- Row 1: Compact User Identity -->
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <label class="relative shrink-0 cursor-pointer group" title="Ketuk untuk ganti foto profil">
                    <input type="file" wire:model="new_avatar" accept="image/*" class="hidden">
                    <img src="{{ $user->avatar_image_url }}" alt="{{ $user->name }}"
                        class="w-11 h-11 rounded-2xl object-cover border border-zinc-200 dark:border-zinc-700 shadow-xs group-hover:opacity-80 transition-opacity">
                    <span wire:loading wire:target="new_avatar"
                        class="absolute inset-0 rounded-2xl bg-black/50 flex items-center justify-center">
                        <i class="fa-solid fa-circle-notch fa-spin text-white text-xs"></i>
                    </span>
                </label>
                <div class="min-w-0">
                    <div class="text-[11px] font-medium text-zinc-500 dark:text-zinc-400">{{ $sapaan }},</div>
                    <h1 class="text-sm font-black tracking-tight text-zinc-900 dark:text-white truncate">{{ $user->name }}</h1>
                    <div class="text-[10px] text-zinc-500 dark:text-zinc-400 truncate">
                        {{ $user->jabatan?->nama ?? 'Pegawai' }}@if ($user->bidang?->nama) &bull; {{ $user->bidang->nama }} @endif
                    </div>
                </div>
            </div>

            <span class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider {{ strtolower($user->status_kerja ?? 'wfo') === 'wfh' ? 'bg-amber-500/15 text-amber-700 dark:text-amber-300' : 'bg-emerald-500/15 text-emerald-700 dark:text-emerald-300' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ strtolower($user->status_kerja ?? 'wfo') === 'wfh' ? 'bg-amber-500' : 'bg-emerald-500' }} animate-pulse"></span>
                {{ strtoupper($user->status_kerja ?? 'WFO') }}
            </span>
        </div>

        <!-- Row 2: Live Clock + Today's Timestamps (Compact Split) -->
        <div class="rounded-2xl bg-zinc-50 dark:bg-zinc-950/70 border border-zinc-200/70 dark:border-zinc-800/80 px-4 py-3 flex items-center justify-between gap-3">
            <div>
                <div class="text-[10px] font-semibold text-zinc-500 dark:text-zinc-400">
                    {{ now()->translatedFormat('l, d M Y') }}
                </div>
                <div class="flex items-baseline gap-1 font-mono mt-0.5">
                    <span x-text="jam" class="text-2xl font-black tracking-tight text-zinc-900 dark:text-white">--:--</span>
                    <span x-text="':' + detik" class="text-xs font-bold text-zinc-400"></span>
                    <span class="text-[10px] font-extrabold text-sky-600 dark:text-sky-400 ml-0.5">WIB</span>
                </div>
            </div>

            <div class="flex items-center gap-3 text-right">
                <div>
                    <div class="text-[9px] font-bold uppercase tracking-wider text-zinc-400">Masuk</div>
                    <div class="text-xs font-black font-mono {{ $hariIni['jam_masuk'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-400' }}">
                        {{ $hariIni['jam_masuk'] ? \Carbon\Carbon::parse($hariIni['jam_masuk'])->format('H:i') : '--:--' }}
                    </div>
                </div>
                <div class="w-px h-7 bg-zinc-200 dark:bg-zinc-800"></div>
                <div>
                    <div class="text-[9px] font-bold uppercase tracking-wider text-zinc-400">Pulang</div>
                    <div class="text-xs font-black font-mono {{ $hariIni['jam_pulang'] ? 'text-sky-600 dark:text-sky-400' : 'text-zinc-400' }}">
                        {{ $hariIni['jam_pulang'] ? \Carbon\Carbon::parse($hariIni['jam_pulang'])->format('H:i') : '--:--' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 3: Arahkan Presensi ke Halaman Presensi (1 Pintu dengan Peta & Radius GPS) -->
        @if ($aksiUtama !== 'selesai')
            <a href="{{ route('mobile.absensi') }}" wire:navigate
                class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-sky-600 to-teal-500 hover:from-sky-500 hover:to-teal-400 text-white font-extrabold text-xs shadow-lg shadow-sky-600/20 flex items-center justify-center gap-2 active:scale-[0.98] transition-all">
                <i class="fa-solid fa-location-crosshairs text-sm"></i>
                <span>{{ $aksiUtama === 'masuk' ? 'Buka Halaman Presensi Masuk' : 'Buka Halaman Presensi Pulang' }}</span>
                <i class="fa-solid fa-arrow-right text-[11px]"></i>
            </a>
        @else
            <div class="w-full py-3 px-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/25 text-emerald-700 dark:text-emerald-300 font-bold text-xs flex items-center justify-center gap-2">
                <i class="fa-solid fa-circle-check"></i>
                <span>Presensi Masuk &amp; Pulang Hari Ini Selesai</span>
            </div>
        @endif
    </section>

    <!-- 2. COMPACT BENTO METRICS (Jam Kerja Efektif ASN) -->
    <section class="grid grid-cols-2 gap-3">
        <div class="rounded-2xl bg-white dark:bg-zinc-900/90 border border-zinc-200/90 dark:border-zinc-800 p-3.5 shadow-xs">
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

        <div class="rounded-2xl bg-white dark:bg-zinc-900/90 border border-zinc-200/90 dark:border-zinc-800 p-3.5 shadow-xs">
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

    <!-- 3. QUICK LHK -->
    <a href="{{ route('mobile.lhk.create') }}" wire:navigate
        class="rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-600 text-white p-4 flex items-center gap-3 shadow-md shadow-violet-600/20 active:scale-[0.98] transition-transform">
        <div class="w-11 h-11 rounded-xl bg-white/15 flex items-center justify-center text-lg shrink-0">
            <i class="fa-solid fa-pen-to-square"></i>
        </div>
        <div class="min-w-0 flex-1">
            <div class="text-sm font-extrabold">Isi LHK Hari Ini</div>
            <div class="text-[11px] text-violet-100 truncate">Catat laporan kinerja harian Anda</div>
        </div>
        <i class="fa-solid fa-chevron-right text-xs text-white/70"></i>
    </a>

    <!-- 4. LAYANAN -->
    @php
        $layanan = [
            ['route' => 'mobile.lhk.index', 'icon' => 'fa-clipboard-list', 'label' => 'LHK', 'color' => 'bg-violet-500/15 text-violet-600 dark:text-violet-400'],
            ['route' => 'mobile.izin.index', 'icon' => 'fa-envelope-open-text', 'label' => 'Izin & Cuti', 'color' => 'bg-amber-500/15 text-amber-600 dark:text-amber-400'],
            ['route' => 'mobile.riwayat', 'icon' => 'fa-clock-rotate-left', 'label' => 'Riwayat', 'color' => 'bg-sky-500/15 text-sky-600 dark:text-sky-400'],
            ['route' => 'mobile.hari-libur', 'icon' => 'fa-calendar-day', 'label' => 'Hari Libur', 'color' => 'bg-rose-500/15 text-rose-600 dark:text-rose-400'],
            ['route' => 'mobile.lhk.bank-aktivitas', 'icon' => 'fa-box-archive', 'label' => 'Bank Aktivitas', 'color' => 'bg-indigo-500/15 text-indigo-600 dark:text-indigo-400'],
            ['route' => 'mobile.reputasi', 'icon' => 'fa-medal', 'label' => 'Reputasi', 'color' => 'bg-yellow-500/15 text-yellow-600 dark:text-yellow-400'],
            ['route' => 'mobile.persetujuan', 'icon' => 'fa-file-signature', 'label' => 'Persetujuan', 'color' => 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400'],
            ['route' => 'mobile.profil', 'icon' => 'fa-circle-user', 'label' => 'Profil', 'color' => 'bg-zinc-500/15 text-zinc-600 dark:text-zinc-300'],
        ];
    @endphp
    <section class="rounded-2xl bg-white dark:bg-zinc-900/90 border border-zinc-200/90 dark:border-zinc-800 p-4 shadow-xs">
        <h2 class="text-xs font-extrabold text-zinc-900 dark:text-white mb-3">Layanan</h2>
        <div class="grid grid-cols-4 gap-x-2 gap-y-3">
            @foreach ($layanan as $item)
                @if (Route::has($item['route']))
                    <a href="{{ route($item['route']) }}" wire:navigate class="flex flex-col items-center gap-1.5 active:scale-95 transition-transform">
                        <div class="w-11 h-11 rounded-2xl {{ $item['color'] }} flex items-center justify-center text-base">
                            <i class="fa-solid {{ $item['icon'] }}"></i>
                        </div>
                        <span class="text-[10px] font-semibold text-zinc-600 dark:text-zinc-300 text-center leading-tight">{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </section>

    <!-- 5. RIWAYAT ABSEN 7 HARI -->
    <section class="rounded-2xl bg-white dark:bg-zinc-900/90 border border-zinc-200/90 dark:border-zinc-800 p-4 shadow-xs">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-xs font-extrabold text-zinc-900 dark:text-white">Riwayat Absen</h2>
            <a href="{{ route('mobile.riwayat') }}" wire:navigate class="text-[11px] font-bold text-sky-600 dark:text-sky-400 hover:underline">
                Lihat Semua &rarr;
            </a>
        </div>
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
    </section>

    <!-- Rhoe UI Modal: Notifikasi -->
    <x-mobile.rhoe-modal model="notifSheet" title="Notifikasi ({{ $unreadNotifCount }})" subtitle="Pemberitahuan terbaru terkait presensi, izin, dan kinerja">
        <div class="space-y-2">
            @forelse ($recentNotifications as $notif)
                <div wire:click="markNotificationAsRead('{{ $notif->id }}')"
                    class="p-3 rounded-2xl border cursor-pointer transition-colors {{ is_null($notif->read_at) ? 'bg-sky-50/80 dark:bg-sky-500/10 border-sky-200 dark:border-sky-500/30' : 'bg-zinc-50 dark:bg-zinc-950/60 border-zinc-200/70 dark:border-zinc-800' }}">
                    <div class="text-xs font-bold text-zinc-900 dark:text-white">{{ $notif->data['title'] ?? 'Pemberitahuan' }}</div>
                    <p class="text-[11px] text-zinc-600 dark:text-zinc-400 mt-0.5">{{ $notif->data['body'] ?? ($notif->data['message'] ?? '') }}</p>
                    <div class="text-[9px] text-zinc-400 mt-1">{{ $notif->created_at->diffForHumans() }}</div>
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
