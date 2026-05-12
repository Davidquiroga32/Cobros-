<?php

namespace App\Services;

use App\Models\CobradorEstado;

class CobradorEstadoService
{
    public function obtenerDashboard()
    {
        return CobradorEstado::with('cobrador')
            ->whereHas('cobrador', function ($q) {
                $q->where('role', 'cobrador');
            })
            ->latest()
            ->get();
    }
}