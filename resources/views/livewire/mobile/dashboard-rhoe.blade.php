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

        <!-- Row 3: 1 Smart Context-Aware Primary Button -->
        @if ($aksiUtama === 'masuk')
            <button type="button" @click="doPunch('masuk')" :disabled="punching"
                class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white font-extrabold text-xs shadow-lg shadow-emerald-600/20 flex items-center justify-center gap-2 active:scale-[0.98] transition-all cursor-pointer disabled:opacity-60">
                <i x-show="!punching" class="fa-solid fa-fingerprint text-sm"></i>
                <i x-show="punching" x-cloak class="fa-solid fa-circle-notch fa-spin text-sm"></i>
                <span x-text="punching ? 'Mengambil Lokasi GPS...' : 'Tap Absen Masuk Sekarang'"></span>
            </button>
        @elseif ($aksiUtama === 'pulang')
            <button type="button" @click="doPunch('pulang')" :disabled="punching"
                class="w-full py-3.5 px-4 rounded-2xl bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-500 hover:to-blue-500 text-white font-extrabold text-xs shadow-lg shadow-sky-600/20 flex items-center justify-center gap-2 active:scale-[0.98] transition-all cursor-pointer disabled:opacity-60">
                <i x-show="!punching" class="fa-solid fa-right-from-bracket text-sm"></i>
                <i x-show="punching" x-cloak class="fa-solid fa-circle-notch fa-spin text-sm"></i>
                <span x-text="punching ? 'Mengambil Lokasi GPS...' : 'Tap Absen Pulang Sekarang'"></span>
            </button>
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

    <!-- 3. 7-DAY ATTENDANCE TIMELINE -->
    @if (! empty($riwayat7Hari))
        <section class="rounded-2xl bg-white dark:bg-zinc-900/90 border border-zinc-200/90 dark:border-zinc-800 p-4 shadow-xs">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-xs font-extrabold text-zinc-900 dark:text-white">Rekap 7 Hari Terakhir</h2>
                <a href="{{ route('mobile.riwayat') }}" wire:navigate class="text-[11px] font-bold text-sky-600 dark:text-sky-400 hover:underline">
                    Detail &rarr;
                </a>
            </div>
            <div class="grid grid-cols-7 gap-1.5">
                @foreach ($riwayat7Hari as $item)
                    @php
                        $rawStatus = $item['status'] ?? null;
                        $st = $rawStatus instanceof \BackedEnum ? (string) $rawStatus->value : (string) ($rawStatus ?? '');
                        $stLabel = $rawStatus instanceof \Filament\Support\Contracts\HasLabel
                            ? (string) $rawStatus->getLabel()
                            : ($st !== '' ? ucfirst(str_replace('_', ' ', $st)) : 'Belum ada');
                        $pillClass = match ($st) {
                            'hadir' => 'bg-emerald-500 text-white shadow-emerald-500/20',
                            'terlambat' => 'bg-amber-500 text-white shadow-amber-500/20',
                            'izin', 'cuti', 'dinas_luar' => 'bg-sky-500 text-white shadow-sky-500/20',
                            default => 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400',
                        };
                    @endphp
                    <div title="{{ ($item['hari'] ?? '') . ' ' . ($item['tgl'] ?? '') . ' — ' . $stLabel }}"
                        class="flex flex-col items-center gap-1">
                        <span class="text-[9px] font-bold text-zinc-400 uppercase">{{ $item['hari'] ?? '' }}</span>
                        <div class="w-8 h-8 rounded-xl {{ $pillClass }} flex items-center justify-center text-[11px] font-extrabold shadow-xs">
                            {{ $item['tgl'] ?? '-' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <!-- 4. QUICK ACTIONS (Ringkas, Tanpa Duplikasi Bottom Nav) -->
    <section class="grid grid-cols-2 gap-3">
        <a href="{{ route('mobile.lhk.index') }}" wire:navigate
            class="rounded-2xl bg-white dark:bg-zinc-900/90 border border-zinc-200/90 dark:border-zinc-800 p-3.5 flex items-center gap-3 active:scale-[0.98] transition-transform shadow-xs">
            <div class="w-9 h-9 rounded-xl bg-violet-500/15 text-violet-600 dark:text-violet-400 flex items-center justify-center text-sm shrink-0">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div class="min-w-0">
                <div class="text-xs font-extrabold text-zinc-900 dark:text-white truncate">Isi LHK</div>
                <div class="text-[10px] text-zinc-500 dark:text-zinc-400 truncate">Laporan harian</div>
            </div>
        </a>

        <a href="{{ route('mobile.izin') }}" wire:navigate
            class="rounded-2xl bg-white dark:bg-zinc-900/90 border border-zinc-200/90 dark:border-zinc-800 p-3.5 flex items-center gap-3 active:scale-[0.98] transition-transform shadow-xs">
            <div class="w-9 h-9 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm shrink-0">
                <i class="fa-solid fa-envelope-open-text"></i>
            </div>
            <div class="min-w-0">
                <div class="text-xs font-extrabold text-zinc-900 dark:text-white truncate">Izin &amp; Cuti</div>
                <div class="text-[10px] text-zinc-500 dark:text-zinc-400 truncate">Ajukan cepat</div>
            </div>
        </a>
    </section>

    <!-- Rhoe UI Modal: Notifikasi -->
    <x-mobile.rhoe-modal model="notifSheet" title="Notifikasi ({{ $unreadNotifCount }})" subtitle="Pemberitahuan terbaru terkait presensi, izin &amp; kinerja">
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
                    class="px-3.5 py-2 rounded-xl bg-sky-500 hover:bg-sky-400 text-white text-xs font-bold transition-colors cursor-pointer">
                    Tandai Semua Dibaca
                </button>
            @endif
            <button type="button" @click="notifSheet = false"
                class="px-3.5 py-2 rounded-xl bg-zinc-200/80 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-200 text-xs font-bold cursor-pointer">
                Tutup
            </button>
        </x-slot:footer>
    </x-mobile.rhoe-modal>
</div>
