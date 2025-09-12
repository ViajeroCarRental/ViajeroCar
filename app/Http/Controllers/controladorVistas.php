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
        return view('Usuarios.Login');
    }

    public function registro()
    {
        return view('Usuarios.Registro');
    }



    public function recuperarContrasena()
    {
        return view('Usuarios.RecuperarContraseña'); // si renombras: view('Usuarios.RecuperarContrasena')
    }

    public function verificacionDeCorreo()
    {
        return view('Usuarios.VerificacionDeCorreo');
    }

    /* ================== Vehículos ================== */
    public function catalogoVehiculos()
    {
        return view('Usuarios.CatalogoVehiculos');
    }



    public function detalleVehiculo()
    {
        return view('Usuarios.DatelleVehiculo'); // si renombras: view('Usuarios.DetalleVehiculo')
    }

    /* ================== Reservaciones / Facturas ================== */
    public function reservar()
    {
        return view('Usuarios.Reservar');
    }

    public function misReservaciones()
    {
        return view('Usuarios.MisReservaciones');
    }


    public function misFacturas()
    {
        return view('Usuarios.MisFactutas'); // si renombras: view('Usuarios.MisFacturas')
    }

    /* ================== Membresías ================== */
    public function membresias()
    {
        return view('Usuarios.Membresias');
    }

    public function miMembresia()
    {
        return view('Usuarios.MiMembresia');
    }

    /* ================== Usuario ================== */
    public function perfil()
    {
        return view('Usuarios.Perfil');
    }

    public function notificaciones()
    {
        return view('Usuarios.Notificaciones');
    }

    /* ================== Políticas ================== */
    public function politicaDeRenta()
    {
        return view('Usuarios.PoliticaDeRenta');
    }

    public function politicasDeLimpieza()
    {
        return view('Usuarios.PoliticasDeLimpieza');
    }

    public function avisoDePrivacidad()
    {
        return view('Usuarios.AvisoDePrivacidad');
    }

    public function terminosYCondiciones()
    {
        return view('Usuarios.TerminosYCondiciones');
    }

    /* ================== Información ================== */
    public function contactoYUbicaciones()
    {
        return view('Usuarios.ContactoYUbicaciones');
    }

    public function ayuda()
    {
        return view('Usuarios.Ayuda');
    }
}
