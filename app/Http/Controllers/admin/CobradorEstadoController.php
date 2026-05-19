<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CobradorEstadoService;

class CobradorEstadoController extends Controller
{
    public function index(CobradorEstadoService $service)
    {
        // BUG ORIGINAL: retornaba JSON en una ruta web. Ahora retorna vista.
        $cobradores = $service->obtenerDashboard();

        return view('admin.cobradores.estado', compact('cobradores'));
    }
}