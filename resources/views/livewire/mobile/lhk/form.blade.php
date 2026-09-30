<div class="p-5 pb-24">
    <div class="flex items-center mb-6">
        <a href="{{ route('mobile.lhk.index') }}" wire:navigate class="mr-4 text-gray-500 hover:text-gray-800">
            <i class="fa-solid fa-arrow-left text-lg"></i>
        </a>
        <h1 class="text-xl font-bold text-gray-800">Buat LHK</h1>
    </div>

    <!-- Info Kehadiran -->
    <div class="bg-white rounded-2xl p-5 shadow-sm mb-6">
        <h2 class="text-sm font-bold text-gray-800 mb-4 border-b pb-2">Informasi Kehadiran</h2>
        
        <div class="mb-4">
            <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal LHK</label>
            <input type="date" wire:model.live="tanggal" class="w-full bg-[#F5F7FA] border-none rounded-xl p-3 text-sm focus:ring-2 focus:ring-[#1B6CA8] transition">
        </div>

        @if(!$absenHariIni)
            <div class="bg-gray-100 text-gray-500 p-3 rounded-xl text-sm flex items-center">
                <i class="fa-solid fa-circle-info mr-2"></i> Belum ada data absensi.
            </div>
        @else
            <div class="flex space-x-3">
                <div class="flex-1 bg-[#F59E0B]/10 border border-[#F59E0B]/20 rounded-xl p-3 flex flex-col items-center">
                    <span class="text-[10px] text-[#F59E0B] font-semibold uppercase mb-1">Jam Masuk</span>
                    <span class="text-lg font-bold text-[#F59E0B]">{{ $absenHariIni->jam_masuk ?? '-' }}</span>
                </div>
                <div class="flex-1 bg-[#2D9B4F]/10 border border-[#2D9B4F]/20 rounded-xl p-3 flex flex-col items-center">
                    <span class="text-[10px] text-[#2D9B4F] font-semibold uppercase mb-1">Jam Pulang</span>
                    <span class="text-lg font-bold text-[#2D9B4F]">{{ $absenHariIni->jam_pulang ?? '-' }}</span>
                </div>
            </div>
            @if(!$absenHariIni->log_masuk_id || !$absenHariIni->log_pulang_id)
                <div class="bg-red-50 text-red-500 p-3 rounded-xl text-xs mt-3 flex items-center border border-red-200">
                    <i class="fa-solid fa-triangle-exclamation mr-2"></i> Absensi belum lengkap (Masuk & Pulang wajib ada).
                </div>
            @endif
        @endif
    </div>

    <!-- Rincian Pekerjaan -->
    <div class="bg-white rounded-2xl p-5 shadow-sm mb-6">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h2 class="text-sm font-bold text-gray-800">Rincian Pekerjaan</h2>
            <button wire:click="addPekerjaan" class="text-xs font-semibold text-[#1B6CA8] bg-[#1B6CA8]/10 px-3 py-1.5 rounded-full hover:bg-[#1B6CA8]/20 transition">
                <i class="fa-solid fa-plus mr-1"></i> Tambah
            </button>
        </div>

        @if(empty($pekerjaans))
            <div class="text-center py-6 text-gray-400 text-sm">
                Belum ada kegiatan. Tap tombol Tambah.
            </div>
        @else
            <div class="space-y-4">
                @foreach($pekerjaans as $index => $pekerjaan)
                    <div class="bg-[#F8FAFC] border border-gray-200 p-4 rounded-xl relative">
                        <div class="flex justify-between items-center mb-3">
                            <span class="text-xs font-bold text-[#1B6CA8] bg-[#1B6CA8]/10 px-2 py-1 rounded">Kegiatan {{ $index + 1 }}</span>
                            <button wire:click="removePekerjaan({{ $index }})" class="text-red-400 hover:text-red-600 transition">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-[10px] text-gray-500 mb-1">Jam Mulai</label>
                                <input type="time" wire:model="pekerjaans.{{ $index }}.jam_mulai" class="w-full bg-white border border-gray-200 rounded-lg p-2 text-sm focus:ring-1 focus:ring-[#1B6CA8]">
                                @error('pekerjaans.'.$index.'.jam_mulai') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-[10px] text-gray-500 mb-1">Jam Selesai</label>
                                <input type="time" wire:model="pekerjaans.{{ $index }}.jam_selesai" class="w-full bg-white border border-gray-200 rounded-lg p-2 text-sm focus:ring-1 focus:ring-[#1B6CA8]">
                                @error('pekerjaans.'.$index.'.jam_selesai') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="block text-[10px] text-gray-500 mb-1">Rencana Aksi</label>
                            <select wire:model="pekerjaans.{{ $index }}.rencana_aksi_id" class="w-full bg-white border border-gray-200 rounded-lg p-2 text-sm focus:ring-1 focus:ring-[#1B6CA8]">
                                <option value="1">Diluar Sasaran Kinerja</option>
                                <option value="2">Melaksanakan Tugas Lain</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="block text-[10px] text-gray-500 mb-1">Aktivitas</label>
                            <select wire:model="pekerjaans.{{ $index }}.aktivitas_id" class="w-full bg-white border border-gray-200 rounded-lg p-2 text-sm focus:ring-1 focus:ring-[#1B6CA8]">
                                <option value="">- Pilih Aktivitas -</option>
                                @foreach($aktivitasList as $a)
                                    <option value="{{ $a->id }}">{{ $a->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="block text-[10px] text-gray-500 mb-1">Hasil Pekerjaan</label>
                            <textarea wire:model="pekerjaans.{{ $index }}.hasil_pekerjaan" rows="2" class="w-full bg-white border border-gray-200 rounded-lg p-2 text-sm focus:ring-1 focus:ring-[#1B6CA8]"></textarea>
                            @error('pekerjaans.'.$index.'.hasil_pekerjaan') <span class="text-red-500 text-[10px]">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[10px] text-gray-500 mb-1">Link Bukti (G-Drive)</label>
                            <input type="url" wire:model="pekerjaans.{{ $index }}.bukti_url" placeholder="https://..." class="w-full bg-white border border-gray-200 rounded-lg p-2 text-sm focus:ring-1 focus:ring-[#1B6CA8]">
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Simpan -->
    <button wire:click="save" wire:loading.attr="disabled" class="w-full bg-[#1B6CA8] text-white font-bold py-4 rounded-xl shadow-lg hover:bg-[#155685] transition flex justify-center items-center">
        <span wire:loading.remove>Simpan LHK</span>
        <span wire:loading><i class="fa-solid fa-circle-notch fa-spin"></i> Menyimpan...</span>
    </button>
</div>
