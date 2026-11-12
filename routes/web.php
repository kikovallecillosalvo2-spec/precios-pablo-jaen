<?php

use App\Http\Controllers\PriceController;
use Illuminate\Support\Facades\Route;

// Página web de la calculadora
Route::get('/', [PriceController::class, 'index'])->name('precios');

// API JSON
Route::get('/api/convertir', [PriceController::class, 'conversion'])->name('precios.convertir');
Route::get('/api/descuento', [PriceController::class, 'descuento'])->name('precios.descuento');
