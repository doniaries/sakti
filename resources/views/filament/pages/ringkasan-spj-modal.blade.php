@php
    /** @var \App\Models\PengajuanSpj $record */
    $record->loadMissing([
        'bidang',
        'kegiatan',
        'subKegiatan',
        'user',
        'pptk',
        'ppk',
        'pa',
        'bendahara',
        'masterToko',
        'details.satuan',
    ]);

    $status = $record->status;
    $totalNominal = (float) ($record->total_nilai_realisasi ?: $record->total_nilai_rencana);

    $posisiSaatIni = match ($status) {
        'draft' => 'Draf Baru — Belum Diajukan (Pemohon / Staf)',
        'diajukan' => 'Meja PPTK — Menunggu Tanda Tangan Surat Pesanan',
        'menunggu_realisasi' => 'Pemohon — Surat Pesanan Disetujui, Dalam Realisasi Belanja & SPJ',
        'menunggu_verifikasi_pptk' => 'Meja PPTK — Menunggu Verifikasi Bukti Belanja SPJ',
        'revisi_pptk' => 'Perlu Revisi — Dikembalikan oleh PPTK',
        'menunggu_verifikasi_ppk' => 'Meja PPK (Keuangan) — Menunggu Uji Berkas & Verifikasi Pajak',
        'revisi_ppk' => 'Perlu Revisi — Dikembalikan oleh PPK',
        'menunggu_persetujuan_pa' => 'Meja PA (Kepala Dinas) — Menunggu Persetujuan NPD',
        'revisi_pa' => 'Perlu Revisi — Dikembalikan oleh Pengguna Anggaran',
        'menunggu_verifikasi_bendahara' => 'Meja Bendahara — Menunggu Uji Kas & Penerbitan SP2D',
        'revisi_bendahara' => 'Perlu Revisi — Dikembalikan oleh Bendahara',
        'disetujui' => 'Siap Dicairkan — Disetujui Bendahara Pengeluaran',
        'dicairkan' => 'LUNAS — Dana Telah Selesai Dicairkan',
        default => $record->status_label,
    };

    $pejabatAktif = match ($status) {
        'draft', 'menunggu_realisasi' => $record->user?->name ?? 'Pemohon (Staf)',
        'diajukan', 'menunggu_verifikasi_pptk', 'revisi_pptk' => $record->pptk?->name ?? 'PPTK Terkait',
        'menunggu_verifikasi_ppk', 'revisi_ppk' => $record->ppk?->name ?? 'Kasubag Keuangan / PPK',
        'menunggu_persetujuan_pa', 'revisi_pa' => $record->pa?->name ?? 'Kepala Dinas / PA',
        'menunggu_verifikasi_bendahara', 'revisi_bendahara', 'disetujui', 'dicairkan' => $record->bendahara?->name ?? 'Bendahara Pengeluaran',
        default => '-',
    };

    $rawBank = $record->nama_bank ?: $record->masterToko?->nama_bank;
    $namaBank = $rawBank instanceof \BackedEnum ? $rawBank->value : ($rawBank ?: '-');
@endphp

