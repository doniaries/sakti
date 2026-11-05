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
    return view('welcome', compact('pengaturan'));
})->name('portal');

Route::post('/portal/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('portal');
})->name('portal.logout');

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
        Route::get('/profil', \App\Livewire\Mobile\Profil::class)->name('mobile.profil');
        Route::get('/izin', \App\Livewire\Mobile\Izin\Index::class)->name('mobile.izin.index');
        Route::get('/izin/buat', \App\Livewire\Mobile\Izin\Form::class)->name('mobile.izin.create');
        Route::get('/hari-libur', \App\Livewire\Mobile\HariLibur::class)->name('mobile.hari-libur');
        Route::get('/reputasi', \App\Livewire\Mobile\Reputasi::class)->name('mobile.reputasi');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/admin/pengajuan-spj/{record}/preview', [\App\Http\Controllers\SpjPrintController::class, 'preview'])
        ->name('admin.pengajuan-spj.preview');
    Route::get('/admin/pengajuan-spj/{record}/cetak', [\App\Http\Controllers\SpjPrintController::class, 'print'])
        ->name('admin.pengajuan-spj.cetak');
});

// Fallback jika browser melakukan GET pada endpoint livewire/update
Route::get('livewire/update', function () {
    $bidang = request()->query('bidang');
    $params = $bidang ? ['bidang' => $bidang] : [];
    return redirect()->to(\App\Filament\Resources\PengajuanSpjs\PengajuanSpjResource::getUrl('index', $params));
});

// Fallback jika hosting cPanel mematikan symlink() PHP (melayani file storage langsung)
Route::get('/storage/{path}', function (string $path) {
    $filePath = storage_path('app/public/' . $path);

    if (! file_exists($filePath)) {
        abort(404);
    }

    return response()->file($filePath);
})->where('path', '.*');


