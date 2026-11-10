@php
    /** @var \App\Models\PengajuanSpj $record */
    $status = $record->status;

    // Tentukan status tiap tahapan (completed, active, revision, pending)
    $steps = [
        1 => [
            'key' => 'pemohon',
            'title' => 'Pengajuan & Pembuatan Pesanan',
            'role_label' => 'Pemohon (Staf Inisiator)',
            'actor' => $record->user?->name ?? 'Staf Bidang',
            'actor_sub' => $record->bidang?->nama ?? 'Bidang Dinas',
            'date' => $record->tanggal_pengajuan ? $record->tanggal_pengajuan->translatedFormat('d F Y') : ($record->created_at ? $record->created_at->translatedFormat('d F Y') : null),
            'desc' => match ($status) {
                'draft' => 'Draf surat pesanan sedang disusun dan belum diajukan ke PPTK.',
                default => 'Surat pesanan barang/jasa telah dibuat dan diajukan.',
            },
            'state' => match ($status) {
                'draft' => 'active',
                default => 'completed',
            },
        ],
        2 => [
            'key' => 'pptk',
            'title' => 'Verifikasi & Persetujuan PPTK',
            'role_label' => 'Pejabat Pelaksana Teknis Kegiatan (PPTK)',
            'actor' => $record->pptk?->name ?? 'PPTK Terkait',
            'actor_sub' => 'Kepala Bidang / PPTK Sub Kegiatan',
            'date' => null,
            'desc' => match ($status) {
                'draft' => 'Menunggu pengajuan dari pemohon.',
                'diajukan' => 'Surat pesanan sedang menunggu persetujuan / tanda tangan PPTK.',
                'menunggu_realisasi' => 'Surat pesanan disetujui. Pemohon sedang melaksanakan realisasi belanja & upload SPJ.',
                'menunggu_verifikasi_pptk' => 'Berkas bukti belanja & kuitansi sedang diverifikasi oleh PPTK.',
                'revisi_pptk' => 'Berkas dikembalikan oleh PPTK untuk perbaikan/kelengkapan bukti.',
                default => 'Surat pesanan dan berkas SPJ telah diverifikasi dan disetujui PPTK.',
            },
            'state' => match ($status) {
                'draft' => 'pending',
                'diajukan', 'menunggu_realisasi', 'menunggu_verifikasi_pptk' => 'active',
                'revisi_pptk' => 'revision',
                default => 'completed',
            },
        ],
        3 => [
            'key' => 'ppk',
            'title' => 'Uji Berkas Keuangan & Pajak (PPK)',
            'role_label' => 'Pejabat Pembuat Komitmen (PPK)',
            'actor' => $record->ppk?->name ?? 'Kasubag Keuangan / PPK',
            'actor_sub' => 'Verifikator Keuangan & Perpajakan',
            'date' => null,
            'desc' => match ($status) {
                'draft', 'diajukan', 'menunggu_realisasi', 'menunggu_verifikasi_pptk', 'revisi_pptk' => 'Menunggu verifikasi selesai di tingkat PPTK.',
                'menunggu_verifikasi_ppk' => 'Berkas sedang diteliti oleh PPK: verifikasi kuitansi, nota, PPN, dan PPh 22.',
                'revisi_ppk' => 'Berkas dikembalikan oleh PPK untuk perbaikan dokumen/pajak.',
                default => 'Berkas fisik dan perhitungan pajak telah diverifikasi sah oleh PPK.',
            },
            'state' => match ($status) {
                'draft', 'diajukan', 'menunggu_realisasi', 'menunggu_verifikasi_pptk', 'revisi_pptk' => 'pending',
                'menunggu_verifikasi_ppk' => 'active',
                'revisi_ppk' => 'revision',
                default => 'completed',
            },
        ],
        4 => [
            'key' => 'pa',
            'title' => 'Persetujuan Pembayaran & Penerbitan NPD',
            'role_label' => 'Pengguna Anggaran (PA / Kepala Dinas)',
            'actor' => $record->pa?->name ?? 'Kepala Dinas',
            'actor_sub' => 'Pengguna Anggaran OPD',
            'date' => null,
            'desc' => match ($status) {
                'draft', 'diajukan', 'menunggu_realisasi', 'menunggu_verifikasi_pptk', 'revisi_pptk', 'menunggu_verifikasi_ppk', 'revisi_ppk' => 'Menunggu verifikasi PPK selesai.',
                'menunggu_persetujuan_pa' => 'Menunggu persetujuan akhir Kepala Dinas dan penerbitan Nota Pencairan Dana (NPD).',
                'revisi_pa' => 'Berkas dikembalikan oleh Pengguna Anggaran untuk perbaikan.',
                default => 'Disetujui oleh Pengguna Anggaran dan Nota Pencairan Dana (NPD) telah diterbitkan.',
            },
            'state' => match ($status) {
                'menunggu_persetujuan_pa' => 'active',
                'revisi_pa' => 'revision',
                'menunggu_verifikasi_bendahara', 'revisi_bendahara', 'disetujui', 'dicairkan' => 'completed',
                default => 'pending',
            },
        ],
        5 => [
            'key' => 'bendahara',
            'title' => 'Pengujian Kas & Pembayaran (Bendahara)',
            'role_label' => 'Bendahara Pengeluaran',
            'actor' => $record->bendahara?->name ?? 'Bendahara Pengeluaran',
            'actor_sub' => 'Pengelola Kas & Pembukuan SP2D',
            'date' => null,
            'desc' => match ($status) {
                'menunggu_verifikasi_bendahara' => 'Berkas sedang diuji kas oleh Bendahara Pengeluaran untuk ketersediaan dana dan kelengkapan SP2D.',
                'revisi_bendahara' => 'Berkas dikembalikan oleh Bendahara Pengeluaran untuk perbaikan administrasi pembayaran.',
                'disetujui' => 'Pengujian kas selesai. Berkas disetujui dan siap untuk dicairkan / dibayarkan.',
                'dicairkan' => 'Pengujian kas tuntas dan pembayaran telah direalisasikan.',
                default => 'Menunggu persetujuan NPD dari Pengguna Anggaran.',
            },
            'state' => match ($status) {
                'menunggu_verifikasi_bendahara' => 'active',
                'revisi_bendahara' => 'revision',
                'disetujui' => 'active',
                'dicairkan' => 'completed',
                default => 'pending',
            },
        ],
        6 => [
            'key' => 'cair',
            'title' => 'Pencairan Dana & Selesai (Lunas)',
            'role_label' => 'Pencairan Kas OPD',
            'actor' => $record->bendahara?->name ?? 'Kas Daerah / Bendahara',
            'actor_sub' => 'Realisasi Belanja APBD',
            'date' => $record->tanggal_dicairkan ? $record->tanggal_dicairkan->translatedFormat('d F Y') : null,
            'desc' => match ($status) {
                'dicairkan' => 'Dana telah berhasil dicairkan kepada pihak penyedia/penerima. Realisasi otomatis tercatat ke buku anggaran belanja.',
                default => 'Menunggu proses pencairan dana oleh Bendahara Pengeluaran.',
            },
            'state' => match ($status) {
                'dicairkan' => 'completed',
                default => 'pending',
            },
        ],
    ];

    // Temukan tahapan aktif saat ini untuk kartu Posisi Saat Ini
    $activeStepNum = 1;
    foreach ($steps as $num => $step) {
        if (in_array($step['state'], ['active', 'revision'], true)) {
            $activeStepNum = $num;
            break;
        }
        if ($step['state'] === 'completed') {
            $activeStepNum = $num;
        }
    }

    $currentHeroInfo = match ($status) {
        'draft' => [
            'label' => 'TAHAP 1: PEMOHON (DRAF BELUM DIAJUKAN)',
            'color' => 'gray',
            'bg' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-700',
            'message' => 'Berkas masih berupa Draf dan belum diajukan oleh Pemohon ke PPTK.',
        ],
        'diajukan' => [
            'label' => 'TAHAP 2: MEJA PPTK (PERSETUJUAN SURAT PESANAN)',
            'color' => 'amber',
            'bg' => 'bg-amber-50 text-amber-900 dark:bg-amber-950/50 dark:text-amber-200 border-amber-300 dark:border-amber-700/60',
            'message' => 'Surat Pesanan telah diajukan dan sedang menunggu persetujuan/tanda tangan PPTK.',
        ],
        'menunggu_realisasi' => [
            'label' => 'TAHAP 2: PEMOHON (REALISASI BELANJA & INPUT SPJ)',
            'color' => 'blue',
            'bg' => 'bg-blue-50 text-blue-900 dark:bg-blue-950/50 dark:text-blue-200 border-blue-300 dark:border-blue-700/60',
            'message' => 'Surat Pesanan telah disetujui PPTK. Pemohon sedang melaksanakan realisasi belanja dan mengunggah berkas bukti pengeluaran SPJ.',
        ],
        'menunggu_verifikasi_pptk' => [
            'label' => 'TAHAP 2: MEJA PPTK (VERIFIKASI BUKTI SPJ)',
            'color' => 'blue',
            'bg' => 'bg-blue-50 text-blue-900 dark:bg-blue-950/50 dark:text-blue-200 border-blue-300 dark:border-blue-700/60',
            'message' => 'Berkas bukti belanja & kuitansi sedang diverifikasi oleh PPTK sebelum diteruskan ke PPK.',
        ],
        'revisi_pptk' => [
            'label' => '⚠️ PERLU REVISI: DIKEMBALIKAN OLEH PPTK',
            'color' => 'danger',
            'bg' => 'bg-red-50 text-red-900 dark:bg-red-950/50 dark:text-red-200 border-red-300 dark:border-red-700/60',
            'message' => 'Berkas dikembalikan oleh PPTK untuk perbaikan kelengkapan bukti oleh Pemohon.',
        ],
        'menunggu_verifikasi_ppk' => [
            'label' => 'TAHAP 3: MEJA PPK / KASUBAG KEUANGAN (UJI BERKAS & PAJAK)',
            'color' => 'indigo',
            'bg' => 'bg-indigo-50 text-indigo-900 dark:bg-indigo-950/50 dark:text-indigo-200 border-indigo-300 dark:border-indigo-700/60',
            'message' => 'Berkas sedang diverifikasi oleh Pejabat Pembuat Komitmen (PPK): verifikasi kelengkapan bukti pengeluaran, nota kuitansi, dan pemungutan pajak PPN/PPh 22.',
        ],
        'revisi_ppk' => [
            'label' => '⚠️ PERLU REVISI: DIKEMBALIKAN OLEH PPK (KEUANGAN)',
            'color' => 'danger',
            'bg' => 'bg-red-50 text-red-900 dark:bg-red-950/50 dark:text-red-200 border-red-300 dark:border-red-700/60',
            'message' => 'Berkas dikembalikan oleh PPK untuk perbaikan dokumen bukti pengeluaran atau perhitungan pajak.',
        ],
        'menunggu_persetujuan_pa' => [
            'label' => 'TAHAP 4: MEJA PENGGUNA ANGGARAN (PA / KEPALA DINAS)',
            'color' => 'purple',
            'bg' => 'bg-purple-50 text-purple-900 dark:bg-purple-950/50 dark:text-purple-200 border-purple-300 dark:border-purple-700/60',
            'message' => 'Berkas menunggu persetujuan akhir Pengguna Anggaran (PA / Kepala Dinas) dan penerbitan Nota Pencairan Dana (NPD).',
        ],
        'revisi_pa' => [
            'label' => '⚠️ PERLU REVISI: DIKEMBALIKAN OLEH PENGGUNA ANGGARAN (PA)',
            'color' => 'danger',
            'bg' => 'bg-red-50 text-red-900 dark:bg-red-950/50 dark:text-red-200 border-red-300 dark:border-red-700/60',
            'message' => 'Berkas dikembalikan oleh Pengguna Anggaran (Kepala Dinas) untuk perbaikan.',
        ],
        'menunggu_verifikasi_bendahara' => [
            'label' => 'TAHAP 5: MEJA BENDAHARA PENGELUARAN (PENGUJIAN KAS & SP2D)',
            'color' => 'emerald',
            'bg' => 'bg-emerald-50 text-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200 border-emerald-300 dark:border-emerald-700/60',
            'message' => 'NPD telah disahkan PA. Saat ini berkas berada pada meja Bendahara Pengeluaran untuk pengujian ketersediaan kas dan penerbitan pembayaran.',
        ],
        'revisi_bendahara' => [
            'label' => '⚠️ PERLU REVISI: DIKEMBALIKAN OLEH BENDAHARA PENGELUARAN',
            'color' => 'danger',
            'bg' => 'bg-red-50 text-red-900 dark:bg-red-950/50 dark:text-red-200 border-red-300 dark:border-red-700/60',
            'message' => 'Berkas dikembalikan oleh Bendahara Pengeluaran untuk perbaikan administrasi pembayaran.',
        ],
        'disetujui' => [
            'label' => 'TAHAP 5: SIAP DICAIRKAN (DISETUJUI BENDAHARA)',
            'color' => 'teal',
            'bg' => 'bg-teal-50 text-teal-900 dark:bg-teal-950/50 dark:text-teal-200 border-teal-300 dark:border-teal-700/60',
            'message' => 'Pengujian kas selesai. Berkas disetujui penuh oleh Bendahara dan menunggu transfer/pencairan dana.',
        ],
        'dicairkan' => [
            'label' => '✅ SELESAI & DANA TELAH DICAIRKAN (LUNAS)',
            'color' => 'success',
            'bg' => 'bg-green-50 text-green-900 dark:bg-green-950/50 dark:text-green-200 border-green-300 dark:border-green-700/60',
            'message' => 'Dana belanja telah berhasil dicairkan. Realisasi anggaran belanja otomatis tercatat di sistem.',
        ],
        default => [
            'label' => 'STATUS: ' . strtoupper(str_replace('_', ' ', (string) $status)),
            'color' => 'gray',
            'bg' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200 border-gray-300 dark:border-gray-700',
            'message' => $record->status_label,
        ],
    };

    $nominal = (float) ($record->total_nilai_realisasi ?: $record->total_nilai_rencana);
