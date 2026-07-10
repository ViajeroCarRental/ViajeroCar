<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AltaClienteController extends Controller
{
    /**
     * Sistema fijo para esta vista (Viajero Car Rental = 1).
     * Si algún día se monta One Degreen, cambia este valor en su propia instancia.
     */
    private const ID_SISTEMA = 1;

    /* ============================================================
     |  GET · Mostrar la vista de Alta Cliente
     |  (antes vivía en controladorVistasAdmin::altaCliente)
     ============================================================ */
    public function index()
    {
        // Categorías reales (con su id) para el Paso 3
        $categorias = DB::table('categorias_carros')
            ->where('activo', 1)
            ->orderBy('orden')
            ->get(['id_categoria', 'nombre', 'precio_dia', 'precio_semana', 'precio_mes']);

        // Paquetes de protección reales (Paso 3)
        $protecciones = DB::table('seguro_paquete')
            ->where('activo', 1)
            ->orderBy('orden')
            ->get(['id_paquete', 'nombre', 'precio_por_dia', 'deducible_colision', 'deducible_robo']);

        return view('Admin.AltaCliente', compact('categorias', 'protecciones'));
    }

    /* ============================================================
     |  POST · Guardar todo el alta (un solo form, una transacción)
     ============================================================ */
    public function store(Request $request)
    {
        $tipo = $request->input('tipo_persona'); // fisica | moral | general

        if (!in_array($tipo, ['fisica', 'moral', 'general'], true)) {
            return back()->with('error', 'Tipo de cliente inválido.')->withInput();
        }

        try {
            DB::beginTransaction();

            // 1) Usuario + marcador de sistema
            $idUsuario = $this->crearUsuario($request, $tipo);
            $this->vincularSistema($idUsuario);

            // 2) Cliente (expediente base)
            $idCliente = $this->crearCliente($request, $tipo, $idUsuario);

            // 3) Facturación (física y moral; general no factura)
            if ($tipo === 'fisica' || $tipo === 'moral') {
                $this->guardarFacturacion($request, $tipo, $idCliente);
            }

            // 4) Datos de persona moral + conductores
            $mapaConductores = [];
            if ($tipo === 'moral') {
                $this->guardarMoral($request, $idCliente);
                $mapaConductores = $this->guardarConductores($request, $idCliente);
            }

            // 5) Documentos del cliente (LONGBLOB)
            $this->guardarDocumentosCliente($request, $tipo, $idCliente);

            // 6) Tarifas negociadas por categoría (Paso 3)
            $this->guardarTarifas($request, $idCliente);

            // 7) Convenio + cláusulas + responsivas
            $idConvenio = $this->guardarConvenio($request, $tipo, $idCliente);
            $this->guardarClausulas($request, $idConvenio);

            if ($tipo === 'moral') {
                $this->guardarResponsivas($request, $idConvenio, $mapaConductores);
            }

            DB::commit();

            if ($request->input('accion_post_submit') === 'generar_pdf') {
                return redirect()->route('admin.convenio.pdf', $idConvenio);
            }

            return redirect()
                ->route('rutaAltaCliente')
                ->with('success', 'Cliente y convenio registrados correctamente.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Error al guardar: ' . $e->getMessage())
                ->withInput();
        }
    }

    /* ============================================================
     |  BLOQUE 1 · Usuario
     ============================================================ */
    private function crearUsuario(Request $request, string $tipo): int
    {
        // Según el tipo, el "nombre/correo/teléfono" del usuario sale de campos distintos
        if ($tipo === 'fisica') {
            $nombre   = $request->input('fisica_nombre');
            $correo   = $request->input('fisica_correo');
            $telefono = $request->input('fisica_telefono');
        } elseif ($tipo === 'moral') {
            // Para empresa: razón social como nombre, datos de la empresa
            $nombre   = $request->input('moral_razon');
            $correo   = $request->input('moral_correo');
            $telefono = $request->input('moral_telefono');
        } else { // general
            $nombre   = $request->input('general_nombre');
            $correo   = $request->input('general_correo');
            $telefono = $request->input('general_telefono');
        }

        $existe = DB::table('usuarios')->where('correo', $correo)->exists();

        if ($existe) {
            throw new \Exception("El correo {$correo} ya está registrado. Usa uno diferente o busca al cliente existente.");
        }

        return DB::table('usuarios')->insertGetId([
            'nombres'           => $nombre,           // todo el nombre va aquí
            'apellidos'         => '',                // apellidos vacío (decisión tomada)
            'correo'            => $correo,
            'numero'            => $telefono,
            'contrasena_hash'   => Hash::make(Str::random(40)), // random; el cliente no la usa aún
            'email_verificado'  => 0,
            'pais'              => $request->input($tipo . '_fiscal_pais'),
            'activo'            => 1,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    private function vincularSistema(int $idUsuario): void
    {
        // Marca de a qué sistema pertenece el cliente (Viajero = 1)
        DB::table('usuario_sistema_preferente')->insert([
            'id_usuario'    => $idUsuario,
            'id_sistema'    => self::ID_SISTEMA,
            'es_preferente' => 1,
            'descuento_pct' => 0.00,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }

    /* ============================================================
     |  BLOQUE 2 · Cliente
     ============================================================ */
    private function crearCliente(Request $request, string $tipo, int $idUsuario): int
    {
        // Campos que varían según el tipo
        $nacimiento     = null;
        $numeroIdent    = null;
        $tipoIdent      = null;
        $licencia       = null;
        $vigencia       = null;
        $numeroEmpresa  = null;
        $nombreEmpresa  = null;

        if ($tipo === 'fisica') {
            $nacimiento    = $this->fecha($request->input('fisica_nacimiento'));
            $numeroIdent   = $request->input('fisica_numero_identificacion');
            $tipoIdent     = $request->input('fisica_tipo_identificacion');
            $licencia      = $request->input('fisica_licencia');
            $vigencia      = $this->fecha($request->input('fisica_vigencia_licencia'));
            $numeroEmpresa = $request->input('fisica_numero_empresa');
            $nombreEmpresa = $request->input('fisica_nombre_empresa');
        } elseif ($tipo === 'general') {
            $nacimiento  = $this->fecha($request->input('general_nacimiento'));
            $numeroIdent = $request->input('general_identificacion');
            $licencia    = $request->input('general_licencia');
            $vigencia    = $this->fecha($request->input('general_vigencia_licencia'));
        } else { // moral → los datos de licencia/identificación son del representante (van en cliente_moral)
            // En clientes sólo guardamos el tipo y el vínculo
        }

        return DB::table('clientes')->insertGetId([
            'id_usuario'             => $idUsuario,
            'tipo_persona'           => $tipo,
            'fecha_nacimiento'       => $nacimiento,
            'numero_identificacion'  => $numeroIdent,
            'tipo_identificacion'    => $tipoIdent,
            'numero_licencia'        => $licencia,
            'vigencia_licencia'      => $vigencia,
            'numero_empresa'         => $numeroEmpresa,
            'nombre_empresa'         => $nombreEmpresa,
            'activo'                 => 1,
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);
    }

    /* ============================================================
     |  BLOQUE 3 · Facturación
     ============================================================ */
    private function guardarFacturacion(Request $request, string $tipo, int $idCliente): void
    {
        $p = $tipo . '_'; // prefijo: fisica_ o moral_

        DB::table('cliente_facturacion')->insert([
            'id_cliente'       => $idCliente,
            'rfc'              => $request->input($p . 'facturacion_rfc'),
            'razon_social'     => $request->input($p . 'facturacion_razon'),
            'uso_cfdi'         => $request->input($p . 'facturacion_cfdi'),
            'regimen_fiscal'   => $request->input($p . 'facturacion_regimen'),
            'correo_factura'   => $tipo === 'fisica'
                ? $request->input('fisica_correo_factura')
                : $request->input('moral_facturacion_correo'),
            'pais'             => $request->input($p . 'fiscal_pais'),
            'codigo_postal'    => $request->input($p . 'fiscal_cp'),
            'municipio'        => $request->input($p . 'fiscal_municipio'),
            'localidad'        => $request->input($p . 'fiscal_localidad'),
            'estado'           => $request->input($p . 'fiscal_estado'),
            'colonia'          => $request->input($p . 'fiscal_colonia'),
            'calle'            => $request->input($p . 'fiscal_calle'),
            'numero_exterior'  => $request->input($p . 'fiscal_numero_exterior'),
            'numero_interior'  => $request->input($p . 'fiscal_numero_interior'),
            'referencias'      => $request->input($p . 'fiscal_referencias'),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    /* ============================================================
     |  BLOQUE 4 · Persona moral + conductores
     ============================================================ */
    private function guardarMoral(Request $request, int $idCliente): void
    {
        DB::table('cliente_moral')->insert([
            'id_cliente'                       => $idCliente,
            'razon_social'                     => $request->input('moral_razon'),
            'telefono_empresa'                 => $request->input('moral_telefono'),
            'correo_empresa'                   => $request->input('moral_correo'),
            'representante_nombre'             => $request->input('moral_representante'),
            'representante_nacimiento'         => $this->fecha($request->input('moral_representante_nacimiento')),
            'representante_telefono'           => $request->input('moral_telefono_representante'),
            'representante_correo'             => $request->input('moral_correo_representante'),
            'representante_identificacion'     => $request->input('moral_representante_identificacion'),
            'representante_licencia'           => $request->input('moral_licencia_titular'),
            'representante_vigencia_licencia'  => $this->fecha($request->input('moral_vigencia_licencia_titular')),
            'created_at'                       => now(),
            'updated_at'                       => now(),
        ]);
    }

    /**
     * Guarda los conductores adicionales (array dinámico de la vista).
     * Devuelve un mapa [indice_en_form => id_conductor_convenio] para las responsivas.
     */
    private function guardarConductores(Request $request, int $idCliente): array
    {
        $mapa = [];

        $nombres = $request->input('driver_nombre', []); // arrays paralelos: driver_nombre[], driver_licencia[], etc.

        if (!is_array($nombres)) {
            return $mapa;
        }

        foreach ($nombres as $i => $nombre) {
            if (empty($nombre)) {
                continue;
            }

            $docs = [
                'identificacion_frontal' => $this->extraerArchivoArray($request, 'driver_identificacion_frontal', $i),
                'identificacion_trasera' => $this->extraerArchivoArray($request, 'driver_identificacion_trasera', $i),
                'licencia_frontal'       => $this->extraerArchivoArray($request, 'driver_licencia_frontal', $i),
                'licencia_trasera'       => $this->extraerArchivoArray($request, 'driver_licencia_trasera', $i),
            ];

            $idConductor = DB::table('conductor_adicional_convenio')->insertGetId([
                'id_cliente'        => $idCliente,
                'nombre'            => $nombre,
                'nacimiento'        => $this->fecha($this->valArray($request, 'driver_nacimiento', $i)),
                'telefono'          => $this->valArray($request, 'driver_telefono', $i),
                'correo'            => $this->valArray($request, 'driver_correo', $i),
                'identificacion'    => $this->valArray($request, 'driver_ine', $i),
                'licencia'          => $this->valArray($request, 'driver_licencia', $i),
                'vigencia_licencia' => $this->fecha($this->valArray($request, 'driver_vigencia_licencia', $i)),
                'firma'             => $this->valArray($request, 'driver_firma', $i),

                'identificacion_frontal_contenido'  => $docs['identificacion_frontal']['contenido'],
                'identificacion_frontal_nombre'     => $docs['identificacion_frontal']['nombre'],
                'identificacion_frontal_mime'       => $docs['identificacion_frontal']['mime'],
                'identificacion_frontal_extension'  => $docs['identificacion_frontal']['extension'],

                'identificacion_trasera_contenido'  => $docs['identificacion_trasera']['contenido'],
                'identificacion_trasera_nombre'     => $docs['identificacion_trasera']['nombre'],
                'identificacion_trasera_mime'       => $docs['identificacion_trasera']['mime'],
                'identificacion_trasera_extension'  => $docs['identificacion_trasera']['extension'],

                'licencia_frontal_contenido'        => $docs['licencia_frontal']['contenido'],
                'licencia_frontal_nombre'           => $docs['licencia_frontal']['nombre'],
                'licencia_frontal_mime'             => $docs['licencia_frontal']['mime'],
                'licencia_frontal_extension'        => $docs['licencia_frontal']['extension'],

                'licencia_trasera_contenido'        => $docs['licencia_trasera']['contenido'],
                'licencia_trasera_nombre'           => $docs['licencia_trasera']['nombre'],
                'licencia_trasera_mime'             => $docs['licencia_trasera']['mime'],
                'licencia_trasera_extension'        => $docs['licencia_trasera']['extension'],

                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            $mapa[$i] = $idConductor;
        }

        return $mapa;
    }

    /* ============================================================
     |  BLOQUE 5 · Documentos del cliente (cliente_archivo, LONGBLOB)
     ============================================================ */
    private function guardarDocumentosCliente(Request $request, string $tipo, int $idCliente): void
    {
        // Mapa: tipo_documento => name del input file en la vista, según el tipo de cliente
        $mapas = [
            'fisica' => [
                'identificacion_frontal' => 'fisica_identificacion_frontal',
                'identificacion_trasera' => 'fisica_identificacion_trasera',
                'licencia_frontal'       => 'fisica_licencia_frontal',
                'licencia_trasera'       => 'fisica_licencia_trasera',
                'csf'                    => 'fisica_csf',
            ],
            'moral' => [
                'csf'                    => 'moral_csf',
                'acta_constitutiva'      => 'moral_acta_constitutiva',
                'identificacion_frontal' => 'moral_identificacion_frontal',
                'identificacion_trasera' => 'moral_identificacion_trasera',
                'licencia_frontal'       => 'moral_licencia_frontal',
                'licencia_trasera'       => 'moral_licencia_trasera',
                'responsiva_cliente'     => 'moral_responsiva_cliente',
            ],
            'general' => [
                'identificacion_frontal' => 'general_identificacion_frontal',
                'identificacion_trasera' => 'general_identificacion_trasera',
                'licencia_frontal'       => 'general_licencia_frontal',
                'licencia_trasera'       => 'general_licencia_trasera',
            ],
        ];

        foreach ($mapas[$tipo] as $tipoDoc => $inputName) {
            if (!$request->hasFile($inputName)) {
                continue;
            }

            $archivo = $request->file($inputName);

            DB::table('cliente_archivo')->insert([
                'id_cliente'      => $idCliente,
                'tipo_documento'  => $tipoDoc,
                'contenido'       => file_get_contents($archivo->getRealPath()),
                'nombre_original' => $archivo->getClientOriginalName(),
                'mime_type'       => $archivo->getMimeType(),
                'extension'       => $archivo->getClientOriginalExtension(),
                'tamano_bytes'    => $archivo->getSize(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        // Convenio firmado (Paso 4) — también va en cliente_archivo
        if ($request->hasFile('convenio_firmado')) {
            $archivo = $request->file('convenio_firmado');

            DB::table('cliente_archivo')->insert([
                'id_cliente'      => $idCliente,
                'tipo_documento'  => 'convenio_firmado',
                'contenido'       => file_get_contents($archivo->getRealPath()),
                'nombre_original' => $archivo->getClientOriginalName(),
                'mime_type'       => $archivo->getMimeType(),
                'extension'       => $archivo->getClientOriginalExtension(),
                'tamano_bytes'    => $archivo->getSize(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }
    }

    /* ============================================================
     |  BLOQUE 6 · Tarifas negociadas (cliente_tarifa_convenio)
     ============================================================ */
    private function guardarTarifas(Request $request, int $idCliente): void
    {
        // Arrays paralelos por categoría:
        // tarifa_id_categoria[], tarifa_diaria[], tarifa_semanal[], tarifa_mensual[],
        // tarifa_id_paquete[], tarifa_paquete_nombre[], tarifa_paquete_precio[], tarifa_total[]
        $categorias = $request->input('tarifa_id_categoria', []);

        if (!is_array($categorias)) {
            return;
        }

        foreach ($categorias as $i => $idCategoria) {
            $diaria  = $this->money($this->valArray($request, 'tarifa_diaria', $i));
            $semanal = $this->money($this->valArray($request, 'tarifa_semanal', $i));
            $mensual = $this->money($this->valArray($request, 'tarifa_mensual', $i));

            // Si no capturó nada para esta categoría, la saltamos
            if ($diaria <= 0 && $semanal <= 0 && $mensual <= 0) {
                continue;
            }

            $idPaquete     = $this->valArray($request, 'tarifa_id_paquete', $i) ?: null;
            $paqueteNombre = $this->valArray($request, 'tarifa_paquete_nombre', $i);
            $paquetePrecio = $this->money($this->valArray($request, 'tarifa_paquete_precio', $i));
            $total         = $this->money($this->valArray($request, 'tarifa_total', $i));

            DB::table('cliente_tarifa_convenio')->insert([
                'id_cliente'         => $idCliente,
                'id_categoria'       => $idCategoria,
                'tarifa_diaria'      => $diaria,
                'tarifa_semanal'     => $semanal,
                'tarifa_mensual'     => $mensual,
                'id_paquete'         => $idPaquete,
                'paquete_nombre'     => $paqueteNombre,
                'paquete_precio_dia' => $paquetePrecio ?: null,
                'total_diario'       => $total,
                'created_at'         => now(),
                'updated_at'         => now(),
            ]);
        }
    }

    /* ============================================================
     |  BLOQUE 7 · Convenio + cláusulas + responsivas
     ============================================================ */
    private function guardarConvenio(Request $request, string $tipo, int $idCliente): int
    {
        // Las firmas dependen del tipo de cliente
        $firmaCliente       = null;
        $firmaAsesor        = null;
        $firmaRepresentante = null;
        $firmaConductor     = null;

        if ($tipo === 'fisica') {
            $firmaCliente = $request->input('firma_usuario_fisica');
            $firmaAsesor  = $request->input('firma_asesor_fisica');
        } elseif ($tipo === 'moral') {
            $firmaRepresentante = $request->input('firma_representante_legal');
            $firmaConductor     = $request->input('firma_conductor_convenio');
            $firmaAsesor        = $request->input('firma_asesor_moral');
        } else { // general
            $firmaCliente = $request->input('firma_usuario_general');
            $firmaAsesor  = $request->input('firma_asesor_general');
        }

        // Convenio firmado subido (opcional)
        $conv = ['contenido' => null, 'nombre' => null, 'mime' => null, 'extension' => null];
        if ($request->hasFile('convenio_firmado')) {
            $a = $request->file('convenio_firmado');
            $conv = [
                'contenido' => file_get_contents($a->getRealPath()),
                'nombre'    => $a->getClientOriginalName(),
                'mime'      => $a->getMimeType(),
                'extension' => $a->getClientOriginalExtension(),
            ];
        }

        return DB::table('convenios')->insertGetId([
            'id_cliente'                  => $idCliente,
            'tipo'                        => $tipo,
            'firma_cliente'               => $firmaCliente,
            'firma_asesor'                => $firmaAsesor,
            'firma_representante'         => $firmaRepresentante,
            'firma_conductor'             => $firmaConductor,
            'observaciones'               => $request->input('observaciones_convenio'),
            'convenio_firmado_contenido'  => $conv['contenido'],
            'convenio_firmado_nombre'     => $conv['nombre'],
            'convenio_firmado_mime'       => $conv['mime'],
            'convenio_firmado_extension'  => $conv['extension'],
            'created_at'                  => now(),
            'updated_at'                  => now(),
        ]);
    }


    private function guardarClausulas(Request $request, int $idConvenio): void
    {
        $clausulas = $request->input('clausulas', []);

        if (!is_array($clausulas)) {
            return;
        }

        $orden = 1;

        foreach ($clausulas as $texto) {
            if (empty(trim($texto))) {
                continue;
            }

            DB::table('convenio_clausula')->insert([
                'id_convenio' => $idConvenio,
                'texto'       => $texto,
                'orden'       => $orden++,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    private function guardarResponsivas(Request $request, int $idConvenio, array $mapaConductores): void
    {
        $todas = $request->input('responsiva_clausulas', []);

        foreach ($mapaConductores as $iForm => $idConductor) {
            $idResponsiva = DB::table('convenio_responsiva')->insertGetId([
                'id_convenio'           => $idConvenio,
                'id_conductor_convenio' => $idConductor,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);

            $clausulas = $todas[$iForm] ?? [];

            if (!is_array($clausulas)) {
                continue;
            }

            $orden = 1;

            foreach ($clausulas as $texto) {
                if (empty(trim($texto))) {
                    continue;
                }

                DB::table('responsiva_clausula')->insert([
                    'id_responsiva' => $idResponsiva,
                    'texto'         => $texto,
                    'orden'         => $orden++,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }
        }
    }

    public function generarConvenioPdf($id)
    {
        $convenio = DB::table('convenios')->where('id_convenio', $id)->first();

        if (!$convenio) abort(404);

        $cliente = DB::table('clientes')
            ->join('usuarios', 'usuarios.id_usuario', '=', 'clientes.id_usuario')
            ->where('clientes.id_cliente', $convenio->id_cliente)
            ->select(
                'clientes.tipo_persona',
                'clientes.fecha_nacimiento',
                'clientes.numero_identificacion',
                'clientes.tipo_identificacion',
                'clientes.numero_licencia',
                'clientes.vigencia_licencia',
                'clientes.numero_empresa',
                'clientes.nombre_empresa',
                'usuarios.nombres',
                'usuarios.apellidos',
                'usuarios.correo',
                'usuarios.numero'
            )
            ->first();

        $facturacion = DB::table('cliente_facturacion')
            ->where('id_cliente', $convenio->id_cliente)
            ->first();

        $moral = DB::table('cliente_moral')
            ->where('id_cliente', $convenio->id_cliente)
            ->first();

        $tarifas = DB::table('cliente_tarifa_convenio')
            ->leftJoin('categorias_carros', 'categorias_carros.id_categoria', '=', 'cliente_tarifa_convenio.id_categoria')
            ->where('cliente_tarifa_convenio.id_cliente', $convenio->id_cliente)
            ->select('cliente_tarifa_convenio.*', 'categorias_carros.nombre as categoria')
            ->get();

        $clausulas = DB::table('convenio_clausula')
            ->where('id_convenio', $id)
            ->orderBy('orden')
            ->get();

        $pdf = Pdf::loadView('admin.convenio', compact(
            'convenio',
            'cliente',
            'facturacion',
            'moral',
            'tarifas',
            'clausulas'
        ))->setPaper('letter')
            ->setOption('isRemoteEnabled', true);

        return $pdf->stream('convenio-viajero.pdf');
    }

    public function generarResponsivaPdf($id)
    {
        $responsiva = DB::table('convenio_responsiva')->where('id_responsiva', $id)->first();

        if (!$responsiva) abort(404);

        $convenio = DB::table('convenios')
            ->where('id_convenio', $responsiva->id_convenio)
            ->first();

        $cliente = DB::table('clientes')
            ->join('usuarios', 'usuarios.id_usuario', '=', 'clientes.id_usuario')
            ->where('clientes.id_cliente', $convenio->id_cliente)
            ->select('clientes.*', 'usuarios.*')
            ->first();

        $conductor = DB::table('conductor_adicional_convenio')
            ->where('id_conductor_convenio', $responsiva->id_conductor_convenio)
            ->first();

        $clausulas = DB::table('responsiva_clausula')
            ->where('id_responsiva', $id)
            ->orderBy('orden')
            ->get();

        $pdf = Pdf::loadView('admin.responsiva', compact(
            'responsiva',
            'convenio',
            'cliente',
            'conductor',
            'clausulas'
        ))->setPaper('letter');

        return $pdf->stream('responsiva-viajero.pdf');
    }

    /* ============================================================
     |  SERVIR documentos guardados (LONGBLOB) — para ver imagen/PDF
     |  La ruta /archivo/{id} existente sólo sirve la tabla 'archivos';
     |  ésta sirve los documentos de cliente_archivo.
     ============================================================ */
    public function verDocumento($id)
    {
        $doc = DB::table('cliente_archivo')->where('id_cliente_archivo', $id)->first();

        if (!$doc || !$doc->contenido) {
            abort(404);
        }

        return response($doc->contenido)
            ->header('Content-Type', $doc->mime_type ?? 'application/octet-stream')
            ->header('Content-Disposition', 'inline; filename="' . ($doc->nombre_original ?? 'documento') . '"');
    }

    /* ============================================================
     |  HELPERS
     ============================================================ */

    /** Convierte "$1,234.50" → 1234.50 */
    private function money($valor): float
    {
        if ($valor === null || $valor === '') {
            return 0.0;
        }
        return (float) preg_replace('/[^0-9.]/', '', (string) $valor);
    }

    /** Normaliza fecha (la vista manda Y-m-d desde flatpickr) */
    private function fecha($valor): ?string
    {
        if (empty($valor)) {
            return null;
        }
        try {
            return Carbon::parse($valor)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Valor de un array paralelo en el índice i */
    private function valArray(Request $request, string $name, $i)
    {
        $arr = $request->input($name, []);
        return is_array($arr) ? ($arr[$i] ?? null) : null;
    }

    /** Extrae un archivo de un input file tipo array (name[]) en el índice i */
    private function extraerArchivoArray(Request $request, string $name, $i): array
    {
        $vacio = ['contenido' => null, 'nombre' => null, 'mime' => null, 'extension' => null];

        $files = $request->file($name);
        if (!is_array($files) || !isset($files[$i]) || !$files[$i]) {
            return $vacio;
        }

        $a = $files[$i];

        return [
            'contenido' => file_get_contents($a->getRealPath()),
            'nombre'    => $a->getClientOriginalName(),
            'mime'      => $a->getMimeType(),
            'extension' => $a->getClientOriginalExtension(),
        ];
    }
}
