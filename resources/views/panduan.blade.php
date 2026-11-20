<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Panduan Pengguna – Portal Parpora Sijunjung (SAKTI Parpora)</title>
    <meta name="description" content="Panduan lengkap cara login, akses menu, presensi mobile, LHK, izin/cuti, SPJ, dan instalasi PWA Portal Parpora & SAKTI Disparpora Kabupaten Sijunjung.">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* Light Mode untuk Panduan Pengguna */
        html.panduan-light body {
            background-color: #f8fafc !important;
            color: #0f172a !important;
        }
        html.panduan-light header {
            background-color: rgba(255, 255, 255, 0.92) !important;
            border-color: rgba(226, 232, 240, 0.9) !important;
        }
        html.panduan-light .text-white {
            color: #0f172a !important;
        }
        html.panduan-light .text-slate-200,
        html.panduan-light .text-slate-300 {
            color: #334155 !important;
        }
        html.panduan-light .text-slate-400,
        html.panduan-light .text-slate-500 {
            color: #475569 !important;
        }
        html.panduan-light .bg-white\/\[0\.04\],
        html.panduan-light .bg-white\/\[0\.03\],
        html.panduan-light .bg-white\/5 {
            background-color: #ffffff !important;
            border-color: #e2e8f0 !important;
            box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
        }
        html.panduan-light .text-sky-300 {
            color: #0284c7 !important;
        }
        html.panduan-light .text-emerald-300 {
            color: #059669 !important;
        }
        html.panduan-light .text-amber-300 {
            color: #d97706 !important;
        }
        html.panduan-light .text-purple-300 {
            color: #7c3aed !important;
        }
        html.panduan-light .text-rose-300 {
            color: #e11d48 !important;
        }
        html.panduan-light .text-teal-300 {
            color: #0d9488 !important;
        }
        html.panduan-light footer {
            border-color: #e2e8f0 !important;
            background-color: #ffffff !important;
        }
    </style>
    <script>
        (function () {
            const saved = localStorage.getItem('portal_theme_override') || localStorage.getItem('rhoe_theme_override');
            const hour = new Date().getHours();
            const isLight = saved ? saved === 'light' : (hour >= 6 && hour < 18);
            document.documentElement.classList.toggle('panduan-light', isLight);
        })();
        window.togglePanduanTheme = function () {
            const isLight = document.documentElement.classList.toggle('panduan-light');
            const val = isLight ? 'light' : 'dark';
            localStorage.setItem('portal_theme_override', val);
            localStorage.setItem('rhoe_theme_override', val);
            const icon = document.getElementById('panduanThemeIcon');
            if (icon) {
                icon.className = isLight ? 'fa-solid fa-moon text-slate-700 text-xs' : 'fa-solid fa-sun text-amber-400 text-xs';
            }
        };
    </script>
