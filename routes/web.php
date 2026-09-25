<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AnalyticsController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Group route WAJIB LOGIN
Route::middleware(['auth'])->group(function () {

    // 1. AKSES OPERATOR & ADMIN (Dashboard Billing & Timer Rental)
    Route::middleware(['role:admin,operator'])->group(function () {
        Route::get('/dashboard', [RentalController::class, 'index'])->name('dashboard');
        Route::post('/rental/start', [RentalController::class, 'startSession'])->name('rental.start');
        Route::post('/rental/order/{session}', [RentalController::class, 'addOrder'])->name('rental.order');
        Route::post('/rental/stop/{session}', [RentalController::class, 'stopSession'])->name('rental.stop');
        Route::get('/rental/receipt/{id}', [RentalController::class, 'printReceipt'])->name('rental.receipt');
        Route::post('/rental/extend/{sessionId}', [RentalController::class, 'extendSession'])->name('rental.extend');
    });

    // 2. KHUSUS AKSES ADMIN (Stok, Laporan Transaksi, & Analytics)
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('products', ProductController::class)->except(['create', 'edit', 'show']);
        Route::get('/reports/transactions', [ReportController::class, 'index'])->name('reports.transactions');
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('reports.analytics');
    });

});

require __DIR__.'/auth.php';