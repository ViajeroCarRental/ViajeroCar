<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservacionLineaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return false;
    }

    public function rules(): array
    {
        $rules = parent::rules();

        $rules['paypal_order_id'] = 'required|string';
        $rules['addons'] = ['nullable', 'string'];

        return $rules;
    }
}
