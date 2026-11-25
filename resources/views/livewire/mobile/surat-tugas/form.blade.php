<div class="pb-24">
    <div
        class="sticky top-0 z-20 bg-white/90 dark:bg-zinc-950/90 backdrop-blur-md border-b border-zinc-200 dark:border-zinc-800 px-4 py-3 flex items-center gap-3">
        <a href="{{ route('mobile.surat-tugas.index') }}" wire:navigate
            class="w-9 h-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-700 dark:text-zinc-200">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-base font-bold text-zinc-900 dark:text-zinc-100">Buat Surat Tugas</h1>
            <p class="text-[11px] text-zinc-500 dark:text-zinc-400">Penugasan Tim Kreatif</p>
        </div>
    </div>

    <form wire:submit="save" class="p-4 space-y-4">
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-4 space-y-4">
            <div class="text-xs font-bold uppercase tracking-wider text-teal-600 dark:text-teal-400">Dasar Surat
                Undangan (Opsional)</div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Nomor Surat
                    Undangan</label>
                <input type="text" wire:model="dasar_surat_undangan" placeholder="Contoh: 005/123/Kominfo-2026"
                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2.5 text-sm text-zinc-900 dark:text-zinc-100">
                @error('dasar_surat_undangan')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Asal Surat</label>
                <input type="text" wire:model="asal_surat_undangan"
                    placeholder="Contoh: Sekretariat Daerah Kab. Sijunjung"
                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2.5 text-sm text-zinc-900 dark:text-zinc-100">
                @error('asal_surat_undangan')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Perihal Surat / Nama
                    Kegiatan <span class="text-rose-500">*</span></label>
                <input type="text" wire:model="perihal_surat_undangan" placeholder="Contoh: Undangan Rapat evaluasi"
                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2.5 text-sm text-zinc-900 dark:text-zinc-100">
                @error('perihal_surat_undangan')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Tanggal
                        Surat</label>
                    <x-mobile.date-picker wire:model="tanggal_surat_undangan" placeholder="Pilih tanggal..." />
                    @error('tanggal_surat_undangan')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Jam Acara</label>
                    <select wire:model="jam_acara"
                        class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2.5 text-sm text-zinc-900 dark:text-zinc-100">
                        <option value="">-- Pilih Jam --</option>
                        @foreach ($jamOptions as $jam)
                            <option value="{{ $jam }}">{{ $jam }} WIB</option>
                        @endforeach
                    </select>
                    @error('jam_acara')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Lokasi Acara / Kegiatan
                    <span class="text-rose-500">*</span></label>
                <input type="text" wire:model="lokasi" placeholder="Contoh: Geopark Silokek"
                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2.5 text-sm text-zinc-900 dark:text-zinc-100">
                @error('lokasi')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Tanggal Mulai
                        Kegiatan <span class="text-rose-500">*</span></label>
                    <x-mobile.date-picker wire:model="tanggal_mulai" placeholder="Pilih tanggal..." />
                    @error('tanggal_mulai')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Tanggal Selesai
                        Kegiatan <span class="text-rose-500">*</span></label>
                    <x-mobile.date-picker wire:model="tanggal_selesai" placeholder="Pilih tanggal..." />
                    @error('tanggal_selesai')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Upload Bukti Surat
                    Undangan</label>
                <input type="file" wire:model="bukti_surat_undangan" accept=".pdf,.jpg,.jpeg,.png,.webp"
                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2 text-xs text-zinc-700 dark:text-zinc-300">
                <p class="text-[11px] text-zinc-500 mt-1">Format: PDF, JPG, PNG, atau WEBP (maks. 5 MB).</p>
                @error('bukti_surat_undangan')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-4 space-y-4">
            <div class="text-xs font-bold uppercase tracking-wider text-teal-600 dark:text-teal-400">Detail Kegiatan &
                Tim Kreatif</div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Penanggung Jawab
                    Kegiatan Bidang <span class="text-rose-500">*</span></label>
                <div
                    class="max-h-40 overflow-y-auto space-y-1.5 rounded-xl border border-zinc-200 dark:border-zinc-800 p-2.5 bg-zinc-50 dark:bg-zinc-950">
                    @foreach ($penanggungJawabs as $pj)
                        <label
                            class="flex items-center gap-2.5 p-1.5 rounded-lg hover:bg-white dark:hover:bg-zinc-900 cursor-pointer">
                            <input type="checkbox" wire:model="penanggung_jawab_ids" value="{{ $pj->id }}"
                                class="rounded border-zinc-300 text-teal-600 focus:ring-teal-500">
                            <div class="min-w-0">
                                <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200 truncate">
                                    {{ $pj->name }}</div>
                                @if ($pj->jabatan?->nama)
                                    <div class="text-[10px] text-zinc-500 dark:text-zinc-400 truncate">
                                        {{ $pj->jabatan->nama }}</div>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('penanggung_jawab_ids')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Personil Tim
                    Kreatif</label>
                <div
                    class="max-h-48 overflow-y-auto space-y-1.5 rounded-xl border border-zinc-200 dark:border-zinc-800 p-2.5 bg-zinc-50 dark:bg-zinc-950">
                    @forelse ($timKreatif as $anggota)
                        <label
                            class="flex items-center gap-2.5 p-1.5 rounded-lg hover:bg-white dark:hover:bg-zinc-900 cursor-pointer">
                            <input type="checkbox" wire:model="pegawai_ids" value="{{ $anggota->id }}"
                                class="rounded border-zinc-300 text-teal-600 focus:ring-teal-500">
                            <div class="min-w-0">
                                <div class="text-xs font-semibold text-zinc-800 dark:text-zinc-200 truncate">
                                    {{ $anggota->name }}</div>
                                <div class="text-[10px] text-zinc-500 dark:text-zinc-400 truncate">
                                    {{ $anggota->jabatan_tim_kreatif }}</div>
                            </div>
                        </label>
                    @empty
                        <p class="text-xs text-zinc-500 text-center py-2">Belum ada data Tim Kreatif.</p>
                    @endforelse
                </div>
                @error('pegawai_ids')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1">Keterangan</label>
                <textarea wire:model="keterangan" rows="3" placeholder="Catatan tambahan..."
                    class="w-full rounded-xl border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-950 px-3 py-2.5 text-sm text-zinc-900 dark:text-zinc-100"></textarea>
                @error('keterangan')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <button type="submit" wire:loading.attr="disabled"
            class="w-full py-3 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-sm shadow-lg shadow-teal-600/20 active:scale-[0.99] transition-all flex items-center justify-center gap-2">
            <i class="fa-solid fa-paper-plane"></i>
            <span wire:loading.remove wire:target="save">Simpan Surat Tugas</span>
            <span wire:loading wire:target="save">Menyimpan...</span>
        </button>
    </form>
</div>
