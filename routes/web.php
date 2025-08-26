<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ControladorVistas;

//rutas vistas

/* ================== Inicio ================== */
Route::get('/', [ControladorVistas::class, 'home'])->name('rutahome');

/* ================== Autenticación ================== */
Route::get('/login', [ControladorVistas::class, 'login'])->name('rutalogin');
Route::get('/registro', [ControladorVistas::class, 'registro'])->name('rutaregistro');
Route::get('/recuperar-contrasena', [ControladorVistas::class, 'recuperarContrasena'])->name('rutarecuperarcontrasena');
Route::get('/verificacion-correo', [ControladorVistas::class, 'verificacionDeCorreo'])->name('rutaverificacioncorreo');

/* ================== Vehículos ================== */
Route::get('/catalogo-vehiculos', [ControladorVistas::class, 'catalogoVehiculos'])->name('rutacatalogovehiculos');
Route::get('/detalle-vehiculo', [ControladorVistas::class, 'detalleVehiculo'])->name('rutadetallevehiculo');

/* ================== Reservaciones ================== */
Route::get('/reservar', [ControladorVistas::class, 'reservar'])->name('rutareservar');
Route::get('/mis-reservaciones', [ControladorVistas::class, 'misReservaciones'])->name('rutamisreservaciones');
Route::get('/mis-facturas', [ControladorVistas::class, 'misFacturas'])->name('rutamisfacturas');

/* ================== Membresías ================== */
Route::get('/membresias', [ControladorVistas::class, 'membresias'])->name('rutamembresias');
Route::get('/mi-membresia', [ControladorVistas::class, 'miMembresia'])->name('rutamimembresia');

/* ================== Usuario ================== */
Route::get('/perfil', [ControladorVistas::class, 'perfil'])->name('rutaperfil');
Route::get('/notificaciones', [ControladorVistas::class, 'notificaciones'])->name('rutanotificaciones');

/* ================== Políticas ================== */
Route::get('/politica-renta', [ControladorVistas::class, 'politicaDeRenta'])->name('rutapoliticarenta');
Route::get('/politicas-limpieza', [ControladorVistas::class, 'politicasDeLimpieza'])->name('rutapoliticaslimpieza');
Route::get('/aviso-privacidad', [ControladorVistas::class, 'avisoDePrivacidad'])->name('rutaavisoprivacidad');
Route::get('/terminos-condiciones', [ControladorVistas::class, 'terminosYCondiciones'])->name('rutaterminoscondiciones');

/* ================== Información ================== */
Route::get('/contacto-ubicaciones', [ControladorVistas::class, 'contactoYUbicaciones'])->name('rutacontactoubicaciones');
Route::get('/ayuda', [ControladorVistas::class, 'ayuda'])->name('rutaayuda');
