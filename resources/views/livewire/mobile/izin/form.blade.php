<div class="p-5 pb-28" x-data="{ activeTab: 'pengajuan' }">
    <!-- Header Back Navigation -->
    <div class="flex items-center space-x-3 mb-6">
        <a href="{{ route('mobile.izin.index') }}" wire:navigate
            class="w-10 h-10 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-center text-slate-600 hover:text-slate-800 active:scale-95 transition-all">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Form Pengajuan</h1>
            <p class="text-xs text-slate-500 font-medium">Izin, Sakit & Cuti Pegawai</p>
        </div>
    </div>

    <!-- Category Filter Selector (Izin, Sakit, Cuti) -->
    <div class="mb-5">
        <label class="block text-xs font-bold text-slate-700 mb-2">Kategori Pengajuan</label>
        @php
            $countByKat = fn (string $kat) => $riwayatPengajuan->filter(fn ($p) => ($p->jenisIzin?->kategori?->value ?? $p->jenisIzin?->kategori) === $kat)->count();
        @endphp
        <div class="grid grid-cols-3 gap-2 w-full">
            <button type="button" wire:click="setKategori('izin')"
                class="relative py-2.5 px-2 rounded-2xl border text-xs font-bold transition-all flex flex-col items-center shadow-xs {{ $kategoriFilter === 'izin' ? 'bg-sky-100/90 border-sky-500 text-sky-800 ring-2 ring-sky-500/20' : 'bg-sky-50/60 border-sky-200/70 text-sky-700' }}">
                <span class="absolute top-1.5 right-1.5 px-1.5 py-0.2 rounded-full bg-sky-600 text-white text-[9px] font-extrabold">{{ $countByKat('izin') }}</span>
                <i class="fa-solid fa-envelope-open-text mb-1 text-sm text-sky-600"></i>
                <span>Izin</span>
            </button>
            <button type="button" wire:click="setKategori('sakit')"
                class="relative py-2.5 px-2 rounded-2xl border text-xs font-bold transition-all flex flex-col items-center shadow-xs {{ $kategoriFilter === 'sakit' ? 'bg-rose-100/90 border-rose-500 text-rose-800 ring-2 ring-rose-500/20' : 'bg-rose-50/60 border-rose-200/70 text-rose-700' }}">
                <span class="absolute top-1.5 right-1.5 px-1.5 py-0.2 rounded-full bg-rose-600 text-white text-[9px] font-extrabold">{{ $countByKat('sakit') }}</span>
                <i class="fa-solid fa-notes-medical mb-1 text-sm text-rose-600"></i>
                <span>Sakit</span>
            </button>
            <button type="button" wire:click="setKategori('cuti')"
                class="relative py-2.5 px-2 rounded-2xl border text-xs font-bold transition-all flex flex-col items-center shadow-xs {{ $kategoriFilter === 'cuti' ? 'bg-amber-100/90 border-amber-500 text-amber-800 ring-2 ring-amber-500/20' : 'bg-amber-50/60 border-amber-200/70 text-amber-700' }}">
                <span class="absolute top-1.5 right-1.5 px-1.5 py-0.2 rounded-full bg-amber-600 text-white text-[9px] font-extrabold">{{ $countByKat('cuti') }}</span>
                <i class="fa-solid fa-umbrella-beach mb-1 text-sm text-amber-600"></i>
                <span>Cuti</span>
            </button>
        </div>
    </div>

    <!-- Tab Switcher (Buat Pengajuan vs Daftar & Surat PDF) -->
    <div class="grid grid-cols-2 gap-1.5 p-1 mb-5 bg-slate-100 rounded-2xl border border-slate-200/70">
        <button type="button" @click="activeTab = 'pengajuan'"
            :class="activeTab === 'pengajuan' ? 'bg-white text-slate-800 shadow-xs' : 'text-slate-500 hover:text-slate-700'"
            class="py-2 px-3 rounded-xl text-xs font-extrabold transition-all flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-pen-to-square"></i>
            <span>Buat Pengajuan</span>
        </button>
        <button type="button" @click="activeTab = 'daftar'"
            :class="activeTab === 'daftar' ? 'bg-white text-slate-800 shadow-xs' : 'text-slate-500 hover:text-slate-700'"
            class="py-2 px-3 rounded-xl text-xs font-extrabold transition-all flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-file-pdf text-rose-500"></i>
            <span>Daftar Pengajuan</span>
            <span
                class="px-1.5 py-0.5 rounded-full text-[10px] bg-sky-100 text-sky-700">{{ $riwayatPengajuan->count() }}</span>
        </button>
    </div>

    <!-- Daftar Pengajuan Izin, Cuti & Sakit + PDF -->
    <div x-show="activeTab === 'daftar'" x-cloak class="mb-5 space-y-3">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-sm font-extrabold text-slate-800">Daftar Izin, Cuti & Sakit</h2>
            <a href="{{ route('mobile.izin.index') }}" class="text-xs font-bold text-sky-600 hover:underline">Lihat
                Semua</a>
        </div>

        @forelse($riwayatPengajuan as $item)
            @php
                $kat = $item->jenisIzin?->kategori ?? 'izin';
                $katBadge = match ($kat) {
                    'cuti' => 'bg-amber-50 text-amber-700 border-amber-200',
                    'sakit' => 'bg-rose-50 text-rose-700 border-rose-200',
                    default => 'bg-sky-50 text-sky-700 border-sky-200',
                };
                $statusBadge = match ($item->status) {
                    'disetujui' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    'ditolak' => 'bg-rose-50 text-rose-700 border-rose-200',
                    default => 'bg-amber-50 text-amber-700 border-amber-200',
                };
            @endphp
            <div class="bg-white rounded-2xl p-3.5 shadow-sm border border-slate-100 space-y-2.5">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span
                                class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase border {{ $katBadge }}">
                                {{ strtoupper($kat) }}
                            </span>
                            <span class="text-xs font-bold text-slate-800">
                                {{ $item->jenisIzin?->nama ?? 'Pengajuan' }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">
                            {{ $item->tanggal_mulai?->format('d/m/Y') ?? '-' }}
                            @if ($item->tanggal_selesai && $item->tanggal_selesai->ne($item->tanggal_mulai))
                                - {{ $item->tanggal_selesai->format('d/m/Y') }}
                            @endif
                            • <span class="font-semibold text-slate-700">{{ $item->jumlah_hari }} Hari</span>
                        </p>
                    </div>
                    <span
                        class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase border {{ $statusBadge }} shrink-0">
                        {{ ucfirst($item->status) }}
                    </span>
                </div>

                @if ($item->alasan)
                    <p class="text-xs text-slate-600 line-clamp-2">{{ $item->alasan }}</p>
                @endif

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2 text-[11px]">
                    <a href="{{ route('pengajuan-izin.cetak-pdf', ['pengajuanIzin' => $item, 'preview' => 1]) }}"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 font-bold hover:bg-rose-100 transition-colors">
                        <i class="fa-solid fa-file-pdf"></i>
                        <span>{{ $item->status === 'menunggu' ? 'Draft Surat PDF' : 'Lihat Surat PDF' }}</span>
                    </a>

                    @if ($item->lampiran)
                        <a href="{{ asset('storage/' . $item->lampiran) }}" target="_blank"
                            class="inline-flex items-center gap-1 text-sky-600 font-bold hover:underline">
                            <i class="fa-solid fa-paperclip"></i>
                            <span>Bukti Lampiran</span>
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl p-4 text-center text-xs text-slate-400 border border-slate-100">
                Belum ada riwayat pengajuan pada kategori ini.
            </div>
        @endforelse
    </div>

    <!-- Tab: Form Pengajuan -->
    <div x-show="activeTab === 'pengajuan'">
        <!-- Informasi Kuota Cuti Pegawai (Jika Kategori Cuti) -->
        @if ($kategoriFilter === 'cuti')
            <div
                class="mb-5 bg-gradient-to-br from-amber-500 via-amber-600 to-orange-600 rounded-3xl p-4 text-white shadow-lg shadow-amber-600/20 relative overflow-hidden">
                <div class="absolute -right-4 -bottom-4 text-white/10 text-7xl pointer-events-none">
                    <i class="fa-solid fa-umbrella-beach"></i>
                </div>
                <div class="flex items-center justify-between mb-3 relative z-10">
                    <span class="text-xs font-black uppercase tracking-wider text-amber-100 flex items-center">
                        <i class="fa-solid fa-id-card-clip mr-1.5"></i> Kuota Cuti Pegawai ({{ date('Y') }})
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-white/20 backdrop-blur-xs">
                        {{ $sisaCuti > 0 ? 'Tersedia' : 'Habis' }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-2.5 relative z-10 text-center">
                    <div class="bg-black/20 backdrop-blur-xs rounded-2xl p-2.5 border border-white/10">
                        <span class="text-[10px] text-amber-100 block font-semibold">Terpakai</span>
                        <span class="text-lg font-black tracking-tight text-white">{{ $cutiTerpakai }}</span>
                        <span class="text-[9px] text-amber-200 block">Hari</span>
                    </div>
                    <div class="bg-white text-amber-900 rounded-2xl p-2.5 shadow-md">
                        <span class="text-[10px] text-amber-700 block font-extrabold">Sisa Kuota</span>
                        <span class="text-lg font-black tracking-tight text-amber-800">{{ $sisaCuti }}</span>
                        <span class="text-[9px] text-amber-600 block font-bold">Hari Lagi</span>
                    </div>
                </div>
                @if ($sisaCuti <= 0)
                    <div class="mt-3 p-2 bg-rose-500/90 rounded-xl text-[11px] font-bold text-white flex items-center">
                        <i class="fa-solid fa-triangle-exclamation mr-1.5"></i>
                        <span>Kuota cuti tahunan Anda telah habis untuk tahun ini.</span>
                    </div>
                @elseif($jumlah_hari > $sisaCuti)
                    <div class="mt-3 p-2 bg-rose-500/90 rounded-xl text-[11px] font-bold text-white flex items-center">
                        <i class="fa-solid fa-triangle-exclamation mr-1.5"></i>
                        <span>Pengajuan ({{ $jumlah_hari }} hari) melebihi sisa kuota ({{ $sisaCuti }}
                            hari).</span>
                    </div>
                @endif
            </div>
        @endif

        <!-- Form Card -->
        <form wire:submit="save" class="bg-white rounded-3xl p-5 border border-slate-100 shadow-xs space-y-4">
            <!-- Error Banner -->
            @if ($errors->any())
                <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                    <div class="font-bold flex items-center mb-1">
                        <i class="fa-solid fa-circle-exclamation mr-1.5"></i> Mohon lengkapi isian form:
                    </div>
                    <ul class="list-disc list-inside space-y-0.5 text-[11px] text-rose-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Jenis Izin / Cuti Select -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Jenis Permohonan <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <select wire:model.live="jenis_izin_id"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all appearance-none">
                        <option value="">-- Pilih Jenis --</option>
                        @foreach ($jenisIzins as $ji)
                            <option value="{{ $ji->id }}">
                                {{ $ji->nama }}
                                ({{ ucfirst($ji->kategori) }}{{ $ji->kuota_hari ? ' - Max ' . $ji->kuota_hari . ' Hari' : '' }})
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>
                @if ($selectedJenis)
                    <div class="mt-1.5 flex items-center justify-between text-[11px] text-slate-500">
                        <span>
                            <i class="fa-solid fa-circle-info text-sky-500 mr-1"></i>
                            {{ $selectedJenis->keterangan ?: 'Kategori: ' . ucfirst($selectedJenis->kategori) }}
                        </span>
                        @if ($selectedJenis->butuh_lampiran)
                            <span class="text-rose-600 font-bold"><i class="fa-solid fa-paperclip mr-0.5"></i> Wajib
                                Bukti</span>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Tanggal Mulai & Tanggal Selesai Grid -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Tanggal Mulai <span class="text-rose-500">*</span>
                    </label>
                    <x-mobile.date-picker
                        wire:model.live="tanggal_mulai"
                        :min="now()->format('Y-m-d')"
                        buttonClass="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
                    />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Tanggal Selesai <span class="text-rose-500">*</span>
                    </label>
                    <x-mobile.date-picker
                        wire:model.live="tanggal_selesai"
                        :min="$tanggal_mulai && $tanggal_mulai >= now()->format('Y-m-d') ? $tanggal_mulai : now()->format('Y-m-d')"
                        buttonClass="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all"
                    />
                </div>
            </div>

            <!-- Summary Hari Kerja Badge -->
            <div class="bg-sky-50/70 border border-sky-100 rounded-2xl p-3 flex items-center justify-between">
                <div class="flex items-center space-x-2 text-sky-700">
                    <i class="fa-regular fa-calendar-check text-base"></i>
                    <span class="text-xs font-semibold">Total Hari Kerja Diambil</span>
                </div>
                <span class="px-2.5 py-1 rounded-xl bg-sky-600 text-white font-extrabold text-xs shadow-xs">
                    {{ $jumlah_hari }} Hari Kerja
                </span>
            </div>

            <!-- Alasan Pengajuan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Alasan / Keperluan <span class="text-rose-500">*</span>
                </label>
                <textarea wire:model="alasan" rows="3" placeholder="Tuliskan keterangan detail alasan permohonan..."
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all leading-relaxed"></textarea>
                @error('alasan')
                    <span class="text-[11px] text-rose-500">{{ $message }}</span>
                @enderror
            </div>

            <!-- Upload Lampiran / Bukti Dokumen (Hanya untuk Sakit dg Surat Dokter atau Cuti khusus) -->
            @if ($kategoriFilter === 'izin')
                <!-- Izin tidak memerlukan dokumen surat -->
                <div
                    class="p-3 bg-slate-50/80 border border-slate-200/80 rounded-2xl flex items-center space-x-2.5 text-slate-500 text-xs">
                    <i class="fa-solid fa-circle-check text-emerald-500 text-sm shrink-0"></i>
                    <span class="text-[11px] leading-tight">Pengajuan Izin tidak memerlukan lampiran berkas dokumen /
                        surat.</span>
                </div>
            @elseif($kategoriFilter === 'sakit' && !$selectedJenis?->butuh_lampiran)
                <!-- Sakit Tanpa Surat Dokter -->
                <div
                    class="p-3 bg-rose-50/60 border border-rose-200/80 rounded-2xl flex items-center space-x-2.5 text-rose-700 text-xs">
                    <i class="fa-solid fa-notes-medical text-rose-500 text-sm shrink-0"></i>
                    <span class="text-[11px] leading-tight">Kategori Sakit tanpa surat dokter tidak memerlukan berkas
                        surat keterangan dokter.</span>
                </div>
            @elseif($selectedJenis?->butuh_lampiran || $kategoriFilter === 'sakit')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Surat Keterangan Dokter / Bukti Pendukung <span class="text-rose-500">* (Wajib)</span>
                    </label>

                    <div
                        class="border-2 border-dashed border-slate-200 rounded-2xl p-4 text-center hover:border-sky-400 transition-all bg-slate-50/30">
                        <input type="file" wire:model="lampiran" id="lampiran" class="hidden"
                            accept=".pdf,.jpg,.jpeg,.png">
                        <label for="lampiran" class="cursor-pointer block">
                            <div
                                class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center mx-auto mb-2 text-base">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-700 block">Pilih Berkas / Foto Surat
                                Dokter</span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">Format PDF, JPG, PNG (Maks.
                                2MB)</span>
                        </label>

                        <!-- Loading State Livewire -->
                        <div wire:loading wire:target="lampiran"
                            class="mt-2 text-xs font-bold text-sky-600 flex items-center justify-center space-x-1.5">
                            <i class="fa-solid fa-circle-notch fa-spin"></i>
                            <span>Mengunggah berkas...</span>
                        </div>

                        <!-- Preview File Uploaded -->
                        @if ($lampiran)
                            <div
                                class="mt-3 p-2 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-xs text-emerald-800 font-semibold">
                                <div class="flex items-center space-x-2 truncate">
                                    <i class="fa-solid fa-file-check text-emerald-600"></i>
                                    <span class="truncate">{{ $lampiran->getClientOriginalName() }}</span>
                                </div>
                                <span class="text-[10px] bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-md">Siap
                                    Diunggah</span>
                            </div>
                        @endif
                    </div>
                    @error('lampiran')
                        <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            @endif

            <!-- Submit Button -->
            <div class="pt-2">
                @php
                    $isCutiOverQuota = $kategoriFilter === 'cuti' && ($sisaCuti <= 0 || $jumlah_hari > $sisaCuti);
                @endphp
                <button type="submit" wire:loading.attr="disabled" @if ($isCutiOverQuota) disabled @endif
                    class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-sky-600 to-sky-500 hover:from-sky-700 hover:to-sky-600 text-white font-extrabold text-sm shadow-lg shadow-sky-500/25 active:scale-98 transition-all flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none">
                    <span wire:loading.remove wire:target="save">
                        <i class="fa-solid fa-paper-plane mr-1.5"></i>
                        {{ $isCutiOverQuota ? 'Melebihi Sisa Kuota Cuti' : 'Kirim Pengajuan' }}
                    </span>
                    <span wire:loading wire:target="save"><i class="fa-solid fa-circle-notch fa-spin mr-1.5"></i>
                        Memproses Pengajuan...</span>
                </button>
            </div>
        </form>
    </div>
</div>
