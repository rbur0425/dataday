<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApartmentController;

Route::get('/', [ApartmentController::class, 'index'])->name('apartments.index');
Route::get('/apartments/{apartment}', [ApartmentController::class, 'show'])->name('apartments.show');
Route::POST('/apartments/generate-negotiation-script', [ApartmentController::class, 'generateNegotiationScript']);
