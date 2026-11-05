<x-filament-panels::page>
    @php
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $isMasuk = $todayAbsensi && filled($todayAbsensi->jam_masuk);
        $isPulang = $todayAbsensi && filled($todayAbsensi->jam_pulang);
        $todayStatusVal = $todayAbsensi?->status instanceof \App\Enums\StatusKehadiran ? $todayAbsensi->status->value : (string) ($todayAbsensi?->status ?? '');
        $isTerlambat = $todayStatusVal === 'terlambat';
        $items = $recentAbsensi ?? (isset($this->recentAbsensi) ? $this->recentAbsensi : []);
    @endphp

    <div class="space-y-6">
        {{-- Card Header: Jam Digital & Informasi Pegawai --}}
        <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-white/10 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                {{-- Info Pegawai --}}
                <div class="flex items-center gap-4">
                    <x-filament-panels::avatar.user :user="$user" class="w-16 h-16 rounded-full ring-4 ring-primary-500/20" />
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ $user->name }}
                            </h2>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-100 text-primary-800 dark:bg-primary-500/20 dark:text-primary-400">
                                Portal Web / Laptop
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                            NIP: {{ $user->nip ?? '-' }} &bull; {{ $user->bidang->nama ?? 'Pimpinan / Tanpa Bidang' }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1.5">
                            <x-heroicon-m-clock class="w-4 h-4 text-amber-500" />
                            <span>Shift: <strong>{{ $jadwal->nama ?? 'Jadwal Default' }}</strong> (Masuk: {{ $jamMasukResmi }} WIB &bull; Pulang: {{ $jamPulangResmi }} WIB)</span>
                        </p>
                    </div>
                </div>

                {{-- Jam Digital Server --}}
                <div class="flex flex-col items-start md:items-end justify-center bg-gray-50 dark:bg-white/5 px-6 py-3.5 rounded-xl border border-gray-200/60 dark:border-white/5">
                    <span class="text-xs font-medium text-gray-400 uppercase tracking-wider">
                        {{ $todayDate }}
                    </span>
                    <div
                        x-data="{ time: '' }"
                        x-init="
                            const update = () => {
                                const now = new Date();
                                time = now.toLocaleTimeString('id-ID', { timeZone: 'Asia/Jakarta', hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
                            };
                            update();
                            setInterval(update, 1000);
                        "
                        class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white font-mono tracking-tight"
                        x-text="time"
                    ></div>
                </div>
            </div>
        </div>

        {{-- Grid 2 Kolom: Tombol Absen Masuk & Pulang --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Kolom 1: Absen Masuk --}}
            <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-white/10 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                <x-heroicon-o-arrow-right-end-on-rectangle class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 dark:text-white">Presensi Masuk Pagi</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Buka: {{ $bukaMasuk }} - Tutup: {{ $tutupMasuk }} WIB</p>
                            </div>
                        </div>
                        @if ($isMasuk)
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold {{ $isTerlambat ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-400' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400' }}">
                                <x-heroicon-m-check-circle class="w-4 h-4" />
                                {{ $isTerlambat ? 'Terlambat' : 'Tepat Waktu' }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300">
                                Belum Absen
                            </span>
                        @endif
                    </div>

                    @if ($isMasuk)
                        <div class="p-4 rounded-xl bg-emerald-50/50 dark:bg-emerald-500/5 border border-emerald-200 dark:border-emerald-500/20 text-center my-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Jam Masuk Tercatat:</p>
                            <p class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 font-mono mt-1">
                                {{ substr($todayAbsensi->jam_masuk, 0, 5) }} WIB
                            </p>
                        </div>
                    @else
                        <p class="text-xs text-gray-600 dark:text-gray-400 my-4 leading-relaxed">
                            Pastikan Anda sudah berada di jam kerja dinas. Klik tombol di bawah untuk mencatat kehadiran masuk pagi dari laptop Anda.
                        </p>
                    @endif
                </div>

                <div>
                    @if ($isMasuk)
                        <button
                            type="button"
                            disabled
                            class="w-full py-3 px-4 rounded-xl text-sm font-semibold bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-gray-500 cursor-not-allowed text-center"
                        >
                            ✓ Sudah Mengambil Absen Masuk
                        </button>
                    @else
                        <button
                            type="button"
                            wire:click="punchMasuk"
                            wire:loading.attr="disabled"
                            class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/20 transition-all cursor-pointer"
                        >
                            <x-heroicon-m-check class="w-5 h-5" />
                            <span wire:loading.remove wire:target="punchMasuk">Ambil Absen Masuk Sekarang</span>
                            <span wire:loading wire:target="punchMasuk">Merekam Kehadiran...</span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Kolom 2: Absen Pulang --}}
            <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-white/10 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                <x-heroicon-o-arrow-left-start-on-rectangle class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-900 dark:text-white">Presensi Pulang Sore</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Mulai Dibuka: Pukul {{ $jamPulangResmi }} WIB</p>
                            </div>
                        </div>
                        @if ($isPulang)
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-500/20 dark:text-blue-400">
                                <x-heroicon-m-check-circle class="w-4 h-4" />
                                Sudah Pulang
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300">
                                Belum Pulang
                            </span>
                        @endif
                    </div>

                    @if ($isPulang)
                        <div class="p-4 rounded-xl bg-blue-50/50 dark:bg-blue-500/5 border border-blue-200 dark:border-blue-500/20 text-center my-4">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Jam Pulang Tercatat:</p>
                            <p class="text-3xl font-extrabold text-blue-600 dark:text-blue-400 font-mono mt-1">
                                {{ substr($todayAbsensi->jam_pulang, 0, 5) }} WIB
                            </p>
                        </div>
                    @else
                        <p class="text-xs text-gray-600 dark:text-gray-400 my-4 leading-relaxed">
                            Absen pulang dapat diambil setelah menyelesaikan jam kerja dinas resmi hari ini (pukul {{ $jamPulangResmi }} WIB).
                        </p>
                    @endif
                </div>

                <div>
                    @if (!$isMasuk)
                        <button
                            type="button"
                            disabled
                            class="w-full py-3 px-4 rounded-xl text-sm font-semibold bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-gray-500 cursor-not-allowed text-center"
                        >
                            Menunggu Absen Masuk Terlebih Dahulu
                        </button>
                    @elseif ($isPulang)
                        <button
                            type="button"
                            disabled
                            class="w-full py-3 px-4 rounded-xl text-sm font-semibold bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-gray-500 cursor-not-allowed text-center"
                        >
                            ✓ Sudah Mengambil Absen Pulang
                        </button>
                    @else
                        <button
                            type="button"
                            wire:click="punchPulang"
                            wire:loading.attr="disabled"
                            class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/20 transition-all cursor-pointer"
                        >
                            <x-heroicon-m-arrow-left-on-rectangle class="w-5 h-5" />
                            <span wire:loading.remove wire:target="punchPulang">Ambil Absen Pulang Sekarang</span>
                            <span wire:loading wire:target="punchPulang">Merekam Kehadiran Pulang...</span>
                        </button>
                    @endif
                </div>
            </div>

        </div>

        {{-- Tabel Riwayat 7 Hari Terakhir --}}
        <div class="p-6 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-white/10 shadow-sm">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2 text-base">
                <x-heroicon-o-calendar-days class="w-5 h-5 text-primary-500" />
                <span>Riwayat Kehadiran Anda (7 Hari Terakhir)</span>
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-white/5 text-gray-400 text-xs uppercase">
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Jam Masuk</th>
                            <th class="py-3 px-4">Jam Pulang</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Perangkat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse ($items as $item)
                            @php
                                $statusVal = $item->status instanceof \App\Enums\StatusKehadiran ? $item->status->value : (string) ($item->status ?? '');
                                $statusLabel = $item->status instanceof \App\Enums\StatusKehadiran ? $item->status->getLabel() : ucfirst($statusVal ?: 'Belum Hadir');
                            @endphp
                            <tr class="text-gray-700 dark:text-gray-300">
                                <td class="py-3.5 px-4 font-medium">
                                    {{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->isoFormat('dddd, D MMMM Y') }}
                                </td>
                                <td class="py-3.5 px-4 font-mono">
                                    {{ $item->jam_masuk ? substr($item->jam_masuk, 0, 5) . ' WIB' : '-' }}
                                </td>
                                <td class="py-3.5 px-4 font-mono">
                                    {{ $item->jam_pulang ? substr($item->jam_pulang, 0, 5) . ' WIB' : '-' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        @if ($statusVal === 'hadir') bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400
                                        @elseif ($statusVal === 'terlambat') bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-400
                                        @elseif ($statusVal === 'tidak_hadir') bg-red-100 text-red-800 dark:bg-red-500/20 dark:text-red-400
                                        @else bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-gray-300 @endif
                                    ">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-gray-500">
                                    {{ $item->is_absen_mobile ? '📱 Mobile GPS' : '💻 Portal Web' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-400 text-xs">
                                    Belum ada riwayat kehadiran tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>

