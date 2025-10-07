<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ControladorVistas;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\BusquedaCatalogoController;
use App\Http\Controllers\ContactoController;
use Illuminate\Support\Facades\Bus;

//rutas vistas Usuario

/*  Inicio  */
Route::get('/', [BusquedaController::class, 'home'])->name('rutaHome');
//ruta Vista Catalogo
Route::get('/catalogo', [BusquedaCatalogoController::class, 'catalogo'])->name('rutaCatalogo');
//ruta Vista Reservaciones
Route::get('/reservaciones',[ControladorVistas::class,'reservaciones'])->name('rutaReservaciones');
//ruta Vista Contacto
Route::get('/contacto',[ControladorVistas::class,'contacto'])->name('rutaContacto');
//ruta Vista Politicas
Route::get('/politicas',[ControladorVistas::class,'politicas'])->name('rutaPoliticas');
//ruta Vista FAQ
Route::get('/faq',[ControladorVistas::class,'faq'])->name('rutaFAQ');
//ruta Vista Login
Route::get('/login',[ControladorVistas::class,'login'])->name('rutaLogin');
//ruta Vista Perfil
Route::get('/perfil',[ControladorVistas::class,'perfil'])->name('rutaPerfil');
    //busqueda
    Route::post('/buscar', [BusquedaController::class, 'buscar'])->name('rutaBuscar');
//busqueda catalogo
Route::get('/catalogo/filtrar', [BusquedaCatalogoController::class, 'index'])->name('rutaCatalogoFiltrado');
//contacto form
Route::post('/contacto', [ContactoController::class, 'store'])->name('contacto.store');

// VISTAS Admin
//Vistas Flotilla
//inicio
Route::get('/admin/dashboard', [App\Http\Controllers\controladorVistasAdmin::class, 'dashboard'])->name('rutaDashboard');
//mantenimiento
Route::get('/admin/mantenimiento', [App\Http\Controllers\controladorVistasAdmin::class, 'mantenimiento'])->name('rutaMantenimiento');
//flotilla
Route::get('/admin/flotilla', [App\Http\Controllers\controladorVistasAdmin::class, 'flotilla'])->name('rutaFlotilla');
//polizas
Route::get('/admin/polizas', [App\Http\Controllers\controladorVistasAdmin::class, 'polizas'])->name('rutaPolizas');
//carroceria
Route::get('/admin/carroceria', [App\Http\Controllers\controladorVistasAdmin::class, 'carroceria'])->name('rutaCarroceria');
//seguros
Route::get('/admin/seguros', [App\Http\Controllers\controladorVistasAdmin::class, 'seguros'])->name('rutaSeguros');
//gastos
Route::get('/admin/gastos', [App\Http\Controllers\controladorVistasAdmin::class, 'gastos'])->name('rutaGastos');

//Vistas Usuarios Admin
//usuarios
Route::get('/admin/usuarios', [App\Http\Controllers\controladorVistasAdmin::class, 'usuarios'])->name('rutaUsuarios');
//roles
Route::get('/admin/roles', [App\Http\Controllers\controladorVistasAdmin::class, 'roles'])->name('rutaRoles');

//Vistas Ventas
//inicio
Route::get('/admin/ventas', [App\Http\Controllers\controladorVistasAdmin::class, 'ventas'])->name('rutaInicioVentas');
//reservaciones
Route::get('/admin/reservaciones', [App\Http\Controllers\controladorVistasAdmin::class, 'reservacionesAdmin'])->name('rutaReservacionesAdmin');
//cotizaciones
Route::get('/admin/cotizaciones', [App\Http\Controllers\controladorVistasAdmin::class, 'cotizaciones'])->name('rutaCotizaciones');
//cotizaciones activas
Route::get('/admin/cotizaciones-activas', [App\Http\Controllers\controladorVistasAdmin::class, 'cotizacionesRecientes'])->name('rutaCotizacionesRecientes');
//cotizar
Route::get('/admin/cotizar', [App\Http\Controllers\controladorVistasAdmin::class, 'cotizar'])->name('rutaCotizar');
//reservaciones activas
Route::get('/admin/reservaciones-activas', [App\Http\Controllers\controladorVistasAdmin::class, 'reservacionesActivas'])->name('rutaReservacionesActivas');
//visor de reservaciones
Route::get('/admin/visor-reservaciones', [App\Http\Controllers\controladorVistasAdmin::class, 'visorReservaciones'])->name('rutaVisorReservaciones');
//administracion de reservaciones
Route::get('/admin/administracion-reservaciones', [App\Http\Controllers\controladorVistasAdmin::class, 'administracionReservaciones'])->name('rutaAdministracionReservaciones');
//historial completo
Route::get('/admin/historial-completo', [App\Http\Controllers\controladorVistasAdmin::class, 'historialCompleto'])->name('rutaHistorialCompleto');
//Contrato
Route::get('/admin/contrato', [App\Http\Controllers\controladorVistasAdmin::class, 'contrato'])->name('rutaContrato');
//Alta Cliente
Route::get('/admin/alta-cliente', [App\Http\Controllers\controladorVistasAdmin::class, 'altaCliente'])->name('rutaAltaCliente');
//Licencia
Route::get('/admin/licencia', [App\Http\Controllers\controladorVistasAdmin::class, 'licencia'])->name('rutaLicencia');
//RFC
Route::get('/admin/rfc', [App\Http\Controllers\controladorVistasAdmin::class, 'RFC_Fiscal'])->name('rutaRFC');
