<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StockImportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'landing')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegistration'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/stok', [ProductController::class, 'index'])->name('stok.index');
    Route::post('/stok', [ProductController::class, 'store'])->name('stok.store');
    Route::put('/stok/{product}', [ProductController::class, 'update'])->name('stok.update');
    Route::get('/stok/impor', [StockImportController::class, 'index'])->name('stok.import');
    Route::post('/stok/impor/excel', [StockImportController::class, 'import'])->name('stok.import.excel');
    Route::post('/stok/impor/manual', [ProductController::class, 'store'])->name('stok.import.manual');
    Route::get('/stok/template', [StockImportController::class, 'template'])->name('stok.template');

    Route::get('/analisis', [AnalysisController::class, 'index'])->name('analisis.index');
    Route::get('/transaksi', [TransactionController::class, 'index'])->name('transaksi.index');
    Route::get('/pengaturan', [SettingsController::class, 'index'])->name('pengaturan.index');
    Route::put('/pengaturan/profil', [SettingsController::class, 'updateProfile'])->name('pengaturan.profil.update');
    Route::put('/pengaturan/whatsapp', [SettingsController::class, 'saveWhatsAppNumber'])->name('pengaturan.whatsapp.save');
});
