<?php

//use App\Http\Controllers\BonusController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Admin\BonusController;
use App\Http\Controllers\Api\V1\Admin\SettingBonusController;

// Mengubah halaman utama ('/') langsung menampilkan view 'login'
Route::get('/', function () {
    return view('login');
});

// Route /login tetap dipertahankan agar link atau redirect bawaan sistem tidak rusak
Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/berita', function () {
    return view('berita');
})->name('berita');

Route::get('/admin/bonus', function () {
    return view('admin.bonus');
});

// Route untuk Kelola Bonus dan Pengaturan Bonus
Route::get('/bonus', [BonusController::class, 'index'])->name('bonus.index');

// --- TAMBAHKAN RUTE EXPORT INI ---
Route::get('/bonus/export', [BonusController::class, 'exportExcel'])->name('bonus.export');

Route::put('/pengaturan-bonus', [SettingBonusController::class, 'update'])->name('pengaturan-bonus.update');