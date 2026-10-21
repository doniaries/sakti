<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Presensi - {{ $user->name }} - {{ $periode }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { margin: 12mm 15mm; size: A4 portrait; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans p-4 sm:p-8">
    <!-- Action Bar for User/Admin -->
    <div class="max-w-4xl mx-auto mb-6 flex justify-between items-center no-print bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center space-x-2">
            <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-xs font-bold text-slate-600">Pratinjau Cetak Laporan Kehadiran</span>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="window.close()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                Tutup
            </button>
            <button onclick="window.print()" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-sky-600 hover:bg-sky-700 shadow-md shadow-sky-600/30 flex items-center transition">
                <i class="fa-solid fa-print mr-2"></i> Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- Paper Container -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 rounded-3xl shadow-lg border border-slate-200/60">
        <!-- Kop Surat Instansi -->
        <div class="flex items-center border-b-2 border-slate-900 pb-5 mb-6">
            @if(!empty($pengaturan->logo) && file_exists(public_path('storage/' . $pengaturan->logo)))
                <img src="{{ asset('storage/' . $pengaturan->logo) }}" alt="Logo" class="w-20 h-20 object-contain mr-5">
            @else
                <div class="w-16 h-16 rounded-2xl bg-sky-600 text-white flex items-center justify-center text-3xl font-black mr-5">
                    <i class="fa-solid fa-leaf"></i>
                </div>
            @endif
            <div class="flex-1">
                <h2 class="text-xl font-black text-slate-900 tracking-tight uppercase">{{ $pengaturan->nama_aplikasi }}</h2>
                <h3 class="text-sm font-extrabold text-slate-700 tracking-wide uppercase">{{ $pengaturan->sub_nama ?? 'PEMERINTAH PROVINSI / KABUPATEN' }}</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Sistem Monitoring Presensi Elektronik & Laporan Kinerja Pegawai</p>
            </div>
            <div class="text-right">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Status Dokumen</span>
                <span class="text-xs font-extrabold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200 uppercase">Resmi Terverifikasi</span>
            </div>
        </div>

        <!-- Judul Laporan -->
        <div class="text-center mb-6">
            <h4 class="text-base font-black text-slate-900 uppercase tracking-wide">LAPORAN REKAPITULASI PRESENSI PEGAWAI</h4>
            <p class="text-xs font-bold text-slate-600 mt-0.5">Periode Bulan: <span class="text-sky-700">{{ $periode }}</span></p>
        </div>

        <!-- Data Pegawai -->
        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200/80 mb-6 grid grid-cols-2 gap-4 text-xs">
            <div>
                <table class="w-full">
                    <tr>
                        <td class="w-24 text-slate-500 py-1 font-medium">Nama Pegawai</td>
                        <td class="font-bold text-slate-800">: {{ trim(($user->gelar_depan ? $user->gelar_depan.' ' : '').$user->name.($user->gelar_belakang ? ', '.$user->gelar_belakang : '')) }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 py-1 font-medium">NIP</td>
                        <td class="font-bold text-slate-800">: {{ $user->nip ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 py-1 font-medium">Jabatan</td>
                        <td class="font-bold text-slate-800">: {{ $user->jabatan->nama ?? '-' }}</td>
                    </tr>
                </table>
            </div>
            <div>
                <table class="w-full">
                    <tr>
                        <td class="w-24 text-slate-500 py-1 font-medium">Bidang</td>
                        <td class="font-bold text-slate-800">: {{ $user->bidang->nama ?? 'Pimpinan / Sekretariat' }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 py-1 font-medium">Status Kerja</td>
                        <td class="font-bold text-slate-800 uppercase">: {{ $user->status_kerja ?? 'WFO' }}</td>
                    </tr>
                    <tr>
                        <td class="text-slate-500 py-1 font-medium">Filter Cetak</td>
                        <td class="font-bold text-sky-700 uppercase">: {{ $status === 'semua' ? 'Semua Log Presensi' : str_replace('_', ' ', $status) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Ringkasan Statistik -->
        <div class="grid grid-cols-5 gap-2.5 mb-6 text-center text-xs">
            <div class="border border-slate-200 rounded-xl p-2.5 bg-white">
                <span class="block text-slate-400 font-bold text-[10px] uppercase">Total Log</span>
                <span class="text-base font-black text-slate-800">{{ $rekap['total'] }} Hari</span>
            </div>
            <div class="border border-emerald-200 rounded-xl p-2.5 bg-emerald-50/50">
                <span class="block text-emerald-600 font-bold text-[10px] uppercase">Hadir Tepat</span>
                <span class="text-base font-black text-emerald-700">{{ $rekap['hadir'] - $rekap['terlambat'] }}</span>
            </div>
            <div class="border border-amber-200 rounded-xl p-2.5 bg-amber-50/50">
                <span class="block text-amber-600 font-bold text-[10px] uppercase">Terlambat</span>
                <span class="text-base font-black text-amber-700">{{ $rekap['terlambat'] }}</span>
            </div>
            <div class="border border-sky-200 rounded-xl p-2.5 bg-sky-50/50">
                <span class="block text-sky-600 font-bold text-[10px] uppercase">Dinas Luar</span>
                <span class="text-base font-black text-sky-700">{{ $rekap['dinas_luar'] }}</span>
            </div>
            <div class="border border-rose-200 rounded-xl p-2.5 bg-rose-50/50">
                <span class="block text-rose-600 font-bold text-[10px] uppercase">Alpa / Bolos</span>
                <span class="text-base font-black text-rose-700">{{ $rekap['alpa'] }}</span>
            </div>
        </div>

        <!-- Tabel Riwayat Kehadiran -->
        <table class="w-full text-xs border border-slate-300 rounded-lg overflow-hidden mb-8">
            <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px] border-b border-slate-300">
                <tr>
                    <th class="py-2.5 px-3 text-center border-r border-slate-300 w-10">No</th>
                    <th class="py-2.5 px-3 text-left border-r border-slate-300">Hari / Tanggal</th>
                    <th class="py-2.5 px-3 text-center border-r border-slate-300 w-24">Jam Masuk</th>
                    <th class="py-2.5 px-3 text-center border-r border-slate-300 w-24">Jam Pulang</th>
                    <th class="py-2.5 px-3 text-center border-r border-slate-300 w-28">Status</th>
                    <th class="py-2.5 px-3 text-left">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($riwayat as $index => $item)
                    @php
                        $st = $item->status?->value ?? $item->status;
                    @endphp
                    <tr class="hover:bg-slate-50">
                        <td class="py-2 px-3 text-center border-r border-slate-200 font-mono">{{ $index + 1 }}</td>
                        <td class="py-2 px-3 text-left border-r border-slate-200 font-semibold">{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('l, d F Y') }}</td>
                        <td class="py-2 px-3 text-center border-r border-slate-200 font-mono font-bold {{ $item->jam_masuk ? 'text-slate-800' : 'text-slate-300' }}">{{ $item->jam_masuk ?? '-' }}</td>
                        <td class="py-2 px-3 text-center border-r border-slate-200 font-mono font-bold {{ $item->jam_pulang ? 'text-slate-800' : 'text-slate-300' }}">{{ $item->jam_pulang ?? '-' }}</td>
                        <td class="py-2 px-3 text-center border-r border-slate-200 uppercase font-extrabold text-[10px]">
                            <span class="inline-block px-2 py-0.5 rounded-full {{ $st === 'hadir' ? 'text-emerald-700 bg-emerald-50' : ($st === 'terlambat' ? 'text-amber-700 bg-amber-50' : ($st === 'dinas_luar' ? 'text-sky-700 bg-sky-50' : 'text-rose-700 bg-rose-50')) }}">
                                {{ str_replace('_', ' ', $st) }}
                            </span>
                        </td>
                        <td class="py-2 px-3 text-left text-slate-500 italic">{{ $item->keterangan ?? ($st === 'hadir' ? 'Kehadiran Normal' : '-') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-6 text-center text-slate-400 italic">Tidak ada catatan kehadiran pada filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Tanda Tangan Pegawai & Pimpinan -->
        <div class="grid grid-cols-2 gap-8 text-xs text-center mt-12 pt-6 border-t border-slate-200">
            <div>
                <p class="text-slate-500 mb-16">Mengetahui,<br><span class="font-bold text-slate-800">Atasan Langsung / Kepala Dinas</span></p>
                <p class="font-extrabold text-slate-900 underline">{{ $user->atasan->name ?? '( ________________________ )' }}</p>
                <p class="text-[10px] text-slate-500 font-mono">NIP. {{ $user->atasan->nip ?? '........................................' }}</p>
            </div>
            <div>
                <p class="text-slate-500 mb-16">{{ now()->translatedFormat('d F Y') }}<br><span class="font-bold text-slate-800">Pegawai Bersangkutan</span></p>
                <p class="font-extrabold text-slate-900 underline">{{ trim(($user->gelar_depan ? $user->gelar_depan.' ' : '').$user->name.($user->gelar_belakang ? ', '.$user->gelar_belakang : '')) }}</p>
                <p class="text-[10px] text-slate-500 font-mono">NIP. {{ $user->nip ?? '........................................' }}</p>
            </div>
        </div>

        <!-- Footer Cetak -->
        <div class="mt-10 pt-4 border-t border-slate-100 flex justify-between items-center text-[9px] text-slate-400">
            <span>Dicetak secara otomatis melalui {{ $pengaturan->nama_aplikasi }} v{{ $pengaturan->versi_aplikasi }}</span>
            <span>Waktu Cetak: {{ now()->translatedFormat('d/m/Y H:i') }} WIB</span>
        </div>
    </div>
</body>
</html>
