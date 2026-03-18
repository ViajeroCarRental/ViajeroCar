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
use App\Http\Controllers\SiniestrosController;
use App\Http\Controllers\ConductorAdicionalController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\Contrato2Controller;
use App\Http\Controllers\ContratoBaseController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UsuarioAdminController;
use App\Http\Controllers\SeguroPaqueteController;
use App\Http\Controllers\SeguroIndividualController;
use App\Http\Controllers\ContratoFinalController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\ContratosAbiertosController;
use App\Http\Controllers\VisorReservacionesController;
use App\Http\Controllers\HistorialController;
use App\Http\Controllers\ChecklistCambioAutoController;
use App\Http\Controllers\CategoriasController;
use App\Http\Controllers\PropietarioVehiculoController;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\VisorReservacionController;
//rutas vistas Usuario


//--------------------------------------------------------------------------//


Route::get('/ventas/reservacion/{id}', [VisorReservacionController::class, 'mostrarReservacion'])->name('visor.show');
Route::put('/ventas/reservacion/{id}', [VisorReservacionController::class, 'actualizarReservacion'])->name('visor.update');


//--------------------------------------------------------------------------//

/*  Inicio  */
Route::get('/', [BusquedaController::class, 'home'])->name('rutaHome');
//ruta Vista Catalogo
Route::get('/catalogo', [CatalogoController::class, 'index'])->name('rutaCatalogo');
//ruta Vista Reservaciones
Route::get('/reservaciones',[ReservacionesController::class,'reservaciones'])->name('rutaReservaciones');
//ruta Vista Contacto
Route::get('/contacto',[ControladorVistas::class,'contacto'])->name('rutaContacto');
//ruta Vista Politicasf
Route::get('/politicas',[ControladorVistas::class,'politicas'])->name('rutaPoliticas');
//ruta Vista FAQ
Route::get('/faq',[ControladorVistas::class,'faq'])->name('rutaFAQ');
//busqueda
Route::post('/buscar', [BusquedaController::class, 'buscar'])->name('rutaBuscar');
Route::get('/catalogo/filtrar', [CatalogoController::class, 'filtrar'])->name('rutaCatalogoFiltrar');
//contacto form
Route::post('/contacto', [ContactoController::class, 'store'])->name('contacto.store');
//busqueda catalogoV
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
Route::get('/perfil', [LoginController::class, 'perfil'])
    ->name('rutaPerfil')
    ->middleware('sesion.activa');

Route::get('/admin',  [LoginController::class, 'adminHome'])
    ->name('admin.home')
    ->middleware('sesion.activa');

Route::get('/reservaciones-usuario', [ReservacionesController::class, 'desdeNavbar'])
    ->name('rutaReservacionesUsuario');

    Route::post('/visor-reservacion/{id}/reenviar-correo', [VisorReservacionController::class, 'reenviarCorreo'])->name('visor.reenviarCorreo');



