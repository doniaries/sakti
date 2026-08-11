<x-filament-panels::page>
    <!-- Form Pilih Bulan -->
    <form wire:submit="lihatLhk" class="mb-8">
        {{ $this->form }}

        <div class="mt-4 flex justify-end">
            <x-filament::button type="submit" color="info">
                Lihat
            </x-filament::button>
        </div>
    </form>

    <!-- Tabel Evidence (Tampil setelah form di-submit/ada data) -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200 dark:bg-gray-900 dark:border-gray-800">
        
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">
                Absensi Bulan {{ isset($data['bulan']) ? \Carbon\Carbon::create()->month($data['bulan'])->translatedFormat('F') : '...' }} 
                Tahun {{ $data['tahun'] ?? '...' }} (EVIDENCE)
            </h2>
            <x-filament::button color="info">
                Lihat Laporan dan Kirim ke Atasan Langsung untuk di tanda tangan
            </x-filament::button>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-center border-collapse">
                <thead>
                    <tr class="bg-[#5c6096] text-white">
                        <th rowspan="2" class="border border-[#6f73a8] px-3 py-2">Tanggal</th>
                        <th rowspan="2" class="border border-[#6f73a8] px-3 py-2">Hari</th>
                        <th rowspan="2" class="border border-[#6f73a8] px-3 py-2">Keterangan</th>
                        <th colspan="2" class="border border-[#6f73a8] px-3 py-2">Set Masuk</th>
                        <th colspan="2" class="border border-[#6f73a8] px-3 py-2">Absen</th>
                        <th colspan="3" class="border border-[#6f73a8] px-3 py-2">Potongan</th>
                        <th rowspan="2" class="border border-[#6f73a8] px-3 py-2">Proses</th>
                    </tr>
                    <tr class="bg-[#5c6096] text-white">
                        <th class="border border-[#6f73a8] px-3 py-2">Tanggal</th>
                        <th class="border border-[#6f73a8] px-3 py-2">Jam</th>
                        <th class="border border-[#6f73a8] px-3 py-2">Tanggal</th>
                        <th class="border border-[#6f73a8] px-3 py-2">Jam</th>
                        <th class="border border-[#6f73a8] px-3 py-2">Kehadiran</th>
                        <th class="border border-[#6f73a8] px-3 py-2">Terlambat</th>
                        <th class="border border-[#6f73a8] px-3 py-2">Apel</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 dark:text-gray-200">
                    <!-- Tanggal 1 -->
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3">1</td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3">Sabtu</td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                    </tr>
                    <!-- Tanggal 2 -->
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3">2</td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3">Minggu</td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                    </tr>
                    <!-- Tanggal 3 (Hadir) -->
                    <tr class="bg-[#cd5c3b] text-white">
                        <td class="border border-[#e07556] px-3 py-3">3</td>
                        <td class="border border-[#e07556] px-3 py-3">Senin</td>
                        <td class="border border-[#e07556] px-3 py-3">Hadir</td>
                        <td class="border border-[#e07556] px-3 py-3">03 Agustus 2026</td>
                        <td class="border border-[#e07556] px-3 py-3">07:30:00</td>
                        <td class="border border-[#e07556] px-3 py-3">03 Agustus 2026</td>
                        <td class="border border-[#e07556] px-3 py-3">08:48:27</td>
                        <td class="border border-[#e07556] px-3 py-3">0</td>
                        <td class="border border-[#e07556] px-3 py-3">0</td>
                        <td class="border border-[#e07556] px-3 py-3">0</td>
                        <td class="border border-[#e07556] px-3 py-3">
                            <button class="bg-[#1bb8aa] hover:bg-teal-500 text-white px-4 py-1 rounded text-xs font-semibold shadow-sm transition">
                                Buat LHK
                            </button>
                        </td>
                    </tr>
                    <!-- Tanggal 4 (Hadir) -->
                    <tr class="bg-[#cd5c3b] text-white">
                        <td class="border border-[#e07556] px-3 py-3">4</td>
                        <td class="border border-[#e07556] px-3 py-3">Selasa</td>
                        <td class="border border-[#e07556] px-3 py-3">Hadir</td>
                        <td class="border border-[#e07556] px-3 py-3">04 Agustus 2026</td>
                        <td class="border border-[#e07556] px-3 py-3">07:30:00</td>
                        <td class="border border-[#e07556] px-3 py-3">04 Agustus 2026</td>
                        <td class="border border-[#e07556] px-3 py-3">07:09:08</td>
                        <td class="border border-[#e07556] px-3 py-3">0</td>
                        <td class="border border-[#e07556] px-3 py-3">0</td>
                        <td class="border border-[#e07556] px-3 py-3">0</td>
                        <td class="border border-[#e07556] px-3 py-3">
                            <button class="bg-[#1bb8aa] hover:bg-teal-500 text-white px-4 py-1 rounded text-xs font-semibold shadow-sm transition">
                                Buat LHK
                            </button>
                        </td>
                    </tr>
                    <!-- Tanggal 5 -->
                    <tr class="bg-white dark:bg-gray-800">
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3">5</td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3">Rabu</td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                        <td class="border border-gray-200 dark:border-gray-700 px-3 py-3"></td>
                    </tr>
                    <!-- Tanggal 6 (Hadir) -->
                    <tr class="bg-[#cd5c3b] text-white">
                        <td class="border border-[#e07556] px-3 py-3">6</td>
                        <td class="border border-[#e07556] px-3 py-3">Kamis</td>
                        <td class="border border-[#e07556] px-3 py-3">Hadir</td>
                        <td class="border border-[#e07556] px-3 py-3">06 Agustus 2026</td>
                        <td class="border border-[#e07556] px-3 py-3">07:30:00</td>
                        <td class="border border-[#e07556] px-3 py-3">06 Agustus 2026</td>
                        <td class="border border-[#e07556] px-3 py-3">07:30:00</td>
                        <td class="border border-[#e07556] px-3 py-3">0</td>
                        <td class="border border-[#e07556] px-3 py-3">0</td>
                        <td class="border border-[#e07556] px-3 py-3">0</td>
                        <td class="border border-[#e07556] px-3 py-3">
                            <button class="bg-[#1bb8aa] hover:bg-teal-500 text-white px-4 py-1 rounded text-xs font-semibold shadow-sm transition">
                                Buat LHK
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
