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
            return back()->with('ok', 'Gracias, tu mensaje fue enviado.'); // Silencio elegante
        }

        // ✅ Validación
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
                'name.required'    => 'El nombre es obligatorio.',
                'name.max'         => 'El nombre no debe exceder 120 caracteres.',
                'phone.required'   => 'El móvil es obligatorio.',
                'phone.regex'      => 'El móvil no tiene un formato válido.',
                'phone.max'        => 'El móvil no debe exceder 20 caracteres.',
                'email.required'   => 'El correo es obligatorio.',
                'email.email'      => 'Debe ser un correo válido.',
                'email.max'        => 'El correo no debe exceder 120 caracteres.',
                'subject.max'      => 'El asunto no debe exceder 150 caracteres.',
                'message.required' => 'El mensaje es obligatorio.',
                'message.max'      => 'El mensaje no debe exceder 800 caracteres.',
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

        // Podrías disparar un correo aquí si quieres (Mail::to(...)->send(...))

        return back()->with('ok', '¡Listo! Tu mensaje fue enviado. Te contactaremos muy pronto.');
    }
}
