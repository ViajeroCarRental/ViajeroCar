<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class controladorVistasAdmin extends Controller
{
    //Apartado vistas Flotilla
    // Dashboard
    public function dashboard()
    {
        return view('Admin.dashboard');
    }

    public function mantenimiento()
    {
        return view('Admin.mantenimiento');
    }

    public function flotilla()
    {
        return view('Admin.flotilla');
    }

    public function polizas()
    {
        return view('Admin.polizas');
    }

    public function carroceria()
    {
        return view('Admin.carroceria');
    }

    public function seguros()
    {
        return view('Admin.seguros');
    }
    public function gastos()
    {
        return view('Admin.gastos');
    }

    //Apartado vistas Usuarios Admin

    public function usuarios()
    {
        return view('Admin.Usuarios');
    }

    public function roles()
    {
        return view('Admin.Roles');
    }
}
