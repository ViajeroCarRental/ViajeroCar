<?php

namespace App\Http\Controllers;

use App\Services\FacturapiService;
use Illuminate\Http\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AutofacturaController extends Controller
{
    // 1) Página de búsqueda: el cliente mete folio + correo
    public function buscar()
    {
        return view('autofactura.buscar');
    }

    // 2) Valida folio + correo y redirige al formulario
    public function validar(Request $request)
    {
        $datos = $request->validate([
            'folio'  => 'required|string',
            'correo' => 'required|email',
        ], [
            'folio.required'  => 'Ingresa tu folio de reservación.',
            'correo.required' => 'Ingresa el correo con el que reservaste.',
        ]);

        // Buscar la reservación por folio (codigo) + correo
        $reservacion = DB::table('reservaciones')
            ->where('codigo', $datos['folio'])
            ->where('email_cliente', $datos['correo'])
            ->first();

        if (!$reservacion) {
            return back()->withInput()
                ->with('error', 'No encontramos una reservación con ese folio y correo. Verifica los datos.');
        }

        // Guardar en sesión para el siguiente paso
        session([
            'autofactura_folio'   => $datos['folio'],
            'autofactura_id_res'  => $reservacion->id_reservacion,
        ]);

        return redirect()->route('autofactura.formulario');
    }

    // 3) Muestra el formulario fiscal
    public function formulario()
    {
        $idReservacion = session('autofactura_id_res');
        if (!$idReservacion) {
            return redirect()->route('autofactura.buscar');
        }

        $reservacion = DB::table('reservaciones')->where('id_reservacion', $idReservacion)->first();
        if (!$reservacion) {
            return redirect()->route('autofactura.buscar')->with('error', 'Reservación no encontrada.');
        }

        // ¿Ya está facturada?
        $facturaExistente = DB::table('facturas')
            ->where('folio_reservacion', $reservacion->codigo)
            ->where('estatus', 'timbrada')
            ->first();

        if ($facturaExistente) {
            return view('autofactura.ya-facturada', ['factura' => $facturaExistente]);
        }

        // ¿El contrato ya está cerrado? Si sí, no puede autofacturar
        $contrato = DB::table('contratos')
            ->where('id_reservacion', $reservacion->id_reservacion)
            ->first();

        if ($contrato && $contrato->estado === 'cerrado') {
            return view('autofactura.cerrada', ['reservacion' => $reservacion]);
        }

        // Autocompletar datos fiscales previos (por correo del cliente)
        $datosPrevios = DB::table('datos_fiscales')
            ->where('correo_cliente', $reservacion->email_cliente)
            ->where('predeterminado', true)
            ->first();

        return view('autofactura.formulario', [
            'reservacion'  => $reservacion,
            'datosPrevios' => $datosPrevios,
        ]);
    }

    // 4) Timbra la factura
    public function timbrar(Request $request, FacturapiService $facturapi)
    {
        $idReservacion = session('autofactura_id_res');
        if (!$idReservacion) {
            return redirect()->route('autofactura.buscar');
        }

        $reservacion = DB::table('reservaciones')->where('id_reservacion', $idReservacion)->first();
        if (!$reservacion) {
            return redirect()->route('autofactura.buscar')->with('error', 'Reservación no encontrada.');
        }

        // Candado: ¿ya facturada?
        $yaFacturada = DB::table('facturas')
            ->where('folio_reservacion', $reservacion->codigo)
            ->where('estatus', 'timbrada')
            ->exists();

        if ($yaFacturada) {
            return redirect()->route('autofactura.formulario');
        }

        // Candado: ¿contrato cerrado?
        $contrato = DB::table('contratos')->where('id_reservacion', $reservacion->id_reservacion)->first();
        if ($contrato && $contrato->estado === 'cerrado') {
            return back()->with('error', 'Tu contrato ya cerró. Ya no puedes facturar en línea.');
        }

        $datos = $request->validate([
            'rfc'            => 'required|string|min:12|max:13',
            'razon_social'   => 'required|string|max:254',
            'regimen_fiscal' => 'required|string|size:3',
            'codigo_postal'  => 'required|string|size:5',
            'correo'         => 'required|email',
            'uso_cfdi'       => 'required|string',
            'forma_pago'     => 'required|string',
        ]);

        $datos['rfc'] = strtoupper(trim($datos['rfc']));

        // El monto viene de la reservación, NO del formulario
        $total    = (float) $reservacion->total;
        $subtotal = round($total / 1.16, 2);

        try {
            $respuesta = $facturapi->crearFactura([
                'customer' => [
                    'legal_name' => $datos['razon_social'],
                    'tax_id'     => $datos['rfc'],
                    'tax_system' => $datos['regimen_fiscal'],
                    'email'      => $datos['correo'],
                    'address'    => ['zip' => $datos['codigo_postal']],
                ],
                'use'            => $datos['uso_cfdi'],
                'payment_method' => 'PUE',
                'payment_form'   => $datos['forma_pago'],
                'items' => [[
                    'quantity' => 1,
                    'product'  => [
                        'description'  => "Renta de vehiculo - Folio {$reservacion->codigo}",
                        'product_key'  => '78111808',
                        'unit_key'     => 'E48',
                        'price'        => $subtotal,
                        'tax_included' => false,
                        'taxes' => [['type' => 'IVA', 'rate' => 0.16]],
                    ],
                ]],
            ]);

            // Guardar la factura
            $idFactura = DB::table('facturas')->insertGetId([
                'id_contrato'       => $contrato->id_contrato ?? null,
                'folio_reservacion' => $reservacion->codigo,
                'facturapi_id'      => $respuesta['id'],
                'uuid'              => $respuesta['uuid'] ?? null,
                'rfc_receptor'      => $datos['rfc'],
                'nombre_receptor'   => $datos['razon_social'],
                'total'             => $respuesta['total'] ?? $total,
                'status'            => $respuesta['status'] ?? 'valid',
                'estatus'           => 'timbrada',
                'origen'            => 'autofactura',
                'fecha_timbrado'    => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            // Guardar el xml y pdf
            $rutaXml = null;
            $rutaPdf = null;

            try {
                $contenidoXml = $facturapi->descargarXml($respuesta['id']);
                $contenidoPdf = $facturapi->descargarPdf($respuesta['id']);

                $nombreBase = 'factura_' . $reservacion->codigo . '_' . $respuesta['id'];

                $rutaXml = 'facturas/' . $nombreBase . '.xml';
                $rutaPdf = 'facturas/' . $nombreBase . '.pdf';

                \Illuminate\Support\Facades\Storage::put($rutaXml, $contenidoXml);
                \Illuminate\Support\Facades\Storage::put($rutaPdf, $contenidoPdf);

                // Actualizar la factura con las rutas
                DB::table('facturas')->where('id', $idFactura)->update([
                    'ruta_xml' => $rutaXml,
                    'ruta_pdf' => $rutaPdf,
                ]);
            } catch (\Exception $e) {
                Log::warning('No se pudo guardar XML/PDF en servidor', [
                    'folio' => $reservacion->codigo,
                    'error' => $e->getMessage(),
                ]);
            }

            // Guardar datos fiscales para la próxima vez (por correo)
            DB::table('datos_fiscales')->updateOrInsert(
                ['correo_cliente' => $reservacion->email_cliente, 'rfc' => $datos['rfc']],
                [
                    'razon_social'          => $datos['razon_social'],
                    'regimen_fiscal'        => $datos['regimen_fiscal'],
                    'codigo_postal'         => $datos['codigo_postal'],
                    'correo'                => $datos['correo'],
                    'facturapi_customer_id' => $respuesta['customer']['id'] ?? null,
                    'predeterminado'        => 1,
                    'updated_at'            => now(),
                    'created_at'            => now(),
                ]
            );

            // Enviar por correo
            try {
                $facturapi->enviarPorCorreo($respuesta['id'], $datos['correo']);
            } catch (\Exception $e) {
                Log::warning('No se pudo enviar correo de autofactura', ['folio' => $reservacion->codigo]);
            }

            return redirect()->route('autofactura.exito', ['id' => $idFactura]);
        } catch (RequestException $e) {
            $errorApi = $e->response->json();
            Log::error('Error autofactura', ['detalle' => $errorApi]);

            $mensajeSat = $errorApi['message'] ?? '';
            $mensajeAmigable = 'No se pudo generar la factura. ';

            if (str_contains($mensajeSat, 'DomicilioFiscalReceptor') || str_contains($mensajeSat, 'Rfc')) {
                $mensajeAmigable .= 'Verifica que tu RFC, nombre y código postal coincidan con tu Constancia de Situación Fiscal.';
            } elseif (str_contains($mensajeSat, 'RegimenFiscal')) {
                $mensajeAmigable .= 'El régimen fiscal no corresponde a tu RFC.';
            } else {
                $mensajeAmigable .= 'Revisa tus datos fiscales.';
            }

            return back()->withInput()->with('error', $mensajeAmigable);
        }
    }

    // 5) Pantalla de éxito con descargas
    public function exito($id)
    {
        $factura = DB::table('facturas')->where('id', $id)->where('estatus', 'timbrada')->first();
        if (!$factura) {
            return redirect()->route('autofactura.buscar');
        }
        return view('autofactura.exito', ['factura' => $factura]);
    }

    // Descargas
    public function pdf($id, FacturapiService $facturapi)
    {
        $factura = DB::table('facturas')->where('id', $id)->where('estatus', 'timbrada')->first();
        abort_if(!$factura, 404);
        return response($facturapi->descargarPdf($factura->facturapi_id), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="factura-' . $factura->folio_reservacion . '.pdf"');
    }

    public function xml($id, FacturapiService $facturapi)
    {
        $factura = DB::table('facturas')->where('id', $id)->where('estatus', 'timbrada')->first();
        abort_if(!$factura, 404);
        return response($facturapi->descargarXml($factura->facturapi_id), 200)
            ->header('Content-Type', 'application/xml')
            ->header('Content-Disposition', 'attachment; filename="factura-' . $factura->folio_reservacion . '.xml"');
    }
}
