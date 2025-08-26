<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class controladorVistas extends Controller
{
    // Vista de inicio
    public function home()
    {
        return view('welcome');
    }

    // vista login
    public function login()
    {
        return view('Login');
    }

    // vista registro
    public function register()
    {
        return view('Registro');
    }

}
