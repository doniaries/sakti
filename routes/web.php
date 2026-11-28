<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Mobile\Auth\Login;
use App\Livewire\Mobile\Dashboard;
use App\Livewire\Mobile\Absensi;
use App\Livewire\Mobile\Riwayat;
use App\Livewire\Mobile\Lhk\Index as LhkIndex;
use App\Livewire\Mobile\Lhk\Form as LhkForm;

Route::get('/', function (\Illuminate\Http\Request $request) {
    $pengaturan = \App\Models\PengaturanAplikasi::getPengaturan();
    $portalLinks = \App\Models\PortalLink::aktif()->get();
    $tema = $request->query('tema', $pengaturan->portal_theme ?? 'default');
    $view = $tema === 'rhoe' ? 'welcome-rhoe' : 'welcome';

    return view($view, compact('pengaturan', 'portalLinks'));
})->name('portal');

Route::get('/panduan', fn () => view('panduan'))->name('panduan');
Route::get('/mobile', fn () => redirect()->route('mobile.dashboard'));

Route::post('/portal/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('mobile.login');
})->name('portal.logout');

// Route Pelacakan & Verifikasi Keaslian Dokumen Transaksi via QR Code (Akses Publik)
Route::get('/lacak-dokumen/{kode?}', [\App\Http\Controllers\DokumenTrackingController::class, 'track'])
    ->name('dokumen.tracking');

Route::get('/verifikasi-surat-tugas/{kode?}', [\App\Http\Controllers\SuratTugasPrintController::class, 'verifikasi'])
    ->name('surat-tugas.verifikasi');

Route::get('/verifikasi-izin/{kode?}', [\App\Http\Controllers\CetakLaporanController::class, 'verifikasiIzin'])
    ->name('pengajuan-izin.verifikasi');

Route::get('/mobile/google-native', function (\Illuminate\Http\Request $request) {
    $idToken = (string) $request->query('id_token', '');
    if ($idToken === '') {
        return redirect()->route('mobile.login');
    }
    $res = \Illuminate\Support\Facades\Http::get('https://oauth2.googleapis.com/tokeninfo', ['id_token' => $idToken]);
    if (! $res->ok()) {
        return redirect()->route('mobile.login')->with('notify', ['type' => 'error', 'title' => 'Gagal Masuk', 'message' => 'Token Google tidak valid.']);
    }
    $email = strtolower((string) ($res->json('email') ?? ''));
    $user = \App\Models\User::findByLoginEmail($email);
    if (! $user || ! $user->aktif) {
        return redirect()->route('mobile.login')->with('notify', ['type' => 'error', 'title' => 'Gagal Masuk', 'message' => 'Akun Google belum terdaftar atau nonaktif.']);
    }
    \Illuminate\Support\Facades\Auth::login($user, true);
    $request->session()->regenerate();
    return redirect()->route('mobile.dashboard');
})->name('mobile.google-native');

