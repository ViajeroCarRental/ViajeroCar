<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ControladorVistas;

//rutas vistas Usuario

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

//rutas vistas Admin

// ============================ ADMIN: PANEL PRINCIPAL ============================
Route::get('/admin/dashboard',[App\Http\Controllers\controladorVistasAdmin::class, 'dashboard'])->name('rutadashboardadmin');        // Resumen / métricas

// ========================== ADMIN: OPERACIÓN (RESERVAS/RENTAS) =================
Route::get('/admin/reservaciones',[App\Http\Controllers\controladorVistasAdmin::class, 'reservaciones'])->name('rutareservacionesadmin'); // Listado de reservaciones
Route::get('/admin/rentas',[App\Http\Controllers\controladorVistasAdmin::class, 'rentas'])->name('rutarentasadmin');               // Gestión de rentas activas

// ============================= ADMIN: FACTURACIÓN/PAGOS ========================
Route::get('/admin/facturas',[App\Http\Controllers\controladorVistasAdmin::class, 'facturas'])->name('rutafacturasadmin');           // Facturas
Route::get('/admin/pagos',[App\Http\Controllers\controladorVistasAdmin::class, 'pagos'])->name('rutapagosadmin');                 // Pagos

// ============================ ADMIN: INVENTARIO/VEHÍCULOS ======================
Route::get('/admin/inventario',[App\Http\Controllers\controladorVistasAdmin::class, 'inventario'])->name('rutainventarioadmin');       // Inventario de vehículos

// ============================== ADMIN: DOCUMENTOS ==============================
Route::get('/admin/plantillas',[App\Http\Controllers\controladorVistasAdmin::class, 'plantillas'])->name('rutaplantillasadmin');       // Plantillas (contratos, PDFs, etc.)
Route::get('/admin/contratos',[App\Http\Controllers\controladorVistasAdmin::class, 'contratos'])->name('rutacontratosadmin');         // Contratos de renta

// =========================== ADMIN: CALENDARIO/OCUPACIÓN =======================
Route::get('/admin/calendario-ocupacion',[App\Http\Controllers\controladorVistasAdmin::class, 'calendarioDeOcupacion'])->name('rutacalendariodeocupacionadmin'); // Agenda/ocupación

// ============================= ADMIN: REPORTES/AUDITORÍA =======================
Route::get('/admin/reportes',[App\Http\Controllers\controladorVistasAdmin::class, 'reportes'])->name('rutareportesadmin');           // Reportes
Route::get('/admin/bitacora',[App\Http\Controllers\controladorVistasAdmin::class, 'bitacora'])->name('rutabitacoraadmin');           // Bitácora de eventos

// =========================== ADMIN: CONFIGURACIÓN/SISTEMA ======================
Route::get('/admin/usuarios-roles',[App\Http\Controllers\controladorVistasAdmin::class, 'usuariosYRoles'])->name('rutausuariosyrolesadmin'); // Usuarios y roles
Route::get('/admin/configuracion',[App\Http\Controllers\controladorVistasAdmin::class, 'configuracion'])->name('rutaconfiguracionadmin');

Route::get('/admin/membresias',[App\Http\Controllers\controladorVistasAdmin::class, 'membresias'])->name('rutamembresiasadmin'); // Preferencias del sistema
