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
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UsuarioAdminController;
use App\Http\Controllers\SeguroPaqueteController;
use App\Http\Controllers\SeguroIndividualController;
use App\Http\Controllers\ContratoFinalController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\ContratosAbiertosController;
use App\Http\Controllers\VisorReservacionesController;
use App\Http\Controllers\HistorialController;
use App\Http\Controllers\ContratosAbiertosControllerController;
use App\Http\Controllers\ChecklistCambioAutoController;
use Illuminate\Support\Facades\DB;
//rutas vistas Usuario

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
// Obtener cargos guardados en el contrato (Paso 4: gasolina + dropoff)
Route::get('/admin/contrato/cargos/{idContrato}', [App\Http\Controllers\ContratoController::class, 'obtenerCargosContrato'])->name('contrato.obtenerCargos');

// 📄 Guardar documentación subida (Paso 5)
Route::post('/admin/contrato/guardar-documentacion', [App\Http\Controllers\ContratoController::class, 'guardarDocumentacion'])->name('contrato.guardarDocumentacion');
Route::get('/admin/contrato/documentacion/{idContrato}',[App\Http\Controllers\ContratoController::class, 'obtenerDocumentacion'])->name('contrato.obtenerDocumentacion');
Route::get('/archivo/{id}', function($id){
    $archivo = DB::table('archivos')->where('id_archivo',$id)->first();
    if (!$archivo) abort(404);

    return response($archivo->contenido)
        ->header('Content-Type', $archivo->mime_type);
})->name('archivo.mostrar');
Route::get('/admin/contrato/{id}/documentos-existen',
    [ContratoController::class, 'verificarDocumentosExistentes']
)->name('contrato.documentos.existen');

// Obtener conductores asociados al contrato (AJAX)
Route::get('/admin/contrato/{id}/conductores', [App\Http\Controllers\ContratoController::class, 'obtenerConductores']);
/* =============================================================
   📌 Solicitud de cambio de fecha (Paso 1 – editar fecha inicio)
   ============================================================= */
// Crear solicitud de cambio de fecha
Route::post('/admin/contrato/solicitar-cambio-fecha',[App\Http\Controllers\ContratoController::class, 'solicitarCambioFecha'])->name('contrato.solicitarCambioFecha');
// Endpoint que recibe el clic del superadmin (aprobar)
Route::get('/admin/contrato/cambio-fecha/aprobar/{token}',[App\Http\Controllers\ContratoController::class, 'aprobarCambioFecha'])->name('contrato.aprobarCambioFecha');
// Endpoint que recibe el clic del superadmin (rechazar)
Route::get('/admin/contrato/cambio-fecha/rechazar/{token}',[App\Http\Controllers\ContratoController::class, 'rechazarCambioFecha'])->name('contrato.rechazarCambioFecha');
Route::get('/admin/contrato/cambio-fecha/estado/{id}', [ContratoController::class, 'estadoCambioFecha']);
Route::post('/admin/contrato/{idReservacion}/recalcular-total', [ContratoController::class, 'recalcularYActualizarTotales']);
// 📌 Obtener vehículos por categoría (para el modal del contrato)
Route::get('/admin/contrato/vehiculos-por-categoria/{idCategoria}', [App\Http\Controllers\ContratoController::class, 'vehiculosPorCategoria'])->name('contrato.vehiculosPorCategoria');
Route::post('/admin/contrato/{idReservacion}/actualizar-categoria',[App\Http\Controllers\ContratoController::class, 'actualizarCategoria'])->name('contrato.actualizarCategoria');
// 🚗 Asignar vehículo a la reservación
Route::post('/admin/contrato/asignar-vehiculo', [App\Http\Controllers\ContratoController::class, 'asignarVehiculo'])->name('contrato.asignarVehiculo');
// 🔥 Upgrade — obtener oferta
Route::get('/admin/contrato/{id}/oferta-upgrade', [ContratoController::class, 'obtenerOfertaUpgrade'])->name('contrato.oferta-upgrade');

// 🔥 Upgrade — aceptar
Route::post('/admin/contrato/{id}/aceptar-upgrade', [ContratoController::class, 'aceptarUpgrade'])->name('contrato.aceptar-upgrade');

// 🔥 Upgrade — rechazar
Route::post('/admin/contrato/{id}/rechazar-upgrade', [ContratoController::class, 'rechazarUpgrade'])->name('contrato.rechazar-upgrade');

Route::get('/admin/contrato/categoria-info/{codigo}',[ContratoController::class, 'categoriaInfo'])->name('contrato.categoria-info');


