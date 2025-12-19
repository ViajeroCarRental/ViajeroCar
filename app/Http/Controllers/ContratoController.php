<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;


class ContratoController extends Controller
{
    /**
     * 📄 Mostrar un contrato específico a partir del ID de reservación.
     */
    public function mostrarContrato($id)
{
    try {
        // 🔹 1. Obtener la reservación base
        $reservacion = DB::table('reservaciones as r')
            ->leftJoin('sucursales as sr', 'r.sucursal_retiro', '=', 'sr.id_sucursal')
            ->leftJoin('sucursales as se', 'r.sucursal_entrega', '=', 'se.id_sucursal')
            ->select(
                'r.id_reservacion',
                'r.codigo',
                'r.nombre_cliente',
                'r.email_cliente',
                'r.telefono_cliente',
                'r.fecha_inicio',
                'r.fecha_fin',
                'r.hora_retiro',
                'r.hora_entrega',
                'r.total',
                'sr.nombre as sucursal_retiro_nombre',
                'se.nombre as sucursal_entrega_nombre',
                'r.id_vehiculo',
                'r.id_categoria'
            )
            ->where('r.id_reservacion', $id)
            ->first();

        if (!$reservacion) {
            return redirect()->back()->with('error', 'Reservación no encontrada.');
        }

        // 🔹 2. Buscar o crear contrato
        $contrato = DB::table('contratos')
            ->where('id_reservacion', $reservacion->id_reservacion)
            ->first();

        if (!$contrato) {
            $numeroContrato = 'CTR-' . strtoupper(substr($reservacion->codigo, 0, 4)) . '-' . now()->format('ymdHis');

            $idContrato = DB::table('contratos')->insertGetId([
                'id_reservacion'  => $reservacion->id_reservacion,
                'numero_contrato' => $numeroContrato,
                'estado'          => 'abierto',
                'abierto_en'      => now(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            DB::table('contrato_evento')->insert([
                'id_contrato'  => $idContrato,
                'evento'       => 'Contrato creado automáticamente',
                'detalle'      => json_encode(['reservacion' => $reservacion->codigo]),
                'realizado_en' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            $contrato = DB::table('contratos')->where('id_contrato', $idContrato)->first();
        }

        // 🔹 3. Cargar categorías (FALTABA ESTA PARTE)
        $categorias = DB::table('categorias_carros')
            ->select('id_categoria', 'nombre')
            ->orderBy('nombre')
            ->get();

        // 🔹 4. Cargos adicionales disponibles
        $cargos_conceptos = DB::table('cargo_concepto')
            ->where('activo', true)
            ->get();

        // 🔹 5. Vehículo
        $vehiculo = DB::table('vehiculos')
            ->where('id_vehiculo', $reservacion->id_vehiculo)
            ->first();

        // 🔹 6. Seguros
        $seguros = DB::table('seguro_paquete')
            ->where('activo', true)
            ->select('id_paquete as id_seguro', 'nombre', 'descripcion as cobertura', 'precio_por_dia')
            ->get();

        // 🔹 7. Seguro seleccionado
        $seguroSeleccionado = DB::table('reservacion_paquete_seguro as rps')
            ->join('seguro_paquete as sp', 'rps.id_paquete', '=', 'sp.id_paquete')
            ->select('sp.id_paquete as id_seguro', 'sp.nombre', 'sp.precio_por_dia')
            ->where('rps.id_reservacion', $reservacion->id_reservacion)
            ->first();

            // 🔹 7.1. Seguros individuales seleccionados (si existen)
        $segurosIndividualesSeleccionados = $this->obtenerIndividualesSeleccionados($reservacion->id_reservacion);

        // 🔹 7.2. Protecciones individuales disponibles
$individuales = DB::table('seguro_individuales')
    ->where('activo', true)
    ->select('id_individual', 'nombre', 'descripcion', 'precio_por_dia')
    ->get();

    // Clasificación por nombre
$grupo_colision = $individuales->filter(fn($i) =>
    str_contains($i->nombre, 'LDW') ||
    str_contains($i->nombre, 'PDW') ||
    str_contains($i->nombre, 'CDW') ||
    str_contains($i->nombre, 'DECLINE')
);

$grupo_medicos = $individuales->filter(fn($i) =>
    str_contains($i->nombre, 'PAI')
);

$grupo_asistencia = $individuales->filter(fn($i) =>
    str_contains($i->nombre, 'PRA')
);

$grupo_terceros = $individuales->filter(fn($i) =>
    str_contains($i->nombre, 'LI') // LI – ALI – EXT.LI
);

$grupo_protecciones = $individuales->filter(fn($i) =>
    str_contains($i->nombre, 'LOU') ||
    str_contains($i->nombre, 'LA')
);

// Pasar a la vista
view()->share([
    'grupo_colision'     => $grupo_colision,
    'grupo_medicos'      => $grupo_medicos,
    'grupo_asistencia'   => $grupo_asistencia,
    'grupo_terceros'     => $grupo_terceros,
    'grupo_protecciones' => $grupo_protecciones,
]);



        // 🔹 8. Servicios adicionales
        $servicios = DB::table('servicios')->get();

        // 🔹 9. Detectar conductores adicionales
        $servicioConductor = DB::table('servicios')
            ->where('nombre', 'LIKE', '%conductor adicional%')
            ->where('activo', true)
            ->first();

        $conductoresExtras = collect();

        if ($servicioConductor) {
            $adicional = DB::table('reservacion_servicio')
                ->where('id_reservacion', $reservacion->id_reservacion)
                ->where('id_servicio', $servicioConductor->id_servicio)
                ->first();

            if ($adicional && $adicional->cantidad > 0) {
                for ($i = 1; $i <= $adicional->cantidad; $i++) {
                    $conductoresExtras->push([
                        'id_conductor' => null,
                        'nombres' => "Conductor adicional $i",
                    ]);
                }
            }
        }

        // 🔹 11. Ubicaciones (🔥 LO QUE FALTABA)
$ubicaciones = DB::table('ubicaciones_servicio')
    ->where('activo', 1)
    ->orderBy('estado')
    ->orderBy('destino')
    ->get();

    // 🔹 12. Cargar datos de Delivery desde la reservación
$delivery = DB::table('reservaciones')
    ->select(
        'delivery_activo as activo',
        'delivery_ubicacion as id_ubicacion',
        'delivery_direccion as direccion',
        'delivery_km as kms',
        'delivery_precio_km as precio_km',
        'delivery_total as total'
    )
    ->where('id_reservacion', $reservacion->id_reservacion)
    ->first();


    // Obtener costo por km de la categoría seleccionada
$costoKmCategoria = DB::table('categoria_costo_km')
    ->where('id_categoria', $reservacion->id_categoria)
    ->value('costo_km') ?? 0;


        // 🔹 10. Retornar vista
        return view('Admin.Contrato', [
            'reservacion'        => $reservacion,
            'vehiculo'           => $vehiculo,
            'seguros'            => $seguros,
            'servicios'          => $servicios,
            'seguroSeleccionado' => $seguroSeleccionado,
            'contrato'           => $contrato,
            'cargos_conceptos'   => $cargos_conceptos,
            'conductoresExtras'  => $conductoresExtras,
            'categorias'         => $categorias,  // ←🔥 AHORA SÍ LO MANDAS A LA VISTA
            'ubicaciones'        => $ubicaciones,
            'costoKmCategoria'   => $costoKmCategoria,
            'delivery'           => $delivery,
            'segurosIndividualesSeleccionados' => $segurosIndividualesSeleccionados,
            'individuales' => $individuales,
            'idReservacion' => $id  // 👈 ESTO ESTÁ BIEN


        ]);



    } catch (\Throwable $e) {
        Log::error('Error en ContratoController@mostrarContrato: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Ocurrió un error al cargar la reservación.');
    }
}


public function obtenerConductores($idContrato)
{
    try {
        $conductores = DB::table('contrato_conductor_adicional')
            ->where('id_contrato', $idContrato)
            ->select('id_conductor', 'nombres', 'apellidos')
            ->orderBy('id_conductor')
            ->get();

        return response()->json($conductores);
    } catch (\Throwable $e) {
        Log::error("Error en ContratoController@obtenerConductores: " . $e->getMessage());
        return response()->json(['error' => 'Error al obtener los conductores adicionales.'], 500);
    }
}



    /**
     * ⚙️ Actualiza servicios adicionales seleccionados.
     */
    public function actualizarServicios(Request $request)
{
    try {
        $data = $request->validate([
            'id_reservacion'  => 'required|integer|exists:reservaciones,id_reservacion',
            'id_servicio'     => 'required|integer|exists:servicios,id_servicio',
            'cantidad'        => 'required|integer|min:0',
            'precio_unitario' => 'required|numeric|min:0',
        ]);

        // 🔹 Buscar el servicio actual
        $servicio = DB::table('servicios')
            ->where('id_servicio', $data['id_servicio'])
            ->first();

        // 🔹 Buscar registro existente en reservacion_servicio
        $existe = DB::table('reservacion_servicio')
            ->where('id_reservacion', $data['id_reservacion'])
            ->where('id_servicio', $data['id_servicio'])
            ->first();

        // =========================================================
        // 🧹 CASO 1: Si la cantidad es 0 → eliminar servicio
        // =========================================================
        if ($data['cantidad'] == 0) {
            if ($existe) {
                DB::table('reservacion_servicio')->where('id', $existe->id)->delete();
            }

            // 🔁 Si el servicio es "Conductor adicional", eliminar también los conductores del contrato
            if ($servicio && stripos($servicio->nombre, 'conductor adicional') !== false) {
                $contrato = DB::table('contratos')
                    ->where('id_reservacion', $data['id_reservacion'])
                    ->first();

                if ($contrato) {
                    DB::table('contrato_conductor_adicional')
                        ->where('id_contrato', $contrato->id_contrato)
                        ->delete();
                }
            }

            return response()->json([
                'status' => 'deleted',
                'msg' => 'Servicio eliminado y sincronizado correctamente.'
            ]);
        }

        // =========================================================
        // ✏️ CASO 2: Actualizar servicio existente
        // =========================================================
        if ($existe) {
            DB::table('reservacion_servicio')
                ->where('id', $existe->id)
                ->update([
                    'cantidad'        => $data['cantidad'],
                    'precio_unitario' => $data['precio_unitario'],
                    'updated_at'      => now(),
                ]);
            $accion = 'updated';
        } else {
            // =========================================================
            // ➕ CASO 3: Insertar servicio nuevo
            // =========================================================
            DB::table('reservacion_servicio')->insert([
                'id_reservacion'  => $data['id_reservacion'],
                'id_servicio'     => $data['id_servicio'],
                'cantidad'        => $data['cantidad'],
                'precio_unitario' => $data['precio_unitario'],
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
            $accion = 'inserted';
        }

        // =========================================================
        // 🚗 SINCRONIZAR CONDUCTORES ADICIONALES
        // =========================================================
        if ($servicio && stripos($servicio->nombre, 'conductor adicional') !== false) {
            $contrato = DB::table('contratos')
                ->where('id_reservacion', $data['id_reservacion'])
                ->first();

            if ($contrato) {
                $idContrato = $contrato->id_contrato;
                $cantidadDeseada = $data['cantidad'];

                $conductoresActuales = DB::table('contrato_conductor_adicional')
                    ->where('id_contrato', $idContrato)
                    ->get();

                $actualCount = $conductoresActuales->count();

                // 🧩 Si hay más de los necesarios → eliminar excedentes
                if ($actualCount > $cantidadDeseada) {
                    $sobrantes = $actualCount - $cantidadDeseada;
                    DB::table('contrato_conductor_adicional')
                        ->where('id_contrato', $idContrato)
                        ->orderByDesc('id_conductor')
                        ->limit($sobrantes)
                        ->delete();
                }

                // 🧩 Si hay menos → crear los faltantes
                if ($actualCount < $cantidadDeseada) {
                    for ($i = $actualCount + 1; $i <= $cantidadDeseada; $i++) {
                        DB::table('contrato_conductor_adicional')->insert([
                            'id_contrato' => $idContrato,
                            'nombres' => "Conductor adicional {$i}",
                            'apellidos' => '',
                            'numero_licencia' => null,
                            'pais_licencia' => null,
                            'fecha_nacimiento' => null,
                            'contacto' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        }

        return response()->json([
            'status' => $accion,
            'msg' => 'Servicio actualizado y sincronizado correctamente.'
        ]);
    } catch (\Throwable $e) {
        Log::error('Error en ContratoController@actualizarServicios: ' . $e->getMessage());
        return response()->json(['error' => 'Error interno al actualizar servicios.'], 500);
    }
}


    /**
     * 🛡️ Actualiza paquete de seguro seleccionado.
     */
    public function actualizarSeguro(Request $request)
{
    try {
        $data = $request->validate([
            'id_reservacion'  => 'required|integer|exists:reservaciones,id_reservacion',
            'id_paquete'      => 'required|integer|exists:seguro_paquete,id_paquete',
            'precio_por_dia'  => 'required|numeric|min:0',
        ]);

        $idReservacion = $data['id_reservacion'];

        // 🔥 FIX IMPORTANTE:
        // Al activar un paquete → eliminar TODOS los individuales
        DB::table('reservacion_seguro_individual')
            ->where('id_reservacion', $idReservacion)
            ->delete();

        // Buscar si ya existía un paquete
        $existe = DB::table('reservacion_paquete_seguro')
            ->where('id_reservacion', $idReservacion)
            ->first();

        // 🔻 Si precio es 0 → eliminar paquete
        if ($data['precio_por_dia'] == 0) {
            if ($existe) {
                DB::table('reservacion_paquete_seguro')
                    ->where('id', $existe->id)
                    ->delete();

                return response()->json([
                    'status' => 'deleted',
                    'msg'    => 'Paquete eliminado correctamente.'
                ]);
            }

            return response()->json([
                'status' => 'noop',
                'msg'    => 'No existía paquete para eliminar.'
            ]);
        }

        // 🔄 Si ya existía → actualizar
        if ($existe) {
            DB::table('reservacion_paquete_seguro')
                ->where('id', $existe->id)
                ->update([
                    'id_paquete'      => $data['id_paquete'],
                    'precio_por_dia'  => $data['precio_por_dia'],
                    'updated_at'      => now(),
                ]);

            return response()->json([
                'status' => 'updated',
                'msg'    => 'Paquete actualizado correctamente.'
            ]);
        }

        // ➕ Insertar nuevo paquete
        DB::table('reservacion_paquete_seguro')->insert([
            'id_reservacion'  => $idReservacion,
            'id_paquete'      => $data['id_paquete'],
            'precio_por_dia'  => $data['precio_por_dia'],
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return response()->json([
            'status' => 'inserted',
            'msg'    => 'Paquete agregado correctamente.'
        ]);

    } catch (\Throwable $e) {
        Log::error('Error en actualizarSeguro: ' . $e->getMessage());
        return response()->json([
            'error' => 'Error interno al actualizar el seguro.'
        ], 500);
    }
}


    /**
     * 💰 Activa o desactiva cargos adicionales (toggle ON/OFF).
     */
    public function actualizarCargos(Request $request)
{
    try {
        $idContrato = $request->id_contrato;
        $idConcepto = $request->id_concepto;

        // VALIDACIÓN RÁPIDA
        if (!$idContrato || !$idConcepto) {
            return response()->json(['success' => false, 'msg' => 'Datos incompletos']);
        }

        // VERIFICAR SI EL CARGO YA EXISTE
        $existe = DB::table('cargo_adicional')
            ->where('id_contrato', $idContrato)
            ->where('id_concepto', $idConcepto)
            ->count();

        if ($existe) {
            // Si ya existe, eliminarlo (switch apagado)
            DB::table('cargo_adicional')
                ->where('id_contrato', $idContrato)
                ->where('id_concepto', $idConcepto)
                ->delete();

            return response()->json([
                'success' => true,
                'action'  => 'deleted',
                'msg'     => 'Cargo eliminado'
            ]);
        } else {
            // Insertar como CARGO FIJO
            DB::table('cargo_adicional')->insert([
                'id_contrato' => $idContrato,
                'id_concepto' => $idConcepto,
                'concepto'    => DB::table('cargo_concepto')->where('id_concepto', $idConcepto)->value('nombre') ?? 'Cargo adicional',
                'monto'       => DB::table('cargo_concepto')->where('id_concepto', $idConcepto)->value('monto_base') ?? 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            return response()->json([
                'success' => true,
                'action'  => 'inserted',
                'msg'     => 'Cargo agregado'
            ]);
        }

    } catch (\Exception $e) {
        Log::error("ERROR actualizarCargos Paso 4: " . $e->getMessage());
        return response()->json(['success' => false, 'msg' => 'Error en servidor']);
    }
}

public function obtenerCargosContrato($idContrato)
{
    try {
        $cargos = DB::table('cargo_adicional')
            ->where('id_contrato', $idContrato)
            ->select('id_concepto', 'monto', 'detalle')
            ->get()
            ->map(function ($c) {
                $c->detalle = $c->detalle ? json_decode($c->detalle) : null;
                return $c;
            });

        return response()->json([
            'success' => true,
            'cargos'  => $cargos
        ]);

    } catch (\Throwable $e) {
        Log::error("ERROR obtenerCargosContrato: ".$e->getMessage());
        return response()->json([
            'success' => false
        ], 500);
    }
}


    /**
 * 📄 Guarda documentación de identificación y licencia
 */
public function guardarDocumentacion(Request $request)
{
    try {

        // ============================
        // 1. VALIDACIÓN DE DATOS
        // ============================
        $data = $request->validate([
            'id_contrato'   => 'required|integer|exists:contratos,id_contrato',

            // Identificación
            'tipo_identificacion' => 'nullable|string|max:50',
            'numero_identificacion' => 'nullable|string|max:50',
            'nombre' => 'nullable|string|max:100',
            'apellido_paterno' => 'nullable|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'fecha_vencimiento_id' => 'nullable|date',

            // Contacto de emergencia
            'contacto_emergencia' => 'nullable|string|max:120',

            // Licencia
            'numero_licencia' => 'nullable|string|max:80',
            'emite_licencia' => 'nullable|string|max:100', // viene del HTML
            'fecha_emision_licencia' => 'nullable|date',
            'fecha_vencimiento_licencia' => 'nullable|date',

            // Conductor adicional (si aplica)
            'id_conductor' => 'nullable|integer|exists:contrato_conductor_adicional,id_conductor',

            // Archivos (MULTIPLATAFORMA)
            'idFrente'  => 'nullable|file|max:20480',
            'idReverso' => 'nullable|file|max:20480',
            'licFrente' => 'nullable|file|max:20480',
            'licReverso'=> 'nullable|file|max:20480',

        ]);

        $idContrato   = $data['id_contrato'];
        $idConductor  = $data['id_conductor'] ?? null;
        // ============================
// 1.1 VALIDACIÓN REAL DE ARCHIVOS (MULTIPLATAFORMA)
// ============================
$archivos = [
    'idFrente'  => $request->file('idFrente'),
    'idReverso' => $request->file('idReverso'),
    'licFrente' => $request->file('licFrente'),
    'licReverso'=> $request->file('licReverso'),
];

foreach ($archivos as $campo => $file) {
    if ($file) {
        $mime = $file->getMimeType();

        if (!str_starts_with($mime, 'image/')) {
            throw ValidationException::withMessages([
                $campo => "Tipo de archivo no permitido: $mime"
            ]);
        }
    }
}



        // ============================
        // 2. FUNCIÓN PARA GUARDAR FOTO
        // ============================
        $guardarImagen = function ($file) {
            if (!$file) return null;

            return DB::table('archivos')->insertGetId([
                'nombre_original' => $file->getClientOriginalName(),
                'tipo'            => 'imagen',
                'contenido'       => file_get_contents($file->getRealPath()), // LONGBLOB real
                'extension'       => $file->extension(),
                'mime_type'       => $file->getMimeType(),
                'tamano_bytes'    => $file->getSize(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        };

        // Guardar imágenes
        $idArchivoFrente = $guardarImagen($request->file('idFrente'));
        $idArchivoReverso = $guardarImagen($request->file('idReverso'));
        $idLicFrente      = $guardarImagen($request->file('licFrente'));
        $idLicReverso     = $guardarImagen($request->file('licReverso'));


        // ============================
        // 3. CREAR CONDUCTOR ADICIONAL
        // ============================
        if (empty($idConductor) && !empty($data['nombre'])) {

            $idConductor = DB::table('contrato_conductor_adicional')->insertGetId([
                'id_contrato'      => $idContrato,
                'nombres'          => $data['nombre'],
                'apellidos'        => trim(($data['apellido_paterno'] ?? '') . ' ' . ($data['apellido_materno'] ?? '')),
                'numero_licencia'  => $data['numero_licencia'] ?? null,
                'pais_licencia'    => $data['emite_licencia'] ?? null,
                'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
                'contacto'         => $data['contacto_emergencia'] ?? null, // NUEVO
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            DB::table('contrato_evento')->insert([
                'id_contrato' => $idContrato,
                'evento'      => 'Conductor adicional registrado automáticamente',
                'detalle'     => json_encode([
                    'nombre'   => $data['nombre'],
                    'licencia' => $data['numero_licencia'] ?? 'N/A'
                ]),
                'realizado_en' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }


        // ============================
        // 4. DOCUMENTO: IDENTIFICACIÓN
        // ============================
        DB::table('contrato_documento')->insert([
            'id_contrato'        => $idContrato,
            'id_conductor'       => $idConductor,
            'tipo'               => 'identificacion',

            'tipo_identificacion'=> $data['tipo_identificacion'] ?? null,
            'numero_identificacion' => $data['numero_identificacion'] ?? null,
            'nombre'             => $data['nombre'] ?? null,
            'apellido_paterno'   => $data['apellido_paterno'] ?? null,
            'apellido_materno'   => $data['apellido_materno'] ?? null,
            'fecha_nacimiento'   => $data['fecha_nacimiento'] ?? null,
            'fecha_vencimiento'  => $data['fecha_vencimiento_id'] ?? null,

            'id_archivo_frente'  => $idArchivoFrente,
            'id_archivo_reverso' => $idArchivoReverso,

            'created_at'         => now(),
            'updated_at'         => now(),
        ]);


        // ============================
        // 5. DOCUMENTO: LICENCIA
        // ============================
        DB::table('contrato_documento')->insert([
            'id_contrato'       => $idContrato,
            'id_conductor'      => $idConductor,
            'tipo'              => 'licencia',

            'numero_identificacion' => $data['numero_licencia'] ?? null,
            'pais_emision'      => $data['emite_licencia'] ?? null, // CORREGIDO
            'fecha_emision'     => $data['fecha_emision_licencia'] ?? null,
            'fecha_vencimiento' => $data['fecha_vencimiento_licencia'] ?? null,

            'id_archivo_frente' => $idLicFrente,
            'id_archivo_reverso'=> $idLicReverso,

            'created_at'        => now(),
            'updated_at'        => now(),
        ]);


        // ============================
        // 6. VALIDAR LICENCIA VENCIDA
        // ============================
        if (!empty($data['fecha_vencimiento_licencia'])) {

            $vence = Carbon::parse($data['fecha_vencimiento_licencia']);

            if ($vence->isPast()) {

                DB::table('contrato_evento')->insert([
                    'id_contrato' => $idContrato,
                    'evento'      => 'Licencia vencida detectada',
                    'detalle'     => json_encode([
                        'conductor' => $idConductor ? "Adicional #$idConductor" : 'Titular',
                        'vence'     => $vence->format('Y-m-d')
                    ]),
                    'realizado_en'=> now(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                return response()->json([
                    'warning' => true,
                    'msg' => '⚠️ La licencia está vencida. Por favor, sube una vigente.'
                ]);
            }
        }


        // ============================
        // 7. RESPUESTA FINAL
        // ============================
        return response()->json([
            'success' => true,
            'msg'     => 'Documentación guardada correctamente.'
        ]);


    }     catch (ValidationException $e) {

        return response()->json([
            'error' => 'Error de validación',
            'detalles' => $e->errors(),
            'files' => collect($request->files)->map(fn($f) => [
                'nombre' => $f->getClientOriginalName(),
                'mime'   => $f->getMimeType(),
                'tamano' => $f->getSize(),
            ]),
        ], 422);

    } catch (\Throwable $e) {

        Log::error("ERROR guardarDocumentacion: ".$e->getMessage());

        return response()->json([
            'error'   => 'Error interno al guardar documentación.',
            'detalle' => $e->getMessage(),
        ], 500);
    }

}

public function obtenerDocumentacion($idContrato)
{
    try {

        // ====================================================
        // 🔧 Función interna para crear URL de archivo
        // ====================================================
        $makeUrl = function ($idArchivo) {
            return $idArchivo ? route('archivo.mostrar', ['id' => $idArchivo]) : null;
        };

        // ====================================================
        // 🔹 OBTENER DOCUMENTOS DEL TITULAR
        // ====================================================
        $docsTitular = DB::table('contrato_documento')
            ->where('id_contrato', $idContrato)
            ->whereNull('id_conductor')
            ->get();

        $ident = $docsTitular->firstWhere('tipo', 'identificacion');
        $lic   = $docsTitular->firstWhere('tipo', 'licencia');

        // ====================================================
        // 🔹 Determinar si la licencia está vencida
        // ====================================================
        $licenciaVencida = false;
        if ($lic && $lic->fecha_vencimiento) {
            $licenciaVencida = \Carbon\Carbon::parse($lic->fecha_vencimiento)->isPast();
        }

        // ====================================================
        // 🔹 UNIFICAR CAMPOS DEL TITULAR
        // ====================================================
        $titularCampos = [
            // Identificación
            'tipo_identificacion'   => $ident->tipo_identificacion ?? null,
            'numero_identificacion' => $ident->numero_identificacion ?? null,
            'nombre'                => $ident->nombre ?? null,
            'apellido_paterno'      => $ident->apellido_paterno ?? null,
            'apellido_materno'      => $ident->apellido_materno ?? null,
            'fecha_nacimiento'      => $ident->fecha_nacimiento ?? null,
            'fecha_vencimiento_id'  => $ident->fecha_vencimiento ?? null,

            // Licencia
            'numero_licencia'         => $lic->numero_identificacion ?? null,
            'pais_emision'            => $lic->pais_emision ?? null,
            'fecha_emision_licencia'  => $lic->fecha_emision ?? null,
            'fecha_vencimiento_licencia' => $lic->fecha_vencimiento ?? null,

            // Contacto
            'contacto_emergencia' => null // No estaba en esta tabla
        ];

        // ====================================================
        // 🔹 UNIFICAR ARCHIVOS DE TITULAR
        // ====================================================
        $titularArchivos = [
            'idFrente_url'  => $ident ? $makeUrl($ident->id_archivo_frente) : null,
            'idReverso_url' => $ident ? $makeUrl($ident->id_archivo_reverso) : null,
            'licFrente_url' => $lic   ? $makeUrl($lic->id_archivo_frente)  : null,
            'licReverso_url'=> $lic   ? $makeUrl($lic->id_archivo_reverso) : null,
        ];

        // ====================================================
        // 🔹 TITULAR FINAL, FORMATO EXACTO PARA JS
        // ====================================================
        $titular = [
            'campos'           => $titularCampos,
            'archivos'         => $titularArchivos,
            'licencia_vencida' => $licenciaVencida,
        ];

        // ====================================================
        // 🔹 OBTENER CONDUCTORES ADICIONALES
        // ====================================================
        $conductores = DB::table('contrato_conductor_adicional')
            ->where('id_contrato', $idContrato)
            ->get();

        $docsAdicionales = DB::table('contrato_documento')
            ->where('id_contrato', $idContrato)
            ->whereNotNull('id_conductor')
            ->get()
            ->groupBy('id_conductor');

        $adicionalesData = [];

        foreach ($conductores as $c) {

            $grupo = $docsAdicionales->get($c->id_conductor, collect());
            $ident = $grupo->firstWhere('tipo', 'identificacion');
            $lic   = $grupo->firstWhere('tipo', 'licencia');

            $vencida = false;
            if ($lic && $lic->fecha_vencimiento) {
                $vencida = \Carbon\Carbon::parse($lic->fecha_vencimiento)->isPast();
            }

            // 📌 Unificar campos del adicional
            $campos = [
                'tipo_identificacion'   => $ident->tipo_identificacion ?? null,
                'numero_identificacion' => $ident->numero_identificacion ?? null,
                'nombre'                => $ident->nombre ?? $c->nombres,
                'apellido_paterno'      => $ident->apellido_paterno ?? null,
                'apellido_materno'      => $ident->apellido_materno ?? null,
                'fecha_nacimiento'      => $ident->fecha_nacimiento ?? $c->fecha_nacimiento,
                'fecha_vencimiento_id'  => $ident->fecha_vencimiento ?? null,

                'numero_licencia'         => $lic->numero_identificacion ?? $c->numero_licencia,
                'pais_emision'            => $lic->pais_emision ?? $c->pais_licencia,
                'fecha_emision_licencia'  => $lic->fecha_emision ?? null,
                'fecha_vencimiento_licencia' => $lic->fecha_vencimiento ?? null,

                'contacto_emergencia'     => $c->contacto,
            ];

            // 📌 Unificar archivos del adicional
            $archivos = [
                'idFrente_url'  => $ident ? $makeUrl($ident->id_archivo_frente) : null,
                'idReverso_url' => $ident ? $makeUrl($ident->id_archivo_reverso) : null,
                'licFrente_url' => $lic   ? $makeUrl($lic->id_archivo_frente)  : null,
                'licReverso_url'=> $lic   ? $makeUrl($lic->id_archivo_reverso) : null,
            ];

            // 📌 Estructura EXACTA para JS
            $adicionalesData[$c->id_conductor] = [
                'campos'           => $campos,
                'archivos'         => $archivos,
                'licencia_vencida' => $vencida,
            ];
        }

        // ====================================================
        // 🎯 RESPUESTA EXACTA PARA EL JS
        // ====================================================
        return response()->json([
            'success'    => true,
            'documentos' => [
                'titular'     => $titular,
                'adicionales' => $adicionalesData,
            ],
        ]);

    } catch (\Throwable $e) {

        Log::error("ERROR obtenerDocumentacion: ".$e->getMessage());

        return response()->json([
            'success' => false,
            'msg'     => 'Error interno al obtener documentación'
        ], 500);
    }
}
public function verificarDocumentosExistentes($idContrato)
{
    try {

        $existen = DB::table('contrato_documento')
            ->where('id_contrato', $idContrato)
            ->exists();

        return response()->json([
            'success' => true,
            'existen' => $existen
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'existen' => false,
            'msg' => 'Error verificando documentos'
        ], 500);
    }
}






public function guardarCargoVariable(Request $request)
{
    try {
        $idContrato = $request->id_contrato;
        $idConcepto = $request->id_concepto;

        if (!$idContrato || !$idConcepto) {
            return response()->json([
                'success' => false,
                'msg' => 'Datos incompletos'
            ]);
        }

        // ======================================
        // 🔥 DATOS VARIABLES RECIBIDOS
        // ======================================
        $montoVariable = $request->monto_variable ?? 0;
        $kilometros    = $request->km ?? null;
        $destino       = $request->destino ?? null;
        $litros        = $request->litros ?? null;
        $precioLitro   = $request->precio_litro ?? null;

        // Guardar JSON único (genérico)
        $json = [
            'km'           => $kilometros,
            'destino'      => $destino,
            'litros'       => $litros,
            'precio_litro' => $precioLitro,
            'monto'        => $montoVariable,
        ];

        // Buscar si ya existe
        $existe = DB::table('cargo_adicional')
            ->where('id_contrato', $idContrato)
            ->where('id_concepto', $idConcepto)
            ->first();

        // ======================================
        // 🔥 SI MONTO ES 0 → BORRAR
        // ======================================
        if ($montoVariable == 0) {
            DB::table('cargo_adicional')
                ->where('id_contrato', $idContrato)
                ->where('id_concepto', $idConcepto)
                ->delete();

            return response()->json([
                'success' => true,
                'action'  => 'deleted',
                'msg'     => 'Cargo eliminado'
            ]);
        }

        // ======================================
        // 🔄 SI EXISTE → UPDATE
        // ======================================
        if ($existe) {
            DB::table('cargo_adicional')
                ->where('id_contrato', $idContrato)
                ->where('id_concepto', $idConcepto)
                ->update([
                    'monto'      => $montoVariable,
                    'detalle'      => json_encode($json),
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'action'  => 'updated',
                'msg'     => 'Cargo actualizado'
            ]);
        }

        // ======================================
        // ➕ SI NO EXISTE → INSERT
        // ======================================
        DB::table('cargo_adicional')->insert([
            'id_contrato' => $idContrato,
            'id_concepto' => $idConcepto,
            'concepto'    => DB::table('cargo_concepto')->where('id_concepto', $idConcepto)->value('nombre'),
            'monto'       => $montoVariable,
            'detalle'       => json_encode($json),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'action'  => 'inserted',
            'msg'     => 'Cargo variable guardado'
        ]);

    } catch (\Exception $e) {
        Log::error("ERROR guardarCargoVariable Paso 4: " . $e->getMessage());
        return response()->json([
            'success' => false,
            'msg'     => 'Error en servidor'
        ]);
    }
}



public function actualizarSegurosIndividuales(Request $request)
{
    try {
        $data = $request->validate([
            'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
            'id_seguro'      => 'required|integer|exists:seguro_individuales,id_individual',
            'precio_por_dia' => 'required|numeric|min:0',
        ]);

        $idReservacion = $data['id_reservacion'];
        $idIndividual  = $data['id_seguro'];

        // 🔥 1. Si activa un individual → se elimina cualquier paquete
        DB::table('reservacion_paquete_seguro')
            ->where('id_reservacion', $idReservacion)
            ->delete();

        // 🔥 2. Insertar individual si no existe
        $existe = DB::table('reservacion_seguro_individual')
            ->where('id_reservacion', $idReservacion)
            ->where('id_individual', $idIndividual)
            ->first();

        if (!$existe) {
            DB::table('reservacion_seguro_individual')->insert([
                'id_reservacion' => $idReservacion,
                'id_individual'  => $idIndividual,
                'precio_por_dia' => $data['precio_por_dia'],
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        return response()->json([
            'ok' => true,
            'status' => 'inserted',
            'msg' => 'Protección individual agregada correctamente.'
        ]);

    } catch (\Throwable $e) {
        Log::error("Error en actualizarSegurosIndividuales: " . $e->getMessage());
        return response()->json(['error' => 'Error interno al actualizar.'], 500);
    }
}

public function eliminarSeguroIndividual(Request $request)
{
    try {
        $request->validate([
            'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
            'id_seguro'      => 'required|integer|exists:seguro_individuales,id_individual',
        ]);

        DB::table('reservacion_seguro_individual')
            ->where('id_reservacion', $request->id_reservacion)
            ->where('id_individual', $request->id_seguro)
            ->delete();

        return response()->json([
            'ok' => true,
            'status' => 'deleted',
            'msg' => 'Protección individual eliminada.'
        ]);

    } catch (\Throwable $e) {
        Log::error("Error al eliminar seguro individual: " . $e->getMessage());
        return response()->json(['error' => 'Error interno al eliminar.'], 500);
    }
}

public function eliminarTodosLosIndividuales(Request $request)
{
    try {
        $request->validate([
            'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
        ]);

        DB::table('reservacion_seguro_individual')
            ->where('id_reservacion', $request->id_reservacion)
            ->delete();

        return response()->json([
            'ok' => true,
            'msg' => 'Todos los seguros individuales eliminados.'
        ]);

    } catch (\Throwable $e) {
        Log::error("Error al borrar todos los individuales: ".$e->getMessage());
        return response()->json(['error' => 'Error interno'], 500);
    }
}


private function borrarIndividuales($idReservacion)
{
    DB::table('reservacion_seguro_individual')
        ->where('id_reservacion', $idReservacion)
        ->delete();
}

private function borrarPaquete($idReservacion)
{
    DB::table('reservacion_paquete_seguro')
        ->where('id_reservacion', $idReservacion)
        ->delete();
}

private function obtenerIndividualesSeleccionados($idReservacion)
{
    return DB::table('reservacion_seguro_individual as rsi')
        ->join('seguro_individuales as si', 'rsi.id_individual', '=', 'si.id_individual')
        ->select(
            'si.id_individual',
            'si.nombre',
            'si.descripcion',
            'rsi.precio_por_dia'
        )
        ->where('rsi.id_reservacion', $idReservacion)
        ->get();
}


private function calcularTotalProtecciones($idReservacion)
{
    $dias = DB::table('reservaciones')
    ->selectRaw("DATEDIFF(fecha_fin, fecha_inicio) + 1 as dias")
    ->where('id_reservacion', $idReservacion)
    ->value('dias') ?? 1;


    // 🔥 Paquete
    $paquete = DB::table('reservacion_paquete_seguro')
        ->where('id_reservacion', $idReservacion)
        ->first();

    if ($paquete) {
        return $paquete->precio_por_dia * $dias;
    }

    // 🔥 Individuales
    return DB::table('reservacion_seguro_individual')
        ->where('id_reservacion', $idReservacion)
        ->sum(DB::raw("precio_por_dia * {$dias}"));
}


public function guardarDeliveryReservacion(Request $request)
{
    try {
        $data = $request->validate([
            'id_reservacion'      => 'required|integer|exists:reservaciones,id_reservacion',
            'delivery_activo'     => 'required|boolean',
            'delivery_ubicacion'  => 'nullable|string|max:120',
            'delivery_direccion'  => 'nullable|string|max:255',
            'delivery_km'         => 'nullable|numeric|min:0',
            'delivery_precio_km'  => 'nullable|numeric|min:0',
            'delivery_total'      => 'nullable|numeric|min:0',
        ]);

        // 🔍 Buscar reservación
        $res = DB::table('reservaciones')
            ->where('id_reservacion', $data['id_reservacion'])
            ->first();

        if (!$res) {
            return response()->json(['error' => 'Reservación no encontrada'], 404);
        }

        // 🧹 Si está desactivado → limpiar datos
        if ($data['delivery_activo'] == 0) {

            DB::table('reservaciones')
                ->where('id_reservacion', $data['id_reservacion'])
                ->update([
                    'delivery_activo'     => 0,
                    'delivery_ubicacion'  => null,
                    'delivery_direccion'  => null,
                    'delivery_km'         => 0,
                    'delivery_precio_km'  => 0,
                    'delivery_total'      => 0,
                    'updated_at'          => now(),
                ]);

            return response()->json([
                'status' => 'deleted',
                'msg' => 'Delivery desactivado correctamente'
            ]);
        }

        // 📝 Guardar delivery activo
        DB::table('reservaciones')
            ->where('id_reservacion', $data['id_reservacion'])
            ->update([
                'delivery_activo'     => $data['delivery_activo'],
                'delivery_ubicacion'  => $data['delivery_ubicacion'],
                'delivery_direccion'  => $data['delivery_direccion'],
                'delivery_km'         => $data['delivery_km'],
                'delivery_precio_km'  => $data['delivery_precio_km'],
                'delivery_total'      => $data['delivery_total'],
                'updated_at'          => now(),
            ]);

        return response()->json([
            'status' => 'updated',
            'msg'    => 'Delivery guardado correctamente',
            'total'  => $data['delivery_total']
        ]);

    } catch (\Throwable $e) {
        Log::error("Error en guardarDeliveryReservacion: ".$e->getMessage());
        return response()->json(['error' => 'Error interno'], 500);
    }
}


public function solicitarCambioFecha(Request $request)
{
    try {

        $data = $request->validate([
            'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
            'nueva_fecha'    => 'required|date',
            'nueva_hora'     => 'nullable',
            'motivo'         => 'nullable|string|max:500'
        ]);

        // Reservación original
        $res = DB::table('reservaciones')
            ->where('id_reservacion', $data['id_reservacion'])
            ->first();

        if (!$res) {
            return response()->json(['error' => 'Reservación no encontrada'], 404);
        }

        // Crear token único
        $token = bin2hex(random_bytes(32));

        // Guardar solicitud
        DB::table('contrato_cambio_fecha')->insert([
            'id_reservacion'   => $res->id_reservacion,
            'fecha_anterior'   => $res->fecha_inicio,
            'hora_anterior'    => $res->hora_retiro,
            'fecha_solicitada' => $data['nueva_fecha'],
            'hora_solicitada'  => $data['nueva_hora'],
            'motivo'           => $data['motivo'] ?? null,
            'token'            => $token,
            'estado'           => 'pendiente',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        /* ==========================================================
           📧 Enviar correo al superadministrador
        ========================================================== */

        $superadminEmail = "administrador@viajerocarental.com"; // <-- cámbialo

        $linkAprobar  = url("/admin/contrato/cambio-fecha/aprobar/{$token}");
        $linkRechazar = url("/admin/contrato/cambio-fecha/rechazar/{$token}");

        $html = "
            <div style='font-family:sans-serif;'>
                <h2 style='color:#D6121F;'>Solicitud de cambio de fecha</h2>

                <p><b>Reservación:</b> {$res->codigo}</p>
                <p><b>Cliente:</b> {$res->nombre_cliente}</p>

                <p><b>Fecha actual:</b> {$res->fecha_inicio} {$res->hora_retiro}</p>
                <p><b>Nueva fecha solicitada:</b> {$data['nueva_fecha']} {$data['nueva_hora']}</p>

                <p><b>Motivo:</b> ".($data['motivo'] ?? 'No especificado')."</p>

                <p>Acciones:</p>

                <p>
                    <a href='{$linkAprobar}'
                       style='background:#16a34a;color:#fff;padding:10px 14px;border-radius:6px;text-decoration:none;'>
                        Aprobar cambio
                    </a>
                </p>

                <p>
                    <a href='{$linkRechazar}'
                       style='background:#dc2626;color:#fff;padding:10px 14px;border-radius:6px;text-decoration:none;'>
                        Rechazar solicitud
                    </a>
                </p>
            </div>
        ";

        Mail::html($html, function ($message) use ($superadminEmail) {
            $message->to($superadminEmail)
                    ->subject("Solicitud de cambio de fecha - Viajero Car Rental");
        });

        return response()->json([
            'success' => true,
            'msg'     => 'Solicitud enviada al superadministrador.'
        ]);

    } catch (\Throwable $e) {
        Log::error('Error en solicitarCambioFecha: '.$e->getMessage());
        return response()->json(['error' => 'Error interno.'], 500);
    }
}

public function aprobarCambioFecha($token)
{
    try {
        $sol = DB::table('contrato_cambio_fecha')
            ->where('token', $token)
            ->where('estado', 'pendiente')
            ->first();

        if (!$sol) {
            return "Solicitud inválida o ya procesada.";
        }

        // Actualizar la reservación
        DB::table('reservaciones')
            ->where('id_reservacion', $sol->id_reservacion)
            ->update([
                'fecha_inicio' => $sol->fecha_solicitada,
                'hora_retiro'  => $sol->hora_solicitada,
                'aprobado_por_superadmin' => true,
                'updated_at' => now(),
            ]);

            // 🔄 Recalcular totales con nueva fecha
$res = DB::table('reservaciones')
    ->where('id_reservacion', $sol->id_reservacion)
    ->first();

$this->recalcularYActualizarTotales(
    new Request([
        'fecha_inicio' => $sol->fecha_solicitada,
        'hora_inicio'  => $sol->hora_solicitada,
        'fecha_fin'    => $res->fecha_fin,
        'hora_fin'     => $res->hora_entrega,
        'id_categoria' => $res->id_categoria,
    ]),
    $sol->id_reservacion
);



        // Marcar solicitud como aprobada
        DB::table('contrato_cambio_fecha')
            ->where('id', $sol->id)
            ->update([
                'estado' => 'aprobado',
                'autorizado_por' => 'superadmin',
                'fecha_autorizacion' => now()
            ]);

        return "
            <h2 style='font-family:sans-serif;color:#16a34a'>Cambio aprobado ✔</h2>
            <p>La fecha ha sido actualizada exitosamente.</p>
        ";

    } catch (\Throwable $e) {
        Log::error("Error en aprobarCambioFecha: " . $e->getMessage());
        return "Error interno.";
    }
}

public function rechazarCambioFecha($token)
{
    try {
        $sol = DB::table('contrato_cambio_fecha')
            ->where('token', $token)
            ->where('estado', 'pendiente')
            ->first();

        if (!$sol) {
            return "Solicitud inválida o ya procesada.";
        }

        DB::table('contrato_cambio_fecha')
            ->where('id', $sol->id)
            ->update([
                'estado' => 'rechazado',
                'autorizado_por' => 'superadmin',
                'fecha_autorizacion' => now()
            ]);

        return "
            <h2 style='font-family:sans-serif;color:#dc2626'>Cambio rechazado ❌</h2>
            <p>No se realizaron modificaciones en la reservación.</p>
        ";

    } catch (\Throwable $e) {
        Log::error("Error en rechazarCambioFecha: ".$e->getMessage());
        return "Error interno.";
    }
}

public function estadoCambioFecha($idReservacion)
{
    $registro = DB::table('contrato_cambio_fecha')
        ->where('id_reservacion', $idReservacion)
        ->orderBy('id', 'desc')
        ->first();

    if (!$registro) {
        return response()->json([ "estado" => "sin-solicitud" ]);
    }

    return response()->json([
        "estado" => $registro->estado,
        "fecha_nueva" => $registro->fecha_solicitada
    ]);
}

public function recalcularYActualizarTotales(Request $request, $idReservacion)
{
    try {
        // 1️⃣ Validar datos
        $data = $request->validate([
            'fecha_inicio' => 'required|date',
            'hora_inicio'  => 'nullable',
            'fecha_fin'    => 'required|date',
            'hora_fin'     => 'nullable',
            'id_categoria' => 'required|integer|exists:categorias_carros,id_categoria',
        ]);

        // 2️⃣ Cargar reservación
        $res = DB::table('reservaciones')
            ->where('id_reservacion', $idReservacion)
            ->first();

        if (!$res) {
            return response()->json(['error' => 'Reservación no encontrada'], 404);
        }

        // 3️⃣ Obtener precio real según reglas
        $categoria = DB::table('categorias_carros')
            ->where('id_categoria', $data['id_categoria'])
            ->first();

        if (!$categoria) {
            return response()->json(['error' => 'Categoría no encontrada'], 404);
        }

        // 📌 PRECIO POR DÍA DEFINITIVO
if ($res->tarifa_ajustada == 1 && $res->tarifa_modificada > 0) {
    // ⭐ Tarifa personalizada por el operador
    $precioReal = $res->tarifa_modificada;
} else {
    // ⭐ Precio según la categoría (ideal cuando cambias categoría)
    $precioReal = $categoria->precio_dia;
}

        // 4️⃣ Calcular días (incluye día inicial)
        $dias = Carbon::parse($data['fecha_inicio'])
    ->diffInDays(Carbon::parse($data['fecha_fin']));


        // 5️⃣ Calcular subtotal, impuestos y total
        $subtotal  = $dias * $precioReal;
        $iva       = $subtotal * 0.16;
        $total     = $subtotal + $iva;

        // 6️⃣ Guardar en DB
        DB::table('reservaciones')
            ->where('id_reservacion', $idReservacion)
            ->update([
                'fecha_inicio' => $data['fecha_inicio'],
                'hora_retiro'  => $data['hora_inicio'],
                'fecha_fin'    => $data['fecha_fin'],
                'hora_entrega' => $data['hora_fin'],
                'subtotal'     => $subtotal,
                'impuestos'    => $iva,
                'total'        => $total,
                'id_categoria' => $data['id_categoria'],
                'updated_at'   => now(),
            ]);

        return response()->json([
            'success' => true,
            'dias'    => $dias,
            'precio_dia' => number_format($precioReal, 2),
            'subtotal' => number_format($subtotal, 2),
            'impuestos' => number_format($iva, 2),
            'total'    => number_format($total, 2),
            'total_formateado' => number_format($total, 2),
            'moneda' => $res->moneda,
        ]);

    } catch (\Throwable $e) {
        Log::error("❌ Error en recalcularYActualizarTotales: ".$e->getMessage());
        return response()->json(['error' => 'Error interno.'], 500);
    }
}

/**
 * 🚗 Obtener vehículos disponibles por categoría
 * Usado por el modal del paso 1 del contrato.
 */
public function vehiculosPorCategoria($idCategoria)
{
    try {

        Log::info("🔍 Buscando vehículos para categoría: $idCategoria");

        $vehiculos = DB::table('vehiculos as v')
            ->leftJoin('vehiculo_imagenes as img', function ($join) {
                $join->on('img.id_vehiculo', '=', 'v.id_vehiculo')
                     ->where('img.orden', 0);
            })
            ->leftJoin('mantenimientos as m', 'm.id_vehiculo', '=', 'v.id_vehiculo')
            ->select(
                'v.id_vehiculo',
                'v.nombre_publico',
                'v.marca',
                'v.modelo',
                'v.color',
                'v.transmision',
                'v.asientos',
                'v.puertas',
                'v.numero_serie',
                'v.placa',
                'v.kilometraje',
                'v.gasolina_actual',
                'v.fin_vigencia_poliza',
                'img.url as foto_url',

                // mantenimiento
                'm.kilometraje_actual',
                'm.proximo_servicio'
            )
            ->where('v.id_categoria', $idCategoria)
            ->orderBy('v.marca')
            ->orderBy('v.modelo')
            ->get();

        // Procesar km restantes + color
        $vehiculos->transform(function ($v) {

            // calcular km restantes
            if ($v->proximo_servicio && $v->kilometraje) {
                $v->km_restantes = $v->proximo_servicio - $v->kilometraje;
            } else {
                $v->km_restantes = null;
            }

            // color
            if ($v->km_restantes === null) {
                $v->color_mantenimiento = "gris";
            } elseif ($v->km_restantes > 1200) {
                $v->color_mantenimiento = "verde";
            } elseif ($v->km_restantes > 600) {
                $v->color_mantenimiento = "amarillo";
            } else {
                $v->color_mantenimiento = "rojo";
            }

            return $v;
        });

        return response()->json([
            "success" => true,
            "data" => $vehiculos
        ]);

    } catch (\Throwable $e) {

        Log::error("❌ ERROR vehiculosPorCategoria: " . $e->getMessage());

        return response()->json([
            "success" => false,
            "error" => $e->getMessage()
        ], 500);
    }
}


public function actualizarCategoria(Request $request, $idReservacion)
{
    try {
        $data = $request->validate([
            'id_categoria' => 'required|integer|exists:categorias_carros,id_categoria'
        ]);

        // 1️⃣ Cargar reservación actual
        $res = DB::table('reservaciones')
            ->where('id_reservacion', $idReservacion)
            ->first();

        if (!$res) {
            return response()->json([
                'success' => false,
                'error'   => 'Reservación no encontrada.'
            ], 404);
        }

        // 2️⃣ Cargar categoría nueva para sacar tarifa base real
        $categoria = DB::table('categorias_carros')
            ->where('id_categoria', $data['id_categoria'])
            ->first();

        if (!$categoria) {
            return response()->json([
                'success' => false,
                'error'   => 'Categoría no encontrada.'
            ], 404);
        }

        // 3️⃣ Flags para el frontend
        $vehiculoRemovido = !is_null($res->id_vehiculo);
        $tarifaLimpiada   = ($res->tarifa_ajustada == 1) || (!is_null($res->tarifa_modificada) && $res->tarifa_modificada > 0);

        // 4️⃣ Actualizar reservación según tu flujo C
        DB::table('reservaciones')
            ->where('id_reservacion', $idReservacion)
            ->update([
                // Categoría nueva
                'id_categoria'     => $data['id_categoria'],

                // Siempre quitar vehículo al cambiar categoría (opción C)
                'id_vehiculo'      => null,

                // Reset total de tarifa modificada
                'tarifa_ajustada'  => 0,
                'tarifa_modificada'=> null,

                // Fijar nueva tarifa base del catálogo
                'tarifa_base'      => $categoria->precio_dia,

                'updated_at'       => now(),
            ]);

        return response()->json([
            'success'          => true,
            'msg'              => 'Categoría actualizada correctamente.',
            'vehiculo_removido'=> $vehiculoRemovido,
            'tarifa_limpiada'  => $tarifaLimpiada,
            'tarifa_base_nueva'=> number_format($categoria->precio_dia, 2),
        ]);

    } catch (\Throwable $e) {
        Log::error("Error en actualizarCategoria: ".$e->getMessage(), [
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]);

        return response()->json([
            'success' => false,
            'error'   => 'Error interno al guardar la categoría.'
        ], 500);
    }
}

public function asignarVehiculo(Request $request)
{
    try {
        $data = $request->validate([
            'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
            'id_vehiculo'    => 'required|integer|exists:vehiculos,id_vehiculo',
        ]);

        DB::table('reservaciones')
            ->where('id_reservacion', $data['id_reservacion'])
            ->update([
                'id_vehiculo' => $data['id_vehiculo'],
                'updated_at'  => now(),
            ]);

        return response()->json([
            'success' => true,
            'msg'     => 'Vehículo asignado correctamente.',
        ]);

    } catch (\Throwable $e) {
        Log::error("Error asignando vehículo: " . $e->getMessage());
        return response()->json(['success' => false, 'error' => 'Error interno'], 500);
    }
}

public function obtenerOfertaUpgrade($idReservacion)
{
    try {
        // 1️⃣ Reservación
        $res = DB::table('reservaciones')
            ->where('id_reservacion', $idReservacion)
            ->first();

        if (!$res) {
            return response()->json(['success' => false, 'error' => 'Reservación no encontrada']);
        }

        // 2️⃣ Categoría actual
        $catActual = DB::table('categorias_carros')
            ->where('id_categoria', $res->id_categoria)
            ->first();

        if (!$catActual) {
            return response()->json(['success' => false, 'error' => 'Categoría actual no encontrada']);
        }

        // 🟦 ORDEN OFICIAL (DEBES AJUSTARLO SI CAMBIA)
        $orden = ["C", "D", "E", "F", "IC", "I", "IB", "M", "L", "H", "HI"];

        // 🟩 posición actual
        $posActual = array_search($catActual->codigo, $orden);

        if ($posActual === false) {
            return response()->json(['success' => false, 'msg' => 'Categoría actual no está en el orden oficial.']);
        }

        // 3️⃣ Conseguir TODAS las categorías superiores
        $codigosSuperiores = array_slice($orden, $posActual + 1);

        if (empty($codigosSuperiores)) {
            return response()->json(['success' => false, 'msg' => 'No hay categorías superiores disponibles.']);
        }

        // 4️⃣ Obtener esas categorías desde DB
        $categorias = DB::table('categorias_carros')
            ->whereIn('codigo', $codigosSuperiores)
            ->orderBy('precio_dia', 'asc')
            ->get();

        if ($categorias->isEmpty()) {
            return response()->json(['success' => false, 'msg' => 'No hay categorías superiores en DB.']);
        }

        // 5️⃣ Seleccionar UNA categoría random
        $catSuperior = $categorias->random();

        // 6️⃣ Vehículo random
        $vehiculo = DB::table('vehiculos')
            ->where('id_categoria', $catSuperior->id_categoria)
            ->inRandomOrder()
            ->first();

        if (!$vehiculo) {
            return response()->json(['success' => false, 'msg' => 'No hay vehículos disponibles para upgrade.']);
        }

        // 7️⃣ Imagen del vehículo
        $foto = DB::table('vehiculo_imagenes')
            ->where('id_vehiculo', $vehiculo->id_vehiculo)
            ->orderBy('orden', 'asc')
            ->value('url');

        $fotoFinal = $foto ?? '/img/default-car.jpg';

        // ⭐ PRECIOS
        $precioReal    = $catSuperior->precio_dia;
        $precioInflado = round($precioReal * 1.35, 2);
        $descuento     = rand(55, 75);
        $precioFinal   = round($precioInflado * (1 - ($descuento / 100)), 2);

        // ⭐ RESPUESTA COMPLETA AL FRONT
        return response()->json([
            'success' => true,
            'categoria' => [
                'id_categoria'     => $catSuperior->id_categoria,
                'codigo'           => $catSuperior->codigo,
                'nombre'           => $catSuperior->nombre,
                'descripcion'      => $catSuperior->descripcion,

                'precio_real'      => $precioReal,
                'precio_inflado'   => $precioInflado,
                'descuento'        => $descuento,
                'precio_final'     => $precioFinal,

                'imagen'           => $fotoFinal,
                'nombre_vehiculo'  => $vehiculo->nombre_publico,
                'transmision'      => $vehiculo->transmision,
                'asientos'         => $vehiculo->asientos,
                'puertas'          => $vehiculo->puertas,
                'color'            => $vehiculo->color,
            ],
        ]);

    } catch (\Throwable $e) {
        Log::error("Error obtenerOfertaUpgrade: " . $e->getMessage());
        return response()->json(['success' => false, 'error' => 'Error interno'], 500);
    }
}



public function aceptarUpgrade(Request $request, $idReservacion)
{
    try {
        $data = $request->validate([
            'id_categoria' => 'required|integer|exists:categorias_carros,id_categoria'
        ]);

        // ⚙️ Categoría nueva
        $cat = DB::table('categorias_carros')
            ->where('id_categoria', $data['id_categoria'])
            ->first();

        if (!$cat) {
            return response()->json(['error' => 'Categoría no encontrada'], 404);
        }

        // 🔄 Aplicar upgrade
        DB::table('reservaciones')
            ->where('id_reservacion', $idReservacion)
            ->update([
                'id_categoria'      => $cat->id_categoria,
                'tarifa_base'       => $cat->precio_dia,
                'tarifa_ajustada'   => 0,
                'tarifa_modificada' => null,
                'id_vehiculo'       => null,
                'updated_at'        => now(),
            ]);

        return response()->json([
            'success' => true,
            'msg'     => 'Upgrade aplicado correctamente.',
            'tarifa_base' => number_format($cat->precio_dia, 2)
        ]);

    } catch (\Throwable $e) {
        Log::error("Error aceptarUpgrade: ".$e->getMessage());
        return response()->json(['error' => 'Error interno'], 500);
    }
}

public function rechazarUpgrade($idReservacion)
{
    try {
        DB::table('contrato_evento')->insert([
            'id_contrato'  => DB::table('contratos')->where('id_reservacion', $idReservacion)->value('id_contrato'),
            'evento'       => 'Upgrade rechazado',
            'detalle'      => json_encode([]),
            'realizado_en' => now(),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return response()->json([
            'success' => true,
            'msg'     => 'Oferta rechazada.'
        ]);

    } catch (\Throwable $e) {
        Log::error("Error rechazarUpgrade: ".$e->getMessage());
        return response()->json(['error' => 'Error interno'], 500);
    }
}

public function categoriaInfo($codigo)
{
    try {
        $cat = DB::table('categorias_carros')
            ->where('codigo', $codigo)
            ->first();

        if (!$cat) {
            return response()->json([
                'success' => false,
                'error'   => 'Categoría no encontrada'
            ], 404);
        }

        return response()->json([
            'success'   => true,
            'categoria' => $cat
        ]);

    } catch (\Throwable $e) {
        Log::error("Error categoriaInfo: ".$e->getMessage());
        return response()->json([
            'success' => false,
            'error'   => 'Error interno'
        ], 500);
    }
}

public function vehiculoRandom($idCategoria)
{
    try {
        // Buscar vehículos disponibles de esa categoría
        $vehiculos = DB::table('vehiculos')
            ->leftJoin('vehiculo_imagenes', 'vehiculos.id_vehiculo', '=', 'vehiculo_imagenes.id_vehiculo')
            ->where('vehiculos.id_categoria', $idCategoria)
            ->select(
                'vehiculos.id_vehiculo',
                'vehiculos.nombre_publico',
                'vehiculos.transmision',
                'vehiculos.asientos',
                'vehiculos.puertas',
                'vehiculos.color',
                'vehiculo_imagenes.url AS foto_url'
            )
            ->inRandomOrder()
            ->first();

        if (!$vehiculos) {
            return response()->json([
                'success' => false,
                'error'   => 'No hay vehículos disponibles para esta categoría'
            ]);
        }

        return response()->json([
            'success'  => true,
            'vehiculo' => $vehiculos
        ]);

    } catch (\Throwable $e) {
        Log::error("Error vehiculoRandom: ".$e->getMessage());

        return response()->json([
            'success' => false,
            'error'   => 'Error interno'
        ], 500);
    }
}

public function registrarPago(Request $request)
{
    try {
        $data = $request->validate([
            'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
            'id_contrato'    => 'required|integer|exists:contratos,id_contrato',
            'metodo'         => 'required|string|max:50',
            'tipo_pago'      => 'required|string|max:50',
            'monto'          => 'required|numeric|min:0.01',
            'notas'          => 'nullable|string',
            'extra_datos'    => 'nullable|array'
        ]);

        // Insertar pago
        DB::table('pagos')->insert([
            'id_reservacion'      => $data['id_reservacion'],
            'id_contrato'         => $data['id_contrato'],
            'metodo'              => $data['metodo'],
            'tipo_pago'           => $data['tipo_pago'],
            'monto'               => $data['monto'],
            'estatus'             => 'paid',
            'moneda'              => 'MXN',
            'payload_webhook'     => json_encode($data['extra_datos'] ?? null),
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        return response()->json([
            'success' => true,
            'msg'     => 'Pago registrado correctamente'
        ]);

    } catch (\Throwable $e) {
        Log::error("ERROR registrarPago: ".$e->getMessage());
        return response()->json(['error' => 'Error interno al registrar pago'], 500);
    }
}

public function resumenPaso6($idReservacion)
{
    try {
        // ===============================
        // 1) Reservación
        // ===============================
        $res = DB::table('reservaciones')
            ->where('id_reservacion', $idReservacion)
            ->first();

        if (!$res) {
            return response()->json([
                'ok' => false,
                'msg' => 'Reservación no encontrada'
            ]);
        }

        // ===============================
        // 2) Calcular días (correcto con +1)
        // ===============================
        $dias = Carbon::parse($res->fecha_inicio)
            ->diffInDays(Carbon::parse($res->fecha_fin));

        // ===============================
        // 3) Tarifa correcta
        //    PRIORIDAD: tarifa_modificada → tarifa_base
        // ===============================
        $tarifa = $res->tarifa_modificada !== null
            ? $res->tarifa_modificada
            : $res->tarifa_base;

        $baseTotal = $tarifa * $dias;

        // ===============================
        // 4) Servicios adicionales
        // ===============================
        $adds = DB::table('reservacion_servicio')
            ->where('id_reservacion', $idReservacion)
            ->selectRaw("SUM(cantidad * precio_unitario * $dias) as total")
            ->first()->total ?? 0;

        // ===============================
        // 5) Delivery
        // ===============================
        $delivery = $res->delivery_total ?? 0;

        // ===============================
        // 6) Seguros
        // ===============================
        $precioSeguros = $this->calcularTotalProtecciones($idReservacion);

        // ===============================
        // 7) Cargos adicionales del contrato
        // ===============================
        $contrato = DB::table('contratos')
            ->where('id_reservacion', $idReservacion)
            ->first();

        $cargos = 0;

        if ($contrato) {
            $cargos = DB::table('cargo_adicional')
                ->where('id_contrato', $contrato->id_contrato)
                ->sum('monto');
        }

        // ===============================
        // 8) Subtotal, IVA, total
        // ===============================
        $subtotal = $baseTotal + $adds + $delivery + $precioSeguros + $cargos;
        $iva = $subtotal * 0.16;
        $totalContrato = $subtotal + $iva;

        // ===============================
        // 9.1 Guardar totales reales en DB
        // ===============================
        DB::table('reservaciones')
            ->where('id_reservacion', $idReservacion)
            ->update([
                'subtotal' => $subtotal,
                'impuestos' => $iva,
                'total' => $totalContrato,
                'updated_at' => now(),
            ]);

        // ===============================
        // 9) Pagos
        // ===============================
        $pagos = DB::table('pagos')
            ->where('id_reservacion', $idReservacion)
            ->orderBy('created_at')
            ->get()
            ->map(function ($p) {
                return [
                    'id_pago' => $p->id_pago,
                    'fecha'   => Carbon::parse($p->created_at)->format('Y-m-d H:i'),
                    'tipo'    => $p->tipo_pago,
                    'origen'  => $p->metodo,
                    'monto'   => $p->monto,
                ];
            });

        $totalPagado = $pagos->sum('monto');
        $saldoPendiente = $totalContrato - $totalPagado;

        // ===============================
        // 🔥 10) Respuesta final (JSON)
        // Incluye tarifas para que JS pueda actualizar correctamente
        // ===============================
        return response()->json([
            'ok' => true,
            'data' => [
                'base' => [
                    'dias' => $dias,
                    'tarifa' => $tarifa,
                    'tarifa_base' => $res->tarifa_base,
                    'tarifa_modificada' => $res->tarifa_modificada,
                    'descripcion' => "{$dias} días · {$tarifa} por día",
                    'total' => $baseTotal
                ],
                'adicionales' => [
                    'servicios' => $adds,
                    'delivery' => $delivery,
                    'seguros' => $precioSeguros,
                    'cargos' => $cargos,
                    'total' => $adds + $delivery + $precioSeguros + $cargos
                ],
                'totales' => [
                    'subtotal'        => $subtotal,
                    'iva'             => $iva,
                    'total_contrato'  => $totalContrato,
                    'saldo_pendiente' => $saldoPendiente,
                ],
                'pagos' => $pagos,
            ]
        ]);

    } catch (\Throwable $e) {
        Log::error("Error resumenPaso6: ".$e->getMessage());
        return response()->json(['ok' => false, 'msg' => 'Error interno'], 500);
    }
}


public function agregarPagoPaso6(Request $request)
{
    try {
        $data = $request->validate([
            'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
            'tipo_pago'      => 'required|string|max:50',
            'monto'          => 'required|numeric|min:0.01',
            'ultimos4'       => 'nullable|string|max:10',
            'auth'           => 'nullable|string|max:50',
            'notas'          => 'nullable|string|max:500',
            'metodo'         => 'nullable|string|max:50',
        ]);

        DB::table('pagos')->insert([
            'id_reservacion' => $data['id_reservacion'],
            'metodo'         => $data['metodo'] ?? 'mostrador',
            'tipo_pago'      => $data['tipo_pago'],
            'monto'          => $data['monto'],
            'estatus'        => 'paid',
            'payload_webhook'=> json_encode([
                'ultimos4' => $data['ultimos4'] ?? null,
                'auth'     => $data['auth'] ?? null,
                'notas'    => $data['notas'] ?? null,
            ]),
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return response()->json(['ok' => true]);

    } catch (\Throwable $e) {
        Log::error("Error agregarPagoPaso6: ".$e->getMessage());
        return response()->json(['ok' => false, 'msg' => 'Error interno'], 500);
    }
}
public function eliminarPago($idPago)
{
    try {
        DB::table('pagos')->where('id_pago', $idPago)->delete();
        return response()->json(['ok' => true]);
    } catch (\Throwable $e) {
        Log::error("Error eliminarPago: ".$e->getMessage());
        return response()->json(['ok' => false], 500);
    }
}
public function pagoPayPal(Request $req)
{
    $req->validate([
        'id_reservacion' => 'required|integer',
        'order_id'       => 'required|string',
        'monto'          => 'required|numeric|min:1',
    ]);

    DB::beginTransaction();

    try {
        $res = DB::table('reservaciones')
            ->where('id_reservacion', $req->id_reservacion)
            ->first();

        if (!$res) {
            return response()->json(['ok' => false, 'msg' => 'Reservación no encontrada'], 404);
        }

        // Crear el registro de pago
        $idPago = DB::table('pagos')->insertGetId([
            'id_reservacion'       => $req->id_reservacion,
            'id_contrato'          => null,

            'origen_pago'          => 'online',
            'pasarela'             => 'paypal',
            'referencia_pasarela'  => $req->order_id,

            'estatus'              => 'paid',
            'metodo'               => 'PayPal',
            'tipo_pago'            => 'PAGO RESERVACIÓN',

            'monto'                => $req->monto,
            'moneda'               => 'MXN',

            'payload_webhook'      => null,
            'captured_at'          => now(),

            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        // Actualizar reservación
        DB::table('reservaciones')
            ->where('id_reservacion', $req->id_reservacion)
            ->update([
                'paypal_order_id' => $req->order_id,
                'status_pago'     => 'Pagado',
                'metodo_pago'     => 'en línea',
                'estado'          => 'confirmada',
                'updated_at'      => now(),
            ]);

        DB::commit();

        return response()->json([
            'ok' => true,
            'msg' => 'Pago registrado',
            'id_pago' => $idPago
        ]);

    } catch (\Throwable $th) {
        DB::rollBack();
        return response()->json(['ok' => false, 'msg' => $th->getMessage()]);
    }
}
public function pagoManual(Request $req)
{
    $req->validate([
        'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
        'tipo_pago'      => 'required|string|max:50',
        'metodo'         => 'required|string|max:50',
        'monto'          => 'required|numeric|min:1',
        'notas'          => 'nullable|string|max:500',
        'comprobante'    => 'nullable|file|mimes:jpg,jpeg,png,pdf',
    ]);

    DB::beginTransaction();

    try {
        // ---------------------------------------------------
        // 1) Determinar ORIGEN DEL PAGO según el método
        // ---------------------------------------------------
        $origen = match (strtoupper($req->metodo)) {
            'EFECTIVO'         => 'mostrador',
            'TRANSFERENCIA',
            'SPEI',
            'DEPOSITO'         => 'mostrador',
            'VISA',
            'MASTERCARD',
            'AMEX',
            'DEBITO'           => 'terminal',
            default            => 'mostrador',
        };

        // ---------------------------------------------------
        // 2) Subir comprobante SI existe
        // ---------------------------------------------------
        $filePath = null;

        if ($req->hasFile('comprobante')) {
            $filePath = $req->file('comprobante')->store('pagos', 'public');
        }

        // ---------------------------------------------------
        // 3) Insertar el pago manual
        // ---------------------------------------------------
        $idPago = DB::table('pagos')->insertGetId([
            'id_reservacion' => $req->id_reservacion,
            'id_contrato'    => null,

            'origen_pago' => $origen,
            'metodo'      => strtoupper($req->metodo),
            'tipo_pago'   => strtoupper($req->tipo_pago),

            'monto'       => $req->monto,
            'moneda'      => 'MXN',
            'estatus'     => 'paid',

            'comprobante' => $filePath,
            'pasarela'    => null,
            'referencia_pasarela' => null,

            'payload_webhook' => json_encode([
                'notas' => $req->notas,
            ]),

            'captured_at' => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // ---------------------------------------------------
        // 4) Verificar si queda saldo pendiente y actualizar reservación
        // ---------------------------------------------------
        $res = DB::table('reservaciones')->where('id_reservacion', $req->id_reservacion)->first();

        $pagado = DB::table('pagos')
            ->where('id_reservacion', $req->id_reservacion)
            ->where('estatus', 'paid')
            ->sum('monto');

        $saldo = $res->total - $pagado;

        if ($saldo <= 0) {
            DB::table('reservaciones')
                ->where('id_reservacion', $req->id_reservacion)
                ->update([
                    'status_pago' => 'Pagado',
                    'metodo_pago' => $origen,
                    'estado'      => 'confirmada',
                    'updated_at'  => now(),
                ]);
        }

        DB::commit();

        return response()->json([
            'ok' => true,
            'id_pago' => $idPago,
        ]);

    } catch (\Throwable $th) {
        DB::rollBack();
        Log::error("Error pagoManual: ".$th->getMessage());

        return response()->json([
            'ok' => false,
            'msg' => 'Error interno al registrar el pago',
        ], 500);
    }
}


public function pagoEfectivo(Request $req)
{
    $req->validate([
        'id_reservacion' => 'required|integer',
        'tipo_pago'      => 'required|string',
        'monto'          => 'required|numeric|min:1',
        'notas'          => 'nullable|string|max:500',
    ]);

    $idPago = DB::table('pagos')->insertGetId([
        'id_reservacion' => $req->id_reservacion,

        'origen_pago' => 'mostrador',
        'metodo'      => 'EFECTIVO',
        'tipo_pago'   => $req->tipo_pago,
        'monto'       => $req->monto,
        'moneda'      => 'MXN',

        'estatus'     => 'paid',
        'pasarela'    => null,
        'referencia_pasarela' => null,

        'payload_webhook' => json_encode([
            'notas' => $req->notas,
        ]),

        'captured_at' => now(),
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);

    return response()->json(['ok' => true, 'id_pago' => $idPago]);
}

public function pagoTerminal(Request $req)
{
    $req->validate([
        'id_reservacion' => 'required|integer',
        'tipo_pago'      => 'required|string',
        'metodo'         => 'required|string', // VISA, MASTERCARD, AMEX, DEBITO
        'monto'          => 'required|numeric|min:1',
        'comprobante'    => 'required|file|mimes:jpg,jpeg,png,pdf',
    ]);

    // Guardar ticket
    $filePath = $req->file('comprobante')->store('pagos', 'public');

    $idPago = DB::table('pagos')->insertGetId([
        'id_reservacion' => $req->id_reservacion,

        'origen_pago' => 'terminal',
        'metodo'      => $req->metodo,
        'tipo_pago'   => $req->tipo_pago,

        'monto'       => $req->monto,
        'moneda'      => 'MXN',

        'estatus'     => 'paid',
        'comprobante' => $filePath,

        'pasarela'    => null,
        'referencia_pasarela' => null,

        'payload_webhook' => null,
        'captured_at'     => now(),

        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json(['ok' => true, 'id_pago' => $idPago]);
}
public function pagoTransferencia(Request $req)
{
    $req->validate([
        'id_reservacion' => 'required|integer',
        'tipo_pago'      => 'required|string',
        'metodo'         => 'required|string', // TRANSFERENCIA / SPEI / DEPOSITO
        'monto'          => 'required|numeric|min:1',
        'comprobante'    => 'required|file|mimes:jpg,jpeg,png,pdf',
        'notas'          => 'nullable|string|max:500',
    ]);

    $filePath = $req->file('comprobante')->store('pagos', 'public');

    $idPago = DB::table('pagos')->insertGetId([
        'id_reservacion' => $req->id_reservacion,

        'origen_pago' => 'mostrador',
        'metodo'      => $req->metodo,
        'tipo_pago'   => $req->tipo_pago,

        'monto'       => $req->monto,
        'moneda'      => 'MXN',

        'estatus'     => 'paid',
        'comprobante' => $filePath,

        'pasarela'    => null,
        'referencia_pasarela' => null,

        'payload_webhook' => json_encode([
            'notas' => $req->notas,
        ]),

        'captured_at' => now(),

        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json(['ok' => true, 'id_pago' => $idPago]);
}

private function calcularSaldoPendiente($id)
{
    $res = DB::table('reservaciones')->where('id_reservacion', $id)->first();
    if (!$res) return 0;

    $pagos = DB::table('pagos')
        ->where('id_reservacion', $id)
        ->where('estatus', 'paid')
        ->sum('monto');

    return ($res->total - $pagos);
}

public function resumenContrato($idReservacion)
{
    try {

        // 🔹 UNIR CON VEHÍCULOS
        $res = DB::table('reservaciones')
            ->leftJoin('vehiculos as v', 'reservaciones.id_vehiculo', '=', 'v.id_vehiculo')
            ->where('reservaciones.id_reservacion', $idReservacion)
            ->select(
                'reservaciones.codigo',
                'reservaciones.nombre_cliente',
                'reservaciones.telefono_cliente',
                'reservaciones.email_cliente',

                // VEHÍCULO
                'v.marca as veh_marca',
                'v.modelo as veh_modelo',
                'v.transmision as veh_transmision',
                'v.asientos as veh_asientos',
                'v.puertas as veh_puertas',
                'v.kilometraje as veh_km',
                'v.categoria as veh_categoria',

                // FECHAS Y HORARIOS
                'reservaciones.fecha_inicio',
                'reservaciones.hora_retiro',
                'reservaciones.fecha_fin',
                'reservaciones.hora_entrega',
                // TOTALES
                // TOTALES
'reservaciones.subtotal',
'reservaciones.impuestos',
'reservaciones.total',
'reservaciones.tarifa_base',
'reservaciones.tarifa_modificada'

            )
            ->first();

        if (!$res) {
            return response()->json([
                'success' => false,
                'msg' => 'Reservación no encontrada'
            ]);
        }
$dias = \Carbon\Carbon::parse($res->fecha_inicio)
        ->diffInDays(\Carbon\Carbon::parse($res->fecha_fin));



        /* ============================================================
            🛡️ SEGUROS (PAQUETE O INDIVIDUALES)
        ============================================================ */

        // 1) Buscar paquete
        $paquete = DB::table('reservacion_paquete_seguro as rps')
            ->join('seguro_paquete as sp', 'rps.id_paquete', '=', 'sp.id_paquete')
            ->where('rps.id_reservacion', $idReservacion)
            ->select('sp.nombre', 'sp.descripcion', 'rps.precio_por_dia')
            ->first();

        // 2) Buscar individuales
        $individuales = DB::table('reservacion_seguro_individual as ri')
            ->join('seguro_individuales as si', 'ri.id_individual', '=', 'si.id_individual')
            ->where('ri.id_reservacion', $idReservacion)
            ->select('si.nombre', 'si.descripcion', 'ri.precio_por_dia')
            ->get();

        // Resultado armado
        $seguros = [
            'tipo' => null,
            'lista' => [],
            'total' => 0
        ];

        if ($paquete) {
            $seguros['tipo'] = 'paquete';
            $seguros['lista'][] = [
                'nombre' => $paquete->nombre,
                'descripcion' => $paquete->descripcion,
                'precio' => $paquete->precio_por_dia
            ];
            $seguros['total'] = $paquete->precio_por_dia * $dias;


        } elseif ($individuales->count() > 0) {

            $seguros['tipo'] = 'individuales';

            foreach ($individuales as $ind) {
                $seguros['lista'][] = [
                    'nombre' => $ind->nombre,
                    'descripcion' => $ind->descripcion,
                    'precio' => $ind->precio_por_dia
                ];
                $seguros['total'] += $ind->precio_por_dia * $dias;

            }
        }

        /* ============================================================
            🎒 SERVICIOS ADICIONALES (Reservación → Servicios)
        ============================================================ */

        $servicios = DB::table('reservacion_servicio as rs')
            ->join('servicios as s', 'rs.id_servicio', '=', 's.id_servicio')
            ->where('rs.id_reservacion', $idReservacion)
            ->select('s.nombre', 'rs.cantidad', 'rs.precio_unitario')
            ->get();

        $listaServicios = [];
        foreach ($servicios as $srv) {
            $listaServicios[] = [
                'nombre'  => $srv->nombre,
                'cantidad'=> $srv->cantidad,
                'precio'  => $srv->precio_unitario,
                'total'   => ($srv->cantidad * $srv->precio_unitario) * $dias
            ];
        }

        /* ============================================================
            🚗 CARGOS (Delivery / Dropoff / etc.)
        ============================================================ */

        $reg = DB::table('reservaciones')
            ->where('id_reservacion', $idReservacion)
            ->first();

        $cargos = [];

        // DELIVERY
        if ($reg && $reg->delivery_activo) {
            $cargos[] = [
                'nombre' => 'Entrega a domicilio',
                'km'     => $reg->delivery_km,
                'total'  => $reg->delivery_total
            ];
        }

        /* ============================================================
   🛠️ CARGOS ADICIONALES (GASOLINA, DROPOFF, PENALIZACIONES)
============================================================ */

$cargosAdic = DB::table('cargo_adicional')
    ->where('id_contrato', $idReservacion) // si manejas id_contrato separado, te lo ajusto
    ->select(
        'concepto',
        'monto',
        'detalle'
    )
    ->get();

$cargos = [];

foreach ($cargosAdic as $c) {

    $detalle = $c->detalle ? json_decode($c->detalle, true) : null;

    $cargos[] = [
        'nombre' => $c->concepto,
        'total'  => $c->monto,
        'km'     => $detalle['km'] ?? null,
        'litros' => $detalle['litros'] ?? null,
        'nota'   => $detalle['nota'] ?? null
    ];
}
/* ============================================================
   💵 TOTALES DESGLOSADOS (de la reservación)
============================================================ */
$totales = [
    'tarifa_base' => $res->tarifa_base ?? 0,
    'subtotal'    => $res->subtotal ?? 0,
    'iva'         => $res->impuestos ?? 0,
    'total'       => $res->total ?? 0
];

/* ============================================================
   💳 PAGOS REALIZADOS
============================================================ */
$pagos = DB::table('pagos')
    ->where('id_reservacion', $idReservacion)
    ->where('estatus', 'paid')
    ->sum('monto');

$saldoPendiente = max(0, ($res->total ?? 0) - $pagos);



        // (Opcional) GASOLINA – solo si más adelante agregas ese campo
        /*
        if ($reg->gasolina_faltante) {
            $cargos[] = [
                'nombre' => 'Gasolina faltante',
                'total'  => $reg->gasolina_total
            ];
        }
        */

        /* ============================================================
            📤 RETURN JSON FINAL
        ============================================================ */

        return response()->json([
            'success' => true,
            'data' => [

                // DATOS BÁSICOS
                'codigo' => $res->codigo,

                'cliente' => [
                    'nombre'   => $res->nombre_cliente,
                    'telefono' => $res->telefono_cliente,
                    'email'    => $res->email_cliente,
                ],

                // VEHÍCULO
                'vehiculo' => [
                    'marca'       => $res->veh_marca,
                    'modelo'      => $res->veh_modelo,
                    'categoria'   => $res->veh_categoria,
                    'transmision' => $res->veh_transmision,
                    'pasajeros'   => $res->veh_asientos,
                    'puertas'     => $res->veh_puertas,
                    'km'          => $res->veh_km,
                ],

                // FECHAS + HORARIOS
                'fechas' => [
                    'inicio'      => $res->fecha_inicio,
                    'hora_inicio' => $res->hora_retiro,
                    'fin'         => $res->fecha_fin,
                    'hora_fin'    => $res->hora_entrega,
                    'dias'        => $dias,
                ],

                // SEGUROS
                'seguros'   => $seguros,

                // SERVICIOS ADICIONALES
                'servicios' => $listaServicios,

                // CARGOS
                'cargos'    => $cargos,
                'totales' => $totales,
                'pagos'   => [
                    'realizados' => $pagos,
                    'saldo'      => $saldoPendiente
]

            ]
        ]);

    } catch (\Throwable $e) {

        Log::error("ERROR resumenContrato: " . $e->getMessage());

        return response()->json([
            'success' => false,
            'msg' => 'Error interno'
        ], 500);
    }
}

public function editarTarifa(Request $request, $idReservacion)
{
    try {
        $nuevoValor = $request->tarifa_modificada;

        if (!$nuevoValor || $nuevoValor <= 0) {
            return response()->json([
                'ok' => false,
                'msg' => 'Tarifa inválida'
            ]);
        }

        DB::table('reservaciones')
            ->where('id_reservacion', $idReservacion)
            ->update([
                'tarifa_modificada' => $nuevoValor
            ]);

        return response()->json(['ok' => true]);

    } catch (\Exception $e) {
        return response()->json([
            'ok' => false,
            'msg' => 'Error al actualizar tarifa',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function editarCortesia(Request $request, $idReservacion)
{
    try {
        $horas = (int) $request->cortesias;

        if (!in_array($horas, [1, 2, 3])) {
            return response()->json([
                'ok' => false,
                'msg' => 'Las horas de cortesía deben ser 1, 2 o 3'
            ]);
        }

        DB::table('reservaciones')
            ->where('id_reservacion', $idReservacion)
            ->update([
                'horas_cortesia' => $horas
            ]);

        return response()->json(['ok' => true]);

    } catch (\Exception $e) {
        return response()->json([
            'ok' => false,
            'msg' => 'Error al guardar horas de cortesía',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function finalizar($idReservacion)
{
    try {
        // 1) Validar reservación
        $reservacion = DB::table('reservaciones')
            ->where('id_reservacion', $idReservacion)
            ->first();

        if (!$reservacion) {
            return redirect()->back()->with('error', 'Reservación no encontrada.');
        }

        // 2) Verificar si ya existe contrato
        $contratoExistente = DB::table('contratos')
            ->where('id_reservacion', $idReservacion)
            ->first();

        if ($contratoExistente) {
            return redirect()->route('contrato.final', $contratoExistente->id_contrato);
        }

        // 3) Generar número único de contrato
        $numeroContrato = 'CTR-' . strtoupper(bin2hex(random_bytes(4)));

        // 4) Crear contrato
        $idContrato = DB::table('contratos')->insertGetId([
            'id_reservacion'  => $idReservacion,
            'id_asesor'       => session('id_usuario') ?? null,
            'numero_contrato' => $numeroContrato,
            'estado'          => 'abierto',
            'abierto_en'      => now(),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        // 5) Redirigir al contrato final
        return redirect()->route('contrato.final', $idContrato);

    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Error al finalizar contrato: ' . $e->getMessage());
    }
}
public function status($idReservacion)
{
    $existe = DB::table('contratos')
        ->where('id_reservacion', $idReservacion)
        ->exists();

    return response()->json(['existe' => $existe]);
}



}


