<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class controladorVistasAdmin extends Controller
{
    //Apartado vistas Flotilla
    // Dashboard
    public function dashboard()
    {
        return view('Admin.Dashboard');
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

    //Apartado vistas Ventas
    public function ventas()
    {
        return view('Admin.homeVentas');
    }

    public function reservacionesAdmin()
    {
        return view('Admin.Reservaciones');
    }

    public function cotizaciones()
    {
        return view('Admin.Cotizaciones');
    }

    public function cotizacionesRecientes()
    {
        return view('Admin.CotizacionesRecientes');
    }


    public function visorReservaciones()
    {
        return view('Admin.visorReservaciones');
    }

    public function administracionReservaciones()
    {
        return view('Admin.AdministracionReservas');
    }

    public function historialCompleto()
    {
        return view('Admin.Historial');
    }

    public function contrato()
    {
        return view('Admin.Contrato');
    }

    public function altaCliente()
    {
        return view('Admin.AltaCliente');
    }

    public function licencia()
    {
        return view('Admin.Licencia');
    }

    public function RFC_Fiscal()
    {
        return view('Admin.RFC-Fiscal');
    }

    public function Facturar()
    {
        return view('Admin.facturar');
    }
}
