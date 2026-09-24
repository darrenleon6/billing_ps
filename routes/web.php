<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;

// Halaman Utama Dashboard Billing
Route::get('/', [RentalController::class, 'index'])->name('rental.index');

// Aksi Rental & FnB
Route::post('/rental/start', [RentalController::class, 'startSession'])->name('rental.start');
Route::post('/rental/order/{session}', [RentalController::class, 'addOrder'])->name('rental.order');
Route::post('/rental/stop/{session}', [RentalController::class, 'stopSession'])->name('rental.stop');
Route::get('/rental/receipt/{id}', [RentalController::class, 'printReceipt'])->name('rental.receipt');
Route::post('/rental/extend/{sessionId}', [RentalController::class, 'extendSession'])->name('rental.extend');
Route::get('/dashboard', [RentalController::class, 'index'])->name('dashboard');

Route::get('/reports/transactions', [ReportController::class, 'index'])
    ->name('reports.transactions');
Route::resource('products', ProductController::class)->except(['create', 'edit', 'show']);
