<?php

namespace App\Http\Controllers\Cobrador;

use App\Http\Controllers\Controller;
use App\Models\Cuota;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgendaController extends Controller
{
    public function index(Request $request)
    {
        $cobrador = Auth::user();
        $fecha    = $request->get('fecha')
            ? \Carbon\Carbon::parse($request->get('fecha'))
            : today();

        // Cuotas del día seleccionado
        $cuotas = Cuota::with(['cliente', 'credito'])
            ->whereHas('credito', fn ($q) => $q->where('cobrador_id', $cobrador->id))
            ->whereDate('fecha_vencimiento', $fecha)
            ->orderBy('estado')
            ->get();

        // Cuotas atrasadas (vencidas anteriores)
        $cuotasAtrasadas = Cuota::with(['cliente', 'credito'])
            ->whereHas('credito', fn ($q) => $q->where('cobrador_id', $cobrador->id))
            ->where('fecha_vencimiento', '<', $fecha->startOfDay())
            ->whereIn('estado', ['pendiente', 'parcial', 'vencida'])
            ->orderBy('fecha_vencimiento')
            ->get();

        // Cobros ya realizados en ese día
        $cobradosHoy = Pago::where('cobrador_id', $cobrador->id)
            ->whereDate('fecha_pago', $fecha)
            ->with(['cliente', 'cuota'])
            ->latest('fecha_pago')
            ->get();

        // IDs de clientes ya cobrados
        $clientesCobradosIds = $cobradosHoy->pluck('cliente_id')->unique()->toArray();

        // Separar pendientes vs cobrados
        $pendientes = $cuotas->whereNotIn('cliente_id', $clientesCobradosIds);
        $cobrados   = $cuotas->whereIn('cliente_id', $clientesCobradosIds);

        // Totales
        $totalEsperado = $cuotas->sum('saldo_cuota');
        $totalCobrado  = $cobradosHoy->sum('monto_pagado');
        $totalAtrasado = $cuotasAtrasadas->sum('saldo_cuota');

        return view('cobrador.agenda', compact(
            'fecha', 'cuotas', 'cuotasAtrasadas', 'cobradosHoy',
            'pendientes', 'cobrados', 'clientesCobradosIds',
            'totalEsperado', 'totalCobrado', 'totalAtrasado',
        ));
    }
}