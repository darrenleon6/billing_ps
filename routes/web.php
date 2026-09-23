<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RentalController;

// Halaman Utama Dashboard Billing
Route::get('/', [RentalController::class, 'index'])->name('rental.index');

// Aksi Rental & FnB
Route::post('/rental/start', [RentalController::class, 'startSession'])->name('rental.start');
Route::post('/rental/order/{session}', [RentalController::class, 'addOrder'])->name('rental.order');
Route::post('/rental/stop/{session}', [RentalController::class, 'stopSession'])->name('rental.stop');
