<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservacionLineaRequest extends FormRequest
{
    /**
     * Autorizar siempre esta petición (se puede afinar con auth si quieres).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para la reserva con pago en línea (PayPal).
     */
    public function rules(): array
    {
        return [
            // 🔹 Datos de categoría
            'categoria_id' => ['required', 'integer', 'exists:categorias_carros,id_categoria'],

            // 🔹 Fechas y horas
            'pickup_date'  => ['required', 'date_format:Y-m-d'],
            'pickup_time'  => ['required', 'date_format:H:i'],
            'dropoff_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:pickup_date'],
            'dropoff_time' => ['required', 'date_format:H:i'],

            // 🔹 Sucursales
            'pickup_sucursal_id'  => ['required', 'integer', 'exists:sucursales,id_sucursal'],
            'dropoff_sucursal_id' => ['required', 'integer', 'exists:sucursales,id_sucursal'],

            // 🔹 Datos del cliente
            'nombre'   => ['required', 'string', 'max:120'],
            'email'    => ['required', 'email', 'max:120'],
            'telefono' => ['required', 'string', 'max:40'],

            // 🔹 Vuelo (opcional)
            'vuelo' => ['nullable', 'string', 'max:40'],

            // 🔹 Addons (cadena tipo "1:2,3:1")
            'addons' => ['nullable', 'string'],

            // 🔹 ID de orden de PayPal (obligatorio)
            'paypal_order_id' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * (Opcional) Mensajes personalizados si quieres algo más amigable.
     */
    public function messages(): array
    {
        return [
            'categoria_id.required' => 'Debes seleccionar una categoría de vehículo.',
            'categoria_id.exists'   => 'La categoría seleccionada no existe.',

            'pickup_date.required'  => 'La fecha de entrega es obligatoria.',
            'pickup_date.date_format' => 'La fecha de entrega debe tener el formato YYYY-MM-DD.',
            'dropoff_date.required'   => 'La fecha de devolución es obligatoria.',
            'dropoff_date.after_or_equal' => 'La fecha de devolución no puede ser anterior a la de entrega.',

            'pickup_time.required'  => 'La hora de entrega es obligatoria.',
            'pickup_time.date_format' => 'La hora de entrega debe tener el formato HH:MM.',
            'dropoff_time.required'   => 'La hora de devolución es obligatoria.',
            'dropoff_time.date_format' => 'La hora de devolución debe tener el formato HH:MM.',

            'pickup_sucursal_id.required'  => 'Debes seleccionar la sucursal de entrega.',
            'pickup_sucursal_id.exists'    => 'La sucursal de entrega no existe.',
            'dropoff_sucursal_id.required' => 'Debes seleccionar la sucursal de devolución.',
            'dropoff_sucursal_id.exists'   => 'La sucursal de devolución no existe.',

            'nombre.required'   => 'El nombre del cliente es obligatorio.',
            'email.required'    => 'El correo electrónico es obligatorio.',
            'email.email'       => 'El correo electrónico no tiene un formato válido.',
            'telefono.required' => 'El teléfono es obligatorio.',

            'paypal_order_id.required' => 'No se recibió el identificador de la orden de PayPal.',
        ];
    }
}
