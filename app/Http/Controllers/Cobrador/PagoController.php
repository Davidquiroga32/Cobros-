<?php

namespace App\Http\Controllers\Cobrador;

use App\Http\Controllers\Controller;
use App\Models\Credito;
use App\Models\Cuota;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    /**
     * Formulario para registrar pago de una cuota específica
     */
    public function create(Cuota $cuota)
    {
        $cuota->load(['credito.cliente', 'cliente']);
        abort_if($cuota->credito->cobrador_id !== Auth::id(), 403);
        abort_if($cuota->estado === 'pagada', 422, 'Esta cuota ya fue pagada.');

        return view('cobrador.pagos.create', compact('cuota'));
    }

    /**
     * Registrar el pago
     */
    public function store(Request $request, Cuota $cuota)
    {
        abort_if($cuota->credito->cobrador_id !== Auth::id(), 403);

        $validated = $request->validate([
            'monto_pagado'  => ['required', 'numeric', 'min:0.01', 'max:' . $cuota->saldo_cuota],
            'metodo_pago'   => ['required', 'in:efectivo,transferencia,nequi,daviplata,otro'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'latitud'       => ['nullable', 'numeric'],
            'longitud'      => ['nullable', 'numeric'],
        ], [
            'monto_pagado.required' => 'Ingresa el monto pagado.',
            'monto_pagado.max'      => 'El monto no puede superar el saldo de la cuota ($' . number_format($cuota->saldo_cuota, 2) . ').',
            'metodo_pago.required'  => 'Selecciona el método de pago.',
        ]);

        DB::transaction(function () use ($validated, $cuota) {
            $cobrador = Auth::user();
            $credito  = $cuota->credito;
            $monto    = (float) $validated['monto_pagado'];
            $esParcial = $monto < $cuota->saldo_cuota;

            // Crear el pago
            Pago::create([
                'recibo_numero'  => Pago::generarRecibo(),
                'credito_id'     => $credito->id,
                'cuota_id'       => $cuota->id,
                'cliente_id'     => $cuota->cliente_id,
                'cobrador_id'    => $cobrador->id,
                'monto_pagado'   => $monto,
                'monto_mora'     => 0,
                'total_recibido' => $monto,
                'metodo_pago'    => $validated['metodo_pago'],
                'fecha_pago'     => now(),
                'observaciones'  => $validated['observaciones'] ?? null,
                'latitud'        => $validated['latitud'] ?? null,
                'longitud'       => $validated['longitud'] ?? null,
                'es_pago_parcial' => $esParcial,
            ]);

            // Actualizar cuota
            $nuevoSaldo   = $cuota->saldo_cuota - $monto;
            $nuevoPagado  = $cuota->valor_pagado + $monto;
            $cuota->update([
                'valor_pagado' => $nuevoPagado,
                'saldo_cuota'  => $nuevoSaldo,
                'fecha_pago'   => $esParcial ? null : now()->toDateString(),
                'estado'       => $esParcial ? 'parcial' : 'pagada',
            ]);

            // Actualizar saldo del crédito
            $nuevoSaldoCredito = $credito->saldo_pendiente - $monto;
            $estadoCredito = $nuevoSaldoCredito <= 0
                ? 'pagado'
                : ($credito->cuotas()->where('estado', 'vencida')->exists() ? 'mora' : 'al_dia');

            $credito->update([
                'saldo_pendiente'    => max(0, $nuevoSaldoCredito),
                'estado'             => $estadoCredito,
                'proxima_fecha_pago' => $credito->cuotas()
                    ->whereIn('estado', ['pendiente', 'parcial'])
                    ->orderBy('fecha_vencimiento')
                    ->value('fecha_vencimiento'),
            ]);
        });

        return redirect()->route('cobrador.agenda')
            ->with('success', '✅ Pago registrado exitosamente.');
    }

    /**
     * Historial de pagos del cobrador
     */
    public function index(Request $request)
    {
        $cobrador = Auth::user();

        $pagos = Pago::with(['cliente', 'credito', 'cuota'])
            ->where('cobrador_id', $cobrador->id)
            ->when($request->get('buscar'), function ($q, $buscar) {
                $q->whereHas('cliente', fn ($cq) => $cq->where('nombre', 'like', "%{$buscar}%"));
            })
            ->when($request->get('fecha'), function ($q, $fecha) {
                $q->whereDate('fecha_pago', $fecha);
            })
            ->latest('fecha_pago')
            ->paginate(20)
            ->withQueryString();

        $totalHoy   = Pago::where('cobrador_id', $cobrador->id)->whereDate('fecha_pago', today())->sum('monto_pagado');
        $totalMes   = Pago::where('cobrador_id', $cobrador->id)->whereMonth('fecha_pago', now()->month)->sum('monto_pagado');
        $totalPagos = Pago::where('cobrador_id', $cobrador->id)->whereDate('fecha_pago', today())->count();

        return view('cobrador.pagos.index', compact('pagos', 'totalHoy', 'totalMes', 'totalPagos'));
    }
}