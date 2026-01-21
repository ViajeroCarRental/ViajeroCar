<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\CambioAutoMail;

class ChecklistCambioAutoController extends Controller
{
    /**
     * Flag para activar/desactivar logs de depuración.
     * Ponlo en false si ya no quieres ruido en el log.
     *
     * @var bool
     */
    protected $debugCambioAuto = true;

    /**
     * Helper de log para todo lo relacionado al cambio de auto.
     */
    protected function logCambioAuto(string $message, array $context = []): void
    {
        if ($this->debugCambioAuto) {
            Log::debug('[CambioAuto] ' . $message, $context);
        }
    }

    /**
     * Mostrar checklist de cambio de auto
     * $id = id_contrato
     */
    public function index($id)
    {
        $this->logCambioAuto('index: inicio', ['id_contrato' => $id]);

        // 1) Contrato
        $contrato = DB::table('contratos')
            ->where('id_contrato', $id)
            ->first();

        if (!$contrato) {
            $this->logCambioAuto('index: contrato no encontrado', ['id_contrato' => $id]);
            abort(404, 'Contrato no encontrado.');
        }

        // 2) Reservación ligada al contrato
        $reservacion = DB::table('reservaciones')
            ->where('id_reservacion', $contrato->id_reservacion)
            ->first();

        if (!$reservacion) {
            $this->logCambioAuto('index: reservacion no encontrada', [
                'id_contrato'    => $id,
                'id_reservacion' => $contrato->id_reservacion ?? null,
            ]);
            abort(404, 'Reservación no encontrada para este contrato.');
        }

        // 3) Vehículo original de la reservación
        $vehiculo = null;
        if (!empty($reservacion->id_vehiculo)) {
            $vehiculo = DB::table('vehiculos')
                ->where('id_vehiculo', $reservacion->id_vehiculo)
                ->first();
        }

        // 4) Categoría original (para mostrar el CÓDIGO)
        $categoria = null;
        if (!empty($reservacion->id_categoria)) {
            $categoria = DB::table('categorias_carros')
                ->where('id_categoria', $reservacion->id_categoria)
                ->first();
        }

        // 5) Daños ya registrados (tablas de la izquierda y derecha)
        $danosEmpresa = DB::table('cambios_vehiculo_fotos')
            ->where('id_contrato', $contrato->id_contrato)
            ->where('lado', 'recibido')   // lado empresa
            ->orderBy('id_foto_cambio')
            ->get();

        $danosCliente = DB::table('cambios_vehiculo_fotos')
            ->where('id_contrato', $contrato->id_contrato)
            ->where('lado', 'entregado')  // lado cliente
            ->orderBy('id_foto_cambio')
            ->get();

        // 6) Categorías activas para el select (lado cliente)
        $categorias = DB::table('categorias_carros')
            ->where('activo', 1)
            ->orderBy('codigo')
            ->get();

        // 7) Asesor que hizo el cambio (si existe en el contrato)
        $asesor = null;
        if (!empty($contrato->id_asesor)) {
            $asesor = DB::table('usuarios')
                ->where('id_usuario', $contrato->id_asesor)
                ->select('nombres', 'apellidos')
                ->first();
        }

        $this->logCambioAuto('index: datos preparados para la vista', [
            'id_contrato'          => $contrato->id_contrato,
            'id_reservacion'       => $reservacion->id_reservacion ?? null,
            'id_vehiculo'          => $vehiculo->id_vehiculo ?? null,
            'id_categoria'         => $categoria->id_categoria ?? null,
            'danos_empresa_count'  => $danosEmpresa->count(),
            'danos_cliente_count'  => $danosCliente->count(),
            'categorias_count'     => $categorias->count(),
            'asesor'               => $asesor ? ($asesor->nombres . ' ' . $asesor->apellidos) : null,
        ]);

        // 8) Enviamos todo a la vista
        return view('Admin.checklist2', [
            'contrato'     => $contrato,
            'reservacion'  => $reservacion,
            'vehiculo'     => $vehiculo,
            'categoria'    => $categoria,
            'danosEmpresa' => $danosEmpresa,
            'danosCliente' => $danosCliente,
            'categorias'   => $categorias,
            'asesor'       => $asesor,
        ]);
    }

