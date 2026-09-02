<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\SettingBonusController;

Route::prefix('v1')->group(function () {

    // --- ROUTE PUBLIC ---
    Route::post('/login', [AuthController::class, 'login']);

    // --- ROUTE PROTECTED (Wajib Token Sanctum) ---
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Pengaturan Bonus
        Route::prefix('admin')->group(function () {
            Route::get('/pengaturan-bonus', [SettingBonusController::class, 'index']);
            Route::put('/pengaturan-bonus', [SettingBonusController::class, 'update']);
        });

    });

});
