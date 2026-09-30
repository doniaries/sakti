<?php

use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LhkController;
use Illuminate\Support\Facades\Route;

// ─── Public Routes ─────────────────────────────────────────────────────────
Route::post('/login', [AuthController::class, 'login']);

// ─── Protected Routes (Sanctum) ────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/me', [AuthController::class, 'me']);
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
});
