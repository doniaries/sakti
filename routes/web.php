<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Mobile\Auth\Login;
use App\Livewire\Mobile\Dashboard;
use App\Livewire\Mobile\Absensi;
use App\Livewire\Mobile\Riwayat;
use App\Livewire\Mobile\Lhk\Index as LhkIndex;
use App\Livewire\Mobile\Lhk\Form as LhkForm;

Route::get('/', function () {
    return redirect('/mobile/login'); // Redirect to mobile login instead of admin for now
});

Route::prefix('mobile')->group(function () {
    Route::get('/login', Login::class)->name('mobile.login')->middleware('guest');
    
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('mobile.dashboard');
        Route::get('/absensi', Absensi::class)->name('mobile.absensi');
        Route::get('/riwayat', Riwayat::class)->name('mobile.riwayat');
        Route::get('/lhk', LhkIndex::class)->name('mobile.lhk.index');
        Route::get('/lhk/buat', LhkForm::class)->name('mobile.lhk.create');
        // LHK detail bisa tambahkan jika perlu
    });
});
