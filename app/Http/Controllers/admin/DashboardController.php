<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Credito;
use App\Models\Cuota;
use App\Models\Pago;
use App\Models\User;
use App\Services\CobradorEstadoService;

class DashboardController extends Controller
{
    public function index()
    {
        $cobradoresEstado = app(CobradorEstadoService::class)
        ->obtenerDashboard();

        // KPIs globales
        $totalClientes    = Cliente::where('estado', 'activo')->count();
        $totalCobradores  = User::where('role', 'cobrador')->where('active', true)->count();
        $creditosActivos  = Credito::whereIn('estado', ['activo', 'al_dia', 'mora'])->count();
        $carteraTotal     = Credito::whereIn('estado', ['activo', 'al_dia', 'mora'])->sum('saldo_pendiente');
        $cobradoHoy       = Pago::whereDate('fecha_pago', today())->sum('monto_pagado');
        $cobradoMes       = Pago::whereMonth('fecha_pago', now()->month)->whereYear('fecha_pago', now()->year)->sum('monto_pagado');
        $clientesEnMora   = Cliente::whereHas('creditos', fn ($q) => $q->where('estado', 'mora'))->count();
        $cuotasVencidas   = Cuota::where('estado', 'vencida')->count();

        // Rendimiento por cobrador
        $cobradores = User::where('role', 'cobrador')
            ->where('active', true)
            ->withCount(['clientes as total_clientes'])
            ->withCount(['clientes as en_mora' => fn ($q) => $q->whereHas('creditos', fn ($sq) => $sq->where('estado', 'mora'))])
            ->with(['pagos' => fn ($q) => $q->whereDate('fecha_pago', today())])
            ->orderBy('name')
            ->take(10)
            ->get()
            ->map(function ($c) {
                $c->cobrado_hoy = $c->pagos->sum('monto_pagado');
                $c->pagos_hoy   = $c->pagos->count();
                return $c;
            });

        // Cobros ultimos 30 dias (1 sola query)
        $cobros30raw = Pago::selectRaw('DATE(fecha_pago) as fecha, SUM(monto_pagado) as total')
            ->whereBetween('fecha_pago', [now()->subDays(29)->startOfDay(), now()->endOfDay()])
            ->groupBy('fecha')
            ->pluck('total', 'fecha');

        $cobros30 = collect();
        for ($i = 29; $i >= 0; $i--) {
            $fecha = now()->subDays($i);
            $cobros30->push([
                'fecha' => $fecha->format('d/m'),
                'total' => (float) ($cobros30raw[$fecha->format('Y-m-d')] ?? 0),
            ]);
        }

        // Créditos recientes
        $creditosRecientes = Credito::with(['cliente', 'cobrador'])
            ->latest()
            ->take(5)
            ->get();

        // Cuotas vencidas top
        $cuotasVencidasTop = Cuota::with(['cliente', 'credito.cobrador'])
            ->where('estado', 'vencida')
            ->orderBy('fecha_vencimiento')
            ->take(8)
            ->get();

        return view('admin.dashboard', compact('cobradoresEstado',
            'totalClientes', 'totalCobradores', 'creditosActivos', 'carteraTotal',
            'cobradoHoy', 'cobradoMes', 'clientesEnMora', 'cuotasVencidas',
            'cobradores', 'cobros30', 'creditosRecientes', 'cuotasVencidasTop'
        ));
    }
}