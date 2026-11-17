<div class="p-4 pb-28 text-slate-800 dark:text-slate-100">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2.5">
            <a href="{{ route('mobile.dashboard') }}" wire:navigate
                class="w-9 h-9 rounded-xl bg-white dark:bg-white/10 border border-slate-200 dark:border-white/15 flex items-center justify-center text-slate-600 dark:text-slate-200">
                <i class="fa-solid fa-arrow-left text-xs"></i>
            </a>
            <div>
                <h1 class="text-base font-extrabold text-slate-900 dark:text-white leading-tight">Persetujuan Cepat</h1>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Approve Izin, LHK, SPJ & Surat Tugas dari HP</p>
            </div>
        </div>
    </div>

    <!-- Tab Selector -->
    @php
        $enabledTabCount = collect([$izinEnabled ?? true, $lhkEnabled ?? true, $spjEnabled ?? true, $stEnabled ?? true])->filter()->count();
        $gridColsClass = match ($enabledTabCount) {
            1 => 'grid-cols-1',
            2 => 'grid-cols-2',
            3 => 'grid-cols-3',
            default => 'grid-cols-4',
        };
    @endphp
    <div class="grid {{ $gridColsClass }} gap-1 p-1 rounded-xl bg-slate-100 dark:bg-[#132338] border border-slate-200 dark:border-slate-700/70 mb-4">
        @if ($izinEnabled ?? true)
        <button type="button" wire:click="setTab('izin')"
            class="py-2 px-1.5 rounded-lg text-[11px] font-bold transition flex items-center justify-center gap-1 {{ $tab === 'izin' ? 'bg-white dark:bg-[#1B6CA8] text-sky-700 dark:text-white shadow-xs' : 'text-slate-500 dark:text-slate-400' }}">
            <span>Izin</span>
            @if ($izinList->count() > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[9px] bg-amber-500/20 text-amber-700 dark:text-amber-300">{{ $izinList->count() }}</span>
            @endif
        </button>
        @endif
        @if ($lhkEnabled ?? true)
        <button type="button" wire:click="setTab('lhk')"
            class="py-2 px-1.5 rounded-lg text-[11px] font-bold transition flex items-center justify-center gap-1 {{ $tab === 'lhk' ? 'bg-white dark:bg-[#1B6CA8] text-sky-700 dark:text-white shadow-xs' : 'text-slate-500 dark:text-slate-400' }}">
            <span>LHK</span>
            @if ($lhkList->count() > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[9px] bg-amber-500/20 text-amber-700 dark:text-amber-300">{{ $lhkList->count() }}</span>
            @endif
        </button>
        @endif
        @if ($spjEnabled ?? true)
        <button type="button" wire:click="setTab('spj')"
            class="py-2 px-1.5 rounded-lg text-[11px] font-bold transition flex items-center justify-center gap-1 {{ $tab === 'spj' ? 'bg-white dark:bg-[#1B6CA8] text-sky-700 dark:text-white shadow-xs' : 'text-slate-500 dark:text-slate-400' }}">
            <span>SPJ</span>
            @if ($spjList->count() > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[9px] bg-amber-500/20 text-amber-700 dark:text-amber-300">{{ $spjList->count() }}</span>
            @endif
        </button>
        @endif
        @if ($stEnabled ?? true)
        <button type="button" wire:click="setTab('surat_tugas')"
            class="py-2 px-1.5 rounded-lg text-[11px] font-bold transition flex items-center justify-center gap-1 {{ $tab === 'surat_tugas' ? 'bg-white dark:bg-[#1B6CA8] text-sky-700 dark:text-white shadow-xs' : 'text-slate-500 dark:text-slate-400' }}">
            <span>Tugas</span>
            @if ($suratTugasList->count() > 0)
                <span class="px-1.5 py-0.5 rounded-full text-[9px] bg-amber-500/20 text-amber-700 dark:text-amber-300">{{ $suratTugasList->count() }}</span>
            @endif
        </button>
        @endif
    </div>

    <!-- Tab Izin & Cuti -->
    @if ($tab === 'izin')
        <div class="space-y-3">
            @forelse ($izinList as $item)
                <div wire:key="izin-{{ $item->id }}" class="p-3.5 rounded-2xl bg-white dark:bg-[#132338] border border-slate-200 dark:border-slate-700/70 shadow-xs">
                    <div class="flex items-start justify-between gap-2 mb-1.5">
                        <div>
                            <p class="text-xs font-extrabold text-slate-900 dark:text-white">{{ $item->user?->name ?? 'Pegawai' }}</p>
                            <p class="text-[11px] font-semibold text-sky-600 dark:text-cyan-400">
                                {{ $item->jenisIzin?->nama ?? 'Izin/Cuti' }} • {{ $item->jumlah_hari }} hari
                            </p>
                        </div>
                        <span class="text-[10px] font-mono text-slate-500 dark:text-slate-400">
                            {{ $item->tanggal_mulai?->format('d/m/Y') }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mb-3">{{ $item->alasan }}</p>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" wire:click="approveIzin({{ $item->id }})"
                            class="py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                            <i class="fa-solid fa-check mr-1"></i> Setujui
                        </button>
                        <button type="button" wire:click="rejectIzin({{ $item->id }})"
                            class="py-2 rounded-xl bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-300 border border-rose-200 dark:border-rose-500/30 text-xs font-bold transition">
                            <i class="fa-solid fa-xmark mr-1"></i> Tolak
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center rounded-2xl bg-white dark:bg-[#132338] border border-slate-200 dark:border-slate-700/70">
                    <i class="fa-regular fa-circle-check text-2xl text-emerald-500 mb-2"></i>
                    <p class="text-xs font-bold text-slate-600 dark:text-slate-300">Tidak ada pengajuan izin yang menunggu.</p>
                </div>
            @endforelse
        </div>
    @endif

    <!-- Tab LHK -->
    @if ($tab === 'lhk')
        <div class="space-y-3">
            @forelse ($lhkList as $item)
                <div wire:key="lhk-{{ $item->id }}" class="p-3.5 rounded-2xl bg-white dark:bg-[#132338] border border-slate-200 dark:border-slate-700/70 shadow-xs">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <p class="text-xs font-extrabold text-slate-900 dark:text-white">{{ $item->lhk?->user?->name ?? 'Pegawai' }}</p>
                        <span class="text-[10px] font-mono text-slate-500 dark:text-slate-400">
                            {{ $item->lhk?->tanggal ? \Carbon\Carbon::parse($item->lhk->tanggal)->format('d/m/Y') : '-' }}
                        </span>
                    </div>
                    <p class="text-[11px] font-semibold text-sky-600 dark:text-cyan-400 mb-1">
                        {{ $item->aktivitas?->nama_aktivitas ?? 'Kegiatan Harian' }}
                    </p>
                    <p class="text-xs text-slate-600 dark:text-slate-300 mb-3">{{ $item->hasil_pekerjaan }}</p>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" wire:click="approveLhk({{ $item->id }})"
                            class="py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                            <i class="fa-solid fa-check mr-1"></i> Setujui
                        </button>
                        <button type="button" wire:click="rejectLhk({{ $item->id }})"
                            class="py-2 rounded-xl bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-300 border border-rose-200 dark:border-rose-500/30 text-xs font-bold transition">
                            <i class="fa-solid fa-xmark mr-1"></i> Tolak
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center rounded-2xl bg-white dark:bg-[#132338] border border-slate-200 dark:border-slate-700/70">
                    <i class="fa-regular fa-circle-check text-2xl text-emerald-500 mb-2"></i>
                    <p class="text-xs font-bold text-slate-600 dark:text-slate-300">Tidak ada LHK yang menunggu verifikasi.</p>
                </div>
            @endforelse
        </div>
    @endif

    <!-- Tab SPJ Keuangan -->
    @if ($tab === 'spj')
        <div class="space-y-3">
            @forelse ($spjList as $item)
                <div wire:key="spj-{{ $item->id }}" class="p-3.5 rounded-2xl bg-white dark:bg-[#132338] border border-slate-200 dark:border-slate-700/70 shadow-xs">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <div>
                            <p class="text-xs font-extrabold text-slate-900 dark:text-white">{{ $item->nomor_pengajuan }}</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $item->user?->name ?? 'Pemohon' }} • {{ $item->bidang?->nama ?? '-' }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-500/15 text-amber-700 dark:text-amber-300">
                            {{ $item->status_label }}
                        </span>
                    </div>
                    <p class="text-xs font-bold text-sky-700 dark:text-cyan-300 mb-3">
                        Rp {{ number_format((float) ($item->total_anggaran ?? 0), 0, ',', '.') }}
                    </p>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" wire:click="approveSpj({{ $item->id }})"
                            class="py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                            <i class="fa-solid fa-check mr-1"></i> Setujui
                        </button>
                        <button type="button" wire:click="revisiSpj({{ $item->id }})"
                            class="py-2 rounded-xl bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30 text-xs font-bold transition">
                            <i class="fa-solid fa-rotate-left mr-1"></i> Revisi
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center rounded-2xl bg-white dark:bg-[#132338] border border-slate-200 dark:border-slate-700/70">
                    <i class="fa-regular fa-circle-check text-2xl text-emerald-500 mb-2"></i>
                    <p class="text-xs font-bold text-slate-600 dark:text-slate-300">Tidak ada SPJ yang menunggu persetujuan Anda.</p>
                </div>
            @endforelse
        </div>
    @endif

    <!-- Tab Surat Tugas Tim Kreatif -->
    @if ($tab === 'surat_tugas')
        <div class="space-y-3">
            @forelse ($suratTugasList as $item)
                <div wire:key="st-{{ $item->id }}" class="p-3.5 rounded-2xl bg-white dark:bg-[#132338] border border-slate-200 dark:border-slate-700/70 shadow-xs">
                    <div class="flex items-start justify-between gap-2 mb-1.5">
                        <div class="min-w-0">
                            <p class="text-xs font-extrabold text-slate-900 dark:text-white leading-snug">{{ $item->nama_kegiatan }}</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ $item->lokasi ?: 'Lokasi belum diisi' }} • {{ $item->pegawais->count() }} anggota tim
                            </p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-500/15 text-amber-700 dark:text-amber-300 shrink-0">
                            Draft
                        </span>
                    </div>
                    <p class="text-[11px] font-mono text-sky-700 dark:text-cyan-300 mb-3">
                        <i class="fa-regular fa-calendar mr-1"></i>
                        {{ $item->tanggal_mulai?->format('d/m/Y') }}
                        @if ($item->tanggal_selesai && $item->tanggal_selesai->ne($item->tanggal_mulai))
                            - {{ $item->tanggal_selesai->format('d/m/Y') }}
                        @endif
                    </p>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('surat-tugas.cetak-pdf', ['suratTugas' => $item, 'preview' => 1]) }}"
                            target="_blank"
                            class="py-2 rounded-xl bg-sky-50 dark:bg-sky-500/15 text-sky-700 dark:text-sky-300 border border-sky-200 dark:border-sky-500/30 text-xs font-bold text-center transition">
                            <i class="fa-solid fa-file-pdf mr-1"></i> Preview
                        </a>
                        <button type="button" wire:click="approveSuratTugas({{ $item->id }})"
                            wire:confirm="Setujui dan terbitkan Surat Tugas ini?"
                            class="py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition">
                            <i class="fa-solid fa-check mr-1"></i> Setujui & Terbitkan
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center rounded-2xl bg-white dark:bg-[#132338] border border-slate-200 dark:border-slate-700/70">
                    <i class="fa-regular fa-circle-check text-2xl text-emerald-500 mb-2"></i>
                    <p class="text-xs font-bold text-slate-600 dark:text-slate-300">Tidak ada Surat Tugas yang menunggu persetujuan Anda.</p>
                </div>
            @endforelse
        </div>
    @endif
</div>

