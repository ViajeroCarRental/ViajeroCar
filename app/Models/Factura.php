<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    protected $fillable = [
        'id_contrato',
        'facturapi_id',
        'uuid',
        'folio_reservacion',
        'rfc_receptor',
        'nombre_receptor',
        'total',
        'status',
        'estatus',
        'facturable_hasta',
        'fecha_timbrado',
        'origen',
        'id_factura_relacionada',
        'ruta_xml',
        'ruta_pdf',
    ];

    protected $casts = [
        'facturable_hasta' => 'datetime',
        'fecha_timbrado'   => 'datetime',
    ];

    public function puedeFacturarse(): bool
    {
        return $this->estatus === 'pendiente'
            && $this->facturable_hasta
            && now()->lte($this->facturable_hasta);
    }
}
