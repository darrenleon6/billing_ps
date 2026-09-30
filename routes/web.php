<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\ShiftController; // 🟢 Tambahkan baris ini di paling atas routes/web.php
use App\Http\Controllers\PromotionController; 
use App\Http\Controllers\ConsoleController;
use App\Http\Controllers\PackageController;

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
        Route::delete('/rental/order/{order}', [RentalController::class, 'deleteOrder'])->name('rental.order.delete');
        Route::post('/shift/start', [ShiftController::class, 'start'])->name('shift.start');
        Route::post('/shift/{shift}/stop', [ShiftController::class, 'stop'])->name('shift.stop');
        Route::get('/reports/shifts', [ShiftController::class, 'index'])->name('reports.shifts');
        Route::get('/reports/transactions', [ReportController::class, 'index'])->name('reports.transactions');
        Route::post('/rental-sessions/{id}/transfer', [RentalController::class, 'transferConsole'])->name('rental.transfer');

   
    });

    // 2. KHUSUS AKSES ADMIN (Stok, Laporan Transaksi, & Analytics)
    Route::middleware(['role:admin'])->group(function () {
        Route::resource('products', ProductController::class)->except(['create', 'edit', 'show']);
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('reports.analytics');
        Route::resource('promotions', PromotionController::class)->except(['create', 'edit', 'show']);
        Route::resource('consoles', ConsoleController::class)->except(['create', 'edit', 'show']);
        Route::resource('packages', PackageController::class)->except(['create', 'edit', 'show']);
        
    });

});

require __DIR__.'/auth.php';