Route::prefix('mobile')->group(function () {
    Route::get('/login', Login::class)->name('mobile.login');
    Route::get('/masuk', Login::class)->name('login');

    // Signed PDF/Cetak routes untuk akses dari aplikasi native Flutter (tanpa cookie sesi browser)
    Route::middleware('signed')->group(function () {
        Route::get('/riwayat/cetak-pdf/signed', [\App\Http\Controllers\CetakLaporanController::class, 'cetakRiwayat'])->name('mobile.riwayat.signed-pdf');
        Route::get('/pengajuan-izin/{pengajuanIzin}/cetak-pdf/signed', [\App\Http\Controllers\CetakLaporanController::class, 'cetakIzinCuti'])->name('mobile.izin.signed-pdf');
    });

    
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('mobile.dashboard');
        Route::get('/absensi', Absensi::class)->name('mobile.absensi');
        Route::get('/riwayat', Riwayat::class)->name('mobile.riwayat');
        Route::get('/riwayat/cetak-pdf', [\App\Http\Controllers\CetakLaporanController::class, 'cetakRiwayat'])->name('mobile.riwayat.cetak-pdf');
        Route::middleware('module:lhk')->group(function () {
            Route::get('/lhk', LhkIndex::class)->name('mobile.lhk.index');
            Route::get('/lhk/buat', LhkForm::class)->name('mobile.lhk.create');
            Route::get('/bank-aktivitas', \App\Livewire\Mobile\Lhk\BankAktivitas::class)->name('mobile.lhk.bank-aktivitas');
            Route::get('/reputasi', \App\Livewire\Mobile\Reputasi::class)->name('mobile.reputasi');
        });
        Route::get('/profil', \App\Livewire\Mobile\Profil::class)->name('mobile.profil');
        Route::get('/kepegawaian', \App\Livewire\Mobile\Kepegawaian::class)->name('mobile.kepegawaian');
        Route::get('/kelengkapan-pegawai', \App\Livewire\Mobile\KelengkapanPegawai::class)->name('mobile.kelengkapan-pegawai');
        Route::get('/agenda', \App\Livewire\Mobile\Agenda::class)->name('mobile.agenda');
        Route::get('/agenda/buat', \App\Livewire\Mobile\AgendaForm::class)->name('mobile.agenda.create');
        Route::get('/agenda/{agenda}/ubah', \App\Livewire\Mobile\AgendaForm::class)->name('mobile.agenda.edit');
        Route::get('/surat-tugas', \App\Livewire\Mobile\SuratTugas\Index::class)->name('mobile.surat-tugas.index');
        Route::get('/surat-tugas/buat', \App\Livewire\Mobile\SuratTugas\Form::class)->name('mobile.surat-tugas.create');
        Route::get('/surat-tugas/{suratTugas}/edit', \App\Livewire\Mobile\SuratTugas\Form::class)->name('mobile.surat-tugas.edit');
        Route::post('/push-subscribe', function (\Illuminate\Http\Request $request) {
            /** @var \App\Models\User|null $user */
            $user = \Illuminate\Support\Facades\Auth::user();
            if (! $user) {
                return response()->json(['ok' => false], 401);
            }
            $data = $request->validate([
                'endpoint' => ['required', 'url'],
                'keys.p256dh' => ['required', 'string'],
                'keys.auth' => ['required', 'string'],
            ]);
            $user->updatePushSubscription(
                $data['endpoint'],
                $data['keys']['p256dh'],
                $data['keys']['auth']
            );
            return response()->json(['ok' => true]);
        })->name('mobile.push-subscribe');
        Route::post('/fcm-token', [\App\Http\Controllers\Api\NotificationController::class, 'saveFcmToken'])
            ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
            ->name('mobile.fcm-token');
        Route::middleware('module:izin')->group(function () {
            Route::get('/izin', \App\Livewire\Mobile\Izin\Index::class)->name('mobile.izin.index');
            Route::get('/izin/buat', \App\Livewire\Mobile\Izin\Form::class)->name('mobile.izin.create');
        });
        Route::get('/pengajuan-izin/{pengajuanIzin}/cetak-pdf', [\App\Http\Controllers\CetakLaporanController::class, 'cetakIzinCuti'])->name('pengajuan-izin.cetak-pdf');
        Route::get('/admin/pegawai/cetak-belum-lengkap', [\App\Http\Controllers\CetakLaporanController::class, 'cetakPegawaiBelumLengkap'])->name('pegawai.cetak-belum-lengkap');
        Route::get('/admin/pegawai/cetak-bezeting', [\App\Http\Controllers\CetakLaporanController::class, 'cetakBezetingPegawai'])->name('pegawai.cetak-bezeting');
        Route::get('/persetujuan', \App\Livewire\Mobile\Persetujuan::class)->name('mobile.persetujuan');
        Route::get('/wfh', \App\Livewire\Mobile\Wfh\Index::class)->middleware('module:wfh')->name('mobile.wfh.index');
        Route::get('/hari-libur', \App\Livewire\Mobile\HariLibur::class)->name('mobile.hari-libur');
        Route::get('/pengaturan-lokasi', \App\Livewire\Mobile\PengaturanLokasi::class)->name('mobile.pengaturan-lokasi');
        Route::get('/pengaturan-modul', \App\Livewire\Mobile\PengaturanModul::class)->name('mobile.pengaturan-modul');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/admin/pengajuan-spj/{record}/preview', [\App\Http\Controllers\SpjPrintController::class, 'preview'])
        ->name('admin.pengajuan-spj.preview');
    Route::get('/admin/pengajuan-spj/{record}/cetak', [\App\Http\Controllers\SpjPrintController::class, 'print'])
        ->name('admin.pengajuan-spj.cetak');
    Route::get('/admin/laporan/lrfk/preview', [\App\Http\Controllers\LaporanPrintController::class, 'previewLrfk'])
        ->name('admin.laporan.lrfk.preview');
    Route::get('/admin/laporan/lrfk/cetak', [\App\Http\Controllers\LaporanPrintController::class, 'cetakLrfk'])
        ->name('admin.laporan.lrfk.cetak');
    Route::get('/admin/laporan/fungsional/preview', [\App\Http\Controllers\LaporanPrintController::class, 'previewLpjFungsional'])
        ->name('admin.laporan.fungsional.preview');
    Route::get('/admin/laporan/fungsional/cetak', [\App\Http\Controllers\LaporanPrintController::class, 'cetakLpjFungsional'])
        ->name('admin.laporan.fungsional.cetak');
    Route::get('/admin/surat-tugas/{suratTugas}/cetak-pdf', [\App\Http\Controllers\SuratTugasPrintController::class, 'cetak'])
        ->name('surat-tugas.cetak-pdf');
    Route::get('/admin/surat-tugas/{suratTugas}/laporan-pdf', [\App\Http\Controllers\SuratTugasPrintController::class, 'cetakLaporan'])
        ->name('surat-tugas.laporan-pdf');
    Route::get('/admin/surat-tugas/{suratTugas}/download-word', [\App\Http\Controllers\SuratTugasPrintController::class, 'downloadWord'])
        ->name('surat-tugas.download-word');
    Route::get('/admin/buku-inventaris/cetak', [\App\Http\Controllers\BukuInventarisPrintController::class, 'cetak'])
        ->name('admin.buku-inventaris.cetak');
    Route::get('/admin/berita-acara-bmd/{beritaAcaraBmd}/cetak', [\App\Http\Controllers\BeritaAcaraBmdPrintController::class, 'cetak'])
        ->name('admin.berita-acara-bmd.cetak');
    Route::get('/admin/pegawai/berkas/preview', function (\Illuminate\Http\Request $request) {
        /** @var \App\Models\User|null $authUser */
        $authUser = \Illuminate\Support\Facades\Auth::user();
        abort_unless($authUser, 403);

        $path = ltrim(str_replace('\\', '/', (string) $request->query('path', '')), '/');
        abort_if($path === '' || str_contains($path, '..'), 404);

        $baseDir = realpath(storage_path('app/public'));
        $fullPath = realpath(storage_path('app/public/' . $path));
        abort_if($baseDir === false || $fullPath === false || ! str_starts_with($fullPath, $baseDir . DIRECTORY_SEPARATOR) || ! is_file($fullPath), 404);

        if (preg_match('#^dokumen_pegawai/(\d+)/#', $path, $matches)) {
            $owner = \App\Models\User::query()->find((int) $matches[1]);
            abort_unless($owner && ($authUser->id === $owner->id || $authUser->can('view', $owner)), 403);
        } elseif (preg_match('#^(dokumen_ijazah|dokumen_sk_jabatan|dokumen_sk_golongan|dokumen_sertifikat)/#', $path)) {
            $isAuthorized = $authUser->hasAnyRole(['super_admin', 'admin', 'admin_kepegawaian', 'pa', 'pptk'])
                || $authUser->riwayatPendidikan()->where('file_ijazah', $path)->exists()
                || $authUser->riwayatJabatan()->where('file_sk_jabatan', $path)->exists()
                || $authUser->riwayatGolongan()->where('file_sk_golongan', $path)->exists();
            abort_unless($isAuthorized, 403);
        } else {
            abort(403);
        }

        return response()->file($fullPath, [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=300',
        ]);
    })->name('admin.pegawai.berkas.preview');
});

