<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;

class CotizacionesAdminController extends Controller
{
    /**
     * 🧭 Vista principal de Cotizar (Admin)
     */
    public function index()
    {
        $ciudades = DB::table('ciudades')
            ->select('id_ciudad', 'nombre', 'estado', 'pais')
            ->orderBy('nombre')
            ->get();

        $sucursales = DB::table('sucursales as s')
            ->join('ciudades as c', 's.id_ciudad', '=', 'c.id_ciudad')
            ->select(
                's.id_sucursal',
                DB::raw("CONCAT(s.nombre, ' - ', c.nombre, ', ', c.estado) as nombre_mostrado"),
                'c.id_ciudad',
                'c.pais'
            )
            ->where('s.activo', true)
            ->orderBy('c.nombre')
            ->orderBy('s.nombre')
            ->get();

        $categorias = DB::table('categorias_carros')
            ->select('id_categoria', 'nombre', 'descripcion')
            ->where('activo', 1)
            ->orderBy('nombre')
            ->get();

        return view('Admin.Cotizar', compact('ciudades', 'sucursales', 'categorias'));
    }

    /**
     * 🚗 Obtener vehículos por categoría (AJAX)
     */
    public function vehiculosPorCategoria($idCategoria = 0)
    {
        try {
            $query = DB::table('vehiculos as v')
                ->leftJoin('marcas as m', 'v.id_marca', '=', 'm.id_marca')
                ->leftJoin('modelos as mo', 'v.id_modelo', '=', 'mo.id_modelo')
                ->select(
                    'v.id_vehiculo',
                    'v.nombre_publico',
                    'v.precio_dia',
                    'v.transmision',
                    'v.combustible',
                    'v.asientos',
                    'm.nombre as marca',
                    'mo.nombre as modelo'
                )
                ->where('v.id_estatus', 1);

            if ($idCategoria > 0) {
                $query->where('v.id_categoria', $idCategoria);
            }

            $vehiculos = $query->orderBy('m.nombre')->get();

            return response()->json($vehiculos, 200);
        } catch (\Throwable $e) {
            Log::error("❌ Error al obtener vehículos: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 🛡️ Obtener paquetes de seguros activos (Cotizar)
     */
    public function getSeguros()
    {
        try {
            $seguros = DB::table('seguro_paquete')
                ->select('id_paquete', 'nombre', 'descripcion', 'precio_por_dia', 'activo')
                ->where('activo', true)
                ->orderBy('precio_por_dia')
                ->get();

            return response()->json($seguros);
        } catch (\Throwable $e) {
            Log::error("❌ Error al obtener seguros: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 🧩 Obtener servicios adicionales activos (Cotizar)
     */
    public function getServicios()
    {
        try {
            $servicios = DB::table('servicios')
                ->select('id_servicio', 'nombre', 'descripcion', 'tipo_cobro', 'precio', 'activo')
                ->where('activo', true)
                ->orderBy('precio')
                ->get();

            return response()->json($servicios);
        } catch (\Throwable $e) {
            Log::error("❌ Error al obtener servicios: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * 💾 Guardar cotización / enviar / confirmar
     */
    public function guardarCotizacion(Request $request)
    {
        try {
            // ✅ Validación
            $validated = $request->validate([
                'pickup_sucursal_id' => 'required|integer|exists:sucursales,id_sucursal',
                'dropoff_sucursal_id' => 'required|integer|exists:sucursales,id_sucursal',
                'pickup_date'   => 'required|date',
                'pickup_time'   => 'required|string|max:10',
                'dropoff_date'  => 'required|date|after_or_equal:pickup_date',
                'dropoff_time'  => 'required|string|max:10',
                'vehiculo_id'   => 'required|integer|exists:vehiculos,id_vehiculo',
                'total'         => 'nullable|numeric',
            ]);

            // 🎫 Folio único
            $folio = 'COT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

            // 🧮 Cálculo
            $dias = max(1, Carbon::parse($request->pickup_date)->diffInDays(Carbon::parse($request->dropoff_date)));
            $iva = round(($request->input('subtotal', 0) ?? 0) * 0.16, 2);
            $total = $request->input('total', 0);

            // 🔍 Datos de sucursales y ciudades
            $sucursalRetiro = DB::table('sucursales')->where('id_sucursal', $request->pickup_sucursal_id)->first();
            $sucursalEntrega = DB::table('sucursales')->where('id_sucursal', $request->dropoff_sucursal_id)->first();

            $pickup_name = $sucursalRetiro ? $sucursalRetiro->nombre : '';
            $dropoff_name = $sucursalEntrega ? $sucursalEntrega->nombre : '';

            // 🔍 Obtener datos del vehículo
            $vehiculo = DB::table('vehiculos as v')
                ->leftJoin('vehiculo_imagenes as img', 'v.id_vehiculo', '=', 'img.id_vehiculo')
                ->leftJoin('marcas as m', 'v.id_marca', '=', 'm.id_marca')
                ->leftJoin('modelos as mo', 'v.id_modelo', '=', 'mo.id_modelo')
                ->leftJoin('categorias_carros as c', 'v.id_categoria', '=', 'c.id_categoria')
                ->select(
                    'v.nombre_publico',
                    'v.precio_dia',
                    'v.anio',
                    'v.transmision',
                    'v.asientos',
                    'v.puertas',
                    'm.nombre as marca',
                    'mo.nombre as modelo',
                    'c.nombre as categoria',
                    'img.url as imagen'
                )
                ->where('v.id_vehiculo', $request->vehiculo_id)
                ->first();

            $imgVehiculo = $vehiculo && $vehiculo->imagen
                ? asset($vehiculo->imagen)
                : asset('img/no-image.png');

            // 🗂️ Asegurar carpeta de cotizaciones
            Storage::makeDirectory('public/cotizaciones');

            // 💾 Insertar cotización
            $idCotizacion = DB::table('cotizaciones')->insertGetId([
                'folio'              => $folio,
                'vehiculo_id'        => $request->vehiculo_id,
                'vehiculo_marca'     => $vehiculo->marca ?? '',
                'vehiculo_modelo'    => $vehiculo->modelo ?? '',
                'vehiculo_categoria' => $vehiculo->categoria ?? '',
                'pickup_date'        => $request->pickup_date,
                'pickup_time'        => $request->pickup_time,
                'pickup_name'        => $pickup_name,
                'dropoff_date'       => $request->dropoff_date,
                'dropoff_time'       => $request->dropoff_time,
                'dropoff_name'       => $dropoff_name,
                'days'               => $dias,
                'tarifa_base'        => $vehiculo->precio_dia ?? 0,
                'iva'                => $iva,
                'total'              => $total,
                'addons'             => json_encode($request->input('extras', [])),
                'cliente'            => json_encode($request->input('cliente', [])),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);

            $cliente = $request->input('cliente', []);
            $extras = $request->input('extras', []);
            $seguro = $request->input('seguro', null);
            $accion = 'guardada';

            // 🧾 Listar servicios seleccionados
            $extrasList = '';
            if ($seguro) {
                $extrasList .= "<li>Protección: {$seguro['nombre']} - $" . number_format($seguro['precio'], 2) . " MXN/día</li>";
            }
            if (!empty($extras)) {
                foreach ($extras as $e) {
                    $extrasList .= "<li>{$e['cantidad']}× {$e['nombre']} - $" . number_format($e['precio'], 2) . " MXN</li>";
                }
            }
            if ($extrasList === '') $extrasList = '<li>Sin adicionales</li>';

            /* ==========================================================
               📄 Generar PDF PROFESIONAL
            ========================================================== */
            $logoPath = public_path('img/Logo3.jpg');
            $fechaHoy = now()->format('d M Y');

            $pdfHtml = "
            <div style='font-family:sans-serif; color:#111; font-size:14px;'>
                <table width='100%' style='border-collapse:collapse;'>
                    <tr>
                        <td><img src='{$logoPath}' style='height:60px;'></td>
                        <td style='text-align:right;'>
                            <strong style='font-size:12px;'>NO. DE COTIZACIÓN</strong><br>
                            <span style='font-size:15px; color:#D6121F; font-weight:bold;'>{$folio}</span><br>
                            <small>Fecha: {$fechaHoy}</small>
                        </td>
                    </tr>
                </table>
                <hr style='margin:16px 0;'>
                <h2 style='color:#111; font-size:18px;'>Resumen de tu cotización</h2>
                <p><strong>Entrega:</strong> {$pickup_name} ({$request->pickup_date} {$request->pickup_time})</p>
                <p><strong>Devolución:</strong> {$dropoff_name} ({$request->dropoff_date} {$request->dropoff_time})</p>
                <p><strong>Días:</strong> {$dias}</p>
                <h3 style='margin-top:20px;'>Tu Auto</h3>
                <table width='100%' cellpadding='6'>
                    <tr>
                        <td width='30%'><img src='{$imgVehiculo}' style='width:100%; border-radius:8px;'></td>
                        <td width='70%' style='vertical-align:top;'>
                            <strong style='font-size:16px;'>{$vehiculo->nombre_publico}</strong><br>
                            <small>{$vehiculo->marca} {$vehiculo->modelo} - {$vehiculo->categoria}</small><br>
                            <small>{$vehiculo->anio} • {$vehiculo->transmision} • {$vehiculo->asientos} asientos</small>
                        </td>
                    </tr>
                </table>
                <h3 style='margin-top:24px;'>Opciones de renta seleccionadas</h3>
                <ul>{$extrasList}</ul>
                <h3 style='margin-top:24px;'>Detalles del precio</h3>
                <table width='100%' style='border-collapse:collapse;'>
                    <tr><td>Tarifa base</td><td style='text-align:right;'>$".number_format($vehiculo->precio_dia * $dias, 2)." MXN</td></tr>
                    <tr><td>Opciones de renta</td><td style='text-align:right;'>$".number_format(($total - $iva - ($vehiculo->precio_dia * $dias)), 2)." MXN</td></tr>
                    <tr><td>Cargos e IVA</td><td style='text-align:right;'>$".number_format($iva, 2)." MXN</td></tr>
                    <tr style='border-top:1px solid #ccc; font-weight:bold;'>
                        <td>TOTAL</td><td style='text-align:right; color:#D6121F;'>$".number_format($total, 2)." MXN</td>
                    </tr>
                </table>
                <p style='margin-top:40px; color:#555;'>Gracias por elegir <strong>Viajero Car Rental</strong>. 🚗</p>
            </div>";

            $pdfPath = storage_path("app/public/cotizaciones/{$folio}.pdf");
            Pdf::html($pdfHtml)->format('a4')->save($pdfPath);

            /* ==========================================================
               📧 Enviar correo con PDF adjunto
            ========================================================== */
            if ($request->has('enviarCorreo') && !empty($cliente['email'])) {
                Mail::html("
                    <div style='font-family:sans-serif;'>
                        <h2 style='color:#D6121F;'>Viajero Car Rental</h2>
                        <p>Estimado(a) <b>{$cliente['nombre']} {$cliente['apellidos']}</b>,</p>
                        <p>Adjuntamos tu cotización <b>{$folio}</b> en formato PDF.</p>
                    </div>
                ", function ($message) use ($cliente, $pdfPath, $folio) {
                    $message->to($cliente['email'])
                            ->subject("Tu cotización #{$folio} - Viajero Car Rental")
                            ->attach($pdfPath);
                });
                $accion = 'enviada por correo';
            }

            /* ==========================================================
               ✅ Confirmar → crear reservación real
            ========================================================== */
            if ($request->has('confirmar')) {
                DB::table('reservaciones')->insert([
                    'codigo'           => 'RES-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5)),
                    'id_vehiculo'      => $request->vehiculo_id,
                    'fecha_inicio'     => $request->pickup_date,
                    'fecha_fin'        => $request->dropoff_date,
                    'hora_retiro'      => $request->pickup_time,
                    'hora_entrega'     => $request->dropoff_time,
                    'sucursal_retiro'  => $request->pickup_sucursal_id,
                    'sucursal_entrega' => $request->dropoff_sucursal_id,
                    'ciudad_retiro'    => $sucursalRetiro->id_ciudad ?? null,
                    'ciudad_entrega'   => $sucursalEntrega->id_ciudad ?? null,
                    'subtotal'         => $total / 1.16,
                    'impuestos'        => $total - ($total / 1.16),
                    'total'            => $total,
                    'moneda'           => 'MXN',
                    'estado'           => 'pendiente_pago',
                    'nombre_cliente'   => $cliente['nombre'] ?? null,
                    'email_cliente'    => $cliente['email'] ?? null,
                    'telefono_cliente' => $cliente['telefono'] ?? null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
                $accion = 'confirmada y registrada como reservación';
            }

            return response()->json([
                'success' => true,
                'accion'  => $accion,
                'folio'   => $folio,
                'id'      => $idCotizacion,
                'message' => "Cotización {$accion} correctamente.",
            ]);

        } catch (\Throwable $e) {
            Log::error("❌ Error en guardarCotizacion: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la cotización.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
