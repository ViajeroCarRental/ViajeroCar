<?php

use App\Http\Controllers\BtnReservacionesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ControladorVistas;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\BusquedaCatalogoController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\controladorVistasAdmin;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\ReservacionesController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ReservacionesAdminController;
use Illuminate\Support\Facades\Bus;

//rutas vistas Usuario

/*  Inicio  */
Route::get('/', [BusquedaController::class, 'home'])->name('rutaHome');
//ruta Vista Catalogo
Route::get('/catalogo', [CatalogoController::class, 'index'])->name('rutaCatalogo');
//ruta Vista Reservaciones
Route::get('/reservaciones',[ReservacionesController::class,'reservaciones'])->name('rutaReservaciones');
//ruta Vista Contacto
Route::get('/contacto',[ControladorVistas::class,'contacto'])->name('rutaContacto');
//ruta Vista Politicas
Route::get('/politicas',[ControladorVistas::class,'politicas'])->name('rutaPoliticas');
//ruta Vista FAQ
Route::get('/faq',[ControladorVistas::class,'faq'])->name('rutaFAQ');
//busqueda
Route::post('/buscar', [BusquedaController::class, 'buscar'])->name('rutaBuscar');
Route::get('/catalogo/filtrar', [CatalogoController::class, 'filtrar'])->name('rutaCatalogoFiltrar');
//contacto form
Route::post('/contacto', [ContactoController::class, 'store'])->name('contacto.store');
//busqueda catalogo
Route::get('/catalogo/resultados', [CatalogoController::class, 'resultados'])->name('rutaCatalogoResultados');
// Flujo principal de reservaciones (desde HOME)

// (opcional) Si vienes desde el catálogo con un vehículo elegido
Route::get('/reservaciones/desde-catalogo', [ReservacionesController::class, 'iniciar'])->name('reservaciones.desdeCatalogo');
Route::get('/reservaciones', [ReservacionesController::class, 'desdeNavbar'])->name('rutaReservaciones');

// WELCOME o CATÁLOGO → entrada estándar a Reservaciones
// (recibe los parámetros, decide paso 2 o 3 y pinta la vista)
Route::get('/reservaciones/iniciar', [ReservacionesController::class, 'iniciar'])->name('rutaReservasIniciar');
Route::post('/cotizaciones', [ReservacionesController::class, 'cotizar'])->name('cotizaciones.store');
// reservas pago en mostrador
Route::post('/reservas', [BtnReservacionesController::class, 'reservar'])->name('reservas.store');
// Reserva Pago en línea
Route::post('/reservas/linea', [BtnReservacionesController::class, 'reservarLinea'])->name('reservas.linea');
Route::get('/login', [LoginController::class, 'showLogin'])->name('auth.show');

// Acciones de autenticación
Route::post('/login',        [LoginController::class, 'login'])->name('auth.login');
Route::post('/register',     [LoginController::class, 'register'])->name('auth.register');
Route::post('/logout',       [LoginController::class, 'logout'])->name('logout');

// Verificación de correo por código
Route::post('/verify-code',         [LoginController::class, 'verifyCode'])->name('auth.verify');
Route::post('/verify-code/resend',  [LoginController::class, 'resendCode'])->name('auth.verify.resend');

// Rutas destino después de login (puedes ajustarlas)
Route::get('/perfil', [LoginController::class, 'perfil'])->name('rutaPerfil');
Route::get('/admin',  [LoginController::class, 'adminHome'])->name('admin.home');






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
// Módulo de reservaciones (nuevo controlador dedicado)
Route::get('/admin/reservaciones', [ReservacionesAdminController::class, 'index'])->name('rutaReservacionesAdmin');
// Endpoint para obtener vehículos por categoría (AJAX)
Route::get('/admin/reservaciones/vehiculos/{idCategoria}', [ReservacionesAdminController::class, 'obtenerVehiculosPorCategoria'])
     ->name('rutaVehiculosPorCategoria');
// Endpoint para obtener paquetes de seguros (protecciones)
Route::get('/admin/reservaciones/seguros', [ReservacionesAdminController::class, 'getSeguros'])->name('rutaSegurosReservaciones');

// Endpoint para obtener servicios adicionales (complementos)
Route::get('/admin/reservaciones/servicios', [ReservacionesAdminController::class, 'getServicios'])->name('rutaServiciosReservaciones');
// Guardar reservación (desde el formulario de pasos)
Route::post('/reservaciones/guardar', [ReservacionesAdminController::class, 'guardarReservacion'])->name('reservaciones.guardar');


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
//facturar
Route::get('/admin/facturar', [App\Http\Controllers\controladorVistasAdmin::class, 'facturar'])->name('rutaFacturar');