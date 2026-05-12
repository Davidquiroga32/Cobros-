<?php

namespace App\Http\Controllers\Cobrador;

use App\Http\Controllers\Controller;
use App\Models\Cuota;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $cobrador = Auth::user();

        // Cuotas de hoy asignadas a este cobrador
        $cuotasHoy = Cuota::with(['cliente', 'credito'])
            ->whereHas('credito', fn ($q) => $q->where('cobrador_id', $cobrador->id))
            ->whereDate('fecha_vencimiento', today())
            ->whereIn('estado', ['pendiente', 'parcial', 'vencida'])
            ->get();

        // Cobros ya realizados hoy
        $cobradosHoy = Pago::where('cobrador_id', $cobrador->id)
            ->whereDate('fecha_pago', today())
            ->with(['cliente', 'cuota'])
            ->latest('fecha_pago')
            ->get();

        // Clientes en mora
        $clientesEnMora = $cobrador->clientes()
            ->with(['creditoActivo'])
            ->whereHas('creditos', fn ($q) => $q->where('estado', 'mora'))
            ->take(5)
            ->get();

        // Métricas del día
        $totalCobradoHoy   = $cobradosHoy->sum('monto_pagado');
        $metaDiaria        = $cuotasHoy->sum('saldo_cuota') + $totalCobradoHoy;
        $porcentajeMeta    = $metaDiaria > 0 ? min(100, round(($totalCobradoHoy / $metaDiaria) * 100)) : 0;

        // Clientes en la ruta de hoy (con cuotas pendientes hoy)
        $totalVisitasHoy   = $cuotasHoy->unique('cliente_id')->count();
        $visitasCompletadas = $cobradosHoy->unique('cliente_id')->count();

        // Cobros de la semana para la gráfica
        $cobrosSemana = collect();
        for ($i = 6; $i >= 0; $i--) {
            $fecha = now()->subDays($i);
            $cobrosSemana->push([
                'fecha'  => $fecha->format('D'),
                'fecha_completa' => $fecha->format('Y-m-d'),
                'total'  => Pago::where('cobrador_id', $cobrador->id)
                    ->whereDate('fecha_pago', $fecha)
                    ->sum('monto_pagado'),
            ]);
        }

        // Próximas cuotas (mañana y pasado)
        $proximasCuotas = Cuota::with(['cliente', 'credito'])
            ->whereHas('credito', fn ($q) => $q->where('cobrador_id', $cobrador->id))
            ->whereBetween('fecha_vencimiento', [today()->addDay(), today()->addDays(3)])
            ->where('estado', 'pendiente')
            ->orderBy('fecha_vencimiento')
            ->take(5)
            ->get();

        return view('cobrador.dashboard', compact(
            'cuotasHoy',
            'cobradosHoy',
            'clientesEnMora',
            'totalCobradoHoy',
            'metaDiaria',
            'porcentajeMeta',
            'totalVisitasHoy',
            'visitasCompletadas',
            'cobrosSemana',
            'proximasCuotas',
        ));
    }
}