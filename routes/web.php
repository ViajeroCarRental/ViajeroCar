<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ControladorVistas;

//rutas vistas Usuario

/*  Inicio  */
Route::get('/', [ControladorVistas::class, 'home'])->name('rutaHome');

//ruta Vista Catalogo
Route::get('/catalogo',[ControladorVistas::class,'catalogo'])->name('rutaCatalogo');

//ruta Vista Reservaciones
Route::get('/reservaciones',[ControladorVistas::class,'reservaciones'])->name('rutaReservaciones');

//ruta Vista Contacto
Route::get('/contacto',[ControladorVistas::class,'contacto'])->name('rutaContacto');

//ruta Vista Politicas
Route::get('/politicas',[ControladorVistas::class,'politicas'])->name('rutaPoliticas');

//ruta Vista FAQ
Route::get('/faq',[ControladorVistas::class,'faq'])->name('rutaFAQ');
