<div class="p-5 pb-28">
    <!-- Header Back Navigation -->
    <div class="flex items-center space-x-3 mb-6">
        <a href="{{ route('mobile.izin.index') }}" wire:navigate 
           class="w-10 h-10 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-center text-slate-600 hover:text-slate-800 active:scale-95 transition-all">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-xl font-extrabold text-slate-800 tracking-tight">Form Pengajuan</h1>
            <p class="text-xs text-slate-500 font-medium">Izin, Sakit, Cuti & WFH Pegawai</p>
        </div>
    </div>

    <!-- Category Filter Selector -->
    <div class="mb-5">
        <label class="block text-xs font-bold text-slate-700 mb-2">Kategori Pengajuan</label>
        <div class="grid grid-cols-4 gap-2">
            <button type="button" wire:click="setKategori('izin')"
                    class="py-2.5 px-2 rounded-2xl border text-xs font-bold transition-all flex flex-col items-center shadow-xs {{ $kategoriFilter === 'izin' ? 'bg-sky-50 border-sky-500 text-sky-700 ring-2 ring-sky-500/20' : 'bg-white border-slate-200 text-slate-600' }}">
                <i class="fa-solid fa-envelope-open-text mb-1 text-sm {{ $kategoriFilter === 'izin' ? 'text-sky-600' : 'text-slate-400' }}"></i>
                <span>Izin</span>
            </button>
            <button type="button" wire:click="setKategori('sakit')"
                    class="py-2.5 px-2 rounded-2xl border text-xs font-bold transition-all flex flex-col items-center shadow-xs {{ $kategoriFilter === 'sakit' ? 'bg-rose-50 border-rose-500 text-rose-700 ring-2 ring-rose-500/20' : 'bg-white border-slate-200 text-slate-600' }}">
                <i class="fa-solid fa-notes-medical mb-1 text-sm {{ $kategoriFilter === 'sakit' ? 'text-rose-600' : 'text-slate-400' }}"></i>
                <span>Sakit</span>
            </button>
            <button type="button" wire:click="setKategori('cuti')"
                    class="py-2.5 px-2 rounded-2xl border text-xs font-bold transition-all flex flex-col items-center shadow-xs {{ $kategoriFilter === 'cuti' ? 'bg-amber-50 border-amber-500 text-amber-700 ring-2 ring-amber-500/20' : 'bg-white border-slate-200 text-slate-600' }}">
                <i class="fa-solid fa-umbrella-beach mb-1 text-sm {{ $kategoriFilter === 'cuti' ? 'text-amber-600' : 'text-slate-400' }}"></i>
                <span>Cuti</span>
            </button>
            <button type="button" wire:click="setKategori('wfh')"
                    class="py-2.5 px-2 rounded-2xl border text-xs font-bold transition-all flex flex-col items-center shadow-xs {{ $kategoriFilter === 'wfh' ? 'bg-teal-50 border-teal-500 text-teal-700 ring-2 ring-teal-500/20' : 'bg-white border-slate-200 text-slate-600' }}">
                <i class="fa-solid fa-house-laptop mb-1 text-sm {{ $kategoriFilter === 'wfh' ? 'text-teal-600' : 'text-slate-400' }}"></i>
                <span>WFH</span>
            </button>
        </div>
    </div>

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
                    @foreach($jenisIzins as $ji)
                        <option value="{{ $ji->id }}">
                            {{ $ji->nama }} ({{ ucfirst($ji->kategori) }}{{ $ji->kuota_hari ? ' - Max ' . $ji->kuota_hari . ' Hari' : '' }})
                        </option>
                    @endforeach
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                    <i class="fa-solid fa-chevron-down text-xs"></i>
                </div>
            </div>
            @if($selectedJenis)
                <div class="mt-1.5 flex items-center justify-between text-[11px] text-slate-500">
                    <span>
                        <i class="fa-solid fa-circle-info text-sky-500 mr-1"></i>
                        {{ $selectedJenis->keterangan ?: 'Kategori: ' . ucfirst($selectedJenis->kategori) }}
                    </span>
                    @if($selectedJenis->butuh_lampiran)
                        <span class="text-rose-600 font-bold"><i class="fa-solid fa-paperclip mr-0.5"></i> Wajib Bukti</span>
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
                <div class="relative">
                    <input type="date" wire:model.live="tanggal_mulai"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                    Tanggal Selesai <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input type="date" wire:model.live="tanggal_selesai"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all">
                </div>
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
            <textarea wire:model="alasan" rows="3"
                      placeholder="Tuliskan keterangan detail alasan permohonan..."
                      class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-800 bg-slate-50/50 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all leading-relaxed"></textarea>
            @error('alasan') <span class="text-[11px] text-rose-500">{{ $message }}</span> @enderror
        </div>

        <!-- Upload Lampiran / Bukti Dokumen -->
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                Dokumen Lampiran / Surat Dokter
                @if($selectedJenis?->butuh_lampiran)
                    <span class="text-rose-500">* (Wajib)</span>
                @else
                    <span class="text-slate-400 font-normal">(Opsional)</span>
                @endif
            </label>

            <div class="border-2 border-dashed border-slate-200 rounded-2xl p-4 text-center hover:border-sky-400 transition-all bg-slate-50/30">
                <input type="file" wire:model="lampiran" id="lampiran" class="hidden" accept=".pdf,.jpg,.jpeg,.png">
                <label for="lampiran" class="cursor-pointer block">
                    <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center mx-auto mb-2 text-base">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <span class="text-xs font-bold text-slate-700 block">Pilih Berkas / Foto Dokumen</span>
                    <span class="text-[10px] text-slate-400 block mt-0.5">Format PDF, JPG, PNG (Maks. 2MB)</span>
                </label>

                <!-- Loading State Livewire -->
                <div wire:loading wire:target="lampiran" class="mt-2 text-xs font-bold text-sky-600 flex items-center justify-center space-x-1.5">
                    <i class="fa-solid fa-circle-notch fa-spin"></i>
                    <span>Mengunggah berkas...</span>
                </div>

                <!-- Preview File Uploaded -->
                @if ($lampiran)
                    <div class="mt-3 p-2 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-xs text-emerald-800 font-semibold">
                        <div class="flex items-center space-x-2 truncate">
                            <i class="fa-solid fa-file-check text-emerald-600"></i>
                            <span class="truncate">{{ $lampiran->getClientOriginalName() }}</span>
                        </div>
                        <span class="text-[10px] bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-md">Siap Diunggah</span>
                    </div>
                @endif
            </div>
            @error('lampiran') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" wire:loading.attr="disabled"
                    class="w-full py-3 px-4 rounded-2xl bg-gradient-to-r from-sky-600 to-sky-500 hover:from-sky-700 hover:to-sky-600 text-white font-extrabold text-sm shadow-lg shadow-sky-500/25 active:scale-98 transition-all flex items-center justify-center space-x-2">
                <span wire:loading.remove wire:target="save"><i class="fa-solid fa-paper-plane mr-1.5"></i> Kirim Pengajuan</span>
                <span wire:loading wire:target="save"><i class="fa-solid fa-circle-notch fa-spin mr-1.5"></i> Memproses Pengajuan...</span>
            </button>
        </div>
    </form>
</div>
