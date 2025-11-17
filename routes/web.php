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
use App\Http\Controllers\ReservacionesActivasController;
use Illuminate\Support\Facades\Bus;
use App\Http\Controllers\FlotillaController;
use App\Http\Controllers\MantenimientoController;
use App\Http\Controllers\PolizasController;
use App\Http\Controllers\CarroceriaController;
use App\Http\Controllers\GastosController;
use App\Http\Controllers\ContratoController;
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

//Vistas Rentas
//inicio
Route::get('/admin/ventas', [App\Http\Controllers\controladorVistasAdmin::class, 'ventas'])->name('rutaInicioVentas');
// Módulo de reservaciones (nuevo controlador dedicado)
Route::get('/admin/reservaciones', [ReservacionesAdminController::class, 'index'])->name('rutaReservacionesAdmin');
// ✅ Nuevo endpoint para obtener info de una categoría
Route::get('/admin/reservaciones/categorias/{idCategoria}', [ReservacionesAdminController::class, 'obtenerCategoriaPorId'])->name('rutaCategoriaPorId');
// Endpoint para obtener paquetes de seguros
Route::get('/admin/reservaciones/seguros', [ReservacionesAdminController::class, 'getSeguros'])->name('rutaSegurosReservaciones');
// Endpoint para obtener servicios adicionales
Route::get('/admin/reservaciones/servicios', [ReservacionesAdminController::class, 'getServicios'])->name('rutaServiciosReservaciones');
// Guardar reservación
Route::post('/reservaciones/guardar', [ReservacionesAdminController::class, 'guardarReservacion'])->name('reservaciones.guardar');

// =========================
// 🌍 RUTAS COTIZACIONES ADMIN
// =========================

// Cotizaciones
Route::get('/admin/cotizaciones', [controladorVistasAdmin::class, 'cotizaciones'])->name('rutaCotizaciones');
Route::get('/admin/cotizaciones-activas', [App\Http\Controllers\controladorVistasAdmin::class, 'cotizacionesRecientes'])->name('rutaCotizacionesRecientes');
Route::get('/admin/cotizar', [App\Http\Controllers\CotizacionesAdminController::class, 'index'])->name('rutaCotizar');

// Guardar la cotización
Route::post('/admin/cotizaciones/guardar', [App\Http\Controllers\CotizacionesAdminController::class, 'guardarCotizacion'])->name('rutaGuardarCotizacion');

// Endpoints AJAX
Route::get('/admin/cotizaciones/seguros', [App\Http\Controllers\CotizacionesAdminController::class, 'getSeguros'])->name('rutaSegurosCotizar');
Route::get('/admin/cotizaciones/servicios', [App\Http\Controllers\CotizacionesAdminController::class, 'getServicios'])->name('rutaServiciosCotizar');
Route::get('/admin/cotizaciones/categoria/{idCategoria}', [App\Http\Controllers\CotizacionesAdminController::class, 'getCategoria'])->name('rutaCategoriaCotizar');
// 🟢 Vista de todas las cotizaciones (para botón "Ver cotizaciones")
Route::get('/admin/cotizaciones/listado', [App\Http\Controllers\CotizacionesAdminController::class, 'listado'])->name('rutaVerCotizaciones');
// Convertir Cotizacion en Reservación
Route::post('/admin/cotizaciones/{id}/convertir', [App\Http\Controllers\CotizacionesAdminController::class, 'convertirAReservacion'])->name('cotizaciones.convertir');
// 🔄 Reenviar cotización por correo
Route::post('/admin/cotizaciones/{id}/reenviar', [App\Http\Controllers\CotizacionesAdminController::class, 'reenviarCotizacion'])->name('cotizaciones.reenviar');
// 🔹 Eliminar cotización manualmente
Route::delete('/admin/cotizaciones/{id}/eliminar', [App\Http\Controllers\CotizacionesAdminController::class, 'eliminarCotizacion'])->name('cotizaciones.eliminar');
// 🔹 Limpieza automática (opcional: protegida o por CRON)
Route::get('/admin/cotizaciones/limpiar-vencidas', [App\Http\Controllers\CotizacionesAdminController::class, 'limpiarCotizacionesVencidas'])->name('cotizaciones.limpiarVencidas');



//reservaciones activas
Route::get('/admin/reservaciones-activas', [ReservacionesActivasController::class, 'index'])->name('rutaReservacionesActivas');
// Endpoint AJAX: obtener detalles por código (para el modal)
Route::get('/admin/reservaciones-activas/{codigo}', [ReservacionesActivasController::class, 'show'])->name('rutaDetalleReservacionActiva');

