<div class="p-5 pb-28">
    <!-- Top Bar -->
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Pengajuan Izin & Cuti</h1>
            <p class="text-xs text-slate-500 font-medium">Status Izin, Sakit, Cuti & WFH Anda</p>
        </div>
        <a href="{{ route('mobile.izin.create') }}" wire:navigate 
           class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-sky-600 to-sky-500 hover:from-sky-700 hover:to-sky-600 text-white font-bold text-xs shadow-md shadow-sky-500/25 active:scale-95 transition-all flex items-center space-x-1.5">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Buat Izin</span>
        </a>
    </div>

    <!-- Quick Category Action Buttons (Izin, Sakit, Cuti, WFH) -->
    <div class="grid grid-cols-4 gap-2 mb-6">
        <a href="{{ route('mobile.izin.create', ['kategori' => 'izin']) }}" wire:navigate 
           class="bg-white p-3 rounded-2xl border border-slate-100 shadow-xs flex flex-col items-center hover:bg-sky-50/50 active:scale-95 transition-all">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-base mb-1.5 shadow-xs">
                <i class="fa-solid fa-envelope-open-text"></i>
            </div>
            <span class="text-xs font-bold text-slate-700">Izin</span>
            <span class="text-[9px] text-slate-400 font-medium">Pribadi</span>
        </a>

        <a href="{{ route('mobile.izin.create', ['kategori' => 'sakit']) }}" wire:navigate 
           class="bg-white p-3 rounded-2xl border border-slate-100 shadow-xs flex flex-col items-center hover:bg-rose-50/50 active:scale-95 transition-all">
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-base mb-1.5 shadow-xs">
                <i class="fa-solid fa-notes-medical"></i>
            </div>
            <span class="text-xs font-bold text-slate-700">Sakit</span>
            <span class="text-[9px] text-slate-400 font-medium">Dokter</span>
        </a>

        <a href="{{ route('mobile.izin.create', ['kategori' => 'cuti']) }}" wire:navigate 
           class="bg-white p-3 rounded-2xl border border-slate-100 shadow-xs flex flex-col items-center hover:bg-amber-50/50 active:scale-95 transition-all">
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base mb-1.5 shadow-xs">
                <i class="fa-solid fa-umbrella-beach"></i>
            </div>
            <span class="text-xs font-bold text-slate-700">Cuti</span>
            <span class="text-[9px] text-slate-400 font-medium">Tahunan/dll</span>
        </a>

        <a href="{{ route('mobile.izin.create', ['kategori' => 'wfh']) }}" wire:navigate 
           class="bg-white p-3 rounded-2xl border border-slate-100 shadow-xs flex flex-col items-center hover:bg-teal-50/50 active:scale-95 transition-all">
            <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-base mb-1.5 shadow-xs">
                <i class="fa-solid fa-house-laptop"></i>
            </div>
            <span class="text-xs font-bold text-slate-700">WFH</span>
            <span class="text-[9px] text-slate-400 font-medium">Dari Rumah</span>
        </a>
    </div>

    <!-- Status Tabs Filter -->
    <div class="flex space-x-2 overflow-x-auto pb-2 mb-4 no-scrollbar">
        <button wire:click="setFilter('semua')" 
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all shadow-xs {{ $statusFilter === 'semua' ? 'bg-slate-800 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
            Semua
        </button>
        <button wire:click="setFilter('menunggu')" 
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all shadow-xs {{ $statusFilter === 'menunggu' ? 'bg-amber-500 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
            <i class="fa-solid fa-hourglass-half mr-1 text-[10px]"></i> Menunggu
        </button>
        <button wire:click="setFilter('disetujui')" 
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all shadow-xs {{ $statusFilter === 'disetujui' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
            <i class="fa-solid fa-circle-check mr-1 text-[10px]"></i> Disetujui
        </button>
        <button wire:click="setFilter('ditolak')" 
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all shadow-xs {{ $statusFilter === 'ditolak' ? 'bg-rose-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
            <i class="fa-solid fa-circle-xmark mr-1 text-[10px]"></i> Ditolak
        </button>
    </div>

    <!-- List Pengajuan Izin -->
    <div class="space-y-3">
        @forelse($pengajuans as $item)
            @php
                $statusColor = match($item->status) {
                    'disetujui' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'ditolak' => 'bg-rose-50 text-rose-700 border-rose-200',
                    default => 'bg-amber-50 text-amber-700 border-amber-200',
                };
                $statusIcon = match($item->status) {
                    'disetujui' => 'fa-circle-check text-emerald-600',
                    'ditolak' => 'fa-circle-xmark text-rose-600',
                    default => 'fa-hourglass-half text-amber-600',
                };
                $kategoriColor = match($item->jenisIzin?->kategori) {
                    'sakit' => 'from-rose-500 to-rose-600',
                    'cuti' => 'from-amber-500 to-amber-600',
                    'wfh' => 'from-teal-500 to-teal-600',
                    default => 'from-sky-500 to-sky-600',
                };
            @endphp
            <div class="bg-white rounded-3xl p-4 border border-slate-100 shadow-xs hover:border-slate-200 transition-all">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr {{ $kategoriColor }} text-white flex items-center justify-center text-sm shadow-xs">
                            @if($item->jenisIzin?->kategori === 'sakit')
                                <i class="fa-solid fa-notes-medical"></i>
                            @elseif($item->jenisIzin?->kategori === 'cuti')
                                <i class="fa-solid fa-umbrella-beach"></i>
                            @elseif($item->jenisIzin?->kategori === 'wfh')
                                <i class="fa-solid fa-house-laptop"></i>
                            @else
                                <i class="fa-solid fa-envelope-open-text"></i>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-xs font-bold text-slate-800">{{ $item->jenisIzin->nama ?? 'Izin' }}</h3>
                            <span class="text-[10px] text-slate-400 font-medium">Diajukan: {{ $item->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider border {{ $statusColor }} flex items-center space-x-1">
                        <i class="fa-solid {{ $statusIcon }}"></i>
                        <span>{{ ucfirst($item->status) }}</span>
                    </span>
                </div>

                <!-- Rentang Tanggal & Durasi -->
                <div class="bg-slate-50/80 rounded-2xl p-3 my-2.5 border border-slate-100/80 flex items-center justify-between text-xs">
                    <div class="flex items-center space-x-2">
                        <i class="fa-regular fa-calendar-days text-sky-600"></i>
                        <span class="font-bold text-slate-700">
                            {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : '-' }} 
                            @if($item->tanggal_selesai && $item->tanggal_selesai->ne($item->tanggal_mulai))
                                - {{ $item->tanggal_selesai->format('d/m/Y') }}
                            @endif
                        </span>
                    </div>
                    <span class="px-2 py-0.5 rounded-lg bg-sky-100 text-sky-800 font-extrabold text-[10px]">
                        {{ $item->jumlah_hari }} Hari Kerja
                    </span>
                </div>

                <!-- Alasan Pengajuan -->
                <div class="text-xs text-slate-600 mb-2 leading-relaxed">
                    <span class="text-slate-400 font-medium">Keterangan:</span> {{ $item->alasan }}
                </div>

                <!-- Lampiran & Catatan Atasan -->
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                    <div>
                        @if($item->lampiran)
                            <a href="{{ asset('storage/' . $item->lampiran) }}" target="_blank" class="text-sky-600 hover:text-sky-700 font-bold flex items-center">
                                <i class="fa-solid fa-paperclip mr-1"></i> Lihat Dokumen Bukti
                            </a>
                        @else
                            <span class="text-slate-400 font-medium">Tanpa Lampiran</span>
                        @endif
                    </div>
                    @if($item->catatan_atasan)
                        <div class="text-[10px] text-amber-700 bg-amber-50 px-2 py-0.5 rounded-lg border border-amber-200/60 max-w-[180px] truncate" title="{{ $item->catatan_atasan }}">
                            <i class="fa-solid fa-comment-dots mr-1"></i>{{ $item->catatan_atasan }}
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl p-8 border border-slate-100 text-center shadow-xs">
                <div class="w-16 h-16 rounded-full bg-sky-50 text-sky-500 flex items-center justify-center text-2xl mx-auto mb-3 shadow-inner">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
                <h4 class="text-sm font-bold text-slate-800 mb-1">Belum Ada Pengajuan</h4>
                <p class="text-xs text-slate-400 max-w-xs mx-auto mb-4">Anda belum memiliki riwayat pengajuan izin, sakit, atau cuti pada filter ini.</p>
                <a href="{{ route('mobile.izin.create') }}" wire:navigate 
                   class="inline-flex items-center space-x-1.5 px-4 py-2 bg-sky-600 text-white rounded-xl text-xs font-bold shadow-sm hover:bg-sky-700 active:scale-95 transition-all">
                    <i class="fa-solid fa-plus"></i>
                    <span>Buat Pengajuan Baru</span>
                </a>
            </div>
        @endforelse
    </div>
</div>
