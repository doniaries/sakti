<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Mobile\Auth\Login;
use App\Livewire\Mobile\Dashboard;
use App\Livewire\Mobile\Absensi;
use App\Livewire\Mobile\Riwayat;
use App\Livewire\Mobile\Lhk\Index as LhkIndex;
use App\Livewire\Mobile\Lhk\Form as LhkForm;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('mobile.dashboard');
    }
    return redirect()->route('mobile.login');
});

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
        // LHK detail bisa tambahkan jika perlu
    });
});
