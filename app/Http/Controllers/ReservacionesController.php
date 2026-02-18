<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelPdf\Facades\Pdf;          // << Spatie PDF
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreCotizacionRequest;

class ReservacionesController extends Controller
{
    /**
     * Entrada principal:
     * - Home/Welcome (filtros completos)  -> Paso 2 (categorías)
     * - Catálogo (vehiculo_id / alias)   -> Paso 3 (auto preseleccionado) o Paso 2
     */
    public function iniciar(Request $request)
    {
        // --- 1) Leer parámetros (acepta alias desde Catálogo) ---
        $vehiculoId = $request->input('vehiculo_id');

        $pickupDateRaw  = $request->input('pickup_date')  ?? $request->input('start');
        $dropoffDateRaw = $request->input('dropoff_date') ?? $request->input('end');

        $pickupTimeRaw  = $request->input('pickup_time');
        $dropoffTimeRaw = $request->input('dropoff_time');

        $pickupSucursalId  = $request->input('pickup_sucursal_id')  ?? $request->input('location');
        $dropoffSucursalId = $request->input('dropoff_sucursal_id') ?? $request->input('location');

        // ✅ categoria_id será la selección del Paso 2
        $categoriaId = $request->input('categoria_id') ?? $request->input('type');
        $plan        = $request->input('plan');

        // Normalizar fechas/horas--------
        $pickupDate  = $this->normalizeDateYmd($pickupDateRaw);
        $dropoffDate = $this->normalizeDateYmd($dropoffDateRaw);
        $pickupTime  = $this->normalizeTime($pickupTimeRaw);
        $dropoffTime = $this->normalizeTime($dropoffTimeRaw);

        if (!$pickupDate || !$dropoffDate) {
            $defaults    = $this->defaultDates();
            $pickupDate  = $pickupDate  ?: $defaults['pickup_date']; // --- fechas fijas por defecto ---
            $dropoffDate = $dropoffDate ?: $defaults['dropoff_date'];
        }
        if (!$pickupTime || !$dropoffTime) {
            $pickupTime  = $pickupTime  ?: '12:00';
            $dropoffTime = $dropoffTime ?: '12:00';
        }

        // --- 2) Si viene vehiculo_id, completar datos desde BD ---
        $vehiculo = null;
        if ($vehiculoId) {
            $vehiculo = DB::table('vehiculos as v')
                ->leftJoin('vehiculo_imagenes as vi', function ($j) {
                    $j->on('vi.id_vehiculo', '=', 'v.id_vehiculo')->where('vi.orden', 1);
                })
                ->leftJoin('sucursales as s', 's.id_sucursal', '=', 'v.id_sucursal')
                ->leftJoin('categorias_carros as c', 'c.id_categoria', '=', 'v.id_categoria')
                ->selectRaw("
                    v.*,
                    s.nombre as sucursal_nombre,
                    c.nombre as categoria_nombre,
                    COALESCE(vi.url, '') as img_url
                ")
                ->where('v.id_vehiculo', $vehiculoId)
                ->first();

            if (!$vehiculo) {
                return redirect()->route('rutaCatalogo')
                    ->withErrors(['catalogo' => 'El vehículo seleccionado no existe o no está disponible.']);
            }

            // Completar filtros si faltaban
            $pickupSucursalId  = $pickupSucursalId  ?: $vehiculo->id_sucursal;
            $dropoffSucursalId = $dropoffSucursalId ?: $vehiculo->id_sucursal;
            $categoriaId       = $categoriaId       ?: $vehiculo->id_categoria;
        }

        // --- 3) Datos para selects (panel de edición) ---
        $ciudades = DB::table('ciudades')
            ->select('id_ciudad', 'nombre', 'estado', 'pais')

            ->orderBy('nombre')
            ->get()
            ->map(function ($c) {
                $c->sucursalesActivas = DB::table('sucursales')
                    ->select('id_sucursal', 'id_ciudad', 'nombre')
                    ->where('id_ciudad', $c->id_ciudad)
                    ->where('activo', true)
                    ->orderBy('nombre')
                    ->get();
                return $c;
            });

        /**
         * ✅ CATEGORÍAS con:
         * - precio_dia (desde categorias_carros)
         * - imagen representativa (tomando 1 vehículo disponible por categoría)
         * - specs (chips) desde el vehículo ejemplo
         *
         * Si no hay vehículos disponibles en una categoría, img_url quedará vacío.
         */
        $categorias = DB::table('categorias_carros as c')
            // 1) Tomar 1 vehículo disponible por categoría (vehículo ejemplo)
            ->leftJoinSub(
                DB::table('vehiculos')
                    ->selectRaw('MIN(id_vehiculo) as id_vehiculo, id_categoria')
                    ->where('id_estatus', 1) // disponible
                    ->groupBy('id_categoria'),
                'vx',
                'vx.id_categoria',
                '=',
                'c.id_categoria'
            )
            // 2) Traer datos del vehículo ejemplo
            ->leftJoin('vehiculos as v', 'v.id_vehiculo', '=', 'vx.id_vehiculo')
            // 3) Traer imagen principal del vehículo ejemplo
            ->leftJoin('vehiculo_imagenes as vi', function ($j) {
                $j->on('vi.id_vehiculo', '=', 'v.id_vehiculo')
                    ->where('vi.orden', 1);
            })
            ->selectRaw("
                c.id_categoria,
                c.nombre,
                COALESCE(c.precio_dia, 0) as precio_dia,

                COALESCE(vi.url, '') as img_url,

                COALESCE(v.marca, '')  as marca,
                COALESCE(v.modelo, '') as modelo,

                COALESCE(v.transmision, 'Manual o automática') as transmision,
                COALESCE(v.asientos, 0) as pasajeros,

                -- defaults (si luego los haces reales en BD, los cambiamos)
                2 as maletas_chicas,
                1 as maletas_grandes,

                1 as apple_carplay,
                1 as android_auto
            ")
            ->orderBy('c.id_categoria', 'asc')
            ->get();

        $sucursales = DB::table('sucursales')
            ->select('id_sucursal', 'nombre')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        // --- 4) Filtros estándar para la vista ---
        $filters = [
            'pickup_sucursal_id'  => $pickupSucursalId,
            'dropoff_sucursal_id' => $dropoffSucursalId,
            'pickup_date'         => $pickupDate,
            'pickup_time'         => $pickupTime,
            'dropoff_date'        => $dropoffDate,
            'dropoff_time'        => $dropoffTime,
            'categoria_id'        => $categoriaId,
            'plan' => $plan,
        ];

        // ======================================================
// ✅ NUEVO CONTROL DE FLUJO (POR CATEGORÍA)
// ======================================================

// Step solicitado por URL
$requestedStep = (int) $request->query('step', 0);

// Validar rango
if ($requestedStep < 1 || $requestedStep > 4) {
    $requestedStep = 0;
}

// Flags del flujo actual
$hasCategoria = !empty($categoriaId);
$hasPlan      = !empty($plan);

// Decidir paso correcto
if ($requestedStep === 1) {
    $step = 1;
} elseif ($requestedStep === 2) {
    $step = 2;
} elseif ($requestedStep === 3) {
    // Complementos requieren categoría + plan
    $step = ($hasCategoria && $hasPlan) ? 3 : 2;
} elseif ($requestedStep === 4) {
    // 🔥 RESUMEN YA NO REQUIERE vehiculo_id
    $step = ($hasCategoria && $hasPlan) ? 4 : 2;
} else {
    // Sin step explícito
    if ($hasCategoria && $hasPlan) {
        $step = 3;
    } else {
        $step = 2;
    }
}

// Ya no listamos vehículos (flujo por categoría)
$vehiculos = collect();


        // --- 6) Complementos (SERVICIOS) — SIEMPRE cargar ---
        $servicios = $this->obtenerServiciosActivos();

        return view('Usuarios.Reservaciones', compact(
            'step',
            'filters',
            'vehiculos',
            'vehiculo',
            'sucursales',
            'categorias',
            'ciudades',
            'servicios'
        ));
    }

    /**
     * Paso 1 desde la navbar (form vacío con defaults).
     */
    public function desdeNavbar(Request $request)
    {
        $ciudades   = $this->getCiudadesConSucursalesActivas();
        $categorias = $this->getCategorias();

        // Defaults hoy 12:00 → +3 días 12:00
        $now    = Carbon::now('America/Mexico_City');
        $pickup = (clone $now)->setTime(12, 0);
        $drop   = (clone $pickup)->addDays(3);

        $filters = [
            'pickup_sucursal_id'  => null,
            'dropoff_sucursal_id' => null,
            'pickup_date'         => $pickup->format('Y-m-d'),
            'pickup_time'         => '12:00',
            'dropoff_date'        => $drop->format('Y-m-d'),
            'dropoff_time'        => '12:00',
            'categoria_id'        => null,
        ];

        // ✅ Entrar mostrando categorías (Paso 2)
        $step      = 2;
        $vehiculos = collect();
        $vehiculo  = null;

        // Complementos listos por si avanzan
        $servicios = $this->obtenerServiciosActivos();

        return view('Usuarios.Reservaciones', compact(
            'step',
            'filters',
            'vehiculos',
            'vehiculo',
            'ciudades',
            'categorias',
            'servicios'
        ));
    }

    /* ===================== NUEVO: generar PDF + guardar en archivos + enviar WhatsApp ===================== */

    public function cotizar(StoreCotizacionRequest $request)
{
    Log::info('🟢 Datos recibidos en cotizar:', $request->all());

    // 1️⃣ Validación: ahora trabajamos por CATEGORÍA, no por vehículo
    $validated = $request->validated();

    // 2️⃣ Buscar la CATEGORÍA y opcionalmente un vehículo ejemplo para la ficha
    $vehiculo = DB::table('categorias_carros as c')
        ->leftJoin('vehiculos as v', 'v.id_categoria', '=', 'c.id_categoria')
        ->leftJoin('vehiculo_imagenes as vi', function ($j) {
            $j->on('vi.id_vehiculo', '=', 'v.id_vehiculo')->where('vi.orden', 1);
        })
        ->leftJoin('sucursales as s', 's.id_sucursal', '=', 'v.id_sucursal')
        ->selectRaw("
            c.id_categoria,
            c.nombre       as categoria_nombre,
            c.precio_dia   as precio_dia,
            v.id_vehiculo,
            v.marca,
            v.modelo,
            s.nombre       as sucursal_nombre,
            COALESCE(vi.url, '') as img_url
        ")
        ->where('c.id_categoria', $request->categoria_id)
        ->first();

    if (!$vehiculo) {
        return response()->json(['ok' => false, 'message' => 'Categoría no encontrada.'], 404);
    }

    // 3️⃣ Fechas
    $pickupDate  = $request->pickup_date;
    $pickupTime  = $request->pickup_time;
    $dropoffDate = $request->dropoff_date;
    $dropoffTime = $request->dropoff_time;

    $d1   = Carbon::createFromFormat('Y-m-d H:i', "{$pickupDate} {$pickupTime}");
    $d2   = Carbon::createFromFormat('Y-m-d H:i', "{$dropoffDate} {$dropoffTime}");
    $days = max(1, $d1->diffInDays($d2));

    // 4️⃣ Sucursales (si no se envían, usamos la de la categoría / vehículo ejemplo)
    $pickupName  = DB::table('sucursales')->where('id_sucursal', $request->pickup_sucursal_id)->value('nombre');
    $dropoffName = DB::table('sucursales')->where('id_sucursal', $request->dropoff_sucursal_id)->value('nombre');

    $pickupName  = $pickupName  ?: ($vehiculo->sucursal_nombre ?? 'Sucursal por definir');
    $dropoffName = $dropoffName ?: ($vehiculo->sucursal_nombre ?? 'Sucursal por definir');

    // 5️⃣ Servicios adicionales
    $addonsQty = $request->input('addons', []);
    $addons    = [];

    if (!empty($addonsQty)) {
        $addonsRows = DB::table('servicios')
            ->select('id_servicio', 'nombre', 'tipo_cobro', 'precio')
            ->whereIn('id_servicio', array_keys($addonsQty))
            ->get()
            ->keyBy('id_servicio');

        foreach ($addonsQty as $id => $qty) {
            $qty = (int)$qty;
            if ($qty <= 0) continue;

            $row = $addonsRows->get((int)$id);
            if (!$row) continue;

            $isPerDay = ($row->tipo_cobro === 'por_dia');
            $subtotalAddon = (float)$row->precio * ($isPerDay ? $days : 1) * $qty;

            $addons[] = [
                'id'       => (int)$id,
                'name'     => $row->nombre,
                'charge'   => $row->tipo_cobro,
                'price'    => (float)$row->precio,
                'qty'      => $qty,
                'subtotal' => $subtotalAddon,
            ];
        }
    }

    // 6️⃣ Totales (tarifa base por CATEGORÍA)
    $tarifaBase = (float)$vehiculo->precio_dia * $days;
    $extrasSub  = array_sum(array_column($addons, 'subtotal'));
    $subtotal   = $tarifaBase + $extrasSub;
    $iva        = round($subtotal * 0.16, 2);
    $total      = $subtotal + $iva;

    // 7️⃣ Folio y cliente
    $folio = 'COT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

    $cliente = [
        'nombre'   => $request->input('nombre', ''),
        'email'    => $request->input('email', ''),
        'telefono' => $request->input('telefono', ''),
    ];

    // 8️⃣ Guardar solo los datos (sin PDF)
    $idCotizacion = DB::table('cotizaciones')->insertGetId([
        'folio'              => $folio,
        'vehiculo_id'        => $vehiculo->id_vehiculo ?? null,      // puede ser null
        'vehiculo_marca'     => $vehiculo->marca ?? '',               // puede venir vacío
        'vehiculo_modelo'    => $vehiculo->modelo ?? '',
        'vehiculo_categoria' => $vehiculo->categoria_nombre ?? '',
        'pickup_date'        => $pickupDate,
        'pickup_time'        => $pickupTime,
        'pickup_name'        => $pickupName,
        'dropoff_date'       => $dropoffDate,
        'dropoff_time'       => $dropoffTime,
        'dropoff_name'       => $dropoffName,
        'days'               => $days,
        'tarifa_base'        => $tarifaBase,
        'extras_sub'         => $extrasSub,
        'iva'                => $iva,
        'total'              => $total,
        'addons'             => json_encode($addons, JSON_UNESCAPED_UNICODE),
        'cliente'            => json_encode($cliente, JSON_UNESCAPED_UNICODE),
        'created_at'         => now(),
        'updated_at'         => now(),
    ]);

    // 9️⃣ Notificación (sin PDF adjunto)
    //    $vehiculo aquí representa categoría + posible vehículo ejemplo
    $this->sendWhatsappToAgent(
        $folio,
        $vehiculo,
        $pickupName,
        $dropoffName,
        $days,
        $total,
        ''
    );

    return response()->json([
        'ok'            => true,
        'folio'         => $folio,
        'cotizacion_id' => $idCotizacion,
    ]);
}


    /* ===================== HELPERS ===================== */

    /** Listado de vehículos disponibles según filtros básicos. */
    private function listarVehiculosDisponibles(array $filters)
    {
        $q = DB::table('vehiculos as v')
            ->leftJoin('vehiculo_imagenes as vi', function ($j) {
                $j->on('vi.id_vehiculo', '=', 'v.id_vehiculo')
                    ->where('vi.orden', 1);
            })
            ->leftJoin('categorias_carros as c', 'c.id_categoria', '=', 'v.id_categoria')
            ->leftJoin('sucursales as s', 's.id_sucursal', '=', 'v.id_sucursal')
            ->selectRaw("
                v.id_vehiculo,
                v.nombre_publico,
                v.marca,
                v.modelo,
                v.anio,
                v.transmision,
                v.asientos,
                v.puertas,
                v.precio_dia,
                v.descripcion,
                v.id_categoria,
                c.nombre as categoria_nombre,
                s.nombre as sucursal_nombre,
                COALESCE(vi.url, '') as img_url
            ")
            ->where('v.id_estatus', 1); // Disponible

        if (!empty($filters['categoria_id'])) {
            $q->where('v.id_categoria', (int)$filters['categoria_id']);
        }

        // ✅ Orden por categoría ID, luego marca/modelo
        return $q->orderBy('c.id_categoria', 'asc')
            ->orderBy('v.marca')
            ->orderBy('v.modelo')
            ->get();
    }

    /** Ciudades con sus sucursales activas (para poblar los <select>). */
    private function getCiudadesConSucursalesActivas()
    {
        return DB::table('ciudades')
            ->select('id_ciudad', 'nombre', 'estado', 'pais')
            ->orderBy('nombre')
            ->get()
            ->map(function ($c) {
                $c->sucursalesActivas = DB::table('sucursales')
                    ->select('id_sucursal', 'id_ciudad', 'nombre')
                    ->where('id_ciudad', $c->id_ciudad)
                    ->where('activo', true)
                    ->orderBy('nombre')
                    ->get();
                return $c;
            });
    }

    /** Catálogos de categorías (simple). */
    private function getCategorias()
    {
        return DB::table('categorias_carros')
            ->select('id_categoria', 'nombre')
            ->orderBy('id_categoria', 'asc')
            ->get();
    }

    /** Complementos activos (servicios) para Paso 3. */
    private function obtenerServiciosActivos()
    {
        return DB::table('servicios')
            ->select('id_servicio', 'nombre', 'descripcion', 'tipo_cobro', 'precio')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
    }

    /* --------- Normalizadores de fecha/hora --------- */

    private function normalizeDateYmd(?string $date): ?string
    {
        if (!$date) return null;
        $date = trim($date);

        // dd/mm/YYYY → Y-m-d
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $date)) {
            [$d, $m, $y] = array_map('intval', explode('/', $date));
            return sprintf('%04d-%02d-%02d', $y, $m, $d);
        }

        // Y-m-d (válido)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        // fallback
        $ts = strtotime($date);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    private function normalizeTime(?string $time): ?string
    {
        if (!$time) return null;
        $time = trim($time);

        // HH:MM (24h)
        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            return $time;
        }

        // Intentar parsear 12h "h:i am/pm"
        $ts = strtotime($time);
        return $ts ? date('H:i', $ts) : null;
    }

    private function mergeDateTime(string $date, string $time): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i', "{$date} {$time}", 'America/Mexico_City');
    }

    private function defaultDates(): array
    {
        $now    = Carbon::now('America/Mexico_City');
        $pickup = (clone $now)->setTime(12, 0);
        $drop   = (clone $pickup)->addDays(3);

        return [
            'pickup_date'  => $pickup->format('Y-m-d'),
            'dropoff_date' => $drop->format('Y-m-d'),
        ];
    }

    /* ===================== Helper privado: WhatsApp Cloud API ===================== */
    private function sendWhatsappToAgent(string $folio, $vehiculo, string $pickupName, string $dropoffName, int $days, float $total, string $pdfUrl): void
    {
        try {
            $token   = config('services.whatsapp.token', env('WA_TOKEN'));
            $phoneId = config('services.whatsapp.phone_id', env('WA_PHONE_ID'));
            $to      = env('AGENTE_WA', '5214427169793'); // E.164 sin '+'

            if (!$token || !$phoneId || !$to) {
                return;
            }

            $body = "Nueva cotización {$folio}\n"
                . "{$vehiculo->marca} {$vehiculo->modelo} ({$vehiculo->categoria_nombre})\n"
                . "Renta: {$days} día(s) | Entrega: {$pickupName} | Devolución: {$dropoffName}\n"
                . "Total estimado: $" . number_format($total, 0) . " MXN\n"
                . "PDF: {$pdfUrl}";

            Http::withToken($token)
                ->post("https://graph.facebook.com/v18.0/{$phoneId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to'   => $to,
                    'type' => 'text',
                    'text' => ['body' => $body],
                ]);
        } catch (\Throwable $e) {
            // Silencioso
        }
    }
}
