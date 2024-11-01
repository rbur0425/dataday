<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApartmentController;

Route::get('/', [ApartmentController::class, 'index'])->name('apartments.index');
