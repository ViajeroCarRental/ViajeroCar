<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChecklistCambioAutoController extends Controller
{
    /**
     * Mostrar checklist de cambio de auto
     */
    public function index()
    {
        return view('Admin.checklist2');
    }
}
