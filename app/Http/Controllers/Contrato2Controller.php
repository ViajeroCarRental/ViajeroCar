<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Contrato2Controller extends ContratoBaseController
{
    // ─────────────────────────────────────────────
    // QUERIES BASE
    // ─────────────────────────────────────────────

    private function pagosReservacionQuery(int $idReservacion)
    {
        return DB::table('pagos')
            ->where('id_reservacion', $idReservacion)
            ->where('estatus', 'paid')
            ->where(function ($q) {
                $q->whereNull('tipo_pago')
                    ->orWhereRaw("UPPER(TRIM(tipo_pago)) <> 'GARANTIA'");
            });
    }

    private function pagosGarantiaQuery(int $idReservacion)
    {
        return DB::table('pagos')
            ->where('id_reservacion', $idReservacion)
            ->where('estatus', 'paid')
            ->whereRaw("UPPER(TRIM(COALESCE(tipo_pago, ''))) = 'GARANTIA'");
    }

    // ─────────────────────────────────────────────
    // TABLA DE GARANTÍAS
    // ─────────────────────────────────────────────

    private function tablaGarantiasSeguro(): array
    {
        return [
            'C'  => ['ldw' => 5000,  'pdw' => 8000,  'cdw10' => 15000, 'cdw20' => 25000,  'declined' => 330000],
            'D'  => ['ldw' => 5000,  'pdw' => 8000,  'cdw10' => 18000, 'cdw20' => 25000,  'declined' => 380000],
            'E'  => ['ldw' => 5000,  'pdw' => 8000,  'cdw10' => 20000, 'cdw20' => 30000,  'declined' => 500000],
            'F'  => ['ldw' => 5000,  'pdw' => 15000, 'cdw10' => 30000, 'cdw20' => 40000,  'declined' => 650000],
            'IC' => ['ldw' => 5000,  'pdw' => 8000,  'cdw10' => 20000, 'cdw20' => 30000,  'declined' => 500000],
            'I'  => ['ldw' => 5000,  'pdw' => 10000, 'cdw10' => 30000, 'cdw20' => 40000,  'declined' => 600000],
            'IB' => ['ldw' => 5000,  'pdw' => 8000,  'cdw10' => 18000, 'cdw20' => 25000,  'declined' => 400000],
            'M'  => ['ldw' => 10000, 'pdw' => 20000, 'cdw10' => 30000, 'cdw20' => 40000,  'declined' => 800000],
            'L'  => ['ldw' => 10000, 'pdw' => 20000, 'cdw10' => 30000, 'cdw20' => 40000,  'declined' => 800000],
            'H'  => ['ldw' => 10000, 'pdw' => 20000, 'cdw10' => 30000, 'cdw20' => 40000,  'declined' => 600000],
            'HI' => ['ldw' => 10000, 'pdw' => 20000, 'cdw10' => 30000, 'cdw20' => 40000,  'declined' => 900000],
        ];
    }

    private function claveGarantiaPorSeguro(?string $nombre): string
    {
        $n = strtolower(trim($nombre ?? ''));

        if ($n === '')              return 'declined';
        if (str_contains($n, 'pdw'))    return 'pdw';
        if (str_contains($n, 'ldw'))    return 'ldw';
        if (str_contains($n, '10'))     return 'cdw10';
        if (str_contains($n, '20'))     return 'cdw20';
        if (str_contains($n, 'declin')) return 'declined';

        return 'declined';
    }

    private function obtenerGarantiaSeguro(?string $codigoCategoria, ?string $nombreSeguro): array
    {
        $codigo = strtoupper($codigoCategoria ?: 'C');
        $clave  = $this->claveGarantiaPorSeguro($nombreSeguro);
        $monto  = $this->tablaGarantiasSeguro()[$codigo][$clave] ?? 0;

        return [
            'codigo_categoria' => $codigo,
            'tipo_seguro'      => $clave,
            'nombre_seguro'    => $nombreSeguro ?: 'Sin paquete',
            'monto'            => $monto,
        ];
    }

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────

    private function formatearTelefono(?string $telefono): string
    {
        $n = preg_replace('/[^0-9]/', '', $telefono ?? '');

        return strlen($n) === 10
            ? '(' . substr($n, 0, 3) . ') ' . substr($n, 3, 3) . '-' . substr($n, 6)
            : ($telefono ?: '--');
    }

    /**
     * Crea un contrato vinculado a una reservación y devuelve su ID.
     */
    private function crearContrato(int $idReservacion): int
    {
        return DB::transaction(function () use ($idReservacion) {
            $id = DB::table('contratos')->insertGetId([
                'id_reservacion'  => $idReservacion,
                'id_asesor'       => session('id_usuario'),
                'numero_contrato' => 'TEMP',
                'estado'          => 'abierto',
                'abierto_en'      => now(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            DB::table('contratos')
                ->where('id_contrato', $id)
                ->update(['numero_contrato' => str_pad($id, 4, '0', STR_PAD_LEFT)]);

            return $id;
        });
    }

    /**
     * Recalcula y sincroniza el estado de pago de una reservación.
     */
    private function sincronizarEstadoPago(int $idReservacion): void
    {
        $res         = DB::table('reservaciones')->where('id_reservacion', $idReservacion)->first();
        $totalPagado = $this->pagosReservacionQuery($idReservacion)->sum('monto');
        $saldo       = $res->total - $totalPagado;

        if ($saldo <= 0) {
            $estadoPago   = 'Pagado';
            $estadoReserva = 'confirmada';
        } elseif ($totalPagado > 0) {
            $estadoPago   = 'Parcial';
            $estadoReserva = $res->estado;
        } else {
            $estadoPago   = 'Pendiente';
            $estadoReserva = 'pendiente_pago';
        }

        DB::table('reservaciones')
            ->where('id_reservacion', $idReservacion)
            ->update([
                'status_pago' => $estadoPago,
                'estado'      => $estadoReserva,
                'updated_at'  => now(),
            ]);
    }

    /**
     * Guarda un archivo binario en la tabla 'archivos' y devuelve su ID.
     */
    private function guardarArchivo($file): ?int
    {
        if (!$file) return null;

        return DB::table('archivos')->insertGetId([
            'nombre_original' => $file->getClientOriginalName(),
            'tipo'            => 'imagen',
            'contenido'       => file_get_contents($file->getRealPath()),
            'extension'       => $file->extension(),
            'mime_type'       => $file->getMimeType(),
            'tamano_bytes'    => $file->getSize(),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);
    }

    // ─────────────────────────────────────────────
    // MOSTRAR CONTRATO
    // ─────────────────────────────────────────────

    public function mostrarContrato2($id)
    {
        try {
            $reservacion = DB::table('reservaciones as r')
                ->leftJoin('contratos as c',          'r.id_reservacion', '=', 'c.id_reservacion')
                ->leftJoin('sucursales as sr',         'r.sucursal_retiro',  '=', 'sr.id_sucursal')
                ->leftJoin('sucursales as se',         'r.sucursal_entrega', '=', 'se.id_sucursal')
                ->leftJoin('vehiculos as v',           'r.id_vehiculo',     '=', 'v.id_vehiculo')
                ->leftJoin('categoria_costo_km as cck', 'r.id_categoria',    '=', 'cck.id_categoria')
                ->where(fn($q) => $q->where('r.id_reservacion', $id)->orWhere('c.id_contrato', $id))
                ->select(
                    'r.*',
                    'c.id_contrato',
                    'c.numero_contrato',
                    'sr.nombre as sucursal_retiro_nombre',
                    'se.nombre as sucursal_entrega_nombre',
                    'v.id_vehiculo',
                    'v.marca',
                    'v.modelo',
                    'v.placa',
                    'v.color',
                    'v.transmision',
                    'v.asientos',
                    'v.puertas',
                    'cck.costo_km as costo_km_cat'
                )
                ->first();

            abort_unless($reservacion, 404, 'No se encontró la reservación con el ID: ' . $id);

            $apellidosClienteFull = trim($reservacion->apellidos_cliente ?? '');

            if (empty($reservacion->apellido_paterno) && !empty($apellidosClienteFull)) {
                $partes = explode(' ', $apellidosClienteFull, 2);
                $reservacion->apellido_paterno = $partes[0];
                $reservacion->apellido_materno = $partes[1] ?? '';
            } else {
                $reservacion->apellido_paterno = $reservacion->apellido_paterno ?? '';
                $reservacion->apellido_materno = $reservacion->apellido_materno ?? '';
            }

            $idReservacion = $reservacion->id_reservacion;

            if (!$reservacion->id_contrato) {
                $reservacion->id_contrato      = $this->crearContrato($idReservacion);
                $reservacion->numero_contrato  = str_pad($reservacion->id_contrato, 4, '0', STR_PAD_LEFT);
            }

            $idContrato = $reservacion->id_contrato;

            // ── Catálogos cacheados ──────────────────────────────────────────
            $ubicaciones = Cache::remember(
                'ubicaciones_servicio',
                86400,
                fn() =>
                DB::table('ubicaciones_servicio')
                    ->select('id_ubicacion', 'estado', 'destino', 'km')
                    ->where('activo', 1)->get()
            );

            $conceptos = Cache::remember(
                'cargo_concepto_filtrado',
                86400,
                fn() =>
                DB::table('cargo_concepto')
                    ->select('id_concepto', 'nombre', 'monto_base', 'descripcion', 'moneda')
                    ->where('activo', true)
                    ->whereNotIn('id_concepto', [5, 6])
                    ->get()
            );

            $categorias = Cache::remember(
                'categorias_carros',
                3600,
                fn() =>
                DB::table('categorias_carros')
                    ->select('id_categoria', 'nombre', 'codigo', 'precio_dia')
                    ->orderBy('nombre')->get()
            );

            $idServicioMenor = Cache::remember(
                'id_servicio_menor',
                86400,
                fn() =>
                DB::table('servicios')->where('nombre', 'LIKE', '%menor%')->value('id_servicio')
            );

            // ── Servicios y conductores ──────────────────────────────────────
            $serviciosReservados = DB::table('reservacion_servicio')
                ->where('id_reservacion', $idReservacion)
                ->pluck('cantidad', 'id_servicio')
                ->toArray();

            $conductoresExtras = collect();
            $idServicioConductor = 4;

            if (isset($serviciosReservados[$idServicioConductor])) {
                $cantidad     = $serviciosReservados[$idServicioConductor];
                $conductoresDb = DB::table('contrato_conductor_adicional')
                    ->where('id_contrato', $idContrato)
                    ->limit($cantidad)
                    ->get();

                for ($i = 1; $i <= $cantidad; $i++) {
                    $c = $conductoresDb[$i - 1] ?? null;
                    $conductoresExtras->push([
                        'id_conductor' => $c->id_conductor ?? null,
                        'nombres'      => $c->nombres ?? "Conductor adicional $i",
                    ]);
                }
            }

            // ── Cálculos de tarifa ───────────────────────────────────────────
            $catActual   = $categorias->firstWhere('id_categoria', $reservacion->id_categoria);
            $fechaInicio = Carbon::parse($reservacion->fecha_inicio ?? now());
            $fechaFin    = Carbon::parse($reservacion->fecha_fin    ?? now()->addDay());
            $horaRetiro  = Carbon::parse($reservacion->hora_retiro  ?? '12:00:00');
            $horaEntrega = Carbon::parse($reservacion->hora_entrega ?? '12:00:00');
            $dias        = max(1, $fechaInicio->diffInDays($fechaFin));

            $precioBase = $catActual->precio_dia ?? 0;
            $precioReal = ($reservacion->tarifa_ajustada == 1 && $reservacion->tarifa_modificada > 0)
                ? $reservacion->tarifa_modificada
                : $precioBase;

            $subtotal = $dias * $precioReal;
            $iva      = $subtotal * 0.16;
            $total    = $subtotal + $iva;

            return view('Admin.Contrato2', [
                'reservacion'         => $reservacion,
                'idReservacion'       => $idReservacion,
                'contrato'            => $reservacion,
                'idContrato'          => $idContrato,
                'vehiculo'            => $reservacion,
                'ubicaciones'         => $ubicaciones,
                'cargos_conceptos'    => $conceptos,
                'categorias'          => $categorias,
                'costoKmCategoria'    => $reservacion->costo_km_cat ?? 0,
                'conductoresExtras'   => $conductoresExtras,
                'serviciosReservados' => $serviciosReservados,
                'idServicioMenor'     => $idServicioMenor ?? 0,
                'fechaInicio'         => $fechaInicio,
                'fechaFin'            => $fechaFin,
                'horaRetiro'          => $horaRetiro,
                'horaEntrega'         => $horaEntrega,
                'diasTotales'         => $dias,
                'precioReal'          => $precioReal,
                'subtotal'            => $subtotal,
                'iva'                 => $iva,
                'total'               => $total,
                'telFinal'            => $this->formatearTelefono($reservacion->telefono_cliente ?? ''),
                'delivery'            => (object) [
                    'activo'       => $reservacion->delivery_activo,
                    'id_ubicacion' => $reservacion->delivery_ubicacion,
                    'direccion'    => $reservacion->delivery_direccion,
                    'kms'          => $reservacion->delivery_km,
                    'total'        => $reservacion->delivery_total,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en mostrarContrato2: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar el contrato.');
        }
    }

    // ─────────────────────────────────────────────
    // SERVICIOS EXTRAS
    // ─────────────────────────────────────────────

    public function actualizarServiciosExtras(Request $request)
    {
        try {
            $idReservacion = $request->id_reservacion;
            $idServicio    = $request->id_servicio;
            $cantidad      = $request->cantidad ?? 1;
            $forzar        = $request->forzar;

            $query = DB::table('reservacion_servicio')
                ->where('id_reservacion', $idReservacion)
                ->where('id_servicio', $idServicio);

            if ($forzar === 'off' || $cantidad <= 0) {
                $query->delete();
                return response()->json(['success' => true, 'action' => 'deleted']);
            }

            $precio = DB::table('servicios')->where('id_servicio', $idServicio)->value('precio') ?? 0;

            if ($query->exists()) {
                $query->update(['cantidad' => $cantidad, 'precio_unitario' => $precio, 'updated_at' => now()]);
            } else {
                DB::table('reservacion_servicio')->insert([
                    'id_reservacion'  => $idReservacion,
                    'id_servicio'     => $idServicio,
                    'cantidad'        => $cantidad,
                    'precio_unitario' => $precio,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    // ─────────────────────────────────────────────
    // CARGOS ADICIONALES
    // ─────────────────────────────────────────────

    public function actualizarCargos(Request $request)
    {
        try {
            $idConcepto    = $request->id_concepto;
            $idContrato    = $request->id_contrato    ?: null;
            $idReservacion = $request->id_reservacion ?: null;
            $forzar        = $request->forzar;

            // Conceptos protegidos
            if (in_array($idConcepto, [5, 6])) {
                return response()->json(['success' => true, 'action' => 'ignored']);
            }

            if ((!$idContrato && !$idReservacion) || !$idConcepto) {
                return response()->json(['success' => false, 'msg' => 'Faltan parámetros requeridos.']);
            }

            $query = DB::table('cargo_adicional')
                ->where('id_concepto', $idConcepto)
                ->where(function ($q) use ($idContrato, $idReservacion) {
                    if ($idContrato)    $q->where('id_contrato', $idContrato);
                    if ($idReservacion) $q->orWhere('id_reservacion', $idReservacion);
                });

            $existe = $query->exists();

            // Forzar eliminación
            if ($forzar === 'off') {
                if ($existe) $query->delete();
                return response()->json(['success' => true, 'action' => 'deleted']);
            }

            $concepto = DB::table('cargo_concepto')->where('id_concepto', $idConcepto)->first();

            $insertar = function () use ($idContrato, $idReservacion, $idConcepto, $concepto) {
                DB::table('cargo_adicional')->insert([
                    'id_contrato'    => $idContrato,
                    'id_reservacion' => $idReservacion,
                    'id_concepto'    => $idConcepto,
                    'concepto'       => $concepto->nombre ?? 'Cargo adicional',
                    'monto'          => $concepto->monto_base ?? 0,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            };

            // Forzar inserción
            if ($forzar === 'on') {
                if (!$existe) $insertar();
                return response()->json(['success' => true, 'action' => 'inserted']);
            }

            // Toggle
            if ($existe) {
                $query->delete();
                return response()->json(['success' => true, 'action' => 'deleted']);
            }

            $insertar();
            return response()->json(['success' => true, 'action' => 'inserted']);
        } catch (\Exception $e) {
            Log::error('ERROR actualizarCargos: ' . $e->getMessage());
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function obtenerCargosContrato(int $idContrato)
    {
        try {
            $cargos = DB::table('cargo_adicional')
                ->where('id_contrato', $idContrato)
                ->select('id_concepto', 'monto', 'detalle')
                ->get()
                ->map(fn($c) => tap($c, fn($c) => $c->detalle = $c->detalle ? json_decode($c->detalle) : null));

            return response()->json(['success' => true, 'cargos' => $cargos]);
        } catch (\Throwable $e) {
            Log::error('ERROR obtenerCargosContrato: ' . $e->getMessage());
            return response()->json(['success' => false], 500);
        }
    }

    // ─────────────────────────────────────────────
    // UPGRADE DE CATEGORÍA
    // ─────────────────────────────────────────────

    protected const ORDEN_CATEGORIAS = ['C', 'D', 'E', 'F', 'IC', 'I', 'IB', 'M', 'L', 'H', 'HI'];

    public function obtenerOfertaUpgrade(int $idReservacion)
    {
        try {
            $res = DB::table('reservaciones')->where('id_reservacion', $idReservacion)->first();
            if (!$res) return response()->json(['success' => false, 'error' => 'Reservación no encontrada']);

            $catActual = DB::table('categorias_carros')->where('id_categoria', $res->id_categoria)->first();
            if (!$catActual) return response()->json(['success' => false, 'error' => 'Categoría no encontrada']);

            $posActual = array_search($catActual->codigo, self::ORDEN_CATEGORIAS);
            if ($posActual === false) return response()->json(['success' => false, 'msg' => 'Categoría fuera del orden oficial.']);

            $codigosSuperiores = array_slice(self::ORDEN_CATEGORIAS, $posActual + 1);
            if (empty($codigosSuperiores)) return response()->json(['success' => false, 'msg' => 'No hay categorías superiores.']);

            $catSuperior = DB::table('categorias_carros')
                ->whereIn('codigo', $codigosSuperiores)
                ->orderBy('precio_dia')
                ->get()
                ->random();

            $vehiculo = DB::table('vehiculos')
                ->where('id_categoria', $catSuperior->id_categoria)
                ->inRandomOrder()->first();

            if (!$vehiculo) return response()->json(['success' => false, 'msg' => 'Sin vehículos disponibles para upgrade.']);

            $foto         = DB::table('vehiculo_imagenes')->where('id_vehiculo', $vehiculo->id_vehiculo)->orderBy('orden')->value('url') ?? '/img/default-car.jpg';
            $precioInflado = round($catSuperior->precio_dia * 1.35, 2);
            $descuento     = rand(55, 75);
            $precioFinal   = round($precioInflado * (1 - $descuento / 100), 2);

            return response()->json([
                'success'  => true,
                'categoria' => [
                    'id_categoria'    => $catSuperior->id_categoria,
                    'codigo'          => $catSuperior->codigo,
                    'nombre'          => $catSuperior->nombre,
                    'descripcion'     => $catSuperior->descripcion,
                    'precio_real'     => $catSuperior->precio_dia,
                    'precio_inflado'  => $precioInflado,
                    'descuento'       => $descuento,
                    'precio_final'    => $precioFinal,
                    'imagen'          => $foto,
                    'nombre_vehiculo' => $vehiculo->nombre_publico,
                    'transmision'     => $vehiculo->transmision,
                    'asientos'        => $vehiculo->asientos,
                    'puertas'         => $vehiculo->puertas,
                    'color'           => $vehiculo->color,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Error obtenerOfertaUpgrade: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Error interno'], 500);
        }
    }

    public function aceptarUpgrade(Request $request, int $idReservacion)
    {
        try {
            $data = $request->validate([
                'id_categoria' => 'required|integer|exists:categorias_carros,id_categoria',
            ]);

            $cat = DB::table('categorias_carros')->where('id_categoria', $data['id_categoria'])->first();

            DB::table('reservaciones')->where('id_reservacion', $idReservacion)->update([
                'id_categoria'      => $cat->id_categoria,
                'tarifa_base'       => $cat->precio_dia,
                'tarifa_ajustada'   => 0,
                'tarifa_modificada' => null,
                'id_vehiculo'       => null,
                'updated_at'        => now(),
            ]);

            return response()->json([
                'success'      => true,
                'msg'          => 'Upgrade aplicado correctamente.',
                'tarifa_base'  => number_format($cat->precio_dia, 2),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error aceptarUpgrade: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }

    public function rechazarUpgrade(int $idReservacion)
    {
        try {
            $idContrato = DB::table('contratos')->where('id_reservacion', $idReservacion)->value('id_contrato');

            DB::table('contrato_evento')->insert([
                'id_contrato'  => $idContrato,
                'evento'       => 'Upgrade rechazado',
                'detalle'      => json_encode([]),
                'realizado_en' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            return response()->json(['success' => true, 'msg' => 'Oferta rechazada.']);
        } catch (\Throwable $e) {
            Log::error('Error rechazarUpgrade: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno'], 500);
        }
    }

    // ─────────────────────────────────────────────
    // DOCUMENTACIÓN
    // ─────────────────────────────────────────────

    public function guardarDocumentacion(Request $request)
    {
        try {
            $idContrato      = $request->id_contrato;
            $idReservacion   = $request->id_reservacion;
            $conductoresData = $request->input('conductores');

            if (!$conductoresData || !is_array($conductoresData)) {
                return response()->json(['error' => 'No se recibieron datos de conductores.'], 422);
            }

            // Validar teléfono de emergencia en conductores adicionales
            foreach ($conductoresData as $c) {
                $esTitular = isset($c['es_titular']) && $c['es_titular'] == '1';
                if (!$esTitular && !empty($c['contacto_emergencia'])) {
                    if (!preg_match('/^\d{10}$/', trim($c['contacto_emergencia']))) {
                        $nombre = $c['nombre'] ?? 'un conductor adicional';
                        return response()->json([
                            'error' => "El teléfono de emergencia de {$nombre} debe tener exactamente 10 dígitos.",
                        ], 422);
                    }
                }
            }

            foreach ($conductoresData as $idx => $c) {
                $idArchivoFrente  = $this->guardarArchivo($request->file("conductores.$idx.idFrente"));
                $idArchivoReverso = $this->guardarArchivo($request->file("conductores.$idx.idReverso"));
                $idLicFrente      = $this->guardarArchivo($request->file("conductores.$idx.licFrente"));
                $idLicReverso     = $this->guardarArchivo($request->file("conductores.$idx.licReverso"));

                $idConductor = $c['id_conductor'] ?? null;
                $esTitular   = isset($c['es_titular']) && $c['es_titular'] == '1';

                if ($esTitular) {
                    DB::table('reservaciones')->where('id_reservacion', $idReservacion)->update([
                        'nombre_cliente'    => $c['nombre'],
                        'apellidos_cliente' => trim(($c['apellido_paterno'] ?? '') . ' ' . ($c['apellido_materno'] ?? '')),
                        'updated_at'        => now(),
                    ]);

                    DB::table('contrato_documento')
                        ->where('id_contrato', $idContrato)
                        ->where('id_conductor', null)
                        ->where('tipo', 'identificacion')
                        ->update(['contacto_emergencia' => $c['contacto_emergencia'] ?? null]);
                } else {
                    $datos = [
                        'nombres'          => $c['nombre'],
                        'apellidos'        => trim(($c['apellido_paterno'] ?? '') . ' ' . ($c['apellido_materno'] ?? '')),
                        'numero_licencia'  => $c['numero_licencia'] ?? null,
                        'pais_licencia'    => $c['id_pais'] ?? null,
                        'fecha_nacimiento' => $c['fecha_nacimiento'] ?? null,
                        'contacto'         => $c['contacto_emergencia'] ?? null,
                        'updated_at'       => now(),
                    ];

                    if (empty($idConductor)) {
                        $idConductor = DB::table('contrato_conductor_adicional')
                            ->insertGetId(array_merge($datos, ['id_contrato' => $idContrato, 'created_at' => now()]));
                    } else {
                        DB::table('contrato_conductor_adicional')->where('id_conductor', $idConductor)->update($datos);
                    }
                }

                $docIdentExistente = DB::table('contrato_documento')
                    ->where('id_contrato', $idContrato)
                    ->where('id_conductor', $idConductor)
                    ->where('tipo', 'identificacion')
                    ->first();

                $docLicExistente = DB::table('contrato_documento')
                    ->where('id_contrato', $idContrato)
                    ->where('id_conductor', $idConductor)
                    ->where('tipo', 'licencia')
                    ->first();

                // ── Identificación ──
                DB::table('contrato_documento')->updateOrInsert(
                    ['id_contrato' => $idContrato, 'id_conductor' => $idConductor, 'tipo' => 'identificacion'],
                    [
                        'tipo_identificacion'   => $c['tipo_identificacion'] ?? 'ine',
                        'numero_identificacion' => $c['numero_identificacion'] ?? null,
                        'nombre'                => $c['nombre'] ?? null,
                        'apellido_paterno'      => $c['apellido_paterno'] ?? null,
                        'apellido_materno'      => $c['apellido_materno'] ?? null,
                        'fecha_nacimiento'      => $c['fecha_nacimiento'] ?? null,
                        'fecha_vencimiento'     => $c['fecha_vencimiento_id'] ?? $c['fecha_vencimiento'] ?? null,
                        // Si se subió archivo, se usa; si no, se conserva el existente; si no hay ninguno, null
                        'id_archivo_frente'     => $idArchivoFrente  ?? ($docIdentExistente->id_archivo_frente ?? null),
                        'id_archivo_reverso'    => $idArchivoReverso ?? ($docIdentExistente->id_archivo_reverso ?? null),
                        'updated_at'            => now(),
                    ]
                );

                // ── Licencia ──
                DB::table('contrato_documento')->updateOrInsert(
                    ['id_contrato' => $idContrato, 'id_conductor' => $idConductor, 'tipo' => 'licencia'],
                    [
                        'numero_identificacion' => $c['numero_licencia'] ?? null,
                        'pais_emision'          => $c['id_pais'] ?? 'MX',
                        'fecha_emision'         => $c['fecha_emision'] ?? null,
                        'fecha_vencimiento'     => $c['fecha_vencimiento'] ?? null,
                        'id_archivo_frente'     => $idLicFrente  ?? ($docLicExistente->id_archivo_frente ?? null),
                        'id_archivo_reverso'    => $idLicReverso ?? ($docLicExistente->id_archivo_reverso ?? null),
                        'updated_at'            => now(),
                    ]
                );
            }

            return response()->json(['success' => true, 'msg' => 'Documentación guardada correctamente.']);
        } catch (\Exception $e) {
            Log::error('ERROR guardarDocumentacion: ' . $e->getMessage() . ' — Línea: ' . $e->getLine());
            return response()->json(['error' => 'Error interno al guardar.', 'detalle' => $e->getMessage()], 500);
        }
    }

    public function obtenerDocumentacion(int $idContrato)
    {
        try {
            $urlArchivo = fn($id) => $id ? route('archivo.mostrar', ['id' => $id]) : null;

            $todosDocumentos = DB::table('contrato_documento')
                ->where('id_contrato', $idContrato)
                ->get()
                ->groupBy('id_conductor');

            $buildDocs = function ($ident, $lic) use ($urlArchivo): array {
                $vencida = $lic && $lic->fecha_vencimiento && Carbon::parse($lic->fecha_vencimiento)->isPast();

                return [
                    'campos' => [
                        'tipo_identificacion'        => $ident->tipo_identificacion ?? null,
                        'numero_identificacion'      => $ident->numero_identificacion ?? null,
                        'nombre'                     => $ident->nombre ?? null,
                        'apellido_paterno'           => $ident->apellido_paterno ?? null,
                        'apellido_materno'           => $ident->apellido_materno ?? null,
                        'fecha_nacimiento'           => $ident->fecha_nacimiento ?? null,
                        'fecha_vencimiento_id'       => $ident->fecha_vencimiento ?? null,
                        'numero_licencia'            => $lic->numero_identificacion ?? null,
                        'id_pais'                    => $lic->pais_emision ?? null,
                        'fecha_emision'              => $lic->fecha_emision ?? null,
                        'fecha_vencimiento'          => $lic->fecha_vencimiento ?? null,
                        'contacto_emergencia'        => $ident->contacto_emergencia ?? null,
                    ],
                    'archivos' => [
                        'idFrente_url'   => $ident ? $urlArchivo($ident->id_archivo_frente) : null,
                        'idReverso_url'  => $ident ? $urlArchivo($ident->id_archivo_reverso) : null,
                        'licFrente_url'  => $lic   ? $urlArchivo($lic->id_archivo_frente) : null,
                        'licReverso_url' => $lic   ? $urlArchivo($lic->id_archivo_reverso) : null,
                    ],
                    'licencia_vencida' => $vencida,
                ];
            };

            // Titular
            $docsTitular = $todosDocumentos->get(null, collect());
            $titular     = $buildDocs(
                $docsTitular->firstWhere('tipo', 'identificacion'),
                $docsTitular->firstWhere('tipo', 'licencia')
            );

            // Adicionales
            $conductores    = DB::table('contrato_conductor_adicional')
                ->where('id_contrato', $idContrato)->get()->keyBy('id_conductor');

            $adicionalesData = [];

            foreach ($todosDocumentos as $idConductor => $grupo) {
                if ($idConductor === null) continue;

                $c = $conductores->get($idConductor);
                if (!$c) continue;

                $ident = $grupo->firstWhere('tipo', 'identificacion');
                $lic   = $grupo->firstWhere('tipo', 'licencia');
                $entry = $buildDocs($ident, $lic);

                // Completar campos del conductor adicional
                $entry['campos']['nombre']             = $entry['campos']['nombre']    ?? $c->nombres;
                $entry['campos']['fecha_nacimiento']   = $entry['campos']['fecha_nacimiento'] ?? $c->fecha_nacimiento;
                $entry['campos']['numero_licencia']    = $entry['campos']['numero_licencia'] ?? $c->numero_licencia;
                $entry['campos']['pais_emision']       = $entry['campos']['pais_emision'] ?? $c->pais_licencia;
                $entry['campos']['contacto_emergencia'] = $c->contacto;

                $adicionalesData[$idConductor] = $entry;
            }

            return response()->json([
                'success'    => true,
                'documentos' => ['titular' => $titular, 'adicionales' => $adicionalesData],
            ]);
        } catch (\Throwable $e) {
            Log::error('ERROR obtenerDocumentacion: ' . $e->getMessage());
            return response()->json(['success' => false, 'msg' => 'Error interno al obtener documentación'], 500);
        }
    }

    public function verificarDocumentosExistentes(int $idContrato)
    {
        try {
            return response()->json([
                'success' => true,
                'existen' => DB::table('contrato_documento')->where('id_contrato', $idContrato)->exists(),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'existen' => false, 'msg' => 'Error verificando documentos'], 500);
        }
    }

    public function obtenerConductores(int $idContrato)
    {
        try {
            return response()->json(
                DB::table('contrato_conductor_adicional')
                    ->where('id_contrato', $idContrato)
                    ->select('id_conductor', 'nombres', 'apellidos')
                    ->orderBy('id_conductor')
                    ->get()
            );
        } catch (\Throwable $e) {
            Log::error('Error obtenerConductores: ' . $e->getMessage());
            return response()->json(['error' => 'Error al obtener los conductores adicionales.'], 500);
        }
    }

    // ─────────────────────────────────────────────
    // RESUMEN PASO 6
    // ─────────────────────────────────────────────

    public function resumenPaso6(int $idReservacion)
    {
        try {
            $res = DB::table('reservaciones')->where('id_reservacion', $idReservacion)->first();
            if (!$res) return response()->json(['ok' => false, 'msg' => 'No encontrada']);

            $dias   = max(1, Carbon::parse($res->fecha_inicio)->diffInDays(Carbon::parse($res->fecha_fin)));
            $tarifa = ($res->tarifa_modificada > 0) ? $res->tarifa_modificada : $res->tarifa_base;
            $baseTotal = $tarifa * $dias;

            $nombresExcluidos = ['Gasolina Prepago', 'Drop Off', 'Dropoff', 'Delivery', 'Servicio de Delivery', 'Prepaid fuel'];

            $listaAdicionales = [];
            $totalServicios   = 0;

            DB::table('reservacion_servicio as rs')
                ->join('servicios as s', 'rs.id_servicio', '=', 's.id_servicio')
                ->where('rs.id_reservacion', $idReservacion)
                ->whereNotIn('s.nombre', $nombresExcluidos)
                ->select('s.nombre', 'rs.cantidad', 'rs.precio_unitario', 's.tipo_cobro')
                ->get()
                ->each(function ($s) use (&$listaAdicionales, &$totalServicios, $dias) {
                    $sub = $s->tipo_cobro === 'por_dia'
                        ? $s->cantidad * $s->precio_unitario * $dias
                        : $s->cantidad * $s->precio_unitario;
                    $totalServicios += $sub;
                    $listaAdicionales[] = ['nombre' => $s->nombre, 'cantidad' => $s->cantidad, 'total' => $sub];
                });

            $delivery = (float) ($res->delivery_total ?? 0);
            if ($res->delivery_activo && $delivery > 0) {
                $listaAdicionales[] = ['nombre' => 'Servicio de Delivery', 'cantidad' => 1, 'total' => $delivery];
            }

            $precioSeguros = (float) $this->calcularTotalProtecciones($idReservacion);

            $nombreSeguro = DB::table('reservacion_paquete_seguro as rps')
                ->join('seguro_paquete as p', 'rps.id_paquete', '=', 'p.id_paquete')
                ->where('rps.id_reservacion', $idReservacion)
                ->value('p.nombre') ?? 'Protecciones seleccionadas';

            $codigoCategoria = DB::table('categorias_carros')
                ->where('id_categoria', $res->id_categoria)->value('codigo') ?? 'C';

            $garantia  = $this->obtenerGarantiaSeguro($codigoCategoria, $nombreSeguro);
            $subtotal  = $baseTotal + $totalServicios + $delivery + $precioSeguros;
            $iva       = $subtotal * 0.16;
            $totalContrato = $subtotal + $iva;

            // Actualizar totales si cambiaron
            $actual = DB::table('reservaciones')
                ->where('id_reservacion', $idReservacion)
                ->first(['subtotal', 'impuestos', 'total']);

            if (!$actual || abs($actual->total - $totalContrato) > 0.01) {
                DB::table('reservaciones')->where('id_reservacion', $idReservacion)->update([
                    'subtotal'    => $subtotal,
                    'impuestos'   => $iva,
                    'total'       => $totalContrato,
                    'updated_at'  => now(),
                ]);
            }

            $totalPagado        = $this->pagosReservacionQuery($idReservacion)->sum('monto');
            $totalGarantiaPagada = $this->pagosGarantiaQuery($idReservacion)->sum('monto');
            $saldoPendiente     = $totalContrato - $totalPagado;
            $garantiaPendiente  = max(0, ($garantia['monto'] ?? 0) - $totalGarantiaPagada);

            $pagos = DB::table('pagos')
                ->where('id_reservacion', $idReservacion)
                ->where('estatus', 'paid')
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => [
                    'base'       => ['total' => $baseTotal, 'descripcion' => "{$dias} días · $" . number_format($tarifa, 2)],
                    'adicionales' => ['lista' => $listaAdicionales, 'total' => $totalServicios + $delivery],
                    'totales'    => [
                        'nombre_seguro'   => $nombreSeguro,
                        'monto_seguros'   => $precioSeguros,
                        'subtotal'        => $subtotal,
                        'iva'             => $iva,
                        'total_contrato'  => $totalContrato,
                        'saldo_pendiente' => $saldoPendiente,
                        'garantia'        => $garantia,
                    ],
                    'pagos' => [
                        'realizados' => $totalPagado,
                        'saldo'      => $saldoPendiente,
                        'garantia'   => ['realizados' => $totalGarantiaPagada, 'pendiente' => $garantiaPendiente],
                        'lista'      => $pagos,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }

    // ─────────────────────────────────────────────
    // PAGOS
    // ─────────────────────────────────────────────

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
                'origen'         => 'nullable|string|max:50',
                'comprobante'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            ]);

            $filePath = $request->hasFile('comprobante')
                ? $request->file('comprobante')->store('pagos', 'public')
                : null;

            DB::beginTransaction();

            $idPago = DB::table('pagos')->insertGetId([
                'id_reservacion'  => $data['id_reservacion'],
                'id_contrato'     => null,
                'origen_pago'     => strtolower($data['origen'] ?? 'mostrador'),
                'metodo'          => strtoupper($data['metodo'] ?? 'EFECTIVO'),
                'tipo_pago'       => $data['tipo_pago'],
                'monto'           => $data['monto'],
                'moneda'          => 'MXN',
                'estatus'         => 'paid',
                'comprobante'     => $filePath,
                'payload_webhook' => json_encode([
                    'ultimos4' => $data['ultimos4'] ?? null,
                    'auth'     => $data['auth']     ?? null,
                    'notas'    => $data['notas']    ?? null,
                ]),
                'captured_at' => now(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $this->sincronizarEstadoPago($data['id_reservacion']);

            DB::commit();

            return response()->json(['ok' => true, 'id_pago' => $idPago]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error agregarPagoPaso6: ' . $e->getMessage());
            return response()->json(['ok' => false, 'msg' => 'Error interno al guardar el pago'], 500);
        }
    }

    public function pagoPayPal(Request $request)
    {
        $request->validate([
            'id_reservacion' => 'required|integer',
            'order_id'       => 'required|string',
            'monto'          => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();

        try {
            $res = DB::table('reservaciones')->where('id_reservacion', $request->id_reservacion)->first();
            if (!$res) return response()->json(['ok' => false, 'msg' => 'Reservación no encontrada'], 404);

            $tipoPago = strtoupper(trim($request->input('tipo_pago', 'PAGO RESERVACION')));

            $idPago = DB::table('pagos')->insertGetId([
                'id_reservacion'      => $request->id_reservacion,
                'id_contrato'         => null,
                'origen_pago'         => 'online',
                'pasarela'            => 'paypal',
                'referencia_pasarela' => $request->order_id,
                'estatus'             => 'paid',
                'metodo'              => 'PayPal',
                'tipo_pago'           => $tipoPago,
                'monto'               => $request->monto,
                'moneda'              => 'MXN',
                'payload_webhook'     => null,
                'captured_at'         => now(),
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);

            DB::table('reservaciones')
                ->where('id_reservacion', $request->id_reservacion)
                ->update(['paypal_order_id' => $request->order_id]);

            $this->sincronizarEstadoPago($request->id_reservacion);

            DB::commit();

            return response()->json(['ok' => true, 'msg' => 'Pago registrado', 'id_pago' => $idPago]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['ok' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function pagoManual(Request $request)
    {
        $request->validate([
            'id_reservacion' => 'required|integer|exists:reservaciones,id_reservacion',
            'tipo_pago'      => 'required|string|max:50',
            'metodo'         => 'required|string|max:50',
            'monto'          => 'required|numeric|min:1',
            'notas'          => 'nullable|string|max:500',
            'comprobante'    => 'nullable|file|mimes:jpg,jpeg,png,pdf',
        ]);

        DB::beginTransaction();

        try {
            $origenFront = strtolower($request->input('origen', ''));
            $origen = match (true) {
                str_contains($origenFront, 'linea')        => 'PayPal',
                str_contains($origenFront, 'paypal')       => 'PayPal',
                str_contains($origenFront, 'terminal')     => 'Terminal',
                str_contains($origenFront, 'transferencia'),
                str_contains($origenFront, 'deposito')     => 'Transferencia / Depósito',
                default                                    => 'Efectivo',
            };

            $filePath = $request->hasFile('comprobante')
                ? $request->file('comprobante')->store('pagos', 'public')
                : null;

            $idPago = DB::table('pagos')->insertGetId([
                'id_reservacion'  => $request->id_reservacion,
                'id_contrato'     => null,
                'origen_pago'     => $origen,
                'metodo'          => strtoupper($request->metodo),
                'tipo_pago'       => strtoupper(trim($request->tipo_pago)),
                'monto'           => $request->monto,
                'moneda'          => 'MXN',
                'estatus'         => 'paid',
                'comprobante'     => $filePath,
                'payload_webhook' => json_encode(['notas' => $request->notas]),
                'captured_at'     => now(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            $this->sincronizarEstadoPago($request->id_reservacion);

            DB::commit();

            return response()->json(['ok' => true, 'id_pago' => $idPago]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error pagoManual: ' . $e->getMessage());
            return response()->json(['ok' => false, 'msg' => 'Error backend: ' . $e->getMessage()]);
        }
    }

    public function eliminarPago(int $idPago)
    {
        try {
            $pago = DB::table('pagos')->where('id_pago', $idPago)->first();
            if (!$pago) return response()->json(['success' => false, 'msg' => 'Pago no encontrado'], 404);

            DB::transaction(function () use ($idPago, $pago) {
                DB::table('pagos')->where('id_pago', $idPago)->delete();
                $this->sincronizarEstadoPago($pago->id_reservacion);
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────
    // FINALIZAR CONTRATO
    // ─────────────────────────────────────────────

    public function finalizar(int $idReservacion)
    {
        try {
            abort_unless(
                DB::table('reservaciones')->where('id_reservacion', $idReservacion)->exists(),
                404,
                'Reservación no encontrada.'
            );

            $contratoExistente = DB::table('contratos')->where('id_reservacion', $idReservacion)->first();

            if ($contratoExistente) {
                return redirect()->route('contrato.final', $contratoExistente->id_contrato);
            }

            $idContrato = $this->crearContrato($idReservacion);

            return redirect()->route('contrato.final', $idContrato);
        } catch (\Exception $e) {
            Log::error('Error en finalizar Contrato2: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al finalizar contrato.');
        }
    }

    // ─────────────────────────────────────────────
    // TARIFA Y CORTESÍAS
    // ─────────────────────────────────────────────

    public function editarTarifa(Request $request, int $idReservacion)
    {
        $nuevoValor = $request->tarifa_modificada;

        if (!$nuevoValor || $nuevoValor <= 0) {
            return response()->json(['ok' => false, 'msg' => 'Tarifa inválida']);
        }

        try {
            DB::table('reservaciones')
                ->where('id_reservacion', $idReservacion)
                ->update(['tarifa_modificada' => $nuevoValor]);

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'msg' => $e->getMessage()], 500);
        }
    }

    public function editarCortesia(Request $request, int $idReservacion)
    {
        $horas = (int) $request->cortesias;

        if (!in_array($horas, [1, 2, 3])) {
            return response()->json(['ok' => false, 'msg' => 'Las horas de cortesía deben ser 1, 2 o 3']);
        }

        try {
            DB::table('reservaciones')
                ->where('id_reservacion', $idReservacion)
                ->update(['horas_cortesia' => $horas]);

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'msg' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────
    // UTILIDADES
    // ─────────────────────────────────────────────

    public function obtenerClienteContrato(int $idContrato)
    {
        $contrato = DB::table('contratos')
            ->join('reservaciones', 'reservaciones.id_reservacion', '=', 'contratos.id_reservacion')
            ->where('contratos.id_contrato', $idContrato)
            ->first(['reservaciones.nombre_cliente', 'reservaciones.apellidos_cliente']);

        return response()->json($contrato);
    }

    public function status(int $idReservacion)
    {
        return response()->json([
            'existe' => DB::table('contratos')->where('id_reservacion', $idReservacion)->exists(),
        ]);
    }

    // ─────────────────────────────────────────────
    // VALIDACIÓN DE DOCUMENTOS
    // ─────────────────────────────────────────────

    public function validarDocumentoMaestro(Request $request)
    {
        $request->validate([
            'tipo'              => 'nullable|string|max:20',
            'numero'            => 'nullable|string|max:50',
            'id_pais'           => 'nullable|string|max:20',
            'fecha_nacimiento'  => 'nullable|date',
            'fecha_emision'     => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        $tipo      = strtolower($request->input('tipo', 'licencia'));
        $numeroRaw = $request->input('numero');
        $numero    = strtoupper(trim($numeroRaw));

        $pais = match (strtoupper(trim($request->input('id_pais', '')))) {
            '1', 'MX', 'MEXICO', 'MÉXICO'                          => 'MX',
            '2', 'US', 'USA', 'U.S.A.', 'U.S.A', 'UNITED STATES'  => 'US',
            '3', 'BR', 'BRASIL', 'BRAZIL'                          => 'BR',
            '4', 'CO', 'COLOMBIA'                                   => 'CO',
            '5', 'CA', 'CANADA', 'CANADÁ'                          => 'CA',
            default                                                 => 'MX',
        };

        $res = ['success' => true, 'status' => 'ok', 'status_edad' => 'adulto', 'edad' => 0, 'msg' => []];

        $prioridad = ['ok' => 0, 'warning' => 1, 'invalido' => 2, 'vencido' => 3, 'error_fecha' => 4, 'prohibido' => 5];
        $setStatus = function (string $nuevo) use (&$res, $prioridad) {
            if ($prioridad[$nuevo] > $prioridad[$res['status']]) {
                $res['status'] = $nuevo;
            }
        };

        $hoy = Carbon::now()->startOfDay();

        if (!$numero) {
            $setStatus('invalido');
            $res['msg'][] = 'Número de documento requerido.';
        }

        try {
            $nacimiento = $request->fecha_nacimiento ? Carbon::parse($request->fecha_nacimiento) : null;
            $emision    = $request->fecha_emision    ? Carbon::parse($request->fecha_emision)    : null;
            $vence      = $request->fecha_vencimiento ? Carbon::parse($request->fecha_vencimiento) : null;
        } catch (\Exception) {
            $setStatus('error_fecha');
            $res['msg'][] = 'Formato de fecha inválido.';
            return response()->json($res);
        }

        if ($nacimiento) {
            if ($nacimiento->isAfter($hoy)) {
                $setStatus('error_fecha');
                $res['msg'][] = 'Fecha de nacimiento futura.';
            } else {
                $edad = $nacimiento->age;
                $res['edad'] = $edad;

                if ($edad < 18) {
                    $setStatus('prohibido');
                    $res['status_edad'] = 'prohibido';
                    $res['msg'][] = 'Debe ser mayor de 18 años.';
                    return response()->json($res);
                }

                if ($edad < 24) {
                    $res['status_edad'] = 'menor';
                    $res['msg'][] = 'Conductor joven.';
                }
            }
        }

        if ($emision && $emision->isAfter($hoy)) {
            $setStatus('error_fecha');
            $res['msg'][] = 'Fecha de emisión futura inválida.';
        }

        if ($nacimiento && $emision && $nacimiento->diffInYears($emision) < 16) {
            $setStatus('prohibido');
            $res['msg'][] = 'Licencia emitida siendo menor de edad.';
        }

        $esPermanente = !$vence || ($vence && $vence->year >= 2090);

        if (!$esPermanente && $vence && $vence->isBefore($hoy)) {
            $setStatus('vencido');
            $res['msg'][] = 'Documento expirado.';
        }

        if ($emision && $vence) {
            if ($vence->lte($emision)) {
                $setStatus('error_fecha');
                array_unshift($res['msg'], 'El vencimiento no puede ser anterior a la emisión.');
            } elseif (!$esPermanente) {
                $duracion = round($emision->floatDiffInYears($vence));

                [$inusual, $fatal, $motivo] = match ($pais) {
                    'MX' => $duracion > 3  ? [true,  false, 'Máximo 3 años permitidos.']          : [false, false, ''],
                    'CA' => $duracion != 5 ? [true,  true,  'Debe ser exactamente de 5 años.']    : [false, false, ''],
                    'US' => $duracion > 8  ? [true,  true,  'Máximo 8 años permitidos.']          : [false, false, ''],
                    'CO' => $duracion > 10 ? [true,  true,  'Máximo 10 años permitidos.']         : [false, false, ''],
                    'BR' => (function () use ($nacimiento, $emision, $duracion) {
                        $edadEmision = ($nacimiento && $emision) ? $nacimiento->diffInYears($emision) : 30;
                        $limite      = $edadEmision >= 50 ? 5 : 10;
                        return $duracion > $limite
                            ? [true, true, "Emitida a los {$edadEmision} años, máximo {$limite} años."]
                            : [false, false, ''];
                    })(),
                    default => [false, false, ''],
                };

                if ($inusual) {
                    $setStatus($fatal ? 'error_fecha' : 'warning');
                    $res['msg'][] = ($fatal ? 'Vigencia inválida' : 'Vigencia inusual') . " para {$pais}: {$motivo}";
                } elseif ($duracion < 1) {
                    $setStatus('warning');
                    $res['msg'][] = 'Vigencia demasiado corta (menor a 1 año).';
                }
            }
        }

        // Validación del número
        if ($numero) {
            $alfanumerico = preg_replace('/[^A-Z0-9]/', '', $numero);
            $soloNumeros  = preg_replace('/[^\d]/', '', $numero);

            $valido   = true;
            $errorMsg = '';

            if (preg_match('/^(.)\1+$/', $alfanumerico) || in_array($alfanumerico, ['123456789', '1234567890', '0123456789', '987654321'])) {
                $valido   = false;
                $errorMsg = 'Número sospechoso o de prueba.';
            }

            if ($valido) {
                switch ($tipo) {
                    case 'ine':
                    case 'ife':
                        $regexCURP = '/^[A-Z]{4}\d{2}(0[1-9]|1[0-2])(0[1-9]|[12]\d|3[01])[HM](AS|BC|BS|CC|CL|CM|CS|CH|DF|DG|GT|GR|HG|JC|MC|MN|MS|NT|NL|OC|PL|QT|QR|SP|SL|SR|TC|TS|TL|VZ|YN|ZS|NE)[A-Z]{3}[A-Z0-9]\d$/';
                        $esCURP    = preg_match($regexCURP, $alfanumerico);
                        $esClave   = preg_match('/^[A-Z]{6}\d{8}[HM]\d{3}$/', $alfanumerico);

                        if (!$esCURP && !$esClave) {
                            $valido   = false;
                            $errorMsg = strlen($alfanumerico) !== 18
                                ? 'El CURP o Clave debe tener exactamente 18 caracteres.'
                                : 'Formato de CURP/Clave inválido (revisa fecha, estado o día).';
                        }

                        if ($esCURP && $nacimiento && substr($alfanumerico, 4, 6) !== $nacimiento->format('ymd')) {
                            $setStatus('warning');
                            $res['msg'][] = 'La fecha de nacimiento no coincide con el CURP ingresado.';
                        }
                        break;

                    case 'pasaporte':
                        if (strlen($alfanumerico) < 6 || strlen($alfanumerico) > 15 || preg_match('/^(.)\1+$/', $alfanumerico)) {
                            $valido = false;
                            $errorMsg = 'El pasaporte es inválido o demasiado corto.';
                        }
                        break;

                    case 'cedula':
                        if (!preg_match('/^\d{7,8}$/', $numero)) {
                            $valido   = false;
                            $errorMsg = 'La Cédula Profesional debe ser de 7 u 8 números.';
                        }
                        break;

                    default: // licencia
                        $valido = match ($pais) {
                            'MX' => (bool) preg_match('/^[A-Z]{3}\d{5,7}$/', $alfanumerico),
                            'US' => (bool) preg_match('/^[A-Z]\d{7}$/', $alfanumerico),
                            'CA' => (bool) preg_match('/^[A-Z]\d{4}-\d{5}-\d{4}$/', $numeroRaw),
                            'CO' => (bool) preg_match('/^\d{7,12}$/', $soloNumeros),
                            'BR' => (bool) preg_match('/^\d{9,11}$/', $soloNumeros),
                            default => strlen($alfanumerico) >= 5,
                        };

                        if (!$valido) $errorMsg = "El número [{$numero}] no cumple reglas de [{$pais}]";
                        break;
                }
            }

            if (!$valido) {
                $setStatus('invalido');
                array_unshift($res['msg'], $errorMsg);
            }
        }

        return response()->json($res);
    }

    // ─────────────────────────────────────────────
    // FIRMA DEL CLIENTE
    // ─────────────────────────────────────────────

    public function guardarFirmaCliente(Request $request)
    {
        try {
            $request->validate([
                'id_contrato'    => 'required|integer|exists:contratos,id_contrato',
                'firma'          => 'required|string',
                'lugar_estancia' => 'required|string|max:255',
            ]);

            DB::table('contratos')
                ->where('id_contrato', $request->id_contrato)
                ->update(['firma_cliente' => $request->firma, 'updated_at' => now()]);

            DB::table('contrato_estancias')->updateOrInsert(
                ['id_contrato' => $request->id_contrato],
                ['lugar_estancia' => $request->lugar_estancia, 'updated_at' => now()]
            );

            return response()->json(['ok' => true, 'msg' => 'Firma y lugar de estancia guardados correctamente.']);
        } catch (\Exception $e) {
            Log::error('Error al guardar firma: ' . $e->getMessage());
            return response()->json(['ok' => false, 'msg' => 'Error interno al guardar los datos.'], 500);
        }
    }

    // ─────────────────────────────────────────────
    // BUSCAR CLIENTE
    // ─────────────────────────────────────────────

    public function buscarPersona(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $personas = DB::table('contrato_documento')
            ->where('tipo', 'identificacion')
            ->where(function ($query) use ($q) {
                $query->where('nombre', 'like', "%{$q}%")
                    ->orWhere('apellido_paterno', 'like', "%{$q}%")
                    ->orWhere('apellido_materno', 'like', "%{$q}%")
                    ->orWhere('numero_identificacion', 'like', "%{$q}%");
            })
            ->select(
                'nombre',
                'apellido_paterno',
                'apellido_materno',
                'numero_identificacion',
                'tipo_identificacion',
                'fecha_nacimiento',
                'fecha_vencimiento as fecha_vencimiento_id',
                'pais_emision',
                'contacto_emergencia',
                'id_contrato',
                'id_conductor',
                'id_archivo_frente',
                'id_archivo_reverso'
            )
            ->limit(10)
            ->get();

        return response()->json($personas);
    }
}
