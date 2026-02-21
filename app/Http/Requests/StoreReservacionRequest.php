<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categoria_id' => 'required|integer|exists:categorias_carros,id_categoria',

            'pickup_date'         => 'required|date',
            'pickup_time'         => 'required',
            'dropoff_date'        => 'required|date',
            'dropoff_time'        => 'required',
            'pickup_sucursal_id'  => 'nullable|integer',
            'dropoff_sucursal_id' => 'nullable|integer',
            'nombre'              => 'nullable|string|max:120',
            'email'               => 'nullable|string|max:120',
            'telefono'            => 'nullable|string|max:40',
            'vuelo'               => 'nullable|string|max:40',
            'addons'              => ['nullable', 'string'],
        ];
    }
}
