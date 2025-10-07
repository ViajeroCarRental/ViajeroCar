<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BusquedaController extends Controller
{
    /* ====== HOME (sin modelos) ====== */
    public function home()
    {
        // Ciudades
        $ciudadesBase = DB::table('ciudades')
            ->select('id_ciudad','nombre','estado','pais')
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

        // Renderiza tu vista (welcome/home)
        return view('welcome', compact('ciudades','categorias'));
    }

    /* ====== BUSCAR (valida y redirige a Catálogo) ====== */
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

        // 4) Redirección al catálogo con filtros
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

        return redirect()->route('rutaReservaciones', $params);
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
        // dd/mm/YYYY -> Y-m-d
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $date)) {
            [$d,$m,$y] = array_map('intval', explode('/',$date));
            return sprintf('%04d-%02d-%02d', $y, $m, $d);
        }
        // Y-m-d (ya bien)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        // fallback: intenta parsear
        $ts = strtotime($date);
        return $ts ? date('Y-m-d', $ts) : null;
    }

    /** Combina fecha + hora en formato Carbon */
    private function mergeDateTime(string $date, string $time): Carbon
    {
        $time = trim($time);
        // Convierte 12h a 24h si lleva am/pm
        if (preg_match('/(am|pm)$/i', $time)) {
            $time = date('H:i', strtotime($time));
        }

        return Carbon::createFromFormat('Y-m-d H:i', "{$date} {$time}", 'America/Mexico_City');
    }
}
