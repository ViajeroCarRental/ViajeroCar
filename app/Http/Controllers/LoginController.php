<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class LoginController extends Controller
{
    /* =======================================================
       🧩 VISTA PRINCIPAL (login + registro)
    ======================================================== */
    public function showLogin()
    {
        return view('Usuarios.login');
    }

    public function showPerfil()
    {
        return view('Usuarios.perfil');
    }

    /* =======================================================
       🧾 REGISTRO: SOLO GENERA CÓDIGO Y ENVÍA CORREO
    ======================================================== */
    public function register(Request $request)
    {
        $request->validate([
            'nombres'               => 'required|string|max:120',
            'ap_paterno'            => 'required|string|max:120',
            'ap_materno'            => 'required|string|max:120',
            'fecha_nacimiento'      => 'required|date',
            'correo'                => 'required|email|unique:usuarios,correo',
            'correo_confirmacion'   => 'required|same:correo',
            'password'              => 'required|min:6',
            'password_confirmacion' => 'required|same:password',
            'acepta_terminos'       => 'accepted',
        ], [
            'required' => 'El campo :attribute es obligatorio.',
            'same' => 'Los campos :attribute no coinciden.',
            'email' => 'Debes ingresar un correo válido.',
            'unique' => 'Este correo ya está registrado.',
            'accepted' => 'Debes aceptar los Términos y Condiciones.',
            'min' => 'La contraseña debe tener al menos :min caracteres.'
        ]);

        try {
            $datos = $request->only([
                'nombres', 'ap_paterno', 'ap_materno', 'fecha_nacimiento',
                'correo', 'password'
            ]);

            $codigo = rand(100000, 999999);
            $expira = Carbon::now()->addMinutes(10);

            session([
                'registro_pendiente' => [
                    'datos' => $datos,
                    'codigo' => $codigo,
                    'expira_en' => $expira
                ]
            ]);

            $this->enviarCorreoVerificacion($datos['correo'], $codigo, $datos['nombres']);

            return back()
                ->with('success', 'Se ha enviado un código de verificación a tu correo.')
                ->with('show_modal', true)
                ->with('correo_modal', $datos['correo'])
                ->withInput();

        } catch (\Throwable $e) {
            Log::error('❌ Error en registro temporal: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al iniciar el registro.'])->withInput();
        }
    }

    /* =======================================================
       📧 ENVÍO DE CORREO DE VERIFICACIÓN (SOLO USUARIO)
    ======================================================== */
    private function enviarCorreoVerificacion($correo, $codigo, $nombre = 'Usuario')
    {
        try {
            $mensaje  = "📩 VERIFICACIÓN DE CORREO\n\n";
            $mensaje .= "Hola, {$nombre} 👋\n\n";
            $mensaje .= "Gracias por registrarte en *Viajero Car Rental*.\n\n";
            $mensaje .= "Tu código de verificación es: {$codigo}\n\n";
            $mensaje .= "Este código expira en 10 minutos.\n\n";
            $mensaje .= "⚠️ Si no solicitaste esta verificación, ignora este mensaje.\n\n";
            $mensaje .= "📆 Fecha de envío: " . now()->format('d/m/Y H:i:s') . "\n";

            Mail::raw($mensaje, function ($msg) use ($correo, $codigo) {
                $msg->to($correo)
                    ->subject("Código de verificación {$codigo} - Viajero Car Rental");
            });

            Log::info("✅ Correo de verificación enviado correctamente a {$correo}");

        } catch (\Throwable $e) {
            Log::error('❌ Error enviando correo de verificación: ' . $e->getMessage());
        }
    }

    /* =======================================================
       🔑 VERIFICACIÓN DE CÓDIGO (INSERTA USUARIO FINAL)
    ======================================================== */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'c1' => 'required|numeric',
            'c2' => 'required|numeric',
            'c3' => 'required|numeric',
            'c4' => 'required|numeric',
            'c5' => 'required|numeric',
            'c6' => 'required|numeric',
        ]);

        $codigoIngresado = implode('', [
            $request->c1, $request->c2, $request->c3,
            $request->c4, $request->c5, $request->c6
        ]);

        $temp = session('registro_pendiente');

        if (!$temp) {
            return back()->withErrors(['error' => 'No hay un registro pendiente o ha expirado.']);
        }

        if ($temp['datos']['correo'] !== $request->correo) {
            return back()->withErrors(['error' => 'El correo no coincide con el registro pendiente.']);
        }

        if ($codigoIngresado != $temp['codigo']) {
            return back()->withErrors(['error' => 'El código ingresado no es correcto.']);
        }

        if (Carbon::now()->greaterThan($temp['expira_en'])) {
            session()->forget('registro_pendiente');
            return back()->withErrors(['error' => 'El código ha expirado. Vuelve a registrarte.']);
        }

        try {
            DB::beginTransaction();

            $datos = $temp['datos'];

            $id_usuario = DB::table('usuarios')->insertGetId([
                'nombres'          => $datos['nombres'],
                'apellidos'        => $datos['ap_paterno'] . ' ' . $datos['ap_materno'],
                'correo'           => $datos['correo'],
                'contrasena_hash'  => Hash::make($datos['password']),
                'email_verificado' => true,
                'activo'           => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $rol = DB::table('roles')->where('nombre', 'Usuario')->first();
            $idRol = $rol ? $rol->id_rol : DB::table('roles')->insertGetId([
                'nombre' => 'Usuario',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('usuario_rol')->insert([
                'id_usuario' => $id_usuario,
                'id_rol'     => $idRol,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            session()->forget('registro_pendiente');

            return redirect()->route('auth.show')->with('success', 'Cuenta creada y verificada correctamente. Ya puedes iniciar sesión.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('❌ Error al crear usuario verificado: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al crear tu cuenta. Intenta nuevamente.']);
        }
    }

    /* =======================================================
       🔁 REENVIAR CÓDIGO
    ======================================================== */
    public function resendCode(Request $request)
    {
        $request->validate(['correo' => 'required|email']);

        $temp = session('registro_pendiente');
        if (!$temp || $temp['datos']['correo'] !== $request->correo) {
            return back()->withErrors(['error' => 'No hay registro pendiente con este correo.']);
        }

        $codigo = rand(100000, 999999);
        $expira = Carbon::now()->addMinutes(10);

        $temp['codigo'] = $codigo;
        $temp['expira_en'] = $expira;
        session(['registro_pendiente' => $temp]);

        $this->enviarCorreoVerificacion($request->correo, $codigo, $temp['datos']['nombres']);

        return back()->with('success', 'Se ha enviado un nuevo código de verificación a tu correo.');
    }

    /* =======================================================
       🚪 LOGIN
    ======================================================== */
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'required' => 'Debes llenar todos los campos para iniciar sesión.',
        ]);

        $usuario = DB::table('usuarios')->where('correo', $request->login)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->contrasena_hash)) {
            return back()->withErrors(['error' => 'Correo o contraseña incorrectos.'])->withInput();
        }

        $rol = DB::table('usuario_rol')
            ->join('roles', 'usuario_rol.id_rol', '=', 'roles.id_rol')
            ->where('usuario_rol.id_usuario', $usuario->id_usuario)
            ->value('roles.nombre');

        session([
            'id_usuario' => $usuario->id_usuario,
            'nombre'     => $usuario->nombres,
            'correo'     => $usuario->correo,
            'rol'        => $rol,
        ]);

        session()->flash('bienvenida', $usuario->nombres);

        // Redirección según rol
        switch ($rol) {
            case 'SuperAdmin':
            case 'Flotilla':
            case 'Ventas':
                return redirect()->route('admin.home')->with('success', "Bienvenido {$rol}.");
            case 'Usuario':
            default:
                return redirect()->route('rutaHome')->with('success', 'Inicio de sesión exitoso.');
        }
    }

    /* =======================================================
       🔒 LOGOUT
    ======================================================== */
    public function logout()
    {
        session()->flush();
        return redirect()->route('auth.show')->with('success', 'Sesión cerrada correctamente.');
    }

    /* =======================================================
       👤 PERFIL USUARIO
    ======================================================== */
    public function perfil()
    {
        if (!session('id_usuario')) {
            return redirect()->route('auth.show');
        }
        return view('usuarios.perfil');
    }

    /* =======================================================
       🧱 PANEL ADMINISTRATIVO (Dashboard)
    ======================================================== */
    public function adminHome()
    {
        if (!session('id_usuario')) {
            return redirect()->route('auth.show');
        }

        $rol = session('rol');
        $permitidos = ['SuperAdmin', 'Flotilla', 'Ventas'];

        if (!in_array($rol, $permitidos)) {
            return redirect()->route('rutaHome')->withErrors(['error' => 'No tienes permiso para acceder al panel.']);
        }

        return view('Admin.dashboard');
    }
}
