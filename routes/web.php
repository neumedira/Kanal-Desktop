<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Admin\BonusController;
use App\Http\Controllers\Api\V1\Admin\SettingBonusController;

Route::get('/', function () {
    return view('welcome');
});

// Tambahkan route ini untuk menampilkan view login
Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/berita', function () {
    return view('berita');
})->name('berita');

// Route untuk Kelola Bonus dan Pengaturan Bonus
Route::get('/bonus', [BonusController::class, 'index'])->name('bonus.index');
Route::put('/pengaturan-bonus', [SettingBonusController::class, 'update'])->name('pengaturan-bonus.update');