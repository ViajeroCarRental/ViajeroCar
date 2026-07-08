<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BtnReservacionesController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Ruta para el Agente IA (reserva desde WhatsApp/voz, sin CSRF, protegida por token)
Route::post('/agente/reservar', [BtnReservacionesController::class, 'reservarAgente'])
    ->name('agente.reservar');

Route::post('/agente/modificar', [App\Http\Controllers\VisorReservacionController::class, 'modificarAgente'])
    ->name('agente.modificar');
