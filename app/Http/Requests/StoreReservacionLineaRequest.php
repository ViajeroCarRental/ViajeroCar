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
     * 🛡️ NORMALIZACIÓN DEFENSIVA antes de validar.
     *
     * Esto blinda al backend contra cualquier formato raro que venga del JS:
     * - Fechas en dd-mm-yyyy o dd/mm/yyyy → yyyy-mm-dd
     * - Horas en HH:MM:SS → HH:MM
     * - Trim de espacios en strings
     *
     * Si el JS algún día vuelve a fallar con el formato, el backend se cura solo.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'pickup_date'  => $this->normalizeDate($this->input('pickup_date')),
            'dropoff_date' => $this->normalizeDate($this->input('dropoff_date')),
            'pickup_time'  => $this->normalizeTime($this->input('pickup_time')),
            'dropoff_time' => $this->normalizeTime($this->input('dropoff_time')),
            'nombre'       => trim((string) $this->input('nombre', '')),
            'email'        => trim((string) $this->input('email', '')),
            'telefono'     => trim((string) $this->input('telefono', '')),
            'vuelo'        => trim((string) $this->input('vuelo', '')),
            'addons'       => trim((string) $this->input('addons', '')),
        ]);
    }

    /**
     * Normaliza una fecha a formato Y-m-d.
     * Acepta: yyyy-mm-dd, dd-mm-yyyy, dd/mm/yyyy
     */
    private function normalizeDate(?string $date): ?string
    {
        if (!$date) return $date;
        $date = trim($date);
        if ($date === '') return $date;

        // Ya viene en yyyy-mm-dd → la dejamos
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        // dd-mm-yyyy o dd/mm/yyyy → yyyy-mm-dd
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $date, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }

        // Último intento con strtotime
        $ts = strtotime($date);
        return $ts ? date('Y-m-d', $ts) : $date;
    }

    /**
     * Normaliza una hora a formato H:i.
     * Acepta: HH:MM, HH:MM:SS, H:MM
     */
    private function normalizeTime(?string $time): ?string
    {
        if (!$time) return $time;
        $time = trim($time);
        if ($time === '') return $time;

        // HH:MM exacto → la dejamos
        if (preg_match('/^\d{2}:\d{2}$/', $time)) {
            return $time;
        }

        // HH:MM:SS → cortamos a HH:MM
        if (preg_match('/^(\d{2}):(\d{2}):\d{2}$/', $time, $m)) {
            return $m[1] . ':' . $m[2];
        }

        // H:MM → 0H:MM
        if (preg_match('/^(\d{1}):(\d{2})$/', $time, $m)) {
            return '0' . $m[1] . ':' . $m[2];
        }

        // Último intento con strtotime
        $ts = strtotime($time);
        return $ts ? date('H:i', $ts) : $time;
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

            // 🔹 Total que cobró PayPal (informativo, lo usa el controlador para comparar)
            'total_local' => ['nullable', 'numeric', 'min:0'],

            // 🔹 Plan (opcional, debe ser "linea" pero no rompemos si llega vacío)
            'plan' => ['nullable', 'string', 'in:linea,mostrador'],
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
