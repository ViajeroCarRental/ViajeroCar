<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCotizacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return false;
    }

    public function rules(): array
    {
        return [
            'categoria_id' => 'required|integer|exists:categorias_carros,id_categoria',
            'pickup_date'         => 'required|date_format:Y-m-d',
            'pickup_time'         => 'required|date_format:H:i',
            'dropoff_date'        => 'required|date_format:Y-m-d',
            'dropoff_time'        => 'required|date_format:H:i',
            'pickup_sucursal_id'  => 'nullable|integer',
            'dropoff_sucursal_id' => 'nullable|integer',
            'addons'              => 'nullable|array',
            'nombre'              => 'nullable|string|max:150',
            'email'               => 'nullable|email|max:150',
            'telefono'            => 'nullable|string|max:30',
        ];
    }
}
