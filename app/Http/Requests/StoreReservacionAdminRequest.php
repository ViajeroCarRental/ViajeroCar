<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservacionAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_categoria'      => 'required|exists:categorias_carros,id_categoria',
            'fecha_inicio'      => 'required|date',
            'fecha_fin'         => 'required|date|after_or_equal:fecha_inicio',
            
            'sucursal_retiro'   => 'required|integer|exists:sucursales,id_sucursal',
            'sucursal_entrega'  => 'required|integer|exists:sucursales,id_sucursal',
            
            'nombre_cliente'    => 'required|string|max:150',
            'apellidos_cliente' => 'required|string|max:150',
            'email_cliente'     => 'required|email|max:150',
            'telefono_cliente'  => 'required|string|max:30',
            'telefono_lada'     => 'nullable|string|max:10', // Opcional, solo valida formato si viene
            
            // Opcionales que podrías querer validar también (si deseas ser estricto)
            // 'tarifa_base'             => 'nullable|numeric|min:0',
            // 'hora_retiro'             => 'nullable|string',
            // 'hora_entrega'            => 'nullable|string',
            // 'adicionalesSeleccionados'=> 'nullable|array',
            // 'seguroSeleccionado'      => 'nullable|array',
        ];
    }
    
    public function attributes(): array
    {
        return [
            'id_categoria'      => 'categoría',
            'fecha_inicio'      => 'fecha de inicio',
            'fecha_fin'         => 'fecha de fin',
            'sucursal_retiro'   => 'sucursal de retiro',
            'sucursal_entrega'  => 'sucursal de entrega',
            'nombre_cliente'    => 'nombre del cliente',
            'apellidos_cliente' => 'apellidos del cliente',
            'email_cliente'     => 'correo electrónico',
            'telefono_cliente'  => 'teléfono',
        ];
    }
}
