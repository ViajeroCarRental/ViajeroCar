<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContratoRevision extends Model
{
    protected $table = 'contrato_revisiones';

    protected $primaryKey = 'id_revision';

    protected $fillable = [
        'id_contrato',
        'seccion',
        'revisado',
        'revisado_por',
        'revisado_en',
    ];

    protected $casts = [
        'revisado' => 'boolean',
        'revisado_en' => 'datetime',
    ];
}