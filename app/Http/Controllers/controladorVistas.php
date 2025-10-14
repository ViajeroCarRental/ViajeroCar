<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ControladorVistas extends Controller
{

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
