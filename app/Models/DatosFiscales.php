<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatosFiscales extends Model
{
    protected $table = 'datos_fiscales';
    protected $primaryKey = 'id_datos_fiscales';
    
    protected $fillable = [
        'id_cliente', 'rfc', 'razon_social', 'regimen_fiscal',
        'codigo_postal', 'correo', 'facturapi_customer_id', 'predeterminado',
    ];
}