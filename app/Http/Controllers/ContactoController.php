<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactoController extends Controller
{
    public function store(Request $request)
    {
        // 🪤 Honeypot (si el bot llena este campo, ignoramos silenciosamente)
        if ($request->filled('company')) {
            return back()->with('ok', __('Thank you, your message was sent.'));
        }

        // ✅ Validación con traducciones
        $data = $request->validate(
            [
                'name'    => ['required','string','max:120'],
                'phone'   => ['required','string','max:20','regex:/^[0-9 +()\-]{8,}$/'],
                'email'   => ['required','email','max:120'],
                'subject' => ['nullable','string','max:150'],
                'message' => ['required','string','max:800'],
                'promociones' => ['nullable','boolean'],
            ],
            [
                'name.required'    => __('The name field is required.'),
                'name.max'         => __('The name must not exceed 120 characters.'),
                'phone.required'   => __('The mobile number is required.'),
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

        // Normalizar checkbox
        $promos = $request->boolean('promociones');

        // Insertar (sin modelos, directo a la BD)
        DB::table('contacto')->insert([
            'nombre'      => $data['name'],
            'telefono'    => $data['phone'],
            'email'       => $data['email'],
            'asunto'      => $data['subject'] ?? null,
            'mensaje'     => $data['message'],
            'promociones' => $promos ? 1 : 0,
            'origen'      => 'contacto_web',
            'fecha_envio' => now(),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return back()->with('ok', __('Done! Your message has been sent. We will contact you shortly.'));
    }
}
