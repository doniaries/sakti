<div class="p-5 pb-28">
    <div class="flex items-center mb-6">
        <a href="{{ route('mobile.lhk.index') }}" wire:navigate class="mr-3 w-9 h-9 rounded-xl bg-white dark:bg-[#132338] border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-sky-600 transition">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 dark:text-white tracking-tight">
                {{ $existingLhk ? 'Edit LHK' : 'Buat LHK' }}
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Laporan Harian Kinerja Pegawai</p>
        </div>
    </div>

    <!-- Info Kehadiran -->
    <div class="bg-white dark:bg-[#132338]/90 rounded-2xl p-5 shadow-xs border border-slate-200/80 dark:border-slate-700/60 mb-5">
        <h2 class="text-sm font-bold text-slate-800 dark:text-white mb-4 border-b border-slate-100 dark:border-slate-700/60 pb-2">Informasi Kehadiran</h2>
        
        <div class="mb-4">
            <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Tanggal LHK</label>
            <input type="date" wire:model.live="tanggal" class="w-full bg-[#F5F7FA] dark:bg-[#0F1E33] border border-slate-200/70 dark:border-slate-700 rounded-xl p-3 text-sm text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-[#1B6CA8] transition">
        </div>

        @if(!$absenHariIni && !$existingLhk)
            <div class="bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-300 p-3 rounded-xl text-xs flex items-center border border-rose-200 dark:border-rose-500/30 font-semibold">
                <i class="fa-solid fa-triangle-exclamation mr-2 shrink-0"></i>
                <span>Belum ada data absensi masuk (pagi) pada tanggal ini.</span>
            </div>
        @else
            <div class="flex space-x-3">
                <div class="flex-1 bg-amber-500/10 border border-amber-500/20 rounded-xl p-3 flex flex-col items-center">
                    <span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold uppercase mb-1">Jam Masuk</span>
                    <span class="text-lg font-bold font-mono text-amber-600 dark:text-amber-300">
                        {{ ($absenHariIni?->jam_masuk ?? $existingLhk?->jam_masuk) ? substr((string) ($absenHariIni?->jam_masuk ?? $existingLhk?->jam_masuk), 0, 5) : '-' }}
                    </span>
                </div>
                <div class="flex-1 bg-emerald-500/10 border border-emerald-500/20 rounded-xl p-3 flex flex-col items-center">
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold uppercase mb-1">Jam Pulang</span>
                    <span class="text-lg font-bold font-mono text-emerald-600 dark:text-emerald-300">
                        {{ ($absenHariIni?->jam_pulang ?? $existingLhk?->jam_pulang) ? substr((string) ($absenHariIni?->jam_pulang ?? $existingLhk?->jam_pulang), 0, 5) : 'Belum Pulang' }}
                    </span>
                </div>
            </div>

            @if(!$bisaIsiLhk)
                <div class="bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-300 p-3 rounded-xl text-xs mt-3 flex items-center border border-rose-200 dark:border-rose-500/30 font-semibold">
                    <i class="fa-solid fa-triangle-exclamation mr-2 shrink-0"></i>
                    <span>Anda harus melakukan absen masuk (pagi) terlebih dahulu untuk mengisi LHK.</span>
                </div>
            @else
                <div class="bg-sky-50 dark:bg-sky-500/10 text-sky-700 dark:text-cyan-300 p-3 rounded-xl text-[11px] mt-3 flex items-start gap-2 border border-sky-200 dark:border-sky-500/30 font-medium leading-relaxed">
                    <i class="fa-solid fa-clock-rotate-left mt-0.5 shrink-0"></i>
                    <span>
                        Waktu awal kegiatan dimulai pukul <strong class="font-mono">{{ $waktuAwalKegiatan }} WIB</strong> (jam masuk resmi jika tidak terlambat, atau jam absensi jika terlambat). Pilih <strong>Aktivitas</strong> terlebih dahulu agar jam terisi otomatis.
                    </span>
                </div>
                @if(!($absenHariIni?->jam_pulang ?? $existingLhk?->jam_pulang))
                    <div class="bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 p-3 rounded-xl text-xs mt-2 flex items-center border border-emerald-200 dark:border-emerald-500/30 font-semibold">
                        <i class="fa-solid fa-circle-check mr-2 shrink-0"></i>
                        <span>Sudah absen pagi. Anda sudah dapat mengisi LHK sekarang walau belum absen pulang.</span>
                    </div>
                @endif
            @endif
        @endif
    </div>

    <!-- Rincian Pekerjaan -->
    <div class="bg-white dark:bg-[#132338]/90 rounded-2xl p-5 shadow-xs border border-slate-200/80 dark:border-slate-700/60 mb-6">
        <div class="flex justify-between items-center mb-4 border-b border-slate-100 dark:border-slate-700/60 pb-2">
            <h2 class="text-sm font-bold text-slate-800 dark:text-white">Rincian Pekerjaan</h2>
            <button type="button" wire:click="addPekerjaan" class="text-xs font-semibold text-[#1B6CA8] dark:text-cyan-300 bg-[#1B6CA8]/10 dark:bg-cyan-500/15 px-3 py-1.5 rounded-full hover:bg-[#1B6CA8]/20 transition">
                <i class="fa-solid fa-plus mr-1"></i> Tambah
            </button>
        </div>

        @if(empty($pekerjaans))
            <div class="text-center py-6 text-slate-400 text-sm">
                Belum ada kegiatan. Tap tombol Tambah.
            </div>
        @else
            <div class="space-y-4">
                @foreach($pekerjaans as $index => $pekerjaan)
                    <div wire:key="pekerjaan-{{ $index }}" class="bg-[#F8FAFC] dark:bg-[#0F1C2E] border border-slate-200 dark:border-slate-700/70 p-4 rounded-xl relative">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs font-bold text-[#1B6CA8] dark:text-cyan-300 bg-[#1B6CA8]/10 dark:bg-cyan-500/15 px-2.5 py-1 rounded-lg">Kegiatan {{ $index + 1 }}</span>
                            <button type="button" wire:click="removePekerjaan({{ $index }})" class="text-rose-400 hover:text-rose-600 transition">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                        
                        <div class="mb-3">
                            <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">1. Rencana Aksi</label>
                            <select wire:model="pekerjaans.{{ $index }}.rencana_aksi_id" class="w-full bg-white dark:bg-[#132338] border border-slate-200 dark:border-slate-700 rounded-lg p-2 text-sm focus:ring-1 focus:ring-[#1B6CA8]">
                                <option value="1">Diluar Sasaran Kinerja</option>
                                <option value="2">Melaksanakan Tugas Lain yang diberikan Pimpinan</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400">2. Pilih Aktivitas (Bank Aktivitas)</label>
                                <a href="{{ route('mobile.lhk.bank-aktivitas') }}" wire:navigate class="text-[10px] font-bold text-sky-600 dark:text-cyan-400 hover:underline">
                                    + Kelola Bank Aktivitas
                                </a>
                            </div>
                            <select wire:model.live="pekerjaans.{{ $index }}.aktivitas_id" class="w-full bg-white dark:bg-[#132338] border border-slate-200 dark:border-slate-700 rounded-lg p-2 text-sm focus:ring-1 focus:ring-[#1B6CA8]">
                                <option value="">- Pilih Aktivitas (Jam Terisi Otomatis) -</option>
                                @foreach($aktivitasList as $a)
                                    <option value="{{ $a->id }}">{{ $a->nama_aktivitas ?? $a->nama }}{{ $a->menit ? ' (' . $a->menit . ' menit)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-3 gap-2 mb-3">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Jam Mulai</label>
                                <input type="time" wire:model.live="pekerjaans.{{ $index }}.jam_mulai" class="w-full bg-white dark:bg-[#132338] border border-slate-200 dark:border-slate-700 rounded-lg p-2 text-xs font-mono focus:ring-1 focus:ring-[#1B6CA8]">
                                @error('pekerjaans.'.$index.'.jam_mulai') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Jam Selesai</label>
                                <input type="time" wire:model.live="pekerjaans.{{ $index }}.jam_selesai" class="w-full bg-white dark:bg-[#132338] border border-slate-200 dark:border-slate-700 rounded-lg p-2 text-xs font-mono focus:ring-1 focus:ring-[#1B6CA8]">
                                @error('pekerjaans.'.$index.'.jam_selesai') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">Lama</label>
                                <div class="w-full bg-slate-100 dark:bg-[#132338]/70 border border-slate-200 dark:border-slate-700 rounded-lg p-2 text-xs font-mono font-bold text-sky-700 dark:text-cyan-300 text-center">
                                    {{ (int) ($pekerjaan['lama_menit'] ?? 0) }} mnt
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">3. Hasil Pekerjaan</label>
                            <textarea wire:model="pekerjaans.{{ $index }}.hasil_pekerjaan" rows="2" placeholder="Uraikan hasil pekerjaan yang dilaksanakan..." class="w-full bg-white dark:bg-[#132338] border border-slate-200 dark:border-slate-700 rounded-lg p-2 text-sm focus:ring-1 focus:ring-[#1B6CA8]"></textarea>
                            @error('pekerjaans.'.$index.'.hasil_pekerjaan') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 dark:text-slate-400 mb-1">4. Link Bukti Google Drive (Evidence)</label>
                            <input type="url" wire:model="pekerjaans.{{ $index }}.bukti_url" placeholder="https://drive.google.com/..." class="w-full bg-white dark:bg-[#132338] border border-slate-200 dark:border-slate-700 rounded-lg p-2 text-sm focus:ring-1 focus:ring-[#1B6CA8]">
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Simpan -->
    <button type="button" wire:click="save" wire:loading.attr="disabled" @disabled(!$bisaIsiLhk)
        class="w-full bg-[#1B6CA8] hover:bg-[#155685] disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-4 rounded-xl shadow-lg transition flex justify-center items-center">
        <span wire:loading.remove>Simpan LHK</span>
        <span wire:loading><i class="fa-solid fa-circle-notch fa-spin mr-1.5"></i> Menyimpan...</span>
    </button>
</div>
