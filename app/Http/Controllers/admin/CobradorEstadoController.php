<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CobradorEstadoService;

class CobradorEstadoController extends Controller
{
    public function index(CobradorEstadoService $service)
    {
        return response()->json(
            $service->obtenerDashboard()
        );
    }
}