// Fallback jika browser melakukan GET pada endpoint livewire/update
Route::get('livewire/update', function () {
    $bidang = request()->query('bidang');
    $params = $bidang ? ['bidang' => $bidang] : [];
    return redirect()->to(\App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource::getUrl('index', $params));
});

// Fallback jika hosting cPanel/VPS belum menjalankan storage:link atau Nginx memblokir static symlink
Route::get('/storage/{path}', function (string $path) {
    $cleanPath = ltrim(str_replace('\\', '/', $path), '/');
    abort_if($cleanPath === '' || str_contains($cleanPath, '..'), 404);

    $baseDir = realpath(storage_path('app/public'));
    $filePath = realpath(storage_path('app/public/' . $cleanPath));

    abort_if($baseDir === false || $filePath === false || ! str_starts_with($filePath, $baseDir . DIRECTORY_SEPARATOR) || ! is_file($filePath), 404);

    return response()->file($filePath);
})->where('path', '.*');

// Route direct streaming file storage publik jika symlink public/storage belum aktif
Route::get('/media-file/{path}', function (string $path) {
    $cleanPath = ltrim(str_replace('\\', '/', $path), '/');
    abort_if($cleanPath === '' || str_contains($cleanPath, '..'), 404);

    $baseDir = realpath(storage_path('app/public'));
    $filePath = realpath(storage_path('app/public/' . $cleanPath));

    abort_if($baseDir === false || $filePath === false || ! str_starts_with($filePath, $baseDir . DIRECTORY_SEPARATOR) || ! is_file($filePath), 404);

    return response()->file($filePath);
})->where('path', '.*')->name('media.serve');

// Route Autentikasi Google OAuth (SSO)
Route::middleware('web')->group(function () {
    Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/drive', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirectToGoogleDrive'])->middleware('auth')->name('auth.google.drive');
    Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});




Route::get('/robots.txt', function () {
    $content = implode("\n", [
        'User-agent: *',
        'Allow: /',
        'Disallow: /admin',
        'Disallow: /api/',
        'Sitemap: ' . url('/sitemap.xml'),
    ]);

    return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
});

Route::get('/sitemap.xml', function () {
    $lastmod = now()->toAtomString();
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
        . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
        . '  <url>' . "\n"
        . '    <loc>' . e(url('/')) . '</loc>' . "\n"
        . '    <lastmod>' . $lastmod . '</lastmod>' . "\n"
        . '    <changefreq>daily</changefreq>' . "\n"
        . '    <priority>1.0</priority>' . "\n"
        . '  </url>' . "\n"
        . '</urlset>';

    return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
});