    public function guardarCambio(Request $request, $id)
    {
        $this->logCambioAuto('guardarCambio: inicio', [
            'id_contrato' => $id,
            // Ojo: no logueamos archivos, solo campos "ligeros"
            'inputs'      => $request->except(['fotos', 'fotos.*']),
        ]);

        try {
            DB::beginTransaction();

            // 1) Contrato
            $contrato = DB::table('contratos')
                ->where('id_contrato', $id)
                ->first();

            if (!$contrato) {
                $this->logCambioAuto('guardarCambio: contrato no encontrado', ['id_contrato' => $id]);
                abort(404, 'Contrato no encontrado.');
            }

            // 2) Reservación ligada al contrato (puede ser null en teoría)
            $reservacion = DB::table('reservaciones')
                ->where('id_reservacion', $contrato->id_reservacion)
                ->first();

            // 3) Vehículos original / nuevo
            $idVehiculoOriginal = $reservacion->id_vehiculo ?? null;
            $idVehiculoNuevo    = $request->input('id_vehiculo_nuevo'); // TODO: conectar con tu UI

            // 4) Usuario que realiza el cambio
            $idUsuario = session('id_usuario');

            if (!$idUsuario) {
                $this->logCambioAuto('guardarCambio: sin usuario en sesión', []);
                abort(403, 'Sesión expirada o usuario no autenticado.');
            }

            // 5) Crear registro en cambios_vehiculo
            $idCambio = DB::table('cambios_vehiculo')->insertGetId([
                'id_contrato'          => $contrato->id_contrato,
                'id_reservacion'       => $reservacion->id_reservacion ?? null,
                'id_vehiculo_original' => $idVehiculoOriginal,
                'id_vehiculo_nuevo'    => $idVehiculoNuevo,
                'realizado_por'        => $idUsuario,
                'realizado_en'         => now(),
                'motivo'               => $request->input('motivo'),
                'estado'               => 'confirmado',
                'created_at'           => now(),
                'updated_at'           => now(),
            ]);

            $this->logCambioAuto('guardarCambio: cambio creado', [
                'id_cambio'           => $idCambio,
                'id_vehiculo_original'=> $idVehiculoOriginal,
                'id_vehiculo_nuevo'   => $idVehiculoNuevo,
                'realizado_por'       => $idUsuario,
            ]);

            // 6) Daños enviados desde el frontend
            $danos = $request->input('danos', []);

            $this->logCambioAuto('guardarCambio: daños recibidos', [
                'id_cambio'   => $idCambio,
                'danos_count' => count($danos),
            ]);

            foreach ($danos as $index => $dano) {
                $contexto = $dano['contexto'] ?? 'empresa';
                $lado = $contexto === 'cliente' ? 'entregado' : 'recibido';

                $zona           = isset($dano['zona']) ? (int)$dano['zona'] : null;
                $tipoDano       = $dano['tipo_dano']    ?? null;
                $comentarioDano = $dano['comentario']   ?? null;
                $costoEstimado  = $dano['costo_estimado'] ?? null;

                $this->logCambioAuto('guardarCambio: procesando daño', [
                    'index'          => $index,
                    'contexto'       => $contexto,
                    'lado'           => $lado,
                    'zona'           => $zona,
                    'tipo_dano'      => $tipoDano,
                    'comentario'     => $comentarioDano,
                    'costo_estimado' => $costoEstimado,
                ]);

                // Archivo asociado a este daño
                $archivo = $request->file("fotos.$index");

                if (!$archivo) {
                    $this->logCambioAuto('guardarCambio: falta foto para daño', ['index' => $index]);
                    throw new \Exception("Falta la foto para el daño #{$index}.");
                }

                $binario       = file_get_contents($archivo->getRealPath());
                $mimeType      = $archivo->getClientMimeType();
                $nombreArchivo = $archivo->getClientOriginalName();

                DB::table('cambios_vehiculo_fotos')->insert([
                    'id_cambio'      => $idCambio,
                    'id_contrato'    => $contrato->id_contrato,
                    'id_reservacion' => $reservacion->id_reservacion ?? null,
                    'lado'           => $lado,
                    'zona'           => $zona,
                    'tipo_dano'      => $tipoDano,
                    'comentario'     => $comentarioDano,
                    'costo_estimado' => $costoEstimado,
                    'archivo'        => $binario,
                    'mime_type'      => $mimeType,
                    'nombre_archivo' => $nombreArchivo,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }

            DB::commit();

            $this->logCambioAuto('guardarCambio: commit OK', [
                'id_contrato' => $contrato->id_contrato,
                'id_cambio'   => $idCambio,
            ]);

            return redirect()
                ->route('checklist2', ['id' => $contrato->id_contrato])
                ->with('success', 'Cambio de vehículo y daños guardados correctamente.');

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Error al guardar cambio de vehículo: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $this->logCambioAuto('guardarCambio: error', [
                'id_contrato' => $id,
                'error'       => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'Ocurrió un error al guardar el cambio de vehículo. Intenta de nuevo.')
                ->withInput();
        }
    }

    public function guardarDano(Request $request, $id)
    {
        $this->logCambioAuto('guardarDano: inicio', [
            'id_contrato' => $id,
            'inputs'      => $request->except(['foto']),
        ]);

        try {
            // 1) Validar lo que viene del modal
            $request->validate([
                'contexto'       => 'required|in:empresa,cliente',
                'zona'           => 'required|integer',
                'tipo_dano'      => 'nullable|string|max:120',
                'comentario'     => 'nullable|string|max:255',
                'costo_estimado' => 'nullable|numeric',
                'foto'           => 'required|file|mimes:jpg,jpeg,png|max:5120',
            ]);

            // 2) Contrato
            $contrato = DB::table('contratos')
                ->where('id_contrato', $id)
                ->first();

            if (!$contrato) {
                $this->logCambioAuto('guardarDano: contrato no encontrado', ['id_contrato' => $id]);
                return response()->json([
                    'ok'      => false,
                    'message' => 'Contrato no encontrado.',
                ], 404);
            }

            // 3) Reservación ligada (si existe)
            $reservacion = DB::table('reservaciones')
                ->where('id_reservacion', $contrato->id_reservacion)
                ->first();

            // 4) Usuario que realiza el cambio (sesión manual)
            $idUsuario = session('id_usuario');
            if (!$idUsuario) {
                $this->logCambioAuto('guardarDano: sin usuario en sesión', []);
                return response()->json([
                    'ok'      => false,
                    'message' => 'Sesión expirada o usuario no autenticado.',
                ], 403);
            }

            // 5) Buscar o crear el registro de "cambio" principal
            $cambio = DB::table('cambios_vehiculo')
                ->where('id_contrato', $contrato->id_contrato)
                ->orderByDesc('id_cambio')
                ->first();

            if ($cambio) {
                $idCambio = $cambio->id_cambio;
                $this->logCambioAuto('guardarDano: usando cambio existente', [
                    'id_cambio' => $idCambio,
                    'estado'    => $cambio->estado ?? null,
                ]);
            } else {
                $idVehiculoOriginal = $reservacion->id_vehiculo ?? null;

                $idCambio = DB::table('cambios_vehiculo')->insertGetId([
                    'id_contrato'          => $contrato->id_contrato,
                    'id_reservacion'       => $reservacion->id_reservacion ?? null,
                    'id_vehiculo_original' => $idVehiculoOriginal,
                    'id_vehiculo_nuevo'    => null,
                    'realizado_por'        => $idUsuario,
                    'realizado_en'         => now(),
                    'motivo'               => null,
                    'estado'               => 'en_proceso',
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);

                $this->logCambioAuto('guardarDano: cambio creado', [
                    'id_cambio'           => $idCambio,
                    'id_vehiculo_original'=> $idVehiculoOriginal,
                    'realizado_por'       => $idUsuario,
                ]);
            }

            // 6) Mapear contexto → lado
            $contexto = $request->input('contexto');
            $lado     = $contexto === 'cliente' ? 'entregado' : 'recibido';

            // 7) Archivo (foto del daño)
            $archivo       = $request->file('foto');
            $binario       = file_get_contents($archivo->getRealPath());
            $mimeType      = $archivo->getClientMimeType();
            $nombreArchivo = $archivo->getClientOriginalName();

            // 8) Insertar en la tabla de fotos / daños
            $idFoto = DB::table('cambios_vehiculo_fotos')->insertGetId([
                'id_cambio'      => $idCambio,
                'id_contrato'    => $contrato->id_contrato,
                'id_reservacion' => $reservacion->id_reservacion ?? null,
                'lado'           => $lado,
                'zona'           => (int)$request->input('zona'),
                'tipo_dano'      => $request->input('tipo_dano'),
                'comentario'     => $request->input('comentario'),
                'costo_estimado' => $request->input('costo_estimado'),
                'archivo'        => $binario,
                'mime_type'      => $mimeType,
                'nombre_archivo' => $nombreArchivo,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            $this->logCambioAuto('guardarDano: daño guardado', [
                'id_cambio'       => $idCambio,
                'id_foto_cambio'  => $idFoto,
                'contexto'        => $contexto,
                'lado'            => $lado,
                'zona'            => (int)$request->input('zona'),
                'costo_estimado'  => $request->input('costo_estimado'),
            ]);

            return response()->json([
                'ok'      => true,
                'message' => 'Daño guardado correctamente.',
                'dano'    => [
                    'id'             => $idFoto,
                    'contexto'       => $contexto,
                    'lado'           => $lado,
                    'zona'           => (int)$request->input('zona'),
                    'tipo_dano'      => $request->input('tipo_dano'),
                    'comentario'     => $request->input('comentario'),
                    'costo_estimado' => $request->input('costo_estimado'),
                    'tiene_foto'     => true,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al guardar daño de cambio de vehículo: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $this->logCambioAuto('guardarDano: error', [
                'id_contrato' => $id,
                'error'       => $e->getMessage(),
            ]);

            return response()->json([
                'ok'      => false,
                'message' => 'Ocurrió un error al guardar el daño.',
            ], 500);
        }
    }

    public function eliminarDano($id)
    {
        $this->logCambioAuto('eliminarDano: inicio', ['id_foto_cambio' => $id]);

        try {
            $registro = DB::table('cambios_vehiculo_fotos')
                ->where('id_foto_cambio', $id)
                ->first();

            if (!$registro) {
                $this->logCambioAuto('eliminarDano: daño no encontrado', [
                    'id_foto_cambio' => $id,
                ]);

                return response()->json([
                    'ok'      => false,
                    'message' => 'Daño no encontrado.',
                ], 404);
            }

            DB::table('cambios_vehiculo_fotos')
                ->where('id_foto_cambio', $id)
                ->delete();

            $this->logCambioAuto('eliminarDano: daño eliminado', [
                'id_foto_cambio' => $id,
            ]);

            return response()->json([
                'ok'      => true,
                'message' => 'Daño eliminado correctamente.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al eliminar daño de cambio de vehículo: ' . $e->getMessage());

            $this->logCambioAuto('eliminarDano: error', [
                'id_foto_cambio' => $id,
                'error'          => $e->getMessage(),
            ]);

            return response()->json([
                'ok'      => false,
                'message' => 'Error interno al eliminar el daño.',
            ], 500);
        }
    }

    public function vehiculosPorCategoria($id, $idCategoria)
    {
        $this->logCambioAuto('vehiculosPorCategoria: inicio', [
            'id_contrato'   => $id,
            'id_categoria'  => $idCategoria,
        ]);

        try {
            // 1) Verificamos que el contrato exista
            $contrato = DB::table('contratos')
                ->where('id_contrato', $id)
                ->first();

            if (!$contrato) {
                $this->logCambioAuto('vehiculosPorCategoria: contrato no encontrado', [
                    'id_contrato' => $id,
                ]);

                return response()->json([
                    'ok'      => false,
                    'message' => 'Contrato no encontrado.',
                ], 404);
            }

            // 2) Traer vehículos de esa categoría
            $vehiculos = DB::table('vehiculos')
                ->where('id_categoria', $idCategoria)
                // ->where('id_estatus', X)
                ->orderBy('nombre_publico')
                ->get([
                    'id_vehiculo',
                    'marca',
                    'modelo',
                    'anio',
                    'nombre_publico',
                    'placa',
                    'transmision',
                    'combustible',
                    'kilometraje',
                    'gasolina_actual',
                    'precio_dia',
                    'color',
                    'numero_serie',
                    'tipo_servicio',
                ]);

            $this->logCambioAuto('vehiculosPorCategoria: vehículos encontrados', [
                'id_contrato'  => $id,
                'id_categoria' => $idCategoria,
                'count'        => $vehiculos->count(),
            ]);

            return response()->json([
                'ok'        => true,
                'vehiculos' => $vehiculos,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al obtener vehículos por categoría: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $this->logCambioAuto('vehiculosPorCategoria: error', [
                'id_contrato'  => $id,
                'id_categoria' => $idCategoria,
                'error'        => $e->getMessage(),
            ]);

            return response()->json([
                'ok'      => false,
                'message' => 'Error interno al obtener vehículos.',
            ], 500);
        }
    }

    public function setVehiculoNuevo(Request $request, $id)
    {
        $this->logCambioAuto('setVehiculoNuevo: inicio', [
            'id_contrato' => $id,
            'input'       => $request->except([]),
        ]);

        try {
            // 1) Validar entrada
            $request->validate([
                'id_vehiculo_nuevo' => 'required|integer|exists:vehiculos,id_vehiculo',
            ]);

            $idVehiculoNuevo = (int) $request->input('id_vehiculo_nuevo');

            // 2) Contrato
            $contrato = DB::table('contratos')
                ->where('id_contrato', $id)
                ->first();

            if (!$contrato) {
                $this->logCambioAuto('setVehiculoNuevo: contrato no encontrado', [
                    'id_contrato' => $id,
                ]);

                return response()->json([
                    'ok'      => false,
                    'message' => 'Contrato no encontrado.',
                ], 404);
            }

            // 3) Reservación ligada
            $reservacion = DB::table('reservaciones')
                ->where('id_reservacion', $contrato->id_reservacion)
                ->first();

            if (!$reservacion) {
                $this->logCambioAuto('setVehiculoNuevo: reservación no encontrada', [
                    'id_contrato'    => $id,
                    'id_reservacion' => $contrato->id_reservacion ?? null,
                ]);

                return response()->json([
                    'ok'      => false,
                    'message' => 'Reservación no encontrada para este contrato.',
                ], 404);
            }

            // 4) Usuario en sesión
            $idUsuario = session('id_usuario');
            if (!$idUsuario) {
                $this->logCambioAuto('setVehiculoNuevo: sin usuario en sesión', []);
                return response()->json([
                    'ok'      => false,
                    'message' => 'Sesión expirada o usuario no autenticado.',
                ], 403);
            }

            // 5) Buscar cambio existente en_proceso (o el último)
            $cambio = DB::table('cambios_vehiculo')
                ->where('id_contrato', $contrato->id_contrato)
                ->where('estado', 'en_proceso')
                ->orderByDesc('id_cambio')
                ->first();

            if ($cambio) {
                DB::table('cambios_vehiculo')
                    ->where('id_cambio', $cambio->id_cambio)
                    ->update([
                        'id_vehiculo_nuevo' => $idVehiculoNuevo,
                        'updated_at'        => now(),
                    ]);

                $idCambio = $cambio->id_cambio;

                $this->logCambioAuto('setVehiculoNuevo: cambio actualizado', [
                    'id_cambio'        => $idCambio,
                    'id_vehiculo_nuevo'=> $idVehiculoNuevo,
                ]);
            } else {
                $idVehiculoOriginal = $reservacion->id_vehiculo ?? null;

                $idCambio = DB::table('cambios_vehiculo')->insertGetId([
                    'id_contrato'          => $contrato->id_contrato,
                    'id_reservacion'       => $reservacion->id_reservacion,
                    'id_vehiculo_original' => $idVehiculoOriginal,
                    'id_vehiculo_nuevo'    => $idVehiculoNuevo,
                    'realizado_por'        => $idUsuario,
                    'realizado_en'         => now(),
                    'motivo'               => null,
                    'estado'               => 'en_proceso',
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);

                $this->logCambioAuto('setVehiculoNuevo: cambio creado', [
                    'id_cambio'           => $idCambio,
                    'id_vehiculo_original'=> $idVehiculoOriginal,
                    'id_vehiculo_nuevo'   => $idVehiculoNuevo,
                    'realizado_por'       => $idUsuario,
                ]);
            }

            return response()->json([
                'ok'       => true,
                'message'  => 'Vehículo nuevo asignado al cambio de auto.',
                'idCambio' => $idCambio,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al asignar vehículo nuevo en cambio de auto: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $this->logCambioAuto('setVehiculoNuevo: error', [
                'id_contrato' => $id,
                'error'       => $e->getMessage(),
            ]);

            return response()->json([
                'ok'      => false,
                'message' => 'Ocurrió un error al asignar el vehículo nuevo.',
            ], 500);
        }
    }

    public function confirmarCambio(Request $request, $id)
    {
        $this->logCambioAuto('confirmarCambio: inicio', [
            'id_contrato' => $id,
            'tiene_fotos' => $request->hasFile('fotos_cambio'),
            'total_fotos' => $request->hasFile('fotos_cambio') ? count($request->file('fotos_cambio')) : 0,
        ]);

        try {
            DB::beginTransaction();

            // 0) Validar SOLO las fotos generales del cambio de vehículo
            $request->validate([
                'fotos_cambio.*' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
            ]);

            // 1) Contrato
            $contrato = DB::table('contratos')
                ->where('id_contrato', $id)
                ->first();

            if (!$contrato) {
                DB::rollBack();
                $this->logCambioAuto('confirmarCambio: contrato no encontrado', [
                    'id_contrato' => $id,
                ]);

                return redirect()
                    ->back()
                    ->with('error', 'Contrato no encontrado.');
            }

            // 2) Reservación ligada al contrato
            $reservacion = DB::table('reservaciones')
                ->where('id_reservacion', $contrato->id_reservacion)
                ->first();

            if (!$reservacion) {
                DB::rollBack();
                $this->logCambioAuto('confirmarCambio: reservación no encontrada', [
                    'id_contrato'    => $id,
                    'id_reservacion' => $contrato->id_reservacion ?? null,
                ]);

                return redirect()
                    ->back()
                    ->with('error', 'Reservación no encontrada para este contrato.');
            }

            // 3) Buscar cambio EN_PROCESO para este contrato
            $cambio = DB::table('cambios_vehiculo')
                ->where('id_contrato', $contrato->id_contrato)
                ->where('estado', 'en_proceso')
                ->orderByDesc('id_cambio')
                ->first();

            if (!$cambio) {
                DB::rollBack();
                $this->logCambioAuto('confirmarCambio: no hay cambio en_proceso', [
                    'id_contrato' => $contrato->id_contrato,
                ]);

                return redirect()
                    ->back()
                    ->with('error', 'No hay un cambio de vehículo en proceso para este contrato.');
            }

            if (!$cambio->id_vehiculo_nuevo) {
                DB::rollBack();
                $this->logCambioAuto('confirmarCambio: no hay vehiculo nuevo asignado', [
                    'id_cambio' => $cambio->id_cambio,
                ]);

                return redirect()
                    ->back()
                    ->with('error', 'No hay un vehículo nuevo asignado al cambio. Selecciónalo antes de confirmar.');
            }

            $idVehiculoNuevo = $cambio->id_vehiculo_nuevo;

            // 4) Actualizar la reservación para usar el nuevo vehículo
            DB::table('reservaciones')
                ->where('id_reservacion', $reservacion->id_reservacion)
                ->update([
                    'id_vehiculo' => $idVehiculoNuevo,
                    'updated_at'  => now(),
                ]);

            // 5) Marcar el cambio como "confirmado"
            DB::table('cambios_vehiculo')
                ->where('id_cambio', $cambio->id_cambio)
                ->update([
                    'estado'     => 'confirmado',
                    'updated_at' => now(),
                ]);

            // 6) Guardar FOTOGRAFÍAS GENERALES del cambio de vehículo
            if ($request->hasFile('fotos_cambio')) {
                foreach ($request->file('fotos_cambio') as $idx => $foto) {
                    if (!$foto || !$foto->isValid()) {
                        $this->logCambioAuto('confirmarCambio: foto inválida saltada', [
                            'index' => $idx,
                        ]);
                        continue;
                    }

                    $binario       = file_get_contents($foto->getRealPath());
                    $mimeType      = $foto->getClientMimeType();
                    $nombreArchivo = $foto->getClientOriginalName();

                    DB::table('cambios_vehiculo_fotos_generales')->insert([
                        'id_cambio'      => $cambio->id_cambio,
                        'id_contrato'    => $contrato->id_contrato,
                        'id_reservacion' => $reservacion->id_reservacion ?? null,
                        'archivo'        => $binario,
                        'mime_type'      => $mimeType,
                        'nombre_archivo' => $nombreArchivo,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
            }

            DB::commit();

            $this->logCambioAuto('confirmarCambio: cambio confirmado y fotos guardadas', [
                'id_cambio'        => $cambio->id_cambio,
                'id_contrato'      => $contrato->id_contrato,
                'id_reservacion'   => $reservacion->id_reservacion ?? null,
                'id_vehiculo_nuevo'=> $idVehiculoNuevo,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Error al confirmar cambio de vehículo: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $this->logCambioAuto('confirmarCambio: error en bloque principal', [
                'id_contrato' => $id,
                'error'       => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Ocurrió un error al confirmar el cambio de vehículo. Intenta de nuevo.');
        }

        /**
         * ================================
         *  AHORA: GENERAR PDF + ENVIAR MAIL
         * ================================
         */
        try {
            // 🔁 Recargamos datos necesarios para el PDF
            $vehiculoOriginal = null;
            if (!empty($cambio->id_vehiculo_original)) {
                $vehiculoOriginal = DB::table('vehiculos')
                    ->where('id_vehiculo', $cambio->id_vehiculo_original)
                    ->first();
            }

            $vehiculoNuevo = DB::table('vehiculos')
                ->where('id_vehiculo', $idVehiculoNuevo)
                ->first();

            // Categoría original
            $categoria = null;
            if (!empty($reservacion->id_categoria)) {
                $categoria = DB::table('categorias_carros')
                    ->where('id_categoria', $reservacion->id_categoria)
                    ->first();
            }

            // Categoría del nuevo vehículo
            $categoriaNuevo = null;
            if (!empty($vehiculoNuevo->id_categoria ?? null)) {
                $categoriaNuevo = DB::table('categorias_carros')
                    ->where('id_categoria', $vehiculoNuevo->id_categoria)
                    ->first();
            }

            // Daños “recibido” (empresa) y “entregado” (cliente)
            $danosEmpresa = DB::table('cambios_vehiculo_fotos')
                ->where('id_contrato', $contrato->id_contrato)
                ->where('lado', 'recibido')
                ->orderBy('id_foto_cambio')
                ->get();

            $danosCliente = DB::table('cambios_vehiculo_fotos')
                ->where('id_contrato', $contrato->id_contrato)
                ->where('lado', 'entregado')
                ->orderBy('id_foto_cambio')
                ->get();

            // Asesor
            $asesor = null;
            if (!empty($contrato->id_asesor)) {
                $asesor = DB::table('usuarios')
                    ->where('id_usuario', $contrato->id_asesor)
                    ->select('nombres', 'apellidos')
                    ->first();
            }

            $fechaCambio = now();

            $this->logCambioAuto('confirmarCambio: generando PDF', [
                'id_contrato'      => $contrato->id_contrato,
                'id_reservacion'   => $reservacion->id_reservacion ?? null,
                'id_vehiculo_orig' => $vehiculoOriginal->id_vehiculo ?? null,
                'id_vehiculo_nuevo'=> $vehiculoNuevo->id_vehiculo ?? null,
                'danos_empresa'    => $danosEmpresa->count(),
                'danos_cliente'    => $danosCliente->count(),
            ]);

            // 🔹 Generar PDF
            $pdfCambio = Pdf::loadView('Admin.cambio_auto-pdf', [
                'contrato'      => $contrato,
                'reservacion'   => $reservacion,
                'vehiculo'      => $vehiculoOriginal,
                'categoria'     => $categoria,
                'vehiculoNuevo' => $vehiculoNuevo,
                'categoriaNuevo'=> $categoriaNuevo,
                'danosEmpresa'  => $danosEmpresa,
                'danosCliente'  => $danosCliente,
                'asesor'        => $asesor,
                'fechaCambio'   => $fechaCambio,
            ])->output();

            // 🔹 Fotos generales del cambio para adjuntar al correo
            $fotosBD = DB::table('cambios_vehiculo_fotos_generales')
                ->where('id_cambio', $cambio->id_cambio)
                ->get();

            $fotosCambio = [];
            foreach ($fotosBD as $foto) {
                $fotosCambio[] = [
                    'contenido' => $foto->archivo,
                    'nombre'    => $foto->nombre_archivo ?: 'foto-cambio-' . $foto->id_cambio . '.jpg',
                    'mime'      => $foto->mime_type ?: 'image/jpeg',
                ];
            }

            $this->logCambioAuto('confirmarCambio: fotos generales para correo', [
                'id_cambio'         => $cambio->id_cambio,
                'fotos_generales'   => count($fotosCambio),
            ]);

            // 🔹 Correo del cliente
            $correoCliente =
                $reservacion->email_cliente
                ?? $reservacion->correo_cliente
                ?? null;

            if ($correoCliente) {
                $this->logCambioAuto('confirmarCambio: enviando correo a cliente', [
                    'correo_cliente' => $correoCliente,
                ]);

                Mail::to($correoCliente)->send(
                    new CambioAutoMail(
                        $reservacion,
                        $contrato,
                        $pdfCambio,
                        $fotosCambio
                    )
                );
            } else {
                $this->logCambioAuto('confirmarCambio: sin correo de cliente', [
                    'id_reservacion' => $reservacion->id_reservacion ?? null,
                ]);

                Log::warning('Cambio de vehículo confirmado pero la reservación no tiene correo de cliente.', [
                    'id_reservacion' => $reservacion->id_reservacion ?? null,
                ]);

                return redirect()
                    ->route('checklist2', ['id' => $contrato->id_contrato])
                    ->with('success', 'Cambio de vehículo confirmado y fotografías guardadas, pero la reservación no tiene correo de cliente para enviar el mail.');
            }

            $this->logCambioAuto('confirmarCambio: correo enviado OK', [
                'id_contrato'  => $contrato->id_contrato,
                'id_cambio'    => $cambio->id_cambio,
                'correo'       => $correoCliente,
            ]);

            return redirect()
                ->route('checklist2', ['id' => $contrato->id_contrato])
                ->with('success', 'Cambio de vehículo confirmado, fotografías guardadas y correo enviado correctamente al cliente.');

        } catch (\Throwable $e) {
            Log::error('Cambio de vehículo confirmado pero falló el envío de correo: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $this->logCambioAuto('confirmarCambio: error al enviar correo', [
                'id_contrato' => $id,
                'error'       => $e->getMessage(),
            ]);

            return redirect()
                ->route('checklist2', ['id' => $contrato->id_contrato])
                ->with('success', 'Cambio de vehículo confirmado y fotografías guardadas correctamente.')
                ->with('error', 'Sin embargo, ocurrió un error al enviar el correo al cliente. Revisa el log o intenta reenviar manualmente.');
        }
    }
}
