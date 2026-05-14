<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContactoController extends Controller
{
    public function store(Request $request)
    {
        // ===== Honeypot anti-spam =====
        // Si el bot llena este campo, logueamos el intento y devolvemos
        // respuesta "exitosa" para no darle pistas al atacante.
        if ($request->filled('company')) {
            Log::warning('Honeypot hit en formulario de contacto', [
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return back()->with('ok', __('Thank you, your message was sent.'));
        }

        // ===== Validación =====
        $data = $request->validate(
            [
                'name'        => ['required', 'string', 'max:120'],
                'phone'       => [
                    'required',
                    'string',
                    'max:20',
                    'regex:/^[0-9 +()\-]{8,}$/',
                    // Exige al menos 8 dígitos numéricos reales
                    function ($attribute, $value, $fail) {
                        if (preg_match_all('/\d/', $value) < 8) {
                            $fail(__('The mobile number must contain at least 8 digits.'));
                        }
                    },
                ],
                'email'       => ['required', 'email', 'max:120'],
                'subject'     => ['nullable', 'string', 'max:150'],
                'message'     => ['required', 'string', 'max:800'],
                'promociones' => ['nullable', 'boolean'],
            ],
            [
                'name.required'    => __('The name field is required.'),
                'name.max'         => __('The name must not exceed 120 characters.'),
                'phone.required'  => __('The mobile number is required.'),
                'phone.regex'      => __('The mobile number format is invalid.'),
                'phone.max'        => __('The mobile number must not exceed 20 characters.'),
                'email.required'   => __('The email field is required.'),
                'email.email'      => __('Please enter a valid email address.'),
                'email.max'        => __('The email must not exceed 120 characters.'),
                'subject.max'      => __('The subject must not exceed 150 characters.'),
                'message.required' => __('The message field is required.'),
                'message.max'      => __('The message must not exceed 800 characters.'),
            ]
        );

        // ===== Insertar en BD =====
        try {
            DB::table('contacto')->insert([
                'nombre'      => $data['name'],
                'telefono'    => $data['phone'],
                'email'       => $data['email'],
                'asunto'      => $data['subject'] ?? null,
                'mensaje'     => $data['message'],
                'promociones' => $request->boolean('promociones') ? 1 : 0,
                'origen'      => 'contacto_web',
                'fecha_envio' => now(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('Error al guardar mensaje de contacto', [
                'error' => $e->getMessage(),
                'ip'    => $request->ip(),
                'email' => $data['email'],
            ]);

            return back()
                ->withInput()
                ->with('error', __('We could not save your message right now. Please try again later or call us.'));
        }

        return back()->with('ok', __('Done! Your message has been sent. We will contact you shortly.'));
    }
}