// ======================
// RUTAS PROTEGIDAS (requieren sesión)
// ======================
Route::middleware('sesion.activa')->group(function () {


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



// ===============================
// RESERVACIONES ACTIVAS
// ===============================

// Vista principal
Route::get('/admin/reservaciones-activas',
[ReservacionesActivasController::class, 'index'])->name('rutaReservacionesActivas');

// 🔧 ACTUALIZAR + ENVIAR CORREO
Route::put('/admin/reservaciones-activas/{id}',
[ReservacionesActivasController::class, 'updateDatos'])->name('rutaUpdateReservacionActiva');

// 🔍 DETALLE (SIEMPRE AL FINAL)
Route::get('/admin/reservaciones-activas/{codigo}',
[ReservacionesActivasController::class, 'show'])->name('rutaDetalleReservacionActiva');

// 🗑️ ELIMINAR
Route::delete('/admin/reservaciones-activas/{id}',
[ReservacionesActivasController::class, 'destroy'])->name('rutaEliminarReservacionActiva');

// ContratoController

Route::get('/admin/contrato/{id}', [ContratoController::class, 'mostrarContrato'])->name('contrato.mostrar');

// Paso 1: Gestión de Fechas y Categoría
Route::post('/admin/contrato/solicitar-cambio-fecha', [ContratoController::class, 'solicitarCambioFecha'])->name('contrato.solicitarCambioFecha');
Route::get('/admin/contrato/cambio-fecha/aprobar/{token}', [ContratoController::class, 'aprobarCambioFecha'])->name('contrato.aprobarCambioFecha');
Route::get('/admin/contrato/cambio-fecha/rechazar/{token}', [ContratoController::class, 'rechazarCambioFecha'])->name('contrato.rechazarCambioFecha');
Route::get('/admin/contrato/cambio-fecha/estado/{id}', [ContratoController::class, 'estadoCambioFecha']);
Route::post('/admin/contrato/{idReservacion}/recalcular-total', [ContratoController::class, 'recalcularYActualizarTotales']);
Route::post('/admin/contrato/{idReservacion}/actualizar-categoria', [ContratoController::class, 'actualizarCategoria'])->name('contrato.actualizarCategoria');
Route::get('/admin/contrato/categoria-info/{codigo}', [ContratoController::class, 'categoriaInfo'])->name('contrato.categoria-info');

// Paso 2: Servicios Adicionales
Route::post('/admin/contrato/servicios', [ContratoController::class, 'actualizarServicios'])->name('contrato.actualizarServicios');

// Paso 3: Seguros (Paquetes e Individuales)
Route::post('/admin/contrato/seguros', [ContratoController::class, 'actualizarSeguro'])->name('contrato.actualizarSeguro');
Route::post('/admin/contrato/seguros-individuales', [ContratoController::class, 'actualizarSegurosIndividuales'])->name('contrato.actualizarSegurosIndividuales');
Route::delete('/admin/contrato/seguros-individuales', [ContratoController::class, 'eliminarSeguroIndividual'])->name('contrato.eliminarSeguroIndividual');
Route::delete('/admin/contrato/seguros-individuales/todos', [ContratoController::class, 'eliminarTodosLosIndividuales'])->name('contrato.eliminarIndividualesTodos');

// Utilidades del Paso 1-3
Route::get('/admin/contrato/{id_reservacion}/resumen', [ContratoBaseController::class, 'resumenContrato'])->name('contrato.resumen');
Route::get('/contrato/{id}/exportar-word', [ContratoController::class, 'exportarWord'])->name('contrato.exportarWord');

// Contrato2Controller
Route::get('/admin/contrato2/{id}', [Contrato2Controller::class, 'mostrarContrato2'])->name('contrato.mostrar2');

// Paso 4: Cargos Adicionales, Delivery y Vehículo
Route::post('/admin/contrato/cargos', [Contrato2Controller::class, 'actualizarCargos'])->name('contrato.actualizarCargos');
Route::post('/admin/contrato/servicios-extra', [Contrato2Controller::class, 'actualizarServiciosExtras'])->name('contrato.servicios_extras');
Route::get('/admin/contrato/cargos/{idContrato}', [Contrato2Controller::class, 'obtenerCargosContrato'])->name('contrato.obtenerCargos');
Route::post('/admin/contrato/cargo-variable', [Contrato2Controller::class, 'guardarCargoVariable']);
Route::post('/admin/reservacion/delivery/guardar', [ContratoController::class, 'guardarDeliveryReservacion'])->name('reservacion.delivery.guardar');
Route::get('/admin/contrato/vehiculos-por-categoria/{idCategoria}', [Contrato2Controller::class, 'vehiculosPorCategoria'])->name('contrato.vehiculosPorCategoria');
Route::post('/admin/contrato/asignar-vehiculo', [Contrato2Controller::class, 'asignarVehiculo'])->name('contrato.asignarVehiculo');
Route::get('/admin/contrato/vehiculo-random/{idCategoria}', [Contrato2Controller::class, 'vehiculoRandom'])->name('contrato.vehiculo-random');

// Upgrade (Dentro del flujo del Paso 4)
Route::get('/admin/contrato/{id}/oferta-upgrade', [Contrato2Controller::class, 'obtenerOfertaUpgrade'])->name('contrato.oferta-upgrade');
Route::post('/admin/contrato/{id}/aceptar-upgrade', [Contrato2Controller::class, 'aceptarUpgrade'])->name('contrato.aceptar-upgrade');
Route::post('/admin/contrato/{id}/rechazar-upgrade', [Contrato2Controller::class, 'rechazarUpgrade'])->name('contrato.rechazar-upgrade');

// Paso 5: Documentación y Conductores
Route::get('/admin/contrato/{id}/cliente', [Contrato2Controller::class, 'obtenerClienteContrato'])->name('contrato.obtenerCliente');
Route::post('/admin/contrato/guardar-documentacion', [Contrato2Controller::class, 'guardarDocumentacion'])->name('contrato.guardarDocumentacion');
Route::get('/admin/contrato/documentacion/{idContrato}', [Contrato2Controller::class, 'obtenerDocumentacion'])->name('contrato.obtenerDocumentacion');
Route::get('/admin/contrato/{id}/documentos-existen', [Contrato2Controller::class, 'verificarDocumentosExistentes'])->name('contrato.documentos.existen');
Route::get('/admin/contrato/{id}/conductores', [Contrato2Controller::class, 'obtenerConductores']);

// Paso 6: Pagos y Finalización
Route::get('/admin/contrato/{id}/resumen-paso6', [Contrato2Controller::class, 'resumenPaso6']);
Route::post('/admin/contrato/pagos/agregar', [Contrato2Controller::class, 'pagoManual'])->name('contrato.pago.agregar');
Route::post('/admin/contrato/pagos/paypal', [Contrato2Controller::class, 'pagoPayPal'])->name('contrato.pago.paypal');
Route::delete('/admin/contrato/pagos/{id_pago}/eliminar', [Contrato2Controller::class, 'eliminarPago'])->name('contrato.pago.eliminar');
Route::post('/admin/contrato/{id}/editar-tarifa', [Contrato2Controller::class, 'editarTarifa']);
Route::post('/admin/contrato/{id}/editar-cortesia', [Contrato2Controller::class, 'editarCortesia']);
Route::post('/admin/contrato/{id}/finalizar', [Contrato2Controller::class, 'finalizar'])->name('contrato.finalizar');
Route::get('/admin/contrato/{id}/status', [Contrato2Controller::class, 'status']);

// ContratoFinalController
Route::get('/admin/contrato-final', [ContratoFinalController::class, 'mostrarContratoFinal'])->name('admin.contratoFinal');
Route::get('/admin/contrato-final/{id}', [ContratoFinalController::class, 'mostrarContratoFinal'])->name('contrato.final');
Route::post('/contrato/firma-cliente', [ContratoFinalController::class, 'guardarFirmaCliente'])->name('contrato.firmaCliente');
Route::post('/contrato/firma-arrendador', [ContratoFinalController::class, 'guardarFirmaArr'])->name('contrato.firmaArr');
Route::post('/contrato/{id}/enviar-correo', [ContratoFinalController::class, 'enviarContratoCorreo']);

// Rutas Externas (Checklist)
Route::post('/contrato/firma-recibio', [ChecklistController::class, 'guardarFirmaRecibio']);

Route::get('/archivo/{id}', function($id){
    $archivo = DB::table('archivos')->where('id_archivo',$id)->first();
    if (!$archivo) abort(404);

    return response($archivo->contenido)
        ->header('Content-Type', $archivo->mime_type);
})->name('archivo.mostrar');

//visor de reservaciones
Route::get('/admin/visor-reservaciones', [App\Http\Controllers\controladorVistasAdmin::class, 'visorReservaciones'])->name('rutaVisorReservaciones');

//historial completo
Route::get('/admin/historial-completo', [App\Http\Controllers\controladorVistasAdmin::class, 'historialCompleto'])->name('rutaHistorialCompleto');
//Alta Cliente
Route::get('/admin/alta-cliente', [App\Http\Controllers\controladorVistasAdmin::class, 'altaCliente'])->name('rutaAltaCliente');
//Licencia
Route::get('/admin/licencia', [App\Http\Controllers\controladorVistasAdmin::class, 'licencia'])->name('rutaLicencia');
//RFC
Route::get('/admin/rfc', [App\Http\Controllers\controladorVistasAdmin::class, 'RFC_Fiscal'])->name('rutaRFC');
//facturar
Route::get('/admin/facturar', [App\Http\Controllers\controladorVistasAdmin::class, 'Facturar'])->name('rutaFacturar');



// 🚗 Rutas de Flotilla (Administrador)
// 🔹 Vista principal de la flotilla
Route::get('/admin/flotilla', [FlotillaController::class, 'indexView'])->name('rutaFlotilla');
// 🔹 Agregar nuevo vehículo
Route::post('/admin/flotilla/agregar', [FlotillaController::class, 'store'])->name('flotilla.agregar');
// 🔹 Consulta vehículo existente
Route::get('/admin/flotilla/{id}/ver', [FlotillaController::class, 'getVehiculo'])->name('flotilla.ver');
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




// === GASTOS ===
Route::get('/admin/gastos', [GastosController::class, 'index'])->name('rutaGastos');
Route::get('/admin/gastos/filtrar', [GastosController::class, 'filtrar']);
// 🔹 Obtener totales por categoría (para las tarjetas)
Route::get('/admin/gastos/totales', [GastosController::class, 'totales'])->name('gastos.totales');

// 🔹 Exportar todos los gastos a Excel (CSV)
Route::get('/admin/gastos/exportar', [GastosController::class, 'exportar'])->name('gastos.exportar');

// 🔹 Rango rápido: hoy, semana o mes
Route::get('/admin/gastos/rango/{tipo}', [GastosController::class, 'rangoRapido'])->name('gastos.rango');




// === Siniestros ===
Route::get('/admin/siniestros', [App\Http\Controllers\SiniestrosController::class, 'index'])->name('rutaSeguros');
Route::post('/admin/siniestros/guardar', [App\Http\Controllers\SiniestrosController::class, 'guardar'])->name('guardarSiniestro');
Route::post('/admin/siniestros/actualizar/{id}', [App\Http\Controllers\SiniestrosController::class, 'actualizar'])->name('actualizarSiniestro');
Route::post('/admin/siniestros/subir/{id}', [App\Http\Controllers\SiniestrosController::class, 'subirArchivo'])->name('subirArchivoSiniestro');
Route::get('/admin/siniestros/ver/{id}', [App\Http\Controllers\SiniestrosController::class, 'ver'])->name('verSiniestro');
Route::get('/admin/siniestros/descargar/{id}', [App\Http\Controllers\SiniestrosController::class, 'descargar'])->name('descargarSiniestro');
// 🔎 Buscador AJAX de vehículos
Route::get('/admin/vehiculos/buscar', [SiniestrosController::class, 'buscarVehiculos'])
    ->name('vehiculos.buscar');


// routes/web.php
Route::get('/preview-poliza', function () {
    $vehiculo = (object)[
        'nombre_publico' => 'Nissan Versa 2023 Automático',
        'placa' => 'VIA-1234',
        'aseguradora' => 'AXA Seguros',
        'fin_vigencia_poliza' => now()->addDays(5)
    ];
    $diasRestantes = 5;
    return view('emails.poliza_vencimiento', compact('vehiculo', 'diasRestantes'));
});


// conductor adicional
// 📄 Ver anexo de conductores adicionales (por contrato)
Route::get('/admin/anexo/conductores/{id}', [ConductorAdicionalController::class, 'verAnexo'])->name('anexo.ver');

// 🗑 Eliminar un conductor adicional del contrato
Route::delete('/admin/anexo/conductor/{id}', [ConductorAdicionalController::class, 'eliminar'])->name('anexo.eliminar');

// ✍️ Guardar firma del arrendador (desde el anexo)
// ⚠️ El name COINCIDE con lo que ya usas en el fetch: route('anexo.guardarFirma')
Route::post('/admin/anexo/firma-arrendador', [ConductorAdicionalController::class, 'guardarFirmaArrendador'])->name('anexo.guardarFirma');

// ✍️ Guardar firma de un conductor adicional
Route::post('/admin/anexo/firma-conductor', [ConductorAdicionalController::class, 'guardarFirmaConductor'])->name('anexo.guardarFirmaConductor');

// ⚠️ enviar anexos por correo (desde el contrato)
Route::post('/admin/anexo/{id}/enviar-anexos',[ConductorAdicionalController::class, 'enviarAnexos'])->name('anexo.enviarAnexos');


// 📄 Mostrar checklist (usando el controlador)
Route::get('/admin/reservacion/{id}/checklist',[ChecklistController::class, 'showChecklist'])->name('checklist.ver');

// 📤 Enviar checklist de SALIDA (fotos + comentarios + fechas/horas)
Route::post('/admin/checklist/{id}/enviar-salida',[ChecklistController::class, 'enviarChecklistSalida'])->name('checklist.enviarSalida');

Route::post('/admin/checklist/{id}/enviar-entrada', [ChecklistController::class, 'enviarChecklistEntrada'])->name('admin.checklist.enviar-entrada');

/* ==============================
CHECKLIST CAMBIO DE AUTO
=================================*/
Route::get('/admin/checklist2/{id}', [ChecklistCambioAutoController::class, 'index'])->name('checklist2');

// enviar checklist cambio de auto
Route::post('/admin/checklist2/{id}/guardar', [ChecklistCambioAutoController::class, 'guardarCambio'])->name('checklist2.guardar');

// Guardar UN daño desde el modal (AJAX)
Route::post('/admin/checklist2/{id}/danio', [ChecklistCambioAutoController::class, 'guardarDano'])
    ->name('checklist2.guardarDano');

    Route::delete('/admin/checklist2/danio/{id}', [ChecklistCambioAutoController::class, 'eliminarDano'])
    ->name('checklist2.eliminarDano');
// 1) Vehículos por categoría (para llenar el modal)
Route::get('/admin/checklist2/{id}/vehiculos/categoria/{idCategoria}', [ChecklistCambioAutoController::class, 'vehiculosPorCategoria'])
    ->name('checklist2.vehiculosPorCategoria');

// 2) Seleccionar / guardar vehículo nuevo (estado en_proceso)
Route::post('/admin/checklist2/{id}/set-vehiculo-nuevo', [ChecklistCambioAutoController::class, 'setVehiculoNuevo'])
    ->name('checklist2.setVehiculoNuevo');

// 3) Confirmar cambio de vehículo (actualiza reservación + confirma cambio)
Route::post('/admin/checklist2/{id}/confirmar-cambio', [ChecklistCambioAutoController::class, 'confirmarCambio'])
    ->name('checklist2.confirmarCambio');


/* ===============================================
   ADMIN · ROLES Y PERMISOS
================================================ */

Route::get('/admin/roles', [RolesController::class, 'index'])->name('roles.index');

Route::get('/admin/roles/listar', [RolesController::class, 'listar']);   // ← CORREGIDO

Route::get('/admin/roles/obtener/{id}', [RolesController::class, 'obtener']); // ← CORREGIDO

Route::post('/admin/roles/crear', [RolesController::class, 'crear']); // ← CORREGIDO

Route::post('/admin/roles/actualizar/{id}', [RolesController::class, 'actualizar']);
Route::post('/admin/roles/eliminar/{id}', [RolesController::class, 'eliminar']);


// LISTAR – UNA SOLA RUTA
// LISTAR
// LISTAR
Route::get('/admin/usuarios', [UsuarioAdminController::class, 'index'])
    ->name('admin.usuarios.index');

// CREAR
Route::post('/admin/usuarios', [UsuarioAdminController::class, 'store'])
    ->name('admin.usuarios.store');

// ACTUALIZAR (URL DISTINTA PARA NO CHOCAR)
Route::post('/admin/usuarios/{id}/update', [UsuarioAdminController::class, 'update'])
    ->name('admin.usuarios.update');

// ELIMINAR ADMIN
Route::post('/admin/usuarios/{id}/delete', [UsuarioAdminController::class, 'destroy'])
    ->name('admin.usuarios.destroy');

// ELIMINAR CLIENTE
Route::post('/admin/clientes/{id}/delete', [UsuarioAdminController::class, 'destroyCliente'])
    ->name('admin.clientes.destroy');


Route::prefix('admin')->group(function () {

    Route::get('/seguros', [SeguroPaqueteController::class, 'index'])->name('paqueteseguros.index');

    Route::get('/seguros/list', [SeguroPaqueteController::class, 'list']);
    Route::get('/seguros/{id}', [SeguroPaqueteController::class, 'show']);

    Route::post('/seguros', [SeguroPaqueteController::class, 'store']);
    Route::put('/seguros/{id}', [SeguroPaqueteController::class, 'update']);
    Route::delete('/seguros/{id}', [SeguroPaqueteController::class, 'destroy']);
});


Route::prefix('admin')->group(function () {

    Route::get('/seguros-individuales', [SeguroIndividualController::class, 'index'])
        ->name('paquetesindividuales.index');

    Route::get('/seguros-individuales/list', [SeguroIndividualController::class, 'list']);
    Route::get('/seguros-individuales/{id}', [SeguroIndividualController::class, 'show']);

    Route::post('/seguros-individuales', [SeguroIndividualController::class, 'store']);
    Route::put('/seguros-individuales/{id}', [SeguroIndividualController::class, 'update']);
    Route::delete('/seguros-individuales/{id}', [SeguroIndividualController::class, 'destroy']);
});

Route::prefix('admin')->group(function () {

    Route::get('/propietarios', [PropietarioVehiculoController::class, 'index'])
        ->name('propietariovehiculo.index');

    Route::get('/propietarios/list', [PropietarioVehiculoController::class, 'list']);

    Route::get('/propietarios/{id}', [PropietarioVehiculoController::class, 'show']);

    Route::post('/propietarios', [PropietarioVehiculoController::class, 'store']);

    Route::put('/propietarios/{id}', [PropietarioVehiculoController::class, 'update']);

    Route::delete('/propietarios/{id}', [PropietarioVehiculoController::class, 'destroy']);

});

Route::post('/contrato/guardar-dato', [ChecklistController::class, 'guardarDato'])
     ->name('contrato.guardarDato');
// VER CHECKLIST
Route::get('/admin/reservacion/{id}/checklist',
    [ChecklistController::class, 'showChecklist']
)->name('checklist.ver');

// GUARDAR GASOLINA
Route::post('/checklist/{id}/guardar-gasolina',
    [ChecklistController::class, 'guardarGasolina']
)->name('checklist.guardarGasolina');

// ACTUALIZAR KILOMETRAJE
Route::post('/admin/checklist/{id}/actualizar-km',
    [ChecklistController::class, 'actualizarKilometraje']
)->name('checklist.actualizar.km');

Route::post('/contrato/{id}/guardar-dano',
    [ChecklistController::class, 'guardarDano']
)->name('contrato.guardarDano');

Route::get('/admin/checklist/{id}/danos',
    [ChecklistController::class, 'listarDanos']);
Route::post('/contrato/guardar-inventario',
    [ChecklistController::class, 'guardarInventario'])
->name('contrato.guardarInventario');

// 👁️ Visor de reservaciones (solo lectura)
Route::get('/ventas/reservaciones', [VisorReservacionesController::class, 'index'])->name('visor.reservaciones');

// 🔌 API del visor
Route::get('/api/visor-reservaciones', [VisorReservacionesController::class, 'api']);


// Vista principal del historial
Route::get('/ventas/historial', [HistorialController::class, 'index'])
    ->name('ventas.historial');

// API que envía cotizaciones + reservaciones + contratos
Route::get('/api/historial', [HistorialController::class, 'api'])
    ->name('api.historial');

    //administracion de reservaciones
Route::get('/admin/administracion-reservaciones', [ContratosAbiertosController::class, 'index'])->name('rutaAdministracionReservaciones');
Route::get('/api/contratos-abiertos/{id}', [ContratosAbiertosController::class, 'detalle'])
    ->name('contratos.detalle');
Route::get('/api/contratos-abiertos', [ContratosAbiertosController::class, 'api']);


// Categorías de carros admin


Route::get('/categorias', [CategoriasController::class, 'index'])->name('categorias.index');
Route::post('/categorias', [CategoriasController::class, 'store'])->name('categorias.store');
Route::put('/categorias/{id}', [CategoriasController::class, 'update'])->name('categorias.update');
Route::delete('/categorias/{id}', [CategoriasController::class, 'destroy'])->name('categorias.destroy');
//suta consultar saldo pendiente
Route::get('/admin/contrato/{id}/saldo', [ContratosAbiertosController::class, 'saldo']);

Route::post('/admin/contrato/{id}/cerrar', [ContratosAbiertosController::class, 'finalizarContrato']);

Route::post('/admin/reservaciones-activas/{id}/no-show',
[ReservacionesActivasController::class, 'noShow'])->name('rutaNoShowReservacionActiva');

Route::post('/admin/reservaciones-activas/{id}/cancelar',
[ReservacionesActivasController::class, 'cancelar'])->name('rutaCancelarReservacionActiva');


}); // <- FIN grupo sesion.activa
Route::view('/politicas', 'Usuarios.Politicas')->name('rutaPoliticas');
Route::get('/politicas', [ReservacionesController::class, 'politicas'])->name('rutaPoliticas');

// ======================
// RUTA PARA CAMBIAR IDIOMAS
// ======================
Route::get('lang/{lang}', function ($lang) {
    // Verificar que el idioma sea válido (español o inglés)
    if (!in_array($lang, ['es', 'en'])) {
        abort(400);
    }

    // Guardar el idioma en la sesión
    session(['locale' => $lang]);

    // Redirigir a la página anterior
    return redirect()->back();
})->name('lang.switch');
