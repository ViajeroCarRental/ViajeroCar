<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

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
                'r.id_vehiculo'
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

        // 🔹 3. Cargos adicionales disponibles
        $cargos_conceptos = DB::table('cargo_concepto')
            ->where('activo', true)
            ->get();

        // 🔹 4. Vehículo
        $vehiculo = DB::table('vehiculos')
            ->where('id_vehiculo', $reservacion->id_vehiculo)
            ->first();

        // 🔹 5. Seguros
        $seguros = DB::table('seguro_paquete')
            ->where('activo', true)
            ->select('id_paquete as id_seguro', 'nombre', 'descripcion as cobertura', 'precio_por_dia')
            ->get();

        // 🔹 6. Seguro seleccionado
        $seguroSeleccionado = DB::table('reservacion_paquete_seguro as rps')
            ->join('seguro_paquete as sp', 'rps.id_paquete', '=', 'sp.id_paquete')
            ->select('sp.id_paquete as id_seguro', 'sp.nombre', 'sp.precio_por_dia')
            ->where('rps.id_reservacion', $reservacion->id_reservacion)
            ->first();

        // 🔹 7. Servicios adicionales
        $servicios = DB::table('servicios')->get();

        // 🔹 8. Detectar conductores adicionales desde reservacion_servicio
        // (por ejemplo, el servicio con nombre “Conductor adicional”)
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

        // 🔹 9. Retornar vista
        return view('Admin.Contrato', [
            'reservacion'        => $reservacion,
            'vehiculo'           => $vehiculo,
            'seguros'            => $seguros,
            'servicios'          => $servicios,
            'seguroSeleccionado' => $seguroSeleccionado,
            'contrato'           => $contrato,
            'cargos_conceptos'   => $cargos_conceptos,
            'conductoresExtras'  => $conductoresExtras, // ✅ Se manda a la vista
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

            $existe = DB::table('reservacion_paquete_seguro')
                ->where('id_reservacion', $data['id_reservacion'])
                ->first();

            if ($data['precio_por_dia'] == 0) {
                if ($existe) {
                    DB::table('reservacion_paquete_seguro')
                        ->where('id', $existe->id)
                        ->delete();
                    return response()->json(['status' => 'deleted', 'msg' => 'Seguro eliminado correctamente.']);
                }
                return response()->json(['status' => 'noop', 'msg' => 'No existía seguro para eliminar.']);
            }

            if ($existe) {
                DB::table('reservacion_paquete_seguro')
                    ->where('id', $existe->id)
                    ->update([
                        'id_paquete'      => $data['id_paquete'],
                        'precio_por_dia'  => $data['precio_por_dia'],
                        'updated_at'      => now(),
                    ]);
                return response()->json(['status' => 'updated', 'msg' => 'Seguro actualizado correctamente.']);
            }

            DB::table('reservacion_paquete_seguro')->insert([
                'id_reservacion'  => $data['id_reservacion'],
                'id_paquete'      => $data['id_paquete'],
                'precio_por_dia'  => $data['precio_por_dia'],
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            return response()->json(['status' => 'inserted', 'msg' => 'Seguro agregado correctamente.']);
        } catch (\Throwable $e) {
            Log::error('Error en ContratoController@actualizarSeguro: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno al actualizar el seguro.'], 500);
        }
    }

    /**
     * 💰 Activa o desactiva cargos adicionales (toggle ON/OFF).
     */
    public function actualizarCargos(Request $request)
    {
        try {
            $data = $request->validate([
                'id_contrato'  => 'required|integer|exists:contratos,id_contrato',
                'id_concepto'  => 'required|integer|exists:cargo_concepto,id_concepto',
            ]);

            $concepto = DB::table('cargo_concepto')
                ->where('id_concepto', $data['id_concepto'])
                ->first();

            if (!$concepto) {
                return response()->json(['error' => 'Concepto no encontrado.'], 404);
            }

            $existe = DB::table('cargo_adicional')
                ->where('id_contrato', $data['id_contrato'])
                ->where('id_concepto', $data['id_concepto'])
                ->first();

            if ($existe) {
                DB::table('cargo_adicional')
                    ->where('id_cargo', $existe->id_cargo)
                    ->delete();

                return response()->json(['status' => 'deleted', 'msg' => 'Cargo eliminado correctamente.']);
            }

            DB::table('cargo_adicional')->insert([
                'id_contrato'  => $data['id_contrato'],
                'id_concepto'  => $data['id_concepto'],
                'concepto'     => $concepto->nombre,
                'monto'        => $concepto->monto_base ?? 0,
                'moneda'       => $concepto->moneda ?? 'MXN',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            return response()->json(['status' => 'inserted', 'msg' => 'Cargo agregado correctamente.']);
        } catch (\Throwable $e) {
            Log::error('Error en ContratoController@actualizarCargos: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno al actualizar cargos.'], 500);
        }
    }
    /**
 * 📄 Guarda documentación de identificación y licencia
 */
public function guardarDocumentacion(Request $request)
{
    try {
        // ✅ 1. Validar campos básicos
        $data = $request->validate([
            'id_contrato'   => 'required|integer|exists:contratos,id_contrato',
            'tipo_identificacion' => 'nullable|string|max:50',
            'numero_identificacion' => 'nullable|string|max:50',
            'nombre'        => 'nullable|string|max:100',
            'apellido_paterno' => 'nullable|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'fecha_emision_licencia' => 'nullable|date',
            'fecha_vencimiento_licencia' => 'nullable|date',
            'fecha_vencimiento_id' => 'nullable|date',
            'emite_licencia' => 'nullable|string|max:100',
            'pais_emision' => 'nullable|string|max:100',
            'numero_licencia' => 'nullable|string|max:80',
            'id_conductor' => 'nullable|integer|exists:contrato_conductor_adicional,id_conductor',
            'idFrente' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'idReverso' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'licFrente' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            'licReverso' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        $idContrato = $data['id_contrato'];
        $idConductor = $data['id_conductor'] ?? null;

        // ✅ 2. Guardar archivos (frente y reverso)
        $guardarArchivo = function ($file) {
            if (!$file) return null;
            $path = $file->store('public/documentos');
            return DB::table('archivos')->insertGetId([
                'nombre_original' => $file->getClientOriginalName(),
                'ruta' => $path,
                'tipo' => 'imagen',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        };

        $idArchivoFrente = $guardarArchivo($request->file('idFrente'));
        $idArchivoReverso = $guardarArchivo($request->file('idReverso'));
        $idLicFrente = $guardarArchivo($request->file('licFrente'));
        $idLicReverso = $guardarArchivo($request->file('licReverso'));

        // ✅ 3. Si el conductor adicional no existe, crear registro automático
        if (empty($idConductor) && !empty($data['nombre'])) {
            $idConductor = DB::table('contrato_conductor_adicional')->insertGetId([
                'id_contrato'       => $idContrato,
                'nombres'           => $data['nombre'],
                'apellidos'         => trim(($data['apellido_paterno'] ?? '') . ' ' . ($data['apellido_materno'] ?? '')),
                'numero_licencia'   => $data['numero_licencia'] ?? null,
                'pais_licencia'     => $data['pais_emision'] ?? null,
                'fecha_nacimiento'  => $data['fecha_nacimiento'] ?? null,
                'contacto'          => $data['telefono_cliente'] ?? null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // Registrar evento en bitácora
            DB::table('contrato_evento')->insert([
                'id_contrato'  => $idContrato,
                'evento'       => 'Conductor adicional registrado automáticamente',
                'detalle'      => json_encode([
                    'nombre' => $data['nombre'],
                    'licencia' => $data['numero_licencia'] ?? 'N/A'
                ]),
                'realizado_en' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // ✅ 4. Insertar documento de IDENTIFICACIÓN
        DB::table('contrato_documento')->insert([
            'id_contrato' => $idContrato,
            'id_conductor' => $idConductor,
            'tipo' => 'identificacion',
            'tipo_identificacion' => $data['tipo_identificacion'] ?? null,
            'numero_identificacion' => $data['numero_identificacion'] ?? null,
            'nombre' => $data['nombre'] ?? null,
            'apellido_paterno' => $data['apellido_paterno'] ?? null,
            'apellido_materno' => $data['apellido_materno'] ?? null,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'fecha_vencimiento' => $data['fecha_vencimiento_id'] ?? null,
            'id_archivo_frente' => $idArchivoFrente,
            'id_archivo_reverso' => $idArchivoReverso,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ✅ 5. Insertar documento de LICENCIA
        DB::table('contrato_documento')->insert([
            'id_contrato' => $idContrato,
            'id_conductor' => $idConductor,
            'tipo' => 'licencia',
            'numero_identificacion' => $data['numero_licencia'] ?? null,
            'pais_emision' => $data['emite_licencia'] ?? null,
            'fecha_emision' => $data['fecha_emision_licencia'] ?? null,
            'fecha_vencimiento' => $data['fecha_vencimiento_licencia'] ?? null,
            'id_archivo_frente' => $idLicFrente,
            'id_archivo_reverso' => $idLicReverso,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ✅ 6. Validar vencimiento de licencia
        if (!empty($data['fecha_vencimiento_licencia'])) {
            $vence = Carbon::parse($data['fecha_vencimiento_licencia']);
            if ($vence->isPast()) {
                DB::table('contrato_evento')->insert([
                    'id_contrato' => $idContrato,
                    'evento' => 'Licencia vencida detectada',
                    'detalle' => json_encode([
                        'conductor' => $idConductor ? "Adicional #$idConductor" : 'Titular',
                        'vence' => $vence->format('Y-m-d')
                    ]),
                    'realizado_en' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return response()->json([
                    'warning' => true,
                    'msg' => '⚠️ La licencia está vencida. Por favor, sube una vigente.'
                ]);
            }
        }

        // ✅ 7. Respuesta final
        return response()->json([
            'success' => true,
            'msg' => 'Documentación guardada correctamente.',
        ]);

    } catch (\Throwable $e) {
        Log::error('Error en ContratoController@guardarDocumentacion: ' . $e->getMessage());
        return response()->json(['error' => 'Error interno al guardar documentación.'], 500);
    }
}


}
