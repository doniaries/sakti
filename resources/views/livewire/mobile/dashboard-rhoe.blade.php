<div x-data="{
        notifSheet: false,
        activeTooltip: null,
        isDark: document.documentElement.classList.contains('dark'),
        jam: '--:--',
        detik: '--',
        punching: null,
        init() {
            const tick = () => {
                const d = new Date();
                this.jam = String(d.getHours()).padStart(2, '0') + ':' + String(d.getMinutes()).padStart(2, '0');
                this.detik = String(d.getSeconds()).padStart(2, '0');
            };
            tick();
            setInterval(tick, 1000);
        },
        toggleTheme() {
            if (window.toggleMobileTheme) {
                window.toggleMobileTheme();
                this.isDark = document.documentElement.classList.contains('dark');
            }
        },
        doPunch(jenis) {
            this.punching = jenis;
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        $wire.ambilAbsen(jenis, pos.coords.latitude, pos.coords.longitude).finally(() => this.punching = null);
                    },
                    () => {
                        $wire.ambilAbsen(jenis, null, null).finally(() => this.punching = null);
                    },
                    { enableHighAccuracy: true, timeout: 8000 }
                );
            } else {
                $wire.ambilAbsen(jenis, null, null).finally(() => this.punching = null);
            }
        }
    }"
    @open-notif-sheet.window="notifSheet = true"
    class="min-h-screen pb-28 bg-transparent text-zinc-900 dark:text-zinc-100 relative overflow-x-hidden transition-colors duration-300">

    <!-- Subtle Rhoe Dot Grid -->
    <div class="pointer-events-none fixed inset-0 opacity-40 dark:opacity-70"
        style="background-image: radial-gradient(rgba(148, 163, 184, 0.22) 1px, transparent 1px); background-size: 22px 22px; mask-image: radial-gradient(ellipse 90% 50% at 50% 0%, #000 45%, transparent 100%);">
    </div>

    <main class="relative z-10 px-4 pt-4 space-y-4">
        <!-- 1. Avatar Info Card (Rhoe UI Pattern) -->
        <section class="rounded-3xl bg-white dark:bg-zinc-900/85 border border-zinc-200/90 dark:border-zinc-800 p-4 shadow-sm flex items-center gap-3.5">
            <label class="relative shrink-0 cursor-pointer group" title="Ketuk untuk ganti foto profil">
                <input type="file" wire:model="new_avatar" accept="image/*" class="hidden">
                <img src="{{ $user->avatar_image_url }}" alt="{{ $user->name }}"
                    class="w-14 h-14 rounded-2xl object-cover border-2 border-sky-500/30 shadow-md group-hover:opacity-80 transition-opacity">
                <span wire:loading.remove wire:target="new_avatar"
                    class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 border-2 border-white dark:border-zinc-900 flex items-center justify-center">
                    <i class="fa-solid fa-camera text-[8px] text-white"></i>
                </span>
                <!-- Loading Indicator saat upload avatar -->
                <span wire:loading wire:target="new_avatar"
                    class="absolute inset-0 rounded-2xl bg-black/50 flex items-center justify-center">
                    <i class="fa-solid fa-circle-notch fa-spin text-white text-sm"></i>
                </span>
            </label>

            <div class="min-w-0 flex-1">
                <h1 class="text-sm font-black tracking-tight text-zinc-900 dark:text-white truncate">
                    {{ $user->name }}
                </h1>
                <p class="text-[11px] font-mono text-zinc-500 dark:text-zinc-400 truncate">
                    NIP. {{ $user->nip ?: '-' }}
                </p>
                <div class="flex flex-wrap items-center gap-1.5 mt-1.5">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-sky-500/10 border border-sky-500/20 text-sky-600 dark:text-sky-400 text-[10px] font-bold">
                        <i class="fa-solid fa-briefcase text-[9px]"></i>
                        <span>{{ $user->jabatan?->nama ?? 'Pegawai' }}</span>
                    </span>
                    @if ($user->bidang?->nama)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 text-[10px] font-semibold">
                            {{ $user->bidang->nama }}
                        </span>
                    @endif
                </div>
            </div>
        </section>

        <!-- Holiday Banner (Feedback Alert) -->
        @if ($hariLiburHariIni)
            <div class="rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 p-3.5 flex items-center gap-3 text-xs text-amber-800 dark:text-amber-300">
                <i class="fa-solid fa-calendar-day text-amber-500 text-base"></i>
                <div>
                    <div class="font-extrabold">Hari Libur: {{ $hariLiburHariIni->nama }}</div>
                    <div class="text-[11px] opacity-85">Presensi reguler tidak diwajibkan hari ini.</div>
                </div>
            </div>
        @endif

        <!-- 2. Live Clock & Mode Kerja Card -->
        <section class="rounded-3xl bg-white dark:bg-zinc-900/85 border border-zinc-200/90 dark:border-zinc-800 p-5 text-center shadow-sm">
            <div class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 mb-1">
                {{ now()->translatedFormat('l, d F Y') }}
            </div>
            <div class="flex items-baseline justify-center gap-1.5 font-mono">
                <span x-text="jam" class="text-4xl font-black tracking-tight text-emerald-600 dark:text-emerald-400">--:--</span>
                <span x-text="':' + detik" class="text-lg font-bold text-zinc-400"></span>
                <span class="text-xs font-extrabold text-emerald-600 dark:text-emerald-400 ml-1">WIB</span>
            </div>
            <div class="mt-2.5 flex flex-wrap items-center justify-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/25 text-emerald-700 dark:text-emerald-400 text-[11px] font-bold">
                    <i class="fa-solid fa-location-dot text-[10px]"></i>
                    <span>Mode Kerja: {{ strtoupper($user->status_kerja ?? 'WFO') === 'WFH' ? 'WFH (Fleksibel)' : 'WFO (Radius Kantor)' }}</span>
                </span>
            </div>
        </section>

        <!-- 3. Action Buttons: Absen Masuk & Pulang + Loading Indicator + Tooltip -->
        <section class="grid grid-cols-2 gap-3">
            <!-- Absen Masuk -->
            <button type="button"
                @click="doPunch('masuk')"
                :disabled="punching !== null || {{ $hariIni['sudah_masuk'] ? 'true' : 'false' }}"
                title="Jam masuk: {{ $hariIni['buka_masuk'] ?? '06:30' }} - {{ $hariIni['tutup_masuk'] ?? '12:00' }}"
                class="relative rounded-2xl p-4 text-left transition-all active:scale-[0.97] shadow-md cursor-pointer disabled:cursor-not-allowed
                    {{ $hariIni['sudah_masuk']
                        ? 'bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-300 dark:border-emerald-500/30 text-emerald-800 dark:text-emerald-300'
                        : 'bg-emerald-600 hover:bg-emerald-500 text-white shadow-emerald-600/25' }}">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 rounded-xl {{ $hariIni['sudah_masuk'] ? 'bg-emerald-500/20 text-emerald-600 dark:text-emerald-300' : 'bg-white/20 text-white' }} flex items-center justify-center text-sm">
                        <i x-show="punching !== 'masuk'" class="fa-solid {{ $hariIni['sudah_masuk'] ? 'fa-circle-check' : 'fa-right-to-bracket' }}"></i>
                        <i x-show="punching === 'masuk'" x-cloak class="fa-solid fa-circle-notch fa-spin"></i>
                    </div>
                    <span class="text-[9px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full {{ $hariIni['sudah_masuk'] ? 'bg-emerald-500/20' : 'bg-white/20' }}">
                        {{ $hariIni['sudah_masuk'] ? 'Tercatat' : 'Masuk' }}
                    </span>
                </div>
                <div class="text-xs font-black tracking-tight">
                    <span x-show="punching !== 'masuk'">{{ $hariIni['sudah_masuk'] ? 'SUDAH MASUK' : 'ABSEN MASUK' }}</span>
                    <span x-show="punching === 'masuk'" x-cloak>MENGIRIM GPS...</span>
                </div>
                <div class="text-[10px] opacity-85 mt-0.5 font-mono">
                    {{ $hariIni['jam_masuk'] ? \Carbon\Carbon::parse($hariIni['jam_masuk'])->format('H:i') . ' WIB' : 'Tap untuk presensi' }}
                </div>
            </button>

            <!-- Absen Pulang -->
            <button type="button"
                @click="doPunch('pulang')"
                :disabled="punching !== null || {{ (! $hariIni['sudah_masuk'] || $hariIni['sudah_pulang']) ? 'true' : 'false' }}"
                title="Jam pulang resmi: {{ $hariIni['jam_pulang_resmi'] ?? '16:00' }}"
                class="relative rounded-2xl p-4 text-left transition-all active:scale-[0.97] shadow-sm cursor-pointer disabled:cursor-not-allowed
                    {{ $hariIni['sudah_pulang']
                        ? 'bg-sky-50 dark:bg-sky-500/15 border border-sky-300 dark:border-sky-500/30 text-sky-800 dark:text-sky-300'
                        : ($hariIni['sudah_masuk']
                            ? 'bg-sky-600 hover:bg-sky-500 text-white shadow-sky-600/25'
                            : 'bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-400') }}">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-8 h-8 rounded-xl {{ $hariIni['sudah_pulang'] ? 'bg-sky-500/20 text-sky-600 dark:text-sky-300' : ($hariIni['sudah_masuk'] ? 'bg-white/20 text-white' : 'bg-zinc-200 dark:bg-zinc-800 text-zinc-400') }} flex items-center justify-center text-sm">
                        <i x-show="punching !== 'pulang'" class="fa-solid {{ $hariIni['sudah_pulang'] ? 'fa-circle-check' : ($hariIni['sudah_masuk'] ? 'fa-right-from-bracket' : 'fa-lock') }}"></i>
                        <i x-show="punching === 'pulang'" x-cloak class="fa-solid fa-circle-notch fa-spin"></i>
                    </div>
                    <span class="text-[9px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-full {{ $hariIni['sudah_pulang'] ? 'bg-sky-500/20' : ($hariIni['sudah_masuk'] ? 'bg-white/20' : 'bg-zinc-200 dark:bg-zinc-800') }}">
                        {{ $hariIni['sudah_pulang'] ? 'Selesai' : 'Pulang' }}
                    </span>
                </div>
                <div class="text-xs font-black tracking-tight">
                    <span x-show="punching !== 'pulang'">{{ $hariIni['sudah_pulang'] ? 'SUDAH PULANG' : 'ABSEN PULANG' }}</span>
                    <span x-show="punching === 'pulang'" x-cloak>MENGIRIM GPS...</span>
                </div>
                <div class="text-[10px] opacity-85 mt-0.5 font-mono">
                    {{ $hariIni['jam_pulang'] ? \Carbon\Carbon::parse($hariIni['jam_pulang'])->format('H:i') . ' WIB' : ($hariIni['sudah_masuk'] ? 'Tap untuk pulang' : 'Menunggu Masuk') }}
                </div>
            </button>
        </section>

        <!-- 4. Jam Kerja Efektif ASN (Bento Progress Card + Badge) -->
        <section class="rounded-3xl bg-white dark:bg-zinc-900/85 border border-zinc-200/90 dark:border-zinc-800 p-4 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-sky-500/15 text-sky-600 dark:text-sky-400 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <h2 class="text-xs font-extrabold text-zinc-900 dark:text-white">Jam Kerja Efektif ASN</h2>
                        <p class="text-[10px] text-zinc-500 dark:text-zinc-400">Standar {{ $jamKerjaAsn['target_harian_format'] }}/hari &bull; {{ $jamKerjaAsn['target_mingguan_format'] }}/minggu</p>
                    </div>
                </div>
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/25 text-emerald-700 dark:text-emerald-400 text-[11px] font-black">
                    {{ $jamKerjaAsn['persen_bulanan'] }}%
                </span>
            </div>

            <div class="grid grid-cols-2 gap-2.5">
                <div class="rounded-2xl bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/70 dark:border-zinc-800 p-3">
                    <div class="text-[10px] text-zinc-500 dark:text-zinc-400 font-semibold">Hari Ini</div>
                    <div class="text-xs font-black font-mono mt-0.5">
                        {{ $jamKerjaAsn['harian_format'] }} <span class="text-[10px] font-normal text-zinc-400">/ {{ $jamKerjaAsn['target_harian_format'] }}</span>
                    </div>
                    <div class="w-full h-1.5 rounded-full bg-zinc-200 dark:bg-zinc-800 mt-2 overflow-hidden">
                        <div class="h-full rounded-full bg-sky-500 transition-all duration-500" style="width: {{ $jamKerjaAsn['persen_harian'] }}%"></div>
                    </div>
                </div>

                <div class="rounded-2xl bg-zinc-50 dark:bg-zinc-950/60 border border-zinc-200/70 dark:border-zinc-800 p-3">
                    <div class="text-[10px] text-zinc-500 dark:text-zinc-400 font-semibold">Bulan Ini</div>
                    <div class="text-xs font-black font-mono text-sky-600 dark:text-sky-400 mt-0.5">
                        {{ $jamKerjaAsn['bulanan_format'] }} <span class="text-[10px] font-normal text-zinc-400">/ {{ $jamKerjaAsn['target_bulanan_format'] }}</span>
                    </div>
                    <div class="w-full h-1.5 rounded-full bg-zinc-200 dark:bg-zinc-800 mt-2 overflow-hidden">
                        <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ $jamKerjaAsn['persen_bulanan'] }}%"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 5. 7-Day Mini Status Strip with Tooltips -->
        @if (! empty($riwayat7Hari))
            <section class="rounded-3xl bg-white dark:bg-zinc-900/85 border border-zinc-200/90 dark:border-zinc-800 p-4 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-xs font-extrabold">Kehadiran 7 Hari Terakhir</h2>
                    <a href="{{ route('mobile.riwayat') }}" class="text-[11px] font-bold text-sky-600 dark:text-sky-400 hover:underline">Lihat Semua</a>
                </div>
                <div class="grid grid-cols-7 gap-1.5">
                    @foreach ($riwayat7Hari as $item)
                        @php
                            $rawStatus = $item['status'] ?? null;
                            $st = $rawStatus instanceof \BackedEnum ? (string) $rawStatus->value : (string) ($rawStatus ?? '');
                            $stLabel = $rawStatus instanceof \Filament\Support\Contracts\HasLabel
                                ? (string) $rawStatus->getLabel()
                                : ($st !== '' ? ucfirst(str_replace('_', ' ', $st)) : 'Belum ada');
                            $dotClass = match ($st) {
                                'hadir' => 'bg-emerald-500 text-white',
                                'terlambat' => 'bg-amber-500 text-white',
                                'izin', 'cuti', 'dinas_luar' => 'bg-sky-500 text-white',
                                default => 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400',
                            };
                        @endphp
                        <div title="{{ ($item['hari'] ?? '') . ' ' . ($item['tgl'] ?? '') . ' — ' . $stLabel }}"
                            class="flex flex-col items-center gap-1">
                            <span class="text-[9px] font-bold text-zinc-400 uppercase">{{ $item['hari'] ?? '' }}</span>
                            <div class="w-8 h-8 rounded-xl {{ $dotClass }} flex items-center justify-center text-[11px] font-extrabold shadow-xs">
                                {{ $item['tgl'] ?? '-' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </main>

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
