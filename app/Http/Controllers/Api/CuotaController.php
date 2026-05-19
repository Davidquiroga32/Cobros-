<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cuota;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CuotaController extends Controller
{
    public function hoy(Request $request)
    {
        $cobrador = $request->user();
        $fecha    = $request->get('fecha')
            ? Carbon::parse($request->get('fecha'))
            : today();

        $cuotas = Cuota::with(['cliente', 'credito'])
            ->whereHas('credito', fn ($q) => $q->where('cobrador_id', $cobrador->id))
            ->whereDate('fecha_vencimiento', $fecha)
            ->orderBy('estado')
            ->get();

        return response()->json([
            'fecha'          => $fecha->format('Y-m-d'),
            'total'          => $cuotas->count(),
            'total_esperado' => $cuotas->sum('saldo_cuota'),
            'pendientes'     => $cuotas->whereIn('estado', ['pendiente', 'parcial', 'vencida'])->values(),
            'pagadas'        => $cuotas->where('estado', 'pagada')->values(),
        ]);
    }

    public function atrasadas(Request $request)
    {
        $cobrador = $request->user();

        $cuotas = Cuota::with(['cliente', 'credito'])
            ->whereHas('credito', fn ($q) => $q->where('cobrador_id', $cobrador->id))
            ->where('fecha_vencimiento', '<', today()->startOfDay())
            ->whereIn('estado', ['pendiente', 'parcial', 'vencida'])
            ->orderBy('fecha_vencimiento')
            ->get();

        return response()->json([
            'total'          => $cuotas->count(),
            'total_atrasado' => $cuotas->sum('saldo_cuota'),
            'cuotas'         => $cuotas,
        ]);
    }
}
