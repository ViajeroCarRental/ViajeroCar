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

    public function catalogo()
    {
        return view('Usuarios.Catalogo');
    }

    public function reservaciones()
    {
        return view('Usuarios.Reservaciones');
    }

    public function contacto()
    {
        return view('Usuarios.Contacto');
    }

    public function politicas()
    {
        return view('Usuarios.Politicas');
    }

    public function faq()
    {
        return view('Usuarios.FAQ');
    }

    public function login()
    {
        return view('Usuarios.login');
    }

    public function perfil()
    {
        return view('Usuarios.perfil');
    }
}
