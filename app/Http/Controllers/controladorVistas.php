<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ControladorVistas extends Controller
{
    /* ================== Inicio ================== */
    public function home()
    {
        return view('welcome');
    }

    /* ================== Autenticación ================== */
    public function login()
    {
        return view('Login');
    }

    public function registro()
    {
        return view('Registro');
    }

    // OJO: tu archivo se llama "RecuperarContraseña.blade.php".
    // Laravel lo carga, pero es recomendable renombrar a "RecuperarContrasena.blade.php" (sin ñ).
    public function recuperarContrasena()
    {
        return view('RecuperarContraseña'); // si renombras: view('RecuperarContrasena')
    }

    public function verificacionDeCorreo()
    {
        return view('VerificacionDeCorreo');
    }

    /* ================== Vehículos ================== */
    public function catalogoVehiculos()
    {
        return view('CatalogoVehiculos');
    }

    // OJO: en tu carpeta aparece "DatelleVehiculo.blade.php" (parece typo).
    // Recomendado renombrar a "DetalleVehiculo.blade.php".
    public function detalleVehiculo()
    {
        return view('DatelleVehiculo'); // si renombras: view('DetalleVehiculo')
    }

    /* ================== Reservaciones / Facturas ================== */
    public function reservar()
    {
        return view('Reservar');
    }

    public function misReservaciones()
    {
        return view('MisReservaciones');
    }

    // OJO: en la carpeta aparece "MisFactutas.blade.php" (typo).
    // Recomendado renombrar a "MisFacturas.blade.php".
    public function misFacturas()
    {
        return view('MisFactutas'); // si renombras: view('MisFacturas')
    }

    /* ================== Membresías ================== */
    public function membresias()
    {
        return view('Membresias');
    }

    public function miMembresia()
    {
        return view('MiMembresia');
    }

    /* ================== Usuario ================== */
    public function perfil()
    {
        return view('Perfil');
    }

    public function notificaciones()
    {
        return view('Notificaciones');
    }

    /* ================== Políticas ================== */
    public function politicaDeRenta()
    {
        return view('PoliticaDeRenta');
    }

    public function politicasDeLimpieza()
    {
        return view('PoliticasDeLimpieza');
    }

    public function avisoDePrivacidad()
    {
        return view('AvisoDePrivacidad');
    }

    public function terminosYCondiciones()
    {
        return view('TerminosYCondiciones');
    }

    /* ================== Información ================== */
    public function contactoYUbicaciones()
    {
        return view('ContactoYUbicaciones');
    }

    public function ayuda()
    {
        return view('Ayuda');
    }
}
