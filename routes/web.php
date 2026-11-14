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

Route::post('/portal/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('portal');
})->name('portal.logout');

// Route Pelacakan & Verifikasi Keaslian Dokumen Transaksi via QR Code (Akses Publik)
Route::get('/lacak-dokumen/{kode?}', [\App\Http\Controllers\DokumenTrackingController::class, 'track'])
    ->name('dokumen.tracking');

Route::prefix('mobile')->group(function () {
    Route::get('/login', Login::class)->name('mobile.login');
    Route::get('/masuk', Login::class)->name('login');

    
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('mobile.dashboard');
        Route::get('/absensi', Absensi::class)->name('mobile.absensi');
        Route::get('/riwayat', Riwayat::class)->name('mobile.riwayat');
        Route::get('/riwayat/cetak-pdf', [\App\Http\Controllers\CetakLaporanController::class, 'cetakRiwayat'])->name('mobile.riwayat.cetak-pdf');
        Route::get('/lhk', LhkIndex::class)->name('mobile.lhk.index');
        Route::get('/lhk/buat', LhkForm::class)->name('mobile.lhk.create');
        Route::get('/bank-aktivitas', \App\Livewire\Mobile\Lhk\BankAktivitas::class)->name('mobile.lhk.bank-aktivitas');
        Route::get('/profil', \App\Livewire\Mobile\Profil::class)->name('mobile.profil');
        Route::get('/izin', \App\Livewire\Mobile\Izin\Index::class)->name('mobile.izin.index');
        Route::get('/izin/buat', \App\Livewire\Mobile\Izin\Form::class)->name('mobile.izin.create');
        Route::get('/pengajuan-izin/{pengajuanIzin}/cetak-pdf', [\App\Http\Controllers\CetakLaporanController::class, 'cetakIzinCuti'])->name('pengajuan-izin.cetak-pdf');
        Route::get('/persetujuan', \App\Livewire\Mobile\Persetujuan::class)->name('mobile.persetujuan');
        Route::get('/wfh', \App\Livewire\Mobile\Wfh\Index::class)->name('mobile.wfh.index');
        Route::get('/hari-libur', \App\Livewire\Mobile\HariLibur::class)->name('mobile.hari-libur');
        Route::get('/reputasi', \App\Livewire\Mobile\Reputasi::class)->name('mobile.reputasi');
        Route::get('/pengaturan-lokasi', \App\Livewire\Mobile\PengaturanLokasi::class)->name('mobile.pengaturan-lokasi');
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
});

// Fallback jika browser melakukan GET pada endpoint livewire/update
Route::get('livewire/update', function () {
    $bidang = request()->query('bidang');
    $params = $bidang ? ['bidang' => $bidang] : [];
    return redirect()->to(\App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource::getUrl('index', $params));
});

// Fallback jika hosting cPanel/VPS belum menjalankan storage:link atau Nginx memblokir static symlink
Route::get('/storage/{path}', function (string $path) {
    $filePath = storage_path('app/public/' . $path);

    if (! file_exists($filePath)) {
        $privatePath = storage_path('app/private/' . $path);
        if (file_exists($privatePath)) {
            return response()->file($privatePath);
        }
        abort(404);
    }

    return response()->file($filePath);
})->where('path', '.*');

// Route direct streaming file storage publik jika symlink public/storage belum aktif
Route::get('/media-file/{path}', function (string $path) {
    $cleanPath = str_replace(['..', '\\'], ['', '/'], $path);
    $filePath = storage_path('app/public/' . $cleanPath);

    if (! file_exists($filePath)) {
        abort(404);
    }

    return response()->file($filePath);
})->where('path', '.*')->name('media.serve');

// Route Autentikasi Google OAuth (SSO)
Route::middleware('web')->group(function () {
    Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});