<div class="space-y-4 text-xs text-gray-800 dark:text-gray-200">
    {{-- 1. HEADER RINGKASAN: Posisi Berkas & Nominal Belanja --}}
    <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 shadow-xs dark:border-white/10 dark:bg-white/5">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200/60 pb-3 dark:border-white/10">
            <div>
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Posisi Berkas Saat Ini:
                </span>
                <div class="mt-0.5 flex items-center gap-2">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ str_contains($status, 'revisi') ? 'bg-red-400' : ($status === 'dicairkan' ? 'bg-green-400' : 'bg-amber-400') }} opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 {{ str_contains($status, 'revisi') ? 'bg-red-500' : ($status === 'dicairkan' ? 'bg-green-500' : 'bg-amber-500') }}"></span>
                    </span>
                    <span class="text-sm font-extrabold text-gray-900 dark:text-white">
                        {{ $posisiSaatIni }}
                    </span>
                </div>
                <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                    Penanggung Jawab: <strong class="text-gray-800 dark:text-gray-200">{{ $pejabatAktif }}</strong>
                </div>
            </div>

            <div class="text-right">
                <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                    Total Nilai Belanja:
                </span>
                <div class="text-lg font-black text-primary-600 dark:text-primary-400">
                    Rp {{ number_format($totalNominal, 0, ',', '.') }}
                </div>
                <span class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-bold
                    {{ match ($record->status_color) {
                        'success' => 'bg-green-100 text-green-800 dark:bg-green-950/60 dark:text-green-300',
                        'danger' => 'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300',
                        'warning' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300',
                        'info' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300',
                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
                    } }}">
                    {{ $record->status_label }}
                </span>
            </div>
        </div>

        {{-- Alert Catatan Revisi jika ada --}}
        @if (filled($record->catatan_revisi) && str_contains($status, 'revisi'))
            <div class="mt-3 rounded-lg border border-red-300 bg-red-100/70 p-2.5 text-xs text-red-900 dark:border-red-700 dark:bg-red-950/60 dark:text-red-200">
                <div class="font-bold flex items-center gap-1.5 mb-0.5">
                    <span>⚠️</span> Catatan Perbaikan Verifikator:
                </div>
                <p class="italic font-mono pl-4">"{{ $record->catatan_revisi }}"</p>
            </div>
        @endif
    </div>

    {{-- 2. GRID 2 KOLOM: Identitas Anggaran & Penyedia Pembayaran --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        {{-- Kartu Kiri: Identitas Pengajuan & SIPD --}}
        <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-2xs dark:border-white/10 dark:bg-gray-900 space-y-2">
            <h4 class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5 border-b border-gray-100 pb-1.5 dark:border-white/5">
                <svg class="h-4 w-4 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Identitas Pengajuan & Anggaran
            </h4>
            <div class="space-y-1 text-[11px]">
                <div class="flex justify-between"><span class="text-gray-500">No. Pengajuan:</span><strong class="font-mono text-gray-900 dark:text-white">{{ $record->nomor_pengajuan }}</strong></div>
                <div class="flex justify-between"><span class="text-gray-500">Tgl Pengajuan:</span><span>{{ $record->tanggal_pengajuan ? $record->tanggal_pengajuan->format('d/m/Y') : '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Tahun Anggaran:</span><strong class="text-primary-600 dark:text-primary-400">{{ $record->tahun_anggaran }}</strong></div>
                <div class="flex justify-between"><span class="text-gray-500">Bidang Pengusul:</span><span class="font-semibold">{{ $record->bidang?->nama ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Pemohon (Staf):</span><span>{{ $record->user?->name ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">PPTK Ditugaskan:</span><span class="font-semibold">{{ $record->pptk?->name ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Jenis Belanja:</span><span>{{ $record->jenis_belanja_label }} ({{ $record->metode_pengadaan_label }})</span></div>
                <div class="pt-1 border-t border-gray-100 dark:border-white/5">
                    <span class="text-gray-500 block">Sub Kegiatan SIPD:</span>
                    <span class="font-medium text-gray-900 dark:text-white block truncate" title="{{ $record->subKegiatan?->nama_sub_kegiatan ?? '-' }}">{{ $record->subKegiatan?->nama_sub_kegiatan ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Kartu Kanan: Data Penyedia, Rekening & Pajak --}}
        <div class="rounded-xl border border-gray-200 bg-white p-3.5 shadow-2xs dark:border-white/10 dark:bg-gray-900 space-y-2">
            <h4 class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5 border-b border-gray-100 pb-1.5 dark:border-white/5">
                <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Penyedia, Rekening & Pajak
            </h4>
            <div class="space-y-1 text-[11px]">
                <div class="flex justify-between"><span class="text-gray-500">Nama Toko:</span><strong class="text-gray-900 dark:text-white">{{ $record->nama_toko ?: ($record->masterToko?->nama_toko ?: '-') }}</strong></div>
                <div class="flex justify-between"><span class="text-gray-500">Telepon Toko:</span><span>{{ $record->telepon_toko ?: ($record->masterToko?->telepon ?: '-') }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Bank Tujuan:</span><span class="font-semibold">{{ $namaBank }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">No. Rek / Penerima:</span><span class="font-mono">{{ $record->penerima_identitas ?: '-' }} ({{ $record->penerima_nama ?: '-' }})</span></div>
                
                {{-- Rincian Pajak --}}
                <div class="pt-1.5 border-t border-gray-100 dark:border-white/5 grid grid-cols-2 gap-1.5">
                    <div>
                        <span class="text-gray-500 block text-[10px]">PPN 11%:</span>
                        <strong class="font-mono text-gray-800 dark:text-gray-200">Rp {{ number_format((float) $record->nilai_ppn, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[10px]">PPh 22 (1.5%):</span>
                        <strong class="font-mono text-gray-800 dark:text-gray-200">Rp {{ number_format((float) $record->nilai_pph22, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[10px]">Total Pajak:</span>
                        <strong class="font-mono text-red-600 dark:text-red-400">Rp {{ number_format((float) $record->total_pajak, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-[10px]">Nilai Bersih Rekanan:</span>
                        <strong class="font-mono text-emerald-600 dark:text-emerald-400">Rp {{ number_format((float) $record->nilai_bersih, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. TABEL RINGKAS RINCIAN BARANG / ITEM BELANJA --}}
    @if ($record->details && $record->details->isNotEmpty())
        <div class="rounded-xl border border-gray-200 bg-white p-3 shadow-2xs dark:border-white/10 dark:bg-gray-900">
            <h4 class="font-bold text-gray-900 dark:text-white text-xs mb-2 flex items-center justify-between">
                <span>Rincian Item Barang / Belanja ({{ $record->details->count() }} Item)</span>
                <span class="text-[11px] font-normal text-gray-500">Harga Satuan resmi Pagu RKA</span>
            </h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[11px] border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400">
                            <th class="py-1.5 pr-2 w-8 text-center">No</th>
                            <th class="py-1.5 px-2">Uraian Barang / Belanja</th>
                            <th class="py-1.5 px-2">Spesifikasi</th>
                            <th class="py-1.5 px-2 text-center">Volume</th>
                            <th class="py-1.5 px-2 text-right">Harga Satuan</th>
                            <th class="py-1.5 pl-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach ($record->details as $idx => $item)
                            @php
                                $qty = (float) ($item->kuantitas_realisasi ?? $item->kuantitas);
                                $harga = (float) ($item->harga_satuan_realisasi ?? $item->harga_satuan);
                                $sub = (float) ($item->subtotal_realisasi ?? $item->subtotal);
                                $satuan = $item->satuan?->nama ?? $item->satuan_custom ?? 'Item';
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5">
                                <td class="py-1.5 pr-2 text-center text-gray-400">{{ $idx + 1 }}</td>
                                <td class="py-1.5 px-2 font-semibold text-gray-900 dark:text-white">{{ $item->nama_barang_custom }}</td>
                                <td class="py-1.5 px-2 text-gray-500">{{ $item->spesifikasi ?: '-' }}</td>
                                <td class="py-1.5 px-2 text-center font-mono">{{ number_format($qty, 0, ',', '.') }} {{ $satuan }}</td>
                                <td class="py-1.5 px-2 text-right font-mono">Rp {{ number_format($harga, 0, ',', '.') }}</td>
                                <td class="py-1.5 pl-2 text-right font-mono font-bold text-gray-900 dark:text-white">Rp {{ number_format($sub, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
