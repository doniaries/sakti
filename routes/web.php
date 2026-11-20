<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Mobile\Auth\Login;
use App\Livewire\Mobile\Dashboard;
use App\Livewire\Mobile\Absensi;
use App\Livewire\Mobile\Riwayat;
use App\Livewire\Mobile\Lhk\Index as LhkIndex;
use App\Livewire\Mobile\Lhk\Form as LhkForm;

Route::get('/', function () {
    $pengaturan = \App\Models\PengaturanAplikasi::getPengaturan();
    $portalLinks = \App\Models\PortalLink::aktif()->get();
    return view('welcome', compact('pengaturan', 'portalLinks'));
})->name('portal');

Route::get('/panduan', fn () => view('panduan'))->name('panduan');

Route::post('/portal/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('portal');
})->name('portal.logout');

// Route Pelacakan & Verifikasi Keaslian Dokumen Transaksi via QR Code (Akses Publik)
Route::get('/lacak-dokumen/{kode?}', [\App\Http\Controllers\DokumenTrackingController::class, 'track'])
    ->name('dokumen.tracking');

Route::get('/verifikasi-surat-tugas/{kode?}', [\App\Http\Controllers\SuratTugasPrintController::class, 'verifikasi'])
    ->name('surat-tugas.verifikasi');

Route::get('/verifikasi-izin/{kode?}', [\App\Http\Controllers\CetakLaporanController::class, 'verifikasiIzin'])
    ->name('pengajuan-izin.verifikasi');

Route::prefix('mobile')->group(function () {
    Route::get('/login', Login::class)->name('mobile.login');
    Route::get('/masuk', Login::class)->name('login');

    
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
        Route::middleware('module:izin')->group(function () {
            Route::get('/izin', \App\Livewire\Mobile\Izin\Index::class)->name('mobile.izin.index');
            Route::get('/izin/buat', \App\Livewire\Mobile\Izin\Form::class)->name('mobile.izin.create');
        });
        Route::get('/pengajuan-izin/{pengajuanIzin}/cetak-pdf', [\App\Http\Controllers\CetakLaporanController::class, 'cetakIzinCuti'])->name('pengajuan-izin.cetak-pdf');
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
    Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});



