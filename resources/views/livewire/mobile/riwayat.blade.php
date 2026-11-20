<div class="p-5 pb-24">
    <!-- Hero Biru Gradient Melengkung + Month Navigator Mengambang -->
    <section class="-mx-5 -mt-5 mb-4">
        <div
            class="rounded-b-[28px] bg-gradient-to-br from-[#0F172A] via-[#0369A1] to-[#0284C7] px-5 pt-5 pb-12 text-white shadow-lg shadow-sky-950/20">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight">Riwayat Absensi</h1>
                    <p class="text-xs text-sky-100 font-medium">Rekapitulasi kehadiran pegawai</p>
                </div>
                <a href="{{ route('mobile.riwayat.cetak-pdf', ['bulan' => $bulan, 'tahun' => $tahun, 'status' => $filterStatus]) }}"
                    target="_blank"
                    class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-rose-500 to-red-600 hover:from-rose-600 hover:to-red-700 text-white font-bold text-xs flex items-center shadow-md shadow-red-500/25 active:scale-95 transition-all">
                    <i class="fa-solid fa-file-pdf mr-1.5 text-sm"></i>
                    <span>Cetak PDF</span>
                </a>
            </div>
        </div>

        <!-- Month Navigator -->
        <div
            class="relative z-10 -mt-7 mx-5 flex items-center justify-between bg-white px-4 py-2.5 rounded-2xl shadow-xl shadow-slate-900/10 ring-1 ring-slate-900/5 border border-slate-200/80">
            <button wire:click="prevMonth"
                class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-600 hover:text-sky-600 flex items-center justify-center transition-colors">
                <i class="fa-solid fa-chevron-left text-xs"></i>
            </button>
            <span class="text-sm font-bold text-slate-800 tracking-wide">{{ $bulanNama }}</span>
            <button wire:click="nextMonth"
                class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-sky-50 text-slate-600 hover:text-sky-600 flex items-center justify-center transition-colors">
                <i class="fa-solid fa-chevron-right text-xs"></i>
            </button>
        </div>
    </section>

    <!-- Card Hitung Jumlah Kehadiran (Putih Bersih, Selaras Beranda) -->
    <div class="bg-white text-slate-800 p-4 rounded-2xl shadow-md shadow-slate-900/5 mb-5 border border-slate-200/80">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center space-x-2">
                <div class="w-7 h-7 rounded-lg bg-sky-50 flex items-center justify-center text-xs text-sky-600">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
                <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Rekapitulasi
                    Kehadiran</span>
            </div>
            <span
                class="text-xs font-extrabold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200/80">
                Total: {{ $rekap['total'] }} Hari
            </span>
        </div>

        <div class="grid grid-cols-3 gap-1.5 text-center">
            <div class="bg-emerald-50/70 rounded-xl py-1.5 px-1 border border-emerald-200/60">
                <span class="text-base font-black text-emerald-600 block leading-tight">{{ $rekap['hadir'] }}</span>
                <span class="text-[9px] font-bold text-emerald-700 uppercase tracking-tight">Hadir</span>
            </div>
            <div class="bg-amber-50/70 rounded-xl py-1.5 px-1 border border-amber-200/60">
                <span class="text-base font-black text-amber-600 block leading-tight">{{ $rekap['terlambat'] }}</span>
                <span class="text-[9px] font-bold text-amber-700 uppercase tracking-tight">Telat</span>
            </div>
            <div class="bg-sky-50/70 rounded-xl py-1.5 px-1 border border-sky-200/60">
                <span class="text-base font-black text-sky-600 block leading-tight">{{ $rekap['dinas_luar'] }}</span>
                <span class="text-[9px] font-bold text-sky-700 uppercase tracking-tight">DL</span>
            </div>
            <div class="bg-indigo-50/70 rounded-xl py-1.5 px-1 border border-indigo-200/60">
                <span class="text-base font-black text-indigo-600 block leading-tight">{{ $rekap['izin_sakit'] }}</span>
                <span class="text-[9px] font-bold text-indigo-700 uppercase tracking-tight">Izin</span>
            </div>
            <div class="bg-violet-50/70 rounded-xl py-1.5 px-1 border border-violet-200/60">
                <span class="text-base font-black text-violet-600 block leading-tight">{{ $rekap['cuti'] }}</span>
                <span class="text-[9px] font-bold text-violet-700 uppercase tracking-tight">Cuti</span>
            </div>
            <div class="bg-rose-50/70 rounded-xl py-1.5 px-1 border border-rose-200/60">
                <span class="text-base font-black text-rose-600 block leading-tight">{{ $rekap['alpa'] }}</span>
                <span class="text-[9px] font-bold text-rose-700 uppercase tracking-tight">Alpa</span>
            </div>
        </div>

        <!-- Rekap Jam Kerja ASN Bulanan (7j 30m/hari • 37j 30m/minggu) -->
        <div class="mt-3 pt-3 border-t border-slate-100">
            <div class="flex items-center justify-between text-xs mb-1.5">
                <span class="text-slate-600 font-bold flex items-center gap-1.5">
                    <i class="fa-solid fa-business-time text-sky-500"></i>
                    Jam Kerja Bulan Ini
                </span>
                <span class="font-mono font-extrabold text-slate-900">
                    {{ $jamKerjaBulanan['total_format'] }}
                    <span class="text-[10px] font-normal text-slate-400">/ {{ $jamKerjaBulanan['target_format'] }}
                        ({{ $jamKerjaBulanan['persen'] }}%)</span>
                </span>
            </div>
            <div class="w-full h-1.5 rounded-full bg-slate-100 overflow-hidden mb-1">
                <div class="h-full rounded-full bg-gradient-to-r from-sky-500 to-emerald-500 transition-all duration-500"
                    style="width: {{ $jamKerjaBulanan['persen'] }}%"></div>
            </div>
            <p class="text-[10px] text-slate-400">
                Ketentuan ASN: {{ $jamKerjaBulanan['target_harian_format'] }}/hari &bull;
                {{ $jamKerjaBulanan['target_mingguan_format'] }}/minggu ({{ $jamKerjaBulanan['hari_kerja'] }} hari
                kerja, non-istirahat)
            </p>
        </div>
    </div>

    <!-- Filter Status Tabs (Semua, Hadir, Terlambat, DL, Izin/Sakit, Alpa) -->
    <div class="flex space-x-2 overflow-x-auto pb-2 mb-4 no-scrollbar">
        <button wire:click="setFilter('semua')"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all shadow-md shadow-slate-900/5 {{ $filterStatus === 'semua' ? 'bg-sky-600 text-white shadow-sky-600/30' : 'bg-white text-slate-600 border border-slate-200' }}">
            Semua
        </button>
        <button wire:click="setFilter('hadir')"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all shadow-md shadow-slate-900/5 {{ $filterStatus === 'hadir' ? 'bg-emerald-600 text-white shadow-emerald-600/30' : 'bg-white text-slate-600 border border-slate-200' }}">
            Hadir ({{ $rekap['hadir'] }})
        </button>
        <button wire:click="setFilter('terlambat')"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all shadow-md shadow-slate-900/5 {{ $filterStatus === 'terlambat' ? 'bg-amber-500 text-white shadow-amber-500/30' : 'bg-white text-slate-600 border border-slate-200' }}">
            Terlambat ({{ $rekap['terlambat'] }})
        </button>
        <button wire:click="setFilter('dinas_luar')"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all shadow-md shadow-slate-900/5 {{ $filterStatus === 'dinas_luar' ? 'bg-sky-500 text-white shadow-sky-500/30' : 'bg-white text-slate-600 border border-slate-200' }}">
            Dinas Luar ({{ $rekap['dinas_luar'] }})
        </button>
        <button wire:click="setFilter('izin_sakit')"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all shadow-md shadow-slate-900/5 {{ $filterStatus === 'izin_sakit' ? 'bg-indigo-600 text-white shadow-indigo-600/30' : 'bg-white text-slate-600 border border-slate-200' }}">
            Izin & Sakit ({{ $rekap['izin_sakit'] }})
        </button>
        <button wire:click="setFilter('tidak_hadir')"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all shadow-md shadow-slate-900/5 {{ $filterStatus === 'tidak_hadir' ? 'bg-rose-600 text-white shadow-rose-600/30' : 'bg-white text-slate-600 border border-slate-200' }}">
            Alpa ({{ $rekap['alpa'] }})
        </button>
    </div>

    <!-- Riwayat List Items -->
    @if ($riwayat->isEmpty())
        <div
            class="bg-white rounded-3xl p-8 text-center border border-slate-100 shadow-md shadow-slate-900/5 flex flex-col items-center">
            <div
                class="w-16 h-16 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center text-3xl mb-3">
                <i class="fa-solid fa-calendar-xmark"></i>
            </div>
            <h4 class="font-bold text-slate-800 text-sm">Tidak Ada Data Absensi</h4>
            <p class="text-xs text-slate-400 mt-1">Tidak ditemukan log kehadiran pada filter & periode ini.</p>
        </div>
    @else
        <div class="space-y-3">
            <!-- Filter Tanggal -->
            <div class="flex items-center gap-2 mb-3">
                <div class="relative flex-1">
                    <x-mobile.date-picker wire:model.live="filterTanggal" placeholder="Pilih Tanggal..." clearable
                        buttonClass="w-full rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold shadow-md shadow-slate-900/5 transition {{ $filterTanggal ? 'text-slate-800 border-amber-300 bg-amber-50/40' : 'text-slate-500' }}" />
                </div>
                @if ($filterTanggal)
                    <button type="button" wire:click="$set('filterTanggal', '')"
                        class="px-3 py-2 rounded-xl bg-amber-100 hover:bg-amber-200 text-amber-800 text-xs font-bold cursor-pointer">
                        Reset
                    </button>
                @endif
            </div>

            <x-mobile.scroll-list :count="$riwayat->count()" label="Daftar Riwayat Absensi">
                @foreach ($riwayat as $item)
                    @php
                        $status = $item->status?->value ?? $item->status;
                        $tglKey = $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') : null;
                        $lhkItem = $tglKey ? $lhkByTanggal->get($tglKey) ?? null : null;
                        $jumlahKegiatan = $lhkItem?->pekerjaans_count ?? 0;
                        $menitKerjaHarian = $item->hitungMenitKerjaEfektif();
                        $durasiKerjaHarian = \App\Models\AbsensiHarian::formatDurasiJamMenit($menitKerjaHarian);
                        $bisaIsiLhk =
                            $tglKey &&
                            (!empty($item->log_masuk_id) ||
                                !empty($item->jam_masuk) ||
                                in_array($status, ['hadir', 'terlambat', 'dinas_luar'], true));
                        $badgeStyle = match ($status) {
                            'hadir' => 'text-emerald-700 bg-emerald-50 border-emerald-200',
                            'terlambat' => 'text-amber-700 bg-amber-50 border-amber-200',
                            'dinas_luar' => 'text-sky-700 bg-sky-50 border-sky-200',
                            'izin' => 'text-blue-700 bg-blue-50 border-blue-200',
                            'sakit' => 'text-purple-700 bg-purple-50 border-purple-200',
                            default => 'text-rose-700 bg-rose-50 border-rose-200',
                        };
                        $iconColor = match ($status) {
                            'hadir' => 'text-emerald-500 bg-emerald-50',
                            'terlambat' => 'text-amber-500 bg-amber-50',
                            'dinas_luar' => 'text-sky-500 bg-sky-50',
                            'izin' => 'text-blue-500 bg-blue-50',
                            'sakit' => 'text-purple-500 bg-purple-50',
                            default => 'text-rose-500 bg-rose-50',
                        };
                    @endphp

                    <div wire:key="riwayat-{{ $item->id }}" wire:click="bukaDetail({{ $item->id }})"
                        class="bg-white p-4 rounded-2xl shadow-md shadow-slate-900/5 border border-slate-100 cursor-pointer hover:border-sky-300 hover:shadow-md transition active:scale-[0.98] space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3.5 min-w-0">
                                <div
                                    class="w-11 h-11 rounded-2xl {{ $iconColor }} flex items-center justify-center text-base shadow-md shadow-slate-900/5 shrink-0">
                                    <i class="fa-solid fa-calendar-day"></i>
                                </div>

                                <div class="min-w-0">
                                    <h4 class="font-extrabold text-slate-800 text-sm truncate">
                                        {{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->translatedFormat('l, d M Y') : '-' }}
                                    </h4>
                                    <div
                                        class="flex items-center flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500 font-mono mt-1 font-semibold">
                                        <!-- Jam Masuk & Sumber Device dari DB -->
                                        <span class="flex items-center" title="Jam Masuk">
                                            <i class="fa-solid fa-circle text-amber-500 text-[6px] mr-1.5"></i>
                                            {{ $item->jam_masuk ? substr($item->jam_masuk, 0, 8) : '--:--' }}
                                            @if ($item->logMasuk?->perangkat === 'mesin')
                                                <i class="fa-solid fa-fingerprint text-[10px] text-sky-500 ml-1"
                                                    title="Mesin Fingerprint"></i>
                                            @elseif($item->logMasuk?->perangkat === 'mobile')
                                                <i class="fa-solid fa-mobile-screen text-[10px] text-emerald-500 ml-1"
                                                    title="Aplikasi Mobile"></i>
                                            @endif
                                        </span>
                                        <span class="text-slate-300">•</span>
                                        <!-- Jam Pulang & Sumber Device dari DB -->
                                        <span class="flex items-center" title="Jam Pulang">
                                            <i class="fa-solid fa-circle text-emerald-500 text-[6px] mr-1.5"></i>
                                            {{ $item->jam_pulang ? substr($item->jam_pulang, 0, 8) : '--:--' }}
                                            @if ($item->logPulang?->perangkat === 'mesin')
                                                <i class="fa-solid fa-fingerprint text-[10px] text-sky-500 ml-1"
                                                    title="Mesin Fingerprint"></i>
                                            @elseif($item->logPulang?->perangkat === 'mobile')
                                                <i class="fa-solid fa-mobile-screen text-[10px] text-emerald-500 ml-1"
                                                    title="Aplikasi Mobile"></i>
                                            @endif
                                        </span>
                                    </div>
                                    @if (!empty($item->keterangan))
                                        <p class="text-[10px] text-slate-400 mt-1 italic line-clamp-1">
                                            {{ $item->keterangan }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col items-end space-y-1.5 shrink-0 ml-2">
                                <span
                                    class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase border {{ $badgeStyle }} tracking-wider">
                                    {{ str_replace('_', ' ', $status) }}
                                </span>
                                <div class="flex items-center text-[10px] text-slate-400">
                                    <span>Detail</span>
                                    <i class="fa-solid fa-chevron-right text-[8px] ml-1"></i>
                                </div>
                            </div>
                        </div>

                        @if ($bisaIsiLhk)
                            <div
                                class="flex items-center justify-between pt-2 border-t border-slate-100/80 dark:border-slate-700/60 gap-2">
                                <div class="flex items-center flex-wrap gap-x-2.5 gap-y-1">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-mono font-bold {{ $menitKerjaHarian >= \App\Models\AbsensiHarian::TARGET_MENIT_HARIAN_ASN ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : ($menitKerjaHarian > 0 ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400') }}">
                                        <i class="fa-regular fa-clock text-[9px]"></i>
                                        {{ $menitKerjaHarian > 0 ? $durasiKerjaHarian : (!empty($item->jam_masuk) && empty($item->jam_pulang) ? 'Berjalan' : '0j 00m') }}
                                        / 7j 30m
                                    </span>
                                    <span
                                        class="text-[11px] font-semibold {{ $jumlahKegiatan > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">
                                        <i class="fa-solid fa-file-lines mr-1"></i>
                                        {{ $jumlahKegiatan > 0 ? "{$jumlahKegiatan} Keg." : 'LHK Kosong' }}
                                    </span>
                                </div>
                                <a href="{{ route('mobile.lhk.create', ['tanggal' => $tglKey]) }}" wire:navigate
                                    @click.stop
                                    class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-[11px] font-bold transition active:scale-95 shrink-0 {{ $jumlahKegiatan > 0 ? 'bg-sky-50 text-sky-700 border border-sky-200 hover:bg-sky-100 dark:bg-sky-500/15 dark:text-sky-300 dark:border-sky-500/30' : 'bg-[#1B6CA8] hover:bg-[#155685] text-white shadow-md shadow-slate-900/5' }}">
                                    <i
                                        class="fa-solid {{ $jumlahKegiatan > 0 ? 'fa-pen-to-square' : 'fa-plus' }} text-[10px]"></i>
                                    <span>{{ $jumlahKegiatan > 0 ? 'Edit LHK' : 'Buat LHK' }}</span>
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </x-mobile.scroll-list>
        </div>
    @endif

    <!-- MODAL DETAIL KEHADIRAN & GPS -->
    @if ($showDetailModal && $selectedAbsensi)
        <div class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-3 sm:p-4 bg-slate-950/60 backdrop-blur-xs transition-opacity"
            x-data @keydown.escape.window="$wire.tutupDetail()">
            <div
                class="w-full max-w-[408px] bg-white dark:bg-[#132338] rounded-3xl p-5 shadow-2xl border border-slate-200/90 dark:border-slate-700/80 max-h-[85vh] overflow-y-auto space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-2">
                        <div
                            class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-800 text-sm">Detail Presensi Kehadiran</h3>
                            <p class="text-[11px] text-slate-400 font-medium">
                                {{ \Carbon\Carbon::parse($selectedAbsensi->tanggal)->translatedFormat('l, d F Y') }}
                            </p>
                        </div>
                    </div>
                    <button wire:click="tutupDetail"
                        class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center cursor-pointer transition">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Status
                            Kehadiran</span>
                        <span class="font-black text-slate-800 uppercase tracking-tight text-sm">
                            {{ str_replace('_', ' ', $selectedAbsensi->status?->value ?? $selectedAbsensi->status) }}
                        </span>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Tipe
                            Kerja</span>
                        <span class="font-black text-slate-800 uppercase tracking-tight text-sm">
                            {{ $selectedAbsensi->user?->status_kerja === 'wfh' ? 'WFH (Fleksibel)' : 'WFO (Kantor)' }}
                        </span>
                    </div>
                </div>

                @php
                    $detailMenit = $selectedAbsensi->hitungMenitKerjaEfektif();
                    $detailDurasi = \App\Models\AbsensiHarian::formatDurasiJamMenit($detailMenit);
                @endphp
                <div
                    class="p-3.5 rounded-2xl bg-sky-50/70 dark:bg-sky-500/10 border border-sky-200/80 dark:border-sky-500/30 flex items-center justify-between text-xs">
                    <div>
                        <span
                            class="text-[10px] font-bold text-sky-700 dark:text-cyan-300 uppercase tracking-wider block">Jam
                            Kerja Efektif Harian</span>
                        <span class="text-[10px] text-slate-500 dark:text-slate-400">Standar ASN: 7j 30m/hari (di luar
                            jam istirahat)</span>
                    </div>
                    <span
                        class="font-mono font-black text-sm {{ $detailMenit >= \App\Models\AbsensiHarian::TARGET_MENIT_HARIAN_ASN ? 'text-emerald-700 dark:text-emerald-300' : 'text-sky-800 dark:text-cyan-200' }}">
                        {{ $detailDurasi }} / 7j 30m
                    </span>
                </div>

                <!-- Jam Masuk & Jam Pulang Cards -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3.5 rounded-2xl bg-emerald-50/60 border border-emerald-100">
                        <span
                            class="text-[10px] font-extrabold text-emerald-700 uppercase tracking-wider block mb-1">Absen
                            Masuk</span>
                        <div class="font-mono font-black text-lg text-emerald-800">
                            {{ $selectedAbsensi->jam_masuk ? substr($selectedAbsensi->jam_masuk, 0, 8) : '--:--' }}
                        </div>
                        <span class="text-[10px] text-emerald-600 font-medium mt-1 block">
                            {{ $selectedAbsensi->logMasuk?->perangkat ? 'Perangkat: ' . ucfirst($selectedAbsensi->logMasuk->perangkat) : 'Presensi Resmi' }}
                        </span>
                        @if ($selectedAbsensi->latitude_masuk && $selectedAbsensi->longitude_masuk)
                            <a href="https://maps.google.com/?q={{ $selectedAbsensi->latitude_masuk }},{{ $selectedAbsensi->longitude_masuk }}"
                                target="_blank"
                                class="inline-flex items-center gap-1 mt-2 text-[10px] font-bold text-emerald-700 underline">
                                <i class="fa-solid fa-location-dot"></i> Peta Masuk
                            </a>
                        @endif
                    </div>

                    <div class="p-3.5 rounded-2xl bg-sky-50/60 border border-sky-100">
                        <span class="text-[10px] font-extrabold text-sky-700 uppercase tracking-wider block mb-1">Absen
                            Pulang</span>
                        <div class="font-mono font-black text-lg text-sky-800">
                            {{ $selectedAbsensi->jam_pulang ? substr($selectedAbsensi->jam_pulang, 0, 8) : '--:--' }}
                        </div>
                        <span class="text-[10px] text-sky-600 font-medium mt-1 block">
                            {{ $selectedAbsensi->logPulang?->perangkat ? 'Perangkat: ' . ucfirst($selectedAbsensi->logPulang->perangkat) : 'Presensi Resmi' }}
                        </span>
                        @if ($selectedAbsensi->latitude_pulang && $selectedAbsensi->longitude_pulang)
                            <a href="https://maps.google.com/?q={{ $selectedAbsensi->latitude_pulang }},{{ $selectedAbsensi->longitude_pulang }}"
                                target="_blank"
                                class="inline-flex items-center gap-1 mt-2 text-[10px] font-bold text-sky-700 underline">
                                <i class="fa-solid fa-location-dot"></i> Peta Pulang
                            </a>
                        @endif
                    </div>
                </div>

                @if (!empty($selectedAbsensi->keterangan))
                    <div class="p-3.5 rounded-2xl bg-amber-50/60 border border-amber-100 text-xs">
                        <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wider block mb-1">Catatan
                            / Keterangan</span>
                        <p class="text-slate-700">{{ $selectedAbsensi->keterangan }}</p>
                    </div>
                @endif

                <div class="pt-2">
                    <button type="button" wire:click="tutupDetail"
                        class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                        Tutup Rincian
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
