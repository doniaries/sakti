<div class="p-5 pb-28">
    <!-- Header Back Navigation -->
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center space-x-3">
            <a href="{{ route('mobile.dashboard') }}" wire:navigate 
               class="w-10 h-10 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-center text-slate-600 hover:text-slate-800 active:scale-95 transition-all">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Presensi WFH</h1>
                <p class="text-xs text-slate-500 font-medium">Riwayat Work From Home Pegawai</p>
            </div>
        </div>

        <!-- Month Switcher -->
        <div class="flex items-center space-x-1.5 bg-white px-2 py-1 rounded-2xl shadow-xs border border-slate-200/80">
            <button wire:click="prevMonth" class="w-6 h-6 rounded-lg bg-slate-50 hover:bg-sky-50 text-slate-500 hover:text-sky-600 flex items-center justify-center transition-colors active:scale-90">
                <i class="fa-solid fa-chevron-left text-[10px]"></i>
            </button>
            <span class="text-xs font-black text-sky-700 px-1 select-none whitespace-nowrap">
                {{ $namaBulan }}
            </span>
            <button wire:click="nextMonth" class="w-6 h-6 rounded-lg bg-slate-50 hover:bg-sky-50 text-slate-500 hover:text-sky-600 flex items-center justify-center transition-colors active:scale-90">
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </button>
        </div>
    </div>

    <!-- Status Penetapan WFH Card -->
    <div class="mb-5 p-4 rounded-3xl bg-gradient-to-br {{ $user->status_kerja === 'wfh' ? 'from-teal-600 via-teal-700 to-emerald-800' : 'from-slate-800 via-slate-900 to-slate-950' }} text-white shadow-lg shadow-teal-950/20 relative overflow-hidden border border-white/10">
        <div class="absolute -right-4 -bottom-4 text-white/10 text-8xl pointer-events-none">
            <i class="fa-solid fa-house-laptop"></i>
        </div>
        <div class="relative z-10 flex items-start justify-between">
            <div>
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-teal-200/90 flex items-center mb-1">
                    <i class="fa-solid fa-stamp mr-1.5 text-xs"></i> Penetapan Kedinasan
                </span>
                <h2 class="text-lg font-black tracking-tight">
                    {{ $user->status_kerja === 'wfh' ? 'Pegawai Work From Home (WFH)' : 'Pegawai Work From Office (WFO)' }}
                </h2>
                <p class="text-[11px] text-teal-100/80 mt-1 max-w-[280px] leading-relaxed">
                    Penetapan tipe kerja WFO/WFH ditetapkan langsung oleh pimpinan dinas / kepegawaian resmi (bukan diusulkan mandiri).
                </p>
            </div>
            <span class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider {{ $user->status_kerja === 'wfh' ? 'bg-emerald-400 text-slate-900' : 'bg-white/20 text-white' }} shadow-xs">
                {{ strtoupper($user->status_kerja ?? 'WFO') }}
            </span>
        </div>
        <div class="mt-3 pt-3 border-t border-white/10 flex items-center justify-between text-xs text-teal-100/90 relative z-10">
            <span>Total Pelaksanaan WFH Bulan Ini:</span>
            <span class="font-extrabold text-white font-mono bg-white/15 px-2 py-0.5 rounded-lg">{{ $totalWfhBulanIni }} Hari</span>
        </div>
    </div>

    <!-- List Riwayat WFH yang Sudah Dilakukan -->
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                <i class="fa-solid fa-list-check text-teal-600 mr-1.5"></i> Daftar WFH Dilaksanakan
            </h3>
            <span class="text-[11px] font-semibold text-slate-400">{{ $totalWfhBulanIni }} data</span>
        </div>

        @forelse($riwayatWfh as $item)
            @php
                $hasMasukCoords = !empty($item->latitude_masuk) && !empty($item->longitude_masuk);
                $hasPulangCoords = !empty($item->latitude_pulang) && !empty($item->longitude_pulang);
                $mapsMasukUrl = $hasMasukCoords ? "https://www.google.com/maps?q={$item->latitude_masuk},{$item->longitude_masuk}" : null;
                $mapsPulangUrl = $hasPulangCoords ? "https://www.google.com/maps?q={$item->latitude_pulang},{$item->longitude_pulang}" : null;
            @endphp
            <div class="bg-white rounded-3xl p-4 border border-slate-100 shadow-xs space-y-3">
                <!-- Baris Atas: Tanggal & Badge -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-10 h-10 rounded-2xl bg-teal-50 text-teal-700 border border-teal-200/80 flex flex-col items-center justify-center font-bold text-center shrink-0">
                            <span class="text-xs font-extrabold leading-none">{{ $item->tanggal->format('d') }}</span>
                            <span class="text-[9px] uppercase tracking-wider mt-0.5 font-bold">{{ $item->tanggal->translatedFormat('M') }}</span>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-800 leading-snug">{{ $item->tanggal->translatedFormat('l, d F Y') }}</h4>
                            <span class="text-[10px] text-slate-400 font-medium">Status Presensi: {{ ucfirst($item->status ?? 'hadir') }}</span>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-teal-100 text-teal-800 border border-teal-200">
                        WFH
                    </span>
                </div>

                <!-- Jam Masuk & Pulang Grid -->
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <!-- Masuk -->
                    <div class="bg-slate-50/80 rounded-2xl p-2.5 border border-slate-100">
                        <div class="flex items-center justify-between text-[10px] text-slate-500 font-semibold mb-1">
                            <span class="flex items-center"><i class="fa-solid fa-arrow-right-to-bracket text-emerald-600 mr-1"></i> Jam Masuk</span>
                        </div>
                        <div class="text-sm font-extrabold text-slate-800 font-mono">
                            {{ $item->jam_masuk ? substr($item->jam_masuk, 0, 5) . ' WIB' : '--:--' }}
                        </div>
                        <!-- Titik Koordinat Masuk -->
                        @if($hasMasukCoords)
                            <div class="mt-2 pt-2 border-t border-slate-200/60">
                                <span class="text-[9px] text-slate-400 font-medium block">Koordinat Masuk:</span>
                                <span class="text-[10px] font-mono font-bold text-slate-700 block truncate" title="{{ $item->latitude_masuk }}, {{ $item->longitude_masuk }}">
                                    {{ round($item->latitude_masuk, 5) }}, {{ round($item->longitude_masuk, 5) }}
                                </span>
                                <a href="{{ $mapsMasukUrl }}" target="_blank" rel="noopener noreferrer"
                                   class="mt-1 inline-flex items-center text-[10px] font-bold text-sky-600 hover:text-sky-700">
                                    <i class="fa-solid fa-location-dot mr-1 text-rose-500"></i> Buka di Peta
                                </a>
                            </div>
                        @else
                            <span class="text-[10px] text-slate-400 italic block mt-1">Koordinat tidak tercatat</span>
                        @endif
                    </div>

                    <!-- Pulang -->
                    <div class="bg-slate-50/80 rounded-2xl p-2.5 border border-slate-100">
                        <div class="flex items-center justify-between text-[10px] text-slate-500 font-semibold mb-1">
                            <span class="flex items-center"><i class="fa-solid fa-arrow-right-from-bracket text-amber-600 mr-1"></i> Jam Pulang</span>
                        </div>
                        <div class="text-sm font-extrabold text-slate-800 font-mono">
                            {{ $item->jam_pulang ? substr($item->jam_pulang, 0, 5) . ' WIB' : '--:--' }}
                        </div>
                        <!-- Titik Koordinat Pulang -->
                        @if($hasPulangCoords)
                            <div class="mt-2 pt-2 border-t border-slate-200/60">
                                <span class="text-[9px] text-slate-400 font-medium block">Koordinat Pulang:</span>
                                <span class="text-[10px] font-mono font-bold text-slate-700 block truncate" title="{{ $item->latitude_pulang }}, {{ $item->longitude_pulang }}">
                                    {{ round($item->latitude_pulang, 5) }}, {{ round($item->longitude_pulang, 5) }}
                                </span>
                                <a href="{{ $mapsPulangUrl }}" target="_blank" rel="noopener noreferrer"
                                   class="mt-1 inline-flex items-center text-[10px] font-bold text-sky-600 hover:text-sky-700">
                                    <i class="fa-solid fa-location-dot mr-1 text-rose-500"></i> Buka di Peta
                                </a>
                            </div>
                        @else
                            <span class="text-[10px] text-slate-400 italic block mt-1">Koordinat tidak tercatat</span>
                        @endif
                    </div>
                </div>

                @if(!empty($item->keterangan))
                    <div class="text-[11px] text-slate-500 bg-slate-50 p-2 rounded-xl border border-slate-100">
                        <span class="font-semibold text-slate-600">Catatan:</span> {{ $item->keterangan }}
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-3xl p-8 border border-slate-100 text-center shadow-xs">
                <div class="w-14 h-14 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl mx-auto mb-2">
                    <i class="fa-solid fa-house-laptop"></i>
                </div>
                <h4 class="text-xs font-bold text-slate-700">Belum Ada Presensi WFH</h4>
                <p class="text-[11px] text-slate-400 mt-1">
                    Tidak ditemukan data presensi Work From Home yang telah dilakukan pada bulan {{ $namaBulan }}.
                </p>
            </div>
        @endforelse
    </div>
</div>

