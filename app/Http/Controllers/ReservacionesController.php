<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\Pais;

class ReservacionesController extends Controller
{
    /**
     * Tipo de cambio MXN -> USD para mostrar precios al cliente.
     * Debe coincidir con el valor usado en JS (reservaciones.js, BtnReservaLinea.js)
     * y en BtnReservacionesController.
     */
    private const EXCHANGE_RATE = 20;

    /**
     * Multiplicador para el precio diario cuando el cliente paga en mostrador.
     * Debe coincidir con BtnReservacionesController para garantizar que
     * lo que ve el cliente en Step 2 sea exactamente lo que se guarda en BD.
     */
    private const MOSTRADOR_MULTIPLIER = 1.25;

    /**
     * Entrada principal del wizard de reservaciones.
     * - Home/Welcome (filtros completos)  -> Paso 2 (categorías)
     * - Catálogo (vehiculo_id / alias)    -> Paso 3 (auto preseleccionado) o Paso 2
     */
    public function iniciar(Request $request)
    {
        $reset = $request->input('reset') == '1';

        $pickupDateRaw  = $reset ? null : ($request->input('pickup_date')  ?? $request->input('start'));
        $dropoffDateRaw = $reset ? null : ($request->input('dropoff_date') ?? $request->input('end'));

        if (is_string($pickupDateRaw) && str_contains($pickupDateRaw, ' a ')) {
            [$pickupDateRaw, $dropoffDateRaw] = array_map('trim', explode(' a ', $pickupDateRaw, 2));
        }

        $pickupDateISO  = $this->normalizeDateYmd($pickupDateRaw);
        $dropoffDateISO = $this->normalizeDateYmd($dropoffDateRaw);

        $pTimeRaw = $request->input('pickup_time')
            ?? ($request->input('pickup_h') ? $request->input('pickup_h') . ':00' : null);
        $dTimeRaw = $request->input('dropoff_time')
            ?? ($request->input('dropoff_h') ? $request->input('dropoff_h') . ':00' : null);

        $pickupTime  = $reset ? null : $this->normalizeTime($pTimeRaw);
        $dropoffTime = $reset ? null : $this->normalizeTime($dTimeRaw);

        $pickupSucursalId  = $request->input('pickup_sucursal_id')  ?? $request->input('location');
        $dropoffSucursalId = $request->input('dropoff_sucursal_id') ?? $request->input('location');
        $dropoffSucursalId = empty($dropoffSucursalId) && !empty($pickupSucursalId)
            ? $pickupSucursalId
            : $dropoffSucursalId;

        $categoriaId = $request->input('categoria_id') ?? $request->input('type');
        $plan        = $request->input('plan');
        $addonsParam = $request->input('addons', '');
        $vehiculoId  = $request->input('vehiculo_id');

        $step1DataComplete =
            !empty($pickupSucursalId) && !empty($dropoffSucursalId) &&
            !empty($pickupDateISO)    && !empty($dropoffDateISO) &&
            !empty($pickupTime)       && !empty($dropoffTime);

        $requestedStep = (int) $request->input('step', 1);

        if ($reset || (!$step1DataComplete && $requestedStep > 1)) {
            $stepCurrent = 1;
        } else {
            $stepCurrent = $requestedStep;
        }

        if ($stepCurrent >= 3 && (empty($categoriaId) || empty($plan))) {
            $stepCurrent = 2;
        }

        $step = max(1, min(4, $stepCurrent));

        if ($request->get('from') === 'welcome') session(['from_welcome' => true]);
        if ($step1DataComplete) session()->forget('from_welcome');

        $fromWelcome = $request->get('from') === 'welcome' || session('from_welcome');
        if ($step == 1 && $step1DataComplete) $fromWelcome = false;

        $vehiculo = $this->obtenerVehiculoSeleccionado($vehiculoId);
        if ($vehiculoId && !$vehiculo) {
            return redirect()->route('rutaCatalogo')
                ->withErrors(['catalogo' => 'El vehículo seleccionado no existe.']);
        }

        if ($vehiculo) {
            $pickupSucursalId  = $pickupSucursalId  ?: $vehiculo->id_sucursal;
            $dropoffSucursalId = $dropoffSucursalId ?: $vehiculo->id_sucursal;
            $categoriaId       = $categoriaId       ?: $vehiculo->id_categoria;
        }

        $days              = $this->calcularDiasDeRenta($pickupDateISO, $pickupTime, $dropoffDateISO, $dropoffTime);
        $catalogos         = $this->obtenerCatalogos();
        $fechas            = $this->formatearFechas($pickupDateISO, $dropoffDateISO, $pickupTime, $dropoffTime);
        $detallesCategoria = $this->procesarCategoria($catalogos['categorias'], $categoriaId, $plan, $days);
        $detallesAddons    = $this->procesarAddons($addonsParam, $categoriaId, $days);

        $pickupName  = $pickupSucursalId
            ? optional($catalogos['sucursales']->get((int) $pickupSucursalId))->nombre
            : null;
        $dropoffName = $dropoffSucursalId
            ? optional($catalogos['sucursales']->get((int) $dropoffSucursalId))->nombre
            : null;

        $dropoffKm = $dropoffName
            ? (DB::table('ubicaciones_servicio')->where('destino', $dropoffName)->where('activo', true)->value('km') ?? 0)
            : 0;
        $costoKmCategoria = $categoriaId
            ? (DB::table('categoria_costo_km')->where('id_categoria', $categoriaId)->where('activo', true)->value('costo_km') ?? 0)
            : 0;

        $paises = Pais::where('activo', true)
            ->orderBy('prioritario', 'desc')
            ->orderBy('nombre', 'asc')
            ->get();

        $baseParams = array_filter([
            'pickup_sucursal_id'  => $pickupSucursalId,
            'dropoff_sucursal_id' => $dropoffSucursalId,
            'pickup_date'         => $fechas['pickupDate'],
            'pickup_time'         => $pickupTime,
            'dropoff_date'        => $fechas['dropoffDate'],
            'dropoff_time'        => $dropoffTime,
            'categoria_id'        => $categoriaId,
            'plan'                => $plan,
            'addons'              => $addonsParam,
        ], fn($v) => $v !== null && $v !== '');

        $filters = array_merge($baseParams, [
            'pickup_date'  => $fechas['pickupDate'],
            'dropoff_date' => $fechas['dropoffDate'],
        ]);

        // --- Variables para el paso 4 (limpieza de Blade) ---

        // Detección de aeropuerto
        $isAirport =
            (is_string($pickupName)  && str_contains(mb_strtolower($pickupName),  'aeropuerto')) ||
            (is_string($dropoffName) && str_contains(mb_strtolower($dropoffName), 'aeropuerto'));

        // Moneda y formato
        $isUSD = app()->getLocale() === 'en';
        $exchangeRate = self::EXCHANGE_RATE;

        $formatCurrency = function ($amountMXN) use ($isUSD) {
            return $isUSD
                ? '$' . number_format($amountMXN / self::EXCHANGE_RATE, 2) . ' USD'
                : '$' . number_format($amountMXN, 0) . ' MXN';
        };

        $tarifaBaseFormateada = $formatCurrency($detallesCategoria['tarifaBase'] ?? 0);

        $precioDiaOriginal   = (float) (optional($detallesCategoria['categoriaSel'])->precio_dia ?? 0);
        $precioDiaCalculado  = $plan === 'mostrador'
            ? round($precioDiaOriginal * self::MOSTRADOR_MULTIPLIER)
            : $precioDiaOriginal;
        $precioDiaFormateado = $formatCurrency($precioDiaCalculado);

        // Meses para el select de fecha de nacimiento
        $months3 = $isUSD
            ? ['01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec']
            : ['01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr', '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago', '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic'];

        $maxYear = date('Y') - 18;
        $minYear = $maxYear - 80;

        $capacidadTanque = $detallesAddons['capacidadTanque'] ?? 50.0;

        return view('Usuarios.Reservaciones', array_merge(compact(
            'step',
            'stepCurrent',
            'filters',
            'baseParams',
            'fromWelcome',
            'pickupSucursalId',
            'dropoffSucursalId',
            'pickupTime',
            'dropoffTime',
            'categoriaId',
            'plan',
            'vehiculo',
            'pickupName',
            'dropoffName',
            'days',
            'dropoffKm',
            'costoKmCategoria',
            'paises',
            'isUSD',
            'isAirport',
            'exchangeRate',
            'tarifaBaseFormateada',
            'precioDiaFormateado',
            'months3',
            'maxYear',
            'minYear',
            'formatCurrency',
            'capacidadTanque'
        ), $catalogos, $fechas, $detallesCategoria, $detallesAddons));
    }

