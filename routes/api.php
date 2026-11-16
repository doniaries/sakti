<?php

use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\IzinController;
use App\Http\Controllers\Api\LhkController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PersetujuanController;
use App\Http\Middleware\EnsureApiDeviceBound;
use Illuminate\Support\Facades\Route;

// ─── Public Routes ─────────────────────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:30,1');

// ─── Protected Routes (Sanctum + 1 Akun 1 Device) ──────────────────────────
Route::middleware(['auth:sanctum', EnsureApiDeviceBound::class, 'throttle:120,1'])->group(function () {

    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/me/avatar', [AuthController::class, 'updateAvatar']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard', DashboardController::class);

    // Absensi
    Route::prefix('absensi')->group(function () {
        Route::get('/today', [AbsensiController::class, 'today']);
        Route::get('/', [AbsensiController::class, 'index']);
        Route::post('/', [AbsensiController::class, 'store']);
    });

    // LHK
    Route::prefix('lhk')->group(function () {
        Route::get('/aktivitas', [LhkController::class, 'aktivitas']);
        Route::get('/absensi-tanggal', [LhkController::class, 'absensiTanggal']);
        Route::get('/', [LhkController::class, 'index']);
        Route::post('/', [LhkController::class, 'store']);
        Route::get('/{lhk}', [LhkController::class, 'show']);
    });

    // Izin, Sakit & Cuti
    Route::prefix('izin')->group(function () {
        Route::get('/jenis', [IzinController::class, 'jenis']);
        Route::get('/', [IzinController::class, 'index']);
        Route::post('/', [IzinController::class, 'store']);
    });

    // Persetujuan Atasan
    Route::prefix('persetujuan')->group(function () {
        Route::get('/', [PersetujuanController::class, 'index']);
        Route::post('/{pengajuanIzin}/approve', [PersetujuanController::class, 'approve']);
        Route::post('/{pengajuanIzin}/reject', [PersetujuanController::class, 'reject']);
    });

    // Notifikasi
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    });
});
