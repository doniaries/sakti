<div class="pb-28">
    <div class="sticky top-0 z-20 bg-white/90 dark:bg-zinc-950/90 backdrop-blur-md border-b border-zinc-200/80 dark:border-zinc-800 px-4 py-3 flex items-center gap-3">
        <a href="{{ route('mobile.agenda') }}" wire:navigate
            class="w-9 h-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-zinc-700 dark:text-zinc-200 active:scale-95 transition-transform">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div class="min-w-0">
            <h1 class="text-base font-bold text-zinc-900 dark:text-zinc-100 truncate">Tambah Agenda Kegiatan</h1>
            <p class="text-[13px] text-zinc-500 dark:text-zinc-400">Lengkapi jadwal kegiatan &amp; disposisi pegawai</p>
        </div>
    </div>

    <form wire:submit="simpanAgenda" class="p-4 space-y-4">
        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 p-4 space-y-4 shadow-xs">
            <div class="flex items-center gap-2.5 pb-2 border-b border-zinc-100 dark:border-zinc-800">
                <span class="w-6 h-6 rounded-full bg-sky-600 text-white text-xs font-bold flex items-center justify-center shrink-0">1</span>
                <div>
                    <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Informasi Agenda</h2>
                    <p class="text-[13px] text-zinc-500 dark:text-zinc-400">Detail waktu, tempat, dan penyelenggara kegiatan</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Judul Agenda <span class="text-rose-500">*</span></label>
                <input type="text" wire:model="judul" placeholder="Contoh: Rapat Koordinasi..."
                    class="w-full h-11 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-950 px-3.5 text-sm text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-zinc-900 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none transition-all">
                @error('judul') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Tanggal <span class="text-rose-500">*</span></label>
                    <x-mobile.date-picker wire:model="tanggal"
                        buttonClass="w-full h-11 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-950 px-3 text-sm text-zinc-900 dark:text-zinc-100 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none" />
                </div>
                <div class="grid grid-cols-2 sm:col-span-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Mulai <span class="text-rose-500">*</span></label>
                        <x-mobile.time-picker wire:model="jam_mulai"
                            buttonClass="w-full h-11 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-950 px-3 text-sm text-zinc-900 dark:text-zinc-100 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Selesai <span class="text-rose-500">*</span></label>
                        <x-mobile.time-picker wire:model="jam_selesai"
                            buttonClass="w-full h-11 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-950 px-3 text-sm text-zinc-900 dark:text-zinc-100 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none" />
                        @error('jam_selesai') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Tempat <span class="text-rose-500">*</span></label>
                <input type="text" wire:model="tempat" placeholder="Lokasi kegiatan..."
                    class="w-full h-11 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-950 px-3.5 text-sm text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-zinc-900 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none transition-all">
                @if ($riwayatTempat->isNotEmpty())
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach ($riwayatTempat as $lokasiItem)
                            <button type="button" wire:click="$set('tempat', {{ \Illuminate\Support\Js::from($lokasiItem) }})"
                                class="px-2.5 py-1 rounded-lg border text-xs font-medium transition cursor-pointer {{ $tempat === $lokasiItem ? 'bg-sky-50 dark:bg-sky-500/15 border-sky-300 dark:border-sky-500/40 text-sky-700 dark:text-sky-300' : 'bg-zinc-50 dark:bg-zinc-800/70 border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100' }}">
                                {{ $lokasiItem }}
                            </button>
                        @endforeach
                    </div>
                @endif
                @error('tempat') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Asal Undangan / Penyelenggara <span class="text-rose-500">*</span></label>
                <input type="text" wire:model="asal_undangan" placeholder="Nama instansi / penyelenggara..."
                    class="w-full h-11 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-950 px-3.5 text-sm text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-zinc-900 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none transition-all">
                @error('asal_undangan') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 p-4 space-y-4 shadow-xs">
            <div class="flex items-center gap-2.5 pb-2 border-b border-zinc-100 dark:border-zinc-800">
                <span class="w-6 h-6 rounded-full bg-sky-600 text-white text-xs font-bold flex items-center justify-center shrink-0">2</span>
                <div>
                    <h2 class="text-sm font-bold text-zinc-900 dark:text-zinc-100">Disposisi &amp; Lampiran</h2>
                    <p class="text-[13px] text-zinc-500 dark:text-zinc-400">Pilih pegawai yang ditugaskan serta surat undangan</p>
                </div>
            </div>

            <div>
                <x-mobile.multi-select
                    wire:model="disposisi_user_ids"
                    label="Pegawai yang Diperintah (Disposisi)"
                    :required="true"
                    :items="$pegawais"
                    searchPlaceholder="Ketik nama atau jabatan untuk mencari..."
                />
                @error('disposisi_user_ids') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Instruksi Disposisi</label>
                <input type="text" wire:model="instruksi" placeholder="Contoh: Hadir mewakili Kadis..."
                    class="w-full h-11 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-950 px-3.5 text-sm text-zinc-900 dark:text-zinc-100 focus:bg-white dark:focus:bg-zinc-900 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 outline-none transition-all">
                @if ($riwayatInstruksi->isNotEmpty())
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach ($riwayatInstruksi as $instruksiItem)
                            <button type="button" wire:click="$set('instruksi', {{ \Illuminate\Support\Js::from($instruksiItem) }})"
                                class="px-2.5 py-1 rounded-lg border text-xs font-medium transition cursor-pointer {{ $instruksi === $instruksiItem ? 'bg-sky-50 dark:bg-sky-500/15 border-sky-300 dark:border-sky-500/40 text-sky-700 dark:text-sky-300' : 'bg-zinc-50 dark:bg-zinc-800/70 border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-100' }}">
                                {{ $instruksiItem }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <label class="block text-xs font-semibold text-zinc-700 dark:text-zinc-300 mb-1.5">Foto Surat / PDF (Maks. 5MB)</label>
                <input type="file" wire:model="lampiran_file" accept="image/jpeg,image/png,image/webp,application/pdf"
                    class="w-full text-xs text-zinc-600 dark:text-zinc-300 file:mr-3 file:py-2 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100">
                <div wire:loading wire:target="lampiran_file" class="text-xs text-sky-600 mt-1">Mengunggah lampiran...</div>
                @error('lampiran_file') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <button type="submit" wire:loading.attr="disabled"
            class="w-full h-12 rounded-2xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-bold shadow-md active:scale-[0.98] transition cursor-pointer flex items-center justify-center gap-2">
            <i wire:loading wire:target="simpanAgenda" class="fa-solid fa-spinner fa-spin"></i>
            <span>Simpan Agenda</span>
        </button>
    </form>
</div>
