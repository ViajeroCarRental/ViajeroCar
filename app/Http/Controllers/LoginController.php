<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Carbon\Carbon;

class LoginController extends Controller
{
    /* =====================================================
       Configuración de seguridad
    ===================================================== */
    private const MAX_LOGIN_ATTEMPTS    = 5;
    private const LOGIN_DECAY_SECONDS   = 60;
    private const MAX_VERIFY_ATTEMPTS   = 5;
    private const MAX_REGISTER_PER_HOUR = 5;
    private const MAX_RESEND_PER_HOUR   = 3;
    private const CODE_EXPIRES_MINUTES  = 10;

    /* =====================================================
       VISTAS
    ===================================================== */
    public function showLogin()
    {
        return view('Usuarios.login');
    }

    public function showPerfil()
    {
        return view('Usuarios.perfil');
    }

    /* =====================================================
       REGISTRO: Genera código y envía correo de verificación
    ===================================================== */
    public function register(Request $request)
    {
        // Rate limit: prevenir spam de registros desde la misma IP
        $rateKey = 'register:' . $request->ip();
        if (RateLimiter::tooManyAttempts($rateKey, self::MAX_REGISTER_PER_HOUR)) {
            $seconds = RateLimiter::availableIn($rateKey);
            return back()
                ->withErrors(['error' => "Demasiados intentos de registro. Intenta en {$seconds} segundos."])
                ->withInput();
        }

        $request->validate([
            'nombres'               => 'required|string|max:120',
            'ap_paterno'            => 'required|string|max:120',
            'ap_materno'            => 'required|string|max:120',
            'fecha_nacimiento'      => 'required|date|before:' . now()->subYears(18)->toDateString() . '|after:1920-01-01',
            'correo'                => 'required|email|max:191|unique:usuarios,correo',
            'correo_confirmacion'   => 'required|same:correo',
            'password'              => 'required|string|min:8',
            'password_confirmacion' => 'required|same:password',
            'acepta_terminos'       => 'accepted',
        ], [
            'required'                  => 'El campo :attribute es obligatorio.',
            'same'                      => 'Los campos :attribute no coinciden.',
            'email'                     => 'Debes ingresar un correo válido.',
            'unique'                    => 'Este correo ya está registrado.',
            'accepted'                  => 'Debes aceptar los Términos y Condiciones.',
            'min'                       => 'La contraseña debe tener al menos :min caracteres.',
            'fecha_nacimiento.before'   => 'Debes ser mayor de 18 años para registrarte.',
            'fecha_nacimiento.after'    => 'Fecha de nacimiento inválida.',
        ]);

        RateLimiter::hit($rateKey, 3600);

        try {
            // Hashear password ANTES de guardar en sesión (nunca texto plano en disco/Redis/DB)
            $datos = [
                'nombres'          => $request->input('nombres'),
                'ap_paterno'       => $request->input('ap_paterno'),
                'ap_materno'       => $request->input('ap_materno'),
                'fecha_nacimiento' => $request->input('fecha_nacimiento'),
                'correo'           => $request->input('correo'),
                'password_hash'    => Hash::make($request->input('password')),
            ];

            $codigo = random_int(100000, 999999);

            session([
                'registro_pendiente' => [
                    'datos'     => $datos,
                    'codigo'    => $codigo,
                    'expira_en' => Carbon::now()->addMinutes(self::CODE_EXPIRES_MINUTES),
                    'intentos' => 0,
                ]
            ]);

            $enviado = $this->enviarCorreoVerificacion($datos['correo'], $codigo, $datos['nombres']);

            if (!$enviado) {
                session()->forget('registro_pendiente');
                return back()
                    ->withErrors(['error' => 'No se pudo enviar el correo de verificación. Intenta más tarde.'])
                    ->withInput();
            }

            return back()
                ->with('success', 'Se ha enviado un código de verificación a tu correo.')
                ->with('show_modal', true)
                ->with('correo_modal', $datos['correo'])
                ->withInput();

        } catch (\Throwable $e) {
            Log::error('Error en registro temporal: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al iniciar el registro.'])->withInput();
        }
    }

    /* =====================================================
       Envío de correo de verificación
       Retorna bool para que el caller sepa si falló
    ===================================================== */
    private function enviarCorreoVerificacion(string $correo, int $codigo, string $nombre = 'Usuario'): bool
    {
        try {
            $mensaje  = "Hola, {$nombre}.\n\n";
            $mensaje .= "Gracias por registrarte en Viajero Car Rental.\n\n";
            $mensaje .= "Tu código de verificación es: {$codigo}\n\n";
            $mensaje .= "Este código expira en " . self::CODE_EXPIRES_MINUTES . " minutos.\n\n";
            $mensaje .= "Si no solicitaste esta verificación, ignora este mensaje.\n\n";
            $mensaje .= "Fecha de envío: " . now()->format('d/m/Y H:i:s') . "\n";

            Mail::raw($mensaje, function ($msg) use ($correo, $codigo) {
                $msg->to($correo)
                    ->subject("Codigo de verificacion {$codigo} - Viajero Car Rental");
            });

            Log::info("Correo de verificacion enviado a {$correo}");
            return true;

        } catch (\Throwable $e) {
            Log::error('Error enviando correo de verificacion: ' . $e->getMessage());
            return false;
        }
    }

    /* =====================================================
       VERIFICACIÓN DE CÓDIGO: Inserta usuario final
    ===================================================== */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'c1'     => 'required|digits:1',
            'c2'     => 'required|digits:1',
            'c3'     => 'required|digits:1',
            'c4'     => 'required|digits:1',
            'c5'     => 'required|digits:1',
            'c6'     => 'required|digits:1',
        ]);

        $temp = session('registro_pendiente');

        if (!$temp) {
            return back()->withErrors(['error' => 'No hay un registro pendiente o ha expirado.']);
        }

        // Validar expiración ANTES de validar el código (no revelar si el código era correcto)
        if (Carbon::now()->greaterThan($temp['expira_en'])) {
            session()->forget('registro_pendiente');
            return back()->withErrors(['error' => 'El código ha expirado. Vuelve a registrarte.']);
        }

        // Limitar intentos (anti brute force del OTP de 6 dígitos)
        $temp['intentos'] = ($temp['intentos'] ?? 0) + 1;
        if ($temp['intentos'] > self::MAX_VERIFY_ATTEMPTS) {
            session()->forget('registro_pendiente');
            return back()->withErrors(['error' => 'Demasiados intentos fallidos. Vuelve a registrarte.']);
        }
        session(['registro_pendiente' => $temp]);

        if ($temp['datos']['correo'] !== $request->input('correo')) {
            return back()->withErrors(['error' => 'El correo no coincide con el registro pendiente.']);
        }

        $codigoIngresado = $request->input('c1') . $request->input('c2') . $request->input('c3')
                         . $request->input('c4') . $request->input('c5') . $request->input('c6');

        // hash_equals: comparación strict + timing-safe (previene ataques de tiempo)
        if (!hash_equals((string) $temp['codigo'], $codigoIngresado)) {
            $restantes = self::MAX_VERIFY_ATTEMPTS - $temp['intentos'];
            $msg = $restantes > 0
                ? "El código ingresado no es correcto. Intentos restantes: {$restantes}."
                : 'El código ingresado no es correcto.';
            return back()->withErrors(['error' => $msg]);
        }

        try {
            DB::beginTransaction();

            $datos = $temp['datos'];

            $id_usuario = DB::table('usuarios')->insertGetId([
                'nombres'          => $datos['nombres'],
                'apellidos'        => $datos['ap_paterno'] . ' ' . $datos['ap_materno'],
                'correo'           => $datos['correo'],
                'contrasena_hash'  => $datos['password_hash'], // ya hasheada al registro
                'email_verificado' => 1,
                'activo'           => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $rol = DB::table('roles')->where('nombre', 'Usuario')->first();
            $idRol = $rol ? $rol->id_rol : DB::table('roles')->insertGetId([
                'nombre'     => 'Usuario',
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

            return redirect()->route('auth.show')
                ->with('success', 'Cuenta creada y verificada correctamente. Ya puedes iniciar sesión.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error al crear usuario verificado: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al crear tu cuenta. Intenta nuevamente.']);
        }
    }

    /* =====================================================
       REENVIAR CÓDIGO
    ===================================================== */
    public function resendCode(Request $request)
    {
        $request->validate(['correo' => 'required|email']);

        // Rate limit: prevenir spam de correos de reenvío
        $rateKey = 'resend:' . $request->ip() . ':' . strtolower($request->input('correo'));
        if (RateLimiter::tooManyAttempts($rateKey, self::MAX_RESEND_PER_HOUR)) {
            $seconds = RateLimiter::availableIn($rateKey);
            return back()->withErrors(['error' => "Demasiados reenvíos. Intenta en {$seconds} segundos."]);
        }

        $temp = session('registro_pendiente');
        if (!$temp || $temp['datos']['correo'] !== $request->input('correo')) {
            return back()->withErrors(['error' => 'No hay registro pendiente con este correo.']);
        }

        RateLimiter::hit($rateKey, 3600);

        $codigo = random_int(100000, 999999);

        $temp['codigo']    = $codigo;
        $temp['expira_en'] = Carbon::now()->addMinutes(self::CODE_EXPIRES_MINUTES);
        $temp['intentos'] = 0; // reset de intentos al reenviar
        session(['registro_pendiente' => $temp]);

        $enviado = $this->enviarCorreoVerificacion(
            $request->input('correo'),
            $codigo,
            $temp['datos']['nombres']
        );

        if (!$enviado) {
            return back()->withErrors(['error' => 'No se pudo reenviar el correo. Intenta más tarde.']);
        }

        return back()
            ->with('success', 'Se ha enviado un nuevo código de verificación a tu correo.')
            ->with('show_modal', true)
            ->with('correo_modal', $request->input('correo'));
    }

    /* =====================================================
       LOGIN
    ===================================================== */
    public function login(Request $request)
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ], [
            'required' => 'Debes llenar todos los campos para iniciar sesión.',
        ]);

        $loginInput = trim($request->input('login'));

        // Rate limit: prevenir brute force por IP + email
        $rateKey = 'login:' . $request->ip() . ':' . strtolower($request->input('login'));
        if (RateLimiter::tooManyAttempts($rateKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($rateKey);
            return back()
                ->withErrors(['error' => "Demasiados intentos fallidos. Intenta en {$seconds} segundos."])
                ->withInput();
        }

         // 🆕 Detectar si es correo o nombre de usuario y buscar en la columna adecuada
            $campo = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'correo' : 'nombre_usuario';

            $usuario = DB::table('usuarios')
                ->where($campo, $loginInput)
                ->first();


        // Verificación de credenciales (timing constant: si no existe usuario, igual consume tiempo equivalente)
        $validPassword = $usuario
            ? Hash::check($request->input('password'), $usuario->contrasena_hash)
            : Hash::check($request->input('password'), '$2y$10$invalidpaddingforpaddingtimingattack.dummy.hash.value.');

        if (!$usuario || !$validPassword) {
            RateLimiter::hit($rateKey, self::LOGIN_DECAY_SECONDS);
            return back()->withErrors(['error' => 'Correo/Usuario o contraseña incorrectos.'])->withInput();
        }

        // Verificar que la cuenta esté activa y el correo verificado
        if (!$usuario->activo) {
            return back()->withErrors(['error' => 'Tu cuenta está inactiva. Contacta a soporte.'])->withInput();
        }
        // 🆕 Solo exigir verificación de correo si el login fue por correo
    //    (los usuarios admin con solo nombre_usuario no tienen correo que verificar)
    if ($campo === 'correo' && !$usuario->email_verificado) {
        return back()->withErrors(['error' => 'Debes verificar tu correo antes de iniciar sesión.'])->withInput();
    }

        // Login exitoso: limpiar rate limiter
        RateLimiter::clear($rateKey);

        $rol = DB::table('usuario_rol')
            ->join('roles', 'usuario_rol.id_rol', '=', 'roles.id_rol')
            ->where('usuario_rol.id_usuario', $usuario->id_usuario)
            ->value('roles.nombre');

        // Regenerar sesión para prevenir Session Fixation
        $request->session()->regenerate();

        session([
            'id_usuario' => $usuario->id_usuario,
            'nombre'     => $usuario->nombres,
            'correo'     => $usuario->correo,
            'usuario'    => $usuario->nombre_usuario,
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

    /* =====================================================
       LOGOUT
    ===================================================== */
    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.show')->with('success', 'Sesión cerrada correctamente.');
    }

    /* =====================================================
       PERFIL USUARIO
    ===================================================== */
    public function perfil()
    {
        if (!session('id_usuario')) {
            return redirect()->route('auth.show');
        }
        return view('Usuarios.perfil');
    }

    /* =====================================================
       PANEL ADMINISTRATIVO (Dashboard)
    ===================================================== */
    public function adminHome()
    {
        if (!session('id_usuario')) {
            return redirect()->route('auth.show');
        }

        $rol = session('rol');
        $permitidos = ['SuperAdmin', 'Flotilla', 'Ventas'];

        if (!in_array($rol, $permitidos, true)) {
            return redirect()->route('rutaHome')
                ->withErrors(['error' => 'No tienes permiso para acceder al panel.']);
        }

        return view('Admin.Dashboard');
    }
}