@endphp

<div class="space-y-6 text-gray-900 dark:text-gray-100">
    {{-- 1. Kartu Ringkasan Berkas SPJ --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-white/10 dark:bg-gray-900">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-3 dark:border-white/5">
            <div class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-500/10 text-primary-600 dark:bg-primary-400/10 dark:text-primary-400 font-bold">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </span>
                <div>
                    <h2 class="text-base font-bold tracking-tight text-gray-900 dark:text-white">
                        {{ $record->nomor_pengajuan }}
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $record->jenis_belanja_label }} &bull; {{ $record->metode_pengadaan_label }} &bull; Tahun {{ $record->tahun_anggaran }}
                    </p>
                </div>
            </div>

            <div class="text-right">
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Nilai Belanja:</span>
                <p class="text-base font-extrabold text-primary-600 dark:text-primary-400">
                    Rp {{ number_format($nominal, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 pt-3 text-xs">
            <div>
                <span class="text-gray-500 dark:text-gray-400 block font-medium">Bidang Pengusul:</span>
                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $record->bidang?->nama ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400 block font-medium">Pemohon / Pengusul:</span>
                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $record->user?->name ?? '-' }}</span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400 block font-medium">Sub Kegiatan:</span>
                <span class="font-semibold text-gray-800 dark:text-gray-200 truncate block" title="{{ $record->subKegiatan?->nama_sub_kegiatan ?? '-' }}">
                    {{ $record->subKegiatan?->nama_sub_kegiatan ?? '-' }}
                </span>
            </div>
        </div>
    </div>

    {{-- 2. HERO: Posisi Berkas Saat Ini --}}
    <div class="rounded-xl border {{ $currentHeroInfo['bg'] }} p-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div class="relative flex h-8 w-8 shrink-0 items-center justify-center">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-35 bg-current"></span>
                <span class="relative inline-flex h-7 w-7 items-center justify-center rounded-full bg-current text-white font-bold text-xs">
                    📍
                </span>
            </div>
            <div class="flex-1">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xs font-extrabold uppercase tracking-wider">
                        {{ $currentHeroInfo['label'] }}
                    </span>
                    <span class="rounded-full bg-white/80 dark:bg-black/30 px-2.5 py-0.5 text-xs font-bold shadow-2xs">
                        {{ $record->status_label }}
                    </span>
                </div>
                <p class="mt-1 text-sm font-medium">
                    {{ $currentHeroInfo['message'] }}
                </p>

                {{-- Alert Catatan Revisi jika ada --}}
                @if (filled($record->catatan_revisi) && str_contains($status, 'revisi'))
                    <div class="mt-3 rounded-lg border border-red-300 bg-red-100/80 p-3 text-xs text-red-900 dark:border-red-700 dark:bg-red-950/80 dark:text-red-200">
                        <div class="flex items-center gap-1.5 font-bold mb-1">
                            <svg class="h-4 w-4 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                            </svg>
                            Catatan Perbaikan dari Verifikator:
                        </div>
                        <p class="italic font-mono pl-5 leading-relaxed">
                            "{{ $record->catatan_revisi }}"
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- 3. Visual Timeline Stepper (6 Tahapan Alur SPJ) --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-xs dark:border-white/10 dark:bg-gray-900">
        <h3 class="text-sm font-bold tracking-tight text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <svg class="h-4 w-4 text-primary-600 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            Kronologi Tahapan Proses & Disposisi Dokumen
        </h3>

        <div class="relative pl-3">
            {{-- Garis Penghubung Vertikal --}}
            <div class="absolute left-6 top-3 bottom-6 w-0.5 bg-gray-200 dark:bg-gray-700"></div>

            <div class="space-y-6">
                @foreach ($steps as $num => $step)
                    <div class="relative flex items-start gap-4">
                        {{-- Bulatan Indikator Step --}}
                        <div class="relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold transition shadow-xs
                            {{ match ($step['state']) {
                                'completed' => 'bg-green-500 text-white ring-4 ring-green-100 dark:ring-green-950/60',
                                'active' => 'bg-amber-500 text-white ring-4 ring-amber-100 dark:ring-amber-950/60 animate-pulse',
                                'revision' => 'bg-red-500 text-white ring-4 ring-red-100 dark:ring-red-950/60',
                                default => 'bg-gray-200 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                            } }}">
                            @if ($step['state'] === 'completed')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            @elseif ($step['state'] === 'revision')
                                <span>!</span>
                            @else
                                <span>{{ $num }}</span>
                            @endif
                        </div>

                        {{-- Isi Card Tiap Step --}}
                        <div class="flex-1 rounded-lg border p-3 text-xs transition
                            {{ match ($step['state']) {
                                'completed' => 'border-gray-200 bg-gray-50/60 dark:border-white/5 dark:bg-white/5',
                                'active' => 'border-amber-300 bg-amber-50/50 dark:border-amber-700/50 dark:bg-amber-950/30 ring-1 ring-amber-400/40',
                                'revision' => 'border-red-300 bg-red-50/50 dark:border-red-700/50 dark:bg-red-950/30 ring-1 ring-red-400/40',
                                default => 'border-gray-100 bg-white/40 text-gray-400 dark:border-white/5 dark:bg-transparent dark:text-gray-500 opacity-75',
                            } }}">
                            <div class="flex flex-wrap items-center justify-between gap-1 mb-1">
                                <h4 class="font-bold text-sm {{ $step['state'] === 'pending' ? 'text-gray-500 dark:text-gray-400' : 'text-gray-900 dark:text-white' }}">
                                    Tahap {{ $num }}: {{ $step['title'] }}
                                </h4>
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide
                                    {{ match ($step['state']) {
                                        'completed' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                        'active' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 font-extrabold',
                                        'revision' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 font-extrabold',
                                        default => 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400',
                                    } }}">
                                    {{ match ($step['state']) {
                                        'completed' => 'Selesai',
                                        'active' => 'Sedang Diproses',
                                        'revision' => 'Perlu Revisi',
                                        default => 'Menunggu Giliran',
                                    } }}
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 text-gray-600 dark:text-gray-300 my-1 font-medium">
                                <span class="inline-flex items-center gap-1 font-semibold text-gray-800 dark:text-gray-200">
                                    👤 {{ $step['actor'] }}
                                </span>
                                <span class="text-gray-400">&bull;</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ $step['role_label'] }}</span>
                                @if ($step['date'])
                                    <span class="text-gray-400">&bull;</span>
                                    <span class="text-primary-600 dark:text-primary-400 font-semibold">📅 {{ $step['date'] }}</span>
                                @endif
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 mt-1 leading-relaxed">
                                {{ $step['desc'] }}
                            </p>

                            {{-- Keterangan Khusus Tahap Selesai / Cair --}}
                            @if ($num === 6 && $status === 'dicairkan')
                                <div class="mt-2.5 flex flex-wrap items-center gap-3 rounded-md bg-teal-50 border border-teal-200 p-2 text-teal-900 dark:bg-teal-950/40 dark:border-teal-800 dark:text-teal-200 font-mono text-[11px]">
                                    <div><strong>Tanggal Cair:</strong> {{ $record->tanggal_dicairkan?->translatedFormat('d F Y') ?? '-' }}</div>
                                    <div><strong>Nomor BKU:</strong> {{ $record->nomor_bku ?? '-' }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 4. Audit Trail Lengkap: Riwayat Mutasi Status (riwayat_status_spj) --}}
    @if ($record->riwayats && $record->riwayats->isNotEmpty())
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-white/10 dark:bg-gray-900">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3 flex items-center gap-2">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0 1 18 0z" />
                </svg>
                Log Disposisi & Perjalanan Berkas SPJ
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400">
                            <th class="py-2 pr-3 font-semibold">Waktu</th>
                            <th class="py-2 px-3 font-semibold">Eksekutor</th>
                            <th class="py-2 px-3 font-semibold">Peran</th>
                            <th class="py-2 px-3 font-semibold">Perubahan Status</th>
                            <th class="py-2 pl-3 font-semibold">Catatan / Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach ($record->riwayats as $log)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition">
                                <td class="py-2.5 pr-3 font-mono text-[11px] whitespace-nowrap text-gray-500 dark:text-gray-400">
                                    {{ $log->created_at ? $log->created_at->translatedFormat('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="py-2.5 px-3 font-bold text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                    {{ $log->user?->name ?? 'Sistem' }}
                                </td>
                                <td class="py-2.5 px-3 uppercase text-[10px] font-semibold text-gray-500 dark:text-gray-400">
                                    {{ $log->peran_saat_tindakan ?? '-' }}
                                </td>
                                <td class="py-2.5 px-3 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="text-gray-400">{{ $log->status_sebelumnya ?? '-' }}</span>
                                        <span class="text-gray-400 font-bold">&rarr;</span>
                                        <span class="font-bold text-primary-600 dark:text-primary-400">{{ $log->status_berikutnya ?? '-' }}</span>
                                    </span>
                                </td>
                                <td class="py-2.5 pl-3 text-gray-600 dark:text-gray-300 italic">
                                    {{ $log->catatan ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