</head>
<body class="min-h-full bg-[#070B1A] text-slate-200 antialiased selection:bg-sky-500/30">
    <!-- Top Navbar -->
    <header class="sticky top-0 z-30 bg-[#070B1A]/90 backdrop-blur-xl border-b border-white/10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-3">
            <a href="{{ route('portal') }}" class="flex items-center gap-2.5 group">
                <img src="{{ asset('favicon.png') }}" alt="Logo Portal Parpora" class="w-8 h-8 object-contain">
                <div>
                    <div class="text-sm font-extrabold text-white tracking-tight group-hover:text-sky-300 transition-colors">PORTAL PARPORA</div>
                    <div class="text-[10px] text-slate-400">Panduan Pengguna Sistem</div>
                </div>
            </a>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.togglePanduanTheme()" title="Ganti Mode Terang / Gelap"
                    class="w-9 h-9 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 flex items-center justify-center transition-colors cursor-pointer">
                    <i id="panduanThemeIcon" class="fa-solid fa-sun text-amber-400 text-xs"></i>
                </button>
                <a href="{{ route('portal') }}"
                    class="px-3.5 py-2 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 text-xs font-bold text-slate-200 transition-colors inline-flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left text-[11px]"></i>
                    <span>Kembali ke Portal</span>
                </a>
                <a href="{{ url('/admin') }}"
                    class="hidden sm:inline-flex px-3.5 py-2 rounded-xl bg-sky-500 hover:bg-sky-400 text-slate-950 text-xs font-extrabold transition-colors items-center gap-1.5">
                    <i class="fa-solid fa-right-to-bracket text-[11px]"></i>
                    <span>Masuk SAKTI</span>
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8 sm:py-12 space-y-10">
        <!-- Hero -->
        <section class="text-center max-w-2xl mx-auto">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-sky-500/15 border border-sky-400/30 text-sky-300 text-[11px] font-bold uppercase tracking-wider mb-3">
                <i class="fa-solid fa-book-open"></i>
                Panduan Resmi Pengguna
            </span>
            <h1 class="text-2xl sm:text-4xl font-extrabold text-white tracking-tight mb-3">
                Panduan Login &amp; Akses Menu
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">
                Petunjuk singkat bagi ASN &amp; Pegawai Dinas Pariwisata, Pemuda dan Olahraga Kabupaten Sijunjung untuk masuk ke sistem dan menggunakan layanan digital di <strong class="text-slate-200">Portal Parpora (SAKTI)</strong>.
            </p>
        </section>

        <!-- Bagian 1: Cara Login -->
        <section class="space-y-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-sky-500/20 border border-sky-400/30 text-sky-300 flex items-center justify-center font-extrabold text-sm">1</div>
                <h2 class="text-lg sm:text-xl font-extrabold text-white">Cara Login ke Sistem</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="rounded-2xl bg-white/[0.04] border border-white/10 p-5 space-y-2.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-300 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                    </div>
                    <h3 class="text-sm font-bold text-white">Login Mobile Pegawai</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Digunakan untuk <strong>Presensi GPS</strong>, <strong>LHK Harian</strong>, dan <strong>Pengajuan Izin/Cuti</strong> dari HP.
                    </p>
                    <ol class="text-xs text-slate-300 space-y-1 list-decimal list-inside pt-1">
                        <li>Buka <strong>Portal Parpora</strong> lalu pilih menu Mobile / tombol <strong>SAKTI</strong>.</li>
                        <li>Masukkan <strong>Email</strong> dan <strong>Password</strong> akun pegawai, atau klik <strong>Masuk dengan Google</strong>.</li>
                        <li>Izinkan akses <strong>Lokasi (GPS)</strong> dan <strong>Kamera</strong> saat diminta browser.</li>
                    </ol>
                </div>

                <div class="rounded-2xl bg-white/[0.04] border border-white/10 p-5 space-y-2.5">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-300 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-desktop"></i>
                    </div>
                    <h3 class="text-sm font-bold text-white">Login Panel Admin / Kerja</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Digunakan untuk <strong>Data Pegawai</strong>, <strong>SPJ Keuangan</strong>, <strong>Surat Tugas</strong>, dan <strong>Aset BMD</strong>.
                    </p>
                    <ol class="text-xs text-slate-300 space-y-1 list-decimal list-inside pt-1">
                        <li>Klik kartu <strong>SAKTI</strong> pada Portal atau buka <code class="text-sky-300">/admin</code>.</li>
                        <li>Login dengan <strong>Email &amp; Password</strong> atau <strong>Google SSO</strong> yang terdaftar.</li>
                        <li>Menu di sidebar otomatis menyesuaikan <strong>Role / Hak Akses</strong> Anda.</li>
                    </ol>
                </div>

                <div class="rounded-2xl bg-white/[0.04] border border-white/10 p-5 space-y-2.5">
                    <div class="w-10 h-10 rounded-xl bg-violet-500/20 text-violet-300 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <h3 class="text-sm font-bold text-white">Kendala Login / Lupa Password</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Jika lupa password atau email Google belum terhubung dengan akun pegawai:
                    </p>
                    <ul class="text-xs text-slate-300 space-y-1 list-disc list-inside pt-1">
                        <li>Gunakan menu <strong>Lupa Password</strong> di halaman login.</li>
                        <li>Atau hubungi <strong>Admin Dinas / Kasubag Umum &amp; Kepegawaian</strong> untuk reset password.</li>
                        <li>Setelah login, segera perbarui password di menu <strong>Profil</strong>.</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- Bagian 2: Panduan Akses Menu -->
        <section class="space-y-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-sky-500/20 border border-sky-400/30 text-sky-300 flex items-center justify-center font-extrabold text-sm">2</div>
                <h2 class="text-lg sm:text-xl font-extrabold text-white">Panduan Akses Menu Utama</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="rounded-2xl bg-white/[0.03] border border-white/10 p-4 space-y-1.5">
                    <div class="flex items-center gap-2 text-sky-300 font-bold text-sm">
                        <i class="fa-solid fa-fingerprint"></i>
                        <span>Presensi Harian</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Lakukan presensi masuk dan pulang melalui halaman Mobile saat berada di dalam radius lokasi kantor (WFO) atau sesuai status penugasan.
                    </p>
                </div>

                <div class="rounded-2xl bg-white/[0.03] border border-white/10 p-4 space-y-1.5">
                    <div class="flex items-center gap-2 text-emerald-300 font-bold text-sm">
                        <i class="fa-solid fa-clipboard-check"></i>
                        <span>LHK &amp; Rencana Aksi</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Catat aktivitas harian pada menu <strong>LHK</strong> sesuai Rencana Aksi bidang/jabatan, lalu tunggu verifikasi dari atasan langsung.
                    </p>
                </div>

                <div class="rounded-2xl bg-white/[0.03] border border-white/10 p-4 space-y-1.5">
                    <div class="flex items-center gap-2 text-amber-300 font-bold text-sm">
                        <i class="fa-solid fa-calendar-day"></i>
                        <span>Izin &amp; Cuti Pegawai</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Ajukan izin/cuti beserta lampiran pendukung. Status persetujuan dapat dipantau langsung dan otomatis tersinkron ke rekap presensi.
                    </p>
                </div>

                <div class="rounded-2xl bg-white/[0.03] border border-white/10 p-4 space-y-1.5">
                    <div class="flex items-center gap-2 text-purple-300 font-bold text-sm">
                        <i class="fa-solid fa-id-card"></i>
                        <span>Profil &amp; Riwayat Pegawai</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Kelola identitas pegawai serta unggah berkas PDF pada tab <strong>Riwayat Pendidikan</strong>, <strong>Riwayat Jabatan</strong>, dan <strong>Pangkat &amp; Golongan</strong>.
                    </p>
                </div>

                <div class="rounded-2xl bg-white/[0.03] border border-white/10 p-4 space-y-1.5">
                    <div class="flex items-center gap-2 text-rose-300 font-bold text-sm">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                        <span>Pengajuan SPJ &amp; Keuangan</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Alur berjenjang mulai dari pengusul (JF/Staf) → verifikasi <strong>PPTK</strong> → <strong>PPK</strong> → <strong>PA (Kadis)</strong> → pencairan oleh <strong>Bendahara</strong>.
                    </p>
                </div>

                <div class="rounded-2xl bg-white/[0.03] border border-white/10 p-4 space-y-1.5">
                    <div class="flex items-center gap-2 text-teal-300 font-bold text-sm">
                        <i class="fa-solid fa-boxes-stacked"></i>
                        <span>Surat Tugas &amp; Aset BMD</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Penerbitan Surat Tugas Tim Kreatif/Dinas serta pengelolaan Buku Inventaris &amp; Berita Acara BMD oleh Admin/Pengurus Barang.
                    </p>
                </div>
            </div>
        </section>

        <!-- Bagian 3: Instal Aplikasi di Android (PWA) -->
        <section class="rounded-2xl bg-gradient-to-r from-sky-500/10 via-indigo-500/10 to-emerald-500/10 border border-sky-400/20 p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="inline-flex items-center gap-2 text-sky-300 font-extrabold text-sm">
                        <i class="fa-brands fa-android text-lg"></i>
                        <span>Tips: Instal Aplikasi ke Layar Utama HP Android (PWA)</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Buka <code class="text-sky-300">sakti.sijunjung.cloud/mobile/dashboard</code> atau <code class="text-sky-300">sakti.sijunjung.cloud/admin</code> di <strong>Google Chrome Android</strong> → ketuk ikon <strong>titik tiga (⋮)</strong> di pojok kanan atas → pilih <strong>Instal Aplikasi / Tambahkan ke Layar Utama</strong>.
                    </p>
                </div>
                <a href="{{ route('portal') }}"
                    class="shrink-0 px-4 py-2.5 rounded-xl bg-sky-500 hover:bg-sky-400 text-slate-950 font-extrabold text-xs text-center transition-colors">
                    Buka Portal Sekarang
                </a>
            </div>
        </section>
    </main>

    <footer class="border-t border-white/10 py-6 text-center text-[11px] text-slate-500">
        &copy; {{ date('Y') }} Dinas Pariwisata, Pemuda dan Olahraga Kabupaten Sijunjung — Portal Parpora (SAKTI)
    </footer>
</body>
</html>
