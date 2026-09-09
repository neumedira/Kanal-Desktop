<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Admin\HealthCheckController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\BonusController;
use App\Http\Controllers\Api\V1\Admin\ReportController;
use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\SettingBonusController;
use App\Http\Controllers\Api\V1\KategoriController;
use App\Http\Controllers\Api\V1\WartawanController;
use App\Http\Controllers\Api\V1\ArtikelController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/v1/admin/health-check', [HealthCheckController::class, 'index']);

Route::prefix('v1')->group(function () {

    // --- ROUTE PUBLIC ---
    Route::post('/login', [AuthController::class, 'login']);

    // --- ROUTE PROTECTED (Wajib Token Sanctum) ---
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Pengaturan Bonus & Admin Modules
        Route::prefix('admin')->group(function () {
            Route::get('/pengaturan-bonus', [SettingBonusController::class, 'index']);
            Route::put('/pengaturan-bonus', [SettingBonusController::class, 'update']);

            Route::get('/dashboard', [DashboardController::class, 'index']);
            Route::get('/bonus', [BonusController::class, 'index']);
            Route::get('/bonus/export', [ReportController::class, 'export']);
        });

        // --- ROUTE DEV 2 (DATA MODULE) ---

        // Kategori Berita (GET Only)
        Route::get('/kategori', [KategoriController::class, 'index']);
        Route::get('/kategori/{id}', [KategoriController::class, 'show']);

        // Wartawan (GET Only)
        Route::get('/wartawan', [WartawanController::class, 'index']);
        Route::get('/wartawan/{id}', [WartawanController::class, 'show']);

        // Artikel (GET + Patch Keterangan)
        Route::get('/artikel', [ArtikelController::class, 'index']);
        Route::get('/artikel/{id}', [ArtikelController::class, 'show']);
        Route::patch('/artikel/{id}/keterangan', [ArtikelController::class, 'updateKeterangan']);

    });

});
