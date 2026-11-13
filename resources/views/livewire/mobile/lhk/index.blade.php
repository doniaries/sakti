<div class="p-5 pb-28 relative min-h-screen">
    <div class="flex justify-between items-center gap-2 mb-5">
        <div class="min-w-0">
            <h1 class="text-xl font-extrabold text-slate-800 dark:text-white tracking-tight">Laporan LHK</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Daftar hadir yang dapat diisi LHK (sudah
                absen pagi)</p>
        </div>
        <a href="{{ route('mobile.lhk.bank-aktivitas') }}" wire:navigate
            class="px-3 py-1.5 rounded-xl bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-cyan-300 border border-sky-200 dark:border-sky-500/30 text-[11px] font-bold flex items-center gap-1.5 shrink-0 hover:bg-sky-100 transition active:scale-95">
            <i class="fa-solid fa-list-check text-xs"></i>
            <span>Bank Aktivitas</span>
        </a>
    </div>

    @if ($daftarHadirLhk->isEmpty())
        <div
            class="bg-white dark:bg-[#132338]/90 rounded-3xl p-8 text-center border border-slate-200/80 dark:border-slate-700/60 shadow-xs flex flex-col items-center justify-center mt-6">
            <div
                class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800/80 text-slate-400 flex items-center justify-center text-3xl mb-3">
                <i class="fa-solid fa-file-signature"></i>
            </div>
            <h3 class="font-bold text-slate-800 dark:text-white text-sm">Belum Ada Daftar Hadir</h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-[260px]">Lakukan absen masuk pagi terlebih
                dahulu untuk mulai mengisi Laporan Harian Kinerja (LHK).</p>
            <a href="{{ route('mobile.absensi') }}" wire:navigate
                class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-md shadow-sky-600/25 transition">
                <i class="fa-solid fa-fingerprint"></i>
                <span>Ambil Absen Sekarang</span>
            </a>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($daftarHadirLhk as $item)
                @php
                    $status = $item['status'];
                    $color = match ($status) {
                        'Hadir'
                            => 'text-emerald-700 bg-emerald-50 border-emerald-200 dark:text-emerald-300 dark:bg-emerald-500/15 dark:border-emerald-500/30',
                        'Terlambat'
                            => 'text-amber-700 bg-amber-50 border-amber-200 dark:text-amber-300 dark:bg-amber-500/15 dark:border-amber-500/30',
                        'Sakit'
                            => 'text-purple-700 bg-purple-50 border-purple-200 dark:text-purple-300 dark:bg-purple-500/15 dark:border-purple-500/30',
                        'Izin'
                            => 'text-sky-700 bg-sky-50 border-sky-200 dark:text-sky-300 dark:bg-sky-500/15 dark:border-sky-500/30',
                        'Dinas Luar'
                            => 'text-amber-700 bg-amber-50 border-amber-200 dark:text-amber-300 dark:bg-amber-500/15 dark:border-amber-500/30',
                        default
                            => 'text-slate-600 bg-slate-100 border-slate-200 dark:text-slate-300 dark:bg-slate-700/50 dark:border-slate-600/50',
                    };
                    $iconBox = match ($status) {
                        'Hadir' => 'text-emerald-600 bg-emerald-50 dark:text-emerald-400 dark:bg-emerald-500/15',
                        'Terlambat' => 'text-amber-600 bg-amber-50 dark:text-amber-400 dark:bg-amber-500/15',
                        'Sakit' => 'text-purple-600 bg-purple-50 dark:text-purple-400 dark:bg-purple-500/15',
                        'Izin' => 'text-sky-600 bg-sky-50 dark:text-sky-400 dark:bg-sky-500/15',
                        'Dinas Luar' => 'text-amber-600 bg-amber-50 dark:text-amber-400 dark:bg-amber-500/15',
                        default => 'text-slate-500 bg-slate-100 dark:text-slate-400 dark:bg-slate-700/40',
                    };
                @endphp

                <div wire:key="lhk-item-{{ $item['tanggal'] }}"
                    class="bg-white dark:bg-[#132338]/90 p-4 rounded-2xl shadow-xs border border-slate-200/80 dark:border-slate-700/60 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-11 h-11 rounded-2xl {{ $iconBox }} flex justify-center items-center shrink-0">
                                <i class="fa-solid fa-file-lines text-base"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-slate-800 dark:text-white text-sm truncate">
                                    {{ \Carbon\Carbon::parse($item['tanggal'])->translatedFormat('l, d M Y') }}
                                </h4>
                                <div
                                    class="flex items-center flex-wrap gap-x-2.5 gap-y-0.5 text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                    <span class="inline-flex items-center font-mono font-semibold">
                                        <i class="fa-solid fa-right-to-bracket text-emerald-500 mr-1 text-[10px]"></i>
                                        {{ $item['jam_masuk'] ?? '--:--' }}
                                    </span>
                                    <span class="text-slate-300 dark:text-slate-600">•</span>
                                    <span class="inline-flex items-center font-mono font-semibold">
                                        <i class="fa-solid fa-right-from-bracket text-sky-500 mr-1 text-[10px]"></i>
                                        {{ $item['jam_pulang'] ?? 'Belum Pulang' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <span
                            class="px-2.5 py-1 rounded-full border {{ $color }} text-[10px] font-extrabold uppercase shrink-0 tracking-wide">
                            {{ $status }}
                        </span>
                    </div>

                    <div
                        class="flex items-center justify-between pt-2.5 border-t border-slate-100 dark:border-slate-700/60 gap-2">
                        <div
                            class="flex items-center text-xs font-semibold {{ $item['sudah_ada_lhk'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-500 dark:text-slate-400' }}">
                            <i class="fa-solid fa-briefcase mr-1.5 text-[11px]"></i>
                            <span>{{ $item['jumlah_kegiatan'] }} Kegiatan</span>
                        </div>

                        <a href="{{ route('mobile.lhk.create', ['tanggal' => $item['tanggal']]) }}" wire:navigate
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-bold transition active:scale-95 shadow-xs {{ $item['sudah_ada_lhk'] ? 'bg-sky-50 text-sky-700 border border-sky-200 hover:bg-sky-100 dark:bg-sky-500/15 dark:text-sky-300 dark:border-sky-500/30' : 'bg-[#1B6CA8] hover:bg-[#155685] text-white shadow-[#1B6CA8]/25' }}">
                            <i
                                class="fa-solid {{ $item['sudah_ada_lhk'] ? 'fa-pen-to-square' : 'fa-plus' }} text-[11px]"></i>
                            <span>{{ $item['sudah_ada_lhk'] ? 'Edit LHK' : 'Buat LHK' }}</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
