<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class controladorVistasAdmin extends Controller
{
    // Dashboard
    public function dashboard()
    {
        return view('Admin.Dashboard');
    }

    // Bitácora
    public function bitacora()
    {
        return view('Admin.Bitacora');
    }

    // Calendario de Ocupación
    public function calendarioDeOcupacion()
    {
        return view('Admin.CalendarioDeOcupacion');
    }

    // Configuración
    public function configuracion()
    {
        return view('Admin.Configuracion');
    }

    // Contratos (tu archivo está en minúsculas: contratos.blade.php)
    public function contratos()
    {
        return view('Admin.contratos'); // si lo renombras a "Contratos.blade.php": view('Admin.Contratos')
    }

    // Facturas
    public function facturas()
    {
        return view('Admin.Facturas');
    }

    // Inventario
    public function inventario()
    {
        return view('Admin.Inventario');
    }

    // Pagos
    public function pagos()
    {
        return view('Admin.Pagos');
    }

    // Plantillas
    public function plantillas()
    {
        return view('Admin.Plantillas');
    }

    // Rentas
    public function rentas()
    {
        return view('Admin.Rentas');
    }

    // Reportes
    public function reportes()
    {
        return view('Admin.Reportes');
    }

    // Reservaciones
    public function reservaciones()
    {
        return view('Admin.Reservaciones');
    }

    // Usuarios y Roles
    public function usuariosYRoles()
    {
        return view('Admin.UsuariosYRoles');
    }

    public function membresias()
    {
        return view('Admin.Membresias');
    }
}
