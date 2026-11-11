<x-filament-panels::page>
    <div class="max-w-4xl space-y-6">
        <div class="p-6 bg-white dark:bg-zinc-900 rounded-2xl shadow-sm border border-slate-200/80 dark:border-zinc-800">
            <div class="flex items-start space-x-3.5 mb-5 pb-4 border-b border-slate-100 dark:border-zinc-800">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-calendar-check"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800 dark:text-zinc-100">Konfigurasi Tahun Anggaran Aktif</h3>
                    <p class="text-xs text-slate-500 dark:text-zinc-400 mt-0.5">
                        Tahun anggaran global yang berlaku untuk seluruh transaksi keuangan kedinasan, penyusunan RKA/DPA, verifikasi, dan pencairan SPJ.
                    </p>
                </div>
            </div>

            <form wire:submit="simpan" class="space-y-5">
                <div class="max-w-md">
                    <label class="block text-xs font-bold text-slate-700 dark:text-zinc-200 uppercase tracking-wider mb-2">
                        Tahun Anggaran Aktif <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-regular fa-calendar-days text-sm"></i>
                        </div>
                        <input type="number" wire:model="tahun_anggaran_aktif" min="2020" max="2099" required
                            class="w-full pl-9 pr-4 py-2.5 bg-slate-50 dark:bg-zinc-800/80 border border-slate-300 dark:border-zinc-700 rounded-xl text-slate-800 dark:text-zinc-100 font-bold text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition shadow-inner">
                    </div>
                    @error('tahun_anggaran_aktif')
                        <p class="text-xs text-rose-500 mt-1.5 font-medium">{{ $message }}</p>
                    @enderror
                    <p class="text-[11px] text-slate-400 dark:text-zinc-500 mt-2">
                        <i class="fa-solid fa-circle-info mr-1 text-sky-500"></i>
                        Modul DPA, RKA, Nota Pesanan, Kwitansi, dan Nota Pencairan Dana (NPD) otomatis terkunci dan mengacu ke tahun ini.
                    </p>
                </div>

                <div class="pt-4 border-t border-slate-100 dark:border-zinc-800 flex items-center justify-between">
                    <span class="text-xs text-slate-400">
                        Status saat ini: <strong class="text-amber-600 dark:text-amber-400">Tahun {{ $tahun_anggaran_aktif }}</strong>
                    </span>
                    <button type="submit" wire:loading.attr="disabled"
                        class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-extrabold text-xs shadow-md shadow-amber-500/20 active:scale-95 transition-all flex items-center space-x-2 cursor-pointer disabled:opacity-50">
                        <span wire:loading.remove><i class="fa-solid fa-floppy-disk mr-1.5"></i> Simpan Pengaturan Keuangan</span>
                        <span wire:loading><i class="fa-solid fa-circle-notch fa-spin mr-1.5"></i> Menyimpan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-filament-panels::page>
