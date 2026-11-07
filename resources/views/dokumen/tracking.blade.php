<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking & Verifikasi Dokumen - SAKTI Kab. Sijunjung</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased min-h-screen flex flex-col justify-between">

    <!-- Top Navigation / Brand Bar -->
    <header class="bg-gradient-to-r from-emerald-800 via-teal-800 to-emerald-900 text-white shadow-md">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center p-1.5 border border-white/20 shadow-inner">
                    @php
                        $logoInstansi = $pengaturan?->logo ? asset('storage/' . $pengaturan->logo) : null;
                    @endphp
                    @if ($logoInstansi)
                        <img src="{{ $logoInstansi }}" alt="Logo Instansi" class="w-full h-full object-contain">
                    @else
                        <svg class="w-6 h-6 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    @endif
                </div>
                <div>
                    <h1 class="text-base font-bold tracking-tight text-white leading-tight">SAKTI</h1>
                    <p class="text-[11px] text-emerald-200 font-medium">Pemerintah Kabupaten Sijunjung</p>
                </div>
            </div>
            <a href="{{ route('portal') }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 transition-all border border-white/25 flex items-center gap-1.5">
                <span>Beranda</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-4xl mx-auto px-4 py-6 w-full flex-1">

        @if ($isValid && $spj)
            @php
                $nominal = (float) ($spj->total_nilai_realisasi ?: $spj->total_nilai_rencana);
                $terbilang = \App\Services\Spj\SpjWorkflowService::terbilang($nominal);
                $namaDinas = $pengaturan?->nama_dinas ?? 'DINAS PARIWISATA PEMUDA DAN OLAHRAGA';
            @endphp

            <!-- Status Banner: VALID & TERVERIFIKASI -->
            <div class="bg-white rounded-2xl shadow-sm border border-emerald-100 overflow-hidden mb-5">
                <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-5 text-white flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center shrink-0 border border-white/30 shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white text-emerald-800 uppercase tracking-wider mb-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                            Terverifikasi Resmi
                        </div>
                        <h2 class="text-lg md:text-xl font-extrabold leading-snug">DOKUMEN VALID & TERCATAT RESMI</h2>
                        <p class="text-xs text-emerald-100">Dokumen transaksi ini tercatat sah pada sistem SAKTI Pemerintah Kabupaten Sijunjung.</p>
                    </div>
                </div>

                <!-- Fast Summary Pills -->
                <div class="bg-emerald-50/70 border-b border-emerald-100 px-5 py-3 grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                    <div>
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Nomor Pengajuan</span>
                        <span class="font-bold text-slate-800 break-words">{{ $spj->nomor_pengajuan }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Tanggal Terbit</span>
                        <span class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($spj->tanggal_pengajuan)->translatedFormat('d F Y') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Tahun Anggaran</span>
                        <span class="font-bold text-slate-800">{{ $spj->tahun_anggaran }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 text-[10px] uppercase font-semibold block">Status Saat Ini</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            {{ $spj->status_label }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Detail Grid (Simpel Namun Jelas) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">

                <!-- Kartu Rincian Transaksi & Belanja -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80">
                    <div class="flex items-center gap-2 mb-4 pb-2 border-b border-slate-100">
                        <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">
                            1
                        </div>
                        <h3 class="font-bold text-sm text-slate-800">Rincian Transaksi & Belanja</h3>
                    </div>

                    <dl class="space-y-3 text-xs">
                        <div class="flex justify-between items-start gap-2">
                            <dt class="text-slate-500 shrink-0">Jenis Belanja</dt>
                            <dd class="font-semibold text-slate-800 text-right">{{ $spj->jenis_belanja_label }}</dd>
                        </div>
                        <div class="flex justify-between items-start gap-2">
                            <dt class="text-slate-500 shrink-0">Metode Pengadaan</dt>
                            <dd class="font-semibold text-slate-800 text-right">{{ $spj->metode_pengadaan_label }}</dd>
                        </div>
                        <div class="flex justify-between items-start gap-2">
                            <dt class="text-slate-500 shrink-0">Pemasok / Rekanan</dt>
                            <dd class="font-semibold text-slate-800 text-right">{{ $spj->nama_toko ?: ($spj->penerima_nama ?: '-') }}</dd>
                        </div>
                        <div class="flex justify-between items-start gap-2">
                            <dt class="text-slate-500 shrink-0">Kegiatan / Program</dt>
                            <dd class="font-semibold text-slate-800 text-right">
                                {{ $spj->kegiatan?->nama_kegiatan ?? '-' }}
                            </dd>
                        </div>
                        <div class="pt-2 border-t border-slate-100">
                            <span class="text-slate-500 block text-[11px] mb-0.5">Total Nilai Transaksi:</span>
                            <div class="text-lg font-extrabold text-emerald-700">
                                Rp {{ number_format($nominal, 0, ',', '.') }}
                            </div>
                            <div class="text-[11px] text-slate-500 italic">
                                "{{ ucwords(strtolower($terbilang)) }}"
                            </div>
                        </div>
                    </dl>
                </div>

                <!-- Kartu Unit Kerja & Verifikator -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80">
                    <div class="flex items-center gap-2 mb-4 pb-2 border-b border-slate-100">
                        <div class="w-7 h-7 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-xs">
                            2
                        </div>
                        <h3 class="font-bold text-sm text-slate-800">Unit Kerja & Verifikator</h3>
                    </div>

                    <dl class="space-y-3 text-xs">
                        <div class="flex justify-between items-start gap-2">
                            <dt class="text-slate-500 shrink-0">Instansi / SKPD</dt>
                            <dd class="font-semibold text-slate-800 text-right">{{ $namaDinas }}</dd>
                        </div>
                        <div class="flex justify-between items-start gap-2">
                            <dt class="text-slate-500 shrink-0">Bidang / Sub Bagian</dt>
                            <dd class="font-semibold text-slate-800 text-right">{{ $spj->bidang?->nama ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between items-start gap-2">
                            <dt class="text-slate-500 shrink-0">Pemohon / Pengaju</dt>
                            <dd class="font-semibold text-slate-800 text-right">{{ $spj->user?->name ?? '-' }}</dd>
                        </div>

                        <!-- Status Rantai Pejabat -->
                        <div class="pt-2 border-t border-slate-100 space-y-1.5">
                            <span class="text-slate-500 text-[10px] uppercase font-semibold block mb-1">Rantai Verifikasi Pejabat Resmi:</span>
                            
                            <div class="flex items-center justify-between text-[11px] bg-slate-50 p-1.5 rounded-lg border border-slate-100">
                                <span class="text-slate-600 font-medium">PPTK (Pejabat Pelaksana Teknis)</span>
                                <span class="font-semibold text-slate-800">{{ $spj->pptk?->name ?? ($spj->bidang?->kepala?->name ?? 'Tercatat') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] bg-slate-50 p-1.5 rounded-lg border border-slate-100">
                                <span class="text-slate-600 font-medium">PPK (Pejabat Pembuat Komitmen)</span>
                                <span class="font-semibold text-slate-800">{{ $spj->ppk?->name ?? 'Tercatat' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] bg-slate-50 p-1.5 rounded-lg border border-slate-100">
                                <span class="text-slate-600 font-medium">Pengguna Anggaran (PA / Kadis)</span>
                                <span class="font-semibold text-slate-800">{{ $spj->pa?->name ?? ($pengaturan?->nama_kepala_dinas ?? 'Tercatat') }}</span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] bg-slate-50 p-1.5 rounded-lg border border-slate-100">
                                <span class="text-slate-600 font-medium">Bendahara Pengeluaran</span>
                                <span class="font-semibold text-slate-800">{{ $spj->bendahara?->name ?? 'Tercatat' }}</span>
                            </div>
                        </div>
                    </dl>
                </div>

            </div>

            <!-- Jaminan Keamanan Digital -->
            <div class="bg-emerald-50/60 rounded-2xl p-4 border border-emerald-200/70 text-xs text-slate-600 flex items-start gap-3 mb-6">
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div class="leading-relaxed">
                    <span class="font-bold text-emerald-900 block text-xs mb-0.5">Jejak Digital Terverifikasi Real-Time</span>
                    Pengecekan keaslian dokumen ini dilakukan langsung terhadap basis data terenkripsi SAKTI pada <span class="font-semibold text-slate-800">{{ now()->translatedFormat('d F Y, H:i') }} WIB</span>. Dokumen fisik yang memuat QR Code ini diakui sah selama nomor pengajuan dan rincian transaksi berpadanan dengan data di atas.
                </div>
            </div>

        @else

            <!-- Status Banner: DOKUMEN TIDAK DITEMUKAN -->
            <div class="bg-white rounded-2xl shadow-sm border border-rose-100 overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-rose-600 to-red-600 p-6 text-white flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center shrink-0 border border-white/30 shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white text-rose-800 uppercase tracking-wider mb-1">
                            Peringatan
                        </div>
                        <h2 class="text-lg md:text-xl font-extrabold leading-snug">DOKUMEN TIDAK DITEMUKAN / TIDAK VALID</h2>
                        <p class="text-xs text-rose-100">Nomor dokumen transaksi yang Anda cari tidak terdaftar dalam database resmi sistem SAKTI.</p>
                    </div>
                </div>

                <div class="p-5 text-xs text-slate-600 leading-relaxed space-y-2">
                    <p>
                        @if ($nomorDitelusuri)
                            Nomor pengajuan yang ditelusuri: <strong class="text-slate-800 font-mono bg-slate-100 px-2 py-0.5 rounded">{{ $nomorDitelusuri }}</strong>.
                        @else
                            Tidak ada parameter nomor dokumen transaksi yang diberikan.
                        @endif
                    </p>
                    <p class="text-slate-500">
                        Harap periksa kembali cetakan dokumen fisik Anda atau scan ulang QR Code dengan pencahayaan yang cukup. Jika Anda meyakini dokumen ini sah, hubungi pengelola administrasi keuangan atau bidang terkait di dinas.
                    </p>
                </div>
            </div>

        @endif

        <!-- Form Pencarian Ulang / Cek Nomor Lain -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80">
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Periksa Dokumen Transaksi Lain
            </h3>
            <form action="{{ route('dokumen.tracking') }}" method="GET" class="flex flex-col sm:flex-row gap-2 mt-2">
                <input 
                    type="text" 
                    name="nomor" 
                    value="{{ $nomorDitelusuri ?? '' }}" 
                    placeholder="Masukkan Nomor Pengajuan SPJ (Contoh: SP/Sekre/10/2026/0002)" 
                    class="flex-1 px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-slate-800"
                    required
                >
                <button 
                    type="submit" 
                    class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-sm hover:shadow transition-all flex items-center justify-center gap-1.5 shrink-0"
                >
                    <span>Lacak Dokumen</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </form>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200/80 mt-8 py-4">
        <div class="max-w-4xl mx-auto px-4 text-center text-xs text-slate-500">
            <p class="font-medium text-slate-600">&copy; {{ date('Y') }} SAKTI &bull; Sistem Administrasi, Kinerja, dan Tata Informasi</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Pemerintah Kabupaten Sijunjung &bull; Dinas Pariwisata Pemuda dan Olahraga</p>
        </div>
    </footer>

</body>
</html>