//contrato id
Route::get('/admin/contrato/{id}', [App\Http\Controllers\ContratoController::class, 'mostrarContrato'])->name('contrato.mostrar');
// 🧩 Actualizar servicios adicionales (AJAX desde Contrato)
Route::post('/admin/contrato/servicios', [App\Http\Controllers\ContratoController::class, 'actualizarServicios'])->name('contrato.actualizarServicios');
// =============================================================
// 🛡️ Actualización de seguros (Paso 3 del contrato)
// =============================================================
Route::post('/admin/contrato/seguros', [App\Http\Controllers\ContratoController::class, 'actualizarSeguro'])->name('contrato.actualizarSeguro');
// 💰 Actualizar cargos adicionales (Paso 4)
Route::post('/admin/contrato/cargos', [App\Http\Controllers\ContratoController::class, 'actualizarCargos'])->name('contrato.actualizarCargos');
// 📄 Guardar documentación subida (Paso 5)
Route::post('/admin/contrato/guardar-documentacion', [App\Http\Controllers\ContratoController::class, 'guardarDocumentacion'])->name('contrato.guardarDocumentacion');
// Obtener conductores asociados al contrato (AJAX)
Route::get('/admin/contrato/{id}/conductores', [App\Http\Controllers\ContratoController::class, 'obtenerConductores']);






//visor de reservaciones
Route::get('/admin/visor-reservaciones', [App\Http\Controllers\controladorVistasAdmin::class, 'visorReservaciones'])->name('rutaVisorReservaciones');
//administracion de reservaciones
Route::get('/admin/administracion-reservaciones', [App\Http\Controllers\controladorVistasAdmin::class, 'administracionReservaciones'])->name('rutaAdministracionReservaciones');
//historial completo
Route::get('/admin/historial-completo', [App\Http\Controllers\controladorVistasAdmin::class, 'historialCompleto'])->name('rutaHistorialCompleto');
//Alta Cliente
Route::get('/admin/alta-cliente', [App\Http\Controllers\controladorVistasAdmin::class, 'altaCliente'])->name('rutaAltaCliente');
//Licencia
Route::get('/admin/licencia', [App\Http\Controllers\controladorVistasAdmin::class, 'licencia'])->name('rutaLicencia');
//RFC
Route::get('/admin/rfc', [App\Http\Controllers\controladorVistasAdmin::class, 'RFC_Fiscal'])->name('rutaRFC');
//facturar
Route::get('/admin/facturar', [App\Http\Controllers\controladorVistasAdmin::class, 'facturar'])->name('rutaFacturar');



// 🚗 Rutas de Flotilla (Administrador)
// 🔹 Vista principal de la flotilla
Route::get('/admin/flotilla', [FlotillaController::class, 'indexView'])->name('rutaFlotilla');
// 🔹 Agregar nuevo vehículo
Route::post('/admin/flotilla/agregar', [FlotillaController::class, 'store'])->name('flotilla.agregar');
// 🔹 Actualizar vehículo existente
Route::post('/admin/flotilla/{id}/actualizar', [FlotillaController::class, 'update'])->name('flotilla.actualizar');
// 🔹 Eliminar vehículo
Route::delete('/admin/flotilla/{id}/eliminar', [FlotillaController::class, 'destroy'])->name('flotilla.eliminar');



// Rutas de Mantenimiento
Route::get('/admin/mantenimiento', [MantenimientoController::class, 'indexView'])->name('rutaMantenimiento');

Route::get('/admin/mantenimiento', [MantenimientoController::class, 'indexView'])->name('rutaMantenimiento');
Route::put('/admin/mantenimiento/{id}/update', [MantenimientoController::class, 'updateKm'])->name('mantenimiento.actualizarKm');
Route::post('/admin/mantenimiento/{id}/registrar', [MantenimientoController::class, 'registrarMantenimiento'])->name('mantenimiento.registrar');

// Rutas de poliza
Route::get('/admin/polizas', [PolizasController::class, 'index'])->name('rutaPolizas');
Route::get('/admin/polizas/descargar/{archivo}', [PolizasController::class, 'descargar'])->name('descargarPoliza');

Route::post('/admin/polizas/actualizar/{id}', [PolizasController::class, 'actualizar'])->name('actualizarPoliza');
Route::post('/admin/polizas/subir/{id}', [PolizasController::class, 'guardarArchivo'])->name('guardarArchivoPoliza');

// ✅ Ver/descargar vía controlador (sin symlink)
Route::get('/admin/polizas/ver/{id}', [PolizasController::class, 'ver'])->name('verPoliza');
Route::get('/admin/polizas/descargar/{id}', [PolizasController::class, 'descargar'])->name('descargarPoliza');



// 🚗 Carrocería — rutas limpias y sin duplicados
Route::get('/admin/carroceria', [CarroceriaController::class, 'indexView'])
    ->name('rutaCarroceria'); // 👈 nombre exacto que usa tu menú

Route::post('/admin/carroceria/store', [CarroceriaController::class, 'store'])
    ->name('carroceria.store');

Route::put('/admin/carroceria/update/{id}', [CarroceriaController::class, 'update'])
    ->name('carroceria.update');




Route::get('/admin/gastos', [GastosController::class, 'index'])->name('rutaGastos');
Route::get('/admin/gastos/filtrar', [GastosController::class, 'filtrar']);