Route::get('/admin/contrato/vehiculo-random/{idCategoria}',[ContratoController::class, 'vehiculoRandom'])->name('contrato.vehiculo-random');
Route::post('/admin/reservacion/delivery/guardar', [ContratoController::class, 'guardarDeliveryReservacion'])->name('reservacion.delivery.guardar');
Route::post('/admin/contrato/seguros-individuales',[ContratoController::class, 'actualizarSegurosIndividuales'])->name('contrato.actualizarSegurosIndividuales');
Route::delete('/admin/contrato/seguros-individuales',[ContratoController::class, 'eliminarSeguroIndividual'])->name('contrato.eliminarSeguroIndividual');
Route::delete('/admin/contrato/seguros-individuales/todos',[ContratoController::class, 'eliminarTodosLosIndividuales'])->name('contrato.eliminarIndividualesTodos');
Route::post('/admin/contrato/cargo-variable', [ContratoController::class, 'guardarCargoVariable']);
// PASO 6 — resumen
Route::get('/admin/contrato/{id}/resumen-paso6',[ContratoController::class, 'resumenPaso6']);

// PASO 6 — agregar pago
Route::post('/admin/contrato/pagos/agregar',[ContratoController::class, 'agregarPagoPaso6']);

// 🔹 Registrar pago manual (efectivo / terminal / transferencia)
Route::post('/admin/contrato/pagos/agregar', [ContratoController::class, 'pagoManual'])->name('contrato.pago.agregar');

// 🔹 Registrar pago con PayPal (pasarela en línea)
Route::post('/admin/contrato/pagos/paypal',[ContratoController::class, 'pagoPayPal'])->name('contrato.pago.paypal');

// 🔹 Eliminar un pago registrado
Route::delete('/admin/contrato/pagos/{id_pago}/eliminar',[ContratoController::class, 'eliminarPago'])->name('contrato.pago.eliminar');
Route::get('/admin/contrato/{id_reservacion}/resumen',[ContratoController::class, 'resumenContrato'])->name('contrato.resumen');

Route::get('/admin/contrato-final', [App\Http\Controllers\ContratoFinalController::class, 'mostrarContratoFinal'])->name('admin.contratoFinal');
Route::post('/admin/contrato/{id}/editar-tarifa', [ContratoController::class, 'editarTarifa']);
Route::post('/admin/contrato/{id}/editar-cortesia', [ContratoController::class, 'editarCortesia']);
Route::post('/admin/contrato/{id}/finalizar',[ContratoController::class, 'finalizar'])->name('contrato.finalizar');

Route::get('/admin/contrato-final/{id}',
    [App\Http\Controllers\ContratoFinalController::class, 'mostrarContratoFinal']
)->name('contrato.final');

Route::get('/admin/contrato/{id}/status',
    [ContratoController::class, 'status']
);

Route::post('/contrato/firma-cliente', [ContratoFinalController::class, 'guardarFirmaCliente'])->name('contrato.firmaCliente');
Route::post('/contrato/firma-arrendador', [ContratoFinalController::class, 'guardarFirmaArr'])->name('contrato.firmaArr');
Route::get('/contrato/{id}/exportar-word', [ContratoController::class, 'exportarWord'])
    ->name('contrato.exportarWord');
Route::post('/contrato/{id}/enviar-correo', [ContratoFinalController::class, 'enviarContratoCorreo']);


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
// Ver anexo
Route::get('/admin/reservacion/{id}/anexo',
    [ConductorAdicionalController::class, 'verAnexo'])->name('anexo.ver');

// Guardar nuevo conductor
Route::post('/admin/anexo/guardar',
    [ConductorAdicionalController::class, 'guardar'])->name('anexo.guardar');

// Eliminar conductor
Route::delete('/admin/anexo/{id}/eliminar',
    [ConductorAdicionalController::class, 'eliminar'])->name('anexo.eliminar');
    // Guardar firma del arrendador
Route::post('/admin/anexo/guardar-firma',
    [ConductorAdicionalController::class, 'guardarFirma'])
    ->name('anexo.guardarFirma');




// 📄 Mostrar checklist (usando el controlador)
Route::get('/admin/reservacion/{id}/checklist',[ChecklistController::class, 'showChecklist'])->name('checklist.ver');

// 📤 Enviar checklist de SALIDA (fotos + comentarios + fechas/horas)
Route::post('/admin/checklist/{id}/enviar-salida',[ChecklistController::class, 'enviarChecklistSalida'])->name('checklist.enviarSalida');

Route::post('/admin/checklist/{id}/enviar-entrada', [ChecklistController::class, 'enviarChecklistEntrada'])->name('admin.checklist.enviar-entrada');


Route::get('/admin/checklist2',[ChecklistCambioAutoController::class, 'index'])->name('checklist2');

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
use App\Http\Controllers\CategoriasController;

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