    /**
     * Calcula los días de renta con tolerancia de 1 hora.
     * Si dropoff > pickup por más de 1 hora extra, suma un día.
     */
    private function calcularDiasDeRenta($pickupDateISO, $pickupTime, $dropoffDateISO, $dropoffTime)
    {
        if (!$pickupDateISO || !$pickupTime || !$dropoffDateISO || !$dropoffTime) return 1;

        try {
            $d1 = Carbon::createFromFormat('Y-m-d H:i', "{$pickupDateISO} {$pickupTime}");
            $d2 = Carbon::createFromFormat('Y-m-d H:i', "{$dropoffDateISO} {$dropoffTime}");
            $horasTotales = $d1->diffInHours($d2);
            $diasBase   = intdiv($horasTotales, 24);
            $horasExtra = $horasTotales % 24;

            return $horasExtra > 1 ? $diasBase + 1 : max(1, $diasBase);
        } catch (\Throwable $e) {
            return 1;
        }
    }

    private function obtenerVehiculoSeleccionado($vehiculoId)
    {
        if (!$vehiculoId) return null;

        return DB::table('vehiculos as v')
            ->leftJoin('vehiculo_imagenes as vi', function ($j) {
                $j->on('vi.id_vehiculo', '=', 'v.id_vehiculo')->where('vi.orden', 1);
            })
            ->leftJoin('sucursales as s', 's.id_sucursal', '=', 'v.id_sucursal')
            ->leftJoin('categorias_carros as c', 'c.id_categoria', '=', 'v.id_categoria')
            ->selectRaw("v.*, s.nombre as sucursal_nombre, c.nombre as categoria_nombre, COALESCE(vi.url, '') as img_url")
            ->where('v.id_vehiculo', $vehiculoId)
            ->first();
    }

