<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;

class BusquedaController extends Controller
{
    /* ====== HOME ====== */
    public function home()
    {
        $ciudades = $this->getCiudadesConSucursales();
        $categorias = DB::table('categorias_carros')
            ->select('id_categoria', 'nombre')
            ->orderBy('nombre')
            ->get();

        [$googleReviews, $googleRating, $googleTotal] = $this->fetchGoogleReviews();

        return view('welcome', compact('ciudades', 'categorias', 'googleReviews', 'googleRating', 'googleTotal'));
    }

    /* ====== BUSCAR ====== */
    public function buscar(Request $request)
    {
        $this->normalizarFechasEnRequest($request);

        $validated = $request->validate([
            'pickup_sucursal_id'  => 'required|exists:sucursales,id_sucursal',
            'dropoff_sucursal_id' => 'required|exists:sucursales,id_sucursal',
            'pickup_date'         => 'required|date_format:Y-m-d',
            'pickup_time'         => 'required|string',
            'dropoff_date'        => 'required|date_format:Y-m-d',
            'dropoff_time'        => 'required|string',
            'categoria_id'        => 'nullable|exists:categorias_carros,id_categoria',
        ]);

        $pickupAt  = $this->mergeDateTime($validated['pickup_date'], $validated['pickup_time']);
        $dropoffAt = $this->mergeDateTime($validated['dropoff_date'], $validated['dropoff_time']);

        if ($dropoffAt <= $pickupAt) {
            return back()
                ->withErrors(['dropoff_date' => 'La fecha/hora de devolución no puede ser anterior a la de entrega.'])
                ->withInput();
        }

        $params = array_filter([
            'pickup_sucursal_id'  => $validated['pickup_sucursal_id'],
            'dropoff_sucursal_id' => $validated['dropoff_sucursal_id'],
            'pickup_date'         => $validated['pickup_date'],
            'pickup_time'         => $validated['pickup_time'],
            'dropoff_date'        => $validated['dropoff_date'],
            'dropoff_time'        => $validated['dropoff_time'],
            'categoria_id'        => $validated['categoria_id'] ?? null,
        ]);

        return redirect()->route('rutaReservasIniciar', $params);
    }

    /* ====== PRIVADOS ====== */

    /**
     * @return \Illuminate\Support\Collection<int, object{id_ciudad: int, nombre: string, estado: string, pais: string, sucursalesActivas: \Illuminate\Support\Collection}>
     */
    private function getCiudadesConSucursales()
    {
        // Obtener todas las ciudades ordenadas (Querétaro primero)
        $ciudades = DB::table('ciudades')
            ->select('id_ciudad', 'nombre', 'estado', 'pais')
            ->orderByRaw("CASE WHEN nombre = 'Querétaro' THEN 0 ELSE 1 END")
            ->orderBy('nombre')
            ->get();

        // Obtener todas las sucursales activas en una sola consulta
        $sucursales = DB::table('sucursales')
            ->select('id_sucursal', 'id_ciudad', 'nombre')
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->groupBy('id_ciudad');

        // Asignar manualmente las sucursales a cada ciudad
        foreach ($ciudades as $ciudad) {
            $ciudad->sucursalesActivas = $sucursales->get($ciudad->id_ciudad, collect());
        }

        return $ciudades;
    }

    /**
     * @return array{0: \Illuminate\Support\Collection, 1: float|null, 2: int|null}
     */
    private function fetchGoogleReviews(): array
    {
        return Cache::remember('google_reviews_home', 3600, function (): array {
            $apiKey  = config('services.google.places_key');
            $placeId = config('services.google.viajero_place_id');

            if (!$apiKey || !$placeId) {
                return [collect(), null, null];
            }

            try {
                /** @var Response $response */
                $response = Http::timeout(3)->get('https://maps.googleapis.com/maps/api/place/details/json', [
                    'place_id' => $placeId,
                    'fields'   => 'rating,reviews,user_ratings_total',
                    'language' => app()->getLocale(),
                    'key'      => $apiKey,
                ]);

                if ($response->successful() && $response->json('status') === 'OK') {
                    $reviews = collect($response->json('result.reviews', []))->take(4);
                    $rating  = $response->json('result.rating');
                    $total   = $response->json('result.user_ratings_total');

                    return [$reviews, $rating, $total];
                }
            } catch (\Throwable $e) {
                Log::warning('Google Places API error: ' . $e->getMessage());
            }

            return [collect(), null, null];
        });
    }

    private function normalizarFechasEnRequest(Request $request): void
    {
        [$start, $end] = $this->splitRangeFromPickup($request->input('pickup_date'));

        $dropRaw = $request->input('dropoff_date') ?: $end;

        $request->merge([
            'pickup_date'  => $this->normalizeDateYmd($start ?: $request->input('pickup_date')),
            'dropoff_date' => $this->normalizeDateYmd($dropRaw),
        ]);
    }

    /**
     * @return array{0: string|null, 1: string|null}
     */
    private function splitRangeFromPickup(?string $pickup): array
    {
        if (!$pickup) {
            return [null, null];
        }

        $pattern = '/^\s*(\S+)\s+(?:a|to)\s+(\S+)\s*$/i';
        if (preg_match($pattern, $pickup, $m)) {
            return [$this->normalizeDateYmd($m[1]), $this->normalizeDateYmd($m[2])];
        }

        return [$pickup, null];
    }

    private function normalizeDateYmd(?string $date): ?string
    {
        if (!$date) {
            return null;
        }

        $date = trim($date);

        if (preg_match('/^(\d{4}-\d{2}-\d{2})[ T]/', $date, $m)) {
            return $m[1];
        }

        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        $ts = strtotime($date);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    private function mergeDateTime(string $date, string $time): Carbon
    {
        $time = trim(preg_replace('/^\d{4}-\d{2}-\d{2}[ T]/', '', trim($time)));

        $ts = strtotime($time);
        $time = $ts !== false
            ? date('H:i', $ts)
            : (preg_match('/(\d{1,2}):(\d{2})/', $time, $m)
                ? sprintf('%02d:%02d', $m[1], $m[2])
                : '12:00');

        return Carbon::createFromFormat('Y-m-d H:i', "{$date} {$time}", 'America/Mexico_City');
    }
}