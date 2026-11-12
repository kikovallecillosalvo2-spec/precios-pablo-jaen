<?php

use App\Http\Controllers\PriceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app' => 'Calculadora de precios',
        'endpoints' => [
            'conversion' => url('/api/convertir?cantidad=100&de=EUR&a=USD'),
            'descuento' => url('/api/descuento?precio=80&porcentaje=25'),
        ],
    ]);
});

Route::get('/api/convertir', [PriceController::class, 'conversion'])->name('precios.convertir');
Route::get('/api/descuento', [PriceController::class, 'descuento'])->name('precios.descuento');