    private function obtenerCatalogos()
    {
        $sucursales = DB::table('sucursales')
            ->select('id_sucursal', 'id_ciudad', 'nombre', 'ver_usuario')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(function ($suc) {
                $name = strtolower($suc->nombre);
                $suc->icon_class = match (true) {
                    str_contains($name, 'aeropuerto') => 'fa-solid fa-plane-departure',
                    str_contains($name, 'bus') || str_contains($name, 'autobuses') => 'fa-solid fa-bus',
                    str_contains($name, 'oficina') || str_contains($name, 'park')  => 'fa-solid fa-building',
                    default => 'fa-solid fa-location-dot',
                };
                return $suc;
            });

        // DROPOFF: todas las sucursales activas (ignora ver_usuario)
        $sucursalesPorCiudad = $sucursales->groupBy('id_ciudad');

        // PICKUP: solo sucursales visibles para usuario (ver_usuario = 1)
        $sucursalesPickupPorCiudad = $sucursales
            ->where('ver_usuario', true)
            ->groupBy('id_ciudad');

        // Ciudades para el DROPOFF (todas, con sus sucursales sin filtrar)
        $ciudadesDropoff = DB::table('ciudades')
            ->select('id_ciudad', 'nombre', 'estado', 'pais')
            ->get()
            ->map(function ($c) use ($sucursalesPorCiudad) {
                $c->sucursalesActivas = $sucursalesPorCiudad->get($c->id_ciudad, collect());
                return $c;
            })
            ->sortByDesc(fn($c) => $c->nombre === 'Querétaro')
            ->values();

        // Ciudades para el PICKUP (con sus sucursales filtradas por ver_usuario),
        // Querétaro primero
        $ciudadesPickup = DB::table('ciudades')
            ->select('id_ciudad', 'nombre', 'estado', 'pais')
            ->get()
            ->map(function ($c) use ($sucursalesPickupPorCiudad) {
                $c->sucursalesActivas = $sucursalesPickupPorCiudad->get($c->id_ciudad, collect());
                return $c;
            })
            ->sortByDesc(fn($c) => $c->nombre === 'Querétaro')
            ->values();

        $horasDropdown = collect(range(0, 23))->map(fn($i) => str_pad($i, 2, '0', STR_PAD_LEFT));

        $categorias = DB::table('categorias_carros as c')
            ->leftJoinSub(
                DB::table('vehiculos')
                    ->selectRaw('MIN(id_vehiculo) as id_vehiculo, id_categoria')
                    ->where('id_estatus', 1)
                    ->groupBy('id_categoria'),
                'vx',
                'vx.id_categoria',
                '=',
                'c.id_categoria'
            )
            ->leftJoin('vehiculos as v', 'v.id_vehiculo', '=', 'vx.id_vehiculo')
            ->leftJoin('vehiculo_imagenes as vi', function ($j) {
                $j->on('vi.id_vehiculo', '=', 'v.id_vehiculo')->where('vi.orden', 1);
            })
            ->selectRaw("
                c.id_categoria, c.nombre, c.descripcion, c.precio_dia, c.codigo,
                1 as aire_ac, 1 as apple_carplay, 1 as android_auto,
                COALESCE(vi.url, '') as img_url
            ")
            ->orderBy('c.id_categoria', 'asc')
            ->get();

        $serviciosRaw = $this->obtenerServiciosActivos();
        $capacidad = request('categoria_id')
            ? (float) DB::table('vehiculos')->where('id_categoria', request('categoria_id'))->max('capacidad_tanque')
            : 50.0;

        $serviciosProcesados = $this->preprocesarServicios($serviciosRaw, $capacidad);

        // Servicios para el catálogo de cálculo (incluye los automáticos como el ID 5),
        // sin el filtro 'usuario'. Se procesan igual para tener precio/formato coherentes.
        $serviciosCalculoRaw = $this->obtenerServiciosParaCalculo();
        $serviciosCalculo    = $this->preprocesarServicios($serviciosCalculoRaw, $capacidad);

        return [
            'sucursales'         => $sucursales->keyBy('id_sucursal'),
            'ciudadesPickup'     => $ciudadesPickup,
            'ciudadesDropoff'    => $ciudadesDropoff,
            'horasDropdown'      => $horasDropdown,
            'isDifferentDropoff' => request('different_dropoff') == '1',
            'categorias'         => $categorias,
            'servicios'          => $serviciosProcesados,
            'serviciosCalculo'   => $serviciosCalculo,
        ];
    }

    private function formatearFechas($pickupDateISO, $dropoffDateISO, $pickupTime, $dropoffTime)
    {
        Carbon::setLocale('es');
        $ph = $pm = $dh = $dm = '';
        if ($pickupTime)  [$ph, $pm] = array_pad(explode(':', $pickupTime), 2, '00');
        if ($dropoffTime) [$dh, $dm] = array_pad(explode(':', $dropoffTime), 2, '00');

        return [
            'pickupDate'        => $pickupDateISO  ? Carbon::parse($pickupDateISO)->format('d-m-Y')  : '',
            'dropoffDate'       => $dropoffDateISO ? Carbon::parse($dropoffDateISO)->format('d-m-Y') : '',
            'pickupFechaLarga'  => $pickupDateISO  ? strtoupper(Carbon::parse($pickupDateISO)->translatedFormat('D d M Y'))  : null,
            'dropoffFechaLarga' => $dropoffDateISO ? strtoupper(Carbon::parse($dropoffDateISO)->translatedFormat('D d M Y')) : null,
            'ph' => $ph,
            'pm' => $pm,
            'dh' => $dh,
            'dm' => $dm,
        ];
    }

    private function procesarCategoria($categorias, $categoriaId, $plan, $days)
    {
        $specsMap = [
            1  => ['pax' => 5,  'small' => 2, 'big' => 1],
            2  => ['pax' => 5,  'small' => 2, 'big' => 1],
            3  => ['pax' => 5,  'small' => 2, 'big' => 2],
            4  => ['pax' => 5,  'small' => 2, 'big' => 2],
            5  => ['pax' => 5,  'small' => 2, 'big' => 2],
            6  => ['pax' => 5,  'small' => 3, 'big' => 2],
            7  => ['pax' => 7,  'small' => 3, 'big' => 2],
            8  => ['pax' => 7,  'small' => 4, 'big' => 2],
            9  => ['pax' => 13, 'small' => 4, 'big' => 3],
            10 => ['pax' => 5,  'small' => 3, 'big' => 2],
            11 => ['pax' => 5,  'small' => 3, 'big' => 2],
        ];

        $catImages = [
            1  => asset('img/aveo.webp'),
            2  => asset('img/virtus.webp'),
            3  => asset('img/jetta.webp'),
            4  => asset('img/camry.webp'),
            5  => asset('img/renegade.webp'),
            6  => asset('img/taos.webp'),
            7  => asset('img/avanza.webp'),
            8  => asset('img/Odyssey.webp'),
            9  => asset('img/Hiace.webp'),
            10 => asset('img/Frontier.webp'),
            11 => asset('img/Tacoma.webp'),
        ];

        $categoriasPreprocesadas = $categorias->map(function ($cat) use ($specsMap, $catImages, $days) {
            $prepagoDia   = (float) ($cat->precio_dia ?? 0);
            $mostradorDia = round($prepagoDia * self::MOSTRADOR_MULTIPLIER);

            $cat->prepago_total   = $prepagoDia * $days;
            $cat->mostrador_total = $mostradorDia * $days;
            $cat->ahorro_pct = $cat->mostrador_total > 0
                ? round((($cat->mostrador_total - $cat->prepago_total) / $cat->mostrador_total) * 100)
                : 0;

            $cat->img_url = $catImages[$cat->id_categoria]
                ?? (!empty($cat->img_url) ? $cat->img_url : asset('img/Logotipo.webp'));

            $spec = $specsMap[$cat->id_categoria] ?? ['pax' => 5, 'small' => 2, 'big' => 1];
            $cat->pax       = $spec['pax'];
            $cat->s_luggage = $spec['small'];
            $cat->b_luggage = $spec['big'];

            $cat->tiene_ac      = (bool) $cat->aire_ac;
            $cat->tiene_carplay = true;
            $cat->tiene_android = true;

            $cat->transmision_txt = $cat->id_categoria == 9 ? __('Manual') : __('Automatic');

            return $cat;
        });

        $categoriaSel  = $categoriaId
            ? $categoriasPreprocesadas->firstWhere('id_categoria', (int) $categoriaId)
            : null;
        $autoTitulo    = $categoriaSel ? ($categoriaSel->descripcion ?: __('Car or similar')) : __('Car or similar');
        $autoSubtitulo = $categoriaSel ? strtoupper($categoriaSel->nombre) : 'CATEGORY';
        $categoriaImg  = $categoriaSel ? $categoriaSel->img_url : asset('img/Logotipo.webp');
        $tarifaBase    = $categoriaSel
            ? ($plan === 'mostrador' ? $categoriaSel->mostrador_total : $categoriaSel->prepago_total)
            : 0.0;

        return [
            'categorias'    => $categoriasPreprocesadas,
            'categoriaSel'  => $categoriaSel,
            'autoTitulo'    => $autoTitulo,
            'autoSubtitulo' => $autoSubtitulo,
            'categoriaImg'  => $categoriaImg,
            'tarifaBase'    => $tarifaBase,
        ];
    }

    private function preprocesarServicios($servicios, $capacidadTanque)
    {
        $isUSD = app()->getLocale() === 'en';

        return collect($servicios)->map(function ($srv) use ($isUSD, $capacidadTanque) {
            $nombreLower = mb_strtolower($srv->nombre);

            $srv->icon = match (true) {
                str_contains($nombreLower, 'silla')     || str_contains($nombreLower, 'baby')   => 'fa-solid fa-child-reaching',
                str_contains($nombreLower, 'conductor') || str_contains($nombreLower, 'driver') => 'fa-solid fa-user-plus',
                str_contains($nombreLower, 'gasolina')  || str_contains($nombreLower, 'fuel')   => 'fa-solid fa-gas-pump',
                default => 'fa-solid fa-circle-plus',
            };

            $srv->tooltip = match (true) {
                str_contains($nombreLower, 'silla')     || str_contains($nombreLower, 'baby')   => __('Ideal for traveling with children. Subject to availability at the time of delivery.'),
                str_contains($nombreLower, 'conductor') || str_contains($nombreLower, 'driver') => __('Add an additional authorized driver to operate the vehicle during the rental.'),
                str_contains($nombreLower, 'gasolina')  || str_contains($nombreLower, 'fuel')   => __('Early flight? Don\'t waste time looking for a gas station. With Viajero Car Rental, you can prepay your fuel at a preferred rate per liter and return the vehicle directly. Simple, fast and stress-free.'),
                default => __('Check more information about this add-on.'),
            };

            $precioBase    = (float) $srv->precio;
            $precioMostrar = $precioBase;

            if (str_contains($nombreLower, 'gasolina') || str_contains($nombreLower, 'prepaid fuel')) {
                $capacidad     = $capacidadTanque ?: 50;
                $precioMostrar = $precioBase * $capacidad;
                $srv->unidad_txt          = __(' / tank');
                $srv->precio_total_tanque = $precioMostrar;
            } elseif (str_contains($nombreLower, 'additional driver')) {
                $srv->unidad_txt = __('driver per day');
            } else {
                $srv->unidad_txt = ($srv->tipo_cobro === 'por_dia') ? __(' / day') : __(' / event');
            }

            $montoFinal = $isUSD ? ($precioMostrar / self::EXCHANGE_RATE) : $precioMostrar;
            $srv->precio_formateado = '$' . number_format($montoFinal, ($isUSD ? 2 : 0));
            $srv->moneda_txt        = $isUSD ? 'USD' : 'MXN';

            return $srv;
        });
    }

    private function procesarAddons($addonsParam, $categoriaId, $days)
    {
        $addons      = [];
        $extrasTotal = 0;
        $capacidadTanque = $categoriaId
            ? (float) (DB::table('vehiculos')->where('id_categoria', $categoriaId)->where('id_estatus', 1)->max('capacidad_tanque') ?? 0)
            : 0;

        if ($capacidadTanque <= 0) {
            $capacidadTanque = 50.0;
        }

        if ($addonsParam) {
            $pairs = explode(',', $addonsParam);
            $ids   = [];
            foreach ($pairs as $pair) {
                if (str_contains($pair, ':')) $ids[] = (int) explode(':', $pair)[0];
            }

            $serviciosDB = DB::table('servicios')
                ->whereIn('id_servicio', $ids)
                ->where('usuario', true)
                ->get()
                ->keyBy('id_servicio');

            foreach ($pairs as $pair) {
                if (!str_contains($pair, ':')) continue;
                [$id, $qty] = array_map('intval', explode(':', $pair));

                if ($qty <= 0 || !($srv = $serviciosDB->get($id))) continue;

                $nombreLower = mb_strtolower(trim($srv->nombre));
                $esGasolina  = str_contains($nombreLower, 'gasolina') || str_contains($nombreLower, 'prepaid fuel');

                if ($esGasolina || $id === 1) {
                    // Gasolina prepago: cobro por tanque
                    $litros   = max(0, $capacidadTanque);
                    $subtotal = (float) $srv->precio * $litros;
                    $addons[] = [
                        'id'       => $id,
                        'nombre'   => $srv->nombre,
                        'qty'      => 1,
                        'precio'   => (float) $srv->precio,
                        'litros'   => $litros,
                        'subtotal' => $subtotal,
                    ];
                } else {
                    $tipoCobro = strtolower((string) ($srv->tipo_cobro ?? ''));
                    $subtotal  = $tipoCobro === 'por_dia'
                        ? (float) $srv->precio * $qty * $days
                        : (float) $srv->precio * $qty;
                    $addons[] = [
                        'id'       => $id,
                        'nombre'   => $srv->nombre,
                        'qty'      => $qty,
                        'precio'   => (float) $srv->precio,
                        'subtotal' => $subtotal,
                    ];
                }

                $extrasTotal += $subtotal;
            }
        }

        return compact('addons', 'extrasTotal', 'capacidadTanque');
    }

    /* =====================================================================
       HELPERS
       ===================================================================== */

    /** Servicios activos (complementos) para Paso 3. */
    private function obtenerServiciosActivos()
    {
        return DB::table('servicios')
            ->select('id_servicio', 'nombre', 'descripcion', 'tipo_cobro', 'precio')
            ->where('activo', true)
            ->where('usuario', true)
            ->orderBy('nombre')
            ->get();
    }

    /**
     * Servicios para CÁLCULO (catálogo del paso 4), SIN filtrar por 'usuario'.
     *
     * Incluye servicios que el sistema agrega automáticamente aunque no se
     * muestren como card en el paso 3 (ej. conductor menor de 25 = ID 5,
     * que se agrega por la fecha de nacimiento). Solo exige que estén activos.
     */
    private function obtenerServiciosParaCalculo()
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

        // dd-mm-YYYY o dd/mm/YYYY -> Y-m-d
        if (preg_match('/^\d{1,2}[\/\-]\d{1,2}[\/\-]\d{4}$/', $date)) {
            $parts = preg_split('/[\/\-]/', $date);
            return sprintf('%04d-%02d-%02d', (int) $parts[2], (int) $parts[1], (int) $parts[0]);
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        $ts = strtotime($date);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    private function normalizeTime(?string $time): ?string
    {
        if (!$time) return null;
        $time = trim($time);
        if (preg_match('/^\d{2}:\d{2}$/', $time)) return $time;
        $ts = strtotime($time);
        return $ts ? date('H:i', $ts) : null;
    }

    /**
     * Vista de políticas con formulario de búsqueda.
     */
    public function politicas()
    {
        $ciudades        = $this->obtenerCiudadesConSucursales();        // PICKUP: respeta ver_usuario
        $ciudadesDropoff = $this->obtenerCiudadesConSucursalesDropoff(); // DROPOFF: todas las activas (ignora ver_usuario)

        $filters = [
            'pickup_sucursal_id'  => null,
            'dropoff_sucursal_id' => null,
            'pickup_date'         => null,
            'pickup_time'         => null,
            'dropoff_date'        => null,
            'dropoff_time'        => null,
        ];

        return view('Usuarios.Politicas', [
            'ciudades'        => $ciudades,
            'ciudadesDropoff' => $ciudadesDropoff,
            'filters'         => $filters,
        ]);
    }

    private function obtenerCiudadesConSucursales()
    {
        // PICKUP: solo sucursales activas Y visibles para usuario (ver_usuario).
        // El flag lo controla el panel admin (Oficinas).
        $sucursalesPorCiudad = DB::table('sucursales')
            ->select('id_sucursal', 'id_ciudad', 'nombre')
            ->where('activo', true)
            ->where('ver_usuario', true)
            ->orderBy('nombre')
            ->get()
            ->groupBy('id_ciudad');

        return DB::table('ciudades')
            ->select('id_ciudad', 'nombre', 'estado', 'pais')
            ->orderByRaw("CASE WHEN nombre = 'Querétaro' THEN 0 ELSE 1 END")
            ->orderBy('nombre')
            ->get()
            ->map(function ($ciudad) use ($sucursalesPorCiudad) {
                $ciudad->sucursalesActivas = $sucursalesPorCiudad->get($ciudad->id_ciudad, collect());
                return $ciudad;
            });
    }

    /**
     * DROPOFF (políticas): todas las sucursales activas, SIN filtrar por ver_usuario.
     * El cliente puede devolver el auto en cualquier sucursal operativa.
     */
    private function obtenerCiudadesConSucursalesDropoff()
    {
        $sucursalesPorCiudad = DB::table('sucursales')
            ->select('id_sucursal', 'id_ciudad', 'nombre')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->groupBy('id_ciudad');

        return DB::table('ciudades')
            ->select('id_ciudad', 'nombre', 'estado', 'pais')
            ->orderBy('nombre')
            ->get()
            ->map(function ($ciudad) use ($sucursalesPorCiudad) {
                $ciudad->sucursalesActivas = $sucursalesPorCiudad->get($ciudad->id_ciudad, collect());
                return $ciudad;
            });
    }
}
