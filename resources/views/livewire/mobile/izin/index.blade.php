<div class="p-5 pb-28">
    <!-- Hero Biru Gradient Melengkung -->
    <div class="-mx-5 -mt-5 mb-4 rounded-b-[28px] bg-gradient-to-br from-[#0F172A] via-[#0369A1] to-[#0284C7] px-5 pt-5 pb-10 text-white shadow-lg shadow-sky-950/20">
        <div>
            <h1 class="text-xl font-extrabold tracking-tight">Pengajuan Izin & Cuti</h1>
            <p class="text-xs text-sky-100 font-medium">Layanan Izin, Cuti & Persetujuan Pimpinan</p>
        </div>
    </div>

    <!-- Pimpinan Tab Navigation (Mengambang di atas lengkungan) -->
    @if ($isPimpinan)
        <div class="relative z-10 -mt-10 flex rounded-2xl bg-white p-1.5 mb-5 shadow-xl shadow-slate-900/10 ring-1 ring-slate-900/5 border border-slate-200/80">
            <button type="button" wire:click="setTab('saya')"
                class="flex-1 py-2 rounded-xl text-xs font-extrabold transition-all flex items-center justify-center gap-1.5 {{ $activeTab === 'saya' ? 'bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                <i class="fa-solid fa-user text-[13px]"></i>
                <span>Pengajuan Saya</span>
            </button>
            <button type="button" wire:click="setTab('verifikasi')"
                class="flex-1 py-2 rounded-xl text-xs font-extrabold transition-all flex items-center justify-center gap-1.5 {{ $activeTab === 'verifikasi' ? 'bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                <i class="fa-solid fa-clipboard-check text-[13px]"></i>
                <span>Verifikasi Bawahan</span>
                @if ($countMenungguBawahan > 0)
                    <span
                        class="px-1.5 py-0.2 rounded-full text-xs font-extrabold {{ $activeTab === 'verifikasi' ? 'bg-white text-sky-700' : 'bg-rose-500 text-white animate-pulse' }}">
                        {{ $countMenungguBawahan }}
                    </span>
                @endif
            </button>
        </div>
    @endif

    <!-- Quick Category Action Buttons (Hanya di Tab Pengajuan Saya) -->
    @if ($activeTab === 'saya')
        <div class="grid grid-cols-3 gap-2 mb-5">
            <a href="{{ route('mobile.izin.create', ['kategori' => 'izin']) }}" wire:navigate
                class="bg-white p-3 rounded-2xl border border-slate-100 shadow-md shadow-slate-900/5 flex flex-col items-center hover:bg-sky-50/50 active:scale-95 transition-all text-center">
                <div
                    class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-base mb-1.5 shadow-md shadow-slate-900/5">
                    <i class="fa-solid fa-envelope-open-text"></i>
                </div>
                <span class="text-xs font-bold text-slate-700">Izin</span>
                <span class="text-[11px] text-slate-400 font-medium">Pribadi</span>
            </a>

            <a href="{{ route('mobile.izin.create', ['kategori' => 'sakit']) }}" wire:navigate
                class="bg-white p-3 rounded-2xl border border-slate-100 shadow-md shadow-slate-900/5 flex flex-col items-center hover:bg-rose-50/50 active:scale-95 transition-all text-center">
                <div
                    class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-base mb-1.5 shadow-md shadow-slate-900/5">
                    <i class="fa-solid fa-notes-medical"></i>
                </div>
                <span class="text-xs font-bold text-slate-700">Sakit</span>
                <span class="text-[11px] text-slate-400 font-medium">Dokter/Non</span>
            </a>

            <a href="{{ route('mobile.izin.create', ['kategori' => 'cuti']) }}" wire:navigate
                class="bg-white p-3 rounded-2xl border border-slate-100 shadow-md shadow-slate-900/5 flex flex-col items-center hover:bg-amber-50/50 active:scale-95 transition-all text-center">
                <div
                    class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base mb-1.5 shadow-md shadow-slate-900/5">
                    <i class="fa-solid fa-umbrella-beach"></i>
                </div>
                <span class="text-xs font-bold text-slate-700">Cuti</span>
                <span class="text-[11px] text-slate-400 font-medium">Tahunan/dll</span>
            </a>
        </div>
    @endif

    <!-- Status Tabs Filter -->
    <div class="flex space-x-2 overflow-x-auto pb-2 mb-4 no-scrollbar">
        <button wire:click="setFilter('semua')"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all shadow-md shadow-slate-900/5 {{ $statusFilter === 'semua' ? 'bg-slate-800 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
            Semua
        </button>
        <button wire:click="setFilter('menunggu')"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all shadow-md shadow-slate-900/5 {{ $statusFilter === 'menunggu' ? 'bg-amber-500 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
            <i class="fa-solid fa-hourglass-half mr-1 text-xs"></i> Menunggu
        </button>
        <button wire:click="setFilter('disetujui')"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all shadow-md shadow-slate-900/5 {{ $statusFilter === 'disetujui' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
            <i class="fa-solid fa-circle-check mr-1 text-xs"></i> Disetujui
        </button>
        <button wire:click="setFilter('ditolak')"
            class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all shadow-md shadow-slate-900/5 {{ $statusFilter === 'ditolak' ? 'bg-rose-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
            <i class="fa-solid fa-circle-xmark mr-1 text-xs"></i> Ditolak
        </button>
    </div>

    <!-- List Pengajuan Izin -->
    <div class="space-y-3">
    <x-mobile.scroll-list :count="$pengajuans->count()" label="Daftar Pengajuan">
        @forelse($pengajuans as $item)
            @php
                $statusColor = match ($item->status) {
                    'disetujui' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'ditolak' => 'bg-rose-50 text-rose-700 border-rose-200',
                    default => 'bg-amber-50 text-amber-700 border-amber-200',
                };
                $statusIcon = match ($item->status) {
                    'disetujui' => 'fa-circle-check text-emerald-600',
                    'ditolak' => 'fa-circle-xmark text-rose-600',
                    default => 'fa-hourglass-half text-amber-600',
                };
                $kategoriColor = match ($item->jenisIzin?->kategori) {
                    'sakit' => 'from-rose-500 to-rose-600',
                    'cuti' => 'from-amber-500 to-amber-600',
                    'wfh' => 'from-teal-500 to-teal-600',
                    default => 'from-sky-500 to-sky-600',
                };
            @endphp
            <div wire:click="bukaDetail({{ $item->id }})"
                class="bg-white rounded-3xl p-4 border border-slate-100 shadow-md shadow-slate-900/5 hover:border-sky-300 active:scale-[0.99] cursor-pointer transition-all">
                <!-- Header Card: Pegawai (jika Verifikasi) atau Jenis Izin -->
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-2.5 min-w-0 pr-2">
                        <div
                            class="w-9 h-9 rounded-xl bg-gradient-to-tr {{ $kategoriColor }} text-white flex items-center justify-center text-sm shadow-md shadow-slate-900/5 shrink-0">
                            @if ($item->jenisIzin?->kategori === 'sakit')
                                <i class="fa-solid fa-notes-medical"></i>
                            @elseif($item->jenisIzin?->kategori === 'cuti')
                                <i class="fa-solid fa-umbrella-beach"></i>
                            @elseif($item->jenisIzin?->kategori === 'wfh')
                                <i class="fa-solid fa-house-laptop"></i>
                            @else
                                <i class="fa-solid fa-envelope-open-text"></i>
                            @endif
                        </div>
                        <div class="min-w-0">
                            @if ($activeTab === 'verifikasi')
                                <h3 class="text-xs font-extrabold text-slate-800 truncate">
                                    {{ $item->user->name ?? 'Pegawai' }}</h3>
                                <p class="text-xs text-slate-400 font-semibold truncate">
                                    {{ $item->jenisIzin->nama ?? 'Izin' }} •
                                    {{ $item->user->jabatan->nama ?? 'Staf' }}</p>
                            @else
                                <h3 class="text-xs font-bold text-slate-800">{{ $item->jenisIzin->nama ?? 'Izin' }}
                                </h3>
                                <span class="text-xs text-slate-400 font-medium">Diajukan:
                                    {{ $item->created_at->format('d/m/Y H:i') }}</span>
                            @endif
                        </div>
                    </div>
                    <span
                        class="px-2.5 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider border {{ $statusColor }} flex items-center space-x-1 shrink-0">
                        <i class="fa-solid {{ $statusIcon }}"></i>
                        <span>{{ ucfirst($item->status) }}</span>
                    </span>
                </div>

                <!-- Rentang Tanggal & Durasi -->
                <div
                    class="bg-slate-50/80 rounded-2xl p-3 my-2.5 border border-slate-100/80 flex items-center justify-between text-xs">
                    <div class="flex items-center space-x-2">
                        <i class="fa-regular fa-calendar-days text-sky-600"></i>
                        <span class="font-bold text-slate-700">
                            {{ $item->tanggal_mulai ? $item->tanggal_mulai->format('d/m/Y') : '-' }}
                            @if ($item->tanggal_selesai && $item->tanggal_selesai->ne($item->tanggal_mulai))
                                - {{ $item->tanggal_selesai->format('d/m/Y') }}
                            @endif
                        </span>
                    </div>
                    <span class="px-2 py-0.5 rounded-lg bg-sky-100 text-sky-800 font-extrabold text-xs">
                        {{ $item->jumlah_hari }} Hari Kerja
                    </span>
                </div>

                <!-- Alasan Pengajuan -->
                <div class="text-xs text-slate-600 mb-2 leading-relaxed">
                    <span class="text-slate-400 font-medium">Keterangan:</span> {{ $item->alasan }}
                </div>

                <!-- Lampiran & Detail Status -->
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2 text-[13px]">
                    <a href="{{ route('pengajuan-izin.cetak-pdf', ['pengajuanIzin' => $item, 'preview' => 1]) }}"
                        target="_blank"
                        @click.stop
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 font-bold hover:bg-rose-100 transition-colors">
                        <i class="fa-solid fa-file-pdf"></i>
                        <span>{{ $item->status === 'menunggu' ? 'Draft Surat PDF' : 'Lihat Surat PDF' }}</span>
                    </a>
                    <div>
                        @if ($item->lampiran)
                            <a href="{{ asset('storage/' . $item->lampiran) }}" target="_blank"
                                @click.stop
                                class="text-sky-600 hover:text-sky-700 font-bold flex items-center">
                                <i class="fa-solid fa-paperclip mr-1"></i> Bukti Lampiran
                            </a>
                        @else
                            <span class="text-slate-400 font-medium">Tanpa Lampiran</span>
                        @endif
                    </div>

                    <div class="flex items-center space-x-2">
                        @if ($item->catatan_atasan)
                            <div class="text-xs text-amber-700 bg-amber-50 px-2 py-0.5 rounded-lg border border-amber-200/60 max-w-[140px] truncate"
                                title="{{ $item->catatan_atasan }}">
                                <i class="fa-solid fa-comment-dots mr-1"></i>{{ $item->catatan_atasan }}
                            </div>
                        @endif

                        <button type="button" wire:click.stop="bukaDetail({{ $item->id }})"
                            class="text-xs font-bold text-sky-600 hover:text-sky-800 flex items-center gap-0.5">
                            <span>Detail</span>
                            <i class="fa-solid fa-chevron-right text-[8px]"></i>
                        </button>
                    </div>
                </div>

                <!-- Tombol Aksi Verifikasi Pimpinan -->
                @if ($activeTab === 'verifikasi' && $item->status === 'menunggu')
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-end flex-wrap gap-2">
                        @if ($item->jenisIzin?->kategori === 'cuti')
                            <button type="button"
                                @click.stop="window.dispatchEvent(new CustomEvent('buka-preview-cuti', { detail: { id: {{ $item->id }}, url: '{{ route('pengajuan-izin.cetak-pdf', ['pengajuanIzin' => $item->id, 'preview' => 1]) }}' } }))"
                                class="px-3 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 text-xs font-bold flex items-center gap-1 active:scale-95 transition">
                                <i class="fa-solid fa-file-pdf"></i> Preview Surat
                            </button>
                        @endif
                        <button type="button" wire:click.stop="bukaModalAksi({{ $item->id }}, 'tolak')"
                            class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold flex items-center gap-1 active:scale-95 transition">
                            <i class="fa-solid fa-xmark"></i> Tolak
                        </button>
                        <button type="button" wire:click.stop="bukaModalAksi({{ $item->id }}, 'setujui')"
                            class="px-4 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold shadow-sm active:scale-95 transition flex items-center gap-1">
                            <i class="fa-solid fa-check"></i> Setujui
                        </button>
                    </div>
                @endif

                <!-- Tombol Batalkan Pengajuan Saya (Hanya jika masih menunggu) -->
                @if ($activeTab === 'saya' && $item->status === 'menunggu')
                    <div class="mt-3 pt-2 border-t border-slate-100 flex justify-end">
                        <button type="button"
                            @click.stop.prevent="$dispatch('sakti-confirm', { message: 'Yakin ingin membatalkan dan menghapus pengajuan izin ini?', wireId: '{{ $this->getId() }}', method: 'batalkanPengajuan', params: [{{ $item->id }}] })"
                            class="text-[13px] font-bold text-rose-600 hover:text-rose-800 flex items-center gap-1 transition">
                            <i class="fa-solid fa-trash-can text-xs"></i> Batalkan Pengajuan
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-white rounded-3xl p-8 border border-slate-100 text-center shadow-md shadow-slate-900/5">
                <div
                    class="w-16 h-16 rounded-full bg-sky-50 text-sky-500 flex items-center justify-center text-2xl mx-auto mb-3 shadow-inner">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
                <h4 class="text-sm font-bold text-slate-800 mb-1">
                    {{ $activeTab === 'verifikasi' ? 'Tidak Ada Pengajuan Bawahan' : 'Belum Ada Pengajuan' }}
                </h4>
                <p class="text-xs text-slate-400 max-w-xs mx-auto mb-4">
                    {{ $activeTab === 'verifikasi' ? 'Seluruh pengajuan izin dan cuti bawahan telah diproses.' : 'Anda belum memiliki riwayat pengajuan izin pada filter ini.' }}
                </p>
                @if ($activeTab === 'saya')
                    <a href="{{ route('mobile.izin.create') }}" wire:navigate
                        class="inline-flex items-center space-x-1.5 px-4 py-2 bg-sky-600 text-white rounded-xl text-xs font-bold shadow-sm hover:bg-sky-700 active:scale-95 transition-all">
                        <i class="fa-solid fa-plus"></i>
                        <span>Buat Pengajuan Baru</span>
                    </a>
                @endif
            </div>
        @endforelse
    </x-mobile.scroll-list>
    </div>

    <!-- MODAL DETAIL LENGKAP PENGAJUAN -->
    @if ($showDetailModal && $selectedPengajuan)
        <div class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-3 sm:p-4 bg-slate-950/60 backdrop-blur-xs"
            x-data @keydown.escape.window="$wire.tutupDetail()">
            <div
                class="w-full max-w-[408px] bg-white rounded-3xl p-5 shadow-2xl border border-slate-200/90 max-h-[85vh] overflow-y-auto space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-2">
                        <div
                            class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-sm">
                            <i class="fa-solid fa-file-lines"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-slate-800 text-sm">Rincian Pengajuan</h3>
                            <p class="text-[13px] text-slate-400 font-medium">ID #{{ $selectedPengajuan->id }} •
                                {{ $selectedPengajuan->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    <button wire:click="tutupDetail"
                        class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center cursor-pointer transition">
                        <i class="fa-solid fa-xmark text-xs"></i>
                    </button>
                </div>

                <!-- Info Pegawai Pemohon -->
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center space-x-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-sky-600 text-white font-bold text-xs flex items-center justify-center uppercase">
                        {{ substr($selectedPengajuan->user->name ?? 'P', 0, 2) }}
                    </div>
                    <div>
                        <h4 class="font-extrabold text-slate-800 text-xs">{{ $selectedPengajuan->user->name ?? '-' }}
                        </h4>
                        <p class="text-xs text-slate-500 font-medium">
                            {{ $selectedPengajuan->user->jabatan->nama ?? 'Pegawai' }} • NIP:
                            {{ $selectedPengajuan->user->nip ?? '-' }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Jenis
                            Permohonan</span>
                        <span
                            class="font-black text-slate-800">{{ $selectedPengajuan->jenisIzin->nama ?? 'Izin' }}</span>
                    </div>

                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Durasi
                            Hari</span>
                        <span class="font-black text-slate-800">{{ $selectedPengajuan->jumlah_hari }} Hari
                            Kerja</span>
                    </div>
                </div>

                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Rentang
                        Tanggal</span>
                    <span class="font-bold text-slate-800">
                        {{ $selectedPengajuan->tanggal_mulai ? $selectedPengajuan->tanggal_mulai->translatedFormat('l, d F Y') : '-' }}
                        s.d
                        {{ $selectedPengajuan->tanggal_selesai ? $selectedPengajuan->tanggal_selesai->translatedFormat('l, d F Y') : '-' }}
                    </span>
                </div>

                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-xs">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-1">Alasan
                        Permohonan</span>
                    <p class="text-slate-700 leading-relaxed">{{ $selectedPengajuan->alasan }}</p>
                </div>

                @if ($selectedPengajuan->lampiran)
                    <div
                        class="p-3.5 rounded-2xl bg-sky-50 border border-sky-100 flex items-center justify-between text-xs">
                        <span class="text-sky-800 font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-paperclip"></i> Dokumen Bukti Pendukung
                        </span>
                        <a href="{{ asset('storage/' . $selectedPengajuan->lampiran) }}" target="_blank"
                            class="px-3 py-1 rounded-xl bg-sky-600 text-white font-extrabold text-[13px] shadow-md shadow-slate-900/5 hover:bg-sky-700">
                            Buka Berkas
                        </a>
                    </div>
                @endif

                @if ($selectedPengajuan->status !== 'menunggu')
                    <div
                        class="p-3.5 rounded-2xl {{ $selectedPengajuan->status === 'disetujui' ? 'bg-emerald-50 border-emerald-100' : 'bg-rose-50 border-rose-100' }} text-xs">
                        <span
                            class="text-xs font-bold {{ $selectedPengajuan->status === 'disetujui' ? 'text-emerald-700' : 'text-rose-700' }} uppercase tracking-wider block mb-1">
                            Status: {{ ucfirst($selectedPengajuan->status) }}
                        </span>
                        <p class="text-slate-700 font-medium">
                            Diverifikasi oleh: <strong>{{ $selectedPengajuan->approver->name ?? 'Pimpinan' }}</strong>
                        </p>
                        @if ($selectedPengajuan->catatan_atasan)
                            <p class="text-slate-600 mt-1 italic">"{{ $selectedPengajuan->catatan_atasan }}"</p>
                        @endif
                    </div>
                @endif

                @php
                    $canApproveSelected = $selectedPengajuan->status === 'menunggu'
                        && ($selectedPengajuan->user?->atasan_id === auth()->id()
                            || (auth()->user()?->hasAnyRole(['super_admin', 'admin', 'pa', 'ppk', 'pptk']) ?? false));
                @endphp

                <div class="pt-2 space-y-2">
                    @if ($canApproveSelected)
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" wire:click="bukaModalAksi({{ $selectedPengajuan->id }}, 'tolak')"
                                class="py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-extrabold text-xs transition flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-xmark"></i>
                                <span>Tolak</span>
                            </button>
                            <button type="button" wire:click="bukaModalAksi({{ $selectedPengajuan->id }}, 'setujui')"
                                class="py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-sm transition flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-check"></i>
                                <span>Setujui</span>
                            </button>
                        </div>
                    @endif

                    <button type="button" wire:click="tutupDetail"
                        class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                        Tutup Rincian
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL KONFIRMASI PERSETUJUAN / PENOLAKAN -->
    @if ($showKonfirmasiModal)
        <div class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs" x-data
            @keydown.escape.window="$wire.tutupModalAksi()">
            <div class="w-full max-w-sm bg-white rounded-3xl p-5 shadow-2xl border border-slate-200/90 space-y-4">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 rounded-2xl {{ $tipeAksi === 'setujui' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }} flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid {{ $tipeAksi === 'setujui' ? 'fa-check' : 'fa-xmark' }}"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-800 text-sm">
                            {{ $tipeAksi === 'setujui' ? 'Setujui Pengajuan Izin?' : 'Tolak Pengajuan Izin?' }}
                        </h3>
                        <p class="text-[13px] text-slate-400">
                            {{ $tipeAksi === 'setujui' ? 'Data presensi pegawai otomatis dicatat sesuai jadwal.' : 'Berikan alasan penolakan jika diperlukan.' }}
                        </p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1.5">Catatan Pimpinan (Opsional)</label>
                    <textarea wire:model="catatanAksi" rows="2" placeholder="Tuliskan catatan atau arahan dinas..."
                        class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:ring-2 focus:ring-sky-500/30"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-2">
                    <button type="button" wire:click="tutupModalAksi"
                        class="py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="button" wire:click="eksekusiAksi"
                        class="py-2.5 rounded-xl {{ $tipeAksi === 'setujui' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700' }} text-white font-extrabold text-xs shadow-md transition">
                        {{ $tipeAksi === 'setujui' ? 'Ya, Setujui' : 'Ya, Tolak' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal Preview Surat Cuti (Mobile) -->
    <div x-data="{ open: false, previewId: null, previewUrl: '' }"
         @buka-preview-cuti.window="open = true; previewId = $event.detail.id; previewUrl = $event.detail.url"
         x-show="open"
         x-cloak
         class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-3">
        <div class="bg-white rounded-2xl w-full max-w-lg h-[85vh] flex flex-col overflow-hidden shadow-2xl">
            <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between">
                <h3 class="text-sm font-extrabold text-slate-800">Preview Surat Cuti</h3>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-700 text-lg">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="flex-1 bg-slate-100">
                <iframe :src="previewUrl" class="w-full h-full border-0 bg-white"></iframe>
            </div>
            <div class="px-4 py-3 border-t border-slate-200 flex items-center justify-end gap-2 bg-white">
                <button type="button" @click="open = false; $wire.bukaModalAksi(previewId, 'tolak')"
                    class="px-3.5 py-2 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold flex items-center gap-1">
                    <i class="fa-solid fa-xmark"></i> Tolak
                </button>
                <button type="button" @click="open = false; $wire.bukaModalAksi(previewId, 'setujui')"
                    class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-xs font-extrabold flex items-center gap-1">
                    <i class="fa-solid fa-check"></i> Setujui
                </button>
            </div>
        </div>
    </div>
</div>
