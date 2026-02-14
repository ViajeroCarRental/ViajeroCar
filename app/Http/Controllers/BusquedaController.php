<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; // 👈 NUEVO

class BusquedaController extends Controller
{
    /* ====== HOME (sin modelos) ====== */
    public function home()
    {
        // Ciudades
    $ciudadesBase = DB::table('ciudades')
    ->select('id_ciudad','nombre','estado','pais')
    ->orderByRaw("CASE WHEN nombre = 'Querétaro' THEN 0 ELSE 1 END")
    ->orderBy('nombre')
    ->get();


        // Agregar sucursales activas a cada ciudad
        $ciudades = $ciudadesBase->map(function ($c) {
            $c->sucursalesActivas = DB::table('sucursales')
                ->select('id_sucursal','id_ciudad','nombre')
                ->where('id_ciudad', $c->id_ciudad)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();
            return $c;
        });

        // Categorías de autos
        $categorias = DB::table('categorias_carros')
            ->select('id_categoria','nombre')
            ->orderBy('nombre')
            ->get();

        /* ==========================
         *  GOOGLE MAPS – RESEÑAS
         * ========================== */
        $googleReviews = collect();
        $googleRating  = null;
        $googleTotal   = null;

        try {
            // 🔑 Llave y place_id desde .env (los configuramos en el siguiente paso)
            $apiKey  = env('GOOGLE_PLACES_KEY');
            $placeId = env('GOOGLE_VIAJERO_PLACE_ID');

            if ($apiKey && $placeId) {
                $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
                    'place_id' => $placeId,
                    'fields'   => 'rating,reviews,user_ratings_total',
                    'language' => 'es',
                    'key'      => $apiKey,
                ]);

                if ($response->ok() && $response->json('status') === 'OK') {
                    $googleReviews = collect($response->json('result.reviews', []))->take(4);
                    $googleRating  = $response->json('result.rating');
                    $googleTotal   = $response->json('result.user_ratings_total');
                }
            }
        } catch (\Throwable $e) {
            // Opcional: puedes loguear el error si quieres
            // \Log::error('Error al obtener reseñas de Google: '.$e->getMessage());
        }

        // Renderiza tu vista (welcome/home)
        return view('welcome', [
            'ciudades'      => $ciudades,
            'categorias'    => $categorias,
            'googleReviews' => $googleReviews,
            'googleRating'  => $googleRating,
            'googleTotal'   => $googleTotal,
        ]);
    }

    /* ====== BUSCAR (valida y redirige a Reservaciones) ====== */
    public function buscar(Request $request)
    {
        // 1) Normaliza FECHAS desde Flatpickr (rango en pickup_date)
        [$start, $end] = $this->splitRangeFromPickup($request->input('pickup_date'));

        // Si dropoff_date viene vacío o en formato humano, lo corregimos:
        $dropRaw = $request->input('dropoff_date');
        if (!$dropRaw && $end) {
            $dropRaw = $end; // tomar del rango
        }

        // Estándar esperado: Y-m-d
        $pickupDate  = $this->normalizeDateYmd($start ?: $request->input('pickup_date'));
        $dropoffDate = $this->normalizeDateYmd($dropRaw);

        // Hacemos merge para que el validator ya reciba limpios
        $request->merge([
            'pickup_date'  => $pickupDate,
            'dropoff_date' => $dropoffDate,
        ]);

        // 2) Validación ya con valores normalizados
        $validated = $request->validate([
            'pickup_sucursal_id'  => 'required|exists:sucursales,id_sucursal',
            'dropoff_sucursal_id' => 'required|exists:sucursales,id_sucursal',
            'pickup_date'         => 'required|date_format:Y-m-d',
            'pickup_time'         => 'required|string',
            'dropoff_date'        => 'required|date_format:Y-m-d',
            'dropoff_time'        => 'required|string',
            'categoria_id'        => 'nullable|exists:categorias_carros,id_categoria',
        ]);

        // 3) Arma DateTimes
        $pickupAt  = $this->mergeDateTime($validated['pickup_date'],  $validated['pickup_time']);
        $dropoffAt = $this->mergeDateTime($validated['dropoff_date'], $validated['dropoff_time']);

        if ($dropoffAt <= $pickupAt) {
            return back()
                ->withErrors(['dropoff_date' => 'La fecha/hora de devolución no puede ser anterior a la de entrega.'])
                ->withInput();
        }

        // 4) Redirección al flujo de reservaciones
        $params = [
            'pickup_sucursal_id'  => $validated['pickup_sucursal_id'],
            'dropoff_sucursal_id' => $validated['dropoff_sucursal_id'],
            'pickup_date'         => $validated['pickup_date'],
            'pickup_time'         => $validated['pickup_time'],
            'dropoff_date'        => $validated['dropoff_date'],
            'dropoff_time'        => $validated['dropoff_time'],
        ];

        if (!empty($validated['categoria_id'])) {
            $params['categoria_id'] = $validated['categoria_id'];
        }

        // 🔹 Cambio aquí: redirige al nuevo controlador de Reservaciones
        return redirect()->route('rutaReservasIniciar', $params);
    }

    /** Split "YYYY-MM-DD a YYYY-MM-DD" / "YYYY-MM-DD to YYYY-MM-DD" */
    private function splitRangeFromPickup(?string $pickup): array
    {
        if (!$pickup) return [null, null];
        // soporta " a " (es), " to " (en)
        if (preg_match('/^\s*(\d{4}-\d{2}-\d{2})\s+(?:a|to)\s+(\d{4}-\d{2}-\d{2})\s*$/i', $pickup, $m)) {
            return [$m[1], $m[2]];
        }
        // también si llegó en formato humano "dd/mm/YYYY a dd/mm/YYYY"
        if (preg_match('/^\s*(\d{1,2}\/\d{1,2}\/\d{4})\s+(?:a|to)\s+(\d{1,2}\/\d{1,2}\/\d{4})\s*$/i', $pickup, $m)) {
            return [$this->normalizeDateYmd($m[1]), $this->normalizeDateYmd($m[2])];
        }
        return [$pickup, null];
    }

    /** Acepta "YYYY-MM-DD" o "dd/mm/YYYY" y devuelve "YYYY-MM-DD" */
    private function normalizeDateYmd(?string $date): ?string
    {
        if (!$date) return null;
        $date = trim($date);

        // Si vino "YYYY-MM-DDTHH:MM" o "YYYY-MM-DD HH:MM", quédate solo con la fecha
        if (preg_match('/^(\d{4}-\d{2}-\d{2})[ T]/', $date, $m)) {
            $date = $m[1];
        }

        // dd/mm/YYYY -> Y-m-d
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $date)) {
            [$d,$m,$y] = array_map('intval', explode('/',$date));
            return sprintf('%04d-%02d-%02d', $y, $m, $d);
        }

        // Y-m-d ya válido
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        $ts = strtotime($date);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    /** Combina fecha + hora en formato Carbon, tolerando "12:00 PM", "12:00:00", etc. */
    private function mergeDateTime(string $date, string $time): \Illuminate\Support\Carbon
    {
        $date = trim($date);
        $time = trim($time);

        // Si por accidente vino la fecha dentro del campo de hora, quítala
        // ej. "2025-02-12 12:00" o "2025-02-12T12:00:00"
        $time = preg_replace('/^\d{4}-\d{2}-\d{2}[ T]/', '', $time);

        // Normaliza cualquier variante a 24h HH:MM (recorta segundos y maneja AM/PM)
        $ts = strtotime($time);
        if ($ts === false) {
            // fallback: intenta extraer HH:MM manualmente
            if (preg_match('/(\d{1,2}):(\d{2})/', $time, $m)) {
                $h = (int)$m[1]; $i = (int)$m[2];
                $time = sprintf('%02d:%02d', $h, $i);
            } else {
                // Como última instancia, fija 12:00 para no romper el flujo
                $time = '12:00';
            }
        } else {
            $time = date('H:i', $ts);
        }

        return \Illuminate\Support\Carbon::createFromFormat('Y-m-d H:i', "{$date} {$time}", 'America/Mexico_City');
    }

}
